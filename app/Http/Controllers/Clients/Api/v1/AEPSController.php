<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Validations\AEPSValidation as Validations;
use App\Helpers\CommonHelper;
use App\Helpers\UATResponse;
use App\Helpers\ResponseHelper as Response;
use App\Helpers\TransactionHelper;
use App\Helpers\WebhookHelper;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Sdk\Api\v1\SDKController;
use App\Models\AepsTransaction;
use App\Models\State;
use App\Models\District;
use App\Models\Bank;
use App\Models\Agent;
use App\Models\Webhook;
use App\Models\Apilog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AEPSController extends Controller
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
        $this->baseUrl = env('AEPS_BASE_URL_INSTANT');
        $this->client_id = env('AEPS_CLIENT_ID');
        $this->client_secret = env('AEPS_CLIENT_SECRET');
        $this->encryption_key = env('AEPS_ENCRYPTION_KEY');
    }


    /**
     * Create Merchant function
     *
     * @param Request $request
     * @return void
     */
    public function merchantOnBoard(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->merchantOnBoard();
        $validator->after(function ($validator) use ($request) {

            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    }
                }
                $merchantEmail = Agent::where(['user_id' => $request['auth_data']['user_id'], 'email_id' => $request->email])->count();
                if ($merchantEmail == 0) {
                    $validator->errors()->add('email', "E-Mail Id can't be changed.");
                }
                $merchantMobile = Agent::where(['user_id' => $request['auth_data']['user_id'], 'mobile' => $request->mobile])->count();
                if ($merchantMobile  == 0) {
                    $validator->errors()->add('mobile', "Mobile No can't be changed.");
                }
                $merchantAadhaarNo = Agent::where(['user_id' => $request['auth_data']['user_id'], 'aadhar_number' => $request->aadhaarNo])->count();
                if ($merchantAadhaarNo  == 0) {
                    $validator->errors()->add('aadhaarNo', "Aadhaar No can't be changed.");
                }
                $merchantPanNo = Agent::where(['user_id' => $request['auth_data']['user_id'], 'pan_no' => $request->panNo])->count();
                if ($merchantPanNo  == 0) {
                    $validator->errors()->add('panNo', "Pan No can't be changed.");
                }
                if (empty($request->latitude)) {
                    $validator->errors()->add('latitude', 'The latitude field is required.');
                }
                if (empty($request->longitude)) {
                    $validator->errors()->add('longitude', 'The longitude field is required.');
                }
            } else {
                
                $merchantEmail = Agent::where(['user_id' => $request['auth_data']['user_id'], 'email_id' => $request->email])->count();
                if ($merchantEmail > 0) {
                    $validator->errors()->add('email', 'E-Mail Id should be unique.');
                }
                $merchantMobile = Agent::where(['user_id' => $request['auth_data']['user_id'], 'mobile' => $request->mobile])->count();
                if ($merchantMobile  > 0) {
                    $validator->errors()->add('mobile', 'Mobile No should be unique.');
                }
                $merchantAadhaarNo = Agent::where(['user_id' => $request['auth_data']['user_id'], 'aadhar_number' => $request->aadhaarNo])->count();
                if ($merchantAadhaarNo  > 0) {
                    $validator->errors()->add('aadhaarNo', 'Aadhaar No should be unique.');
                }
                $merchantPanNo = Agent::where(['user_id' => $request['auth_data']['user_id'], 'pan_no' => $request->panNo])->count();
                if ($merchantPanNo  > 0) {
                    $validator->errors()->add('panNo', 'Pan No should be unique.');
                }
                if (empty($request->latitude)) {
                    $validator->errors()->add('latitude', 'The latitude field is required.');
                }
                if (empty($request->longitude)) {
                    $validator->errors()->add('longitude', 'The longitude field is required.');
                }
            }
        });
        
        
        
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {
            $encodeAddhar = CommonHelper::encAadhar($request->aadhaarNo);
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            $request->panNo = CommonHelper::case($request->panNo, 'u');
            
            $addressArray = array('full'=>$request->address,'city'=>$request->city,'pincode'=>$request->pinCode);
            $params = [
                'mobile'        => $request->mobile,
                'pan'           => $request->panNo,
                'email'         => $request->email,
                'address'       => $addressArray,
                'dateOfBirth'   => $request->dob,
                'aadhaar'       => $encodeAddhar,
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
            ];

            $requestType = 'aepsMerchant';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], 'merchantOnBoard', $merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                if (isset($response['statuscode'])) {
                    if ($response['statuscode'] === "TXN") {
                        $agent = Agent::where('merchant_code', $request->merchantCode)->first();
                        if (!isset($agent)) {
                            $agent = new Agent;
                            $agent->merchant_code = 'SPI'.$response['data']['outletId'];
                            $message = "Merchant added successfully.";
                        } else {
                            $message = "Merchant updated successfully.";
                        }
                        $agent->user_id = $request['auth_data']['user_id'];
                        $agent->first_name = $request->firstName;
                        $agent->middle_name = $request->middleName;
                        $agent->last_name = $request->lastName;
                        $agent->mobile = $request->mobile;
                        $agent->email_id = $request->email;
                        $agent->address = $request->address;
                        $agent->city = $request->city;
                        $agent->pin_code = $request->pinCode;
                        $agent->dob = $request->dob;
                        $agent->aadhar_number = $request->aadhaarNo;
                        $agent->pan_no = $request->panNo;
                        $agent->shop_name = $request->shopName;
                        $agent->shop_address = $request->shopAddress;
                        $agent->shop_pin = $request->shopPin;
                        $agent->merchant_code = 'SPI'.$response['data']['outletId'];
                        $agent->save();

                        $data['merchantCode'] = 'SPI'.$response['data']['outletId'];
                        $data['firstName'] = $request->firstName;
                        $data['middleName'] = $request->middleName;
                        $data['lastName'] = $request->lastName;
                        $data['mobile'] = $request->mobile;
                        $data['email'] = $request->email;
                        $data['pinCode'] = $response['data']['pincode'];
                        $data['dob'] = $response['data']['dateOfBirth'];
                        $data['aadhaarNo'] = $request->aadhaarNo;
                        $data['panNo'] = $request->panNo;
                        $data['status'] = $response['statuscode'];
                        $data['remarks'] = $response['status'];
                        $data['shopName'] = $request->shopName;
                        $data['shopAddress'] = $request->shopAddress;
                        $data['shopPin'] = $request->shopPin;
                        $data['state'] = $response['data']["state"];
                        $data['district'] = $response['data']["districtName"];
                        $data['address']  = $response['data']["address"];

                        $message = $response['statuscode'] . ': ' . $message;
                        return Response::success($message, $data, 200);
                    } else {
                        $message = $response['statuscode'] . ': ' . $response['status'];
                        return Response::failed($message);
                    }
                } else {
                    $message = "Something went wrong";
                    return Response::swwrong($message);
                }
            } else {
                return $returnResp;
            }
        }
    }

   /**
     * Merchant List function Only For Testing
     *
     * @param Request $request
     * @return void
     */
   public function merchantList(Request $request){
       
    $url = 'https://api.instantpay.in/user/outlet/list';   
    
    $request='{"pagination":{"pageNumber":1,"recordsPerPage":"10"},"filters":{"outletId":0,"mobile":"","pan":""}}'; 
    
    $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret:'.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,"X-Ipay-Endpoint-Ip:137.59.52.66");   
        
    $result = CommonHelper::curl($url, 'POST', $request, $header, 'yes', '1', 'aeps', 'n', '0');   
    
    dd($result);
        
       
   }



    /**
     * Send OTP function
     *
     * @param Request $request
     * @return void
     */
    public function sendOTP(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->sendOTP();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    }
                    if (empty($request->mobile)) {
                        $validator->errors()->add('mobile', 'The mobile field is required.');
                    }
                    if (empty($request->email)) {
                        $validator->errors()->add('email', 'The email field is required.');
                    }
                    if (empty($request->aadhaarNo)) {
                        $validator->errors()->add('aadhaarNo', 'The aadhaarNo field is required.');
                    }
                    if (empty($request->panNo)) {
                        $validator->errors()->add('latitude', 'The panNo field is required.');
                    }
                    if (empty($request->latitude)) {
                        $validator->errors()->add('latitude', 'The latitude field is required.');
                    }
                    if (empty($request->longitude)) {
                        $validator->errors()->add('longitude', 'The longitude field is required.');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            $resp = Response::missing($message);
            return $resp;
        } else {
            $encodeAddhar = CommonHelper::encAadhar($request->aadhaarNo);
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            $params = [
                'mobile'    => $request->mobile,
                'pan'       => $request->panNo,
                'email'     => $request->email,
                'aadhaar'   => $encodeAddhar,
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
                'consent'   => 'Y',
            ];
            $requestType = 'sendOtp';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], 'sendOTP', $merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                $data = [];
                if (isset($response['statuscode'])) {
                    if ($response['statuscode'] === "TXN") {
                        if (isset($response['data'])) {
                            $data = [
                                'aadhaar'     => $response['data']["aadhaar"],
                                'token'     => $response['data']["otpReferenceID"],
                                'hash'      => $response['data']["hash"],
                            ];
                        }
                        $message = $response['statuscode'] . ": OTP send successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "001" && $response['status'] == "Ekyc already Verified, $merchantCode") {
                        $message = $response['statuscode'] . ": " . $response['status'];
                        $resp = Response::failed($message);
                        self::ekycUpdate($request['auth_data']['user_id'], $merchantCode, $request->routeType);
                    } else {
                        $message = $response['statuscode'] . ': ' . $response['status'];;
                        $resp = Response::failed($message);
                    }
                } else {
                    $message = "Something went wrong";
                    $resp = Response::swwrong($message);
                }
            } else {
                return $returnResp;
            }
        }
        return $resp;
    }


    /**
     *  isBharatAtmEkyc function
     *
     * @param Request $request
     * @return void
     */
    public function isBharatAtmEkyc(Request $request)
    {
        $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";

        if (isset($merchantCode) && !empty($merchantCode)) {
            $requestType = 'isBharatAtmEkyc';
            $params['merchantCode'] = $merchantCode;

            $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
            if (isset($response['statuscode'])) {
                if ($response['statuscode'] === "000") {
                    $message = @$response['message'];
                    $resp = Response::success($message, $response);
                } else {
                    $message = $response['statuscode'] . ': ' . $response['message'];;
                    $resp = Response::failed($message);
                }
            } else {
                $message = 'No ekyc data found.';
                $resp = Response::failed($message);
            }
        } else {
            $resp = Response::failed('Please send merchant code');
        }

        return $resp;
    }



    /**
     * Resend OTP function
     *
     * @param Request $request
     * @return void
     */
    public function resendOTP(Request $request)
    {

        $validation = new Validations($request);
        $validator = $validation->resendOTP();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            $resp = Response::missing($message);
            return $resp;
        } else {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            $params = [
                'merchantcode'  => $request->merchantCode,
                'otp'           => "",
                'routeType' => $request->routeType,
                'mobilenumber' => $request->mobile,
                'requestid' => $request->requestId,
                'primaryid' => $request->primaryId,
                'token' => $request->token,
            ];

            $requestType = 'resendOTP';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $merchantCode, $params);
            if ($returnResp == 'production') {

                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                $data = [];
                if (isset($response['statuscode'])) {
                    if ($response['statuscode'] === "000") {
                        if (isset($response['data'])) {
                            $data = [
                                'token'  => $response['data']["token"],
                                'requestId' => $response['data']["requestid"],
                                'primaryId' => $response['data']["primaryid"],
                                'info1' => $response['data']["info1"],
                                'info2' => $response['data']["info2"],
                            ];
                        }
                        $message = $response['statuscode'] . ": OTP re-send successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else {
                        $message = $response['statuscode'] . ': ' . $response['message'];;
                        $resp = Response::failed($message);
                    }
                } else {
                    $message = 'some thing went wrong';
                    $resp = Response::swwrong($message);
                }
            } else {
                $resp = $returnResp;
            }
        }
        return $resp;
    }
    /**
     * Validate OTP function
     *
     * @param Request $request
     * @return void
     */
    public function validateOTP(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->validateOTP();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            $resp = Response::missing($message);
            return $resp;
        } else {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            $params = [
                'otpReferenceID'  => $request->token,
                'otp'           => $request->otp,
                'hash' => $request->hash,
            ];
            
            $requestType = 'validateOTP';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                $data = [];
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                    if ($response['statuscode'] === "TXN") {
                        if (isset($response['data'])) {
                            $data = [
                                'outletId'    =>'SPI'.$response['data']['outletId'],
                                'name'        =>$response['data']["name"],
                                'dateOfBirth' =>$response['data']["dateOfBirth"],
                                'gender' => $response['data']["gender"],
                                'pincode' => $response['data']["pincode"],
                                'state' => $response['data']["state"],
                                'districtName' => $response['data']["districtName"],
                                'address' => $response['data']["address"],
                                'timestamp' => $response["timestamp"],
                            ];
                        }
                        $message = $statusCode . ": Validate OTP successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else {
                        $message = $statusCode . ': ' . $response['message'];;
                        $resp = Response::failed($message);
                    }
                } else {
                    $message = '';
                    $resp = Response::swwrong($message);
                }
            } else {
                $resp = $returnResp;
            }
        }
        return $resp;
    }

    /**
     * Bio Metric function
     *
     * @param Request $request
     * @return void
     */
    public function ekycBioMetric(Request $request)
    {

        $validation = new Validations($request);
        $validator = $validation->ekycBioMetric();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            $resp = Response::missing($message);
            return $resp;
        } else {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            $params = [
                'merchantcode'  => $request->merchantCode,
                'aadharnumber'        => $request->aadhaarNo,
                'rdrequest'     => $request->rdRequest,
                'routeType' => $request->routeType,
                'pannumber' => $request->panNo,
                'mobilenumber' => $request->mobile,
                'primaryid' => isset($request->primaryId) ? $request->primaryId : "",
                'requestid' => isset($request->requestId) ? $request->requestId : "",
                'token' => isset($request->token) ? $request->token : "",
            ];
            $requestType = 'ekycBioMetric';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id']);
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                    if ($response['statuscode'] === "000") {
                        $message = $statusCode . ": " . $response['message'];
                        $resp = Response::success($message, $response, 200);
                        self::ekycUpdate($request['auth_data']['user_id'], $request->merchantCode, $request->routeType);
                    } else if ($response['statuscode'] === "001" && $response['message'] == "Ekyc already Verified, $request->merchantCode") {
                        $message = $statusCode . ": " . $response['message'];
                        $resp = Response::failed($message);
                        self::ekycUpdate($request['auth_data']['user_id'], $request->merchantCode, $request->routeType);
                    } else {
                        $message = $statusCode . ': ' . $response['message'];;
                        $resp = Response::failed($message);
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
     * Get Balance function
     *
     * @param Request $request
     * @return void
     */
    public function getBalance(Request $request)
    {
        $substringToRemove = "SPI";
        $bcId = str_replace($substringToRemove, "", $request->merchantCode);
        $encodeAddhar = CommonHelper::encAadhar($request->aadhaarNo);
        $validation = new Validations($request);
        $validator = $validation->getBalance();
        $validator->after(function ($validator) use ($request) {
            $batm_user_id =  (array) env('BATM_USER_ID');
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            $userId =  $request['auth_data']['user_id'];
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $userId, 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    } /*else if (!in_array($request['auth_data']['user_id'], $batm_user_id)) {
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
                    }*/
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {

            if (!empty($request->clientRefNo) || !empty($request->clientRefNo)) {
                $clientrefno =  !empty($request->clientRefNo) ? $request->clientRefNo : $request->clientRefNo;

                if (DB::table('aeps_transactions')
                    ->where('client_ref_id', $clientrefno)
                    ->count()
                ) {
                    return Response::failed('The client ref id should be unique');
                }
            } else {
                $clientrefno =  CommonHelper::getRandomString('ABE', false);
            }
         
     #finger data
            $biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",$request->fingdata));
            $xml           =  simplexml_load_string($biodata);
            $skeyci        =  (string)$xml->Skey['ci'][0];
            $headerarray   =  json_decode(json_encode((array)$xml), TRUE);
      
         $params = [
             'mobile'          => $request->mobile,
             'bankiin'         => $request->bankiin,
             'externalRef'     => $clientrefno,
             "latitude"        => sprintf("%0.4f",$request->latitude),
             "longitude"       => sprintf("%0.4f", $request->longitude),
            "biometricData"   => [
                "encryptedAadhaar" => $encodeAddhar,
                "pidDataType" =>  "X",
                "pidData"     =>  $headerarray['Data'],
                "ci"          =>  $skeyci,
                "dc"          =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                "dpId"        =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                "errCode"     =>  $headerarray['Resp']['@attributes']['errCode'],
                "errInfo"     =>  isset($headerarray['Resp']['@attributes']['errInfo'])?$headerarray['Resp']['@attributes']['errInfo']:"",
                "fCount"      =>  $headerarray['Resp']['@attributes']['fCount'],
                "tType"       =>  null,
                "fType"       =>  '2',
                "hmac"        =>  $headerarray['Hmac'],
                "iCount"      =>  "0",
                "iType"       =>  "0",
                "mc"          =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                "mi"          =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                "nmPoints"    =>  $headerarray['Resp']['@attributes']['nmPoints'],
                "pCount"      =>  "0",
                "pType"       =>  "0",
                "qScore"      =>  $headerarray['Resp']['@attributes']['qScore'],
                "rdsId"       =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                "rdsVer"      =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                "sessionKey"  =>  $headerarray['Skey'],
                "srno"        =>  isset($headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value']) ? $headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value'] : $headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],
                "ts"    => date('d/m/Y H:i:s'),
                "sysid" =>  "MDAyNTM4ODU4MUI0OTgwNU4wQ1YxODM4TUIwMDU0NDk3SkFOMENWMDM2MjM3NDEw"
            ]
         ]; 


            $requestType = 'getBalance';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id'],$bcId);
                $data = [];
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                     $data = self::getResponse($response, $clientrefno, $request->merchantCode,$request->aadhaarNo,'BE');
                    $response['data']['failed_message'] = isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : $response['status'];
                    if ($response['statuscode'] === "TXN") {
                        $response['data']['transactiontype']   = 'be';
                        $response['data']['merchantmobile']    = $request->mobile;
                        $response['data']['bankiin']           = $request->bankiin;
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "000", $request->merchantCode,$request->aadhaarNo);
                        $message = $statusCode . ": Balance fetched successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "TUP") {
                        $response['data']['transactiontype']   = 'be';
                        $response['data']['merchantmobile']    = $request->mobile;
                        $response['data']['bankiin']           = $request->bankiin;
                        $message = $response['statuscode'] . ": Balance enquiry is pending.";
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode,$request->aadhaarNo);
                        $resp = Response::pending($message, $data);
                    } else if ($response['statuscode'] === "UAD") {
                        $message = $response['statuscode'] . ": Please do 2 factor auth.";
                        $resp = Response::twoFactAuth($message, $data);
                    } else {
                        $message = $statusCode . ': ' . $response['status'];
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
     * Get twoFactAuthCheck function
     *
     * @param Request $request
     * @return void
     */
    public function twoFactAuthCheck(Request $request)
    {
       $substringToRemove = "SPI";
       $bcId = str_replace($substringToRemove, "", $request->merchantCode);
        $validation = new Validations($request);
        $validator = $validation->twoFactAuthCheck();
        $validator->after(function ($validator) use ($request) {
            $batm_user_id =  (array) env('BATM_USER_ID');
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            $userId =  $request['auth_data']['user_id'];
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $userId, 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {

            $params = [];

            $requestType = 'twoFactorAuthCheck';

            $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id'],$bcId);
           
            $data = [];
            if (isset($response['statuscode'])) {

                if ($response['statuscode'] === "TXN" && $response['actcode'] === "LOGGEDIN") {
                    $message = $response['actcode'];
                    $resp = Response::success($message, $data, 200);
                } else {
                    $message = $response['statuscode'] . ': ' . $response['status'];
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
     * Get twoFactAuth function
     *
     * @param Request $request
     * @return void
     */
    public function twoFactAuth(Request $request)
    {
       
        $encodeAddhar = CommonHelper::encAadhar($request->aadhaarNo);
        $substringToRemove = "SPI";
        $bcId = str_replace($substringToRemove, "", $request->merchantCode);
        $validation = new Validations($request);
        $validator = $validation->twoFactAuth();
        $validator->after(function ($validator) use ($request) {
            $batm_user_id =  (array) env('BATM_USER_ID');
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            $userId =  $request['auth_data']['user_id'];
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $userId, 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {
            
        #finger data

            $biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",$request->fingdata));
            $xml           =  simplexml_load_string($biodata);
            $skeyci        =  (string)$xml->Skey['ci'][0];
            $headerarray   =  json_decode(json_encode((array)$xml), TRUE);
      
         $params = [
            "latitude"    => sprintf("%0.4f",$request->latitude),
            "longitude"   => sprintf("%0.4f", $request->longitude),
            "biometricData"   => [
                "encryptedAadhaar" => $encodeAddhar,
                "pidDataType" =>  "X",
                "pidData"     =>  $headerarray['Data'],
                "ci"          =>  $skeyci,
                "dc"          =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                "dpId"        =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                "errCode"     =>  $headerarray['Resp']['@attributes']['errCode'],
                "errInfo"     =>  isset($headerarray['Resp']['@attributes']['errInfo'])?$headerarray['Resp']['@attributes']['errInfo']:"",
                "fCount"      =>  $headerarray['Resp']['@attributes']['fCount'],
                "tType"       =>  null,
                "fType"       =>  '2',
                "hmac"        =>  $headerarray['Hmac'],
                "iCount"      =>  "0",
                "iType"       =>  "0",
                "mc"          =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                "mi"          =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                "nmPoints"    =>  $headerarray['Resp']['@attributes']['nmPoints'],
                "pCount"      =>  "0",
                "pType"       =>  "0",
                "qScore"      =>  $headerarray['Resp']['@attributes']['qScore'],
                "rdsId"       =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                "rdsVer"      =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                "sessionKey"  =>  $headerarray['Skey'],
                "srno"        =>  isset($headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value']) ? $headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value'] : $headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],
                "ts"    => date('d/m/Y H:i:s'),
                "sysid" =>  "MDAyNTM4ODU4MUI0OTgwNU4wQ1YxODM4TUIwMDU0NDk3SkFOMENWMDM2MjM3NDEw"
            ]
         ];
        
            $requestType = 'twoFactAuth';

            $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id'],$bcId);
            $data = [];
            if (isset($response['statuscode'])) {

                if ($response['statuscode'] === "TXN" && $response['actcode'] === "LOGGEDIN") {
                    $message = $response['statuscode'] . ": Auth Validate successful.";
                    $resp = Response::success($message, $data, 200);
                } else {
                    $message = $response['statuscode'] . ': ' . $response['status'];
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
     * Withdrawal function
     *
     * @param Request $request
     * @return void
     */
    public function withdrawal(Request $request)
    {
        $substringToRemove = "SPI";
        $bcId = str_replace($substringToRemove, "", $request->merchantCode);
        $encodeAddhar = CommonHelper::encAadhar($request->aadhaarNo);
        $validation = new Validations($request);
        $validator = $validation->withdrawal();
        $validator->after(function ($validator) use ($request) {
            $batm_user_id =  (array) env('BATM_USER_ID');
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    } /*else if (!in_array($request['auth_data']['user_id'], $batm_user_id)) {
                        $kycStatus = self::ekycStatusOfMerchant($request['auth_data']['user_id'], $merchantCode);
                        if ($kycStatus['ekycStatus'] == 1) {
                            if ($merchantExists->is_active == 0) {
                                $validator->errors()->add('merchantCode', 'This merchant is inactive.');
                            } elseif ($merchantExists->is_active == 2) {
                                $validator->errors()->add('merchantCode', 'Please upload kyc docs.');
                            }
                        } else {
                            $validator->errors()->add('merchantCode', $kycStatus['message']);
                        }
                    }*/
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {

            if (!empty($request->clientRefNo) || !empty($request->clientRefNo)) {
                $clientrefno =  !empty($request->clientRefNo) ? $request->clientRefNo : $request->clientRefNo;

                if (DB::table('aeps_transactions')
                    ->where('client_ref_id', $clientrefno)
                    ->count()
                ) {
                    return Response::failed('The client ref id should be unique');
                }
            } else {
                $clientrefno =  CommonHelper::getRandomString('ACW', false);
            }
            
     #finger data
            $biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",$request->fingdata));
            $xml           =  simplexml_load_string($biodata);
            $skeyci        =  (string)$xml->Skey['ci'][0];
            $headerarray   =  json_decode(json_encode((array)$xml), TRUE);
      
         $params = [
             'amount'          => $request->amount,
             'mobile'          => $request->mobile,
             'bankiin'         => $request->bankiin,
             'externalRef'     => $clientrefno,
             "latitude"        => sprintf("%0.4f",$request->latitude),
             "longitude"       => sprintf("%0.4f", $request->longitude),
            "biometricData"   => [
                "encryptedAadhaar" => $encodeAddhar,
                "pidDataType" =>  "X",
                "pidData"     =>  $headerarray['Data'],
                "ci"          =>  $skeyci,
                "dc"          =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                "dpId"        =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                "errCode"     =>  $headerarray['Resp']['@attributes']['errCode'],
                "errInfo"     =>  isset($headerarray['Resp']['@attributes']['errInfo'])?$headerarray['Resp']['@attributes']['errInfo']:"",
                "fCount"      =>  $headerarray['Resp']['@attributes']['fCount'],
                "tType"       =>  null,
                "fType"       =>  '2',
                "hmac"        =>  $headerarray['Hmac'],
                "iCount"      =>  "0",
                "iType"       =>  "0",
                "mc"          =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                "mi"          =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                "nmPoints"    =>  $headerarray['Resp']['@attributes']['nmPoints'],
                "pCount"      =>  "0",
                "pType"       =>  "0",
                "qScore"      =>  $headerarray['Resp']['@attributes']['qScore'],
                "rdsId"       =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                "rdsVer"      =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                "sessionKey"  =>  $headerarray['Skey'],
                "srno"        =>  isset($headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value']) ? $headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value'] : $headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],
                "ts"    => date('d/m/Y H:i:s'),
                "sysid" =>  "MDAyNTM4ODU4MUI0OTgwNU4wQ1YxODM4TUIwMDU0NDk3SkFOMENWMDM2MjM3NDEw"
            ]
         ]; 
            
        
            
            $requestType = 'withdrawal';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
           
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id'],$bcId);
                
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                    $data = self::getResponse($response, $clientrefno, $request->merchantCode,$request->aadhaarNo,'CW');
                    $response['data']['failed_message'] = isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : $response['status'];
                    if ($response['statuscode'] === "TXN") {
                        $response['data']['transactiontype']   = 'cw';
                        $response['data']['transactionAmount'] = $request->amount;
                        $response['data']['merchantmobile']    = $request->mobile;
                        $response['data']['bankiin']           = $request->bankiin;
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "000", $request->merchantCode, $request->aadhaarNo);
                        $message = $statusCode . ": Transaction is successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "TUP") {
                        $response['data']['transactiontype']   = 'cw';
                        $response['data']['transactionAmount'] = $request->amount;
                        $response['data']['merchantmobile']    = $request->mobile;
                        $response['data']['bankiin']           = $request->bankiin;
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->aadhaarNo);
                        $message = $response['statuscode'] . ": Transaction is pending.";
                        $resp = Response::pending($message, $data);
                    } else if ($response['statuscode'] === "UAD") {
                        $resp['transactiontype'] = 'cw';
                        $resp['transactionAmount'] = $request->amount;
                        $resp['merchantmobile'] = $request->mobile;
                        $resp['bankiin'] = $request->bankiin;
                        self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "001", $request->merchantCode,$request->aadhaarNo);

                        $data = [
                            "clientRefNo" => $clientrefno,
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
                    } else if ($response['statuscode'] === "TUP") {
                        $resp['transactiontype'] = 'cw';
                        $resp['transactionAmount'] = $request->amount;
                        $resp['merchantmobile'] = $request->mobile;
                        $resp['bankiin'] = $request->bankiin;
                        self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->aadhaarNo);
                        $message = "Transaction is pending.";
                        $data = [
                            "clientRefNo" => $clientrefno,
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
                    } else {
                        if (isset($response['data']['transactiontype']) && !empty($response['data']['transactiontype'])) {
                            $resp = $response['data'];
                        } else {
                            $resp['transactiontype'] = 'cw';
                            $resp['transactionAmount'] = $request->amount;
                            $resp['merchantmobile'] = $request->mobile;
                            $resp['bankiin'] = $request->bankiin;
                            $resp['failed_message'] = $statusCode . ': ' . $response['status'];
                        }
                        self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "001", $request->merchantCode, $request->aadhaarNo);
                        $message = $statusCode . ': ' . $response['status'];
                        $resp = Response::failed($message, $data);
                    }
                } else {
                    $resp['transactiontype'] = 'cw';
                    $resp['transactionAmount'] = $request->amount;
                    $resp['merchantmobile'] = $request->mobile;
                    $resp['bankiin'] = $request->bankiin;
                    self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->aadhaarNo);
                    $message = "Transaction is pending.";
                    $data = [
                        "clientRefNo" => $clientrefno,
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

        $validation = new Validations($request);
        $validator = $validation->aadhaarPay();
        $validator->after(function ($validator) use ($request) {
            $batm_user_id =  (array) env('BATM_USER_ID');
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
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
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "000", $request->merchantCode, $request->routeType, $request->aadhaarNo);
                        $message = $statusCode . ": AadhaarPay transfer successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "002") {
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode, $request->routeType, $request->aadhaarNo);
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
                    } else if ($response['statuscode'] === "999") {
                        $resp['transactiontype'] = 'ap';
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
                        self::saveTransaction($resp, $clientrefno, $request['auth_data']['user_id'], "001", $request->merchantCode, $request->routeType, $request->aadhaarNo);
                        $message = $statusCode . ': ' . $response['message'];
                        $resp = Response::failed($message, $data);
                    }
                } else {
                    $resp['transactiontype'] = 'ap';
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
        $substringToRemove = "SPI";
        $bcId = str_replace($substringToRemove, "", $request->merchantCode);
        $encodeAddhar = CommonHelper::encAadhar($request->aadhaarNo);
        $validation = new Validations($request);
        $validator = $validation->statement();
        $validator->after(function ($validator) use ($request) {

            $batm_user_id =  (array) env('BATM_USER_ID');
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    }/* else if (!in_array($request['auth_data']['user_id'], $batm_user_id)) {
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
                    }*/
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {


            if (!empty($request->clientRefNo) || !empty($request->clientRefNo)) {
                $clientrefno =  !empty($request->clientRefNo) ? $request->clientRefNo : $request->clientRefNo;

                if (DB::table('aeps_transactions')
                    ->where('client_ref_id', $clientrefno)
                    ->count()
                ) {
                    return Response::failed('The client ref id should be unique');
                }
            } else {
                $clientrefno =  CommonHelper::getRandomString('AMS', false);
            }

       #finger data
            $biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",$request->fingdata));
            $xml           =  simplexml_load_string($biodata);
            $skeyci        =  (string)$xml->Skey['ci'][0];
            $headerarray   =  json_decode(json_encode((array)$xml), TRUE);
      
         $params = [
             'mobile'          => $request->mobile,
             'bankiin'         => $request->bankiin,
             'externalRef'     => $clientrefno,
             "latitude"        => sprintf("%0.4f",$request->latitude),
             "longitude"       => sprintf("%0.4f", $request->longitude),
             "biometricData"   => [
                "encryptedAadhaar" => $encodeAddhar,
                "pidDataType" =>  "X",
                "pidData"     =>  $headerarray['Data'],
                "ci"          =>  $skeyci,
                "dc"          =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                "dpId"        =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                "errCode"     =>  $headerarray['Resp']['@attributes']['errCode'],
                "errInfo"     =>  isset($headerarray['Resp']['@attributes']['errInfo'])?$headerarray['Resp']['@attributes']['errInfo']:"",
                "fCount"      =>  $headerarray['Resp']['@attributes']['fCount'],
                "tType"       =>  null,
                "fType"       =>  '2',
                "hmac"        =>  $headerarray['Hmac'],
                "iCount"      =>  "0",
                "iType"       =>  "0",
                "mc"          =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                "mi"          =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                "nmPoints"    =>  $headerarray['Resp']['@attributes']['nmPoints'],
                "pCount"      =>  "0",
                "pType"       =>  "0",
                "qScore"      =>  $headerarray['Resp']['@attributes']['qScore'],
                "rdsId"       =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                "rdsVer"      =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                "sessionKey"  =>  $headerarray['Skey'],
                "srno"        =>  isset($headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value']) ? $headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value'] : $headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],
                "ts"    => date('d/m/Y H:i:s'),
                "sysid" =>  "MDAyNTM4ODU4MUI0OTgwNU4wQ1YxODM4TUIwMDU0NDk3SkFOMENWMDM2MjM3NDEw"
            ]
         ]; 
            
            
            $requestType = 'statement';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id'],$bcId);
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];
                     $data = self::getResponse($response, $clientrefno, $request->merchantCode,$request->aadhaarNo,'MS');
                    $response['data']['failed_message'] = isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : $response['status'];
                    if ($response['statuscode'] === "TXN") {
                        $response['data']['transactiontype']   = 'ms';
                        $response['data']['merchantmobile']    = $request->mobile;
                        $response['data']['bankiin']           = $request->bankiin;
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "000", $request->merchantCode,$request->aadhaarNo);
                         
                        $message = $response['statuscode'] . ": Mini statement fetched successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "TUP") {
                        $response['data']['transactiontype']   = 'ms';
                        $response['data']['merchantmobile']    = $request->mobile;
                        $response['data']['bankiin']           = $request->bankiin;
                        self::saveTransaction($response['data'], $clientrefno, $request['auth_data']['user_id'], "002", $request->merchantCode,$request->aadhaarNo);
                        $message = $response['statuscode'] . ": Mini statement is pending.";
                        $resp = Response::pending($message, $data);
                    } else if ($response['statuscode'] === "UAD") {
                        $message = $response['statuscode'] . ": Please do 2 factor auth.";
                        $resp = Response::twoFactAuth($message, $data);
                    } else {
                        $message = $response['statuscode'] . ': ' . $response['status'];;
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
     * Get Balance function
     *
     * @param Request $request
     * @return void
     */
    public function transactionStatus(Request $request)
    {
      
        $substringToRemove = "SPI";
        $bcId = str_replace($substringToRemove, "", $request->merchantCode);
        $validation = new Validations($request);
        $validator = $validation->transactionStatus();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                if (!in_array($merchantCode, array('MC000123123', 'MC012345678'))) {
                    $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->select('is_active')->first();
                    if (empty($merchantExists)) {
                        $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                    } else {
                        if ($merchantExists->is_active == 0) {
                            $validator->errors()->add('merchantCode', 'This merchant is inactive.');
                        } elseif ($merchantExists->is_active == 2) {
                            $validator->errors()->add('merchantCode', 'Please upload kyc docs.');
                        }
                    }
                }
            }
            $clientRefIdCheck = AepsTransaction::where([
                'user_id' => $request['auth_data']['user_id'],
                'client_ref_id' => $request->clientRefNo, 'merchant_code' => $request->merchantCode
            ])->first();
            
          
            if (empty($clientRefIdCheck)) {
                $validator->errors()->add('clientRefNo', 'This client ref no is not valid.');
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {
            
             $clientRefIdCheck = AepsTransaction::where([
                'user_id' => $request['auth_data']['user_id'],
                'client_ref_id' => $request->clientRefNo, 'merchant_code' => $request->merchantCode
             ])->first(['aadhaar_no','transaction_type']);
            
            $params = [
                'externalRef'      => $request->clientRefNo,
                'transactionDate'  => $request->transactionDate,
                'source'           => 'ORDER',
            ];
            $requestType = 'statuscheck';
            $returnResp = self::sendUATResponse($request['auth_data']['user_id'], $requestType, $request->merchantCode, $params);
            if ($returnResp == 'production') {
                $response = $this->APICaller($params, $requestType, $request['auth_data']['user_id'],$bcId);
                if (isset($response['statuscode'])) {
                    $statusCode = isset($response['data']['transactionStatusCode']) ? $response['data']['transactionStatusCode'] : $response['transactionStatusCode'];
                    $data = self::getResponse($response, $request->clientRefNo,$request->merchantCode,$clientRefIdCheck->aadhaar_no,$clientRefIdCheck->transaction_type);
                    if ($response['statuscode'] === "TXN" && $response['data']['transactionStatusCode'] === "TXN") {
                        $message = $response['statuscode'] . ": Transactions status fetched successfully.";

                        AepsTransaction::where(['user_id' => $request['auth_data']['user_id'], 'client_ref_id' => $request->clientRefNo, 'merchant_code' => $request->merchantCode])->update([
                            'status' => 'success',
                            'resp_stan_no' => @$response['data']['transactionReferenceId'],
                            'rrn' => @$response['data']['order']['externalRef']
                        ]);
                        $resp = Response::success($message, $data, 200);
                    } else if ($response['statuscode'] === "TXN" && $response['data']['transactionStatusCode'] === "TUP") {
                        $aepsTransaction = AepsTransaction::where([
                            'user_id' => $request['auth_data']['user_id'],
                            'client_ref_id' => $request->clientRefNo, 'merchant_code' => $request->merchantCode
                        ])->first();
                        $data = [
                            "clientRefNo" => $request->clientRefNo,
                            "bankiin" => $aepsTransaction->bankiin,
                            "stanNo" => "",
                            "rrn" => "",
                            "bankMessage" => "",
                            "bankCode" => "",
                            "statusCode" => "",
                            "transactionType" => "CW",
                            "transactionDateTime" => $aepsTransaction->transaction_date,
                            "transactionAmount" => $aepsTransaction->transaction_amount,
                            "availableBalance" => "0",
                        ];
                        $message = $response['statuscode'] . ": Transactions status is pending.";
                        $resp = Response::pending($message, $data);
                    }else if ($response['statuscode']!== "TXN" && $response['data']['transactionStatusCode']!== "TUP") {
                        $message = $response['statuscode'] . ': ' . $response['data']['transactionStatus'];
                        AepsTransaction::where(['user_id' => $request['auth_data']['user_id'], 'client_ref_id' => $request->clientRefNo, 'merchant_code' => $request->merchantCode])->update([
                            'status' => 'failed',
                            'failed_message' => @$message,
                            'resp_stan_no' => @$response['data']['transactionReferenceId'],
                            'rrn' => @$response['data']['order']['externalRef']
                        ]);
                        $resp = Response::failed($message, $data);
                    } else {
                        $message = $response['statuscode'] . ': ' . $response['data']['transactionStatus'];
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
     * Get transactionCreateAndStatusUpdate function
     *
     * @param Request $request
     * @return void
     */
    public function transactionCreateAndStatusUpdate(Request $request)
    {

        if (!isset($request->clientRefId) || empty($request->clientRefId)) {
            $message = "Please send clientRefId";
            return Response::failed($message);
        }
        if (!isset($request->userId) || empty($request->userId)) {
            $message = "Please send userId";
            return Response::failed($message);
        }
        if (!isset($request->txnType) || empty($request->txnType)) {
            $message = "Please send txnType";
            return Response::failed($message);
        }

        $count = AepsTransaction::where('client_ref_id', $request->clientRefId)->count();
        if ($count == 0) {

            $params = [
                'refernceno'  => $request->clientRefId
            ];
            $requestType = 'statuscheck';

            $response = $this->APICaller($params, $requestType, $request->userId);
            if (isset($response['statuscode'])) {

                $statusCode = isset($response['data']['statuscode']) ? $response['data']['statuscode'] : $response['statuscode'];

                $data = self::getResponse($response, $request->clientRefId);
                if ($response['statuscode'] === "000") {
                    $date = date_create($response['data']['transactiondatetime']);
                    $response['data']['created_at'] = date_format($date, "Y-m-d H:i:s");

                    SDKController::txnCreateAndStatusUpdate($response['data'], $request->clientRefId, $request->userId, "000", $response['data']['merchantcode'], CommonHelper::case($response['data']['routetype'], 'l'), $response['data']['aadharnumber'], '1.0', '');
                    $message = $response['statuscode'] . ": Transactions status fetched successfully.";
                    $resp = Response::success($message, $data, 200);
                } else if ($response['statuscode'] === "001") {

                    if (isset($response['data'])) {
                        $date = date_create($response['data']['transactiondatetime']);
                        $response['data']['created_at'] = date_format($date, "Y-m-d H:i:s");

                        $response['data']['failed_message'] = isset($response['data']['bankmessage']) ? $response['data']['bankmessage'] : $response['message'];

                        SDKController::txnCreateAndStatusUpdate($response['data'], $request->clientRefId, $request->userId, "001", $response['data']['merchantcode'], CommonHelper::case($response['data']['routetype'], 'l'), $response['data']['aadharnumber'], '1.0', '');
                    }
                    $message = $response['statuscode'] . ': ' . $response['message'];

                    $resp = Response::failed($message, $data);
                } else if ($response['statuscode'] === "002") {

                    $resp['transactiontype'] = CommonHelper::case($response['data']['transactiontype'], 'l');
                    $resp['transactionAmount'] =  $response['data']['transactionAmount'];
                    $resp['merchantmobile'] = $response['data']['merchantmobile'];
                    $resp['bankiin'] = $response['data']['bankiin'];
                    $date = date_create($response['data']['transactiondatetime']);
                    $resp['created_at'] = date_format($date, "Y-m-d H:i:s");

                    SDKController::txnCreateAndStatusUpdate($resp, $request->clientRefId, $request['auth_data']['user_id'], "002", $response['data']['merchantcode'], CommonHelper::case($response['data']['routetype'], 'l'), $response['data']['aadharnumber'], '1.0', '');

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
            $message = "Record already created.";
            $resp = Response::failed($message);
        }
        return $resp;
    }


    public function getTxnId()
    {
        return CommonHelper::getRandomString();
    }

    public function state(Request $request, $stateId = '')
    {

        if (!empty($stateId)) {
            $state = State::select('id as stateId', 'state_name as stateName')->where('id', $stateId)->where('is_active', '1')->get();
        } else {
            $state = State::select('id as stateId', 'state_name as stateName')->where('is_active', '1')->get();
        }
        if (!empty($state) && count($state) > 0) {
            $message = "Record fetched successfully.";
            $resp = Response::success($message, $state, 200);
        } else {
            $message = "No record found";
            $resp = Response::failed($message);
        }
        return $resp;
    }

    public function district(Request $request, $stateId = '')
    {

        if (!empty($stateId)) {
            $district = District::select('id as districtId', 'district_title as districtName', 'state_id as stateId')->where('state_id', $stateId)->where('is_active', '1')->get();
        } else {
            $district = District::select('id as districtId', 'district_title as districtName', 'state_id as stateId')->where('is_active', '1')->get();
        }
        if (!empty($district) && count($district) > 0) {
            $message = "Record fetched successfully.";
            $resp = Response::success($message, $district, 200);
        } else {
            $message = "No record found";
            $resp = Response::failed($message);
        }
        return $resp;
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


    /**
     *  EKYC Status Check function
     *
     * @param Request $request
     * @return void
     */
    public function ekycCheckStatus(Request $request)
    {

        $validation = new Validations($request);
        $validator = $validation->ekycStatus();
        $validator->after(function ($validator) use ($request) {
            $merchantCode = isset($request->merchantCode) ? $request->merchantCode : "";
            if (!empty($merchantCode)) {
                $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->first();
                if (empty($merchantExists)) {
                    $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else {
            $userId = $request['auth_data']['user_id'];
            $merchantCode  = $request->merchantCode;
            $routeType  = '';
            $respData = [];
            $documentStatuus = self::documentsStatusOfMerchant($userId, $merchantCode);
            $respData['documents'] = [
                'kycStatus' => $documentStatuus['kycStatus'],
                'kycDate' => $documentStatuus['kycUpdateDate'],
                'remark' => $documentStatuus['remark']
            ];
            if (!empty($routeType)) {
                $agentDataEkycIsEmpty = DB::table('agents')
                    ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                    ->select('ekyc', DB::raw("json_extract(ekyc, '$.$routeType') as routeData"))
                    ->first();
                if (isset($agentDataEkycIsEmpty->ekyc) && !empty($agentDataEkycIsEmpty->ekyc) && !empty($agentDataEkycIsEmpty->routeData)) {
                    $data = json_decode($agentDataEkycIsEmpty->ekyc, TRUE);

                    if ($data[$routeType]['is_ekyc'] == 1) {
                        $respData[$routeType]['kycStatus'] = 'active';
                    } else if ($data[$routeType]['is_ekyc'] == 0) {
                        $respData[$routeType]['kycStatus'] = 'pending';
                    } else if ($data[$routeType]['is_ekyc'] == 2) {
                        $respData[$routeType]['kycStatus'] = 'rejected';
                    } else if ($data[$routeType]['is_ekyc'] == 3) {
                        $respData[$routeType]['kycStatus'] = 'expired';
                    }

                    $respData[$routeType]['kycDate'] =  isset($data[$routeType]['ekyc_date']) ? $data[$routeType]['ekyc_date'] : "NA";
                    $respData[$routeType]['kycExpireDate'] =  isset($data[$routeType]['ekyc_expire_date']) ? $data[$routeType]['ekyc_expire_date'] : "NA";
                    if (isset($data[$routeType]['is_ekyc'])) {
                        $message = 'Record fetched successfully.';
                        $resp = Response::success($message, $respData, 200);
                    } else {
                        $message = 'No ekyc details found.';
                        $resp = Response::failed($message, $respData);
                    }
                } else {
                    /*    $respData['routeType'] = $routeType;
                        $respData['ekycStatus'] = "";
                        $respData['ekycDate'] = "NA";
                        $respData['ekycExpireDate'] = "NA"; */

                    $message = 'No ekyc details found.';
                    $resp = Response::failed($message, $respData);
                }
            } else {
                $agentDataEkycIsEmpty = DB::table('agents')
                    ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                    ->select('ekyc')
                    ->first();
                $data = json_decode($agentDataEkycIsEmpty->ekyc, TRUE);

                if (!empty($data) && $data != 1 && count($data)) {
                    foreach ($data as $key => $datas) {

                        $ekycStatus = 'expired';
                        if ($datas['is_ekyc'] == 1) {
                            $ekycStatus = 'active';
                        } else if ($datas['is_ekyc'] == 0) {
                            $ekycStatus = 'pending';
                        } else if ($datas['is_ekyc'] == 2) {
                            $ekycStatus = 'rejected';
                        } else if ($datas['is_ekyc'] == 3) {
                            $ekycStatus = 'expired';
                        }
                        $respData[$key] = [
                            'kycStatus' => $ekycStatus,
                            'kycDate' => $datas['ekyc_date'],
                            'kycExpireDate' => $datas['ekyc_expire_date']
                        ];
                    }
                    $message = 'Record fetched successfully.';
                    $resp = Response::success($message, $respData, 200);
                } else {
                    $message = 'No ekyc details found.';
                    $resp = Response::failed($message, $data);
                }
            }
        }
        return $resp;
    }

    public function APICaller($params, $requestType, $userId, $bcId='')
    {
        $method = "POST";
        $txnId = isset($params['clientrefno']) ? $params['clientrefno'] : "";
        switch ($requestType) {
     
      
            case 'aepsMerchant':
                $request = $params;
                $url = $this->baseUrl . '/user/outlet/signup/minKyc';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret:'.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,"X-Ipay-Endpoint-Ip:137.59.52.66");
                break;
            case 'sendOtp':
                $request = $params;
                $url = $this->baseUrl . '/user/outlet/signup/initiate';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret: '.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,"X-Ipay-Endpoint-Ip:137.59.52.66");
                break;
            case 'twoFactorAuthCheck':
                $request = $params;
                $url = $this->baseUrl . '/fi/aeps/outletLoginStatus';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret: '.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,'X-Ipay-Outlet-Id: '.$bcId,"X-Ipay-Endpoint-Ip:137.59.52.66");
                break;
            case 'validateOTP':
                $request = $params;
                $url = $this->baseUrl . '/user/outlet/signup/validate';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret: '.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,"X-Ipay-Endpoint-Ip:137.59.52.66");
                break;
            case 'resendOTP':
                $request = $params;
                $url = $this->baseUrl . '/v2ekyc/resendotp';
                $header ='';
                break;
            case 'ekycBioMetric':
                $request = $params;
                $url = $this->baseUrl . '/v2ekyc/ekycbio';
                $header ='';
                break;
            case 'getBalance':
                $request = $params;
                $url = $this->baseUrl . '/fi/aeps/balanceInquiry';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret: '.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,'X-Ipay-Outlet-Id: '.$bcId,"X-Ipay-Endpoint-Ip:137.59.52.66");
                break;
            case 'withdrawal':
                $request = $params;
                $url = $this->baseUrl . '/fi/aeps/cashWithdrawal';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret: '.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,'X-Ipay-Outlet-Id: '.$bcId,"X-Ipay-Endpoint-Ip:137.59.52.66");
                break;
            case 'aadhaarPay':
                $request = $params;
                $url = $this->baseUrl . '/aadharpay/pay';
                $header ='';
                break;
            case 'statement':
                $request = $params;
                $url = $this->baseUrl . '/fi/aeps/miniStatement';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret: '.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,'X-Ipay-Outlet-Id: '.$bcId,"X-Ipay-Endpoint-Ip:137.59.52.66");
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
                $header ='';
                break;
            case 'statuscheck':
                $request = $params;
                $url = $this->baseUrl . '/reports/txnStatus';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret:'.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,"X-Ipay-Endpoint-Ip:137.59.52.66");
                break;
            case 'twoFactAuth':
                $request = $params;
                $url = $this->baseUrl . '/fi/aeps/outletLogin';
                $header =  array("Accept: application/json","Cache-Control: no-cache","Content-Type: application/json", "X-Ipay-Auth-Code:1",'X-Ipay-Client-Secret: '.$this->client_secret,'X-Ipay-Client-Id: '.$this->client_id,'X-Ipay-Outlet-Id: '.$bcId,"X-Ipay-Endpoint-Ip:137.59.52.66");
                break;
        }
      
        $result = CommonHelper::curl($url, $method, json_encode($request), $header, 'yes', $userId, 'aeps', $requestType, $txnId);
        $response = json_decode($result['response'], 1);
       
        return $response;
    }

    public static function getResponse($response, $clientRefNo, $merchantCode,$aadhaarNumber,$transactiontype)
    {
       
        $data = [];
        if (isset($response['data']['externalRef']) && isset($response['data']['bankName'])) {
            if (isset($transactiontype) && $transactiontype == 'MS') {
                $statement = isset($response['data']['miniStatement']) ? $response['data']['miniStatement'] : array();
                $data = [
                    'clientRefNo'          => $clientRefNo,
                    'ipayId'               => isset($response['data']['ipayId']) ? $response['data']['ipayId'] : "",
                    'operatorId'           => isset($response['data']['operatorId']) ? $response['data']['operatorId'] : "",
                    'bankName'             => isset($response['data']['bankName']) ? $response['data']['bankName'] : "",
                    'accountNumber'        => isset($response['data']['accountNumber']) ? $response['data']['accountNumber'] : "",
                    'merchantCode'         => isset($merchantCode) ? $merchantCode : "",
                    'aadhaarNumber'        => isset($aadhaarNumber) ? $aadhaarNumber : "",
                    'transactionMode'      => isset($response['data']['transactionMode']) ? $response['data']['transactionMode'] : "",
                    'payableValue'         => isset($response['data']['payableValue']) ? $response['data']['payableValue'] : "",
                    'transactionValue'     => isset($response['data']['transactionValue']) ? $response['data']['transactionValue'] : "",
                    'transactionDateTime'  => isset($response['timestamp']) ? $response['timestamp'] : "",
                    'updatedAt'            => date('Y-m-d H:i:s'),
                    'bankAccountBalance'   => isset($response['data']['bankAccountBalance']) ? $response['data']['bankAccountBalance'] : "",
                    'openingBalance'       => isset($response['data']['openingBalance']) ? $response['data']['openingBalance'] : "",
                    'closingBalance'       => isset($response['data']['closingBalance']) ? $response['data']['closingBalance'] : "",
                    'statement'            => $statement,
                ];
            } else {
                // dd($response);
                $data = [
                    'clientRefNo'          => $clientRefNo,
                    'ipayId'               => isset($response['data']['ipayId']) ? $response['data']['ipayId'] : "",
                    'operatorId'           => isset($response['data']['operatorId']) ? $response['data']['operatorId'] : "",
                    'bankName'             => isset($response['data']['bankName']) ? $response['data']['bankName'] : "",
                    'accountNumber'        => isset($response['data']['accountNumber']) ? $response['data']['accountNumber'] : "",
                    'merchantCode'         => isset($merchantCode) ? $merchantCode : "",
                    'aadhaarNumber'        => isset($aadhaarNumber) ? $aadhaarNumber : "",
                    'transactionMode'      => isset($response['data']['transactionMode']) ? $response['data']['transactionMode'] : "",
                    'payableValue'         => isset($response['data']['payableValue']) ? $response['data']['payableValue'] : "",
                    'transactionValue'     => isset($response['data']['transactionValue']) ? $response['data']['transactionValue'] : "",
                    'transactionDateTime'  => isset($response['timestamp']) ? $response['timestamp'] : "",
                    'updatedAt'            => date('Y-m-d H:i:s'),
                    'bankAccountBalance'   => isset($response['data']['bankAccountBalance']) ? $response['data']['bankAccountBalance'] : "",
                    'openingBalance'       => isset($response['data']['openingBalance']) ? $response['data']['openingBalance'] : "",
                    'closingBalance'       => isset($response['data']['closingBalance']) ? $response['data']['closingBalance'] : "",
                    
                    
                ];
            }
        }

        return $data;
    }

    public static function sendUATResponse($userId, $method, $transId, $params = array())
    {
        $env ='production';
       // $env = CommonHelper::uatOrProductionConfig($userId, 'aeps');
        if ($env == 'uat') {
            if (isset($transId)) {
                if ($transId == AEPS_SUCCESS_RESPONSE) {
                    if ($method == 'merchantOnBoard') {
                        $resp = UATResponse::response('aeps', $method, 'update_success', $transId, $params);
                    } else {
                        $resp = UATResponse::response('aeps', $method, 'success', $transId, $params);
                    }
                    $message = $resp['message'];
                    return Response::success($resp['message'], $resp['data'], 200);
                } else if ($transId == AEPS_FAILED_RESPONSE) {
                    $resp = UATResponse::response('aeps', $method, 'failed', $transId, $params);
                    return Response::failed($resp['message'], $resp['data']);
                } else {
                    if ($method == 'merchantOnBoard') {
                        if (empty($transId)) {
                            $resp = UATResponse::response('aeps', $method, 'success', $transId, $params);
                            $message = $resp['message'];
                            return Response::success($resp['message'], $resp['data'], 200);
                        }
                    } else {
                        return Response::failed("Merchant code not found", [], 200);
                    }
                }
            } else {
                return Response::failed("Merchant code not found", [], 200);
            }
        } else {
            return 'production';
        }
    }

    public static function saveTransaction($data = array(), $clientRefNo = "", $userId = 1, $statusCode = "", $merchantCode = "", $aadharNo = "")
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
            $aepsLog->resp_stan_no = isset($data['externalRef']) ? $data['externalRef'] : "";
            $aepsLog->resp_bank_code = isset($data['bankcode']) ? $data['bankcode'] : "";
            $aepsLog->resp_bank_message = isset($data['failed_message']) ? $data['failed_message'] : "";
            $aepsLog->transaction_amount = isset($data['transactionAmount']) ? $data['transactionAmount'] : "";
            $aepsLog->available_balance = isset($data['availablebalance']) ? $data['availablebalance'] : "";
            $aepsLog->mobile_no = isset($data['merchantmobile']) ? $data['merchantmobile'] : "";
            $aepsLog->ipayId = isset($data['ipayId']) ? $data['ipayId'] : "";
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
            /* if (isset($data['transactiontype']) && $data['transactiontype'] == 'MS') {
                $aepsLog->ms_data = json_encode($data['minidata']);
            } */
            // save commission data
            if (isset($data['transactiontype']) && $data['transactiontype'] == 'AP') {
                $aepsLog->fee = $commissionData['commission'];
                $aepsLog->tax = $commissionData['tds'];
                $aepsLog->margin = $commissionData['margin'];
            } else {
                $aepsLog->commission = $commissionData['commission'];
                $aepsLog->tds = $commissionData['tds'];
                $aepsLog->margin = $commissionData['margin'];
            }
            
            // end commision data
            $aepsLog->save();
        }
    }

    public static function aepsCredit($requestType, $userId, $clientRefId, $merchantCode, $routeType)
    {
        $fileName = 'public/' . $clientRefId . '.txt';
        //  Storage::disk('local')->put($fileName, 'start 1'.date('H:i:s'));
        try {
            //code...
            $transactionAmount = DB::table('aeps_transactions')->where('client_ref_id', $clientRefId)->first()->transaction_amount;
            if ($requestType  == 'be') {
                $slug = 'aeps_be';
                $transactionAmount = 1;
            } elseif ($requestType  == 'ms') {
                $slug = 'aeps_ms';
                $transactionAmount = 1;
            } elseif ($requestType  == 'cw') {
                $slug = 'aeps_cw';
            } elseif ($requestType  == 'ap') {
                $slug = 'aeps_ap';
                $transactionAmount = 1;
            }

            $getProductId = CommonHelper::getProductId($slug, 'aeps');
            $productId = isset($getProductId->product_id) ? $getProductId->product_id : "";
            $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, $transactionAmount, $userId);
            $commission = isset($getFeesAndTaxes['fee']) ? $getFeesAndTaxes['fee'] : 0;
            $tds = isset($getFeesAndTaxes['tax']) ? $getFeesAndTaxes['tax'] : 0;
            $margin = isset($getFeesAndTaxes['margin']) ? $getFeesAndTaxes['margin'] : "";
            dispatch(new \App\Jobs\CallbackAepsTransactionJob($clientRefId, $requestType, $userId, $commission, $tds, $margin))->delay(5);
        } catch (\Exception  $e) {
            Storage::disk('local')->append($fileName, $e . date('H:i:s'));
        }
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

    public static function ekycUpdate($userId, $merchantCode, $routeType)
    {
        $fileName = 'public/' . $merchantCode . '.txt';
        // Storage::disk('local')->put($fileName, 'start 1'.date('H:i:s'));
        $resp['status'] = false;
        $resp['message'] = "";
        try {
            //code...
            $routeType = CommonHelper::case($routeType, 'l');
            $agentDataEkycIsEmpty = DB::table('agents')
                ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                ->select('ekyc', DB::raw("json_extract(ekyc, '$.$routeType') as routeData"))
                ->first();
            $currDate = date('Y-m-d H:i:s');
            $expireDate = "NA";
            if ($routeType == 'airtel') {
                $expireDate = Carbon::now()->addMonth();
            }
            if (isset($agentDataEkycIsEmpty->ekyc) && !empty($agentDataEkycIsEmpty->ekyc)) {
                if (isset($agentDataEkycIsEmpty) && !empty($agentDataEkycIsEmpty->routeData)) {
                    $query = "UPDATE agents SET ekyc = JSON_SET(ekyc, '$.$routeType.is_ekyc', 1), ekyc = JSON_SET(ekyc, '$.$routeType.ekyc_date', '$currDate') , ekyc = JSON_SET(ekyc, '$.$routeType.ekyc_expire_date', '$expireDate') where merchant_code = '$merchantCode' and user_id = $userId";
                    $check = DB::select($query);
                } else {
                    $data = json_decode($agentDataEkycIsEmpty->ekyc, TRUE);
                    $arrayData = [$routeType => ["is_ekyc" => 1, "ekyc_date" => "$currDate", "ekyc_expire_date" => "$expireDate"]];
                    $jsonData = json_encode(array_merge($data, $arrayData));
                    $query = "UPDATE agents SET ekyc =  '$jsonData' where merchant_code = '$merchantCode' and user_id = $userId";
                    DB::select($query);
                }
            } else {
                $ekycData = '{"' . $routeType . '":{"is_ekyc":1,"ekyc_date":"' . $currDate . '","ekyc_expire_date":"' . $expireDate . '"}}';
                $query = "UPDATE agents SET ekyc = '$ekycData' where merchant_code = '$merchantCode' and user_id = $userId";
                DB::select($query);
            }
            $resp['status'] = true;
            $resp['message'] = "Ekyc update successfully.";
        } catch (\Exception  $e) {
            Storage::disk('local')->append($fileName, $e . date('H:i:s'));
            $resp['status'] = false;
            $resp['message'] = "Some error occured.";
        }

        return $resp;
    }

    public static function sendAEPSCallabck($data, $userId)
    {
        //send callback
        $getWebhooks = Webhook::where('user_id', $userId)->first();
        if ($getWebhooks) {
            $url = $getWebhooks['webhook_url'];
            $secret = $getWebhooks['secret'];
            if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
                $headers = [$getWebhooks['header_key'] => $getWebhooks['header_value']];
                WebhookHelper::AEPSTransaction($data, $url, $secret, $headers);
            } else {

                WebhookHelper::AEPSTransaction($data, $url, $secret);
            }
        }
    }
    public function ekycFileUpload(Request $request)
    {

        $validation = new Validations($request);
        $validator = $validation->aepsEkyc();
        $validator->after(function ($validator) use ($request) {
            $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->first();
            if (empty($merchantExists)) {
                $validator->errors()->add('merchantCode', 'This merchant code is not valid.');
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            $resp = Response::missing($message);
            return $resp;
        } else {

            $apikey = base64_decode(env('AEPS_KYC_KEY'));
            $url = env('AEPS_KYC_URL') . '/api/ekyc';
            $headers = array("hapikey: " . $apikey); // cURL headers for file uploading
            $ch = curl_init();

            $data = array(
                'merchantCode' => $_POST['merchantCode'],
                'aadhaarFront' => new \CURLFile($_FILES['aadhaarFront']['tmp_name'], $_FILES['aadhaarFront']['type'],  $_FILES['aadhaarFront']['name']),
                'aadhaarBack' => new \CURLFile($_FILES['aadhaarBack']['tmp_name'], $_FILES['aadhaarBack']['type'], $_FILES['aadhaarBack']['name']),
                'panFront' => new \CURLFile($_FILES['panFront']['tmp_name'], $_FILES['panFront']['type'], $_FILES['panFront']['name']),
                'shopPhoto' => new \CURLFile($_FILES['shopPhoto']['tmp_name'], $_FILES['shopPhoto']['type'], $_FILES['shopPhoto']['name']),
                'photo' => new \CURLFile($_FILES['photo']['tmp_name'], $_FILES['photo']['type'], $_FILES['photo']['name'])
            );

            @curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            @curl_setopt($ch, CURLOPT_URL, $url);

            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (sizeof($headers) > 0) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            $response = @curl_exec($ch);
            $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_errors = curl_error($ch);
            @curl_close($ch);

            $result = json_decode($response);

            $headerData = json_encode($headers);

            Apilog::create([
                "user_id" => 1,
                "integration_id" => 1,
                "product_id" => 1,
                "url" => $url,
                "txnid" => '',
                "modal" => 'ekyc-document',
                "method" => 'ekycFileUpload',
                "header" => $headerData,
                "request" => json_encode($data),
                "response" => $response,
            ]);
            if (isset($response) && isset($result->success) && $result->success) {
                $merchantExists = Agent::where(['user_id' => $request['auth_data']['user_id'], 'merchant_code' => $request->merchantCode])->first();
                $roots = array('paytm', 'sbm');
                AEPSController::ekycStatusLogs($merchantExists->user_id, $request->merchantCode, $merchantExists->ekyc);
                foreach ($roots as $root) {
                    AdminController::kycUpdate($merchantExists->user_id, $request->merchantCode, $root, 0);
                }
                AdminController::sendKycStatusEmail($merchantExists->user_id, $request->merchantCode, 0);
                $message = 'Files uploaded successfully';
                $agenData = Agent::where(['user_id' => $merchantExists->user_id, 'merchant_code' => $request->merchantCode])->first();
                AEPSController::ekycStatusLogs($merchantExists->user_id, $request->merchantCode, $agenData->ekyc);
                Agent::where('id', $merchantExists->id)->update(['is_attachment_send' => '0', 'documents_status' => 'pending', 'is_ekyc_documents_uploaded' => '1', 'ekyc_documents_uploaded_at' => date('Y-m-d H:i:s')]);
                $message = 'Files uploaded successfully';
                if (!empty($result->data)) {
                    $data = $result->data;
                } else {
                    $data = [];
                }
                return Response::success($message, $data, 200);
            } else {
                if (!empty($result) && isset($result->message)) {
                    $message = $result->message . ' ' . $result->error;
                    return Response::failed($message);
                } else {
                    $message = 'file not uploaded some errors.';
                    return Response::failed($message);
                }
            }
        }
    }

    public static function ekycStatusLogs($userId, $merchantCode, $data)
    {
        DB::table('ekyc_status_logs')->insert([
            'user_id' => $userId,
            'merchant_code' => $merchantCode,
            'ekyc_data' => $data,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function ekycStatusOfMerchant($userId, $merchantCode)
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



    /**
     * documentsStatusOfMerchant
     *
     * @param  mixed $userId
     * @param  mixed $merchantCode
     * @return void
     */
    public static function documentsStatusOfMerchant($userId, $merchantCode)
    {
        $resp['kycStatus'] = 'notfound';
        $resp['kycUpdateDate'] = '';
        $resp['remark'] = "No kyc details found.";
        try {
            $checkKycDoc = DB::table('agents')
                ->select('documents_status', 'documents_remarks', 'is_ekyc_documents_uploaded', 'doc_accepted_at', 'doc_rejected_at')
                ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                ->first();
            if (isset($checkKycDoc->documents_status) && !empty($checkKycDoc->documents_status)) {
                if ($checkKycDoc->documents_status == 'accepted') {
                    $resp['kycStatus'] = 'active';
                    $resp['remark'] = "Document approved";
                    $resp['kycUpdateDate'] = $checkKycDoc->doc_accepted_at;
                } else if ($checkKycDoc->documents_status == 'pending') {
                    if ($checkKycDoc->is_ekyc_documents_uploaded == '1') {
                        $resp['kycStatus'] = 'pending';
                        $resp['remark'] = "KYC is pending";
                    } else {
                        $resp['kycStatus'] = 'notfound';
                        $resp['remark'] = "Upload your KYC documents for activation.";
                    }
                } else if ($checkKycDoc->documents_status == 'rejected') {
                    $resp['kycStatus'] = 'rejected';
                    $resp['kycUpdateDate'] = $checkKycDoc->doc_rejected_at;
                    $resp['remark'] = "KYC rejected. " . $checkKycDoc->documents_remarks;
                } else {
                    $resp['kycStatus'] = 'notfound';
                    $resp['remark'] = "Upload your KYC documents for activation.";
                }
            } else {
                $resp['kycStatus'] = 'notfound';
                $resp['remark'] = "Upload your KYC documents for activation.";
            }
        } catch (\Exception $e) {
            $resp['kycStatus'] = 'notfound';
            $resp['remark'] = "Something Went Wrong ." . $e->getMessage();
        }
        return $resp;
    }
}
