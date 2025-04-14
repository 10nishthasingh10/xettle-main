<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Integration;
use CommonHelper;
use Storage;
use App\Models\TransactionHistory;
use Illuminate\Support\Facades\DB;

class CashfreeHelper
{

    private $key;
    private $secret;
    private $baseUrl;
    private $token;
    private $payoutRoute;
    private $header;

    public function __construct()
    {
        $this->baseUrl = env('CASHFREE_BASE_URL').'/payout/v1/';
        $this->key = base64_decode(env('CASHFREE_KEY'));
        $this->secret = base64_decode(env('CASHFREE_SECRET'));
        $this->token = Storage::get('tokens/cashfreeToken.txt');
        $this->header = array(
            'Authorization: Bearer ' . $this->token,
        );
    }

    public function authorized()
    {

        $request = [];
        $header = array(
            'X-Client-Id: ' . $this->key,
            'X-Client-Secret: ' . $this->secret,
            'Content-Type: application/json',
        );
        $result = CommonHelper::curl($this->baseUrl . 'authorize', "POST", json_encode($request), $header, 'yes', 1, 'cashfree', 'Authorized');
        $response['data'] = json_decode($result['response']);
        if (isset($response['data']->subCode) && $response['data']->subCode == '200') {
            Storage::put('tokens/cashfreeToken.txt', $response['data']->data->token);
        }
        return $response;
    }

    public function verifytoken()
    {

        $request = [];
        $result = CommonHelper::curl($this->baseUrl . 'verifytoken', "POST", json_encode($request), $this->header, 'yes', 1, 'cashfree', 'Verifytoken');
        $response['data'] = json_decode($result['response']);

        return $response;
    }

    public function addBeneficiary(
        $beneId,
        $name,
        $email,
        $phone,
        $bankAccount,
        $ifsc,
        $address1,
        $city,
        $state,
        $pincode,
        $vpa,
        $cardNo
    ) {

        $request = [
            "beneId" => $beneId, "name" => $name, "email" => $email, "phone" => $phone,
            "bankAccount" => $bankAccount, "ifsc" => $ifsc, "address1" => $address1, "city" => $city,
            "state" => $state, "pincode" => $pincode

        ];

        $parameters = json_encode($request);
        $result = CommonHelper::curl($this->baseUrl . 'addBeneficiary', "POST", $parameters, $this->header, 'yes', 1, 3, 2);
        $response['data'] = json_decode($result['response']);

        //curl($url , $method='GET', $parameters, $header, $log="no", $txnid="none")

        return $response;
    }

    public function requestTransfer($beneId, $amount, $transferId)
    {

        $request = [
            "beneId" => $beneId, "amount" => $amount, "transferId" => $transferId,

        ];

        $result = CommonHelper::curl(
            $this->baseUrl . 'requestAsyncTransfer',
            "POST",
            json_encode($request),
            $this->header,
            'yes'
        );

        $response['data'] = json_decode($result['response']);
        return $response;
    }

    public function requestBatchTransfer($orderData, $batchFormat)
    {
        //$mode = CommonHelper::case($orderData['mode'], 'l');
        if ($batchFormat == 'BANK_ACCOUNT') {
            $request = ["batchTransferId" => $orderData['order_ref_id'], "batchFormat" => $batchFormat, "batch" =>
            [
                [
                    "amount" => $orderData['amount'], "transferId" => $orderData['order_ref_id'], "remarks" => $orderData['remark'], "name" => $orderData['name'],
                    "email" => $orderData['email'], "phone" => $orderData['phone'], "bankAccount" => $orderData['account_number'], "ifsc" => $orderData['account_ifsc']
                ]

            ]];
        } else if ($batchFormat == 'UPI') {
            $request = ["batchTransferId" => $orderData['order_ref_id'], "batchFormat" => $batchFormat, "batch" =>
            [
                [
                    "amount" => $orderData['amount'], "transferId" => $orderData['order_ref_id'], "remarks" => $orderData['remark'], "name" => $orderData['name'],
                    "email" => $orderData['email'], "phone" => $orderData['phone'], "vpa" => $orderData['vpa_address'], "beneId" => $orderData['contact_id']
                ]

            ]];
        } else {
            $request = [];
        }
        $result = CommonHelper::curl(
            $this->baseUrl . 'requestBatchTransfer',
            "POST",
            json_encode($request),
            $this->header,
            'yes',
            $orderData['user_id'],
            'cashfree',
            'BatchTransfer',
            $orderData['order_ref_id']
        );

        $response['data'] = json_decode($result['response']);

        return $response;
    }

