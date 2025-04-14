<?php

namespace App\Helpers;


class InstantPayHelper
{

    private $auth_code;
    private $client_id;
    private $secret_key;
    private $ip;
    private $bank_id;
    private $bank_profile_id;
    private $bank_account_number;
    private $baseUrl;
    private $lat;
    private $long;

    public function __construct()
    {
        $this->baseUrl = env('INSTANTPAY_BASE_URL');
        $this->auth_code = base64_decode(env('INSTANTPAY_XIPAY_AUTH_CODE'));
        $this->client_id = base64_decode(env('INSTANTPAY_XIPAY_CLIENT_ID'));
        $this->secret_key = base64_decode(env('INSTANTPAY_XIPAY_CLIENT_SECRET'));
        $this->ip = base64_decode(env('INSTANTPAY_XIPAY_ENDPOINT_IP'));
        $this->bank_id = base64_decode(env('INSTANTPAY_BANK_ID'));
        $this->bank_profile_id = base64_decode(env('INSTANTPAY_BANK_PROFILE_ID'));
        $this->bank_account_number = base64_decode(env('INSTANTPAY_BANK_ACCOUNT_NUMBER'));
        $this->lat = env('INSTANTPAY_LAT');
        $this->long = env('INSTANTPAY_LONG');
    }


    public  function instantpayTransfer($orderData)
    {
        $purpose = CommonHelper::case($orderData['purpose'], 'u');
            if (in_array($purpose, ['SALARY', 'REIMBURSEMENT', 'BONUS', 'INCENTIVE', 'CUSTOMER_REFUND', 'OTHERS']) == false) {
                if ($purpose == 'REFUND') {
                    $purpose = 'CUSTOMER_REFUND';
                } else {
                    $purpose = 'OTHERS';
                }
            }
        $mode = CommonHelper::case($orderData['mode'], 'u');
        if (isset($orderData['account_ifsc']) && !empty($orderData['account_ifsc'])) {
          $ifsc = CommonHelper::case($orderData['account_ifsc'], 'u');
        } else {
            $ifsc = '';
        }
            if ($mode == 'UPI') {
                $accountNo = $orderData['vpa_address'];
            } else {
                $accountNo = $orderData['account_number'];
            }
        $payeeName = $orderData['first_name'].' '.$orderData['last_name'];
            if (strlen($payeeName) >= 49) {
               $payeeName = substr($payeeName,0, 49);
            }
        $payeeName =  $this->special_character_remove($payeeName);
        $request =  [
            'payer' => [
                "bankId" => $this->bank_id,
                "bankProfileId" => $this->bank_profile_id,
                "accountNumber" => $this->bank_account_number,
            ],
            'payee' => [
                "name" => $payeeName,
                "accountNumber" => $accountNo,
                "bankIfsc" => $ifsc,
            ],
            'transferAmount' => (float)$orderData['amount'],
            'externalRef' => $orderData['order_ref_id'],
            'transferMode' => $mode,
            'latitude' => $this->lat,
            'longitude' => $this->long,
            'remarks' => $orderData['remark'],
            'alertEmail' => '',
            'purpose' => $purpose
        ];
        $parameters = json_encode($request);
        $header = array(
            'X-Ipay-Auth-Code:'.$this->auth_code,
            'X-Ipay-Client-Id:'.$this->client_id,
            'X-Ipay-Client-Secret:'.$this->secret_key,
            'X-Ipay-Endpoint-Ip:'.$this->ip,
            'Content-Type: application/json',
        );
 
        $result = CommonHelper::curl(
            $this->baseUrl . '/payments/payout',
            "POST",
            $parameters,
            $header,
            'yes',
            $orderData['user_id'],
            'instantpay',
            'instantpayTransfer',
            $orderData['order_ref_id']
        );

        $response['data'] = json_decode($result['response']);
        return $response;
    }

