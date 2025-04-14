<?php

namespace App\Services;

use App\Helpers\CommonHelper;

class OCRService
{
    protected $key;
    protected $id;
    protected $header;
    protected $url;
    
    public function __construct()
    {
        $this->key = base64_decode(env('IDFY_KEY'));
        $this->id = base64_decode(env('IDFY_ACCOUNT_ID'));
        $this->header = array('account-id' => $this->id, 'api-key' => $this->key);
        $this->url = env('IDFY_URL');
    }

    public function init($data, $uri = "", $method, $userId, $log = "no", $modal = 'ocr', $httpMethod = 'post')
    {
        $url = $this->url . $uri;
        $result = CommonHelper::httpClient($url, $httpMethod, $data, $this->header, $log, $userId, $modal, $method, $data['task_id']);
        return $result;
    }

    public function initGet($data, $uri = "", $method, $userId, $log = "no", $type = "POST")
    {
        $url = $this->url . $uri;
        $result = CommonHelper::httpClient($url, $type, $data, $this->header, 'yes', $userId, 'ocr', $method, $data['task_id']);
        return $result;
    }
}
