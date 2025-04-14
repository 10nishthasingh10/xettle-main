<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AutoSettlementHelper
{

    private  $fileName, $userId;
    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->fileName = 'public/AutoSettlement.txt';
    }

      /**
     * Execute the job.
     *
     * @return void
     */
    public function autoSettlement()
    {
        $fileNames = 'public/AutoSettlements'.$this->userId. time().'.txt';
        try {
            $userService = DB::table('user_config')
            ->select('user_config.threshold')
            ->where(['user_config.user_id' => $this->userId])
            ->first();
            $businessInfo = DB::table('user_bank_infos')
                        ->select('beneficiary_name', 'ifsc', 'account_number')
                        ->where('is_verified', '1')
                        ->where('is_active', '1')
                        ->where('is_primary', '1')
                        ->where('user_id',  $this->userId)
                        ->first();
                if (isset($userService) && !empty($userService) && !empty($businessInfo)) {
                    $user =  DB::table('users')
                        ->select('transaction_amount')
                        ->where('is_active', '1')
                        ->where(['id' => $this->userId])
                        ->where('transaction_amount', '>', $userService->threshold)->first();
                    if (isset($user) && !empty($user)) {
                        $userWallet = intval($user->transaction_amount);
                        $amount = ($userWallet - $userService->threshold);
                        // Get Product
                        $getProductId = CommonHelper::getProductId('auto_settlement', 'auto_settlement');
                        $productId = @$getProductId->product_id;
                        $status = false;
                        if (isset($getProductId) && !empty($getProductId)) {
                        if ($getProductId->min_order_value <= $amount && $amount <= $getProductId->max_order_value) {
                            $amount = $amount;
                            $status = true;
                        } else if ($getProductId->max_order_value <= $amount) {
                            $amount = $getProductId->max_order_value;
                            $status = true;
                        }
                        }
                        // End Product
                         // mode get
                         $mode = 'imps';
                        if ($amount > 200000) {
                            $mode = 'rtgs';
                        }
                         // end
                        $userConfigGetRoute = CommonHelper::getPayoutRouteUsingUserId($this->userId, 'api');
                        if ($userConfigGetRoute['status']) {
                            $integrationId = $userConfigGetRoute['integration_id'];
                        } else {
                            $route = CommonHelper::defaultPayoutRoute('api_payout_route');
                            $integrationId = $route['integration_id'];
                        }

                        $getProductConfig = TransactionHelper::getProductConfig('auto_settlement', AUTO_SETTLEMENT_SERVICE_ID);
                        if ($getProductConfig['status'] && $status == true) {

                            if ($getProductConfig['data']['min_order_value'] <= $amount && $getProductConfig['data']['max_order_value'] >= $amount) {
                            $txnRefId = CommonHelper::getRandomString('SETTREF', false);
                           
                            $txnId = CommonHelper::getRandomString('SETTTXN', false);
                            $txn = CommonHelper::getRandomString('STTID', false);
                            $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, $amount, $this->userId);
                            $checkRecordInserted = self::storeUserSettlement($this->userId, $amount, $getFeesAndTaxes['fee'], $getFeesAndTaxes['tax'], AUTO_SETTLEMENT_SERVICE_ID,  $mode, $txnRefId, $txn, $txnId, $integrationId, $userService->threshold);
                            }
                        }
                    }
                }

        } catch (\Exception  $e) {
            Storage::disk('local')->put($fileNames, $e . time());
        }
    }



    public function storeUserSettlement($userId, $amount, $fee, $tax, $serviceId, $mode, $settlementRefId, $settlementId, $txnId, $integrationId, $threshold)
    {

        $resp['status'] = false;
        $resp['message'] = "Record initiated";
        try {
            $businessInfo = DB::table('user_bank_infos')
                ->select('beneficiary_name', 'ifsc', 'account_number')
                ->where('is_verified', '1')
                ->where('is_active', '1')
                ->where('is_primary', '1')
                ->where('user_id', $userId)
                ->first();
                $isInserted1 = false;
                $isInserted2 = false;
            
            if (isset($businessInfo) && !empty($businessInfo)) {

                $finalAmount = $amount - ($fee + $tax);
                $isInserted1 = DB::table('user_settlements')->insert(
                    [
                        'user_id' => $userId,
                        'settlement_ref_id' => $settlementRefId,
                        'amount' => $finalAmount,
                        'fee' => $fee,
                        'tax' => $tax,
                        'account_number' => @$businessInfo->account_number,
                        'account_ifsc' => @$businessInfo->ifsc,
                        'beneficiary_name' => @$businessInfo->beneficiary_name,
                        'status' => 'initiate',
                        'mode' => $mode
                    ]
                );
                $isInserted2 = DB::table('user_settlement_logs')->insert(
                    [
                        'user_id' => $userId,
                        'settlement_ref_id' => $settlementRefId,
                        'settlement_txn_id' => $settlementId,
                        'amount' => $finalAmount,
                        'service_id' => $serviceId,
                        'integration_id' => $integrationId,
                        'mode' => $mode,
                        'status' => 'initiate'
                    ]
                );
            }

            if ($isInserted1 && $isInserted2) {
                DB::select("CALL debitSettlementBalanceOrder($userId, '" . $settlementRefId . "', '" . $settlementId . "', '" . $integrationId . "',  '" . $serviceId . "', '" . $txnId . "', '" . $threshold . "', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);
                if ($response['status'] == '1') {
                    dispatch(new \App\Jobs\SettlementJobs( $userId, $settlementId))->onQueue('autosettlement_order_queue')->delay(Carbon::now()->addSeconds(30));
                }
                $resp['status'] = true;
                $resp['message'] = "Record inserted";
            } else {
                $resp['status'] = false;
                $resp['message'] = "Record not inserted";
            }
        } catch (\Exception $e) {
            Storage::disk('local')->put($this->fileName, $e . time());
            $resp['status'] = false;
            $resp['message'] = "Record not inserted";
        }
        return $resp;
    }
}
