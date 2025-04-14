<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use Validations\PanCardValidation as Validations;
use Exception;
use App\Helpers\ResponseHelper as Response;
use App\Helpers\TransactionHelper;
use App\Helpers\WebhookHelper;
use App\Http\Controllers\Api\v1\Callbacks\PANCallbackController;
use App\Models\PanCard;
use App\Models\Webhook;
use App\Services\PanCard\PanCardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * MATMController
 */
class PanCardController extends Controller
{

    public $userId, $clientRefId, $orderRefId;

    protected const URI = '/';


    /**
     * Method addAgent
     *
     * @param Request $request [explicite description]
     * @param PanCardService $service [explicite description]
     *
     * @return void
     */
    public function addAgent(Request $request, PanCardService $service)
    {
        try {

            $validations = Validations::init($request, 'addAgent');

            if ($validations['status'] == true) {
                $clientRefId =  CommonHelper::getRandomString('PCR', false);
                $pan  = PanCard::
                        where('mobile', $request->mobile)
                        ->where('email', $request->email)
                        ->first();
                if ($pan) {
                    if (!empty($pan->psa_id)) {
                        $data['psaId'] = $pan->psa_id;
                        return Response::failed('The Agent already registered. Your psaId: '.$pan->psa_id, $data);
                    }
                }



                    $name  = $request->firstName;
                    if (!empty( $request->middleName)) {
                        $name  = $name." ".$request->middleName;
                    }

                    if (!empty($request->lastName)) {
                        $name  = $name." ".$request->lastName;
                    }
                    $body = [
                        "PsaName" =>  $name,
                        "Email" => $request->email,
                        "DOB" => date("d/m/Y", strtotime($request->dob)) ,
                        "PinCode" => $request->pinCode,
                        "Phone1" => $request->mobile,
                        "Phone2" =>  $request->mobile,
                        "Location" => $request->address,
                        "ClientRefId" => $clientRefId,
                        "State" => $request->stateId,
                        "District" => $request->districtId,
                        "PanNo" => $request->pan,
                        "AadhaarNo" => $request->aadhaar
                    ];
                    $url = 'api/UTIPAN/VleOnbording';

                $response = $service->init($body, self::URI .$url , 'addAgent', $request['auth_data']['user_id'], 'yes');


                $statusCode =  @$response['response']['response']->StatusCode;
                $message =  @$response['response']['response']->Message;

                if (isset($statusCode)) {
                    if ($statusCode == "000") {
                        $obj = PanCard::create([
                            'user_id' => $request['auth_data']['user_id'],
                            'mobile' => $request->mobile,
                            'email'  => $request->email,
                            'first_name'  => $request->firstName,
                            'middle_name'  => $request->middleName,
                            'last_name'  => $request->lastName,
                            'dob'  => $request->dob,
                            'gender'  => $request->gender,
                            'address'  => $request->address,
                            'pan'  => $request->pan,
                            'aadhaar'  => $request->aadhaar,
                            'pin'  => $request->pinCode,
                            'state'  => $request->stateId,
                            'client_ref_id'  => $clientRefId,
                            'district'  => $request->districtId,
                            'psa_id' => @$response['response']['response']->Data[0]->PsaId,
                            'status' => '1',
                        ]);

                            $message = $statusCode . ": Agent onboarded successfully.";

                            $data['psaId'] = @$response['response']['response']->Data[0]->PsaId;
                            $data['email'] = $request->email;
                            $data['mobile'] = $request->mobile;

                            $resp = Response::success($message, $data);

                    } else {
                        $data = [];
                        $pasId = @$response['response']['response']->usercode;
                        if (empty($pasId)) {
                            $pasId = @$response['response']['response']->Data[0]->PsaId;
                        }

                        if (isset($pasId)) {
                            $message = $statusCode . ': ' . $message;
                            $obj = PanCard::create([
                                'user_id' => $request['auth_data']['user_id'],
                                'mobile' => $request->mobile,
                                'email'  => $request->email,
                                'first_name'  => $request->firstName,
                                'middle_name'  => $request->middleName,
                                'last_name'  => $request->lastName,
                                'dob'  => $request->dob,
                                'gender'  => $request->gender,
                                'address'  => $request->address,
                                'pan'  => $request->pan,
                                'aadhaar'  => $request->aadhaar,
                                'pin'  => $request->pinCode,
                                'state'  => $request->stateId,
                                'client_ref_id'  => $clientRefId,
                                'district'  => $request->districtId,
                                'psa_id' => @$response['response']['response']->Data[0]->PsaId,
                                'status' => '1',
                            ]);
                                $data['psaId'] = $pasId;
                        } else {
                            $message = $statusCode . ': ' . $message;
                        }
                        if (str_contains($message, "This EmailId already exsists") || str_contains($message, "This MobileNo already exsists")) {
                            $psaId = @$response['response']['response']->Data[0]->PsaId;
                            $message = "The Agent already registered. Your psaId: ".$psaId;
                         }
                        $resp = Response::failed($message, $data);
                    }
                } else {
                    $message = "Something went wrong";
                    $resp = Response::failed($message, @$response['response']['response']);
                }
                return $resp;
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Method initTxn
     *
     * @param Request $request [explicite description]
     * @param PanCardService $service [explicite description]
     * @param String $type [explicite description]
     *
     * @return void
     */
    public function initTxn(Request $request, PanCardService $service )
    {
        try {

            $validations = Validations::init($request, 'initTxn');

            if ($validations['status'] == true) {
                $type = 'uti';
                if (isset($request->routeType) && !empty($request->routeType)) {
                    $type = $request->routeType;
                }

                if (!in_array($type, ['nsdl', 'uti'])) {
                    return Response::failed('Invalid route type');
                }

                $pan  = PanCard::where('psa_id', $request->psaId)
                                ->where('user_id', $request['auth_data']['user_id'])
                                ->where('mobile', $request->mobile)
                                ->first();

                if (empty($pan)) {
                    return Response::failed('Agent not found.');
                } else if ($pan->status == '0') {
                    return Response::failed('Agent status inactive.');
                }else if ($pan->status == '2') {
                    return Response::failed('Agent status blocked.');
                }

                if ($type == 'nsdl') {
                   return OffersAuthController::generateAuthTokenForPan($request);
                } else {
                    $clientRefId =  CommonHelper::getRandomString('UTI', false);
                    $body = [
                        "userHandle" => $request->psaId,
                        "transId" => $clientRefId,
                    ];
                    $url = "api/UTIPAN/UTIPanCheckSum";
                }

                $response = $service->init($body, self::URI . $url, 'initTxn', $request['auth_data']['user_id'], 'yes');

                if ($type == 'nsdl') {
                    $statusCode =  @$response['response']['response']->statuscode;
                    $message =  @$response['response']['response']->message;
                 } else {
                     $statusCode =  @$response['response']['response']->StatusCode;
                     $message =  @$response['response']['response']->Message;
                 }
                 if (isset($statusCode)) {
                     if ($statusCode == "000") {

                        $message = $statusCode . ": Agent onboarded successfully.";
                        if ($type == 'nsdl') {
                            $data['psaId'] = $request->psaId;
                            $data['orderRefId'] = "";
                            $data['url'] = env('NSDL_WEB_URL').$response['response']['response']->text;
                            $resp = Response::success($message, $data);
                        } else {
                            $checksum = $response['response']['response']->Data[0]->checksum;
                            $entityId = $response['response']['response']->Data[0]->entityId;
                            $data['psaId'] = $request->psaId;
                            $data['orderRefId'] = $response['response']['response']->Data[0]->transId;
                            $data['url'] = env('UTI_WEB_URL')."userHandle=".$request->psaId."&transId=".$data['orderRefId']."&checksum=".$checksum."&entityId=".$entityId;
                            $resp = Response::success($message, $data);
                        }
                    } else {
                        $message = $statusCode . ': ' . $message;
                        $resp = Response::failed($message);
                    }
                } else {
                    $message = "Something went wrong";
                    $resp = Response::failed($message, $response);
                }
                return $resp;
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Method agentDetails
     *
     * @param Request $request [explicite description]
     * @param PanCardService $service [explicite description]
     *
     * @return void
     */
    public function txnStatus(Request $request, PanCardService $service)
    {
        try {

            $validations = Validations::init($request, 'txnStatus');

            if ($validations['status'] == true) {
                $txn = DB::table('pan_txns')
                        ->select('order_ref_id', 'user_id', 'txn_type')
                        ->where('user_id', $request['auth_data']['user_id'])
                        ->where('order_ref_id', $request->orderRefId)
                        ->first();
                if (empty($txn)) {
                    return Response::failed('The order ref id is invalid.');
                }

                if ($txn->txn_type == 'nsdl') {
                    $body = [
                        "orderid" =>  $request->orderRefId
                    ];
                    $url = 'common/getorderstatus';
                } else {

                    $body = [
                        "refernceid" =>  $request->orderRefId,
                        "secretkey" => base64_decode(env('PANCARD_UTI_SECRET')),
                        "saltkey" => base64_decode(env('PANCARD_UTI_SALT')),
                    ];
                    $url = 'api/Common/PSAstatuscheck';
                }

                $response = $service->init($body, self::URI . $url, 'pancardStatusCheck', $request['auth_data']['user_id'], 'yes', 'pancard');

                if ($txn->txn_type == 'nsdl') {
                    if (isset($response['response']['response']->statuscode)) {
                        if ($response['response']['response']->statuscode == "000" && $response['response']['response']->status == 'S') {
                                DB::table('pan_txns')->where([
                                    'order_ref_id' =>  $request->orderRefId
                                ])->update([
                                        'status' => 'success',
                                        'txn_status' => 'S'
                                    ]);
                            $message = @$response['response']['response']->statuscode . ": ".@$response['response']['response']->message;

                            $data["txnId"] = @$response['response']['response']->txnid;
                            $data["orderRefId"] = @$request->orderRefId;
                            $data["statusCode"] = @$response['response']['response']->statuscode;
                            $resp = Response::success($message, @$data);
                        } else if ($response['response']['response']->statuscode == "001") {
                            $failedmessage = isset($response['response']['response']->message) ? $response['response']['response']->message : $response['response']['response']->message;
                            self::fundRefunded($txn->user_id, $request->orderRefId,  @$response['response']['response']->message,  @$response['response']['response']->statuscode);
                            $data["txnId"] = @$response['response']['response']->txnid;
                            $data["orderRefId"] = @$request->orderRefId;
                            $data["statusCode"] = @$response['response']['response']->statuscode;

                            $resp = Response::failed($failedmessage, @$data);
                        } else {
                            $data["orderRefId"] = @$request->orderRefId;
                            $data["txnId"] = @$response['response']['response']->txnid;
                            $data["statusCode"] = @$response['response']['response']->statuscode;
                            $failedmessage =isset($response['response']['response']->message) ? $response['response']['response']->message : $response['response']['response']->message;
                            $resp = Response::pending($failedmessage, @$data);
                        }
                    } else {
                        $message = "Something went wrong";
                        $resp = Response::failed($message, $response);
                    }
                } else {
                    if (isset($response['response']['response']->StatusCode)) {
                        if ($response['response']['response']->StatusCode == "000") {

                            if ($response['response']['response']->Data[0]->status == 'S') {
                                DB::table('pan_txns')->where([
                                    'order_ref_id' =>  $request->orderRefId
                                ])->update([
                                        'status' => 'success',
                                        'txn_status' => 'S'
                                    ]);
                                $message = @$response['response']['response']->StatusCode . ": ".
                                @$response['response']['response']->Message;


                                $data["txnId"] = @$response['response']['response']->Data[0]->applicationNo;
                                $data["orderRefId"] = @$request->orderRefId;
                                $data["statusCode"] = @$response['response']['response']->StatusCode;

                                $resp = Response::success($message, @$data);
                            } else if ($response['response']['response']->Data[0]->status == 'P') {
                                $failedmessage =isset($response['response']['response']->Message) ? $response['response']['response']->Message : $response['response']['response']->Message;

                                $data["txnId"] = @$response['response']['response']->Data[0]->applicationNo;
                                $data["orderRefId"] = @$request->orderRefId;
                                $data["statusCode"] = @$response['response']['response']->StatusCode;

                                $resp = Response::pending($failedmessage, @$data);
                            } else if ($response['response']['response']->Data[0]->status == 'F'){
                                $failedmessage = isset($response['response']['response']->Message) ? $response['response']['response']->Message : @$response['response']['response']->Message;

                                $data["txnId"] = @$response['response']['response']->Data[0]->applicationNo;
                                $data["orderRefId"] = @$request->orderRefId;
                                $data["statusCode"] = @$response['response']['response']->StatusCode;
                                self::fundRefunded($txn->user_id, $request->orderRefId,  @$response['response']['response']->Message,  @$response['response']['response']->StatusCode);

                                $resp = Response::failed($failedmessage, @$data);
                            } else {
                                $failedmessage =isset($response['response']['response']->Message) ? $response['response']['response']->Message : $response['response']['response']->Message;
                                $resp = Response::pending($failedmessage, @$response['response']['response']);
                            }
                        } else if ($response['response']['response']->StatusCode == "001") {

                            $data["txnId"] = @$response['response']['response']->Data[0]->applicationNo;
                            $data["orderRefId"] = @$request->orderRefId;
                            $data["statusCode"] = @$response['response']['response']->StatusCode;

                            $failedmessage =isset($response['response']['response']->Message) ? $response['response']['response']->Message : @$response['response']['response']->Message;
                            self::fundRefunded($txn->user_id, $request->orderRefId,  @$response['response']['response']->Message,  @$response['response']['response']->StatusCode);
                            $resp = Response::failed($failedmessage, @$data);
                        } else {
                            $data["txnId"] = @$response['response']['response']->Data[0]->applicationNo;
                            $data["orderRefId"] = @$request->orderRefId;
                            $data["statusCode"] = @$response['response']['response']->StatusCode;

                            $failedmessage =isset($response['response']['response']->Message) ? $response['response']['response']->Message : @$response['response']['response']->Message;
                            $resp = Response::pending($failedmessage, @$data);
                        }
                    } else {
                        $message = "Something went wrong";
                        $resp = Response::failed($message, $response);
                    }
                }
                return $resp;
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Method txnInitFromNSDl
     *
     * @param Request $request [explicite description]
     * @param PanCardService $service [explicite description]
     *
     * @return void
     */
    public static function txnInitFromNSDl($request,  $service, $userId)
    {
        try {

            $validations = Validations::init($request, 'txnInitFromNSDl');

            if ($validations['status'] == true) {
                $txn = DB::table('pan_txns')
                        ->where('order_ref_id', $request->orderRefId)
                        ->first();
                if (!empty($txn)) {
                    return Response::failed('The order ref id is invalid.');
                }

                $agent = DB::table('pan_agents')
                    ->where('psa_id', $request->psaId)
                    ->where('user_id', $userId)
                    ->first();
                if (empty($agent)) {
                    return Response::failed('The psaId is invalid.');
                }
                $isPhysical = 'pan_digital';

                if (!empty($request->isPhysical) && $request->isPhysical == 'Y') {
                    $isPhysical = 'pan_physical';
                }

                $taxData = PanCardController::getFeeAndTaxs(@$agent->user_id, $isPhysical);

                if(!empty($request->pan)) {
                    $pan = @$request->pan;
                    $panType  = "UPDATE";
                    $url = 'nsdl/xxettlepan/Pancorrection';
                } else {
                    $pan = "";
                    $panType  = "NEW";
                    $url = 'nsdl/xettlepan/newpanrequat';
                }
                DB::table('pan_txns')
                    ->insert([
                        'order_ref_id' => @$request->orderRefId,
                        'route_type' => 'NEW',
                        'txn_status' => 'P',
                        'status' => 'queued',
                        'psa_code' =>  @$request->psaId,
                        'email' => @$request->email,
                        'txn_type' => 'nsdl',
                        'mobile' => @$request->mobile,
                        'phy_pan_is_req' => @$request->isPhyPan,
                        'pan_type' =>  @$panType,
                        'name_on_pan' =>  @$request->nameOnPan,
                        'tax' => 0,
                        'fee' => $taxData['fee'],
                        'margin' => $taxData['margin'],
                        'service_id' => PAN_CARD_SERVICE_ID,
                        'user_id' => @$userId,
                        'pan' => @$pan,
                    ]);
                    $WebHookStatus = 0;
                    $panCardWebHookResponse = PanCardController::sendFirstNsdlWebhook($request, $userId);
                    if (!$panCardWebHookResponse) {
                        DB::table('pan_txns')
                        ->where( 'order_ref_id' , @$request->orderRefId)
                        ->update([
                            'status' => 'failed',
                            'failed_message' => 'webhook_not_receive'
                        ]);
                        $message = "Webhook response not send";
                        return Response::failed($message);
                    } else {
                        if (@$panCardWebHookResponse->status == 200 && @$panCardWebHookResponse->orderRefId == $request->orderRefId) {
                            $WebHookStatus = 1;
                        }
                    }
                    if ($WebHookStatus == 0) {
                        DB::table('pan_txns')
                            ->where( 'order_ref_id' , @$request->orderRefId)
                            ->update([
                                'status' => 'failed',
                                'failed_message' => 'webhook_not_receive'
                            ]);
                        $message = "Webhook response not send";
                        return Response::failed($message);
                    }
                $txnStatus = PANCallbackController::moveOrderToProcessingByOrderId(@$userId, @$request->orderRefId);

                if ($txnStatus['status']) {

                    $body = [
                        "usercode" => $request->psaId,
                        "clientrefid" => $request->orderRefId,
                        "firstname" => $request->firstName,
                        "middlename" => $request->middleName,
                        "lastname" => $request->lastName,
                        "nameonpan" => $request->nameOnPan,
                        "title" => $request->title,
                        "gender" => $request->gender,
                        "dob" => $request->dob,
                        "mobile" => $request->mobile,
                        "emailid" => $request->email,
                        "pincode" => $request->pinCode,
                        "applnMode" => $request->applnMode,
                        "phyPanIsReq" => $request->isPhyPan,
                        "pan" => $pan
                    ];
 
                    $response = $service->init($body, self::URI . $url, 'initNsdlPan', $userId, 'yes', 'pancard');

                        if (isset($response['response']['response']->statuscode)) {
                            if ($response['response']['response']->statuscode == "000") {
                                $message = @$response['response']['response']->statuscode . ": ".@$response['response']['response']->message;

                                $resp = Response::success($message, @$response['response']['response']);
                            } else if ($response['response']['response']->statuscode == "001") {
                                $failedmessage = isset($response['response']['response']->message) ? $response['response']['response']->message : $response['response']['response']->message;
                                self::fundRefunded($agent->user_id, $request->orderRefId,  @$response['response']['response']->message,  @$response['response']['response']->statuscode);
                               
                                $resp = Response::failed($failedmessage, @$response['response']['response']);
                            } else {
                                $data["orderRefId"] = @$request->orderRefId;

                                $failedmessage =isset($response['response']['response']->message) ? $response['response']['response']->message : $response['response']['response']->message;
                                $resp = Response::pending($failedmessage, @$response['response']['response']);
                            }
                        } else {
                            $message = "Something went wrong";
                            $resp = Response::failed($message, $response);
                        }
                        return $resp;
                } else {
                    $res = ['status' => $txnStatus['status'], 'message' => $txnStatus['message']];
                    $resp = Response::failed($txnStatus['message'], $res);
                }
                return $resp;
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            return ResponseHelper::failed($e->getMessage());
        }
    }


    /**
     * Method getFeeAndTaxs
     *
     * @param $userId $userId [explicite description]
     * @param $slug $slug [explicite description]
     *
     * @return void
     */
    public static function getFeeAndTaxs($userId, $slug)
    {
        $resp['fee'] = 0;
        $resp['tax'] = 0;
        $resp['margin'] = "";
        $resp['product_id'] = "";
        $resp['message'] = "init";
        try {
            //code...
            $getProductId = CommonHelper::getProductId( $slug, 'pan');
            $productId = isset($getProductId->product_id) ? $getProductId->product_id : "";
            $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, 0, $userId);
            $resp['fee'] = isset($getFeesAndTaxes['fee']) ? $getFeesAndTaxes['fee'] : 0;
            $resp['tax'] = isset($getFeesAndTaxes['tax']) ? $getFeesAndTaxes['tax'] : 0;
            $resp['margin'] = isset($getFeesAndTaxes['margin']) ? $getFeesAndTaxes['margin'] : "";
            $resp['product_id'] = isset($productId) ? $productId : "";
            $resp['message'] = "success";
        } catch (\Exception  $e) {
            $resp['fee'] = 0;
            $resp['tax'] = 0;
            $resp['margin'] = "";
            $resp['message'] = "no record found. " . $e->getMessage();
        }

        return $resp;
    }

    public static function sendSecondCallabck($data, $userId, $route)
    {
       //send callback
       $getWebhooks = Webhook::where('user_id', $userId)->first();
       if ($getWebhooks) {
           $url = $getWebhooks['webhook_url'];
           $secret = $getWebhooks['secret'];
           if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
               $headers = [$getWebhooks['header_key'] => $getWebhooks['header_value']];
                WebhookHelper::PANSecondTransaction($data, $url, $secret, $headers, $route);
           } else {

                WebhookHelper::PANSecondTransaction($data, $url, $secret, '', $route);
           }
       }

    }

    public static function sendFirstCallabck($data, $userId, $route, $status)
    {
       //send callback
       $getWebhooks = Webhook::where('user_id', $userId)->first();
       if ($getWebhooks) {
           $url = $getWebhooks['webhook_url'];
           $secret = $getWebhooks['secret'];
           if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
               $headers = [$getWebhooks['header_key'] => $getWebhooks['header_value']];
                WebhookHelper::PANFirstTransaction($data, $url, $secret, $headers, $route, $status);
           } else {

                WebhookHelper::PANFirstTransaction($data, $url, $secret, '', $route, $status);
           }
       }

    }

    /**
     * Method fundRefunded
     *
     * @param $userId $userId [explicite description]
     * @param $orderRefId $orderRefId [explicite description]
     * @param $failedMessage $failedMessage [explicite description]
     * @param $statusCode $statusCode [explicite description]
     *
     * @return array
     */
    public static function fundRefunded($userId, $orderRefId, $failedMessage, $statusCode): array
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {

            $OrderData = DB::table('pan_txns')
                ->select('order_ref_id', 'user_id', 'service_id')
                ->where(['status' => 'pending',  'order_ref_id' => $orderRefId])
                ->first();

            if (isset($OrderData) && !empty($OrderData)) {
                $id = @DB::table('user_services')->where([
                    'user_id' => $userId,
                    'service_id' => PAN_CARD_SERVICE_ID
                ])->first()->id;
                $txn = CommonHelper::getRandomString('TXN', false);
                DB::select("CALL panStatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id, $id, 'failed', '" . $txn . "', '" . $failedMessage . "', '" . $statusCode . "','', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);

                if ($response['status'] == '1') {
                    $resp['status'] = true;
                    $resp['message'] = 'Fund refunded successfully.';
                } else {
                    $resp['status'] = false;
                    $resp['message'] = $response['message'];
                }
            }
        } catch (\Exception $e) {
            $resp['status'] = false;
            $resp['message'] = SOMETHING_WENT_WRONG;
        }
        return $resp;
    }

    public static function sendFirstNsdlWebhook($data, $userId) {
        $getWebhooks = Webhook::where('user_id', $userId)->first();
        if ($getWebhooks) {
            $url = $getWebhooks['webhook_url'];
            $secret = $getWebhooks['secret'];

            $arrayPayLoad['event'] = 'pan.request.pending';
            $arrayPayLoad['code'] = "0x0206";
            $arrayPayLoad['message'] = 'Transaction pending';

            $isPhysical = 'Digital';
            if (!empty($data['CouponType']) && $data['CouponType'] == 'Physical') {
                $isPhysical = 'Physical';
            } else if (!empty($data['isPhyPan']) && $data['isPhyPan'] == 'Y') {
                $isPhysical = 'Physical';
            }

            $arrayPayLoad['data'] = [
                'orderRefId' => !empty($data['ServiceProviderId']) ? $data['ServiceProviderId'] : @$data['orderRefId'],
                'appNo' =>  !empty($data['UTIapplicationNo']) ? $data['UTIapplicationNo'] : @$data['orderid'],
                'psaId' => !empty($data['VleID']) ? $data['VleID'] : $data['psaId'],
                'status' => 'P',
                'nameOnPan' => !empty($data['nameOnPan']) ? $data['nameOnPan'] : "",
                'panType' => !empty($data['panType']) ? $data['panType'] : "",
                'operatorTxnId' => !empty($data['OperatorTxnId']) ? $data['OperatorTxnId'] : "",
                'psaMobile' =>  !empty($data['psamobile']) ? $data['psamobile'] : "",
                'couponType' =>  $isPhysical,
                'routeType' =>  'nsdl'
            ];

            $payloadJson = json_encode($arrayPayLoad);
            $signature = hash_hmac('sha256', $payloadJson,  $secret);
            $header = ['Signature' => $signature];
            if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
                $header = [
                    $getWebhooks['header_key'] => $getWebhooks['header_value'],
                    'Signature' => $signature
                ];
            }
            $responseData = Http::timeout(300)
                ->withHeaders($header)->accept('application/json')
                ->post($url, $arrayPayLoad);
            $response = $responseData ? $responseData->getBody()->getContents() : null;
            $resp['statusCode'] = $responseData ? $responseData->getStatusCode() : 500;

            if (!empty($responseData->getStatusCode()) && $responseData->getStatusCode() == 200) {
                $resps = (object)json_decode($response);
                $insertArr['uuid'] = self::uuid4();
                $insertArr['httpVerb'] = '"post"';
                $insertArr['webhookUrl'] = $url;
                $insertArr['attempt'] = 1;
                $insertArr['payload'] = $payloadJson;
                $insertArr['response'] = $response;
                $insertArr['created_at'] = date('Y-m-d H:i:s');
                $insertArr['updated_at'] = date('Y-m-d H:i:s');
                \App\Models\MWebhookLog::insertLog($insertArr);
                return $resps;
            }

            $resp = (object)json_decode($response);
            $insertArr['uuid'] = self::uuid4();
            $insertArr['httpVerb'] = '"post"';
            $insertArr['webhookUrl'] = $url;
            $insertArr['payload'] = $payloadJson;
            $insertArr['response'] = $resp;
            $insertArr['errorMessage'] = 'Something went wrong';
            $insertArr['errorType'] = @$resp['statusCode'];
            $insertArr['created_at'] = date('Y-m-d H:i:s');
            $insertArr['updated_at'] = date('Y-m-d H:i:s');
            \App\Models\MWebhookLog::insertLog($insertArr);
            return false;
        }
        return false;
    }

    public static function uuid4() {
        /* 32 random HEX + space for 4 hyphens */
        $out = bin2hex(random_bytes(18));

        $out[8]  = "-";
        $out[13] = "-";
        $out[18] = "-";
        $out[23] = "-";
        /* UUID v4 */
        $out[14] = "4";

        /* variant 1 - 10xx */
        $out[19] = ["8", "9", "a", "b"][random_int(0, 3)];

        return $out;
    }
}
