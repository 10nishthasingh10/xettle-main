<?php

namespace App\Helpers;


use App\Models\User;
use App\Models\UserService;
use App\Models\Transaction;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Service;
use App\Models\Product;
use App\Models\UPICallback;
use App\Models\GlobalProductFee;
use App\Models\GlobalConfig;
use App\Models\RefundTransaction;
use App\Models\UPICollect;
use App\Models\Webhook;
use App\Helpers\WebhookHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransactionHelper
{
    /**
     * Check and Lock User Balance
     *
     * @param [type] $userId
     * @param [type] $serviceId
     * @param [type] $amount
     * @return void
     */
    public static function checkAndLock($userId, $serviceId, $amount, $orderRefId = "")
    {
        $amountPositiveInt = self::intPositive($amount);
        if ($amountPositiveInt['status']) {

            DB::beginTransaction();

            $result = UserService::where(['user_id' => $userId, 'service_id' => $serviceId])
                ->where('is_active', '1')
                ->first();

            if ($result) {
                $accNumber = $result->service_account_number;
                $lockedAmount = $result->locked_amount;
                $transAmount = $result->transaction_amount;
                $positiveInt = self::intPositive($transAmount);

                if ($positiveInt['status']) {
                    if ($transAmount >= $amount) {
                        $result->transaction_amount -= (float) $amount;
                        $result->locked_amount += (float) $amount;
                        $data = $result->save();
                        $arrayData = array(
                            'user_id' => $userId,
                            'order_id' => $orderRefId,
                            'ser_trn_amount' => $result->transaction_amount,
                            'ser_loc_amount' => $result->locked_amount,
                            'amount' => $amount,
                            'type' => 'check_and_lock'
                        );

                        self::isServiceWalletNegative($arrayData);

                        if ($data) {
                            $response['status'] = true;
                            $response['message'] = 'Transaction amount locked';
                        } else {
                            $response['status'] = false;
                            $response['message'] = 'DB action failed';
                        }
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Insufficient funds';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Insufficient funds';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Service not activated';
            }

            DB::commit();
        } else {
            $response['status'] = false;
            $response['message'] = 'Invalid transaction amount';
        }
        return $response;
    }

    /**
     * Refund Locked Amount to Transactional Balance
     *
     * @param [type] $userId
     * @param [type] $serviceId
     * @param [type] $amount
     * @return void
     */
    public static function refundLockedAmountBK($orderId, $userId, $serviceId, $amount)
    {

        $response['status'] = false;
        $response['message'] = 'no record found';

        $orderData = Order::where('order_id', $orderId)
            ->where('trn_loc_refunded', '0')
            ->whereNull('trn_loc_refunded_at')
            ->where('trn_reflected', '0')
            ->whereNull('trn_reflected_at')
            ->first();

        if (!empty($orderData)) {
            $amount = $orderData->amount + $orderData->fee + $orderData->tax;
            DB::beginTransaction();
            $userService = UserService::where(['user_id' => $userId, 'service_id' => $serviceId])
                ->first();
            if (!empty($userService)) {
                $accNumber = $userService->service_account_number;
                $lockedAmount = $userService->locked_amount;
                $transAmount = $userService->transaction_amount;
                $userService->locked_amount = ($lockedAmount - (float) $amount);
                $userService->transaction_amount = ($transAmount + (float) $amount);
                $data = $userService->save();
                $arrayData = array(
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'ser_trn_amount' => $userService->transaction_amount,
                    'ser_loc_amount' => $userService->locked_amount,
                    'amount' => $amount,
                    'type' => 'refund_locked_amount',
                );
                self::isServiceWalletNegative($arrayData);
                if (!empty($data)) {
                    $orderData->trn_loc_refunded = '1';
                    $orderData->trn_loc_refunded_at = date('Y-m-d H:i:s');
                    $orderData->refund_count += 1;
                    $refUp = $orderData->save();
                    if (!empty($refUp)) {
                        $msg = self::refundTransHistory($userId, $orderId, $serviceId, $accNumber, $amount, $transAmount, $lockedAmount);
                    }
                    $response['status'] = true;
                    $response['message'] = 'Transaction amount refunded';
                    DB::commit();
                } else {
                    $response['status'] = false;
                    $response['message'] = 'DB action failed';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'no linked service account found';
            }
        }



        return $response;
    }
    public static function refundLockedAmount($orderId, $userId, $serviceId, $amount)
    {

        $response['status'] = false;
        $response['message'] = 'no record found';

        $orderData = Order::where('order_id', $orderId)
            ->where('trn_loc_refunded', '0')
            ->whereNull('trn_loc_refunded_at')
            ->where('trn_reflected', '0')
            ->whereNull('trn_reflected_at')
            ->first();

        return $response;
    }

    /** Debit Fund Transfer To Account */
    public static function internalTransfer($userId, $serviceId, $fromAccNum, $toAccNum, $amount, $remarks)
    {
        DB::beginTransaction();
        try {

            $user = User::where('id', $userId)
            ->first();

            $openingBalance = $user->locked_amount + $user->transaction_amount;
            $closingBalance = $openingBalance - $amount;
            $user->transaction_amount -= $amount;
            $user->save();

            $txn = CommonHelper::getRandomString('txn', false);
            // Debit Transfer Amount //
            $transaction = new Transaction;
            $transaction->trans_id = $txn;
            $transaction->user_id = $userId;
            $transaction->account_number = $fromAccNum;
            $transaction->tr_amount = $amount;
            $transaction->tr_date = date('Y-m-d H:i:s');
            $transaction->tr_type = 'dr';
            $transaction->tr_identifiers = 'internal_transfer';
            $transaction->tr_narration = $amount . ' Amount debited from main account ';
            $transaction->opening_balance = $openingBalance;
            $transaction->closing_balance = $closingBalance;
            $transaction->remarks = $remarks;
            $transaction->save();

            $userService = UserService::where('user_id', $userId)->where('service_id', $serviceId)->first();

            $userServiceOpeningBalance = $userService->locked_amount + $userService->transaction_amount;
            $userServiceClosingBalance = $userServiceOpeningBalance + $amount;

            $userService->transaction_amount += $amount;
            $userService->save();

            $arrayData = array(
                'user_id' => $userId,
                'order_id' => $txn,
                'amount' => $amount,
                'ser_trn_amount' => $userService->transaction_amount,
                'ser_loc_amount' => $userService->locked_amount,
                'type' => 'internal_transfer_dr',
            );
            self::isServiceWalletNegative($arrayData);
            // Credit Transfer Account
            $txn = CommonHelper::getRandomString('txn', false);
            $transaction = new Transaction;
            $transaction->trans_id = $txn;
            $transaction->user_id = $userId;
            $transaction->account_number = $toAccNum;
            $transaction->tr_amount = $amount;
            $transaction->tr_date = date('Y-m-d H:i:s');
            $transaction->tr_type = 'cr';
            $transaction->tr_identifiers = 'internal_transfer';
            $transaction->tr_narration = $amount . ' Amount credited to service account ';
            $transaction->opening_balance = $userServiceOpeningBalance;
            $transaction->closing_balance = $userServiceClosingBalance;
            $transaction->remarks = $remarks;
            $transaction->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

      // Cliam Back Transaction
    /** Cliam Back To Account */
    public static function claimBackTransfer($userId, $serviceId, $amount, $remarks, $claimBy)
    {
        DB::beginTransaction();
        $userService = UserService::where('user_id', $userId)->where('service_id', $serviceId)->first();
        $userServiceOpeningBalance = $userService->transaction_amount;
        $userServiceClosingBalance = $userServiceOpeningBalance - $amount;

        $userService->transaction_amount -= $amount;
        $userService->save();
        $fromAccNum = $userService->service_account_number;
        // Credit Transfer Account
        $txn = CommonHelper::getRandomString('txn', false);
        $transaction = new Transaction;
        $transaction->txn_id = $txn;
        $transaction->user_id = $userId;
        $transaction->account_number = $fromAccNum;
        $transaction->tr_amount = $amount;
        $transaction->tr_total_amount = '-'.number_format($amount, 2);
        $transaction->tr_date = date('Y-m-d H:i:s');
        $transaction->service_id = $serviceId;
        $transaction->tr_type = 'dr';
        $transaction->tr_identifiers = 'claim_back_transfer';
        $transaction->tr_narration = $amount . ' Amount debited from payout account ';
        $transaction->closing_balance = $userServiceClosingBalance;
        $transaction->remarks = $remarks;
        if($claimBy->is_admin == '1') {
            $transaction->udf4 = "admin:".$claimBy->id;
        }
        if($claimBy->is_admin == '0') {
            $transaction->udf4 = "user:".$claimBy->id;
        }
        $transaction->save();
        

        $user = User::where('id', $userId)
        ->first();

        $openingBalance = $user->transaction_amount;
        $closingBalance = $openingBalance + $amount;
        $user->transaction_amount += $amount;
        $user->save();
        $toAccNum = $user->account_number;
        $txn = CommonHelper::getRandomString('txn', false);
        // Debit Transfer Amount //
        $transaction = new Transaction;
        $transaction->txn_id = $txn;
        $transaction->user_id = $userId;
        $transaction->account_number = $toAccNum;
        $transaction->tr_amount = $amount;
        $transaction->tr_total_amount = '+'.number_format($amount, 2);
        $transaction->tr_date = date('Y-m-d H:i:s');
        $transaction->tr_type = 'cr';
        $transaction->tr_identifiers = 'claim_back_transfer';
        $transaction->tr_narration = $amount . ' Amount credited to primary account ';

        $transaction->closing_balance = $closingBalance;
        $transaction->remarks = $remarks;
        if($claimBy->is_admin == '1') {
            $transaction->udf4 = "admin:".$claimBy->id;
        }
        if($claimBy->is_admin == '0') {
            $transaction->udf4 = "user:".$claimBy->id;
        }
        $transaction->save();

        DB::commit();
    }
    /** Debit Fund Amount From Service Account To Credit to Main Account */
    public static function internalTransferToMainAcc($userId, $serviceId, $fromAccNum, $toAccNum, $amount, $remarks)
    {
        $user = User::where('id', $userId)->first();
        $userService = UserService::where('user_id', $userId)->where('service_id', $serviceId)->first();

        $openingBalance = $userService->transaction_amount;
        $closingBalance = $userService->transaction_amount - $amount;
        $userService->transaction_amount -= $amount;
        $userService->save();

        // Debit Transfer Amount //
        $txn = CommonHelper::getRandomString('txn', false);
        $transaction = new Transaction;
        $transaction->trans_id = $txn;
        $transaction->user_id = $userId;
        $transaction->account_number = $fromAccNum;
        $transaction->tr_amount = $amount;
        $transaction->tr_date = date('Y-m-d H:i:s');
        $transaction->tr_type = 'dr';
        $transaction->tr_identifiers = 'internal_transfer';
        $transaction->tr_narration = $amount . ' Amount debited from service account ';
        $transaction->opening_balance = $openingBalance;
        $transaction->closing_balance = $closingBalance;
        $transaction->remarks = $remarks;
        $transaction->save();

        // Credit Transfer Account
        $txn = CommonHelper::getRandomString('txn', false);
        $transaction = new Transaction;
        $transaction->trans_id = $txn;
        $transaction->user_id = $userId;
        $transaction->account_number = $toAccNum;
        $transaction->tr_amount = $amount;
        $transaction->tr_date = date('Y-m-d H:i:s');
        $transaction->tr_type = 'cr';
        $transaction->tr_identifiers = 'internal_transfer';
        $transaction->tr_narration = $amount . ' Amount credited to main account ';
        $transaction->opening_balance = $user->transaction_amount;
        $transaction->closing_balance = $user->transaction_amount + $amount;
        $transaction->remarks = $remarks;
        $transaction->save();

        $user->transaction_amount += $amount;
        $user->save();
    }



    /**
     * get Fee , Tax and Total Amount
     *
     * @param [string] $product_id
     * @param [float] $amount
     * @param [int] $userId
     * @param [string] $operation
     * @return void
     */
    public static function getFeesAndTaxes($product_id, $amount, $userId = null)
    {
        $response['amount'] = $amount;
        $response['fee'] = 0;
        $response['tax'] = 0;
        $response['margin'] = '';
        $response['total_amount'] = 0;
        $response['scheme'] = '';

        $globalConfig = DB::table('global_config')->select('attribute_1')
            ->where('slug', 'is_custom_fee_active')
            ->first();
        $is_custom_fee_active = empty($globalConfig)?'0':$globalConfig->attribute_1;


        //if userId not null, run query from custom scheme
        //else fetch products fee from regular scheme
        if ($userId !== null && $is_custom_fee_active === '1') {

            //check in amount range
            $fee = DB::table('user_config as uc')
                ->select('gpf.*', 'gp.tax_value')
                ->leftJoin('scheme_rules as gpf', 'gpf.scheme_id', 'uc.scheme_id')
                ->leftJoin('global_products as gp', 'gp.product_id', 'gpf.product_id')
                ->leftJoin('schemes', 'uc.scheme_id', 'schemes.id')
                ->where('gpf.product_id', $product_id)
                ->where('gpf.start_value', '<=', $amount)
                ->where('gpf.end_value', '>=', $amount)
                ->where('gpf.is_active', '1')
                ->where('schemes.is_active', '1')
                ->whereNotNull('uc.scheme_id')
                ->where('uc.user_id', $userId)
                ->first();

            if (empty($fee)) {
                $fee = DB::table('user_config as uc')
                    ->select('gpf.*', 'gp.tax_value')
                    ->leftJoin('scheme_rules as gpf', 'gpf.scheme_id', 'uc.scheme_id')
                    ->leftJoin('global_products as gp', 'gp.product_id', 'gpf.product_id')
                    ->leftJoin('schemes', 'uc.scheme_id', 'schemes.id')
                    ->where('gpf.product_id', $product_id)
                    ->whereNull('gpf.start_value')
                    ->whereNull('gpf.end_value')
                    ->where('gpf.is_active', '1')
                    ->where('schemes.is_active', '1')
                    ->whereNotNull('uc.scheme_id')
                    ->where('uc.user_id', $userId)
                    ->first();
            }

            $response['scheme'] = 'custom';
        }


        //fetch info from global
        if (empty($fee)) {
            $fee = DB::table('global_product_fees as gpf')
                ->select('gpf.*', 'gp.tax_value')
                ->leftJoin('global_products as gp', 'gp.product_id', 'gpf.product_id')
                ->where('gpf.product_id', $product_id)
                ->where('gpf.start_value', '<=', $amount)
                ->where('gpf.end_value', '>=', $amount)
                ->where('gpf.is_active', '1')
                ->first();

                $response['scheme'] = 'global';
        }

        if (empty($fee)) {

            $fee = DB::table('global_product_fees as gpf')
                ->select('gpf.*', 'gp.tax_value')
                ->leftJoin('global_products as gp', 'gp.product_id', 'gpf.product_id')
                ->where('gpf.product_id', $product_id)
                ->whereNull('gpf.start_value')
                ->whereNull('gpf.end_value')
                ->where('gpf.is_active', '1')
                ->first();

                $response['scheme'] = 'global';
        }


        if (!empty($fee)) {
            if ($fee->type == 'percent') {
                // $response['fee'] = round(((float) $amount) * $fee->fee / 100, 4, PHP_ROUND_HALF_EVEN);

                $calculatedFee = round(((float) $amount) * (float) $fee->fee / 100, 4, PHP_ROUND_HALF_EVEN);

                if((float) $calculatedFee < (float) $fee->min_fee && !empty($fee->min_fee)) {
                    $response['fee'] = (float) $fee->min_fee;
                    $response['margin'] =  'fixed' . '@' . $response['fee'];
                }
                elseif ((float) $calculatedFee > (float) $fee->max_fee && !empty($fee->max_fee)) {
                    $response['fee'] = (float) $fee->max_fee;
                    $response['margin'] =  'fixed' . '@' . $response['fee'];
                }
                else {
                    $response['fee'] = (float) $calculatedFee;
                    $response['margin'] = $fee->type . '@' . $fee->fee;
                }
            } else if ($fee->type == 'fixed') {
                $response['fee'] = $fee->fee;
                $response['margin'] = $fee->type . '@' . $fee->fee;
            }

            $fee->tax_value = !empty($fee->tax_value) ? $fee->tax_value : 18;

            $response['amount'] = $amount;
            $response['tax'] = round($response['fee'] * $fee->tax_value / 100, 4, PHP_ROUND_HALF_EVEN);
            // $response['margin'] = $fee->type . '@' . $fee->fee;
        }

        /**
         * Total Amount Calculation, based on the operation
         */
        // switch ($operation) {
        //     case '-':
        //         $response['total_amount'] = $response['amount'] - $response['fee'] - $response['tax'];
        //         break;

        //     case '+':
        //     default:
        //         $response['total_amount'] = $response['amount'] + $response['fee'] + $response['tax'];
        //         break;
        // }

        $response['total_amount'] = $response['amount'] + $response['fee'] + $response['tax'];

        return $response;
    }

    /**
     * get Fee , Tax and Total Amount
     *
     * @param [type] $product_id
     * @param [type] $amount
     * @return void
     */
    public static function getFeesAndTaxesOld($product_id, $amount)
    {
        $response['amount'] = $amount;
        $response['fee'] = 0;
        $response['tax'] = 0;
        $response['margin'] = '';
        $response['total_amount'] = 0;

        $fee = GlobalProductFee::where('product_id', $product_id)->where('start_value', '<=', $amount)->where('end_value', '>=', $amount)->where('is_active', '1')->first();

        if (!empty($fee)) {
            if ($fee->type == 'percent') {
                $calculatedFee = round(((float) $amount) * (float) $fee->fee / 100, 4, PHP_ROUND_HALF_EVEN);
                if((float) $calculatedFee < (float) $fee->min_fee && !empty($fee->min_fee)) {
                    $response['fee'] = (float) $fee->min_fee;
                    $response['margin'] =  'fixed' . '@' . $response['fee'];
                }
                elseif ((float) $calculatedFee > (float) $fee->max_fee && !empty($fee->max_fee)) {
                    $response['fee'] = (float) $fee->max_fee;
                    $response['margin'] =  'fixed' . '@' . $response['fee'];
                }
                else {
                    $response['fee'] = (float) $calculatedFee;
                }
                // $response['fee'] = round(((float) $amount) * $fee->fee / 100, 4, PHP_ROUND_HALF_EVEN);
                //$response['fee_2'] = $amount * $fee->fee / 100;
            }
            if ($fee->type == 'fixed') {
                $response['fee'] = $fee->fee;
            }

            $response['amount'] = $amount;
            $response['tax'] = round($response['fee'] * 18 / 100, 4, PHP_ROUND_HALF_EVEN);
            //$response['tax_2'] = $response['fee']*18/100;
            if(empty($response['margin'])){
                $response['margin'] = $fee->type . '@' . $fee->fee;
            }
            $response['total_amount'] = $response['amount'] + $response['fee'] + $response['tax'];
        } else {
            $fee = GlobalProductFee::where('product_id', $product_id)->whereNull('start_value')->whereNull('end_value')->where('is_active', '1')->first();
            
            if ($fee) {
                if ($fee->type == 'percent') {
                    $calculatedFee = round(((float) $amount) * (float) $fee->fee / 100, 4, PHP_ROUND_HALF_EVEN);
                    if((float) $calculatedFee < (float) $fee->min_fee && !empty($fee->min_fee)) {
                        $response['fee'] = (float) $fee->min_fee;
                        $response['margin'] =  'fixed' . '@' . $response['fee'];
                    }
                    elseif ((float) $calculatedFee > (float) $fee->max_fee && !empty($fee->max_fee)) {
                        $response['fee'] = (float) $fee->max_fee;
                        $response['margin'] =  'fixed' . '@' . $response['fee'];
                    }
                    else {
                        $response['fee'] = (float) $calculatedFee;
                    }
                    //$response['fee_2'] = $amount * $fee->fee / 100;
                }
                if ($fee->type == 'fixed') {
                    $response['fee'] = (float) $fee->fee;
                }

                $response['amount'] = $amount;
                $response['tax'] = round((float) $response['fee'] * 18 / 100, 4, PHP_ROUND_HALF_EVEN);
                //$response['tax_2'] = $response['fee']*18/100;
                if(empty($response['margin'])){
                    $response['margin'] = $fee->type . '@' . $fee->fee;
                }
                $response['total_amount'] = (float) $response['amount'] + (float) $response['fee'] + (float) $response['tax'];
            }
        }
        return $response;
    }

    /**
     * Insert Transaction
     *
     * @param [type] $orderId
     * @param [type] $orderRefId
     * @return void
     */
    public static function insertTransaction($orderId, $orderRefId)
    {
        $remarks = '';

        $orderInfo = Order::where('order_id', $orderId)
            ->where('order_ref_id', $orderRefId)
            ->where('trn_loc_refunded', '0')
            ->whereNull('trn_loc_refunded_at')
            ->where('trn_reflected', '0')
            ->whereNull('trn_reflected_at')
            ->first();

        if (!empty($orderInfo)) {
            $serviceId = $orderInfo['service_id'];
            $userId = $orderInfo['user_id'];
            $amount = (float) $orderInfo['amount'];
            $fee = (float) $orderInfo['fee'];
            $tax = (float) $orderInfo['tax'];

            $getServiceAcc = UserService::where('user_id', $userId)
                ->where('service_id', $serviceId)
                ->first();

            if (!empty($getServiceAcc)) {
                $systemPoolAccount = GlobalConfig::select('attribute_1')
                    ->where('slug', 'system_pool_account')
                    ->first();

                $systemFeeAccount = GlobalConfig::select('attribute_1')
                    ->where('slug', 'system_fee_account')
                    ->first();

                $systemTaxAccount = GlobalConfig::select('attribute_1')
                    ->where('slug', 'system_tax_account')
                    ->first();

                $poolAcc = User::where('account_number', $systemPoolAccount->attribute_1)
                    ->where('is_admin', '1')
                    ->first();

                $feeAcc = User::where('account_number', $systemFeeAccount->attribute_1)
                    ->where('is_admin', '1')
                    ->first();

                $taxAcc = User::where('account_number', $systemTaxAccount->attribute_1)
                    ->where('is_admin', '1')
                    ->first();

                $systemPoolAccount = $systemPoolAccount->attribute_1;
                $poolAccUserId = $poolAcc->id;
                $poolAccOpeningBalance = $poolAcc->locked_amount + $poolAcc->transaction_amount;

                $systemFeeAccount = $systemFeeAccount->attribute_1;
                $feeAccUserId = $feeAcc->id;
                $feeAccOpeningBalance = $feeAcc->locked_amount + $feeAcc->transaction_amount;

                $systemTaxAccount = $systemTaxAccount->attribute_1;
                $taxAccUserId = $taxAcc->id;
                $taxAccOpeningBalance = $taxAcc->locked_amount + $taxAcc->transaction_amount;

                $txn = CommonHelper::getRandomString('txn', false);
                $userOpeningBalance = $getServiceAcc->locked_amount + $getServiceAcc->transaction_amount;
                $userClosingBalance =  $userOpeningBalance - $amount;

                /* Order Amount DR Transaction */
                $ordTran = new Transaction;
                $ordTran->trans_id = $txn;
                $ordTran->user_id = $userId;
                $ordTran->service_id = $serviceId;
                $ordTran->order_id = $orderId;
                $ordTran->account_number = $getServiceAcc->service_account_number;
                $ordTran->tr_amount = $amount;
                $ordTran->tr_date = date('Y-m-d H:i:s');
                $ordTran->tr_type = 'dr';
                $ordTran->tr_identifiers = 'payout_disbursement';
                $ordTran->tr_narration = $amount . ' debited against ' . $orderId;
                $ordTran->opening_balance = $userOpeningBalance;
                $ordTran->closing_balance = $userClosingBalance;
                $ordTran->remarks = $remarks;
                $ordTran->save();

                $txn = CommonHelper::getRandomString('txn', false);
                $userOpeningBalance = $userClosingBalance;
                $userClosingBalance = $userOpeningBalance - $fee;


                /* Fee Amount DR Transaction */
                $feeTran = new Transaction;
                $feeTran->trans_id = $txn;
                $feeTran->user_id = $userId;
                $feeTran->service_id = $serviceId;
                $feeTran->order_id = $orderId;
                $feeTran->account_number = $getServiceAcc->service_account_number;
                $feeTran->tr_amount = $fee;
                $feeTran->tr_date = date('Y-m-d H:i:s');
                $feeTran->tr_type = 'dr';
                $feeTran->tr_identifiers = 'payout_fee';
                $feeTran->tr_narration = $fee . ' fee debited against ' . $orderId;
                $feeTran->opening_balance = $userOpeningBalance;
                $feeTran->closing_balance = $userClosingBalance;
                $feeTran->remarks = $remarks;
                $feeTran->save();

                $txn = CommonHelper::getRandomString('txn', false);
                $userOpeningBalance = $userClosingBalance;
                $userClosingBalance = $userOpeningBalance - $tax;
                /* Tax Amount DR Transaction */
                $taxTran = new Transaction;
                $taxTran->trans_id = $txn;
                $taxTran->user_id = $userId;
                $taxTran->service_id = $serviceId;
                $taxTran->order_id = $orderId;
                $taxTran->account_number = $getServiceAcc->service_account_number;
                $taxTran->tr_amount = $tax;
                $taxTran->tr_date = date('Y-m-d H:i:s');
                $taxTran->tr_type = 'dr';
                $taxTran->tr_identifiers = 'payout_fee_tax';
                $taxTran->tr_narration = $tax . ' tax debited against ' . $orderId;
                $taxTran->opening_balance = $userOpeningBalance;
                $taxTran->closing_balance = $userClosingBalance;
                $taxTran->remarks = $remarks;
                $taxTran->save();

                $getServiceAcc->locked_amount -= ($amount + $fee + $tax);
                $getServiceAcc->save();
                /**
                 * Is negative service wallet
                 */
                $arrayData = array(
                    'user_id' => $userId,
                    'order_id' => $txn,
                    'amount' => $amount + $fee + $tax,
                    'ser_trn_amount' => $getServiceAcc->transaction_amount,
                    'ser_loc_amount' => $getServiceAcc->locked_amount,
                    'type' => 'payout_fee_tax_dr'
                );
                self::isServiceWalletNegative($arrayData);
                 // end
                $txn = CommonHelper::getRandomString('txn', false);
                $poolAccOpeningBalance = $poolAcc->locked_amount + $poolAcc->transaction_amount;
                $poolAccClosingBalance = $poolAccOpeningBalance + $amount;
                /* Order Amount CR Transaction */
                $ordAmtTran = new Transaction;
                $ordAmtTran->trans_id = $txn;
                $ordAmtTran->user_id = $poolAccUserId;
                $ordAmtTran->service_id = $serviceId;
                $ordAmtTran->order_id = $orderId;
                $ordAmtTran->account_number = $systemPoolAccount;
                $ordAmtTran->tr_amount = $amount;
                $ordAmtTran->tr_date = date('Y-m-d H:i:s');
                $ordAmtTran->tr_type = 'cr';
                $ordAmtTran->tr_identifiers = 'payout_disbursement';
                $ordAmtTran->tr_narration = $amount . ' credited against ' . $orderId;
                $ordAmtTran->opening_balance = $poolAccOpeningBalance;
                $ordAmtTran->closing_balance = $poolAccClosingBalance;
                $ordAmtTran->remarks = $remarks;
                $ordAmtTran->save();

                $poolAcc->transaction_amount += $amount;
                $poolAcc->save();

                $txn = CommonHelper::getRandomString('txn', false);
                $feeAccOpeningBalance = $feeAcc->locked_amount + $feeAcc->transaction_amount;
                $feeAccClosingBalance = $feeAccOpeningBalance + $fee;
                /* Fee Amount Fee CR Transaction */
                $ordFeeTran = new Transaction;
                $ordFeeTran->trans_id = $txn;
                $ordFeeTran->user_id = $feeAccUserId;
                $ordFeeTran->service_id = $serviceId;
                $ordFeeTran->order_id = $orderId;
                $ordFeeTran->account_number = $systemFeeAccount;
                $ordFeeTran->tr_amount = $fee;
                $ordFeeTran->tr_date = date('Y-m-d H:i:s');
                $ordFeeTran->tr_type = 'cr';
                $ordFeeTran->tr_identifiers = 'payout_fee';
                $ordFeeTran->tr_narration = $fee . ' fee credited against ' . $orderId;
                $ordFeeTran->opening_balance = $feeAccOpeningBalance;
                $ordFeeTran->closing_balance = $feeAccClosingBalance;
                $ordFeeTran->remarks = $remarks;
                $ordFeeTran->save();

                $feeAcc->transaction_amount += $fee;
                $feeAcc->save();

                $txn = CommonHelper::getRandomString('txn', false);
                $taxAccOpeningBalance = $taxAcc->locked_amount + $taxAcc->transaction_amount;
                $taxAccClosingBalance = $taxAccOpeningBalance + $tax;
                /* Tax Amount CR Transaction */
                $ordTaxTran = new Transaction;
                $ordTaxTran->trans_id = $txn;
                $ordTaxTran->user_id = $taxAccUserId;
                $ordTaxTran->order_id = $orderId;
                $ordTaxTran->account_number = $systemTaxAccount;
                $ordTaxTran->service_id = $serviceId;
                $ordTaxTran->tr_amount = $tax;
                $ordTaxTran->tr_date = date('Y-m-d H:i:s');
                $ordTaxTran->tr_type = 'cr';
                $ordTaxTran->tr_identifiers = 'payout_fee_tax';
                $ordTaxTran->tr_narration = $tax . ' tax credited against ' . $orderId;
                $ordTaxTran->opening_balance = $taxAccOpeningBalance;
                $ordTaxTran->closing_balance = $taxAccClosingBalance;
                $ordTaxTran->remarks = $remarks;
                $ordTaxTran->save();

                $taxAcc->transaction_amount += $tax;
                $taxAcc->save();

                $orderInfo->trn_reflected = '1';
                $orderInfo->trn_reflected_at = date('Y-m-d H:i:s');
                $orderInfo->reflect_count += 1;
                $orderInfo->save();
            }
        }
    }

    /**
     * Reverse Transaction
     *
     * @param [type] $orderId
     * @param [type] $orderRefId
     * @return void
     */
    public static function reverseTrn($orderRefId)
    {
        $remarks = '';
        $resp['status'] = false;
        $resp['message'] = '';
        DB::beginTransaction();
        try {
            $orderInfo = Order::
                where('trn_reversed', '0')
                ->where('trn_reversed_at', null)
                ->where('order_ref_id', $orderRefId)->first();
                $serviceId = $orderInfo['service_id'];
                $userId = $orderInfo['user_id'];
                $amount = (float) $orderInfo['amount'];
                $fee = (float) $orderInfo['fee'];
                $tax = (float) $orderInfo['tax'];

                $getServiceAcc = UserService::where('user_id', $userId)->where('service_id', $serviceId)->first();
                    $systemPoolAccount = GlobalConfig::select('attribute_1')->where('slug', 'system_pool_account')->first();
                    $systemFeeAccount = GlobalConfig::select('attribute_1')->where('slug', 'system_fee_account')->first();
                    $systemTaxAccount = GlobalConfig::select('attribute_1')->where('slug', 'system_tax_account')->first();

                    $poolAcc = User::where('account_number', $systemPoolAccount->attribute_1)->where('is_admin', '1')->first();
                    $feeAcc = User::where('account_number', $systemFeeAccount->attribute_1)->where('is_admin', '1')->first();
                    $taxAcc = User::where('account_number', $systemTaxAccount->attribute_1)->where('is_admin', '1')->first();

                    $systemPoolAccount = $systemPoolAccount->attribute_1;
                    $poolAccUserId = $poolAcc->id;
                    $poolAccOpeningBalance = $poolAcc->locked_amount + $poolAcc->transaction_amount;

                    $systemFeeAccount = $systemFeeAccount->attribute_1;
                    $feeAccUserId = $feeAcc->id;
                    $feeAccOpeningBalance = $feeAcc->locked_amount + $feeAcc->transaction_amount;

                    $systemTaxAccount = $systemTaxAccount->attribute_1;
                    $taxAccUserId = $taxAcc->id;
                    $taxAccOpeningBalance = $taxAcc->locked_amount + $taxAcc->transaction_amount;

                    $txn = CommonHelper::getRandomString('txn', false);
                    $userOpeningBalance = $getServiceAcc->locked_amount + $getServiceAcc->transaction_amount;
                    $userClosingBalance =  $userOpeningBalance + $amount;
                    /* Order Amount DR Transaction */
                    $ordTran = new Transaction;
                    $ordTran->trans_id = $txn;
                    $ordTran->user_id = $userId;
                    $ordTran->service_id = $serviceId;
                    $ordTran->order_id = $orderRefId;
                    $ordTran->account_number = $getServiceAcc->service_account_number;
                    $ordTran->tr_amount = $amount;
                    $ordTran->tr_date = date('Y-m-d H:i:s');
                    $ordTran->tr_type = 'cr';
                    $ordTran->tr_identifiers = 'payout_disbursement_reversal';
                    $ordTran->tr_narration = $amount . ' credited against ' . $orderRefId;
                    $ordTran->opening_balance = $userOpeningBalance;
                    $ordTran->closing_balance = $userClosingBalance;
                    $ordTran->remarks = $remarks;
                    $ordTran->save();

                    $getServiceAcc->transaction_amount += $amount;
                    $getServiceAcc->save();

                    $txn = CommonHelper::getRandomString('txn', false);
                    $userOpeningBalance = $getServiceAcc->locked_amount + $getServiceAcc->transaction_amount;
                    $userClosingBalance = $userOpeningBalance + $fee;
                    /* Fee Amount DR Transaction */
                    $feeTran = new Transaction;
                    $feeTran->trans_id = $txn;
                    $feeTran->user_id = $userId;
                    $feeTran->service_id = $serviceId;
                    $feeTran->order_id = $orderRefId;
                    $feeTran->account_number = $getServiceAcc->service_account_number;
                    $feeTran->tr_amount = $fee;
                    $feeTran->tr_date = date('Y-m-d H:i:s');
                    $feeTran->tr_type = 'cr';
                    $feeTran->tr_identifiers = 'payout_fee_reversal';
                    $feeTran->tr_narration = $fee . ' fee credited against ' . $orderRefId;
                    $feeTran->opening_balance = $userOpeningBalance;
                    $feeTran->closing_balance = $userClosingBalance;
                    $feeTran->remarks = $remarks;
                    $feeTran->save();

                    $getServiceAcc->transaction_amount += $fee;
                    $getServiceAcc->save();

                    $txn = CommonHelper::getRandomString('txn', false);
                    $userOpeningBalance = $getServiceAcc->locked_amount + $getServiceAcc->transaction_amount;
                    $userClosingBalance = $userOpeningBalance + $tax;
                    /* Tax Amount DR Transaction */
                    $taxTran = new Transaction;
                    $taxTran->trans_id = $txn;
                    $taxTran->user_id = $userId;
                    $taxTran->service_id = $serviceId;
                    $taxTran->order_id = $orderRefId;
                    $taxTran->account_number = $getServiceAcc->service_account_number;
                    $taxTran->tr_amount = $tax;
                    $taxTran->tr_date = date('Y-m-d H:i:s');
                    $taxTran->tr_type = 'cr';
                    $taxTran->tr_identifiers = 'payout_fee_tax_reversal';
                    $taxTran->tr_narration = $tax . ' tax credited against ' . $orderRefId;
                    $taxTran->opening_balance = $userOpeningBalance;
                    $taxTran->closing_balance = $userClosingBalance;
                    $taxTran->remarks = $remarks;
                    $taxTran->save();

                    $getServiceAcc->transaction_amount += $tax;
                    $getServiceAcc->save();

                    $txn = CommonHelper::getRandomString('txn', false);
                    $poolAccOpeningBalance = $poolAcc->locked_amount + $poolAcc->transaction_amount;
                    $poolAccClosingBalance = $poolAccOpeningBalance - $amount;
                    /* Order Amount CR Transaction */
                    $ordAmtTran = new Transaction;
                    $ordAmtTran->trans_id = $txn;
                    $ordAmtTran->user_id = $poolAccUserId;
                    $ordAmtTran->service_id = $serviceId;
                    $ordAmtTran->order_id = $orderRefId;
                    $ordAmtTran->account_number = $systemPoolAccount;
                    $ordAmtTran->tr_amount = $amount;
                    $ordAmtTran->tr_date = date('Y-m-d H:i:s');
                    $ordAmtTran->tr_type = 'dr';
                    $ordAmtTran->tr_identifiers = 'payout_disbursement_reversal';
                    $ordAmtTran->tr_narration = $amount . ' debited against ' . $orderRefId;
                    $ordAmtTran->opening_balance = $poolAccOpeningBalance;
                    $ordAmtTran->closing_balance = $poolAccClosingBalance;
                    $ordAmtTran->remarks = $remarks;
                    $ordAmtTran->save();

                    $poolAcc->transaction_amount -= $amount;
                    $poolAcc->save();

                    $txn = CommonHelper::getRandomString('txn', false);
                    $feeAccOpeningBalance = $feeAcc->locked_amount + $feeAcc->transaction_amount;
                    $feeAccClosingBalance = $feeAccOpeningBalance - $fee;
                    /* Fee Amount Fee CR Transaction */
                    $ordFeeTran = new Transaction;
                    $ordFeeTran->trans_id = $txn;
                    $ordFeeTran->user_id = $feeAccUserId;
                    $ordFeeTran->service_id = $serviceId;
                    $ordFeeTran->order_id = $orderRefId;
                    $ordFeeTran->account_number = $systemFeeAccount;
                    $ordFeeTran->tr_amount = $fee;
                    $ordFeeTran->tr_date = date('Y-m-d H:i:s');
                    $ordFeeTran->tr_type = 'dr';
                    $ordFeeTran->tr_identifiers = 'payout_fee_reversal';
                    $ordFeeTran->tr_narration = $fee . ' fee debited against ' . $orderRefId;
                    $ordFeeTran->opening_balance = $feeAccOpeningBalance;
                    $ordFeeTran->closing_balance = $feeAccClosingBalance;
                    $ordFeeTran->remarks = $remarks;
                    $ordFeeTran->save();

                    $feeAcc->transaction_amount -= $fee;
                    $feeAcc->save();

                    $txn = CommonHelper::getRandomString('txn', false);
                    $taxAccOpeningBalance = $taxAcc->locked_amount + $taxAcc->transaction_amount;
                    $taxAccClosingBalance = $taxAccOpeningBalance - $tax;
                    /* Tax Amount CR Transaction */
                    $ordTaxTran = new Transaction;
                    $ordTaxTran->trans_id = $txn;
                    $ordTaxTran->user_id = $taxAccUserId;
                    $ordTaxTran->order_id = $orderRefId;
                    $ordTaxTran->account_number = $systemTaxAccount;
                    $ordTaxTran->service_id = $serviceId;
                    $ordTaxTran->tr_amount = $tax;
                    $ordTaxTran->tr_date = date('Y-m-d H:i:s');
                    $ordTaxTran->tr_type = 'dr';
                    $ordTaxTran->tr_identifiers = 'payout_fee_tax_reversal';
                    $ordTaxTran->tr_narration = $tax . ' tax debited against ' . $orderRefId;
                    $ordTaxTran->opening_balance = $taxAccOpeningBalance;
                    $ordTaxTran->closing_balance = $taxAccClosingBalance;
                    $ordTaxTran->remarks = $remarks;
                    $ordTaxTran->save();

                    $taxAcc->transaction_amount -= $tax;
                    $taxAcc->save();

                    $orderInfo->trn_reversed = '1';
                    $orderInfo->trn_reversed_at = date('Y-m-d H:i:s');
                    $orderInfo->save();
                    DB::commit();
                    $resp['status'] = true;
                    $resp['message'] = 'Reversed successfully.';
        } catch (Exception $e) {
            DB::rollback();
            $resp['message'] = 'Some errors. '.$e->getMessage();
        }
        return $resp;
    }

    /**
     * Create Contact
     *
     * @param array $contactArray
     * @param [type] $userId
     * @return void
     */
    public static function createContact($contactArray = [], $userId)
    {
        $contact = new Contact;
        $contact->contact_id = CommonHelper::getRandomString('cont');
        $contact->user_id = $userId;
        $contact->first_name = $contactArray['contact_first_name'];
        $contact->last_name = $contactArray['contact_last_name'];
        $contact->email = $contactArray['contact_email'];
        $contact->phone = $contactArray['contact_phone'];
        $contact->type = $contactArray['contact_type'];
        $contact->account_type = $contactArray['account_type'];

        if ($contactArray['account_type'] == 'bank_account') {
            $contact->account_number = $contactArray['account_number'];
            $contact->account_ifsc = $contactArray['account_ifsc'];
        } elseif ($contactArray['account_type'] == 'vpa') {
            $contact->vpa_address = $contactArray['account_vpa'];
        } else {
            $contact->card_number = $contactArray['card_no'];
        }

        $contact->address1 = isset($contactArray['address1']) ? $contactArray['address1'] : 'NA';
        $contact->address2 = isset($contactArray['address2']) ? $contactArray['address2'] : 'NA';
        $contact->city = isset($contactArray['city']) ? $contactArray['city'] : 'NA';
        $contact->state = isset($contactArray['state']) ? $contactArray['state'] : 'NA';
        $contact->pincode = isset($contactArray['pincode']) ? $contactArray['pincode'] : 'NA';
        $contact->reference = $contactArray['reference'];
        $contact->is_active = '1';
        $data = $contact->save();
        if ($data) {
            $response['status'] = true;
            $response['message'] = 'Contact created';
            $response['data'] = $contact;
        } else {
            $response['status'] = false;
            $response['message'] = 'DB action failed';
        }
        return $response;
    }

    /**
     * Create Order
     *
     * @param array $orderArray
     * @param [type] $userId
     * @param [type] $contactId
     * @param [type] $productId
     * @param [type] $integrationId
     * @param [type] $payout_reference_id
     * @return void
     */
    public static function createOrder($orderArray = [], $userId, $contactId, $productId, $integrationId, $payout_reference_id, $serviceId)
    {

        $order = new Order;
        $order->contact_id = $contactId;
        $order->product_id = $productId;
        $order->service_id = $serviceId;
        $order->user_id = $userId;
        $order->client_ref_id = $orderArray['payout_reference_id'];
        $order->batch_id = $orderArray['batch_id'] ? $orderArray['batch_id'] : 'NA';
        $order->order_ref_id = $payout_reference_id;
        $order->currency = 'INR';
        $order->amount = $orderArray['payout_amount'];
        $order->fee = $orderArray['fee'];
        $order->tax = $orderArray['tax'];
        $order->mode = $orderArray['payout_mode'];
        $order->purpose = $orderArray['payout_purpose'];
        $order->narration = $orderArray['payout_narration'];
        $order->remark =  isset($orderArray['payout_remark']) ? $orderArray['payout_remark'] : 'NA';
        $order->txt_3 = $orderArray['margin'];
        $order->ip = $orderArray['agent']['ip'];
        $order->area =  $orderArray['agent']['area'];
        $order->user_agent =  $orderArray['agent']['userAgent'];
        $order->status = 'hold';
        $data = $order->save();
        if ($data) {
            $response['status'] = true;
            $response['message'] = 'Order created';
            $response['data'] = $order;
        } else {
            $response['status'] = false;
            $response['message'] = 'DB action failed';
        }
        return $response;
    }

    public static function createTransactionAndOrder($orderRefId, $userId, $serviceId, $productId, $orderArray)
    {
        $response['status'] = false;
        $response['message'] = 'Order Not created';
        DB::beginTransaction();
        try {
            $totalAmount = $orderArray['charges']['amount'] + $orderArray['charges']['fee'] + $orderArray['charges']['tax'];
            $PayoutRate = DB::table('reseller_commission')->where('user_id', $userId)->select('payout_rate')->first();
            $payoutFinalAmount = $totalAmount * ($PayoutRate->payout_rate / 100);
            // dd($payoutFinalAmount);
            $amountPositiveInt = self::intPositive($totalAmount);
            if ($amountPositiveInt['status']) {
                if (empty($orderArray['remark'])) {
                    $businessInfo = DB::table('business_infos')->where('user_id', $userId)->first();
                    if (!empty($businessInfo) && !empty($businessInfo->business_name)) {
                        $remarksData = "Fund Transfer";
                    } else {
                        $remarksData = "Fund Transfer";
                    } 
                } else {
                    $remarksData = $orderArray['remark'];
                }
                    // Transaction Create
                   $orderData = [
                        'contact_id' => $orderArray['accountNo'],
                        'product_id' => $productId,
                        'service_id' => $serviceId,
                        'client_ref_id' => $orderArray['clientRefId'],
                        'narration' =>  isset($orderArray['narration']) ? $orderArray['narration'] : "",
                        'user_id' => $userId,
                        'order_ref_id' => $orderRefId,
                        'currency' => 'INR',
                        'integration_id' => 'int_1702712555',
                        'amount' => $orderArray['charges']['amount'],
                        'fee' => $orderArray['charges']['fee'],
                        'tax' => $orderArray['charges']['tax'],
                        'mode' => CommonHelper::case($orderArray['mode'], 'u'),
                        'purpose' => CommonHelper::case($orderArray['purpose'], 'u'),
                        'remark' => $remarksData,
                        'mode' => CommonHelper::case($orderArray['mode'], 'u'),
                        'txt_3' => $orderArray['charges']['margin'],
                        'ip' => $orderArray['agent']['ip'],
                        'area' => '11',
                        'reseller_commision' => $payoutFinalAmount, 
                        'user_agent' => $orderArray['agent']['userAgent'],
                        'status' => '1'
                   ];
                   if (isset($orderArray['udf1'])) {
                        $orderData = array_merge($orderData, ['udf1' => $orderArray['udf1']]);
                   }
                   if (isset($orderArray['udf2'])) {
                    $orderData = array_merge($orderData, ['udf2' => $orderArray['udf2']]);
                   }
                   
                $createTransaction = DB::table('orders')->insert($orderData);
                if ($createTransaction) {
                    DB::commit();
                    $response['status'] = true;
                    $response['message'] = 'Order created successfully.';
                    $response['dataorder'] = $orderData;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Order not created.';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Invalid transaction amount';
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            $response['status'] = false;
            $response['message'] = 'something went wrong : '.$e->getMessage() ;
        }
        return $response;
    }
    /**
     * Check Amount is Postive
     *
     * @param [type] $num
     * @return void
     */
    public static function intPositive($num)
    {
        if ($num < 0) {
            $response['status'] = false;
            $response['message'] = 'Negative integer value';
        } else {
            $response['status'] = true;
            $response['message'] = 'Positive integer value';
        }
        return $response;
    }

    public static function getProductConfig($mode, $serviceId)
    {
        $product = Product::where('slug', $mode)->where('service_id', $serviceId)->where('is_active', '1')->first();
        if ($product) {
            $response['status'] = true;
            $response['message'] = 'Product available for transactions';
            $response['data'] = array(
                'product_id' => $product->product_id,
                'name' => $product->name, 'slug' => $product->slug, 'min_order_value' => $product->min_order_value, 'max_order_value' => $product->max_order_value, 'type' => $product->type, 'is_fee_enabled' => $product->is_fee_enabled, 'is_tax_enabled' => $product->is_tax_enabled
            );
        } else {
            $response['status'] = false;
            $response['message'] = 'Product not found or inactive';
        }
        return $response;
    }
    
    public static function moveOrderToProcessingByOrderIdBulkPayout($orderId, $integrationId = null)
    {
        $order = Order::where('order_id', $orderId)->first();
        if ($order) {
            if ($order['cron_status'] === '0') {
                $orderUpdate = Order::where('order_id', $orderId)->update(['status' => 'processing', 'integration_id' => $integrationId, 'cron_status' => '1']);
                if ($orderUpdate) {
                    $response['status'] = true;
                    $response['message'] = 'Order updated successfully';
                } else {
                    $response['status'] = false;
                    $response['message'] = 'DB action failed';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'already processed';
            }
        }
        return $response;
    }

    public static function moveOrderToProcessingByOrderId($userId, $orderRefId, $integrationId = null)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {
            $txn = CommonHelper::getRandomString('txn', false);
            DB::select("CALL debitPayoutBalanceOrder($userId, '".$orderRefId."', '".$integrationId."', '".$txn."', @json)");
            $results = DB::select('select @json as json');
            $response = json_decode($results[0]->json, true);
            if($response['status'] == '1') {
                $resp['status'] = true;
                $resp['message'] = 'Order processing successfully.';
            } else {
                $resp['status'] = false;
                $resp['message'] = $response['message'];
            }
        } catch (\Exception $e) {
            $resp['status'] = false;
            $resp['message'] = 'Some errors : '.$e->getMessage();
        }
        return $resp;
    }

    public static function inwardCredit($trnData = [], $trnType)
    {
        $productService = Service::leftJoin('global_products', 'global_services.service_id', '=', 'global_products.service_id')
            ->where('global_services.service_slug', $trnType)->first();

        $serviceId = $productService->service_id;
        $productId = $productService->product_id;

        $bankTxnId = $trnData['bank_txn_id'];
        $customerRefId = $trnData['customer_ref_id'];

        $npciTxnId = isset($trnData['npci_txn_id']) ? $trnData['npci_txn_id'] : '';
        $originalOrderId = isset($trnData['original_order_id']) ? $trnData['original_order_id'] : '';
        $merchantTxnRefId = isset($trnData['merchant_txn_ref_id']) ? $trnData['merchant_txn_ref_id'] : '';
        $payerVpa = isset($trnData['payer_vpa']) ? $trnData['payer_vpa'] : '';
        $payerAccName = isset($trnData['payer_acc_name']) ? $trnData['payer_acc_name'] : '';

        $remarks = '';
        DB::beginTransaction();
        try {
            if(isset($trnData['is_upi_collect']) && $trnData['is_upi_collect']) {
                $upiTrnInfo = UPICollect::where([['customer_ref_id', $customerRefId], ['bank_txn_id', $bankTxnId], ['is_trn_credited', '0']])
                ->whereNull('trn_credited_at')->first();
            }else {
                $upiTrnInfo = UPICallback::where([['customer_ref_id', $customerRefId], ['bank_txn_id', $bankTxnId], ['is_trn_credited', '0']])
                    ->whereNull('trn_credited_at')->first();
            }
            if ($upiTrnInfo) {
                $userId = $upiTrnInfo['user_id'];
                $amount = $upiTrnInfo['amount'];

                $getUserAcc = User::where('id', $userId)->first();

                $systemPoolAccount = GlobalConfig::select('attribute_1')->where('slug', 'system_pool_account')->first();

                $poolAcc = User::where('account_number', $systemPoolAccount->attribute_1)->where('is_admin', '1')->first();

                $systemPoolAccount = $systemPoolAccount->attribute_1;
                $poolAccUserId = $poolAcc->id;
                $poolAccOpeningBalance = $poolAcc->locked_amount + $poolAcc->transaction_amount;

                //inward transaction credit to poolAccount
                $txn = CommonHelper::getRandomString('txn', false);
                $poolAccOpeningBalance = $poolAcc->locked_amount + $poolAcc->transaction_amount;
                $poolAccClosingBalance = $poolAccOpeningBalance + $amount;
                /* Order Amount CR Transaction */
                $ordAmtCrTran = new Transaction;
                $ordAmtCrTran->trans_id = $txn;
                $ordAmtCrTran->user_id = $poolAccUserId;
                $ordAmtCrTran->service_id = $serviceId;
                $ordAmtCrTran->order_id = $customerRefId;
                $ordAmtCrTran->account_number = $systemPoolAccount;
                $ordAmtCrTran->tr_amount = $amount;
                $ordAmtCrTran->tr_date = date('Y-m-d H:i:s');
                $ordAmtCrTran->tr_type = 'cr';
                $ordAmtCrTran->tr_identifiers = 'upi_inward';
                $ordAmtCrTran->tr_narration = $amount . ' credited against ' . $customerRefId;
                $ordAmtCrTran->opening_balance = $poolAccOpeningBalance;
                $ordAmtCrTran->closing_balance = $poolAccClosingBalance;
                $ordAmtCrTran->remarks = $remarks;
                $ordAmtCrTran->tr_reference = $merchantTxnRefId;
                $ordAmtCrTran->udf1 = $npciTxnId;
                $ordAmtCrTran->udf2 = $originalOrderId;
                $ordAmtCrTran->udf3 = $payerVpa;
                $ordAmtCrTran->udf4 = $payerAccName;
                $ordAmtCrTran->save();

                $poolAcc->transaction_amount += $amount;
                $poolAcc->save();


                //inward transaction debit from pool account
                $txn = CommonHelper::getRandomString('txn', false);
                $poolAccOpeningBalance = $poolAcc->locked_amount + $poolAcc->transaction_amount;
                $poolAccClosingBalance = $poolAccOpeningBalance - $amount;

                $ordAmtDrTran = new Transaction;
                $ordAmtDrTran->trans_id = $txn;
                $ordAmtDrTran->user_id = $poolAccUserId;
                $ordAmtDrTran->service_id = $serviceId;
                $ordAmtDrTran->order_id = $customerRefId;
                $ordAmtDrTran->account_number = $systemPoolAccount;
                $ordAmtDrTran->tr_amount = $amount;
                $ordAmtDrTran->tr_date = date('Y-m-d H:i:s');
                $ordAmtDrTran->tr_type = 'dr';
                $ordAmtDrTran->tr_identifiers = 'upi_inward_credit';
                $ordAmtDrTran->tr_narration = $amount . ' debited against ' . $customerRefId;
                $ordAmtDrTran->opening_balance = $poolAccOpeningBalance;
                $ordAmtDrTran->closing_balance = $poolAccClosingBalance;
                $ordAmtDrTran->remarks = $remarks;
                $ordAmtDrTran->tr_reference = $merchantTxnRefId;
                $ordAmtDrTran->udf1 = $npciTxnId;
                $ordAmtDrTran->udf2 = $originalOrderId;
                $ordAmtDrTran->udf3 = $payerVpa;
                $ordAmtDrTran->udf4 = $payerAccName;
                $ordAmtDrTran->save();

                $poolAcc->transaction_amount -= $amount;
                $poolAcc->save();

                //inward transaction credit to user account
                $txn = CommonHelper::getRandomString('txn', false);
                $userOpeningBalance = $getUserAcc->locked_amount + $getUserAcc->transaction_amount;
                $userClosingBalance =  $userOpeningBalance + $amount;

                $ordTran = new Transaction;
                $ordTran->trans_id = $txn;
                $ordTran->user_id = $userId;
                $ordTran->service_id = $serviceId;
                $ordTran->order_id = $customerRefId;
                $ordTran->account_number = $getUserAcc->account_number;
                $ordTran->tr_amount = $amount;
                $ordTran->tr_date = date('Y-m-d H:i:s');
                $ordTran->tr_type = 'cr';
                $ordTran->tr_identifiers = 'upi_inward_credit';
                $ordTran->tr_narration = $amount . ' credited against ' . $customerRefId;
                $ordTran->opening_balance = $userOpeningBalance;
                $ordTran->closing_balance = $userClosingBalance;
                $ordTran->remarks = $remarks;
                $ordTran->tr_reference = $merchantTxnRefId;
                $ordTran->udf1 = $npciTxnId;
                $ordTran->udf2 = $originalOrderId;
                $ordTran->udf3 = $payerVpa;
                $ordTran->udf4 = $payerAccName;
                $ordTran->save();

                $getUserAcc->transaction_amount += $amount;
                $getUserAcc->save();


                $upiTrnInfo->is_trn_credited = '1';
                $upiTrnInfo->trn_credited_at = date('Y-m-d H:i:s');
                $upiTrnInfo->save();
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }

    /**
     * Refund Transaction History History function
     *
     * @param [type] $userId
     * @param [type] $orderId
     * @param [type] $serviceId
     * @param [type] $accountNo
     * @param [type] $refundAmt
     * @param [type] $transactionAmt
     * @param [type] $lockedAmt
     * @return void
     */
    public static function refundTransHistory($userId, $orderId, $serviceId, $accountNo, $refundAmt, $transactionAmt, $lockedAmt)
    {
        $data = array(
            "user_id" => isset($userId) ? $userId : null,
            "order_id" => isset($orderId) ? $orderId : null,
            "service_id" => isset($serviceId) ? $serviceId : null,
            "account_number" => isset($accountNo) ? $accountNo : null,
            "refund_amount" => isset($refundAmt) ? $refundAmt : null,
            "transaction_amount" => isset($transactionAmt) ? $transactionAmt : null,
            "locked_amount" => isset($lockedAmt) ? $lockedAmt : null,
        );
        $refundTrans = RefundTransaction::create($data);
        $resp = [];
        if ($refundTrans) {
            $resp['status'] = true;
            $resp['message'] = 'Refund transaction created successfully';
        } else {
            $resp['status'] = false;
            $resp['message'] = 'DB action failed';
        }
        return $resp;
    }

    public static function sendCallback ($userId, $orderRefId, $status)
    {
         //send callback
         $getWebhooks = Webhook::where('user_id', $userId)->first();
         if ($getWebhooks) {
             $order = Order::where('order_ref_id', $orderRefId)->first();
             $url = $getWebhooks['webhook_url'];
             $secret = $getWebhooks['secret'];
             if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
                 $headers = [$getWebhooks['header_key'] => $getWebhooks['header_value']];
                if($status == 'processed') {
                    WebhookHelper::PayoutSuccess($order, $url, $secret, $headers);
                } else if($status == 'failed') {
                    WebhookHelper::PayoutFailed($order, $url, $secret, $headers);
                } else if($status == 'reversed') {
                    WebhookHelper::PayoutReverse($order, $url, $secret, $headers);
                }
             } else {
                if($status == 'processed') {
                    WebhookHelper::PayoutSuccess($order, $url, $secret);
                }else if($status == 'failed') {
                    WebhookHelper::PayoutFailed($order, $url, $secret);
                } else if($status == 'reversed') {
                    WebhookHelper::PayoutReverse($order, $url, $secret);
                }
             }
         }
         // end
    }

    public static function isServiceWalletNegative($data)
    {
        try {
            $is_service_trn_minus = '0';
            $is_service_locked_minus = '0';
            if(isset($data['ser_trn_amount'])) {
                if ($data['ser_trn_amount'] < 0) {
                    $is_service_trn_minus =  '1';
                }
            }
            if(isset($data['ser_loc_amount'])) {
                if ($data['ser_loc_amount'] < 0) {
                    $is_service_locked_minus =  '1';
                }
            }
            $array = array(
                'user_id' => isset($data['user_id']) ? $data['user_id'] : 1,
                'order_id' => isset($data['order_id']) ? $data['order_id'] : '',
                'amount' => isset($data['amount']) ? $data['amount'] : '',
                'service_transactions_amount' => isset($data['ser_trn_amount']) ? $data['ser_trn_amount'] : ' ',
                'service_locked_amount' => isset($data['ser_loc_amount']) ? $data['ser_loc_amount'] : ' ',
                'is_service_trn_minus' => isset($is_service_trn_minus) ? $is_service_trn_minus : '0',
                'is_service_locked_minus' => isset($is_service_locked_minus) ? $is_service_locked_minus : '0',
                'type' => isset($data['type']) ? $data['type'] : '0'
            );
            DB::table('users_service_amount_minues')->insert($array);
        } catch (\Throwable $th) {
            //throw $th;
        }
        
    }



    /**
     * Credit transaction for Load Request Money
     * When request approved by admin
     */
    public static function txnForLoadMoneyRequest($data)
    {
        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('txn_ref_id', $data->request_id)
            ->count();

        if ($isTransactions > 0) {
            return "Transaction already credit";
        }

        //getting Product ID
        $products = CommonHelper::getProductId('load_money', 'load_money');

        //fee and tax on fee calculation
        $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $data->amount, $data->user_id);
        $feeRate = $taxFee->margin;
        $fee = round($taxFee->fee, 2);
        $tax = round($taxFee->tax, 2);

        $totAmount = $data->amount - $fee - $tax;

        $txnTotalAmount = ($totAmount >= 0) ? '+' . $totAmount : $totAmount;

        $txnNarration = $totAmount . ' credited against ' . $data->utr;
        $txnId = CommonHelper::getRandomString('txn', false);

        // DB::select("CALL LoadMoneyFundCreditTransaction('$products->service_id', $data->id, '$txnId', '$txnTotalAmount', $data->amount, $taxFee->fee, $taxFee->tax, '$txnNarration', '$data->remarks', $data->admin_id, @outData)");
        DB::select("CALL LoadMoneyFundCreditTxnJob($data->user_id, '$products->service_id', $data->id, '$txnId', '$txnTotalAmount', $data->amount, $fee, $tax, '$txnNarration', '$data->remarks', $data->admin_id, '$feeRate', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }
}
