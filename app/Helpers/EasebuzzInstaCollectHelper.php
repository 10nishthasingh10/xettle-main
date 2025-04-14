<?php

namespace App\Helpers;

use App\Jobs\PrimaryFundCredit;
use App\Jobs\SendTransactionEmailJob;
use App\Models\Apilog;
use Exception;
use Illuminate\Support\Facades\DB;

class EasebuzzInstaCollectHelper
{
    private $key;
    private $salt;
    private $baseUrl;

    const VAN_PREFIX = '11';

    public function __construct()
    {
        $this->baseUrl = env('EASEBUZZ_BASE_URL') . '/api/v1';
        // $this->baseUrl = 'http://127.0.0.1:8000/api/upload'; //'http://localhost/cb/api/v1';
        // $this->baseUrl = 'http://localhost/cb/api/v1';
        $this->key = base64_decode(env('EASEBUZZ_KEY'));
        $this->salt = base64_decode(env('EASEBUZZ_SALT'));
    }


    public function getKey()
    {
        return $this->key;
    }


    public function auth(array $args)
    {
        $keys = '|';

        if (count($args) > 0) {
            foreach ($args as $v) {
                $keys .= "{$v}|";
            }
        }

        $str = "{$this->key}{$keys}{$this->salt}";

        return hash('sha512', $str);
    }



    public function apiCaller($params, $url, $authArr = [], $userId = 0, $requestType = "POST", $method = "generateVan", $modal = "EasebuzzInstaCollect")
    {

        $header = array(
            'Content-Type: application/json',
            'Accept' => 'application/json',
            "Authorization: {$this->auth($authArr)}",
            "WIRE-API-KEY: {$this->key}"
        );

        $result = CommonHelper::curl(
            $this->baseUrl . $url,
            $requestType,
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
     * Submit KYC docs to Ebuzz for partner VAN
     */
    public function uploadKycDocs($params, $url, $authArr = [], $userId = 0, $requestType = "POST", $method = "generateVan", $modal = "EasebuzzInstaCollect")
    {
        $ch = curl_init();

        $header = array(
            "Content-Type: multipart/form-data",
            // 'Accept' => 'application/json',
            "Authorization: {$this->auth($authArr)}",
            "WIRE-API-KEY: {$this->key}"
        );


        @curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        @curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $url);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = @curl_exec($ch);
        $err = @curl_error($ch);
        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        @curl_close($ch);

        Apilog::create([
            "user_id" => $userId,
            "integration_id" => 1,
            "product_id" => 1,
            "url" => $this->baseUrl . $url,
            "txnid" => '',
            "modal" => $modal,
            "method" =>  $method,
            "header" => json_encode($header),
            "request" => json_encode($params),
            "response" => $response,
        ]);

        return ["response" => $response, "error" => $err, 'code' => $code];
    }


    /**
     * Handle EB Instant Collect Callback
     */
    public static function handleVanCallbackCredit($merchant, $callbackData)
    {
        if (empty($callbackData['unique_transaction_reference'])) {
            $res['status'] = 'FAILURE';
            $res['message'] = 'UTR is missing.';
            $res['time'] = date('Y-m-d H:i:s');
            return response()->json($res);
        }

        $paymentStatus = @$callbackData['status'];

        if ($paymentStatus === 'unsettled' || $paymentStatus === 'received') {

            $utr = trim($callbackData['unique_transaction_reference']);

            //check for already transaction by UTR
            $checkUTR = DB::table('fund_receive_callbacks')
                ->where('root_type', 'eb_van')
                ->where('utr', $utr)
                ->first();

            if (!empty($checkUTR)) {
                $res['status'] = 'FAILURE';
                $res['message'] = 'Transaction Already Credited.';
                $res['time'] = date('Y-m-d H:i:s');
                return response()->json($res);
            }


            $refId = 'EBT_' . $utr;

            //getting priduct service ID
            //getting Product ID
            $products = CommonHelper::getProductId('van_collect', 'van_collect');

            //fee and tax on fee calculation
            $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $callbackData['amount'], $merchant->user_id);
            $feeRate = $taxFee->margin;
            $fee = round($taxFee->fee, 2);
            $tax = round($taxFee->tax, 2);
            $crAmount = ($callbackData['amount'] - $fee - $tax);

            //store callback response
            $vanData = [
                'root_type' => 'eb_van',
                'user_id' => $merchant->user_id,
                'ref_no' => $refId,
                'utr' => $utr,
                'amount' => $callbackData['amount'],
                'fee' => $fee,
                'tax' => $tax,
                'cr_amount' => $crAmount,
                'fee_rate' => $feeRate,
                'reference_id' => $callbackData['id'],
                'v_account_id' => $callbackData['virtual_account']['id'],
                // 'label' => $callbackData['virtual_account']['label'],
                'v_account_number' => $callbackData['virtual_account']['virtual_account_number'],
                // 'virtual_ifsc' => $callbackData['virtual_account']['virtual_ifsc_number'],
                'remitter_name' => $callbackData['remitter_full_name'],
                'remitter_account' => $callbackData['remitter_account_number'],
                'remitter_ifsc' => $callbackData['remitter_account_ifsc'],
                'transfer_type' => @$callbackData['payment_mode'],
                // 'service_charge' => @$callbackData['service_charge'],
                // 'gst_amount' => @$callbackData['gst_amount'],
                // 'service_charge_with_gst' => @$callbackData['service_charge_with_gst'],
                'remarks' => $callbackData['narration'],
                // 'status' => $callbackData['status'],
                'payment_time' => $callbackData['transaction_date'],
                'is_trn_credited' => '0',
                'created_at' => date('Y-m-d H:i:s')
            ];

            try {

                $vanData['rowId'] = DB::table('fund_receive_callbacks')->insertGetId($vanData);

                //check service is enable or not
                $isServiceActive = CommonHelper::checkIsServiceActive('eb_partner_van', $merchant->user_id);

                if ($isServiceActive) {
                    PrimaryFundCredit::dispatch((object) $vanData, 'partner_van_eb_credit')->onQueue('primary_fund_queue');
                }

                $res['status'] = true;
                $res['message'] = 'Request captured successfully';
                $res['data'] = $callbackData;

                return response()->json($res);
            } catch (Exception $e) {
                //inward credit error
                $res['status'] = 'FAILURE';
                $res['message'] = 'Credit Error: ' . $e->getMessage();
                return response()->json($res);
            }
        } else {
            $res['status'] = 'FAILURE';
            $res['message'] = 'Status is ' . $paymentStatus;
            $res['time'] = date('Y-m-d H:i:s');

            return response()->json($res);
        }


        $res['status'] = 'FAILURE';
        $res['message'] = 'Unexpected response received';
        $res['time'] = date('Y-m-d H:i:s');
        return response()->json($res);
    }