    public  function ipAutoSettlement($orderData, $name)
    {
        $orderData = (array) $orderData;

        $purpose = 'OTHERS';
        $mode = CommonHelper::case($orderData['mode'], 'u');
        $ifsc = CommonHelper::case($orderData['account_ifsc'], 'u');
        $accountNo = $orderData['account_number'];
        $payeeName = $name;
            if (strlen($payeeName) >= 49) {
               $payeeName = substr($payeeName,0, 49);
            }
        $payeeName =  $this->special_character_remove($payeeName);
        $request =  [
            'payer' => [
                "bankId" => $this->bank_id,
                "bankProfileId" => $this->bank_profile_id,
                "accountNumber" => $this->bank_account_number,
            ],
            'payee' => [
                "name" => $payeeName,
                "accountNumber" => $accountNo,
                "bankIfsc" => $ifsc,
            ],
            'transferAmount' => (float)$orderData['amount'],
            'externalRef' => $orderData['settlement_txn_id'],
            'transferMode' => $mode,
            'latitude' => $this->lat,
            'longitude' => $this->long,
            'remarks' => 'remarks',
            'alertEmail' => '',
            'purpose' => $purpose
        ];
        $parameters = json_encode($request);
        $header = array(
            'X-Ipay-Auth-Code:'.$this->auth_code,
            'X-Ipay-Client-Id:'.$this->client_id,
            'X-Ipay-Client-Secret:'.$this->secret_key,
            'X-Ipay-Endpoint-Ip:'.$this->ip,
            'Content-Type: application/json',
        );
 
        $result = CommonHelper::curl(
            $this->baseUrl . '/payments/payout',
            "POST",
            $parameters,
            $header,
            'yes',
            $orderData['user_id'],
            'instantpay',
            'ipAutoSettlement',
            $orderData['settlement_txn_id']
        );

        $response['data'] = json_decode($result['response']);
        return $response;
    }

    public  function instantpayTransferStatus($orderRefId, $date, $userId)
    {
        $newDate = date('Y-m-d', strtotime($date));
        $request = [
            'transactionDate' => $newDate,
            'externalRef' => $orderRefId
        ];
        $headers = array(
            'X-Ipay-Auth-Code:'.$this->auth_code,
            'X-Ipay-Client-Id:'.$this->client_id,
            'X-Ipay-Client-Secret:'.$this->secret_key,
            'X-Ipay-Endpoint-Ip:'.$this->ip,
            'Content-Type: application/json',
        );
        $result = CommonHelper::curl(
            $this->baseUrl . "/reports/txnStatus",
            "POST",
            json_encode($request),
            $headers,
            'yes',
            $userId,
            'instantpay',
            'instantpayTransferStatus',
            $orderRefId,
            json_encode($request),
        );

        $response['data'] = json_decode($result['response']);

        return $response;
    }

    public  function instantpayBalanceCheck($orderRefId)
    {

        $request = [
            "bankId" => $this->bank_id,
            "bankProfileId" => $this->bank_profile_id,
            "accountNumber" => $this->bank_account_number,
            "latitude" => env('INSTANTPAY_LAT'),
            "longitude" => env('INSTANTPAY_LONG'),
            'externalRef' => $orderRefId
        ];
        $headers = array(
            'X-Ipay-Auth-Code:'.$this->auth_code,
            'X-Ipay-Client-Id:'.$this->client_id,
            'X-Ipay-Client-Secret:'.$this->secret_key,
            'X-Ipay-Endpoint-Ip:'.$this->ip,
            'Content-Type: application/json',
        );
        $result = CommonHelper::curl(
            $this->baseUrl . "/accounts/balance",
            "POST",
            json_encode($request),
            $headers,
            'yes',
            '',
            'instantpay',
            'instantpayBalanceCheck',
            $orderRefId,
            json_encode($request),
        );

        $response['data'] = json_decode($result['response']);

        return $response;
    }

   public function special_character_remove($string)
   {
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , ' ', $string);
        return trim($string, '-');
    }


    /**
     * Verify Bank Account API
     */
    public function verifyBankAccount($bankDetails, $refNo, $userId)
    {
        

        $params = [
            'payee' => [
                'accountNumber' => $bankDetails->account_number,
                'bankIfsc' => $bankDetails->ifsc,
            ],
            'externalRef' => $refNo,
            'consent' => 'Y',
            'isCached' => '1',
            'latitude' => $this->lat,
            'longitude' => $this->long,
        ];

        $headers = array(
            'X-Ipay-Auth-Code:' . $this->auth_code,
            'X-Ipay-Client-Id:' . $this->client_id,
            'X-Ipay-Client-Secret:' . $this->secret_key,
            'X-Ipay-Endpoint-Ip:' . $this->ip,
            'Content-Type: application/json',
        );

        return CommonHelper::curl(
            $this->baseUrl . "/identity/verifyBankAccount",
            "POST",
            json_encode($params),
            $headers,
            'yes',
            $userId,
            'instantpay',
            'verifyBankAccount',
            $refNo
        );
    }

}