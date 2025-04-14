<?php

namespace App\Services;

use App\Helpers\CommonHelper;

class InsuranceService
{
	protected  $username;
    protected  $password;
    protected  $baseUrl;
    protected  $header;


    function __construct()
    {
        $this->username = base64_decode(env('INS_USERNAME'));
        $this->password = base64_decode(env('INS_PASSWORD'));
        $this->baseUrl = env('INS_BASE_URL');
        $this->header = ['x-api-key'=> base64_decode(env('INS_API_KEY')),'Authorization'=>'Basic '.base64_encode($this->username.':'.$this->password)];
    }


    /**
     * setCredential
     *
     * @return string
     */
    public function setFullUrl($method): string
    {
    	if ($method == 'pos')
        	return $this->baseUrl.'/iam-pos/api/v1/user/auth/partner';

        return "";
    }

    public function init($data, $uri = "", $method, $userId, $log = "no", $modal = 'insurance', $httpMethod = 'post')
    {
        //$url = $this->baseUrl . $uri;
        $fullURL = $this->setFullUrl('pos');
        $result = CommonHelper::httpClient($fullURL, $httpMethod, $data, $this->header, $log, $userId, $modal, $method);
        return $result;
    }

    public function pos($request)
    {
    	$parameters = [
    		"referenceAuthId" => "7623457764",
    		"mobile" => "7623457764"
    	];

    	$fullURL = $this->setFullUrl('pos');

    	$result = $this->commonService->init($parameters, $fullURL, 'post', @$request->user()->user_id, 'yes', 'insurance_pos', 'insurance', '', $this->header, 'json', $this->basicAuth);
    	return $result;
    }
}