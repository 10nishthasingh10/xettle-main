<?php

namespace App\Services\DocVerify;

use App\Helpers\CommonHelper;
use Exception;
use Illuminate\Support\Facades\DB;

class DocVerifyTechnoApi implements DocVerify
{
    private $cpid;
    private $token;
    private $baseUrl;
    private $header;
    private $tableName = 'validations';

    public function __construct()
    { 
        $this->baseUrl = env('TECHNO_URL');
        $this->cpid = base64_decode(env('TECHNO_CPID'));
        $this->token = base64_decode(env('TECHNO_TOKEN'));

        $this->header = array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        );
    }


    public function send(DocVerifyBO $obj)
    {
        // $data = [
        //  'data'=> $data,
        //  'uri'   
        // ];
        // $data, $uri = "", $reqSlug, $userId, $log = "no", $modal = 'Validation', $httpMethod = 'post'

        $url = $this->baseUrl . $this->getUri($obj->uri);
        $result = CommonHelper::httpClient(
            $url,
            $obj->http,
            $this->makeParams($obj->uri, $obj->param),
            $this->header,
            $obj->log,
            $obj->userId,
            $obj->table,
            $obj->slug,
            $obj->clientRefId
        );

        return $result;
    }


    public function create($userId, $dbData, $taxData)
    {
        $response['status'] = false;
        $response['message'] = 'Order Not created';

        DB::beginTransaction();
        try {
            // Transaction Create
            $orderData = [
                'service_id' => VALIDATE_SERVICE_ID,
                'user_id' => $userId,
                // 'group_id' => @$dbData['task_id'],
                'param_1' => @$dbData['param1'],
                'param_2' => @$dbData['param2'],
                'order_ref_id' => @$dbData['order_ref_id'],
                'type' => @$dbData['type'],
                'status' => 'queued',
                'fee' => @$taxData['fee'],
                'tax' => @$taxData['tax'],
                'product_id' => @$taxData['product_id'],
                'margin' => @$taxData['margin']
            ];
            $createTransaction = DB::table($this->tableName)->insert($orderData);
            if ($createTransaction) {
                DB::commit();
                $response['status'] = true;
                $response['message'] = 'Order created successfully.';
            } else {
                $response['status'] = false;
                $response['message'] = 'Order not created.';
            }
        } catch (Exception $e) {
            DB::rollback();
            $response['status'] = false;
            $response['message'] = 'something went wrong : ' . $e->getMessage();
        }
        return $response;
    }



    public function update($cond = [], $data = [])
    {
        if (DB::table($this->tableName)->where($cond)->update($data)) {
            return true;
        }
        return false;
    }



    /**
     * Making AADHAAR API Params
     */
    private function makeParams($type, $array)
    {

        $param = [];

        switch ($type) {
            case 'pan':
                $param = [
                    "panno" => $array['panno'],
                    "Client_refid" => $array['clientRefId'],
                    "cpid" => $this->cpid,
                    "token" => $this->token
                ];
                break;
            case 'aadhaar':
                $param = [
                    "aadhaarno" => $array['aadhaar'],
                    "Client_refid" => $array['clientRefId'],
                    "cpid" => $this->cpid,
                    "token" => $this->token
                ];
                break;
            case 'aadhaarOtp':
                $param = [
                    "Mahareferid" => $array['requestId'],
                    "Client_refid" => $array['clientRefId'],
                    "otp" => $array['otp'],
                    "cpid" => $this->cpid,
                    "token" => $this->token
                ];
                break;
        }

        return $param;
    }


    public function getUri($type)
    {
        $uri = '';

        switch ($type) {
            case 'pan':
                $uri = '/Verification/PanVerification';
                break;
            case 'aadhaar':
                $uri = '/Verification/AadhaarVerificationOTP';
                break;
            case 'aadhaarOtp':
                $uri = '/Verification/SubmitAadhaarOTP';
                break;
        }

        return $uri;
    }
}
