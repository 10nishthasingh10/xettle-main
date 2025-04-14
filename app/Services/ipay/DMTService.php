<?php

namespace App\Services\ipay;

use App\Helpers\CommonHelper;

class DMTService
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
    private $header;
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

        $this->header = array(
            'X-Ipay-Auth-Code' => $this->auth_code,
            'X-Ipay-Client-Id' => $this->client_id,
            'X-Ipay-Client-Secret' => $this->secret_key,
            'X-Ipay-Endpoint-Ip' => $this->ip,
            'Content-Type' => 'application/json',
        );
    }


    public function init($data, $uri = "", $method, $userId, $log = "no", $modal = 'dmt', $httpMethod = 'POST')
    {

        $url = $this->baseUrl . $uri;
        if (in_array($method, ['remitterProfile', 'remitterRegistration', 'beneficiaryRegistration', 'banks', 'remitterTransferLimit', 'beneficiaryDelete', 'beneficiaryOTPValidate', 'remitterOTPValidate', 'remitterUpdate', 'fundTransfer', 'remitterEKYC'])) {
            $this->header['X-Ipay-Outlet-Id'] = @$data['outletId'];
        }

        $result = CommonHelper::httpClient($url, $httpMethod, $data, $this->header, $log, $userId, $modal, $method, @$data['txnId']);
        return $result;
    }

    public function initGet($data, $uri = "", $method, $userId, $log = "no", $type = "POST")
    {
        $url = $this->url . $uri;
        $result = CommonHelper::httpClient($url, $type, $data, $this->header, 'yes', $userId, 'dmt', $method, @$data['txnId']);
        return $result;
    }
}
