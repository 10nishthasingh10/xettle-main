<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Validations\UPIValidation as Validations;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\UpiCollectHelper;
use App\Jobs\PrimaryFundCredit;
use App\Models\UPIMerchant;
use App\Models\UPICollect;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UPIController extends Controller
{
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
     * construct function init Client Key,Client Secret and Base Url
     */
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
     * index addCollect function
     *
     * @param Request $request
     * @return void
     */
    public function collect(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->collect();
        if ($validator->fails()) {
            $resp['code']       = "0x0100";
            $resp['message']    = json_decode(json_encode($validator->errors()), true);
            $resp['status']     = $this::ERROR_STATUS;
            return response()->json($resp);
        }
        $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        $userConfigGetRoute = CommonHelper::getUPICollectRouteUsingUserId($userId, 'api');
        if ($userConfigGetRoute['status']) {
            $types = $userConfigGetRoute['slug'];
            $integrationId = $userConfigGetRoute['integration_id'];
        } else {
            $route = CommonHelper::defaultUPICollectRoute('upi_collect_route');
            $types = $route['slug'];
            $integrationId = $route['integration_id'];
        }
        switch ($integrationId) {
            case 'int_1702712555':
                $header = [  
                    'IPAddress:127.0.0.1',
                    'AuthKey: 3fc68d538877adb234b5251bb068e56f3b2629e142ab06610c9d1cced2aea537',  
                    'content-type: application/json',  
                ];

                $requestType = 'collect';
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
                $modal = 'HUNTOOD PAY';
                $reqType = 'collect';
                $url = "https://app.huntood.com/api/DyupiV2/V4/GenerateUPI";
                $timestamp = microtime(true);
                $request['trxn_id']  = 'XTL' . round(($timestamp - floor($timestamp)) * 1000) . rand(11111111, 99999999);
                
                $parameter['MethodName'] = 'collectionrequest';
                $parameter['ReferenceId'] = $request->referenceId;
                $parameter['amount'] = $request->amount;
                $parameter['name'] = $request->name;
                $parameter['email'] = $request->email;
                $parameter['phone'] = $request->phone;
                $parameter['tax']   = 0.18;
                $parameter['fee']   = 0.10;
                $users = $userId;
                $PayinRate = DB::table('reseller_commission')->where('user_id', $users)->select('payin_rate')->first();
                $amount = $parameter['amount'];
                // $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($modal, $amount, $users);
                // dd($getFeesAndTaxes);
                $AddAmount = $amount + $parameter['tax'] + $parameter['fee'];
                $payinFinalAmount = $AddAmount * ($PayinRate->payin_rate / 100);
                
                // $result = CommonHelper::curl($url, "POST", json_encode($parameter), $header, 'yes', $userId, $modal, 'post', $reqType);
                $response = [
                    
                    "responseCode" => 200,
                    "status" => true,
                    "message" => "SUCCESS",
                    "data"=> [
                        "qr" => "upi://pay?ver=01&mode=15&am=200.00&cu=INR&pa=ftdaddy.fantasysports@timecosmos&pn=FANTASY SPORTS MYFAB11 PRIVATE LIMITED&mc=5816&tr=FTDADDY111402627337&mid=FINTE9520&msid=FANTA-8957&mtid=FANTA-8957",
                        "walletTransactionId" => "DIGW77242030120299314760",
                        "userTrasnactionId" => 'TXN' . mt_rand(11111, 999999),
                        "status" => "SUCCESS",
                        "statusMessage" => "SUCCESS",
                        "statusCode" => "200"
                    ]
                ];
               
                // $response = json_encode($result['response'], 1);
                if($response['status'] == true || $response['status'] == 'success')
                {
                    $upiCollect = new UPICollect;
                    $upiCollect->user_id = $userId;
                    $upiCollect->txn_note = !empty($response['message']) ? $response['message'] : " ";
                    $upiCollect->amount = !empty($request->amount) ? $request->amount : '';
                    $upiCollect->resp_code = !empty($response['status']) ? $response['status'] : " ";
                    $upiCollect->description = !empty($response['message']) ? $response['message'] : " ";
                    $upiCollect->payee_vpa = " ";
                    $upiCollect->customer_ref_id = !empty($request->referenceId) ? $request->referenceId : " ";
                    $upiCollect->merchant_txn_ref_id = !empty($request->referenceId) ? $request->referenceId : " ";
                    $upiCollect->txn_id = isset($txnId) ? $txnId : " ";
                    $upiCollect->original_order_id =  isset($txnId) ? $txnId : " ";
                    $upiCollect->bank_txn_id = isset($request->trxn_id) ? $request->trxn_id : " ";
                    $upiCollect->payer_vpa =  " ";
                    $upiCollect->fee = $parameter['fee'];
                    $upiCollect->tax = $parameter['tax'];
                    $upiCollect->upi_txn_id = isset($response['data']['userTrasnactionId']) ? $response['data']['userTrasnactionId'] : " ";
                    $upiCollect->integration_id = "int_1702712555";
                    $upiCollect->status = "pending";
                    $upiCollect->reseller_commision	 = isset($payinFinalAmount) ? $payinFinalAmount : " ";
                    $upiCollect->save();
                    /* End */
                    $data = [
                        'qr_intent' => !empty($response['data']['qr']) ? $response['data']['qr'] : '',
                        // 'payment_url' => !empty($response['upi_string_image']) ? $response['upi_string_image'] : '',

                    ];
                        $statuscode = 200;
                        $message = "QR generated";
                        $success = true;
                    } else {
                        $statuscode = 500;
                        $message = "Failed to generate QR";
                        $success = false;
                        $data = null;
                    }
                    $resp = [
                        'statuscode' => $statuscode,
                        'status' => $success,
                        'data' => $data,
                        'message' => $message,
                        'errors' => null,
                        'exception' => null
                    ];
               
                return response()->json($resp);
            break;

        }
    }

    public function generateHash($token, $mid, $uttxnid)
    {
        $string = $token . ',' . $mid . ',' . $uttxnid;
        return hash("sha512", $string);
    }
    /**
     * Create Sub Merchant function
     *
     * @param Request $request
     * @return void
     */
    public function merchant(Request $request)
    {
        try {
            $validation = new Validations($request);
            $validator = $validation->merchant();
            if ($validator->fails()) {
                return response()->json([
                    'statuscode' => "0x0100",
                    'message' => json_decode(json_encode($validator->errors()), true),
                    'status' => $this::ERROR_STATUS,
                    'data' => null,
                    'errors' => null,
                    'exception' => null,
                ]);
            }
            $merchantTxnRefId = $this->getTxnId();
            $mcc = '7299';

            if (empty($request->merchantGenre)) {
                $merchantGenre = 'OFFLINE';
            } else {
                $merchantGenre = strtoupper($request->merchantGenre);
            }

            $params = [
                'action' => 'C',
                'merchantBussiessName' => $request->merchantBusinessName,
                'merchantVirtualAddress' => strtolower($request->merchantVirtualAddress) . 'xettle',
                'requestUrl1' => 'https://app.xettle.io/api/callbacks/yesbank',
                'panNo' => $request->panNo,
                'contactEmail' => $merchantTxnRefId . '@email.com',
                'gstn' => $request->gstn ? $request->gstn : '',
                'merchantBussinessType' => $request->merchantBusinessType ? $request->merchantBusinessType : '',
                'perDayTxnCount' => $request->perDayTxnCount ? $request->perDayTxnCount : '',
                'perDayTxnLmt' => $request->perDayTxnLmt ? $request->perDayTxnLmt : '',
                'perDayTxnAmt' => $request->perDayTxnAmt ? $request->perDayTxnAmt : '',
                'mobile' => '9999999999',
                'address' => $request->address ? $request->address : '',
                'state' => $request->state ? $request->state : '',
                'city' => $request->city ? $request->city : '',
                'pinCode' => $request->pinCode ? $request->pinCode : '',
                'subMerchantId' => '',
                'merchantTrxnRefId' => $merchantTxnRefId,
                'mcc' => $mcc, //$request->mcc ? $request->mcc : ''
                'merchantGenre' => $merchantGenre,
            ];
            $requestType = 'merchant';
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }
            $modal = 'fidypay';
            $reqType = 'addMerchant';
            $response = $this->UPICaller($params, $requestType, $userId, $modal, $reqType);
            if (isset($response['code'])) {
                if ($response['code'] === "0x0200") {
                    $merchant = new UPIMerchant;
                    $merchant->user_id = $userId;
                    $merchant->merchant_business_name = $response['merchantBussinessName'];
                    $merchant->merchant_virtual_address = $response['merchantVirtualAddress'];
                    $merchant->request_url = $request->requestUrl;
                    $merchant->pan_no = $request->panNo;
                    $merchant->contact_email = $request->contactEmail;
                    $merchant->gstn = $request->gstn;
                    $merchant->merchant_business_type = $request->merchantBusinessType;
                    $merchant->per_day_txn_count = $request->perDayTxnCount;
                    $merchant->per_day_txn_lmt = $request->perDayTxnLmt;
                    $merchant->per_day_txn_amt = $request->perDayTxnAmt;
                    $merchant->mobile = $request->mobile;
                    $merchant->address = $request->address;
                    $merchant->state = $request->state;
                    $merchant->city = $request->city;
                    $merchant->pin_code = $request->pinCode;
                    $merchant->sub_merchant_id = $response['subMerchantId'];
                    $merchant->merchant_txn_ref_id = $merchantTxnRefId;
                    $merchant->mcc = $mcc; //$request->mcc;
                    $merchant->request_id = $response['requestId'];
                    $merchant->crt_date = $response['crtDate'];
                    $merchant->save();
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
                    $this->message = "Merchant added successfully."; //$response['description'];
                    $status = $this::SUCCESS_STATUS;


                    $dataForDispatch['id'] = $merchant->id;
                    $dataForDispatch['user_id'] = $userId;
                    $dataForDispatch['identifier'] = 'upi_stack_vpa_fee';
                    $dataForDispatch['type'] = 'upi_collect';
                    $dataForDispatch['slug'] = 'upi_create';
                    $dataForDispatch['merchant_txn_ref_id'] = $merchantTxnRefId;

                    //apply VPA creation charges
                    PrimaryFundCredit::dispatch((object) $dataForDispatch, 'upi_stack_creation_fee')->onQueue('primary_fund_queue');
                } elseif ($response['code'] === "0x0201") {
                    $code = $response['code'];
                    $this->message = 'Something went wrong please try again';
                    $status = $this::FAILED_STATUS;
                } else {
                    $code = $response['code'];
                    $this->message = $response['description'];
                    $status = $this::FAILED_STATUS;
                }
            }
            return response()->json([
                'statuscode' => $code,
                'message' => $message,
                'status' => $status,
                'data' => $data,
                'errors' => null,
                'exception' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'statuscode' => "0x0202",
                'message' => "Error: " . $e->getMessage(),
                'status' => $this::FAILED_STATUS,
                'data' => null,
                'errors' => null,
                'exception' => $e->getMessage(),
            ]);
        }
    }



    /**
     * Generate Dynamic QR Code 
     */
    public function generateDynamicQrCode(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vpaAddress' => 'required|min:3',
                'amount' => 'required|numeric|min:1',
                'referenceId' => 'nullable|alpha_num|min:3',
                'note' => 'nullable|min:3'
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing('Some params are missing.', $message);
        }


        try {

            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }

            $url = "https://ibrpay.com/api/UPICollection.aspx";


            $parameter['APIID'] = 'API1006';
            $parameter['Token'] = 'fe06f96f-95f5-4a36-8d7f-0de71bff8b54';
            $parameter['MethodName'] = 'collectionrequest';
            $parameter['client_txn_id'] = '';
            $parameter['amount'] = '';
            $parameter['customer_name'] = '';
            $parameter['customer_email'] = '';
            $parameter['customer_mobile'] = '';

            $header = ["Content-Type: application/json"];
            $result = CommonHelper::curl($url, "POST", json_encode($parameter), $header, 'yes', $userId, 'IBRpay', 'post');
            $response = json_decode($result['response'], 1);
            // dd($response);
            return ResponseHelper::success("QR Code generated successfully", ['qrCode' => $qrCode]);
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }


    /**
     * Generate Dynamic QR Code 
     */
    public function generateStaticQrCode(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vpaAddress' => 'required|min:3',
                'referenceId' => 'nullable|alpha_num|min:3',
                'note' => 'nullable|min:3'
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing('Some params are missing.', $message);
        }


        try {

            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }

            //check VPA address is available in merchant table or not
            $merchantInfo = DB::table('upi_merchants')->select('id', 'merchant_virtual_address', 'merchant_business_name')
                ->where('merchant_virtual_address', strtolower($request->vpaAddress))
                ->where('user_id', $userId)
                ->first();

            if (empty($merchantInfo)) {
                return ResponseHelper::failed("VPA address is not found.");
            }


            $merchant_business_name = rawurlencode($merchantInfo->merchant_business_name);

            if (empty($request->referenceId)) {
                $referenceId = '&tr=' . CommonHelper::getRandomString('USTS', false);
            } else {
                $referenceId = '&tr=' . rawurlencode($request->referenceId);
            }

            if (empty($request->note)) {
                $note = '&tn=' . rawurlencode('via XETTLE');
            } else {
                $note = '&tn=' . rawurlencode($request->note);
            }

            $qrCode = "upi://pay?pa={$merchantInfo->merchant_virtual_address}&pn={$merchant_business_name}{$referenceId}{$note}";

            $qrCode = "data:image/png;base64," . base64_encode(QrCode::margin(1)->format('png')->size(320)->generate($qrCode));

            return ResponseHelper::success("QR Code generated successfully", ['qrCode' => $qrCode]);
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }


    /**
     * transaction status check function
     *
     * @param Request $request
     * @return void
     */
    public function status(Request $request, $txnId)
    {
        if (!$txnId) {
            $resp['statuscode']       = "0x0100";
            $resp['message']    = "UPI txnId required.";
            $resp['status']     = $this::ERROR_STATUS;
            return response()->json($resp);
        }

        $params = [
            'txnId' => $txnId
        ];

        $requestType = 'status';
        $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        $modal = 'fidypay';
        $reqType = 'statusCheck';
        // $reportUpi = \DB::table('upi_collects')->where('customer_ref_id', $txnId)->first();
        $reportUpi = \DB::table('upi_collects')->where('customer_ref_id', $txnId)
        ->select('merchant_txn_ref_id','bank_txn_id','amount','resp_code','description','upi_txn_id', 'status','fee', 'tax','reseller_commision','is_trn_credited','created_at', 'updated_at')->first();
        if (!$reportUpi) {
            $resp = [
                'statuscode' => 500,
                'status' => false,
                'data' => null,
                'message' => "Data Not Found",
                'errors' => null,
                'exception' => null,
            ];

            return response()->json($resp);
        }
        $data = $reportUpi;
        $resp = [
            'statuscode' => 200,
            'success' => true,
            'data' => $data,
            'message' => "Data fetched",
            'errors' => null,
            'exception' => null,
        ];

        return response()->json($resp);
    }

    public function verify(Request $request, $vpa)
    {
        if (!$vpa) {
            return response()->json([
                'statuscode' => "0x0100",
                'message' => "VPA field is required.",
                'status' => $this::ERROR_STATUS,
                'data' => null,
                'errors' => null,
                'exception' => null,
            ]);
        }

        $params = [
            'vpa' => $vpa
        ];

        $requestType = 'verify';
        $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        $modal = 'fidypay';
        $reqType = 'verifyVPA';
        $response = $this->UPICaller($params, $requestType, $userId, $modal, $reqType);

        if (isset($response['code']) && $response['code'] != "0x0203") {
            if ($response['code'] === "0x0200") {
                $data = [
                    'maskName' => $response['maskName'],
                    'bankTxnId' => $response['bankTrxnId'],
                    'virtualAddress' => $response['virtualAddress'],
                    'status' => $response['status'],
                ];

                if (isset($response['statusDescription'])) {
                    $description = $response['statusDescription'];
                }

                if (isset($response['description'])) {
                    $description = $response['description'];
                }

                $code = $response['code'];
                $this->message = $description;
                $status = $this::SUCCESS_STATUS;


                //insert request record
                $dataInsert = [
                    'user_id' => $userId,
                    'vpa' => $vpa,
                    'request_id' => CommonHelper::getRandomString('VPFY', false),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $insertId = DB::table('upi_verify_requests')->insertGetId($dataInsert);

                $dataForDispatch['id'] = $insertId;
                $dataForDispatch['user_id'] = $userId;
                $dataForDispatch['identifier'] = 'upi_stack_verify_fee';
                $dataForDispatch['type'] = 'upi_collect';
                $dataForDispatch['slug'] = 'upi_verify';
                $dataForDispatch['request_id'] = $dataInsert['request_id'];

                //apply VPA creation charges
                PrimaryFundCredit::dispatch((object) $dataForDispatch, 'upi_stack_verify_fee')->onQueue('primary_fund_queue');
            } elseif ($response['code'] === "0x0201") {
                $code = $response['code'];
                $this->message = 'Something went wrong please try again';
                $status = $this::FAILED_STATUS;
            } else {
                $code = $response['code'];
                $this->message = $response['description'];
                $status = $this::FAILED_STATUS;
            }
        }
        if (isset($code)) {
            $resp['code']       = $code;
            $resp['status']     = $status;
            $resp['message']    = $this->message;
        } else {
            $resp['code']       = "0x0201";
            $resp['status']     = $this::FAILED_STATUS;
            $resp['message']    = 'Something went wrong please try again';
        }


        // if (isset($data)) {
        //     $resp['data']   = $data;
        // }
        return response()->json([
            'statuscode' => $code,
            'message' => $message,
            'status' => $status,
            'data' => $data,
            'errors' => null,
            'exception' => null,
        ]);

        // return response()->json($resp);
    }

    public function getTxnId()
    {
        return CommonHelper::getRandomString();
    }

    public function getIV($digits = 3)
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }
   public function UPICaller($params, $requestType, $userId, $modal, $reqType, $header = '')
    {
        $method = 'POST';
        switch ($requestType) {
            case 'collect':
                $request = $params;
                $this->header = $header;
                $url = "https://indicpay.in/api/upi/fetch_qr";
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
                $url = $this->baseUrl . '/checkVirtualAddress/' . $params['vpa'];
                break;
            case 'statuscheck':
                $request = $params;
                $url = 'https://ibrpay.com/API/GetAmount.aspx';
                $method = 'GET';
                $this->header = ["Content-Type: application/json"];
                break;
        }

        $result = CommonHelper::curl($url, $method, json_encode($request), $this->header, 'yes', $userId, $modal, $reqType);
        $response = json_decode($result['response'], 1);
        return $response;
    }

    public function deleteMerchant(Request $request, $vpa)
    {
        $merchantTxnRefId = $this->getTxnId();
        $mcc = '7299';
        $code = "0x0201";
        if (empty($request->merchantGenre)) {
            $merchantGenre = 'OFFLINE';
        } else {
            $merchantGenre = strtoupper($request->merchantGenre);
        }
        if (is_numeric($vpa)) {
            $merchant = UPIMerchant::where('user_id', $vpa)->where('is_active', 1)->limit(100)->orderBy('id', 'desc')->get();
        } else {
            $merchant = UPIMerchant::where('merchant_virtual_address', $vpa)->where('is_active', 1)->orderBy('id', 'desc')->get();
        }

        if (!empty($merchant)) {
            foreach ($merchant as $val) {

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
                if (isset($request->auth_data['user_id'])) {
                    $userId = $request->auth_data['user_id'];
                } else {
                    $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
                }
                $modal = 'fidypay';
                $reqType = 'deleteMerchant';
                $response = $this->UPICaller($params, $requestType, $userId, $modal, $reqType);
                if (isset($response['code'])) {
                    if ($response['code'] === "0x0200") {
                        $merchantU = UPIMerchant::where('id', $val->id)->update(array('is_active' => 0));

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
                        $this->message = "Merchant deleted successfully."; //$response['description'];
                        $status = $this::SUCCESS_STATUS;
                    } elseif ($response['code'] === "0x0201") {
                        $code = $response['code'];
                        $this->message = 'Something went wrong please try again';
                        $status = $this::FAILED_STATUS;
                    } else {
                        $code = $response['code'];
                        $this->message = $response['description'];
                        $status = $this::FAILED_STATUS;
                    }
                }

                $resp['code']       = $code;
                $resp['message']    = $this->message;
                $resp['status']     = isset($status) ? $status : $this::FAILED_STATUS;
                if (isset($data)) {
                    $resp['data']   = $data;
                }
                //return response()->json($resp);
            }
        } else {
            $this->message = 'No record found.';
        }
        return response()->json($resp);
    }
}
