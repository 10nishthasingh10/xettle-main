<?php

namespace App\Services;

use App\Helpers\CommonHelper;

class RechargeService
{
    protected $key;
    protected $id, $mainRechargeBaseUrl;

    public function __construct()
    {
        $this->baseUrl = env('RECH_BASE_URL_UNIQUE');
        $this->user    = env('RECH_USER_UNIQUE');
        $this->pass    = env('RECH_PASS_UNIQUE');
        $this->header   = array('Content-Type'=>'application/json');;
    }

    public function init($data, $uri = "", $method, $userId, $log = "no", $modal = 'recharge', $httpMethod = 'POST')
    {
        
        if (in_array($method, ['getOperator', 'recharge']))
            $url = $uri;
        else
            $url = $uri;
      
    
        $result = CommonHelper::httpClient($url, $httpMethod, $data, $this->header, $log, $userId, $modal, $method, @$data['txnId']);
        return $result;
    }

    public function initGet($data, $uri = "", $method, $userId, $log = "no", $type = "POST")
    {
        $url = $this->url . $uri;
        $result = CommonHelper::httpClient($url, $type, $data, $this->header, 'yes', $userId, 'recharge', $method, @$data['txnId']);
        return $result;
    }


}
