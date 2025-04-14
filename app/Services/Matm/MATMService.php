<?php

namespace App\Services\Matm;

use App\Helpers\CommonHelper;

class MATMService
{

    private $key;
    private $baseUrl;
    private $header;

    public function __construct()
    {
        $this->baseUrl = env('MATM_BASE_URL');
        $this->key = base64_decode(env('MATM_SECURITY_KEY'));
        $this->header = array(
            'SecurityKey' => $this->key,
            'Content-Type' => 'application/json',
        );

    }


    public function init($data, $uri = "", $method, $userId, $log = "no", $modal = 'matm', $httpMethod = 'POST')
    {

        $url = $this->baseUrl . $uri;

        $result = CommonHelper::httpClient($url, $httpMethod, $data, $this->header, $log, $userId, $modal, $method, @$data['txnId']);
        return $result;
    }

    public function initGet($data, $uri = "", $method, $userId, $log = "no", $type = "POST")
    {
        $url = $this->baseUrl . $uri;
        $result = CommonHelper::httpClient($url, $type, $data, $this->header, 'yes', $userId, 'matm', $method, @$data['txnId']);
        return $result;
    }
}
