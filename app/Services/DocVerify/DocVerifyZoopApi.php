<?php

namespace  App\Services\DocVerify;

use App\Helpers\CommonHelper;
use Exception;
use Illuminate\Support\Facades\DB;

class DocVerifyZoopApi implements DocVerify
{
    private $appKey;
    private $appId;
    private $baseUrl;
    private $header;
    private $tableName = 'validations';


    public function __construct()
    {
        $this->baseUrl = env('VERIFY_ZOOP_URL');
        $this->appKey = base64_decode(env('VERIFY_ZOOP_KEY'));
        $this->appId = base64_decode(env('VERIFY_ZOOP_ID'));

        $this->header = array(
            'Content-Type' => 'application/json',
            'app-id' => $this->appId,
            'api-key' => $this->appKey
            // 'Accept' => 'application/json',
        );
    }


    public function send(DocVerifyBO $obj)
    {
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
                'root_type' => @$dbData['root'],
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
            case 'aadhaar':
                $param = [
                    "data" => [
                        "customer_aadhaar_number" => $array['aadhaar'],
                        "consent" => "Y",
                        "consent_text" => "I hear by declare my consent agreement for fetching my information via ZOOP API"
                    ],
                    "task_id" => $array['clientRefId']
                ];
                break;
            case 'aadhaarOtp':
                $param = [
                    "data" => [
                        "request_id" => $array['requestId'],
                        "otp" => $array['otp'],
                        "consent" => "Y",
                        "consent_text" => "I hear by declare my consent agreement for fetching my information via ZOOP API"
                    ],
                    "task_id" => $array['taskId']
                ];
                break;
        }

        return $param;
    }


    public function getUri($type)
    {
        $uri = '';

        switch ($type) {
            case 'aadhaar':
                $uri = '/in/identity/okyc/otp/request';
                break;
            case 'aadhaarOtp':
                $uri = '/in/identity/okyc/otp/verify';
                break;
        }

        return $uri;
    }
}