    public function removeBeneficiary($beneId)
    {

        $request = [
            "beneId" => $beneId
        ];

        $result = CommonHelper::curl($this->baseUrl . 'removeBeneficiary', "POST", json_encode($request), $this->header, 'yes');
        $response['data'] = json_decode($result['response']);
        return $response;
    }

    public function getTransferStatus($transferId)
    {

        $request = [
            "transferId" => ''
        ];

        $result = CommonHelper::curl($this->baseUrl . 'getBatchTransferStatus?batchTransferId=' . $transferId, "GET", json_encode($request), $this->header, 'yes', 1, 'cashfree', 'statusCheck', $transferId);
        $response['data'] = json_decode($result['response']);
        return $response;
    }

    public function getBalance()
    {
        $request = [];
        $result = CommonHelper::curl($this->baseUrl . 'getBalance', "GET", json_encode($request), $this->header, 'yes', 1, 'cashfree', 'getBalance');
        $response['data'] = json_decode($result['response']);
        return $response;
    }

    public  function requestDirectTransfer($orderData, $paymentMode = 'BANK_ACCOUNT')
    {
        if ($paymentMode == 'BANK_ACCOUNT') {
            $mode = CommonHelper::case($orderData['mode'], 'l');
            if (isset($mode)) {
                $request =  [
                    'amount' => $orderData['amount'],
                    'transferId' => $orderData['order_ref_id'],
                    'transferMode' => $mode,
                    'beneDetails' => [
                    'bankAccount' => $orderData['account_number'],
                    'ifsc' => $orderData['account_ifsc'],
                    'name' => $orderData['name'],
                    'email' => $orderData['email'],
                    'phone' => $orderData['phone'],
                    'address1' => isset($orderData['address']) ? $orderData['address'] : "any_dummy_value",
                    'remarks' => $orderData['remark'],
                    ],
                ];
            }
        } elseif ($paymentMode == 'UPI') {
            $request =  [
                'amount' => $orderData['amount'],
                'transferId' => $orderData['order_ref_id'],
                'transferMode' => 'upi',
                'beneDetails' => [
                    'vpa' => $orderData['vpa_address'],
                    'name' => $orderData['name'],
                    'email' => $orderData['email'],
                    'phone' => $orderData['phone'],
                    'address1' => isset($orderData['address']) ? $orderData['address'] : "any_dummy_value",
                    'remarks' => $orderData['remark'],
                ],
            ];
        }
        $result = CommonHelper::curl(
            $this->baseUrl . 'directTransfer',
            "POST",
            json_encode($request),
            $this->header,
            'yes',
            $orderData['user_id'],
            'cashfree',
            'directTransfer',
            $orderData['order_ref_id']
        );

        $response['data'] = json_decode($result['response']);

        return $response;
    }

    public  function cfAutoSettlement($orderData, $userData)
    {
        $orderData = (array) $orderData;
        $userData = (array) $userData;
            $mode = CommonHelper::case($orderData['mode'], 'l');
            if (isset($mode)) {
                $request =  [
                    'amount' => $orderData['amount'],
                    'transferId' => $orderData['settlement_txn_id'],
                    'transferMode' => $mode,
                    'beneDetails' => [
                        'bankAccount' => $orderData['account_number'],
                        'ifsc' => $orderData['account_ifsc'],
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'phone' => $userData['mobile'],
                        'address1' => isset($orderData['address']) ? $orderData['address'] : "any_dummy_value",
                        'remarks' => 'remarks',
                    ],
                ];
            }

                $result = CommonHelper::curl(
                    $this->baseUrl . 'directTransfer',
                    "POST",
                    json_encode($request),
                    $this->header,
                    'yes',
                    $orderData['user_id'],
                    'cashfree',
                    'cfAutoSettlement',
                    $orderData['settlement_txn_id']
                );
                $response['data'] = json_decode($result['response']);

        return $response;
    }

    public function getDirectTransferStatus($transferId)
    {

        $request = [
            "transferId" => ''
        ];

        $result = CommonHelper::curl($this->baseUrl . 'getTransferStatus?transferId=' . $transferId, "GET", json_encode($request), $this->header, 'yes', 1, 'cashfree', 'statusCheck', $transferId);
        $response['data'] = json_decode($result['response']);
        return $response;
    }

}