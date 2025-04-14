<?php

namespace App\Helpers;

use CommonHelper;
use Storage;
use App\Models\TransactionHistory;

class EaseBuzzHelper
{

    private $key;
    private $salt;
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('EASEBUZZ_BASE_URL').'/api/v1/';
        $this->key = base64_decode(env('EASEBUZZ_KEY'));
        $this->salt = base64_decode(env('EASEBUZZ_SALT'));
    }

    public function authorized($request)
    {

        $str = $this->key."|".$request['account_number']."|".$request['ifsc']."|".$request['upi_handle']."|".$request['unique_request_number']."|".$request['amount']."|".$this->salt;
        $hash = hash('sha512', $str);
        return $hash;
    }

    public  function quickTransfer($orderData, $paymentMode = 'BANK_ACCOUNT')
    {
        $mode = CommonHelper::case($orderData['mode'], 'u');
        $request =  array(
            'key' => $this->key,
            'amount' => (float)$orderData['amount'],
            'unique_request_number' => $orderData['order_ref_id'],
            'payment_mode' => $mode,
            'beneficiary_name' => $orderData['name'],
            'narration' => $orderData['remark'],
        );
        $allRequest = [];
        if ($paymentMode == 'BANK_ACCOUNT') {

                $allRequest =  array_merge($request, array(
                    'account_number' => $orderData['account_number'],
                    'ifsc' => $orderData['account_ifsc'],
                    'beneficiary_type' => 'bank_account',
                    'upi_handle' => '',
                ));
        } elseif ($paymentMode == 'UPI') {
            $allRequest =  array_merge($request, array(
                'upi_handle' => $orderData['vpa_address'],
                'beneficiary_type' => 'upi',
                'account_number' => '',
                'ifsc' => '',
            ));
        }
        $hash = $this->authorized($allRequest);
        $headers = array(
            'Authorization: ' . $hash,
            'Content-Type: application/json',
        );
        $result = CommonHelper::curl(
            $this->baseUrl . 'quick_transfers/initiate/',
            "POST",
            json_encode($allRequest),
            $headers,
            'yes',
            $orderData['user_id'],
            'EaseBuzz',
            'quickTransfer',
            $orderData['order_ref_id']
        );

        $response['data'] = json_decode($result['response']);
        return $response;
    }

    public  function ebAutoSettlement($orderData, $name)
    {
        $orderData = (array) $orderData;
        $orderData = (array) $orderData;
        $mode = CommonHelper::case($orderData['mode'], 'u');
        $request =  array(
            'key' => $this->key,
            'amount' => (float)$orderData['amount'],
            'unique_request_number' => $orderData['settlement_txn_id'],
            'payment_mode' => $mode,
            'beneficiary_name' => $name,
            'narration' => 'remark',
        );

        $allRequest =  array_merge($request, array(
            'account_number' => $orderData['account_number'],
            'ifsc' => $orderData['account_ifsc'],
            'beneficiary_type' => 'bank_account',
            'upi_handle' => '',
        ));
        $hash = $this->authorized($allRequest);
        $headers = array(
            'Authorization: ' . $hash,
            'Content-Type: application/json',
        );
        $result = CommonHelper::curl(
            $this->baseUrl . 'quick_transfers/initiate/',
            "POST",
            json_encode($allRequest),
            $headers,
            'yes',
            $orderData['user_id'],
            'EaseBuzz',
            'ebAutoSettlement',
            $orderData['settlement_txn_id']
        );

        $response['data'] = json_decode($result['response']);
        return $response;
    }

    public  function quickTransferStatus($orderRefId, $userId)
    {
        $request = [];
        $headers = array(
            'Authorization: ' . hash('sha512', $this->key."|".$orderRefId."|".$this->salt),
            'Content-Type: application/json',
        );
        $result = CommonHelper::curl(
            $this->baseUrl . "transfers/$orderRefId/?key=".$this->key,
            "GET",
            json_encode($request),
            $headers,
            'yes',
            $userId,
            'EaseBuzz',
            'quickTransferStatus',
            $orderRefId
        );

        $response['data'] = json_decode($result['response']);

        return $response;
    }


}