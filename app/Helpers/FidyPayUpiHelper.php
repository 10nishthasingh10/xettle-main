<?php

namespace App\Helpers;

class FidyPayUpiHelper
{
    private $key;
    private $secret;
    private $baseUrl;
    private $header;


    public function __construct()
    {
        $this->baseUrl = env('FIDYPAY_BASE_URL');
        $this->key = base64_decode(env('FIDYPAY_CLIENT_ID'));
        $this->secret = base64_decode(env('FIDYPAY_CLIENT_SECRET'));
        $this->username = base64_decode(env('FIDYPAY_USER_KEY'));
        $this->password = base64_decode(env('FIDYPAY_USER_SECRET'));
        $this->header = ["Content-Type: application/json", "Client-Id: " . $this->key, "Client-Secret: " . $this->secret, "Authorization: Basic " . base64_encode($this->username . ":" . $this->password) . ""];
    }



    /**
     * Manage URLs for yesbank APIs
     */
    public function upiCaller($params, $requestType, $userId, $modal, $reqType)
    {
        switch ($requestType) {
            case 'collect':
                $request = $params;
                $url = $this->baseUrl . "/collect";
                break;

            case 'merchant':
                $request = $params;

                $url = $this->baseUrl . '/createSubMerchant';
                break;

            case 'status':
                $request = [];
                $url = $this->baseUrl . "/status/" . $params['txnId'];
                break;

            case 'verify':
                $request = [];
                $url = $this->baseUrl . "/checkVirtualAddress/" . $params['vpa'];
                break;
        }

        $result = CommonHelper::curl($url, "POST", json_encode($request), $this->header, 'yes', $userId, $modal, $reqType);
        $response = json_decode($result['response'], 1);
        return $response;
    }
}
