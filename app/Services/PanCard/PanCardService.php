<?php

namespace App\Services\PanCard;

use App\Helpers\CommonHelper;

class PanCardService
{

    private $key;
    private $baseUrl;
    private $header;
    private $baseUrlUTI;
    private $UTIkey;
    private $UTISecret;

    private $UTIheader;
    private $Newheader;
    private $NSDLheader;
    public function __construct()
    {
        $this->baseUrl = env('PANCARD_URL');
        $this->baseUrlUTI = env('PANCARD_UTI_URL');
        $this->key = base64_decode(env('PANCARD_TOKEN'));
        $this->UTIkey = base64_decode(env('PANCARD_UTI_SALT'));
        $this->UTISecret = base64_decode(env('PANCARD_UTI_SECRET'));
        $this->header = array(
            'token' => $this->key,
            'Content-Type' => 'application/json',
        );
        $this->UTIheader = array(
            'SaltKey' => $this->UTIkey,
            'SecretKey' => $this->UTISecret,
            'Content-Type' => 'application/json',
        );
        $this->NSDLheader = array(
            'SecurityKey' => base64_decode(env('AEPS_SECURITY_KEY')),
            'Content-Type' => 'application/json',
        );

    }


    public function init($data, $uri = "", $method, $userId, $log = "no", $modal = 'pancard', $httpMethod = 'POST')
    {

        if (str_contains($uri, 'VleOnbording') || str_contains($uri, 'UTIPanCheckSum')  || str_contains($uri, 'PSAstatuscheck')  ) {
            $url = $this->baseUrlUTI . $uri;
            $this->Newheader = $this->UTIheader;
        } else if ($method ==  'initNsdlPan' ) {
            $url = $this->baseUrlUTI . $uri;
            $this->Newheader = $this->NSDLheader;
        } else {
            $url = $this->baseUrl . $uri;
            $this->Newheader = $this->header;
        }


        $result = CommonHelper::httpClient($url, $httpMethod, $data, $this->Newheader, $log, $userId, $modal, $method, @$data['txnId']);
        return $result;
    }

    public function initGet($data, $uri = "", $method, $userId, $log = "no", $type = "POST")
    {
        $url = $this->baseUrl . $uri;
        $result = CommonHelper::httpClient($url, $type, $data, $this->header, 'yes', $userId, 'pancard', $method, @$data['txnId']);
        return $result;
    }
}
