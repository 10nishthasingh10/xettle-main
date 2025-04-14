<?php

namespace App\Helpers;

use App\Jobs\PrimaryFundCredit;
use App\Jobs\SendTransactionEmailJob;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CashfreeAutoCollectHelper
{

    private $key;
    private $secret;
    private $baseUrl;
    private $token;


    /**
     * construct function init Client Key,Client Secret and Base Url
     */
    public function __construct()
    {
        $this->baseUrl = env('CASHFREE_AC_URL');
        $this->key = base64_decode(env('CASHFREE_AC_KEY'));
        $this->secret = base64_decode(env('CASHFREE_AC_SECRET'));
        $this->token = Storage::get('tokens/cashfree_auto_collect_token.txt');
    }


    public function authorize($userId = 1)
    {

        // $request = [];
        $header = array(
            'X-Client-Id: ' . $this->key,
            'X-Client-Secret: ' . $this->secret,
            'Content-Type: application/json',
        );

        $result = CommonHelper::curl(
            $this->baseUrl . '/cac/v1/authorize',
            "POST",
            json_encode([]),
            $header,
            'yes',
            $userId,
            'CashfreeAutoCollect',
            'authorized'
        );

        $response = json_decode($result['response']);

        if (!empty($response->subCode)) {
            if ($response->subCode == '200')
                Storage::put('tokens/cashfree_auto_collect_token.txt', $response->data->token);
            // if ($response->subCode == '200') {
            //     $this->token = $response->data->token;
            // }
        }

        return $response;
    }



    public function vanManager($params, $url, $userId = 0, $request = "POST", $method = "createVan", $modal = "CashfreeAutoCollect")
    {

        $header = array(
            'Content-Type: application/json',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            "Authorization: Bearer {$this->token}"
        );


        $result = CommonHelper::curl(
            $this->baseUrl . $url,
            $request,
            json_encode($params),
            $header,
            'yes',
            $userId,
            $modal,
            $method
        );


        return $result;
    }



    /**
     * Veryfi VAN Callback Signature
     */
    public function verifySignature($params)
    {

        if (empty($params["signature"])) {
            return false;
        }

        $data = $params;
        $signature = $params["signature"];
        unset($data["signature"]);

        // $data now has all the POST parameters except signature
        ksort($data);  // Sort the $data array based on keys

        $postData = "";

        // dd($data);

        foreach ($data as $key => $value) {
            if (strlen($value) > 0) {
                $postData .= $value;
            }
        }

        $hash_hmac = hash_hmac('sha256', $postData, $this->secret, true);

        // Use the clientSecret from the oldest active Key Pair.
        $computedSignature = base64_encode($hash_hmac);

        // dd($computedSignature);

        if ($signature == $computedSignature) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Handle CF callback response VAN
     * comes through VAN API users
     */
    public static function handleCfAutoCollectCredit($cfMerchants, $callbackData)
    {
        try {
            $dataUtr = isset($callbackData['utr']) ? $callbackData['utr'] : '';

            //check for already transaction by UTR
            $checkUTR = DB::table('cf_merchants_fund_callbacks')
                ->where('utr', $dataUtr)
                ->first();


            if (!empty($checkUTR)) {
                $res['status'] = 'FAILURE';
                $res['message'] = 'Transaction Already Credited.';
                $res['time'] = date('Y-m-d H:i:s');
                return response()->json($res);
            }


            $amount = $callbackData['amount'];
            $userId = $cfMerchants->user_id;
            $txnType = 'smart_collect';

            if (!empty($callbackData['isVpa'])) {
                $isVpa = '1';
                $refId = 'UT_' . $callbackData['utr'];
                $slug = 'vpa_collect';


                //getting service ID
                $products = CommonHelper::getProductId($slug, $txnType);

                //fee and tax on fee calculation
                $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $userId);

                $feeRate = $taxFee->margin;
                $fee = round($taxFee->fee, 2);
                $tax = round($taxFee->tax, 2);
                $crAmount = $amount - $fee - $tax;

                //generate Batch ID for UPI callback transaction
                $batchId = 'UPISC' . $userId . 'ST' . (date("YmdH") . '0' . (int)((date('i') / 30)));

                //store callback response
                $vanData = [
                    'batch_id' => $batchId,
                    'user_id' => $userId,
                    'amount' => $amount,
                    'fee' => $fee,
                    'tax' => $tax,
                    'cr_amount' => $crAmount,
                    'fee_rate' => $feeRate,
                    'ref_no' => $refId,
                    'utr' => $callbackData['utr'],
                    'v_account_id' => $callbackData['vAccountId'],
                    'virtual_vpa_id' => (empty($callbackData['virtualVpaId']) ? '' : $callbackData['virtualVpaId']),
                    'is_vpa' => $isVpa,
                    'v_account_number' => (empty($callbackData['vAccountNumber']) ? '' : $callbackData['vAccountNumber']),
                    'reference_id' => $callbackData['referenceId'],
                    'email' => $callbackData['email'],
                    'phone' => $callbackData['phone'],
                    'credit_ref_no' => (empty($callbackData['creditRefNo']) ? '' : $callbackData['creditRefNo']),
                    'remitter_account' => (empty($callbackData['remitterAccount']) ? '' : $callbackData['remitterAccount']),
                    'remitter_ifsc' => (empty($callbackData['remitterIfsc']) ? '' : $callbackData['remitterIfsc']),
                    'remitter_name' => (empty($callbackData['remitterName']) ? '' : $callbackData['remitterName']),
                    'remitter_vpa' => (empty($callbackData['remitterVpa']) ? '' : $callbackData['remitterVpa']),
                    'transfer_type' => (empty($callbackData['transferType']) ? '' : $callbackData['transferType']),
                    'remarks' => (empty($callbackData['remarks']) ? '' : $callbackData['remarks']),
                    'payment_time' => $callbackData['paymentTime'],
                    'is_trn_credited' => '0',
                    'is_trn_settle' => '0',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $vanData['rowId'] = DB::table('cf_merchants_fund_callbacks')->insertGetId($vanData);

                //check service is enable or not
                $isServiceActive = CommonHelper::checkIsServiceActive('smart_collect', $userId);

                //check callack is enable or not
                $isCallbackActive = CommonHelper::checkIsCallbackActive($userId, 'smart_collect', 'upi');

                if ($isServiceActive && $isCallbackActive) {

                    $getWebhooks = DB::table('webhooks')
                        ->where('user_id', $userId)
                        ->first();

                    if (!empty($getWebhooks)) {
                        $url = $getWebhooks->webhook_url;
                        $secret = $getWebhooks->secret;

                        if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                            $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                            WebhookHelper::autoCollectSuccess((object) $vanData, $url, $secret, $headers);
                        } else {
                            WebhookHelper::autoCollectSuccess((object) $vanData, $url, $secret);
                        }
                    }
                }

                $res['status'] = true;
                $res['message'] = 'Request captured successfully';
                $res['data'] = $callbackData;

                return response()->json($res);
            } else {
                $isVpa = '0';
                $refId = 'BT_' . $callbackData['utr'];
                $slug = 'van_collect';


                //getting priduct service ID
                //getting Product ID
                $products = CommonHelper::getProductId($slug, $txnType);

                //fee and tax on fee calculation
                $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $userId);
                $feeRate = $taxFee->margin;
                $fee = round($taxFee->fee, 2);
                $tax = round($taxFee->tax, 2);
                $crAmount = $amount - $fee - $tax;

                //store callback response
                $vanData = [
                    'user_id' => $userId,
                    'amount' => $amount,
                    'fee' => $fee,
                    'tax' => $tax,
                    'cr_amount' => $crAmount,
                    'fee_rate' => $feeRate,
                    'ref_no' => $refId,
                    'utr' => $dataUtr,
                    'v_account_id' => $callbackData['vAccountId'],
                    'virtual_vpa_id' => (empty($callbackData['virtualVpaId']) ? '' : $callbackData['virtualVpaId']),
                    'is_vpa' => $isVpa,
                    'v_account_number' => (empty($callbackData['vAccountNumber']) ? '' : $callbackData['vAccountNumber']),
                    'reference_id' => $callbackData['referenceId'],
                    'email' => $callbackData['email'],
                    'phone' => $callbackData['phone'],
                    'credit_ref_no' => (empty($callbackData['creditRefNo']) ? '' : $callbackData['creditRefNo']),
                    'remitter_account' => (empty($callbackData['remitterAccount']) ? '' : $callbackData['remitterAccount']),
                    'remitter_ifsc' => (empty($callbackData['remitterIfsc']) ? '' : $callbackData['remitterIfsc']),
                    'remitter_name' => (empty($callbackData['remitterName']) ? '' : $callbackData['remitterName']),
                    'remitter_vpa' => (empty($callbackData['remitterVpa']) ? '' : $callbackData['remitterVpa']),
                    'transfer_type' => (empty($callbackData['transferType']) ? '' : $callbackData['transferType']),
                    'remarks' => (empty($callbackData['remarks']) ? '' : $callbackData['remarks']),
                    'payment_time' => $callbackData['paymentTime'],
                    'is_trn_credited' => '0',
                    'created_at' => date('Y-m-d H:i:s')
                ];


                $vanData['rowId'] = DB::table('cf_merchants_fund_callbacks')->insertGetId($vanData);
                // $vanData['trnType'] = 'van_collect';


                //check service is enable or not
                $isServiceActive = CommonHelper::checkIsServiceActive('smart_collect', $userId);

                if ($isServiceActive) {

                    //check callack is enable or not
                    $isCallbackActive = CommonHelper::checkIsCallbackActive($userId, 'smart_collect', 'van');

                    if ($isCallbackActive) {

                        $getWebhooks = DB::table('webhooks')
                            ->where('user_id', $userId)
                            ->first();

                        if (!empty($getWebhooks)) {
                            $url = $getWebhooks->webhook_url;
                            $secret = $getWebhooks->secret;

                            if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                WebhookHelper::autoCollectSuccess((object) $vanData, $url, $secret, $headers);
                            } else {
                                WebhookHelper::autoCollectSuccess((object) $vanData, $url, $secret);
                            }
                        }
                    }

                    //if service is active, settlement credit
                    $isSettlementActive = CommonHelper::checkIsSettlementActive($userId, 'smart_collect', 'van');

                    if ($isSettlementActive) {
                        $vanData['frequency'] = 'instant';
                        PrimaryFundCredit::dispatch((object) $vanData, 'smart_collect_credit')->onQueue('primary_fund_queue');
                    }
                }

                $res['status'] = true;
                $res['message'] = 'Request captured successfully';
                $res['data'] = $callbackData;

                return response()->json($res);
            }


            $res['status'] = 'FAILURE';
            $res['message'] = 'Unexpected response received';
            $res['time'] = date('Y-m-d H:i:s');

            return response()->json($res);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * Amount credited into user primary wallet
     * When fund comes through VAN WEB User
     * Function used by Jobs
     * VAN Amount Credit Transfer Transactions
     */
    public static function vanCreditTxn($data)
    {

        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('txn_ref_id', $data->ref_no)->count();

        if ($isTransactions > 0) {
            return "Transaction already credited";
        }

        $rowId = $data->rowId;
        $txnId = CommonHelper::getRandomString('txn', false);
        $txnReferenceId = $data->ref_no;

        //getting priduct service ID
        //getting Product ID
        $products = CommonHelper::getProductId($data->trnType, $data->trnType);

        $txnTotalAmount = ($data->cr_amount >= 0) ? '+' . $data->cr_amount : $data->cr_amount;
        $txnNarration = $data->cr_amount . ' credited against ' . $data->utr;

        // DB::select("CALL VanFundCreditTransaction($rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', $data->amount, $taxFee->fee, $taxFee->tax, '$txnNarration', @outData)");
        DB::select("CALL PartnerVanCreditTxnJob($data->user_id, $rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', $data->amount, $data->fee, $data->tax, '$txnNarration', '$products->service_id', '$data->fee_rate', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);


        //check transaction email is enable or not
        $isEnabled = CommonHelper::checkIsServiceActive('txn_settlement_email', $data->user_id);

        if (!empty($response->email) && $isEnabled === true) {

            try {
                $mailParms = [
                    'email' => $response->email,
                    'name' => $response->name,
                    'amount' => $data->amount,
                    'transfer_date' => $response->date,
                    'acc_number' => $response->account_number,
                    'ref_number' => $txnId
                ];

                dispatch(new SendTransactionEmailJob((object) $mailParms, 'vanCredit'));
            } catch (Exception $e) {
                //mail not send
                //Storage::prepend('van_cr_mail.txt', print_r(['date' => date('Y-m-d H:i:s'), 'msg' => $e->getMessage(), 'line' => $e->getLine()], true));
            }
        }

        return $response->status;
    }



    /**
     * Amount credited into user primary wallet
     * When fund comes through VAN API
     * Function used by Jobs
     */
    public static function autoCollectApiCreditTxn($data)
    {

        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('txn_ref_id', $data->ref_no)->count();

        if ($isTransactions > 0) {
            return "Transaction already credited";
        }

        $rowId = $data->rowId;
        $txnId = !empty($data->txnId) ? $data->txnId : CommonHelper::getRandomString('txn', false);
        $txnReferenceId = $data->ref_no;

        $txnType = 'smart_collect';

        if (!empty($data->is_vpa)) {
            $identifire = 'smart_collect_vpa';
            $slug = 'vpa_collect';
        } else {
            $identifire = 'smart_collect_van';
            $slug = 'van_collect';
        }


        //getting priduct service ID
        //getting Product ID
        $products = CommonHelper::getProductId($slug, $txnType);
        $serviceId = $products->service_id;

        if (!empty($data->fee) && !empty($data->tax)) {
            $feeRate = $data->fee_rate;

            $fee = $data->fee;
            $tax = $data->tax;

            $txnTotalAmount = ($data->cr_amount >= 0) ? '+' . $data->cr_amount : $data->cr_amount;
            $txnNarration = $data->cr_amount . ' credited against ' . $data->utr;
        } else {
            $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $data->amount, $data->user_id);
            $feeRate = $taxFee->margin;

            $fee = round($taxFee->fee, 2);
            $tax = round($taxFee->tax, 2);

            $totAmount = $data->amount - $fee - $tax;

            $txnTotalAmount = ($totAmount >= 0) ? '+' . $totAmount : $totAmount;
            $txnNarration = $txnTotalAmount . ' credited against ' . $data->utr;
        }


        // DB::select("CALL AutoCollectCreditTransaction($rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', $data->amount, $fee, $tax, '$txnNarration', '$identifire', '$serviceId', @outData)");
        DB::select("CALL SmartCollectCreditTxnJob($data->user_id, $rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', $data->amount, $fee, $tax, '$txnNarration', '$identifire', '$serviceId', '$feeRate', '$data->frequency', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }



    /**
     * Settle Smart Collect UPI Amount
     * Credit amount to user's primary account
     */
    public static function smartCollectSettle2Primary($trnData)
    {

        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('txn_ref_id', $trnData->batch_id)
            ->where('tr_reference', $trnData->timestamp)
            ->count();

        if ($isTransactions > 0) {
            return "Amounts already settled";
        }

        // $rowId = $trnData->id;
        $txnId = $trnData->txn_id; //CommonHelper::getRandomString('txn', false);
        // $txnReferenceId = $trnData->batch_id;

        // $txnType = 'smart_collect';
        // $identifire = 'smart_collect_vpa';
        // $slug = 'vpa_collect';


        //getting service ID
        $products = CommonHelper::getProductId('vpa_collect', 'smart_collect');
        $serviceId = $products->service_id;

        //fee and tax on fee calculation
        // $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $trnData->total_amount, $trnData->user_id);
        // $feeRate = $taxFee->margin;

        // $fee = round($taxFee->fee, 2);
        // $tax = round($taxFee->tax, 2);
        // $feeRate = '';
        $fee = $trnData->total_fee;
        $tax = $trnData->total_tax;

        $afterFeeTaxAmount = $trnData->total_cr_amount;

        $signedAfterFeeTaxAmount = ($afterFeeTaxAmount >= 0) ? '+' . $afterFeeTaxAmount : $afterFeeTaxAmount;
        $txnNarration = $signedAfterFeeTaxAmount . ' credited to Primary Wallet.';


        DB::select("CALL SmartCollectUpiFundSettleTxnJob('$trnData->timestamp', $trnData->user_id, '$trnData->batch_id', '$trnData->counts', '$serviceId', '$txnId', '$signedAfterFeeTaxAmount', $afterFeeTaxAmount, $trnData->total_amount, $fee, $tax, '$txnNarration', '$trnData->frequency', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }




    /**
     * Amount debited from user primary wallet
     * When VPA or VAN created using API
     * Function used by Jobs
     */
    public static function autoCollectApiDebitTxn($data)
    {
        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('txn_ref_id', $data->requestId)->count();

        if ($isTransactions > 0) {
            return "Transaction already debited";
        }

        $rowId = $data->rowId;
        $txnId = CommonHelper::getRandomString('txn', false);
        $identifier = $data->identifier;
        $requestId = $data->requestId;
        $txnType = $data->txnType;
        $slug = $data->slug;


        //getting priduct service ID
        //getting Product ID
        $products = CommonHelper::getProductId($slug, $txnType);
        $serviceId = $products->service_id;

        //fee and tax on fee calculation
        $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, 1, $data->user_id);
        $feeRate = $taxFee->margin;
        $fee = round($taxFee->fee, 2);
        $tax = round($taxFee->tax, 2);

        $totAmount = 0 - $fee - $tax;

        $txnTotalAmount = ($totAmount >= 0) ? '+' . $totAmount : $totAmount;

        $txnNarration = ($fee + $tax) . ' debited against ' . $requestId;

        // DB::select("CALL AutoCollectDebitTransaction($rowId, '$txnId', '$requestId', '$txnTotalAmount', 0, $taxFee->fee, $taxFee->tax, '$txnNarration', '$identifier', 'dr', '$serviceId', @outData)");
        DB::select("CALL SmartCollectDebitTxnJob($data->user_id, $rowId, '$txnId', '$requestId', '$txnTotalAmount', 0, $fee, $tax, '$txnNarration', '$identifier', 'dr', '$serviceId', '$feeRate', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }


    /**
     * Reversed Amount of UPI Stack,
     * Debit amount to user's primary account
     */
    public static function smartCollectDisputedTxn($trnData)
    {
        //getting transaction info
        if (!empty($trnData->batch_id)) {
            $utrTxn = DB::table('transactions')->select('id', 'txn_id', 'tr_amount', 'service_id')
                ->where('user_id', $trnData->user_id)
                ->where('txn_ref_id', $trnData->batch_id)
                ->where('txn_id', $trnData->txn_id)
                ->first();
        } else {
            $utrTxn = DB::table('transactions')->select('id', 'txn_id', 'tr_amount', 'service_id')
                ->where('user_id', $trnData->user_id)
                ->where('txn_ref_id', $trnData->ref_no)
                ->first();
        }



        if (empty($utrTxn)) {
            return 'Invalid Transaction.';
        }

        $txnId = CommonHelper::getRandomString('txn', false);
        if (!empty($trnData->is_vpa)) {
            $identifire = 'smart_collect_vpa_dispute';
            $txnRefId = 'RUT_' . $trnData->utr;
        } else {
            $identifire = 'smart_collect_van_dispute';
            $txnRefId = 'RBT_' . $trnData->utr;
        }


        // $rvTxnAmount = $utrTxn->tr_amount - $utrTxn->tr_fee - $utrTxn->tr_tax;
        $txnNarration = $trnData->amount . ' debited against disputed UTR ' . $trnData->utr;
        $adminId = "ADMIN::" . $trnData->admin_id;


        DB::select("CALL SmartCollectDisputeTxnJob($trnData->user_id, $trnData->id, '$txnId', '$txnRefId', '$trnData->utr', $trnData->amount, $trnData->amount, 0, 0, '$txnNarration', '$utrTxn->service_id', '$utrTxn->txn_id', '$adminId', '$identifire', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }
}