    /**
     * Handle VAN KYC status callback
     */
    public static function handleVanKycStatusCallback($callbackData)
    {
        $logInsertId = $callbackData['logId'];
        $callbackData = $callbackData['data'];

        $virtualAccount = $callbackData['virtual_account_number'];

        //select account information
        $accountInfo = DB::table('user_van_accounts')
            ->select('account_number', 'root_type', 'user_id', 'kyc_status')
            ->where('account_number', $virtualAccount)
            ->where('root_type', 'eb_van')
            ->first();

        if (empty($accountInfo)) {
            $res['status'] = 'FAILURE';
            $res['message'] = 'Invalid virtual account number.';
            $res['time'] = date('Y-m-d H:i:s');

            self::updateResponseApiLog($logInsertId, $res);

            return response()->json($res);
        }


        if ($accountInfo->kyc_status === '1') {
            $res['status'] = 'FAILURE';
            $res['message'] = 'KYC activated already.';
            $res['time'] = date('Y-m-d H:i:s');

            self::updateResponseApiLog($logInsertId, $res);

            return response()->json($res);
        }

        if ($accountInfo->kyc_status != '2') {
            $res['status'] = 'FAILURE';
            $res['message'] = 'KYC Docs not updated yet.';
            $res['time'] = date('Y-m-d H:i:s');

            self::updateResponseApiLog($logInsertId, $res);

            return response()->json($res);
        }

        $kycStatusDb = 0;

        if ($callbackData['kyc_flow']) {
            $kycStatus = $callbackData['kyc_status'];

            if ($kycStatus === 'rejected') {
                $kycStatusDb = '3';
            } else if ($kycStatus === 'approved') {
                $kycStatusDb = '1';
            } else {
                $kycStatusDb = '2';
            }

            DB::table('user_van_accounts')
                ->where('account_number', $virtualAccount)
                ->where('root_type', 'eb_van')
                // ->where('kyc_status', '2')
                ->update(
                    [
                        'status' => '1',
                        'kyc_status' => $kycStatusDb,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                );

            $res['status'] = 'SUCCESS';
            $res['message'] = 'Request captured successfully';
            $res['time'] = date('Y-m-d H:i:s');

            self::updateResponseApiLog($logInsertId, $res);

            return response()->json($res);
        }


        $res['status'] = 'FAILURE';
        $res['message'] = 'kyc_flow is not true.';
        $res['time'] = date('Y-m-d H:i:s');

        self::updateResponseApiLog($logInsertId, $res);

        return response()->json($res);
    }



    /**
     * Amount credited into user primary wallet
     * When fund comes through VAN API
     * Function used by Jobs
     */
    public static function ebInstaCollectCreditTxnJob($data)
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
        $identifire = !empty($data->identifire) ? $data->identifire : 'eb_van_inward_credit';


        //getting priduct service ID
        //getting Product ID
        $products = CommonHelper::getProductId('van_collect', 'van_collect');

        $trTotalAmountSigned = ($data->cr_amount >= 0) ? '+' . $data->cr_amount : $data->cr_amount;
        $txnNarration = $data->cr_amount . ' credited against ' . $data->utr;

        DB::select("CALL EbPartnerVanCreditTxnJob($data->user_id, $rowId, '$txnId', '$txnReferenceId', '$data->utr', '$trTotalAmountSigned', $data->amount, $data->fee, $data->tax, $data->cr_amount, '$txnNarration', '$products->service_id', '$data->fee_rate', '$identifire', @outData)");

        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);


        if (!empty($response->email)) {
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



    public static function getBusinessTypeCode($businessType)
    {
        $businessType = strtolower($businessType);
        $businessTypeCode = 0;

        switch ($businessType) {
            case 'proprietorship':
                //41 Proprietor
                $businessTypeCode = 41;
                break;

            case 'partnership':
                //2 Partnership
                $businessTypeCode = 2;
                break;

            case 'public limited':
                //4 Govt/ Govt Undertakings
                $businessTypeCode = 4;
                break;

            case 'private limited':
                //3 Companies registered under AcT
                $businessTypeCode = 3;
                break;

            case 'llp':
                // 45 LLPs
                $businessTypeCode = 45;
                break;

            case 'ngo':
            case 'society':
            case 'trust':
                // 44 Regd Trusts
                $businessTypeCode = 44;
                break;

            case 'not registered':
                //1 Individual- HUF
                $businessTypeCode = 1;
                break;

            default:
                $businessTypeCode = 1;
                break;
        }

        return strval($businessTypeCode);
    }



    private static function updateResponseApiLog($logId, $response)
    {
        DB::table('apilogs')
            ->where('id', $logId)
            ->update([
                'resp_message' => json_encode($response)
            ]);
    }
}
