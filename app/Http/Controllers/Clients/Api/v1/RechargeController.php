<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\DthRecharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Validations\RechargeValidation as Validations;
use Validations\OrderValidation;
use App\Helpers\ResponseHelper as Response;
use App\Helpers\TransactionHelper;
use App\Helpers\WebhookHelper;
use App\Models\Recharge;
use App\Models\Webhook;
use App\Services\RechargeService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserService;
use App\Models\RechargeData;
use App\Models\RetaillerBill;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\UpiCollect;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\PostPaidRecharge;
use App\Models\ElectricityRecharge;
use App\Models\LicRecharge;
use App\Models\CreditcardRecharge;
use Exception;
use Illuminate\Support\Facades\DB;

class RechargeController extends Controller
{


    protected const DOCUMENT_URI = '/rechargesplan/api/XTLPlanAPI';
    protected const DOCUMENT_RECHARGE_URI = '/xettlerecharge/api';
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
        $this->baseUrl = env('RECH_BASE_URL_UNIQUE');
        $this->user    = env('RECH_USER_UNIQUE');
        $this->pass    = env('RECH_PASS_UNIQUE');
     
    }

    /**
     * Recharge planAndOffers function
     *
     * @param Request $request
     * @return void
     */
    public function planAndOffers(Request $request, RechargeService $rechageService)
    {
        try {
    
            $validations = Validations::init($request, 'planAndOffers');
        
            if ($validations['status'] == true) {


                $respData = self::validateRequest($request);
                if ($respData['status'] == false) {
                    return Response::failed($respData['message']);
                }
     
                $body = $respData['data'];
                $uri = $respData['uri'];
                $responseMethod = $respData['responseMethod'];
                $response = $rechageService->init($body, self::DOCUMENT_URI . '/'.$uri, 'getPlan', $request['auth_data']['user_id'], 'yes');
           
                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {

                    if (isset($response['response']['response']->data->status) && @$response['response']['response']->data->status == 0) {

                        return Response::failed(preg_replace('/[A-Z]/', ' $0', @$response['response']['response']->data->desc));
                    }
           
                    $resp = self::respFormat($response['response']['response'], $responseMethod, 1);

                    if (@$resp['status'] === 0) {
                        return Response::failed(@$resp['message']);
                    }
                    return Response::success('Plan fetched successfully.', $resp);
                } else {
                    return Response::failed(@$response['response']['response']->message, $response['response']['response']);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * Recharge Plan function
     *
     * @param Request $request
     * @return void
     */
    public function rechargePlan(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'rechargePlan');
            if ($validations['status'] == true) {
                
                $data = $request->all();
                $body = [
                    "cricle" => @$data['circle'],
                    "Operator" => @$data['operator']
                ];

                $response = $rechageService->init($body, self::DOCUMENT_URI . '/simpleplan', 'getPlan', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {

                    $resp = self::respFormat($response['response']['response'], 'plan', 1);
                    if (@$resp['status'] === 0) {
                        return Response::failed(@$resp['message']);
                    }
                    return Response::success('Plan fetched successfully.', $resp);
                } else {
                    return Response::failed(@$response['response']['response']->message, $response['response']['response']);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * Recharge circle function
     *
     * @param Request $request
     * @return void
     */
    public function circle()
    {
        try {
            $res = DB::table('mst_circles')->select('id as circleId', 'name as circleName')->get();
            return Response::success('Circle fetched successfully.', $res);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * R Offer Plan function
     *
     * @param Request $request
     * @return void
     */
    public function rOfferPlan(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'rOfferPlan');
            if ($validations['status'] == true) {
                $data = $request->all();
                $data['operator'] = self::getROfferPlanFormat($data['operator']);
                $body = [
                    "phno" => @$data['phone'],
                    "Operator" => @$data['operator']
                ];

                $response = $rechageService->init($body, self::DOCUMENT_URI . '/rofferplan', 'mobileOffer', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {
                    $resp = self::respFormat($response['response']['response'], 'roffer', 1);

                    if (@$resp['status'] === 0) {
                        return Response::failed(@$resp['message']);
                    }
                    return Response::success('Plan fetched successfully.', $resp);
                } else {
                    return Response::failed(@$response['response']['response']->message, $response['response']['response']);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * dth customer info function
     *
     * @param Request $request
     * @return void
     */
    public function dthCustomerInfo(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'rOfferPlan');
            if ($validations['status'] == true) {
                $data = $request->all();

                
                $data['operator'] = str_replace("DIGITAL", "dth", $data['operator']);
                $operator = ucfirst(CommonHelper::case($data['operator'], 'l'));
                $data['operator'] = preg_replace("/\s+/", "", $operator);

                if ($data['operator'] == "Tatasky") {
                    $data['operator'] = "TataSky";
                }
                if ($data['operator'] == "Sundirecttv") {
                    $data['operator'] = "Sundirect";
                }
                if ($data['operator'] == "Videocond2h") {
                    $data['operator'] = "Videocon";
                }
               
                
                $body = [
                    "phno" => @$data['phone'],
                    "Operator" => @$data['operator']
                ];

                $response = $rechageService->init($body, self::DOCUMENT_URI . '/dthcustomerinfo', 'dthCustomerInfo', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {
             
                    if (@$response['response']['response']->data->status === 0) {
                        return Response::failed(@$response['response']['response']->data->desc);
                    }
                    $res = [
                        "Balance" => @$response['response']['response']->data->Balance,
                        "Customer Name" => @$response['response']['response']->data->customerName,
                        "Next Recharge Date" => @$response['response']['response']->data->NextRechargeDate,
                        "Status" => @$response['response']['response']->data->status,
                        "Plan Name" => @$response['response']['response']->data->planname,
                        "Monthly Recharge" =>  @$response['response']['response']->data->MonthlyRecharge,
                    ];

                    return Response::success('Customer info fetched successfully.', $res);
                } else {
                    return Response::failed(@$response['response']['response']->message);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * dth plan function
     *
     * @param Request $request
     * @return void
     */
    public function dthPlan(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'dthPlan');
            if ($validations['status'] == true) {
                $data = $request->all();
                $data['operator'] = str_replace("DIGITAL", "dth", $data['operator']);
                $data['operator'] = ucwords(CommonHelper::case($data['operator'], 'l'));

                if ( $data['operator'] == "Sun Direct Tv") {
                    $data['operator'] = "Sun Direct";
                }
                if ( $data['operator'] == "Videocon D2h") {
                    $data['operator'] = "Videocon";
                }
                if ( $data['operator'] == "Dish Tv") {
                    $data['operator'] = "Dish TV";
                }
                $body = [
                    "Operator" => @$data['operator']
                ];

                $response = $rechageService->init($body, self::DOCUMENT_URI . '/dthPlan', 'dthPlan', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {
                    $resp = self::respFormat($response['response']['response'], 'dthPlan', 1);

                    if (@$resp['status'] === 0) {
                        return Response::failed(@$resp['message']);
                    }
                    return Response::success('DTH Plan fetched successfully.', $resp);
                } else {
                    return Response::failed(@$response['response']['response']->message, @$response['response']['response']);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
           
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * dthCustomerInfoWithMobile function
     *
     * @param Request $request
     * @return void
     */
    public function dthCustomerInfoWithMobile(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'customerInfo');
            if ($validations['status'] == true) {
                $data = $request->all();
                $fstchar = substr($data['number'], 0, 1);

                if ($fstchar >= 6) {
                    $data['type'] = 'phone';
                } else {
                    $data['type'] = 'custId';
                }

                $data['operator'] = self::getCircleAndOperatorId('mst_operators', $request->operatorId);

                $data['operator'] = str_replace("DIGITAL", "dth", $data['operator']);
                $operator = ucfirst(CommonHelper::case($data['operator'], 'l'));
                $data['operator'] = preg_replace("/\s+/", "", $operator);

                if ($data['operator'] == "Tatasky") {
                    $data['operator'] = "TataSky";
                }
                if ($data['operator'] == "Sundirecttv") {
                    $data['operator'] = "Sundirect";
                }
                if ($data['operator'] == "Videocond2h") {
                    $data['operator'] = "Videocon";
                }

                $body = [
                    "phno" => @$data['number'],
                    "Operator" => @$data['operator']
                ];
 

               
                if ($data['type'] == 'phone') {
                    $response = $rechageService->init($body, self::DOCUMENT_URI . '/dthcustomerinfowithmobile', 'dthcustomerinfowithmobile', $request['auth_data']['user_id'], 'yes');
                } else {

                    $data['operator'] = str_replace("DIGITAL", "dth", $data['operator']);
                    $operator = ucfirst(CommonHelper::case($data['operator'], 'l'));
                    $data['operator'] = preg_replace("/\s+/", "", $operator);

                    if ($data['operator'] == "Tatasky") {
                        $data['operator'] = "TataSky";
                    }
                    if ($data['operator'] == "Sundirecttv") {
                        $data['operator'] = "Sundirect";
                    }
                    if ($data['operator'] == "Videocond2h") {
                        $data['operator'] = "Videocon";
                    }
                    $body = [
                        "phno" => @$data['number'],
                        "Operator" => @$data['operator']
                    ];

                    $response = $rechageService->init($body, self::DOCUMENT_URI . '/dthcustomerinfo', 'dthcustomerinfo', $request['auth_data']['user_id'], 'yes');
                }

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {

                    if (isset($response['response']['response']->data->status) && $response['response']['response']->data->status == '0') {
                        return Response::failed(@$response['response']['response']->data->desc);
                    }

                    if ($data['type'] == 'phone') {
                        $resp = [
                            'number' => @$response['response']['response']->data->Custmerid,
                            'monthlyRecharge' => @$response['response']['response']->data->MonthlyRecharge,
                            'balance' => @$response['response']['response']->data->Balance,
                            'customerName' => @$response['response']['response']->data->customerName,
                            'currentStatus' => @$response['response']['response']->data->status,
                            'nextRechargeDate' => @$response['response']['response']->data->NextRechargeDate,
                            'planName' => @$response['response']['response']->data->planname,
                        ];
                    } else {
                        $resp = [
                            "number" => $data['number'],
                            "balance" => @$response['response']['response']->data->Balance,
                            "customerName" => @$response['response']['response']->data->customerName,
                            "nextRechargeDate" => @$response['response']['response']->data->NextRechargeDate,
                            "currentStatus" => @$response['response']['response']->data->status,
                            "planName" => @$response['response']['response']->data->planname,
                            "monthlyRecharge" =>  @$response['response']['response']->data->MonthlyRecharge,
                        ];
                    }
                    return Response::success('DTH customer info fetched successfully.', $resp);
                } else {
                    return Response::failed(@$response['response']['response']->message);
                }
            } else {
                return Response::failed($validations['message']);
            }
         } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * dthPlanWithChannel function
     *
     * @param Request $request
     * @return void
     */
    public function dthPlanWithChannel(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'dthPlan');
            if ($validations['status'] == true) {
                $data = $request->all();
                $body = [
                    "operator" => @$data['operator']
                ];

                $response = $rechageService->init($body, self::DOCUMENT_URI . '/dthplanwithchannel', 'dthPlanWithChannel', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {

                    $resp = self::respFormat($response['response']['response'], 'dthPlanWithChannel', 1);
                    if (@$resp['status'] === 0) {
                        return Response::failed(@$resp['message']);
                    }
                    return Response::success('DTH Plan fetched successfully.', $resp);
                } else {
                    return Response::failed(@$response['response']['response']->message, $response['response']['response']);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * dthROffer function
     *
     * @param Request $request
     * @return void
     */
    public function dthROffer(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'dthROffer');
            if ($validations['status'] == true) {
                $data = $request->all();
                $operator = ucwords(CommonHelper::case($data['operator'], 'l'));
                $data['operator'] = preg_replace("/\s+/", "", $operator);
                if ($data['operator'] == 'AirtelDigital') {
                    $data['operator'] = "Airteldth";
                }
                $body = [
                    "custid" => @$data['custId'],
                    "operator" => @$data['operator']
                ];

                $response = $rechageService->init($body, self::DOCUMENT_URI . '/dthroffer', 'dthROffer', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {
        
                 
                    $resp = self::respFormat($response['response']['response'], 'roffer', 1);
                    if (@$resp['status'] === 0) {
                        return Response::failed(@$resp['message']);
                    }
                    return Response::success('DTH offer fetched successfully.', $resp);
                } else {
                    return Response::failed(@$response['response']['response']->message, $response['response']['response']);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

        
    /**
     * getOperatorAndCircle function
     *
     * @param Request $request
     * @return void
     */
    public function getOperatorAndCircle(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'getOperatorAndCircle');
            if ($validations['status'] == true) {
                $data = $request->all();
                $body = [
                    "phno" => @$data['number'],
                ];

                $response = $rechageService->init($body, self::DOCUMENT_URI . '/operatorcheck', 'dthROffer', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {

                    if (@$response['response']['response']->data->status == '0') {
                        return Response::failed('No record found.');
                    }
                    return Response::success('Operator fetched successfully.', $response['response']['response']->data);
                } else {
                    return Response::failed(@$response['response']['response']->message, $response['response']['response']);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * dthHeavyRefresh function
     *
     * @param Request $request
     * @return void
     */
    public function dthHeavyRefresh(Request $request, RechargeService $rechageService)
    {
        try {
            $validations = Validations::init($request, 'rOfferPlan');
            if ($validations['status'] == true) {
                $data = $request->all();
                $data['operator'] = self::getCircleAndOperatorId('mst_operators', $request->operatorId);

                $operator = ucwords(CommonHelper::case($data['operator'], 'l'));
                $data['operator'] = preg_replace("/\s+/", "", $operator);

                if ($data['operator'] == 'AirtelDigital') {
                    $data['operator'] = "Airteldth";
                }

                $body = [
                    "phno" => @$data['number'],
                    "operator" => @$data['operator']
                ];

                $response = $rechageService->init($body, self::DOCUMENT_URI . '/dthheavyrefresh', 'dthHeavyRefresh', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {
                    if (@$response['response']['response']->data->status == 0) {
                        return Response::failed(@$response['response']['response']->data->desc);
                    }

                    $resp = [
                        'description' => @$response['response']['response']->data->desc,
                        'customerName' => @$response['response']['response']->data->customerName,
                    ];
                    return Response::success('Heavy refresh successfully.', $resp);
                } else {
                    return Response::failed('Record not fetched.', $response['response']['response']);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * getOperator function
     *
     * @param Request $request
     * @return void
     */
    public function getOperator(Request $request)
    {
        try {
            $op = DB::table('mst_operators')->select('id as operatorId',
            DB::raw('IF(logo IS NULL OR logo = "", "'.asset(DEFAULT_BANK_IMAGE).'" , CONCAT("'.asset('images/mobile_recharge_logo/').'", "/", logo)) AS logo'),  'name as operatorName');
            if (!empty($request->type)) {
                $op = $op->where('type', $request->type);
            }

            $op = $op->get();
            return Response::success('Operator fetched successfully.', @$op);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Recharge function
     *
     * @param Request $request
     * @return void
     */
    public function recharge(Request $request, RechargeService $rechageService)
    {
        try {
            $response = [];
            $validations = Validations::init($request, 'recharge');
            if ($validations['status'] == true) {

                if ($request->amount < 0 || $request->amount == "0") {
                    return Response::failed('The amount should be grater then 0.');
                }

                $orderRefId =  $request->clientRefId;
                if (DB::table('recharges')->where('order_ref_id', $orderRefId)->count()) {
                    return Response::failed('The client ref id already exists.');
                }


               /* if ($request->merchantCode != 'null') {
                    $request->merchantCode =  isset($request->merchantCode) ? $request->merchantCode : "";
                } else {
                    $request->merchantCode = "";
                }*/


                 $data = $request->all();
                 $RequestData = [
                    "userid"   => $this->user,
                    "password" => $this->pass,
                    "mobile"   => @$request->phone,
                    "amount"   => @$request->amount,
                    "opt"      => @$request->operatorId,
                    "agentid"  => @$request->clientRefId,
                    "fmt"      => 'json'
                ];

                $parameters= [];
                $url = $this->baseUrl. 'apitransaction?userid='.$this->user.'&password='.$this->pass.'&mobile='.$request->phone.'&amount='.$request->amount.'&opt='.$request->operatorId.'&agentid='.$request->clientRefId.'&fmt=only1';

                $taxData = self::getFeeAndTaxs($request['auth_data']['user_id'], 'recharge');

                $orderInserted = Recharge::create($request['auth_data']['user_id'], $RequestData,  $taxData);
                if ($orderInserted['status']) {
         
                    $ocr = Recharge::moveOrderToProcessingByOrderId($request['auth_data']['user_id'], $orderRefId, $taxData['fee'], $taxData['tax'], 'recharge_debited_fund');
                    
                    if ($ocr['status']) {
                        if ($request->phone == "1111111111") {
                            $response['response']['response'] = (object) self::rechargeFormat('failed', $request->amount, '1111111111', $request->clientRefId);
                        } else if ($request->phone == "9999999999") {
                            $response['response']['response'] = (object)self::rechargeFormat('success', $request->amount, '9999999999', $request->clientRefId);
                        } else {
                         
                            $response = $rechageService->init($parameters, $url, 'recharge', @$request['auth_data']['user_id'], 'yes', 'recharge', 'GET');
                        }
                        
                        $decodedResponse = json_decode($response['response']['response']);
                      
                      
                        if (isset($decodedResponse->Status) &&  $decodedResponse->Status=='SUCCESS') {
                            $data = [
                                'stan_no' => @$decodedResponse->AgentID,
                                'status' => 'processed',
                                'bank_reference' =>  @$decodedResponse->RBID,
                            ];

                            Recharge::updateRecord( ['order_ref_id' => $orderRefId], $data);
                            $resp =  self::rechargeResponseFormat($decodedResponse,  $request->merchantCode);
                            return Response::success('Recharge successfully.', @$resp);
                        }


                        else if (isset($decodedResponse->Status) &&  $decodedResponse->Status=='PENDING') {
                            $resp =  self::rechargeResponseFormat($decodedResponse,  $request->merchantCode);
                            return Response::pending($decodedResponse->message, @$resp);
                        }
                        else {
                            $data = [
                                'stan_no' => @$decodedResponse->AgentID,
                                'status'  => 'failed',
                                'bank_reference' =>  @$decodedResponse->RBID,
                            ];

                            Recharge::updateRecord( ['order_ref_id' => $request->clientRefId], $data);

                            $failedMessage = isset($decodedResponse->MSG) ? $decodedResponse->MSG : 'Something went wrong, please try after some time.';
                            
                            if ('Insufficient Wallet Balance' == $failedMessage) {
                                $failedMessage = "E40321:Something went wrong, please try after some time.";
                            } 

                            Recharge::fundRefunded($request['auth_data']['user_id'], $request->clientRefId, @$failedMessage, 'recharge_amount_refunded', @$decodedResponse->statuscode);
                            $resp =  self::rechargeResponseFormat($decodedResponse,  $request->merchantCode);

                        
                            return Response::failed(@$failedMessage, $resp);
                        }

                    } else {
                        Recharge::fundRefunded($request['auth_data']['user_id'], $request->clientRefId, @$ocr['message'], 'recharge_amount_refunded', '');
                        return Response::failed($ocr['message'], []);
                    }
                } else {
                    return Response::failed($orderInserted['message'], []);
                }
            } else {
                return Response::failed($validations['message']);
            }
        } catch (Exception $e) {
            //echo 'tttt'.$e->getMessage();exit;
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG,$e);
        }
    }

    /**
     * Recharge Status function
     *
     * @param Request $request
     * @return void
     */
    public function rechargeStatus(Request $request, $refId = '', RechargeService $rechageService)
    {
     
        try {

            if (!empty($refId)) {
                $parameters = [];
             
                
                $url = $this->baseUrl. 'status_check?userid='.$this->user.'&password='.$this->pass.'&transid='.$refId.'&fmt=only1';

                $response = $rechageService->init($parameters, $url, 'recharge', @$request['auth_data']['user_id'], 'yes', 'recharge', 'GET');
                dd($response); exit;
                $recharge = Recharge::select('merchant_code')->where('order_ref_id', $refId)->first();
                
                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {
                    $data = [
                        'stan_no' => @$response['response']['response']->txnid,
                        'status' => 'processed',
                        'bank_reference' =>  @$response['response']['response']->venderid,
                    ];


                    Recharge::updateRecord( ['order_ref_id' => $refId], $data);
                    $resp =  self::rechargeResponseFormat($response['response']['response'], @$recharge->merchant_code);
                    return Response::success('Record fetched successfully.', @$resp);
                }

                else if (isset($response['response']['response']->statuscode) && in_array($response['response']['response']->statuscode, ['002', '999'])) {
                    $resp =  self::rechargeResponseFormat($response['response']['response'], @$recharge->merchant_code);
                    return Response::pending($response['response']['response']->message, @$resp);
                }

                else {

                    $data = [
                        'stan_no' => @$response['response']['response']->txnid,
                        'status_code' => @$response['response']['response']->statuscode,
                        'bank_reference' =>  @$response['response']['response']->venderid,
                    ];

                    Recharge::updateRecord( ['order_ref_id' => $refId], $data);
                    $failedMessage = "No record found";
                    if (is_string(@$response['response']['response']->message)) {
                        $failedMessage = $response['response']['response']->message;
                    }
                    if (isset($response['response']['response']->remarks)) {
                        $failedMessage = $response['response']['response']->remarks;
                    }

                    if ('Insufficient Wallet Balance' == $failedMessage) {
                        $failedMessage = "E40321:Something went wrong, please try after some time.";
                    }
                    Recharge::fundRefunded($request['auth_data']['user_id'], $refId, $failedMessage, 'recharge_amount_refunded', @$response['response']['response']->statuscode);
                    $resp =  self::rechargeResponseFormat($response['response']['response'], @$recharge->merchant_code);
                    return Response::failed($failedMessage, $resp);
                }

            } else {
                return Response::failed('Client reference id is required.');
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    function respFormat($response, $method, $provider)
    {
        $respArray = [];

        if ($provider == 1 && !empty($response->data)) {
            if (str_contains(json_encode($response->data), 'Plan Not Available')) {
                $respArray = [
                    "desc" => "Plan Not Available",
                    "message" => "Plan Not Available",
                    "status" => 0
                ];
                return $respArray;
            }
            if (str_contains(json_encode($response->data), 'Customer Not Found')) {
                $respArray = [
                    "desc" => "Customer Not Found",
                    "message" => "Customer Not Found",
                    "status" => 0
                ];
                return $respArray;
            }
            $array = json_decode(json_encode($response->data), true);
                if (count($array)) {
                    switch($method) {
                        case  'plan' :
                            $i = 0;
                            foreach ($array as $key => &$val) {
                                switch($key) {
                                    case  'FULLTT' :
                                        $respArray[$i]['category'] = 'fullTalkTime';
                                        $respArray[$i]['title'] = 'Full Talk Time';
                                        $j = 0;
                                        $content = [];
                                        foreach ($val as $k => &$v) {
                                            $content[$j] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs'],
                                                'description' => @$v['desc'],
                                                'validity' => @$v['validity'],
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                            $j ++;
                                        }
                                        $respArray[$i]['content'] = $content;
                                    break;
        
                                    case  'TOPUP' :
        
                                        $respArray[$i]['category'] = 'topup';
                                        $respArray[$i]['title'] = 'Topup';
        
                                        $j = 0;
                                        $content = [];
                                        foreach ($val as $k => &$v) {
                                            $content[$j] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs'],
                                                'description' => @$v['desc'],
                                                'validity' => @$v['validity'],
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                            $j ++;
                                        }
                                        $respArray[$i]['content'] = $content;
                                    break;
        
                                    case  '3G/4G' :
        
                                    $respArray[$i]['category'] = '3G/4G';
                                    $respArray[$i]['title'] = '3G/4G';
        
                                    $j = 0;
                                    $content = [];
                                    foreach ($val as $k => &$v) {
                                        $content[$j] = [
                                            'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                            'rupee' => @$v['rs'],
                                            'description' => @$v['desc'],
                                            'validity' => @$v['validity'],
                                            'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                        ];
                                        $j ++;
                                    }
                                    $respArray[$i]['content'] = $content;
                                    break;
        
                                    case  '2G' :
        
                                    $respArray[$i]['category'] = '2G';
                                    $respArray[$i]['title'] = '2G';
        
                                    $j = 0;
                                    $content = [];
                                    foreach ($val as $k => &$v) {
                                        $content[$j] = [
                                            'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                            'rupee' => @$v['rs'],
                                            'description' => @$v['desc'],
                                            'validity' => @$v['validity'],
                                            'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                        ];
                                        $j ++;
                                    }
                                    $respArray[$i]['content'] = $content;
                                    break;
        
                                    case  'RATE CUTTER' :
                                    case  'RATECUTTER' :
                                    $respArray[$i]['category'] = 'rateCutter';
                                    $respArray[$i]['title'] = 'Rate Cutter';
        
                                    $j = 0;
                                    $content = [];
                                    foreach ($val as $k => &$v) {
                                        $content[$j] = [
                                            'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                            'rupee' => @$v['rs'],
                                            'description' => @$v['desc'],
                                            'validity' => @$v['validity'],
                                            'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                        ];
                                        $j ++;
                                    }
                                    $respArray[$i]['content'] = $content;
                                    break;

                                    
                                    case  'Romaing' :
                                    $respArray[$i]['category'] = 'romaing';
                                    $respArray[$i]['title'] = 'Romaing';
         
                                    $j = 0;
                                    $content = [];
                                    foreach ($val as $k => &$v) {
                                        $content[$j] = [
                                            'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                            'rupee' => @$v['rs'],
                                            'description' => @$v['desc'],
                                            'validity' => @$v['validity'],
                                            'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                        ];
                                        $j ++;
                                    }
                                    $respArray[$i]['content'] = $content;
                                    break;
        
                                    default;
                                }
                                $i ++;
                            }
        
                        break;
        
                        case  'offer' :
                            $i = 0;
                            $respArray['category'] = 'offer';
                            $respArray['title'] = 'Offer';
                            foreach ($array as $key => &$val) {
                                $content[$i] = [
                                    'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                    'rupee' => @$val['rs'],
                                    'description' => @$val['desc'],
                                    'validity' => !empty($v['validity']) ? $v['validity'] : "",
                                    'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                ];
                            $i++;
        
                            }
                            $respArray['content'] = $content;
                        break;

                        case  'roffer' :
                        $i = 0;
                        $respArray[0]['category'] = 'offer';
                        $respArray[0]['title'] = 'Offer';
                        foreach ($array as $key => &$val) {
                            $content[$i] = [
                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                'rupee' => @$val['rs'],
                                'description' => @$val['desc'],
                                'validity' => !empty($v['validity']) ? $v['validity'] : "",
                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                            ];
                        $i++;
    
                        }
                        $respArray[0]['content'] = $content;
                    break;
    
                        case  'dthPlan' :
        
                        $i = 0;
                        foreach ($array as $key => &$val) {
     
                            switch($key) {
    
                                case  'Plan' :
    
                                    $respArray[$i]['category'] = 'plan';
                                    $respArray[$i]['title'] = 'Plan';
    
                                    $j = 0;
                                    $content = [];
                                    foreach ($val as $k => &$v) {
    
                                        if (is_array($v['rs'])) {
                                            foreach ($v['rs'] as $x => &$y) {
                                                if ($y > 0 && ($y != '0' || $y != '0.00')) {
                                                    $content[] = [
                                                        'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                        'rupee' => @$y,
                                                        'description' => @$v['desc'],
                                                        'validity' => $x,
                                                        'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                                    ];
                                                }
                                            }
                        
                                        } else {
                                            $content[] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs'],
                                                'description' => @$v['desc'],
                                                'validity' => '',
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                        }
                                    }
                                    $respArray[$i]['content'] = $content;
                                break;
        
                                case  'Add-On Pack' :
        
                                    $respArray[$i]['category'] = 'addOnPack';
                                    $respArray[$i]['title'] = 'Add-On Pack';
        
                                    $j = 0;
                                    $content = [];
                                       foreach ($val as $k => &$v) {
    
                                        if (is_array($v['rs'])) {
                                            foreach ($v['rs'] as $x => &$y) {
                                                if ($y > 0 && ($y != '0' || $y != '0.00')) {
                                                    $content[] = [
                                                        'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                        'rupee' => @$y,
                                                        'description' => @$v['desc'],
                                                        'validity' => $x,
                                                        'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                                    ];
                                                }
                                            }
                                        } else {
                                            $content[] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs'],
                                                'description' => @$v['desc'],
                                                'validity' => '',
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                        }

                                    }
                                    $respArray[$i]['content'] = $content;
                                break;
        
                                default;
                            }
                            $i ++;
                        }
        
                        break;
        
                        case  'dthPlanWithChannel' :
        
                        $i = 0;
                        foreach ($array as $key => &$val) {
        
                            switch($key) {
        
                                case  'Plan' :
        
                                    $respArray[$i]['category'] = 'plan';
                                    $respArray[$i]['title'] = 'Plan';
        
                                    $j = 0;
                                    $content = [];
                                    foreach ($val as $k => &$v) {
                                        if (@$v['rs']['1 MONTHS']){
                                            $content[$j] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs']['1 MONTHS'],
                                                'description' => @$v['desc'],
                                                'validity' =>  '1 Months',
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                        }
                                        if (@$v['rs']['2 MONTHS']){
                                            $content[$j] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs']['2 MONTHS'],
                                                'description' => @$v['desc'],
                                                'validity' =>  '2 Months',
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                        }
                                        if (@$v['rs']['3 MONTHS']){
                                            $content[$j] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs']['3 MONTHS'],
                                                'description' => @$v['desc'],
                                                'validity' => '3 Months',
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                        }
                                        $j ++;
                                    }
                                    $respArray[$i]['content'] = $content;
                                break;
        
                                case  'Add-On Pack' :
        
                                    $respArray[$i]['category'] = 'addOnPack';
                                    $respArray[$i]['title'] = 'Add-On Pack';
        
                                    $j = 0;
                                    $content = [];
                                    foreach ($val as $k => &$v) {
                                        if (@$v['rs']['1 MONTHS']){
                                            $content[$j] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs']['1 MONTHS'],
                                                'description' => @$v['desc'],
                                                'validity' =>  '1 Months',
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                        }
                                        if (@$v['rs']['2 MONTHS']){
                                            $content[$j] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs']['2 MONTHS'],
                                                'description' => @$v['desc'],
                                                'validity' =>  '2 Months',
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                        }
                                        if (@$v['rs']['3 MONTHS']){
                                            $content[$j] = [
                                                'planName' => !empty($v['plan_name']) ? $v['plan_name'] : "",
                                                'rupee' => @$v['rs']['3 MONTHS'],
                                                'description' => @$v['desc'],
                                                'validity' =>  '3 Months',
                                                'lastUpdate' => !empty($v['last_update']) ? $v['last_update'] : "",
                                            ];
                                        }
                                        $j ++;
                                    }
                                    $respArray[$i]['content'] = $content;
                                break;
        
                                default;
                            }
                            $i ++;
                        }
        
                        break;
        
        
                        default;
        
                    }
                }

        } else {
            $respArray = [
                "desc" => "Plan Not Available",
                "message" => "Plan Not Available",
                "status" => 0
            ];
        }
        return $respArray;
    }

    public static function getFeeAndTaxs($userId, $slug)
    {
        $resp['fee'] = 0;
        $resp['tax'] = 0;
        $resp['margin'] = "";
        $resp['product_id'] = "";
        @$resp['message'] = "init";
        try {
            //code...
                $getProductId = CommonHelper::getProductId($slug, 'recharge');
                $productId = isset($getProductId->product_id) ? $getProductId->product_id : "";
                $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, 0, $userId);
                $resp['fee'] = isset($getFeesAndTaxes['fee']) ? $getFeesAndTaxes['fee'] : 0;
                $resp['tax'] = isset($getFeesAndTaxes['tax']) ? $getFeesAndTaxes['tax'] : 0;
                $resp['margin'] = isset($getFeesAndTaxes['margin']) ? $getFeesAndTaxes['margin'] : "";
                $resp['product_id'] = isset($productId) ? $productId : "";
                @$resp['message'] = "success";
        } catch (\Exception  $e) {
            $resp['fee'] = 0;
            $resp['tax'] = 0;
            $resp['margin'] = "";
            @$resp['message'] = "no record found. ".SOMETHING_WENT_WRONG;
        }

        return $resp;
    }

    public static function sendCallabck($data, $userId)
    {
       //send callback
       $getWebhooks = Webhook::where('user_id', $userId)->first();
       if ($getWebhooks) {
           $url = $getWebhooks['webhook_url'];
           $secret = $getWebhooks['secret'];
           if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
               $headers = [$getWebhooks['header_key'] => $getWebhooks['header_value']];
                WebhookHelper::RechargeTransaction($data, $url, $secret, $headers);
           } else {

                WebhookHelper::RechargeTransaction($data, $url, $secret);
           }
       }

    }

    public static function getROfferPlanFormat($operatorName)
    {

        $operatorName = CommonHelper::case($operatorName, 'l');
        if ($operatorName == 'airtel') {
            return "Airtel";
        } else if($operatorName == 'tata docomo') {
            return "Tata Docomo";
        }  else if($operatorName == 'tata cdma') {
            return "Tata CDMA";
        } else if($operatorName == 'bsnl topup' || $operatorName == 'bsnl validity') {
            return "BSNL";
        }else if($operatorName == 'idea') {
            return "Idea";
        } else if($operatorName == 'jio') {
            return "Jio";
        }else if($operatorName == 'vodafone') {
            return "Vodafone";
        }

        return  ucwords($operatorName);
    }

    public function getMobilePlanFormat($operatorName)
    {

        $operatorName = CommonHelper::case($operatorName, 'l');
        if ($operatorName == 'airtel') {
            return "Airtel";
        }  else if($operatorName == 'tata docomo') {
            return "Tata Docomo";
        }  else if($operatorName == 'tata cdma') {
            return "Tata Indicom";
        }  else if($operatorName == 'bsnl topup') {
            return "MTS";
        } else if($operatorName == 'bsnl topup' || $operatorName == 'bsnl validity') {
            return "BSNL";
        }else if($operatorName == 'idea') {
            return "Idea";
        } else if($operatorName == 'jio') {
            return "Jio";
        }else if($operatorName == 'vodafone') {
            return "Vodafone";
        }

        return  ucwords($operatorName);
    }

    public static function rechargeFormat($type, $amount, $mobile, $clinetRefId)
    {
        if ($type == 'success') {
          return  [
                "clientRefId"=>"$clinetRefId",
                "amount"=>"$amount",
                "txnId"=>"XTRECF".CommonHelper::getRandomString('', false, 10),
                "venderId"=>"3946125",
                "remarks"=>"Success",
                "customerNumber"=> $mobile,
            ];
        } else {
            return  [
                "clientRefId"=>"$clinetRefId",
                "amount"=>"$amount",
                "txnId"=>"XTRECF".CommonHelper::getRandomString('', false, 10),
                "venderId"=>"",
                "remarks"=>"Time based authentication failed.",
                "customerNumber"=> $mobile,
            ];
        }
    }

    public static function rechargeResponseFormat($response, $merchantCode='')
    {
        
     
        if (!empty(@$response->Status)) {
           
            return  [
                "clientRefId"=> @$response->AgentID,
                "status"=> @$response->Status,
                "amount"=> @$response->Amount,
                "txnId"=> @$response->RBID,
                "customerNumber"=> @$response->Mobile,
                "remarks"=> @$response->MSG,
                "merchantCode"=> $merchantCode,
            ];
        } else {
            return  [];
        }
    }


    public static function getCircle()
    {

          return  [
                [
                    "circleId" => "1",
                    "circleName"=> "Andhra Pradesh Telangana"
                ],[
                    "circleId" => "2",
                    "circleName"=> "Assam"
                ],[
                    "circleId" => "3",
                    "circleName"=> "Bihar Jharkhand"
                ],[
                    "circleId" => "4",
                    "circleName"=> "Chennai"
                ],[
                    "circleId" => "5",
                    "circleName"=> "Delhi NCR"
                ],[
                    "circleId" => "6",
                    "circleName"=> "Gujarat"
                ],[
                    "circleId" => "7",
                    "circleName"=> "Haryana"
                ],[
                    "circleId" => "8",
                    "circleName"=> "Himachal Pradesh"
                ],[
                    "circleId" => "9",
                    "circleName"=> "Jammu Kashmir"
                ],[
                    "circleId" => "10",
                    "circleName"=> "Karnataka"
                ],[
                    "circleId" => "11",
                    "circleName"=> "Kerala"
                ],[
                    "circleId" => "12",
                    "circleName"=> "Kolkata"
                ],[
                    "circleId" => "13",
                    "circleName"=> "Madhya Pradesh Chhattisgarh"
                ],[
                    "circleId" => "14",
                    "circleName"=> "Maharashtra Goa"
                ],[
                    "circleId" => "15",
                    "circleName"=> "Mumbai"
                ],[
                    "circleId" => "16",
                    "circleName"=> "North East"
                ],[
                    "circleId" => "17",
                    "circleName"=> "Orissa"
                ],[
                    "circleId" => "18",
                    "circleName"=> "Punjab"
                ],[
                    "circleId" => "19",
                    "circleName"=> "Rajasthan"
                ],[
                    "circleId" => "20",
                    "circleName"=> "Tamil Nadu"
                ],[
                    "circleId" => "21",
                    "circleName"=> "UP East"
                ],[
                    "circleId" => "22",
                    "circleName"=> "UP West"
                ],[
                    "circleId" => "23",
                    "circleName"=> "West Bengal"
                ]
            ];
    }

    public static function validateRequest($req)
    {

        $resp['status'] = false;
        $resp['message'] = "Invalid type.";
        $resp['data'] = [];
        $circle = "";
        $operator = "";

        if (!empty($req->circleId)) {
            $circle = self::getCircleAndOperatorId('mst_circles', $req->circleId);
        }

        if (!empty($req->operatorId)) {
            $operator = self::getCircleAndOperatorId('mst_operators', $req->operatorId);
        }


        if ($req->type == 'mobilePlans') {
            if (empty($req->operatorId) || empty($operator)) {
                if (empty($operator)) {
                    $resp['message'] = 'Operator id is invalid.';
                } else {
                    $resp['message'] = 'Operator id is required.';
                }
                return $resp;
            }

            if (empty($req->circleId) || empty($circle)) {
                if (empty($circle)) {
                    $resp['message'] = 'Circle id is invalid.';
                } else {
                    $resp['message'] = 'Circle id is required.';
                }
                return $resp;
            }

            $resp['data'] = [
                "cricle" => $circle,
                "Operator" => $operator
            ];
            $resp['uri'] = 'simpleplan';
            $resp['responseMethod'] = 'plan';
            $resp['status'] = true;

            return $resp;

        } else  if ($req->type == 'mobileOffers') {
            if (empty($req->operatorId) || empty($operator)) {
                if (empty($operator)) {
                    $resp['message'] = 'Operator id is invalid.';
                } else {
                    $resp['message'] = 'Operator id is required.';
                }
                return $resp;
            }

            if (empty($req->number)) {
                $resp['message'] = 'Phone number is required.';
                return $resp;
            }
            $op = self::getROfferPlanFormat($operator);

            $resp['data'] = [
                "phno" => $req->number,
                "Operator" => $op
            ];
            $resp['uri'] = 'rofferplan';

            $resp['responseMethod'] = 'roffer';
            $resp['status'] = true;


            return $resp;
        } else  if ($req->type == 'dthPlans') {
            if (empty($req->operatorId) || empty($operator)) {
                if (empty($operator)) {
                    $resp['message'] = 'Operator id is invalid.';
                } else {
                    $resp['message'] = 'Operator id is required.';
                }
                return $resp;
            }

            $op = str_replace("DIGITAL", "dth", $operator);
            $op = ucwords(CommonHelper::case($operator, 'l'));

            if ( $op == "Sun Direct Tv") {
                $op = "Sun Direct";
            }
            if ( $op == "Videocon D2h") {
                $op = "Videocon";
            }
            if ( $op == "Dish Tv") {
                $op = "Dish TV";
            }

            if ( $op == "Airtel Digital") {
                $op = "Airtel dth";
            }

            $resp['data'] = [
                "Operator" => $op
            ];
            $resp['uri'] = 'dthPlan';

            $resp['responseMethod'] = 'dthPlan';
            $resp['status'] = true;


            return $resp;
        } else  if ($req->type == 'dthOffers') {
            if (empty($req->operatorId) || empty($operator)) {
                if (empty($operator)) {
                    $resp['message'] = 'Operator id is invalid.';
                } else {
                    $resp['message'] = 'Operator id is required.';
                }
                return $resp;
            }


            if (empty($req->number)) {
                    $resp['message'] = 'Operator id is required.';
                return $resp;
            }
            $operator = ucwords(CommonHelper::case($operator, 'l'));
            $op = preg_replace("/\s+/", "", $operator);
            if ($op == 'AirtelDigital') {
                $op = "Airteldth";
            }
            
            $resp['data'] = [
                "custid" => $req->number,
                "operator" => $op
            ];
            $resp['uri'] = 'dthroffer';

            $resp['responseMethod'] = 'roffer';
            $resp['status'] = true;


            return $resp;
        } else  if ($req->type == 'dthPlanWithChannels') {
            if (empty($req->operatorId) || empty($operator)) {
                if (empty($operator)) {
                    $resp['message'] = 'Operator id is invalid.';
                } else {
                    $resp['message'] = 'Operator id is required.';
                }
                return $resp;
            }

            $resp['data'] = [
                "operator" => $operator
            ];
            $resp['uri'] = 'dthplanwithchannel';

            $resp['responseMethod'] = 'dthPlanWithChannel';
            $resp['status'] = true;


            return $resp;
        }

        return $resp;
    }

    public static function getCircleAndOperatorId($table, $id) {
       return @DB::table($table)
                ->select( 'name')
                ->where('id', $id)
                ->first()
                ->name;
    }

    public function fetchrecharge(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'ci_id' => "required",
                'op_id' => "required",
            ]
        );

        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing('Some params are missing.', $message);
        }

        try {
            $fetchdata = $this->getRechargeData($request);
            // dd($fetchdata);
            if ($fetchdata) {
                $responseData = [
                    'success' => true,
                    'data' => [
                        'plans' => $fetchdata,
                    ]
                ];
            } else {
                $responseData = [
                    'success' => false,
                    'message' => [
                        'code' => '103',
                        'text' => 'Recharge plans not available for the specified operator, circle.'
                    ]
                ];
            }

            return response()->json($responseData);
            
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    private function getRechargeData($request)
    {
        return [
            [
                'id' => '1028995',
                'operatorId' => $request->op_id,
                // 'circleId' => 19,
                'planType' => 17,
                'planCode' => 'airtel-Rajasthan-local-plans-Rs-19.0',
                'amount' => 19.0,
                'validity' => '2 days',
                'planName' => 'Voice + 4G Data',
                'planDescription' => 'Enjoy TRULY unlimited Local, STD & Roaming calls on any network, 200 MB data for 2 days ',
                'validityInDays' => 2,
            ]
        ];
    }


    public function viewBill(Request $request)
    {
        $header = [
            'content-type: application/json',
            'X-MClient: 14' 
        ];
        // dd($header);
        // $contentType = $request->header('Content-Type');
        // $xMClient = $request->header('X-MClient'); 
        // if ($contentType !== 'application/json' || $xMClient !== '14') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid headers'
        //     ], 400);
        // }
        $validation = new Validations($request);
        $validator = $validation->billview();
        $validator->after(function ($validator) use ($request) {
            $User = User::where('id', $request->uid)->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'User Account disabled');
            } else {
                $isAvailable = DB::table('user_services')
                    ->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])
                    ->select('is_active', 'transaction_amount')->first();
                    // dd($isAvailable);
                if (isset($isAvailable) && $isAvailable->is_active == '1') {
                    $totalAmount =  $request->amt;
                    // dd($totalAmount);
                    if ($isAvailable->transaction_amount <= $totalAmount) {
                        $validator->errors()->add('amount', 'Insufficient funds.');
                    }
                } else {
                    $validator->errors()->add('userId', 'Your payout service is disabled.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        }
        try {  
            $user = User::where('id', $request->uid)->first();
           
            $TxnId = CommonHelper::getRandomString('TXN', 16);
            $BankTxnid = CommonHelper::getRandomString('CUS');
            $OriginalTxnId = CommonHelper::getRandomString('REBILL');
        
            $data = [
                'cir' => $request->cir,
                'cn' => $request->cn,
                'op' => $request->op,
                // 'pswd' => $request->pswd,
                'adParams' => $request->adParams,
                'user_id' => $user->id,
                'service_id' => 'srv_1626077505',
                // 'customer_ref_id' => $request->reqid,
                'txn_id'  => $TxnId,
                'integration_id'  => 'int_123456789',
                'status' => "Success",
                'bank_txn_id' => $BankTxnid,
                'original_order_id' => $OriginalTxnId,
                'response' => "",
                'description' =>"SUCCESS",
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at'=> now()->format('Y-m-d H:i:s'),

            ];
// dd($data);
            $rechargeData = RetaillerBill::create($data);
// dd($data);
            if ($rechargeData) {
                // dd($rechargeData);
                $response = [
                    'success' => true,
                    'data' => [
                        [
                            'billAmount' => '75945.00',
                            'billnetamount' => '75945.00',
                            'acceptPayment' => true,
                            'acceptPartPay' => true,
                            'cellNumber' => $rechargeData['cn']
                        ]
                    ]
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to retrieve bill details'
                ];
            }
            return response()->json($response);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    public function RechargePay(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->rechargeval();
        $validator->after(function ($validator) use ($request) {
            $User = User::where('id', $request->uid)->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'User Account disabled');
            } else {
                $isAvailable = DB::table('user_services')
                    ->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])
                    ->select('is_active', 'transaction_amount')->first();
                    // dd($isAvailable);
                if (isset($isAvailable) && $isAvailable->is_active == '1') {
                    $totalAmount =  $request->amt;
                    // dd($totalAmount);
                    if ($isAvailable->transaction_amount <= $totalAmount) {
                        $validator->errors()->add('amount', 'Insufficient funds.');
                    }
                } else {
                    $validator->errors()->add('userId', 'Your payout service is disabled.');
                }
                $Order = RechargeData::where('customer_ref_id', $request->reqid)->count();
                if ($Order) {
                    $validator->errors()->add('clientRefId', 'Client Ref Id all ready exists.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        }
        else { 
            $isAvailable = DB::table('user_services')->where(['user_id' => $request['uid'], 'service_id' => 'srv_1626077505'])->select('is_active', 'transaction_amount','service_id', 'user_id','service_account_number')->first();
            $transAmount = $isAvailable->transaction_amount;
            $service = $isAvailable->service_id;

            $slug = "data_recharge";
            $amount = $request->amt;
            $product = TransactionHelper::getProductConfig($slug, $service);
            if ($product['status']) {
                if ($product['data']['min_order_value'] <= $amount && $product['data']['max_order_value'] >= $amount) {
                    $product_id = $product['data']['product_id'];
                    $feesAndTaxes = TransactionHelper::getFeesAndTaxes($product_id, $amount,$isAvailable->user_id);
                    
                    $commission = $feesAndTaxes['fee'];
                    $gst = $feesAndTaxes['tax'];
                    $rechargeAmount = $feesAndTaxes['total_amount'];

                    $TxnId = CommonHelper::getRandomString('TXN', 16);
                    $BankTxnid = CommonHelper::getRandomString('CUS');
                    $OriginalTxnId = CommonHelper::getRandomString('RECH');
                    $data = [
                        'amount'       => $request->amt,
                        'cir'          => $request->cir,
                        'cn'           => $request->cn,
                        'op'           => $request->op,
                        'user_id'      => $isAvailable->user_id,
                        'fee'          => $commission,
                        'tax'          => $gst,
                        'total_amount' => $rechargeAmount,
                        'service_id'   => $service,
                        'customer_ref_id'  => $request->reqid,
                        'txn_id'       => $TxnId,
                        'integration_id'  => 'int_123456789',
                        'status'       => '',
                        'bank_txn_id'  => $BankTxnid,
                        'original_order_id' => $OriginalTxnId,
                        'response'     => '',
                        'description'  =>'SUCCESS',
                        'created_at'   => now()->format('Y-m-d H:i:s'),
                        'updated_at'   => now()->format('Y-m-d H:i:s'),

                    ];
                    
                    $rechargeData = RechargeData::create($data);

                    $update_service = DB::table('user_services')
                    ->where('user_id', $data['user_id'])
                    ->where('service_id', $service)
                    ->where('is_active', "1")
                    ->update([
                        'transaction_amount' => DB::raw('transaction_amount - ' . $rechargeAmount),
                        'updated_at' => now(),
                    ]);
                        
                        $orderRefId = $data['customer_ref_id'];
                        $userId = $data['user_id'];
                        $integrationId = $data['integration_id'];

                        $userOpeningBalance = $isAvailable->transaction_amount;
                        $userClosingBalance =  $userOpeningBalance - $rechargeAmount;

                        $dthData = RechargeData::where('user_id', $userId)->where('created_at', $data['created_at'])->first();
                        if ($dthData) {
                            $transactionData = [
                                'user_id'           => $userId,
                                'service_id'        => $service,
                                'txn_id'            => $TxnId,
                                'txn_ref_id'        => $OriginalTxnId,
                                'account_number'    => $isAvailable->service_account_number,
                                'tr_total_amount'   => $rechargeAmount,
                                'tr_amount'         => $amount,
                                'tr_fee'            => $commission,
                                'order_id'          => $OriginalTxnId,
                                'tr_commission'     => '',
                                'tr_tds'            => '',
                                'tr_tax'            => $gst,
                                'tr_date'           => date('Y-m-d H:i:s'),
                                'tr_type'           => '',
                                'tr_identifiers'    => '',
                                'opening_balance'   => $userOpeningBalance,
                                'closing_balance'   => $userClosingBalance,
                                'tr_narration'      => $rechargeAmount . ' recharge debited',
                                'tr_reference'      => '',
                                'remarks'           => 'recharge deduct',
                                'udf1'              => '',
                                'udf2'              => '',
                                'udf3'              => '',
                                'udf4'              => '',
                                'fee_rate'          => '',
                                'created_at'        => now()->format('Y-m-d H:i:s'),
                                'updated_at'        => now()->format('Y-m-d H:i:s'),
                            ];
                            
                            $transaction = Transaction::create($transactionData);

                            $transactionSuccess = false;
                            $checksum = '9074608d58d3647decb2a93bd36eb0cd3e1c68aa2d9c8931a6716585171a07d5';
                            $headers = [
                                'checkSum' => $checksum,
                            ];

                            $randomNumber = rand(0, 1);

                            if ($randomNumber == 1) {
                                $response = '<recharge> 
                                    <status>SUCCESS</status> 
                                    <txId>' . $BankTxnid . '</txId> 
                                    <balance>1000</balance> 
                                    <discountprice>980</discountprice>  
                                </recharge>';
                                if ($dthData) {
                                    $dthData->status = "Success";
                                    $dthData->save();
                                }
                            } else {
                                $response = '<recharge> 
                                    <status>FAILURE</status> 
                                    <errorMsg>Sorry! The transaction couldn’t succeed</errorMsg> 
                                </recharge>';
                                if ($dthData) {
                                    $dthData->status = "Failed";
                                    $dthData->save();
                                }

                                $transactionReturnData = [
                                    'user_id'          => $userId,
                                    'service_id'       => $service,
                                    'txn_id'           => $TxnId,
                                    'txn_ref_id'       => $OriginalTxnId,
                                    'account_number'   => $isAvailable->service_account_number,
                                    'tr_total_amount'  => $rechargeAmount,
                                    'tr_amount'        => $amount,
                                    'tr_fee'            => $commission,
                                    'order_id'          => $OriginalTxnId,
                                    'tr_commission'     => '',
                                    'tr_tds'            => '',
                                    'tr_tax'            => $gst,
                                    'tr_date'           => date('Y-m-d H:i:s'),
                                    'tr_type'           => '',
                                    'tr_identifiers'    => '',
                                    'opening_balance'   => $userClosingBalance,
                                    'closing_balance'   => $userOpeningBalance,
                                    'tr_narration'      => $rechargeAmount . ' recharge credited',
                                    'tr_reference'      => '',
                                    'remarks'           => 'recharge deduct',
                                    'udf1'              => '',
                                    'udf2'              => '',
                                    'udf3'              => '',
                                    'udf4'              => '',
                                    'fee_rate'          => '',
                                    'created_at'        => now()->format('Y-m-d H:i:s'),
                                    'updated_at'        => now()->format('Y-m-d H:i:s'),
                                ];

                                $transactionReturn = Transaction::create($transactionReturnData);

                                $isAvailableModel = UserService::where('user_id', $userId)->where('service_id', $service)->first();
                                if ($isAvailableModel) {
                                    $isAvailableModel->transaction_amount += $rechargeAmount;
                                    $isAvailableModel->save();
                                }
                            }
                        } else{
                            return response()->json([
                                'status' => false,
                                'message' => 'Recharge data not found.',
                            ]);
                        }
                        
                    return response($response);
                } else {
                    $checkAndLock['message'] = $product['message'];
                    return Response::failed($checkAndLock['message'], []);
                }
            }
        }
    }

    public function postpaidRecharge(Request $request){
        $validation = new Validations($request);
        $validator = $validation->postpaid();
        $validator->after(function ($validator) use ($request) {
            $User = User::where('id', $request->uid)->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'User Account disabled');
            } else {
                $isAvailable = DB::table('user_services')
                    ->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])
                    ->select('is_active', 'transaction_amount')->first();
                if (isset($isAvailable) && $isAvailable->is_active == '1') {
                    $totalAmount =  $request->amt;
                    if ($isAvailable->transaction_amount <= $totalAmount) {
                        $validator->errors()->add('amount', 'Insufficient funds.');
                    }
                } else {
                    $validator->errors()->add('userId', 'Your payout service is disabled.');
                }
                $Order = PostPaidRecharge::where('customer_ref_id', $request->reqid)->count();
                if ($Order) {
                    $validator->errors()->add('clientRefId', 'Client Ref Id all ready exists.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } else { 
            $isAvailable = DB::table('user_services')->where(['user_id' => $request['uid'], 'service_id' => 'srv_1626077505'])->select('is_active', 'transaction_amount','service_id', 'user_id','service_account_number')->first();
            $transAmount = $isAvailable->transaction_amount;
            $service = $isAvailable->service_id;

            $slug = "postpaid_recharge";
            $amount = $request->amt;
            $product = TransactionHelper::getProductConfig($slug, $service);
            if ($product['status']) {
                if ($product['data']['min_order_value'] <= $amount && $product['data']['max_order_value'] >= $amount) {
                    $product_id = $product['data']['product_id'];
                    $feesAndTaxes = TransactionHelper::getFeesAndTaxes($product_id, $amount,$isAvailable->user_id);
                    
                    $commission = $feesAndTaxes['fee'];
                    $gst = $feesAndTaxes['tax'];
                    $rechargeAmount = $feesAndTaxes['total_amount'];

                    $TxnId = CommonHelper::getRandomString('TXN', 16);
                    $OriginalTxnId = CommonHelper::getRandomString('POSTPAID');
                    $BankTxnid = CommonHelper::getRandomString('CUS');
                    
                    $data = [
                        'amount' => $request->amt,
                        'cir' => $request->cir,
                        'cn' => $request->cn,
                        'op' => $request->op,
                        'user_id' => $isAvailable->user_id,
                        'fee' => $commission,
                        'tax' => $gst,
                        'total_amount' => $rechargeAmount,
                        'service_id' => $service,
                        'customer_ref_id'  => $request->reqid,
                        'txn_id'  => $TxnId,
                        'integration_id'  => 'int_123456789',
                        'status' => '',
                        'bank_txn_id' => $BankTxnid,
                        'original_order_id' => $OriginalTxnId,
                        'response' => '',
                        'description' =>"SUCCESS",
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at'=> now()->format('Y-m-d H:i:s'),

                    ];
                    // dd($data);
                    $rechargeData = PostPaidRecharge::create($data);

                    $update_service = DB::table('user_services')
                    ->where('user_id', $data['user_id'])
                    ->where('service_id', $service)
                    ->where('is_active', "1")
                    ->update([
                        'transaction_amount' => DB::raw('transaction_amount - ' . $rechargeAmount),
                        'updated_at' => now(),
                    ]);
                        
                        $orderRefId = $data['customer_ref_id'];
                        $userId = $data['user_id'];
                        $integrationId = $data['integration_id'];

                        $userOpeningBalance = $isAvailable->transaction_amount;
                        $userClosingBalance =  $userOpeningBalance - $rechargeAmount;

                        $dthData = PostPaidRecharge::where('user_id', $userId)->where('created_at', $data['created_at'])->first();
                        if ($dthData) {
                            $transactionData = [
                                'user_id' => $userId,
                                'service_id' => $service,
                                'txn_id' => $TxnId,
                                'txn_ref_id' => $OriginalTxnId,
                                'account_number' => $isAvailable->service_account_number,
                                'tr_total_amount' => $rechargeAmount,
                                'tr_amount'  => $amount,
                                'tr_fee'   => $commission,
                                'order_id'=> $OriginalTxnId,
                                'tr_commission' => '',
                                'tr_tds'   => '',
                                'tr_tax'   => $gst,
                                'tr_date'  => date('Y-m-d H:i:s'),
                                'tr_type'  => '',
                                'tr_identifiers' => '',
                                'opening_balance' => $userOpeningBalance,
                                'closing_balance' => $userClosingBalance,
                                'tr_narration'  => $rechargeAmount . ' recharge debited',
                                'tr_reference'  => '',
                                'remarks'  => 'recharge deduct',
                                'udf1'  => '',
                                'udf2'  => '',
                                'udf3'  => '',
                                'udf4'  => '',
                                'fee_rate' => '',
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ];
                            // dd($transactionData);
                            $transaction = Transaction::create($transactionData);

                            $transactionSuccess = false;

                            $randomNumber = rand(0, 1);

                            if ($randomNumber == 1) {
                                $response = '<recharge> 
                                    <status>SUCCESS</status> 
                                    <txId>' . $BankTxnid . '</txId> 
                                    <balance>1000</balance> 
                                    <discountprice>980</discountprice>  
                                </recharge>';
                                if ($dthData) {
                                    $dthData->status = "Success";
                                    $dthData->save();
                                }
                            } else {
                                $response = '<recharge> 
                                    <status>FAILURE</status> 
                                    <errorMsg>Sorry! The transaction couldn’t succeed</errorMsg> 
                                </recharge>';
                                if ($dthData) {
                                    $dthData->status = "Failed";
                                    $dthData->save();
                                }

                                $transactionReturnData = [
                                    'user_id' => $userId,
                                    'service_id' => $service,
                                    'txn_id' => $TxnId,
                                    'txn_ref_id' => $OriginalTxnId,
                                    'account_number' => $isAvailable->service_account_number,
                                    'tr_total_amount' => $rechargeAmount,
                                    'tr_amount'  => $amount,
                                    'tr_fee'   => $commission,
                                    'order_id' => $OriginalTxnId,
                                    'tr_commission' => '',
                                    'tr_tds'   => '',
                                    'tr_tax'   => $gst,
                                    'tr_date'  => date('Y-m-d H:i:s'),
                                    'tr_type'  => '',
                                    'tr_identifiers' => '',
                                    'opening_balance' => $userClosingBalance,
                                    'closing_balance' => $userOpeningBalance,
                                    'tr_narration'  => $rechargeAmount . ' recharge credited',
                                    'tr_reference'  => '',
                                    'remarks'  => 'recharge deduct',
                                    'udf1'  => '',
                                    'udf2'  => '',
                                    'udf3'  => '',
                                    'udf4'  => '',
                                    'fee_rate' => '',
                                    'created_at' => now()->format('Y-m-d H:i:s'),
                                    'updated_at' => now()->format('Y-m-d H:i:s'),
                                ];

                                $transactionReturn = Transaction::create($transactionReturnData);

                                $isAvailableModel = UserService::where('user_id', $userId)->where('service_id', $service)->first();
                                // dd($isAvailableModel);
                                if ($isAvailableModel) {
                                    $isAvailableModel->transaction_amount += $rechargeAmount;
                                    $isAvailableModel->save();
                                }
                            }
                        } else{
                            return response()->json([
                                'status' => false,
                                'message' => 'PostRecharge data not found.',
                            ]);
                        }
                        
                    return response($response);
                } else {
                    $checkAndLock['message'] = $product['message'];
                    return Response::failed($checkAndLock['message'], []);
                }
            }
        }
    }

    public function dthRechargePay(Request $request){

        $validation = new Validations($request);
        $validator = $validation->dthRecharge();
        $validator->after(function ($validator) use ($request) {
            $User = User::where('id', $request->uid)->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'User Account disabled');
            } else {
                $isAvailable = DB::table('user_services')
                    ->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])
                    ->select('is_active', 'transaction_amount')->first();
                    
                if (isset($isAvailable) && $isAvailable->is_active == '1') {
                    $totalAmount =  $request->amt;
                    if ($isAvailable->transaction_amount <= $totalAmount) {
                        $validator->errors()->add('amount', 'Insufficient funds.');
                    }
                } else {
                    $validator->errors()->add('userId', 'Your payout service is disabled.');
                }
                $Order = DthRecharge::where('customer_ref_id', $request->reqid)->count();
                if ($Order) {
                    $validator->errors()->add('clientRefId', 'Client Ref Id all ready exists.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        }
        else { 
            $isAvailable = DB::table('user_services')->where(['user_id' => $request['uid'], 'service_id' => 'srv_1626077505'])->select('is_active', 'transaction_amount','service_id', 'user_id','service_account_number')->first();
            $transAmount = $isAvailable->transaction_amount;
            $service = $isAvailable->service_id;

            $slug = "dth_recharge";
            $amount = $request->amt;
            $product = TransactionHelper::getProductConfig($slug, $service);
            if ($product['status']) {
                if ($product['data']['min_order_value'] <= $amount && $product['data']['max_order_value'] >= $amount) {
                    $product_id = $product['data']['product_id'];
                    $feesAndTaxes = TransactionHelper::getFeesAndTaxes($product_id, $amount,$isAvailable->user_id);
                    
                    $commission = $feesAndTaxes['fee'];
                    $gst = $feesAndTaxes['tax'];
                    $rechargeAmount = $feesAndTaxes['total_amount'];

                    $TxnId = CommonHelper::getRandomString('TXN', 16);
                    $BankTxnid = CommonHelper::getRandomString('CUS');
                    $OriginalTxnId = CommonHelper::getRandomString('DTH');
                    $data = [
                        'amount' => $request->amt,
                        'cir' => $request->cir,
                        'cn' => $request->cn,
                        'op' => $request->op,
                        'user_id' => $isAvailable->user_id,
                        'fee' => $commission,
                        'tax' => $gst,
                        'total_amount' => $rechargeAmount,
                        'service_id' => $service,
                        'customer_ref_id'  => $request->reqid,
                        'txn_id'  => $TxnId,
                        'integration_id'  => 'int_123456789',
                        'status' => '',
                        'bank_txn_id' => $BankTxnid,
                        'original_order_id' => $OriginalTxnId,
                        'response' => '',
                        'description' => 'SUCCESS',
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at'=> now()->format('Y-m-d H:i:s'),

                    ];
                    
                    $rechargeData = DthRecharge::create($data);

                    $update_service = DB::table('user_services')
                    ->where('user_id', $data['user_id'])
                    ->where('service_id', $service)
                    ->where('is_active', "1")
                    ->update([
                        'transaction_amount' => DB::raw('transaction_amount - ' . $rechargeAmount),
                        'updated_at' => now(),
                    ]);
                        
                        $orderRefId = $data['customer_ref_id'];
                        $userId = $data['user_id'];
                        $integrationId = $data['integration_id'];

                        $userOpeningBalance = $isAvailable->transaction_amount;
                        $userClosingBalance =  $userOpeningBalance - $rechargeAmount;

                        $dthData = DthRecharge::where('user_id', $userId)->where('created_at', $data['created_at'])->first();
                        if ($dthData) {
                            $transactionData = [
                                'user_id' => $userId,
                                'service_id' => $service,
                                'txn_id' => $TxnId,
                                'txn_ref_id' => $OriginalTxnId,
                                'account_number' => $isAvailable->service_account_number,
                                'tr_total_amount' => $rechargeAmount,
                                'tr_amount'  => $amount,
                                'tr_fee'   => $commission,
                                'order_id'  => $OriginalTxnId,
                                'tr_commission' => '',
                                'tr_tds'   => '',
                                'tr_tax'   => $gst,
                                'tr_date'  => date('Y-m-d H:i:s'),
                                'tr_type'  => '',
                                'tr_identifiers' => '',
                                'opening_balance' => $userOpeningBalance,
                                'closing_balance' => $userClosingBalance,
                                'tr_narration'  => $rechargeAmount . ' recharge debited',
                                'tr_reference'  => '',
                                'remarks'  => 'recharge deduct',
                                'udf1'  => '',
                                'udf2'  => '',
                                'udf3'  => '',
                                'udf4'  => '',
                                'fee_rate' => '',
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ];
                            
                            $transaction = Transaction::create($transactionData);

                            $transactionSuccess = false;

                            $randomNumber = rand(0, 1);

                            if ($randomNumber == 1) {
                                $response = '<recharge> 
                                    <status>SUCCESS</status> 
                                    <txId>' . $BankTxnid . '</txId> 
                                    <balance>1000</balance> 
                                    <discountprice>980</discountprice>  
                                </recharge>';
                                if ($dthData) {
                                    $dthData->status = "Success";
                                    $dthData->save();
                                }
                            } else {
                                $response = '<recharge> 
                                    <status>FAILURE</status> 
                                    <errorMsg>Sorry! The transaction couldn’t succeed</errorMsg> 
                                </recharge>';
                                if ($dthData) {
                                    $dthData->status = "Failed";
                                    $dthData->save();
                                }

                                $transactionReturnData = [
                                    'user_id' => $userId,
                                    'service_id' => $service,
                                    'txn_id' => $TxnId,
                                    'txn_ref_id' => $OriginalTxnId,
                                    'account_number' => $isAvailable->service_account_number,
                                    'tr_total_amount' => $rechargeAmount,
                                    'tr_amount'  => $amount,
                                    'tr_fee'   => $commission,
                                    'order_id' => $OriginalTxnId,
                                    'tr_commission' => '',
                                    'tr_tds'   => '',
                                    'tr_tax'   => $gst,
                                    'tr_date'  => date('Y-m-d H:i:s'),
                                    'tr_type'  => '',
                                    'tr_identifiers' => '',
                                    'opening_balance' => $userClosingBalance,
                                    'closing_balance' => $userOpeningBalance,
                                    'tr_narration'  => $rechargeAmount . ' recharge credited',
                                    'tr_reference'  => '',
                                    'remarks'  => 'recharge deduct',
                                    'udf1'  => '',
                                    'udf2'  => '',
                                    'udf3'  => '',
                                    'udf4'  => '',
                                    'fee_rate' => '',
                                    'created_at' => now()->format('Y-m-d H:i:s'),
                                    'updated_at' => now()->format('Y-m-d H:i:s'),
                                ];

                                $transactionReturn = Transaction::create($transactionReturnData);

                                $isAvailableModel = UserService::where('user_id', $userId)->where('service_id', $service)->first();
                                // dd($isAvailableModel);
                                if ($isAvailableModel) {
                                    $isAvailableModel->transaction_amount += $rechargeAmount;
                                    $isAvailableModel->save();
                                }
                            }
                        } else{
                            return response()->json([
                                'status' => false,
                                'message' => 'DthRecharge data not found.',
                            ]);
                        }
                        
                    return response($response);
                } else {
                    $checkAndLock['message'] = $product['message'];
                    return Response::failed($checkAndLock['message'], []);
                }
            }
        }
    }

    public function electricitybillPay(Request $request){
        $validation = new Validations($request);
        $validator = $validation->electicity();
        $validator->after(function ($validator) use ($request) {
            $User = User::where('email', $request->uid)->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'User Account disabled');
            } else {
                $isAvailable = DB::table('user_services')
                    ->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])
                    ->select('is_active', 'transaction_amount')->first();
                    
                if (isset($isAvailable) && $isAvailable->is_active == '1') {
                    $totalAmount =  $request->amt;
                    if ($isAvailable->transaction_amount <= $totalAmount) {
                        $validator->errors()->add('amount', 'Insufficient funds.');
                    }
                } else {
                    $validator->errors()->add('userId', 'Your payout service is disabled.');
                }
                $Order = ElectricityRecharge::where('customer_ref_id', $request->reqid)->count();
                if ($Order) {
                    $validator->errors()->add('clientRefId', 'Client Ref Id all ready exists.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        }
        else { 
            $User = User::where('email', $request->uid)->first();
            $isAvailable = DB::table('user_services')->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])->select('is_active', 'transaction_amount','service_id', 'user_id','service_account_number')->first();
            // dd($isAvailable);
            $transAmount = $isAvailable->transaction_amount;
            $service = $isAvailable->service_id;

            $slug = "electric_recharge";
            $amount = $request->amt;
            $product = TransactionHelper::getProductConfig($slug, $service);
            if ($product['status']) {
                if ($product['data']['min_order_value'] <= $amount && $product['data']['max_order_value'] >= $amount) {
                    $product_id = $product['data']['product_id'];
                    $feesAndTaxes = TransactionHelper::getFeesAndTaxes($product_id, $amount,$isAvailable->user_id);
                    
                    $commission = $feesAndTaxes['fee'];
                    $gst = $feesAndTaxes['tax'];
                    $rechargeAmount = $feesAndTaxes['total_amount'];

                    $TxnId = CommonHelper::getRandomString('TXN', 16);
                    $BankTxnid = CommonHelper::getRandomString('CUS');
                    $OriginalTxnId = CommonHelper::getRandomString('ELEC');
                    $data = [
                        'amount' => $request->amt,
                        'ad1' => $request->ad1,
                        'ad2' => $request->ad2,
                        'cn' => $request->cn,
                        'op' => $request->op,
                        'user_id' => $isAvailable->user_id,
                        'fee' => $commission,
                        'tax' => $gst,
                        'total_amount' => $rechargeAmount,
                        'service_id' => $service,
                        'customer_ref_id'  => $request->reqid,
                        'txn_id'  => $TxnId,
                        'integration_id'  => 'int_123456789',
                        'status' => '',
                        'bank_txn_id' => $BankTxnid,
                        'original_order_id' => $OriginalTxnId,
                        'response' => '',
                        'description' => 'SUCCESS',
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at'=> now()->format('Y-m-d H:i:s'),
                    ];
                    
                    $rechargeData = ElectricityRecharge::create($data);

                    $update_service = DB::table('user_services')
                    ->where('user_id', $data['user_id'])
                    ->where('service_id', $service)
                    ->where('is_active', "1")
                    ->update([
                        'transaction_amount' => DB::raw('transaction_amount - ' . $rechargeAmount),
                        'updated_at' => now(),
                    ]);
                        
                        $orderRefId = $data['customer_ref_id'];
                        $userId = $data['user_id'];
                        $integrationId = $data['integration_id'];

                        $userOpeningBalance = $isAvailable->transaction_amount;
                        $userClosingBalance =  $userOpeningBalance - $rechargeAmount;

                        $electricData = ElectricityRecharge::where('user_id', $userId)->where('created_at', $data['created_at'])->first();
                        if ($electricData) {
                            $transactionData = [
                                'user_id' => $userId,
                                'service_id' => $service,
                                'txn_id' => $TxnId,
                                'txn_ref_id' => $OriginalTxnId,
                                'account_number' => $isAvailable->service_account_number,
                                'tr_total_amount' => $rechargeAmount,
                                'tr_amount'  => $amount,
                                'tr_fee'   => $commission,
                                'order_id'  => $OriginalTxnId,
                                'tr_commission' => '',
                                'tr_tds'   => '',
                                'tr_tax'   => $gst,
                                'tr_date'  => date('Y-m-d H:i:s'),
                                'tr_type'  => '',
                                'tr_identifiers' => '',
                                'opening_balance' => $userOpeningBalance,
                                'closing_balance' => $userClosingBalance,
                                'tr_narration'  => $rechargeAmount . ' recharge debited',
                                'tr_reference'  => '',
                                'remarks'  => 'recharge deduct',
                                'udf1'  => '',
                                'udf2'  => '',
                                'udf3'  => '',
                                'udf4'  => '',
                                'fee_rate' => '',
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ];
                            
                            $transaction = Transaction::create($transactionData);

                            $transactionSuccess = false;

                            $randomNumber = rand(0, 1);

                            if ($randomNumber == 1) {
                                $response = '<recharge> 
                                    <status>SUCCESS</status> 
                                    <txId>' . $BankTxnid . '</txId> 
                                    <balance>1000</balance> 
                                    <discountprice>980</discountprice>  
                                </recharge>';
                                if ($electricData) {
                                    $electricData->status = "Success";
                                    $electricData->save();
                                }
                            } else {
                                $response = '<recharge> 
                                    <status>FAILURE</status> 
                                    <errorMsg>Sorry! The transaction couldn’t succeed</errorMsg> 
                                </recharge>';
                                if ($electricData) {
                                    $electricData->status = "Failed";
                                    $electricData->save();
                                }

                                $transactionReturnData = [
                                    'user_id' => $userId,
                                    'service_id' => $service,
                                    'txn_id' => $TxnId,
                                    'txn_ref_id' => $OriginalTxnId,
                                    'account_number' => $isAvailable->service_account_number,
                                    'tr_total_amount' => $rechargeAmount,
                                    'tr_amount'  => $amount,
                                    'tr_fee'   => $commission,
                                    'order_id' => $OriginalTxnId,
                                    'tr_commission' => '',
                                    'tr_tds'   => '',
                                    'tr_tax'   => $gst,
                                    'tr_date'  => date('Y-m-d H:i:s'),
                                    'tr_type'  => '',
                                    'tr_identifiers' => '',
                                    'opening_balance' => $userClosingBalance,
                                    'closing_balance' => $userOpeningBalance,
                                    'tr_narration'  => $rechargeAmount . ' recharge credited',
                                    'tr_reference'  => '',
                                    'remarks'  => 'recharge deduct',
                                    'udf1'  => '',
                                    'udf2'  => '',
                                    'udf3'  => '',
                                    'udf4'  => '',
                                    'fee_rate' => '',
                                    'created_at' => now()->format('Y-m-d H:i:s'),
                                    'updated_at' => now()->format('Y-m-d H:i:s'),
                                ];

                                $transactionReturn = Transaction::create($transactionReturnData);

                                $isAvailableModel = UserService::where('user_id', $userId)->where('service_id', $service)->first();
                                // dd($isAvailableModel);
                                if ($isAvailableModel) {
                                    $isAvailableModel->transaction_amount += $rechargeAmount;
                                    $isAvailableModel->save();
                                }
                            }
                        } else{
                            return response()->json([
                                'status' => false,
                                'message' => 'ElectricityRecharge data not found.',
                            ]);
                        }
                        
                    return response($response);
                } else {
                    $checkAndLock['message'] = $product['message'];
                    return Response::failed($checkAndLock['message'], []);
                }
            }
        }
    }

    public function LICPay(Request $request){
        $validation = new Validations($request);
        $validator = $validation->lic();
        $validator->after(function ($validator) use ($request) {
            $User = User::where('email', $request->uid)->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'User Account disabled');
            } else {
                $isAvailable = DB::table('user_services')
                    ->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])
                    ->select('is_active', 'transaction_amount')->first();
                    
                if (isset($isAvailable) && $isAvailable->is_active == '1') {
                    $totalAmount =  $request->amt;
                    if ($isAvailable->transaction_amount <= $totalAmount) {
                        $validator->errors()->add('amount', 'Insufficient funds.');
                    }
                } else {
                    $validator->errors()->add('userId', 'Your payout service is disabled.');
                }
                $Order = LicRecharge::where('customer_ref_id', $request->reqid)->count();
                if ($Order) {
                    $validator->errors()->add('clientRefId', 'Client Ref Id all ready exists.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        }
        else { 
            $User = User::where('email', $request->uid)->first();
            $isAvailable = DB::table('user_services')->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])->select('is_active', 'transaction_amount','service_id', 'user_id','service_account_number')->first();
            // dd($isAvailable);
            $transAmount = $isAvailable->transaction_amount;
            $service = $isAvailable->service_id;

            $slug = "lic_recharge";
            $amount = $request->amt;
            $product = TransactionHelper::getProductConfig($slug, $service);
            if ($product['status']) {
                if ($product['data']['min_order_value'] <= $amount && $product['data']['max_order_value'] >= $amount) {
                    $product_id = $product['data']['product_id'];
                    $feesAndTaxes = TransactionHelper::getFeesAndTaxes($product_id, $amount,$isAvailable->user_id);
                    
                    $commission = $feesAndTaxes['fee'];
                    $gst = $feesAndTaxes['tax'];
                    $rechargeAmount = $feesAndTaxes['total_amount'];

                    $TxnId = CommonHelper::getRandomString('TXN', 16);
                    $BankTxnid = CommonHelper::getRandomString('CUS');
                    $OriginalTxnId = CommonHelper::getRandomString('LIC');
                    $data = [
                        'amount' => $request->amt,
                        'ad1' => $request->ad1,
                        'ad2' => $request->ad2,
                        'ad3' => $request->ad3,
                        'cn' => $request->cn,
                        'op' => $request->op,
                        'user_id' => $isAvailable->user_id,
                        'fee' => $commission,
                        'tax' => $gst,
                        'total_amount' => $rechargeAmount,
                        'service_id' => $service,
                        'customer_ref_id'  => $request->reqid,
                        'txn_id'  => $TxnId,
                        'integration_id'  => 'int_123456789',
                        'status' => '',
                        'bank_txn_id' => $BankTxnid,
                        'original_order_id' => $OriginalTxnId,
                        'response' => '',
                        'description' => 'SUCCESS',
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at'=> now()->format('Y-m-d H:i:s'),

                    ];
                    
                    $rechargeData = LicRecharge::create($data);

                    $update_service = DB::table('user_services')
                    ->where('user_id', $data['user_id'])
                    ->where('service_id', $service)
                    ->where('is_active', "1")
                    ->update([
                        'transaction_amount' => DB::raw('transaction_amount - ' . $rechargeAmount),
                        'updated_at' => now(),
                    ]);
                        
                        $orderRefId = $data['customer_ref_id'];
                        $userId = $data['user_id'];
                        $integrationId = $data['integration_id'];

                        $userOpeningBalance = $isAvailable->transaction_amount;
                        $userClosingBalance =  $userOpeningBalance - $rechargeAmount;

                        $licData = LicRecharge::where('user_id', $userId)->where('created_at', $data['created_at'])->first();
                        if ($licData) {
                            $transactionData = [
                                'user_id' => $userId,
                                'service_id' => $service,
                                'txn_id' => $TxnId,
                                'txn_ref_id' => $OriginalTxnId,
                                'account_number' => $isAvailable->service_account_number,
                                'tr_total_amount' => $rechargeAmount,
                                'tr_amount'  => $amount,
                                'tr_fee'   => $commission,
                                'order_id'  => $OriginalTxnId,
                                'tr_commission' => '',
                                'tr_tds'   => '',
                                'tr_tax'   => $gst,
                                'tr_date'  => date('Y-m-d H:i:s'),
                                'tr_type'  => '',
                                'tr_identifiers' => '',
                                'opening_balance' => $userOpeningBalance,
                                'closing_balance' => $userClosingBalance,
                                'tr_narration'  => $rechargeAmount . ' recharge debited',
                                'tr_reference'  => '',
                                'remarks'  => 'recharge deduct',
                                'udf1'  => '',
                                'udf2'  => '',
                                'udf3'  => '',
                                'udf4'  => '',
                                'fee_rate' => '',
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ];
                            
                            $transaction = Transaction::create($transactionData);

                            $transactionSuccess = false;

                            $check = CommonHelper::checksumStatus($data);

                            $randomNumber = rand(0, 2);

                            if ($randomNumber == 1) {
                                $response = '<recharge> 
                                    <status>SUCCESS</status> 
                                    <txId>' . $BankTxnid . '</txId> 
                                    <balance>1000</balance> 
                                    <discountprice>980</discountprice>  
                                </recharge>';
                                if ($licData) {
                                    $licData->status = "Success";
                                    $licData->save();
                                }
                            } else if($randomNumber == 2) {
                                $response = '<recharge>
                                <status>SUCCESSPENDING</status> 
                                <txId>Init</txId> 
                                <balance>5000000</balance> 
                                <discountprice>396.61</discountprice> 
                                <couponstatus>null</couponstatus> 
                                <opRefNo>null</opRefNo> 
                            </recharge>';
                            if ($licData) {
                                $licData->status = "Pending";
                                $licData->save();
                            }
                            } else {
                                    $response = '<recharge> 
                                    <status>FAILURE</status> 
                                    <errorMsg>Sorry! The transaction couldn’t succeed</errorMsg> 
                                </recharge>';
                                if ($licData) {
                                    $licData->status = "Failed";
                                    $licData->save();
                                }

                                $transactionReturnData = [
                                    'user_id' => $userId,
                                    'service_id' => $service,
                                    'txn_id' => $TxnId,
                                    'txn_ref_id' => $OriginalTxnId,
                                    'account_number' => $isAvailable->service_account_number,
                                    'tr_total_amount' => $rechargeAmount,
                                    'tr_amount'  => $amount,
                                    'tr_fee'   => $commission,
                                    'order_id' => $OriginalTxnId,
                                    'tr_commission' => '',
                                    'tr_tds'   => '',
                                    'tr_tax'   => $gst,
                                    'tr_date'  => date('Y-m-d H:i:s'),
                                    'tr_type'  => '',
                                    'tr_identifiers' => '',
                                    'opening_balance' => $userClosingBalance,
                                    'closing_balance' => $userOpeningBalance,
                                    'tr_narration'  => $rechargeAmount . ' recharge credited',
                                    'tr_reference'  => '',
                                    'remarks'  => 'recharge deduct',
                                    'udf1'  => '',
                                    'udf2'  => '',
                                    'udf3'  => '',
                                    'udf4'  => '',
                                    'fee_rate' => '',
                                    'created_at' => now()->format('Y-m-d H:i:s'),
                                    'updated_at' => now()->format('Y-m-d H:i:s'),
                                ];

                                $transactionReturn = Transaction::create($transactionReturnData);

                                $isAvailableModel = UserService::where('user_id', $userId)->where('service_id', $service)->first();
                                
                                if ($isAvailableModel) {
                                    $isAvailableModel->transaction_amount += $rechargeAmount;
                                    $isAvailableModel->save();
                                }
                            }
                        } else{
                            return response()->json([
                                'status' => false,
                                'message' => 'LicRecharge data not found.',
                            ]);
                        }
                        
                    return response($response);
                } else {
                    $checkAndLock['message'] = $product['message'];
                    return Response::failed($checkAndLock['message'], []);
                }
            }
        }
    }

    public function educationBillPayment(Request $request){
        $validator = Validator::make($request->all(), [
            'uid' => 'required|string',
            'pwd' => 'required|string',
            'cn' => 'required|string',
            'op' => 'required|string',
            'cir' => 'required|string',
            'amt' => 'required|string',
            'reqid' => 'required|string',
            'ad1' => 'required|string',
            'ad2' => 'required|string',
            'ad3' => 'required|string',
            // 'checkSum' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing('Some params are missing.', $message);
        }
    
        try {
            $checksum = '9074608d58d3647decb2a93bd36eb0cd3e1c68aa2d9c8931a6716585171a07d5';
            $headers = [
                'checkSum' => $checksum,
            ];

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    public function creditCardBillPayment(Request $request){
        $validation = new Validations($request);
        $validator = $validation->creditcard();
        $validator->after(function ($validator) use ($request) {
            $User = User::where('id', $request->uid)->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'User Account disabled');
            } else {
                $isAvailable = DB::table('user_services')
                    ->where(['user_id' => $User['id'], 'service_id' => 'srv_1626077505'])
                    ->select('is_active', 'transaction_amount')->first();
                    
                if (isset($isAvailable) && $isAvailable->is_active == '1') {
                    $totalAmount =  $request->amt;
                    if ($isAvailable->transaction_amount <= $totalAmount) {
                        $validator->errors()->add('amount', 'Insufficient funds.');
                    }
                } else {
                    $validator->errors()->add('userId', 'Your payout service is disabled.');
                }
                $Order = CreditcardRecharge::where('customer_ref_id', $request->reqid)->count();
                if ($Order) {
                    $validator->errors()->add('clientRefId', 'Client Ref Id all ready exists.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing($message);
        } 
        else { 
            $isAvailable = DB::table('user_services')->where(['user_id' => $request['uid'], 'service_id' => 'srv_1626077505'])->select('is_active', 'transaction_amount','service_id', 'user_id','service_account_number')->first();
            $transAmount = $isAvailable->transaction_amount;
            $service = $isAvailable->service_id;

            $slug = "creditcard_recharge";
            $amount = $request->amt;
            $product = TransactionHelper::getProductConfig($slug, $service);
            if ($product['status']) {
                if ($product['data']['min_order_value'] <= $amount && $product['data']['max_order_value'] >= $amount) {
                    $product_id = $product['data']['product_id'];
                    $feesAndTaxes = TransactionHelper::getFeesAndTaxes($product_id, $amount,$isAvailable->user_id);
                    
                    $commission = $feesAndTaxes['fee'];
                    $gst = $feesAndTaxes['tax'];
                    $rechargeAmount = $feesAndTaxes['total_amount'];

                    $TxnId = CommonHelper::getRandomString('TXN', 16);
                    $BankTxnid = CommonHelper::getRandomString('CUS');
                    $OriginalTxnId = CommonHelper::getRandomString('CREDIT');
                    // $encryptedCardNumber = Crypt::encryptString($request->cn);
                    $data = [
                        'amount' => $request->amt,
                        'ad1' => $request->ad1,
                        'ad2' => $request->ad2,
                        'ad3' => $request->ad3,
                        'cn' => $request->cn,
                        'op' => $request->op,
                        'user_id' => $isAvailable->user_id,
                        'fee' => $commission,
                        'tax' => $gst,
                        'total_amount' => $rechargeAmount,
                        'service_id' => $service,
                        'customer_ref_id'  => $request->reqid,
                        'txn_id'  => $TxnId,
                        'integration_id'  => 'int_123456789',
                        'status' => "",
                        'bank_txn_id' => $BankTxnid,
                        'original_order_id' => $OriginalTxnId,
                        'response' => "",
                        'description' =>"SUCCESS",
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at'=> now()->format('Y-m-d H:i:s'),

                    ];
                    
                    $rechargeData = CreditcardRecharge::create($data);

                    $update_service = DB::table('user_services')
                    ->where('user_id', $data['user_id'])
                    ->where('service_id', $service)
                    ->where('is_active', "1")
                    ->update([
                        'transaction_amount' => DB::raw('transaction_amount - ' . $rechargeAmount),
                        'updated_at' => now(),
                    ]);
                        
                        $orderRefId = $data['customer_ref_id'];
                        $userId = $data['user_id'];
                        $integrationId = $data['integration_id'];

                        $userOpeningBalance = $isAvailable->transaction_amount;
                        $userClosingBalance =  $userOpeningBalance - $rechargeAmount;

                        $creditData = CreditcardRecharge::where('user_id', $userId)->where('created_at', $data['created_at'])->first();
                        if ($creditData) {
                            $transactionData = [
                                'user_id' => $userId,
                                'service_id' => $service,
                                'txn_id' => $TxnId,
                                'txn_ref_id' => $OriginalTxnId,
                                'account_number' => $isAvailable->service_account_number,
                                'tr_total_amount' => $rechargeAmount,
                                'tr_amount'  => $amount,
                                'tr_fee'   => $commission,
                                'order_id'  => $OriginalTxnId,
                                'tr_commission' => "",
                                'tr_tds'   => '',
                                'tr_tax'   => $gst,
                                'tr_date'  => date('Y-m-d H:i:s'),
                                'tr_type'  => '',
                                'tr_identifiers' => '',
                                'opening_balance' => $userOpeningBalance,
                                'closing_balance' => $userClosingBalance,
                                'tr_narration'  => $rechargeAmount . ' recharge debited',
                                'tr_reference'  => '',
                                'remarks'  => 'recharge deduct',
                                'udf1'  => '',
                                'udf2'  => '',
                                'udf3'  => '',
                                'udf4'  => '',
                                'fee_rate' => '',
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ];
                            
                            $transaction = Transaction::create($transactionData);

                            $transactionSuccess = false;

                            $check = CommonHelper::checksumStatus($data);

                            // $receivedChecksum = $request->header('checksum');
                            // $headers = [
                            //     'checkSum' => $check,
                            // ];
                            // dd($headers);
                            $randomNumber = rand(0, 2);

                            if ($randomNumber == 1) {
                                $response = '<recharge> 
                                    <status>SUCCESS</status> 
                                    <txId>' . $BankTxnid . '</txId> 
                                    <balance>1000</balance> 
                                    <discountprice>980</discountprice>  
                                </recharge>';
                                if ($creditData) {
                                    $creditData->status = "Success";
                                    $creditData->save();
                                }
                            } else if($randomNumber == 2) {
                                $response = '<recharge>
                                <status>SUCCESSPENDING</status> 
                                <txId>Init</txId> 
                                <balance>5000000</balance> 
                                <discountprice>396.61</discountprice> 
                                <couponstatus>null</couponstatus> 
                                <opRefNo>null</opRefNo> 
                            </recharge>';
                            if ($creditData) {
                                $creditData->status = "Pending";
                                $creditData->save();
                            }
                            } else {
                                    $response = '<recharge> 
                                    <status>FAILURE</status> 
                                    <errorMsg>Sorry! The transaction couldn’t succeed</errorMsg> 
                                </recharge>';
                                if ($creditData) {
                                    $creditData->status = "Failed";
                                    $creditData->save();
                                }

                                $transactionReturnData = [
                                    'user_id' => $userId,
                                    'service_id' => $service,
                                    'txn_id' => $TxnId,
                                    'txn_ref_id' => $OriginalTxnId,
                                    'account_number' => $isAvailable->service_account_number,
                                    'tr_total_amount' => $rechargeAmount,
                                    'tr_amount'  => $amount,
                                    'tr_fee'   => $commission,
                                    'order_id' => $OriginalTxnId,
                                    'tr_commission' => "",
                                    'tr_tds'   => '',
                                    'tr_tax'   => $gst,
                                    'tr_date'  => date('Y-m-d H:i:s'),
                                    'tr_type'  => '',
                                    'tr_identifiers' => '',
                                    'opening_balance' => $userClosingBalance,
                                    'closing_balance' => $userOpeningBalance,
                                    'tr_narration'  => $rechargeAmount . ' recharge credited',
                                    'tr_reference'  => '',
                                    'remarks'  => 'recharge deduct',
                                    'udf1'  => '',
                                    'udf2'  => '',
                                    'udf3'  => '',
                                    'udf4'  => '',
                                    'fee_rate' => '',
                                    'created_at' => now()->format('Y-m-d H:i:s'),
                                    'updated_at' => now()->format('Y-m-d H:i:s'),
                                ];

                                $transactionReturn = Transaction::create($transactionReturnData);

                                $isAvailableModel = UserService::where('user_id', $userId)->where('service_id', $service)->first();
                                
                                if ($isAvailableModel) {
                                    $isAvailableModel->transaction_amount += $rechargeAmount;
                                    $isAvailableModel->save();
                                }
                            }
                        } else{
                            return response()->json([
                                'status' => false,
                                'message' => 'Credit Card Recharge data not found.',
                            ]);
                        }
                        
                    return response($response);
                } else {
                    $checkAndLock['message'] = $product['message'];
                    return Response::failed($checkAndLock['message'], []);
                }
            }
        }
    }

    public function ViewLICbill(Request $request) {
        $validator = Validator::make($request->all(), [
            'adParams' => 'required|json',
            'cir'      => 'required|string',
            'uid'      => 'required|string',
            'pswd'     => 'required|string',
            'cn'       => 'required|string',
            'op'       => 'required|string',
        ]);
    
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing('Some params are missing.', $message);
        }
        try{
            $url = 'https://alpha3.mobikwik.com/retailer/v2/retailerViewbill';

            $header = [
                'Method : POST',
                'Content-Type : application/json',
                'X-Mclient : 14',
            ];

            $randomNumber = rand(0, 1);

            if ($randomNumber == 1) {
                $response = [
                    "success" => true,
                    "data" => [
                        [
                            "billNumber" => "LICI2122000003568293",
                            "billAmount" => "233.90",
                            "billnetamount" => "233.90",
                            "billdate" => "06-04-2021 14:57:15",
                            "acceptPayment" => true,
                            "acceptPartPay" => false,
                            "cellNumber" => "731716781",
                            "dueFrom" => "09/02/2021",
                            "dueTo" => "09/03/2021",
                            "validationId" => "HGA7V1295C0064234803",
                            "billId" => "HGA7V1295C0064234803B"
                        ]
                    ]
                ];
            } else{
                $response = [
                    "success" => false,
                    "data" => []
                ];                
            }
            return response()->json($response);

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    public function ViewEducationalbill(Request $request) {
        $validator = Validator::make($request->all(), [
            'adParams.instituteName' => 'required|string',
            'adParams.Mobile Number of Parent' => 'required|string',
            'adParams.DOB Of Student' => 'required|string',
            'adParams.viewBill' => 'required|boolean',
            'cir'      => 'required|string',
            'uid'      => 'required|string',
            'pswd'     => 'required|string',
            'cn'       => 'required|string',
            'op'       => 'required|string',
        ]);
    
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return Response::missing('Some params are missing.', $message);
        }
        try{
            $url = 'https://alpha3.mobikwik.com/retailer/v2/retailerViewbill';

            $header = [
                'Method : POST',
               ' Content-Type:application/json',
               ' X-Mclient:14',
            ];

            $randomNumber = rand(0, 1);

            if ($randomNumber == 1) {
                $response = [
                    "success" => true,
                    "data" => [
                        [
                            "billNumber" => "LICI2122000003568293",
                            "billAmount" => "233.90",
                            "billnetamount" => "233.90",
                            "billdate" => "06-04-2021 14:57:15",
                            "acceptPayment" => true,
                            "acceptPartPay" => false,
                            "cellNumber" => "731716781",
                            "dueFrom" => "09/02/2021",
                            "dueTo" => "09/03/2021",
                            "validationId" => "HGA7V1295C0064234803",
                            "billId" => "HGA7V1295C0064234803B"
                        ]
                    ]
                ];
            } else{
                $response = [
                    "success" => false,
                    "data" => []
                ];                
            }
            return response()->json($response);

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return Response::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return Response::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    // public function TxnRecords1(Request $request) {
    //     $integrationsQuery = DB::table('integrations');
    
    //     if ($request->has('integration_id')) {
    //         $integrationsQuery->where('integration_id', $request->integration_id);
    //     }
    
    //     $integrations = $integrationsQuery->get();
    //     $allpipeData = [];
    
    //     foreach ($integrations as $integration) {
    //         $integration_id = $integration->integration_id;
    //         $upiCollectStatuses = ['success', 'pending', 'rejected'];
    //         $timeIntervals  = ['minutes', 'hours', 'day', 'week', 'months'];
    
    //         $upiCollectAmounts = [];
    //         $upiAmounts = [];
    
    //         foreach ($upiCollectStatuses as $status) {
    //             // foreach($timeIntervals as $timestatus){
    //             $upiCollectAmounts[$status] = UpiCollect::where('status', $status)->where('integration_id', $integration_id)
    //                 ->when($request->has('user_id'), function ($query) use ($request) {
    //                     return $query->where('user_id', $request->user_id);
    //                 })->when($request->has('startDate') && $request->has('endDate'), function ($query) use ($request) {
    //                     return $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
    //                 })->count('id');

    //                 $upiAmounts[$status] = UpiCollect::where('status', $status)->where('integration_id', $integration_id)
    //                 ->when($request->has('user_id'), function ($query) use ($request) {
    //                     return $query->where('user_id', $request->user_id);
    //                 })->when($request->has('startDate') && $request->has('endDate'), function ($query) use ($request) {
    //                     return $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
    //                 })->sum('amount');
    //             // }
    //         }
   
    //         $allpipeData[] = [
    //             'id' => $integration->id,
    //             'name' => $integration->name,
    //             'integration_id' => $integration_id,
    //             'qrRecords' => $upiCollectAmounts,
    //             'TotalAmount' => $upiAmounts,
    //         ];
    //     }
    
    //     return response()->json($allpipeData);
        
    // }

   
}