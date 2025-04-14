<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Apilog;
use DB;
use Exception;
use Storage;
use Cashfree;
use App\Models\UPIMerchant;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
class DeactivateMerchant extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fpupimerchant:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate Merchant';

    /**
     * Client Id Variable
     *
     * @var string
     */
    protected $key;

    /**
     *  Client Secret Variable
     * @var string
     */
    protected $secret;

    /**
     * Base Url Variable
     * @var string
     */
    protected $baseUrl;

    /**
     * header variable
     *
     * @var [array]
     */
    protected $header;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->baseUrl = env('FIDYPAY_BASE_URL');
        $this->key = base64_decode(env('FIDYPAY_CLIENT_ID'));
        $this->secret = base64_decode(env('FIDYPAY_CLIENT_SECRET'));
        $this->username = base64_decode(env('FIDYPAY_USER_KEY'));
        $this->password = base64_decode(env('FIDYPAY_USER_SECRET'));
        $this->header = ["Content-Type: application/json", "Client-Id: ".$this->key, "Client-Secret: ".$this->secret, "Authorization: Basic ".base64_encode($this->username.":".$this->password).""];
        $vpa =130;
        $merchantTxnRefId = $this->getTxnId();
        $mcc = '7299';
        $code= "0x0201";
        if(empty($request->merchantGenre)){
            $merchantGenre = 'OFFLINE';
        } else {
            $merchantGenre = strtoupper($request->merchantGenre);
        }

        $merchant = UPIMerchant::where('user_id',$vpa)->where('is_active',1)->limit(500)->orderBy('id','asc')->get();

        //print_r($merchant);
        if(!empty($merchant))
        {
           foreach($merchant as $val)
           {
        
            $params = [
                'action' => 'D',
                'merchantBussiessName' => $val->merchant_business_name,
                'merchantVirtualAddress' => "string",
                'requestUrl1' => "string",
                'panNo' => "string",
                'contactEmail' => "string",
                'gstn' => "string",
                'merchantBussinessType' => "string",
                'perDayTxnCount' => "string",
                'perDayTxnLmt' => "string",
                'perDayTxnAmt' => "string",
                'mobile' => "string",
                'address' => "string",
                'state' => "string",
                'city' => "string",
                'pinCode' => "string",
                'subMerchantId' => $val->sub_merchant_id,
                //'merchantTrxnRefId' => $merchantTxnRefId,
                'mcc' => "string", //$request->mcc ? $request->mcc : ''
                'merchantGenre' => "string",
            ];
            $requestType = 'merchant';
            $userId=1;
            // if(isset($request->auth_data['user_id'])) {
            //     $userId = $request->auth_data['user_id'];
            // } else {
            //     $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            // }
            $modal = 'fidypay';
            $reqType = 'deleteMerchant';
            $response = $this->UPICaller($params, $requestType, $userId, $modal, $reqType);
            if(isset($response['code'])) {
                if($response['code'] === "0x0200") {
                    $merchantU = UPIMerchant::where('id',$val->id)->update(array('is_active'=>0));
                    
                $data = [
                            'loginAccess' => $response['loginaccess'],
                            'subMerchantId' => $response['subMerchantId'],
                            'merchantBusinessName' => $response['merchantBussinessName'],
                            'merchantVirtualAddress' => $response['merchantVirtualAddress'],
                            'requestId' => $merchantTxnRefId, //$response['requestId'],
                            'crtDate' => $response['crtDate'],
                            'action' => $response['action']
                        ];
                $code = $response['code'];
                $this->message = "Merchant deleted successfully.";//$response['description'];
                $status = "SUCCESS";
            }
            elseif($response['code'] === "0x0201") {
                    $code = $response['code'];
                    $this->message = 'Something went wrong please try again';
                    $status = "FAILURE";
                } else {
                    $code = $response['code'];
                    $this->message = $response['description'];
                    $status = "FAILURE";
                }
            }
    
        $resp['code']       = $code;
        $resp['message']    = $this->message;
        $resp['status']     = isset($status)?$status:"FAILURE";
        if(isset($data)) {
            $resp['data']   = $data;
        }
        //return response()->json($resp);
        }
    }
    else
    {
        $this->message = 'No record found.';
    }
    $this->info($resp['message']);
    }

    public function UPICaller($params, $requestType, $userId, $modal, $reqType)
    {
        switch ($requestType) {

            case 'merchant':
                $request = $params;

                $url = $this->baseUrl.'/createSubMerchant';
            break;

        }
        
        $result = CommonHelper::curl($url, "POST", json_encode($request) , $this->header, 'yes', $userId, $modal, $reqType);
        $response = json_decode($result['response'], 1);
        return $response;
    }

    public function getTxnId()
    {
        return CommonHelper::getRandomString();
    }
}