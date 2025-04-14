<?php

namespace App\Helpers;

use App\Models\MApiLog;
use Illuminate\Support\Facades\DB;

class IBLUpiHelper
{
    private $key;
    private $secret;
    private $baseUrl;
    private $header;
    private $pgMerchantId;


    public function __construct()
    {
        $this->baseUrl = env('IBL_BASE_URL');
        $this->url = env('IBL_URL');
        $this->key = base64_decode(env('IBL_CLIENT_ID'));
        $this->secret = base64_decode(env('IBL_CLIENT_SECRET'));
        $this->decr_key = base64_decode(env('IBL_DECR_KEY'));
        $this->pgMerchantId = base64_decode(env('IBL_PG_MERCHANT_ID'));
        $this->header = ["Content-Type: application/json", "X-IBM-Client-Id: " . $this->key, "X-IBM-Client-Secret: " . $this->secret];
    }


    public function getMerchantId()
    {
        return $this->pgMerchantId;
    }


    public function getTxnStatus($key)
    {
        $arr = [
            'S' => 'success',
            'F' => 'failure',
            'P' => 'pending',
            'V' => 'validation error',
            'T' => 'timeout',
        ];

        return isset($arr[$key]) ? $arr[$key] : '';
    }



    public function convertRequestResponse($params, $userId, $requestType, $modal, $reqType)
    {
        $paramsForLog = $params;
        $result = $this->encryptUPIdata($params, $userId);
        if ($result['statuscode'] == '000') {
            $params = [
                "pgMerchantId" => $this->pgMerchantId,
                "requestMsg" => $result['text']
            ];

            $response = $this->upiCaller($params, $requestType, $userId, $modal, $reqType, $paramsForLog);
            $apiLogLastId = $response['apiLogLastId'];

            if ($reqType == 'transaction_refund') {
                $response['response'] = (['resp' => $response['response']['apiResp']]);
            }

            if (isset($response['response']['resp'])) {
                $dec_response = $this->decryptUPIdata($response['response']['resp'], $userId);

                MApiLog::updateLog($apiLogLastId, [
                        'response' => json_encode($dec_response)
                    ]);
            } else {
                $dec_response = $response;
            }

            return $dec_response;
        }
    }



    private function encryptUPIdata($data, $userId)
    {
        $url = 'http://45.249.111.172/XettleUPI/Indusind/GetEncryptUPIdata';
        $string = (json_encode($data));
        $request = [
            'message' => $string,
            'decr_key' => $this->decr_key
        ];
        $modal = '';
        $reqType = '';
        $result = CommonHelper::curl($url, "POST", json_encode($request), ["Content-Type: application/json"], 'no', $userId, $modal, $reqType);
        //print_r($result);
        $response = json_decode($result['response'], 1);
        return $response;
    }

    public function decryptUPIdata($string, $userId)
    {
        $url = 'http://45.249.111.172/XettleUPI/Indusind/GetDecryptUPIdata';

        $request = [
            'message' => $string,
            'decr_key' => $this->decr_key
        ];
        $modal = '';
        $reqType = '';
        $result = CommonHelper::curl($url, "POST", json_encode($request), ["Content-Type: application/json"], 'no', $userId, $modal, $reqType);

        $response = json_decode($result['response'], 1);
        return $response;
    }



    /**
     * Manage URLs for Indus APIs
     */
    private function upiCaller($params, $requestType, $userId, $modal, $reqType, $paramsForLog)
    {
        switch ($requestType) {
            case 'collect':
                // $request = $params;
                // $url = $this->baseUrl . "/upijson/meCollectInitiateWeb";
                break;

            case 'merchant':
                $request = $params;
                $url = $this->baseUrl . '/api/onBoardSubMerchant';
                // $url = $this->baseUrl . '/app/uat/web/onBoardSubMerchant';
                break;

            case 'addmerchant':
                // $request = $params;
                // $url = $this->baseUrl . '/web/onBoardDirectMerchant';
                break;

            case 'status':
                // $request = [];
                // $url = $this->baseUrl . "/status/" . $params['txnId'];
                break;

            case 'verify':
                $request = $params;
                $url = $this->baseUrl . '/upijson/validateVPAWeb';
                // $url = $this->baseUrl . '/app/uat/web/validateVPAWeb';
                break;

            case 'transaction_status':
                $request = $params;
                $url = $this->baseUrl . '/upijson/meTranStatusQueryWeb';
                // $url = $this->baseUrl . '/app/uat/web/metranstatusqueryweb';
                break;

            case 'transaction_refund':
                $request = $params;
                $url = $this->baseUrl . '/upi/meRefundJsonService';
                // $url = $this->baseUrl . "/app/uat/web/meRefundJsonService";
                break;

            case 'transaction_history':
                // $request = $params;
                // $url = $this->baseUrl . '/upijson/metransactionhistoryweb';
                break;

            case 'deactivate_merchant':
                // $request = $params;
                // $url = $this->baseUrl . '/api/deActivateMerchant';
                break;

            case 'mepayout':
                // $request = $params;
                // $url = $this->baseUrl . '/upi/mePayServerApi';
                break;
        }

        $result = CommonHelper::curl($url, "POST", json_encode($request), $this->header, 'yes', $userId, $modal, $reqType, '', json_encode($paramsForLog));

        if ($reqType == 'deactivate_merchant') {
            $result['response'] = json_encode(['resp' => $result['response']]);
        }

        // $response = json_decode($result['response'], 1);
        $response['response'] = json_decode($result['response'], 1);
        $response['apiLogLastId'] = $result['apiLogLastId'];
        return $response;
    }



    public function refundAmount($callbackData)
    {
        $orderNo = CommonHelper::getRandomString('RFND');

        $params = [
            "pgMerchantId" => $this->getMerchantId(),
            "orderNo" => $orderNo,
            "orgOrderNo" => $callbackData['merchant_txn_ref_id'],
            "orgINDrefNo" => "",
            "orgCustRefNo" => $callbackData['customer_ref_id'],
            "txnNote" => "Refund initiate for transaction " . $callbackData['customer_ref_id'],
            "txnAmount" => $callbackData['amount'],
            "currencyCode" => "INR",
            "payType" => "P2P",
            "txnType" => "PAY"
        ];

        $requestType = 'transaction_refund';
        $modal = 'ibl';
        $reqType = 'transaction_refund';
        $dec_response = $this->convertRequestResponse($params, $callbackData['user_id'], $requestType, $modal, $reqType);
        // dd($dec_response);
        if (($dec_response['statuscode'] == '000')) {
            if ($dec_response['text']['status'] == 'F') {
                $callbackData['status'] = "failed";
            } else if ($dec_response['text']['status'] == 'S') {
                $callbackData['status'] = "success";
                $callbackData['is_trn_reversed'] = '1';
                $callbackData['trn_reversed_at'] = date('Y-m-d H:i:s');
            }
        } else {
            $callbackData['status'] = "pending";
        }

        $callbackData['order_no'] = $orderNo;
        $callbackData['created_at'] = date('Y-m-d H:i:s');

        DB::table('upi_reverse_transactions')->insert($callbackData);

        return true;
    }
}
