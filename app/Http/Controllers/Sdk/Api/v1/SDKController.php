<?php

namespace App\Http\Controllers\Sdk\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Validations\SDKAepsValidation as Validations;
use App\Helpers\CommonHelper;
use App\Helpers\UATResponse;
use App\Helpers\ResponseHelper as Response;
use App\Helpers\TransactionHelper;
use App\Models\AepsTransaction;
use App\Models\Bank;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SDKController extends Controller
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
        $this->baseUrl = env('AEPS_BASE_URL');
        $this->key = base64_decode(env('AEPS_SECURITY_KEY'));
        $this->header = array("securitykey:" . $this->key, "Content-Type:application/json");
    }

    /**
     * Get init function
     *
     * @param Request $request
     * @return object
     */
    public function init(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->init();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                $merchantExits = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])
                    ->select('is_active')->first();
                if (empty($merchantExits)) {
                    $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                } else {
                    if ($merchantExits->is_active == 0) {
                        $validator->errors()->add('merchantCode', 'This merchant is inactive.');
                    } elseif ($merchantExits->is_active == 2) {
                        $validator->errors()->add('merchantCode', 'Please upload kyc docs.');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {
            if (!empty($request['ekycList'])) {

                $merchant = Agent::select(
                    'first_name',
                    'middle_name',
                    'last_name',
                    'mobile',
                    'email_id as emailId',
                    'address',
                )
                    ->where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])
                    ->first();

                $data['merchantStatus'] = "active";
                $fullName = $merchant->first_name;
                if (!empty($merchant->middle_name)) {
                    $fullName .= ' ' . $merchant->middle_name;
                }
                if (!empty($merchant->last_name)) {
                    $fullName .= ' ' . $merchant->last_name;
                }
                $data['merchantName'] = $fullName;
                $data['merchantCode'] = $request->merchantCode;
                $data['allowedRoutes'] = $request['ekycList'];
                $message = "Record fetched successfully.";
                $resp = Response::success($message, $data, 200);
            } else {
                $message = "No ekyc details found.";
                $resp = Response::failed($message, []);
            }
            return $resp;
        }
    }
    /**
     * Get Balance function
     *
     * @param Request $request
     * @return void
     */
    public function getBalance(Request $request)
    {

        if (!isset($request->ip) || empty($request->ip)) {
            $header = $request->header();
            $request->request->add(['ip' => isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip()]);
        }
        $validation = new Validations($request);
        $validator = $validation->getBalance();
        $validator->after(function ($validator) use ($request) {
            $batm_user_id =  (array) env('BATM_USER_ID');
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                if (empty($merchantExists)) {
                    $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                } else if (!in_array($request['auth_data']['user_id'], $batm_user_id)) {
                    $kycStatus = self::ekycStatusOfMerchant($request['auth_data']['user_id'], $merchantCode,  $request->routeType);
                    if ($kycStatus['ekycStatus'] == 1) {
                        if ($merchantExists->is_active == 0) {
                            $validator->errors()->add('merchantCode', 'This merchant is inactive.');
                        } elseif ($merchantExists->is_active == 2) {
                            $validator->errors()->add('merchantCode', 'Please upload kyc docs.');
                        }
                    } else {
                        $validator->errors()->add('merchantCode', $kycStatus['message']);
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {

            if (!empty($request->clientRefId) || !empty($request->clientRefNo)) {
                $clientrefno =  !empty($request->clientRefId) ? $request->clientRefId : $request->clientRefNo;

                if (DB::table('aeps_transactions')
                    ->where('client_ref_id', $clientrefno)
                    ->count()
                ) {
                    return Response::failed('The client ref id should be unique');
                }
            } else {
                $clientrefno =  CommonHelper::getRandomString('ABE', false);
            }


            $params = [
                'merchantcode'  => $request->merchantCode,
                'aadharnumber'  => $request->aadhaarNo,
                'rdrequest'     => $request->rdRequest,
                'mobile'        => $request->mobile,
                'ip'            => $request->ip,
                'clientrefno'   => $clientrefno,
                'bankiin'       => $request->bankiin,
                'lattitude'     => $request->latitude,
                'longitude'     => $request->longitude,
                'routetype'     => $request->routeType,
            ];


            $requestType = 'getBalance';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                $data = [];
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                    $data = self::getResponse($response, $clientrefno);
                    $response['data']['failed_message'] = isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : $response['message'];
                    if ($response['statuscode'] === "000") {
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "000", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $message = $statusCode . ": Balance fetched successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "002") {
                        $message = $response['statuscode'] . ": Balance enquiry is pending.";
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $resp = Response::pending($message, $data);
                    } else if ($response['statuscode'] === "006") {
                        $message = $response['statuscode'] . ": Please do 2 factor auth.";
                        $resp = Response::twoFactAuth($message, $data);
                    } else {
                        $message = $statusCode . ': ' . $response['message'];
                        $resp = Response::failed($message, $data);
                    }
                } else {
                    $message = "Something went wrong";
                    $resp = Response::swwrong($message);
                }
            } else {
                $resp = $returnResp;
            }
        }
        return $resp;
    }

    /**
     * Withdrawal function
     *
     * @param Request $request
     * @return void
     */
    public function withdrawal(Request $request)
    {

        if (!isset($request->ip) || empty($request->ip)) {
            $header = $request->header();
            $request->request->add(['ip' => isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip()]);
        }

        $validation = new Validations($request);
        $validator = $validation->withdrawal();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                $batm_user_id =  (array) env('BATM_USER_ID');
                if (empty($merchantExists)) {
                    $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                } else if (!in_array($request['auth_data']['user_id'], $batm_user_id)) {
                    $kycStatus = self::ekycStatusOfMerchant($request['auth_data']['user_id'], $merchantCode,  $request->routeType);
                    if ($kycStatus['ekycStatus'] == 1) {
                        if ($merchantExists->is_active == 0) {
                            $validator->errors()->add('merchantCode', 'This merchant is inactive.');
                        } elseif ($merchantExists->is_active == 2) {
                            $validator->errors()->add('merchantCode', 'Please upload kyc docs.');
                        }
                    } else {
                        $validator->errors()->add('merchantCode', $kycStatus['message']);
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {

            if (!empty($request->clientRefId) || !empty($request->clientRefNo)) {
                $clientrefno =  !empty($request->clientRefId) ? $request->clientRefId : $request->clientRefNo;

                if (DB::table('aeps_transactions')
                    ->where('client_ref_id', $clientrefno)
                    ->count()
                ) {
                    return Response::failed('The client ref id should be unique');
                }
            } else {
                $clientrefno =  CommonHelper::getRandomString('ACW', false);
            }

            $params = [
                'merchantcode'  => $request->merchantCode,
                'amount'        => $request->amount,
                'aadharnumber'  => $request->aadhaarNo,
                'rdrequest'     => $request->rdRequest,
                'mobile'        => $request->mobile,
                'ip'            => $request->ip,
                'bankiin'       => $request->bankiin,
                'clientrefno'   => $clientrefno,
                'lattitude'     => $request->latitude,
                'longitude'     => $request->longitude,
                'routetype'     => $request->routeType,
            ];
            $requestType = 'withdrawal';

            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                    $data = self::getResponse($response, $clientrefno);
                    $response['data']['failed_message'] = isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : $response['message'];
                    if ($response['statuscode'] === "000") {
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "000", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $message = $statusCode . ": Transaction is successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] == "002") {
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $message = $response['statuscode'] . ": Transaction is pending.";
                        $resp = Response::pending($message, $data);
                    } else if ($response['statuscode'] === "006") {

                        $resp['transactiontype'] = 'cw';
                        $resp['transactionAmount'] = $request->amount;
                        $resp['merchantmobile'] = $request->mobile;
                        $resp['bankiin'] = $request->bankiin;
                        self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "001", $request->merchantCode, $request->routeType, $request->aadhaarNo);

                        $data = [
                            "clientRefNo" => $clientrefno,
                            "routeType" => CommonHelper::case($request->routeType, 'u'),
                            "bankiin" => $request->bankiin,
                            "stanNo" => "",
                            "rrn" => "",
                            "bankMessage" => "",
                            "bankCode" => "",
                            "statusCode" => "",
                            "merchantCode" => $request->merchantCode,
                            "aadhaarNumber" => "$request->aadhaarNo",
                            "transactionType" => "CW",
                            "transactionDateTime" => "",
                            "transactionAmount" => $request->amount,
                            "availableBalance" => "0",
                        ];

                        $message = $response['statuscode'] . ": Please do 2 factor auth.";
                        $resp = Response::twoFactAuth($message, $data);
                    } else if ($response['statuscode'] == "999") {

                        $resp['transactiontype'] = 'cw';
                        $resp['transactionAmount'] = $request->amount;
                        $resp['merchantmobile'] = $request->mobile;
                        $resp['bankiin'] = $request->bankiin;
                        self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo);
                        $message = "Transaction is pending.";
                        $data = [
                            "clientRefNo" => $clientrefno,
                            "routeType" => CommonHelper::case($request->routeType, 'u'),
                            "bankiin" => $request->bankiin,
                            "stanNo" => "",
                            "rrn" => "",
                            "bankMessage" => "",
                            "bankCode" => "",
                            "statusCode" => "",
                            "merchantCode" => $request->merchantCode,
                            "aadhaarNumber" => "$request->aadhaarNo",
                            "transactionType" => "CW",
                            "transactionDateTime" => "",
                            "transactionAmount" => $request->amount,
                            "availableBalance" => "0",
                        ];
                        $message = $response['statuscode'] . ": Transaction is pending.";
                        $resp = Response::pending($message, $data);
                    } else {
                        $message = $statusCode . ': ' . $response['message'];
                        if (isset($response['data']['transactiontype']) && !empty($response['data']['transactiontype'])) {
                            $resp = $response['data'];
                        } else {
                            $resp['transactiontype'] = 'cw';
                            $resp['transactionAmount'] = $request->amount;
                            $resp['merchantmobile'] = $request->mobile;
                            $resp['bankiin'] = $request->bankiin;
                            $resp['failed_message'] = $statusCode . ': ' . $response['message'];
                        }


                        if ($message == '001: {*} Client Reference should be unique.') {
                            $message = $response['statuscode'] . ": Transaction is pending.";
                            self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                            $resp = Response::pending($message, $data);
                        } else {
                            self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "001", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                            $resp = Response::failed($message, $data);
                        }
                    }
                } else {
                    $resp['transactiontype'] = 'cw';
                    $resp['transactionAmount'] = $request->amount;
                    $resp['merchantmobile'] = $request->mobile;
                    $resp['bankiin'] = $request->bankiin;
                    self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo);
                    $message = "Transaction is pending.";
                    $data = [
                        "clientRefNo" => $clientrefno,
                        "routeType" => CommonHelper::case($request->routeType, 'u'),
                        "bankiin" => $request->bankiin,
                        "stanNo" => "",
                        "rrn" => "",
                        "bankMessage" => "",
                        "bankCode" => "",
                        "statusCode" => "",
                        "merchantCode" => $request->merchantCode,
                        "aadhaarNumber" => "$request->aadhaarNo",
                        "transactionType" => "CW",
                        "transactionDateTime" => "",
                        "transactionAmount" => $request->amount,
                        "availableBalance" => "0",
                    ];
                    $resp = Response::pending($message, $data);
                }
            } else {
                $resp = $returnResp;
            }
        }
        return $resp;
    }

    /**
     *  aadharPay function
     *
     * @param Request $request
     * @return void
     */
    public function aadhaarPay(Request $request)
    {

        if (!isset($request->ip) || empty($request->ip)) {
            $header = $request->header();
            $request->request->add(['ip' => isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip()]);
        }

        $validation = new Validations($request);
        $validator = $validation->aadhaarPay();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                $batm_user_id =  (array) env('BATM_USER_ID');
                if (empty($merchantExists)) {
                    $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                } else if (!in_array($request['auth_data']['user_id'], $batm_user_id)) {
                    $kycStatus = self::ekycStatusOfMerchant($request['auth_data']['user_id'], $merchantCode,  $request->routeType);
                    if ($kycStatus['ekycStatus'] == 1) {
                        if ($merchantExists->is_active == 0) {
                            $validator->errors()->add('merchantCode', 'This merchant is inactive.');
                        } elseif ($merchantExists->is_active == 2) {
                            $validator->errors()->add('merchantCode', 'Please upload kyc docs.');
                        }
                    } else {
                        $validator->errors()->add('merchantCode', $kycStatus['message']);
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {
            if (!empty($request->clientRefId) || !empty($request->clientRefNo)) {
                $clientrefno =  !empty($request->clientRefId) ? $request->clientRefId : $request->clientRefNo;

                if (DB::table('aeps_transactions')
                    ->where('client_ref_id', $clientrefno)
                    ->count()
                ) {
                    return Response::failed('The client ref id should be unique');
                }
            } else {
                $clientrefno =  CommonHelper::getRandomString('AAP', false);
            }

            $params = [
                'merchantcode'  => $request->merchantCode,
                'amount'        => $request->amount,
                'aadharnumber'  => $request->aadhaarNo,
                'rdrequest'     => $request->rdRequest,
                'mobile'        => $request->mobile,
                'ip'            => $request->ip,
                'bankiin'       => $request->bankiin,
                'clientrefno'   => $clientrefno,
                'lattitude'     => $request->latitude,
                'longitude'     => $request->longitude,
                'routetype'     => $request->routeType,
            ];

            $requestType = 'aadhaarPay';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                if (isset($response['statuscode'])) {

                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                    $data = self::getResponse($response, $clientrefno);
                    $response['data']['failed_message'] = isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : $response['message'];
                    if ($response['statuscode'] === "000") {
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "000", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $message = $statusCode . ": AadhaarPay transfer successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] == "002") {
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $message = $response['statuscode'] . ": AadhaarPay transfer is pending.";
                        $resp = Response::pending($message, $data);
                    } else if ($response['statuscode'] === "006") {

                        $resp['transactiontype'] = 'ap';
                        $resp['transactionAmount'] = $request->amount;
                        $resp['merchantmobile'] = $request->mobile;
                        $resp['bankiin'] = $request->bankiin;
                        self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "001", $request->merchantCode, $request->routeType, $request->aadhaarNo);

                        $data = [
                            "clientRefNo" => $clientrefno,
                            "routeType" => CommonHelper::case($request->routeType, 'u'),
                            "bankiin" => $request->bankiin,
                            "stanNo" => "",
                            "rrn" => "",
                            "bankMessage" => "",
                            "bankCode" => "",
                            "statusCode" => "",
                            "merchantCode" => $request->merchantCode,
                            "aadhaarNumber" => "$request->aadhaarNo",
                            "transactionType" => "AP",
                            "transactionDateTime" => "",
                            "transactionAmount" => $request->amount,
                            "availableBalance" => "0",
                        ];


                        $message = $response['statuscode'] . ": Please do 2 factor auth.";
                        $resp = Response::twoFactAuth($message, $data);
                    } else if ($response['statuscode'] == "999") {
                        $resp['transactiontype'] = 'ap';
                        $resp['transactionAmount'] = $request->amount;
                        $resp['merchantmobile'] = $request->mobile;
                        $resp['bankiin'] = $request->bankiin;
                        self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo);
                        $message = "AadhaarPay transfer is pending.";
                        $data = [
                            "clientRefNo" => $clientrefno,
                            "routeType" => CommonHelper::case($request->routeType, 'u'),
                            "bankiin" => $request->bankiin,
                            "stanNo" => "",
                            "rrn" => "",
                            "bankMessage" => "",
                            "bankCode" => "",
                            "statusCode" => "",
                            "merchantCode" => $request->merchantCode,
                            "aadhaarNumber" => "$request->aadhaarNo",
                            "transactionType" => "AP",
                            "transactionDateTime" => "",
                            "transactionAmount" => $request->amount,
                            "availableBalance" => "0",
                        ];
                        $resp = Response::pending($message, $data);
                    } else {
                        if (isset($response['data']['transactiontype']) && !empty($response['data']['transactiontype'])) {
                            $resp = $response['data'];
                        } else {
                            $resp['transactiontype'] = 'AP';
                            $resp['transactionAmount'] = $request->amount;
                            $resp['merchantmobile'] = $request->mobile;
                            $resp['bankiin'] = $request->bankiin;
                            $resp['failed_message'] = $statusCode . ': ' . $response['message'];
                        }
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "001", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $message = $statusCode . ': ' . $response['message'];
                        $resp = Response::failed($message, $data);
                    }
                } else {
                    $resp['transactiontype'] = 'ap';
                    $resp['transactionAmount'] = $request->amount;
                    $resp['merchantmobile'] = $request->mobile;
                    $resp['bankiin'] = $request->bankiin;
                    self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo);
                    $message = "AadhaarPay transfer is pending";
                    $data = [
                        "clientRefNo" => $clientrefno,
                        "routeType" => CommonHelper::case($request->routeType, 'u'),
                        "bankiin" => $request->bankiin,
                        "stanNo" => "",
                        "rrn" => "",
                        "bankMessage" => "",
                        "bankCode" => "",
                        "statusCode" => "",
                        "merchantCode" => $request->merchantCode,
                        "aadhaarNumber" => "$request->aadhaarNo",
                        "transactionType" => "AP",
                        "transactionDateTime" => "",
                        "transactionAmount" => $request->amount,
                        "availableBalance" => "0",
                    ];
                    $resp = Response::pending($message, $data);
                }
            } else {
                $resp = $returnResp;
            }
        }
        return $resp;
    }

    /**
     * Get Balance function
     *
     * @param Request $request
     * @return void
     */
    public function statement(Request $request)
    {
        if (!isset($request->ip) || empty($request->ip)) {
            $header = $request->header();
            $request->request->add(['ip' => isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip()]);
        }

        if (!isset($request->ip) || empty($request->ip)) {
            $header = $request->header();
            $request->ip = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
        }

        $validation = new Validations($request);
        $validator = $validation->statement();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                $batm_user_id =  (array) env('BATM_USER_ID');
                if (empty($merchantExists)) {
                    $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                } else if (!in_array($request['auth_data']['user_id'], $batm_user_id)) {
                    $kycStatus = self::ekycStatusOfMerchant($request['auth_data']['user_id'], $merchantCode,  $request->routeType);
                    if ($kycStatus['ekycStatus'] == 1) {
                        if ($merchantExists->is_active == 0) {
                            $validator->errors()->add('merchantCode', 'This merchant is inactive.');
                        } elseif ($merchantExists->is_active == 2) {
                            $validator->errors()->add('merchantCode', 'Please upload kyc docs.');
                        }
                    } else {
                        $validator->errors()->add('merchantCode', $kycStatus['message']);
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {


            if (!empty($request->clientRefId) || !empty($request->clientRefNo)) {
                $clientrefno =  !empty($request->clientRefId) ? $request->clientRefId : $request->clientRefNo;

                if (DB::table('aeps_transactions')
                    ->where('client_ref_id', $clientrefno)
                    ->count()
                ) {
                    return Response::failed('The client ref id should be unique');
                }
            } else {
                $clientrefno =  CommonHelper::getRandomString('AMS', false);
            }

            $params = [
                'merchantcode'  => $request->merchantCode,
                'aadharnumber'  => $request->aadhaarNo,
                'rdrequest'     => $request->rdRequest,
                'mobile'        => $request->mobile,
                'ip'            => $request->ip,
                'bankiin'       => $request->bankiin,
                'clientrefno'   => $clientrefno,
                'lattitude'     => $request->latitude,
                'longitude'     => $request->longitude,
                'routetype'     => $request->routeType,
            ];
            $requestType = 'statement';
            $clientrefno = $request->clientRefId;
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                    $data = self::getResponse($response, $clientrefno);
                    $response['data']['failed_message'] = isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : $response['message'];
                    if ($response['statuscode'] === "000") {
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "000", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $message = $response['statuscode'] . ": Mini statement fetched successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "002") {
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo, $request->sdkVer, $request->reference);
                        $message = $response['statuscode'] . ": Mini statement is pending.";
                        $resp = Response::pending($message, $data);
                    } else if ($response['statuscode'] === "006") {
                        $message = $response['statuscode'] . ": Please do 2 factor auth.";
                        $resp = Response::twoFactAuth($message, $data);
                    } else {
                        $message = $response['statuscode'] . ': ' . $response['message'];;
                        $resp = Response::failed($message, $data);
                    }
                } else {
                    $message = "Something went wrong";
                    $resp = Response::swwrong($message);
                }
            } else {
                $resp = $returnResp;
            }
        }
        return $resp;
    }


    /**
     * Get twoFactAuth function
     *
     * @param Request $request
     * @return void
     */
    public function twoFactAuth(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->twoFactAuth();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                $batm_user_id =  (array) env('BATM_USER_ID');
                if (empty($merchantExists)) {
                    $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {

            $params = [
                'bcid' => $request->merchantCode,
                'aadharno' => $request->aadhaarNo,
                'fingdata' => $request->rdRequest,
                'mobile' => $request->mobile,
                'servicetype' => CommonHelper::case($request->serviceType, 'u'),
                'lattitude' => $request->latitude,
                'longitude' => $request->longitude
            ];

            $requestType = 'twoFactAuth';

            $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
            $data = [];
            if (isset($response['statuscode'])) {

                if ($response['statuscode'] === "000") {
                    $message = $response['statuscode'] . ": Validate successful.";
                    $resp = Response::success($message, $data, 200);
                } else {
                    $message = $response['statuscode'] . ': ' . $response['message'];
                    $resp = Response::failed($message, $data);
                }
            } else {
                $message = "Something went wrong";
                $resp = Response::swwrong($message);
            }
        }
        return $resp;
    }

    /**
     * Get Balance function
     *
     * @param Request $request
     * @return void
     */
    public function transactionStatus(Request $request)
    {

        $validation = new Validations($request);
        $validator = $validation->transactionStatus();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                $merchantExits = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                if (empty($merchantExits)) {
                    $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                } else {
                    if ($merchantExits->is_active == 0) {
                        $validator->errors()->add('merchantCode', 'This merchant is inactive.');
                    } elseif ($merchantExits->is_active == 2) {
                        $validator->errors()->add('merchantCode', 'Please upload kyc docs.');
                    }
                }
            }
            $clientRefIdCheck = AepsTransaction::where(['user_id' => $request['auth_data']['user_id'], 'client_ref_id' => $request->clientRefId])->first();
            if (empty($clientRefIdCheck)) {
                $validator->errors()->add('merchantCode', 'This client ref id is not valid.');
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {
            $params = [
                'refernceno'  => $request->clientRefId
            ];
            $requestType = 'statuscheck';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                    $data = self::getResponse($response, $request->clientRefId);
                    if ($response['statuscode'] === "000") {
                        $message = $response['statuscode'] . ": Transactions status fetched successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "002") {
                        $message = $response['statuscode'] . ": Transactions status is pending.";
                        $resp = Response::pending($message, $data);
                    } else {
                        $message = $response['statuscode'] . ': ' . $response['message'];;
                        $resp = Response::failed($message, $data);
                    }
                } else {
                    $message = "Something went wrong";
                    $resp = Response::swwrong($message);
                }
            } else {
                $resp = $returnResp;
            }
        }
        return $resp;
    }

    public function getTxnId()
    {
        return CommonHelper::getRandomString();
    }


    public function bankList()
    {

        $bank = Bank::select('id as bankId', 'iin', 'bank', 'url')->where('is_active', '1')->get();
        if (!empty($bank) && count($bank) > 0) {
            $message = "Record fetched successfully.";
            $resp = Response::success($message, $bank, 200);
        } else {
            $message = "No record found";
            $resp = Response::failed($message);
        }
        return $resp;
    }


    public function APICaller($params, $requestType, $userId)
    {
        $method = "POST";
        $txnId = isset($params['clientrefno']) ? $params['clientrefno'] : "";
        switch ($requestType) {

            case 'aepsMerchant':
                $request = $params;
                $url = $this->baseUrl . '/User/MerchantOnboard';
                break;

            case 'sendOtp':
                $request = $params;
                $url = $this->baseUrl . '/v2ekyc/sendotp';
                break;
            case 'validateOTP':
                $request = $params;
                $url = $this->baseUrl . '/v2ekyc/verifyotp';
                break;
            case 'resendOTP':
                $request = $params;
                $url = $this->baseUrl . '/v2ekyc/resendotp';
                break;
            case 'ekycBioMetric':
                $request = $params;
                $url = $this->baseUrl . '/v2ekyc/ekycbio';
                break;
            case 'getBalance':
                $request = $params;
                $url = $this->baseUrl . '/balanceInquiry/getBalance';
                break;
            case 'withdrawal':
                $request = $params;
                $url = $this->baseUrl . '/cashwithdrawal/withdrawal';
                break;
            case 'aadhaarPay':
                $request = $params;
                $url = $this->baseUrl . '/aadharpay/pay';
                break;
            case 'statement':
                $request = $params;
                $url = $this->baseUrl . '/ministatement/statement';
                break;
            case 'state':
                $request = $params;
                $method = "GET";
                $url = $this->baseUrl . '/common/GetState';
                break;
            case 'district':
                $request = $params['state_id'];
                $method = "GET";
                $url = $this->baseUrl . '/common/getdistrict?district=' . $request;
                break;
            case 'statuscheck':
                $request = $params;
                $url = $this->baseUrl . '/statuscheck/getstatus';
                break;
            case 'twoFactAuth':
                $request = $params;
                $url = $this->baseUrl . '/ValidateAadhar/Twofactauth';
                break;
        }
        $result = CommonHelper::curl($url, $method, json_encode($request), $this->header, 'yes', $userId, 'aeps', $requestType, $txnId);
        $response = json_decode($result['response'], 1);
     
        return $response;
    }

    public static function getResponse($response, $clientRefNo)
    {
        $data = [];
        if (isset($response['data']['bankcode']) && isset($response['data']['bankmessage'])) {
            if (isset($response['data']['transactiontype']) && $response['data']['transactiontype'] == 'MS') {
                $statement = isset($response['data']['minidata']) ? $response['data']['minidata'] : array();
                $data = [
                    'clientRefNo'        => $clientRefNo,
                    'routeType'          => isset($response['data']['routetype']) ? $response['data']['routetype'] : "",
                    'bankiin'            => isset($response['data']['bankiin']) ? $response['data']['bankiin'] : "",
                    'stanNo'             => isset($response['data']['stanno']) ? $response['data']['stanno'] : "",
                    'rrn'                => isset($response['data']['rrn']) ? $response['data']['rrn'] : "",
                    'bankMessage'        => isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : "",
                    'bankCode'           => isset($response['data']['bankcode']) ? $response['data']['bankcode'] : "",
                    'statusCode'         => isset($response['data']['statuscode']) ? $response['data']['statuscode'] : "",
                    'merchantCode'       => isset($response['data']['merchantcode']) ? $response['data']['merchantcode'] : "",
                    'aadhaarNumber'      => isset($response['data']['aadharnumber']) ? $response['data']['aadharnumber'] : "",
                    'transactionType'    => isset($response['data']['transactiontype']) ? $response['data']['transactiontype'] : "",
                    'transactionDateTime' => isset($response['data']['transactiondatetime']) ? $response['data']['transactiondatetime'] : "",
                    'transactionAmount'  => isset($response['data']['transactionAmount']) ? $response['data']['transactionAmount'] : "",
                    'availableBalance'   => isset($response['data']['availablebalance']) ? $response['data']['availablebalance'] : "",
                    'statement'          => $statement,
                ];
            } else {
                $data = [
                    'clientRefNo'        => $clientRefNo,
                    'routeType'          => isset($response['data']['routetype']) ? $response['data']['routetype'] : "",
                    'bankiin'            => isset($response['data']['bankiin']) ? $response['data']['bankiin'] : "",
                    'stanNo'             => isset($response['data']['stanno']) ? $response['data']['stanno'] : "",
                    'rrn'                => isset($response['data']['rrn']) ? $response['data']['rrn'] : "",
                    'bankMessage'        => isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : "",
                    'bankCode'           => isset($response['data']['bankcode']) ? $response['data']['bankcode'] : "",
                    'statusCode'         => isset($response['data']['statuscode']) ? $response['data']['statuscode'] : "",
                    'merchantCode'       => isset($response['data']['merchantcode']) ? $response['data']['merchantcode'] : "",
                    'aadhaarNumber'      => isset($response['data']['aadharnumber']) ? $response['data']['aadharnumber'] : "",
                    'transactionType'    => isset($response['data']['transactiontype']) ? $response['data']['transactiontype'] : "",
                    'transactionDateTime' => isset($response['data']['transactiondatetime']) ? $response['data']['transactiondatetime'] : "",
                    'transactionAmount'  => isset($response['data']['transactionAmount']) ? $response['data']['transactionAmount'] : "",
                    'availableBalance'   => isset($response['data']['availablebalance']) ? $response['data']['availablebalance'] : "",
                ];
            }
        }

        return $data;
    }

    public static function sendUATResponse($userId, $method, $transId, $params = array())
    {
        $env = CommonHelper::uatOrProductionConfig($userId, 'aeps');
        if ($env == 'uat') {
            if (isset($transId)) {
                if ($transId == AEPS_SUCCESS_RESPONSE) {
                    $resp = UATResponse::response('aeps', $method, 'success', $transId, $params);
                    return Response::success($resp['message'], $resp['data'], 200);
                } else if ($transId == AEPS_FAILED_RESPONSE) {
                    $resp = UATResponse::response('aeps', $method, 'failed', $transId, $params);
                    return Response::failed($resp['message'], $resp['data']);
                } else {
                    return Response::failed("Merchant code not found", [], 201);
                }
            } else {
                return Response::failed("Merchant code not found", [], 201);
            }
        } else {
            return 'production';
        }
    }

    public static function saveTransaction($data = array(), $clientRefNo = "", $userId = 1, $statusCode = "", $merchantCode = "", $routeType = "", $aadharNo = "", $sdkVer = "", $reference = "")
    {
        if (isset($data['transactiontype'])) {
            $trnAmount = isset($data['transactionAmount']) ? $data['transactionAmount'] : 0;
            $commissionData = self::aepsCommission($data['transactiontype'], $userId, $clientRefNo, $trnAmount);
            $aepsLog = new \App\Models\AepsTransaction;
            $aepsLog->user_id = $userId;
            $aepsLog->merchant_code = $merchantCode;
            $aepsLog->client_ref_id = $clientRefNo;
            $aepsLog->transaction_type = CommonHelper::case($data['transactiontype'], 'l');
            $aepsLog->aadhaar_no = isset($aadharNo) ? $aadharNo : "";
            $aepsLog->bankiin = isset($data['bankiin']) ? $data['bankiin'] : "";
            $aepsLog->resp_stan_no = isset($data['stanno']) ? $data['stanno'] : "";
            $aepsLog->route_type = isset($routeType) ? $routeType : "";
            $aepsLog->sdk_version = $sdkVer;
            $aepsLog->reference = $reference;
            $aepsLog->area = '11';
            $aepsLog->resp_bank_code = isset($data['bankcode']) ? $data['bankcode'] : "";
            $aepsLog->resp_bank_message = isset($data['bankiin']) ? $data['bankiin'] : "";
            $aepsLog->transaction_amount = isset($data['transactionAmount']) ? $data['transactionAmount'] : "";
            $aepsLog->available_balance = isset($data['availablebalance']) ? $data['availablebalance'] : "";
            $aepsLog->mobile_no = isset($data['merchantmobile']) ? $data['merchantmobile'] : "";
            if (isset($data['rrn']) && !empty($data['rrn'])) {
                $aepsLog->rrn = $data['rrn'];
            }
            $aepsLog->transaction_date = isset($data['transactiondatetime']) ? $data['transactiondatetime'] : "";
            if ($statusCode == "000") {
                $aepsLog->status = "success";
            }
            if ($statusCode == "001") {
                $aepsLog->status = "failed";
                $aepsLog->failed_message = isset($data['failed_message']) ? $data['failed_message'] : "";
            }
            if ($statusCode == "002") {
                $aepsLog->status = "pending";
            }
            $aepsLog->transaction_date = isset($data['transactiondatetime']) ? $data['transactiondatetime'] : "";
            /* if (isset($data['transactiontype']) && $data['transactiontype'] == 'MS') {
                $aepsLog->ms_data = json_encode($data['minidata']);
            } */

            if (isset($data['transactiontype']) && $data['transactiontype'] == 'AP') {
                $aepsLog->fee = $commissionData['commission'];
                $aepsLog->tax = $commissionData['tds'];
                $aepsLog->margin = $commissionData['margin'];
            } else {
                $aepsLog->commission = $commissionData['commission'];
                $aepsLog->tds = $commissionData['tds'];
                $aepsLog->margin = $commissionData['margin'];
            }

            $aepsLog->save();
        }
    }

    public static function txnCreateAndStatusUpdate($data = array(), $clientRefNo = "", $userId = 1, $statusCode = "", $merchantCode = "", $routeType = "", $aadharNo = "", $sdkVer = "", $reference = "")
    {
        if (isset($data['transactiontype'])) {

            $aepsLog = new \App\Models\AepsTransaction;
            $aepsLog->user_id = 306;
            $aepsLog->merchant_code = $merchantCode;
            $aepsLog->client_ref_id = $clientRefNo;
            $aepsLog->transaction_type = CommonHelper::case($data['transactiontype'], 'l');
            $aepsLog->aadhaar_no = isset($aadharNo) ? $aadharNo : "";
            $aepsLog->bankiin = isset($data['bankiin']) ? $data['bankiin'] : "";
            $aepsLog->resp_stan_no = isset($data['stanno']) ? $data['stanno'] : "";
            $aepsLog->route_type = isset($routeType) ? $routeType : "";
            $aepsLog->sdk_version = $sdkVer;
            $aepsLog->reference = $reference;
            $aepsLog->area = '11';
            $aepsLog->txn_1 = 'manual';
            $aepsLog->resp_bank_code = isset($data['bankcode']) ? $data['bankcode'] : "";
            $aepsLog->resp_bank_message = isset($data['bankiin']) ? $data['bankiin'] : "";
            $aepsLog->transaction_amount = isset($data['transactionAmount']) ? $data['transactionAmount'] : "";
            $aepsLog->available_balance = isset($data['availablebalance']) ? $data['availablebalance'] : "";
            $aepsLog->mobile_no = isset($data['merchantmobile']) ? $data['merchantmobile'] : "";
            if (isset($data['rrn']) && !empty($data['rrn'])) {
                $aepsLog->rrn = $data['rrn'];
            }
            $aepsLog->transaction_date = isset($data['transactiondatetime']) ? $data['transactiondatetime'] : "";
            $aepsLog->created_at = isset($data['created_at']) ? $data['created_at'] : "";
            if ($statusCode == "000") {
                $aepsLog->status = "success";
            }
            if ($statusCode == "001") {
                $aepsLog->status = "failed";
                $aepsLog->failed_message = isset($data['failed_message']) ? $data['failed_message'] : "";
            }
            if ($statusCode == "002") {
                $aepsLog->status = "pending";
            }
            $aepsLog->transaction_date = isset($data['transactiondatetime']) ? $data['transactiondatetime'] : "";
            /* if (isset($data['transactiontype']) && $data['transactiontype'] == 'MS') {
                $aepsLog->ms_data = json_encode($data['minidata']);
            } */
            $aepsLog->save();
        }
    }

    public static function ekycStatusOfMerchant($userId, $merchantCode, $routeType)
    {
        $resp['ekycStatus'] = 5; // No Ekyc details found
        $resp['message'] = "No kyc details found."; // No Ekyc details found
        try {
            $checkKycDoc = DB::table('agents')
                ->select('documents_status', 'documents_remarks', 'is_ekyc_documents_uploaded')
                ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                ->first();
            if (isset($checkKycDoc->documents_status) && $checkKycDoc->documents_status == 'accepted') {
                $agentDataEkycDataCheck = DB::table('agents')
                    ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                    ->select('ekyc')
                    ->first();

                if (isset($agentDataEkycDataCheck->ekyc) && !empty($agentDataEkycDataCheck->ekyc)) {
                    $routeType = CommonHelper::case($routeType, 'l');
                    $agentDataEkycIsEmpty = DB::table('agents')
                        ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                        ->select('ekyc', 'documents_remarks', DB::raw("json_extract(ekyc, '$.$routeType') as routeData"))
                        ->first();
                    if (isset($agentDataEkycIsEmpty->ekyc) && !empty($agentDataEkycIsEmpty->ekyc)) {

                        $data = json_decode($agentDataEkycIsEmpty->routeData, TRUE);
                        $kycName = "KYC";
                        if ($routeType == 'icici' || $routeType == 'airtel') {
                            $kycName = "eKYC";
                        }
                        if (isset($data) && count($data) > 0) {
                            if ($data['is_ekyc'] == 1) {
                                $resp['ekycStatus'] = 1;
                                $resp['message'] = "$kycName  successfull.";
                            } else if ($data['is_ekyc'] == 0) {
                                $resp['ekycStatus'] = 0;
                                $resp['message'] = "$kycName pending.";
                            } else if ($data['is_ekyc'] == 2) {
                                $resp['ekycStatus'] = 2;
                                $resp['message'] = "$kycName rejected. " . $agentDataEkycIsEmpty->documents_remarks;
                            } else if ($data['is_ekyc'] == 3) {
                                $resp['ekycStatus'] = 3;
                                $resp['message'] = "$kycName expired. " . $agentDataEkycIsEmpty->documents_remarks;
                            }
                        }
                    }
                }
            } else if (isset($checkKycDoc->documents_status) && $checkKycDoc->documents_status == 'pending') {
                if ($checkKycDoc->is_ekyc_documents_uploaded == '1') {
                    $resp['ekycStatus'] = 0;
                    $resp['message'] = "KYC is pending";
                } else {
                    $resp['ekycStatus'] = 0;
                    $resp['message'] = "Upload your KYC documents for activation.";
                }
            } else if (isset($checkKycDoc->documents_status) && $checkKycDoc->documents_status == 'rejected') {
                $resp['ekycStatus'] = 2;
                $resp['message'] = "KYC rejected. " . $checkKycDoc->documents_remarks;
            } else {
                $resp['ekycStatus'] = 5; // No Ekyc details found
                $resp['message'] = "Upload your KYC documents for activation."; // No Ekyc details found
            }
        } catch (\Exception $e) {
            $resp['message'] = "Something Went Wrong ." . $e->getMessage();
        }
        return $resp;
    }


    public static function aepsCommission($requestType, $userId, $clientRefId, $transactionAmount)
    {
        $resp['commission'] = 0;
        $resp['tds'] = 0;
        $resp['margin'] = "";
        $resp['message'] = "init";
        try {
            //code...
            if (isset($transactionAmount)) {
                $slug = 'aeps_be';
                if ($requestType  == 'BE') {
                    $slug = 'aeps_be';
                    $transactionAmount = 1;
                } elseif ($requestType  == 'MS') {
                    $slug = 'aeps_ms';
                    $transactionAmount = 1;
                } elseif ($requestType  == 'CW') {
                    $slug = 'aeps_cw';
                } elseif ($requestType  == 'AP') {
                    $slug = 'aeps_ap';
                }

                $getProductId = CommonHelper::getProductId($slug, 'aeps');
                $productId = isset($getProductId->product_id) ? $getProductId->product_id : "";
                $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, $transactionAmount, $userId);
                $resp['commission'] = isset($getFeesAndTaxes['fee']) ? $getFeesAndTaxes['fee'] : 0;
                $resp['tds'] = isset($getFeesAndTaxes['tax']) ? $getFeesAndTaxes['tax'] : 0;
                $resp['margin'] = isset($getFeesAndTaxes['margin']) ? $getFeesAndTaxes['margin'] : "";
                $resp['message'] = "success";
            } else {
                $resp['commission'] = 0;
                $resp['tds'] = 0;
                $resp['margin'] = "";
                $resp['message'] = "no record found";
            }
        } catch (\Exception  $e) {
            $resp['commission'] = 0;
            $resp['tds'] = 0;
            $resp['margin'] = "";
            $resp['message'] = "no record found. " . $e->getMessage();
        }

        return $resp;
    }
}
