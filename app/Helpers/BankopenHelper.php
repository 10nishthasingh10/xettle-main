<?php

namespace App\Helpers;

use CommonHelper;
use Storage;
use App\Models\TransactionHistory;

class BankopenHelper
{

    private $key;
    private $salt;
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://ibrpay.com/api/PayoutLive.aspx';
        $this->key ='API1006';
        $this->secret = 'fe06f96f-95f5-4a36-8d7f-0de71bff8b54';
        $this->debitAccountNumber = base64_decode(env('OPENBANK_ACC_NUM'));
    }

    public function headerVal()
    {
        return "Bearer {$this->key}:{$this->secret}";
    }


    public  function initPayout($orderData, $paymentMode = 'BANK_ACCOUNT')
    {
        $mode = CommonHelper::case($orderData['mode'], 'u');
        if ($mode == 'IMPS') {
            $modeType = "4";
        } else if ($mode == 'NEFT') {
            $modeType = "2";
        } else if ($mode == 'RTGS') {
            $modeType = "3";
        } else if ($mode == 'RTGS') {
            $modeType = "3";
        }

        if ($paymentMode == 'UPI') {
            $modeType = "21";
        }
        dd($orderData);
        $request =  array(
            "APIID" => $orderData['name'],
            "Token" => $orderData['email'],
            "MethodName" => $orderData['phone'],
            "OrderID" => "",
            "Name" => $this->debitAccountNumber,
            "Amount" => $modeType,
            "number" => (float)$orderData['amount'],
            "ifsc" => $orderData['order_ref_id'],
            "PaymentType" => $orderData['order_ref_id'],
            "CustomerMobileNo" => $orderData['order_ref_id'],
            //"purpose" => $orderData['purpose'],
        );
        $allRequest = [];
        if ($paymentMode == 'BANK_ACCOUNT') {

                $allRequest =  array_merge($request, array(
                    "bene_account_number" => $orderData['account_number'],
                    "ifsc_code" => $orderData['account_ifsc'],
                ));
        } elseif ($paymentMode == 'UPI') {
            $allRequest =  array_merge($request, array(
                "vpa" => $orderData['vpa_address']
            ));
        }
        $hash = $this->headerVal();
        $headers = array(
            'Authorization: ' . $hash,
            'Content-Type: application/json',
        );
        $result = CommonHelper::curl(
            $this->baseUrl . 'payouts',
            "POST",
            json_encode($allRequest),
            $headers,
            'yes',
            $orderData['user_id'],
            'BankOpen',
            'payoutInit',
            $orderData['order_ref_id']
        );

        $response['data'] = json_decode($result['response']);
        return $response;
    }


    public  function bankopenStatus($orderRefId, $userId)
    {
        $request = "";
        $hash = $this->headerVal();
        $headers = array(
            'Content-Type: application/json',
           // 'Accept : application/json',
            'Authorization: ' . $hash,
        );
        $result = CommonHelper::curl(
            $this->baseUrl . "payouts/$orderRefId",
            "GET",
            "",
            $headers,
            'yes',
            $userId,
            'BankOpen',
            'bankopenStatus',
            $orderRefId
        );

        $response['data'] = json_decode($result['response']);

        return $response;
    }


}