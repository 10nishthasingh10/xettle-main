<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use Validations\MATMValidation as Validations;
use Exception;
use App\Helpers\ResponseHelper as Response;
use App\Helpers\TransactionHelper;
use App\Services\Matm\MATMService;
use App\Models\MatmTransaction;
use Illuminate\Support\Facades\DB;

/**
 * MATMController
 */
class MATMController extends Controller
{

    public $userId, $clientRefId, $orderRefId;

    protected const URI = '/MATM';
    protected const SEC_URI = '/Encr';


    /**
     * Method agentDetails
     *
     * @param Request $request [explicite description]
     * @param MATMService $service [explicite description]
     *
     * @return void
     */
    public function agentDetails(Request $request, MATMService $service)
    {
        try {

            $validations = Validations::init($request, 'cpDetails');

            if ($validations['status'] == true) {

                if (DB::table('agents')
                    ->where('user_id', $request['auth_data']['user_id'])
                    ->where('merchant_code', $request->merchantCode)
                    ->count() == 0
                ) {
                    return Response::failed('The merchant code is invalid.');
                }
                $body = [
                    "microsdkversion" =>  $request->sdkVer,
                    "merchantcode" => $request->merchantCode,
                    "lattitude" => $request->latitude,
                    "longitude" => $request->longitude,
                    "merchantphoneno" => $request->merchantPhone,
                    "merchantemail" => $request->merchantEmail,
                ];

                $response = $service->init($body, self::URI . '/getcpdetailssdk', 'cpDetails', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode)) {
                    if ($response['response']['response']->statuscode == "000") {

                        DB::table('agents')->where(

                            ['user_id' => $request['auth_data']['user_id'],
                            'merchant_code' => $response['response']['response']->merchantcode])
                            ->whereNull('tid')
                            ->update(['tid' => $response['response']['response']->tid, 'mid' => $response['response']['response']->mid]);
                            if ($request->txnType == 'sdkenquiry') {
                                $clientRefId =  CommonHelper::getRandomString('MBE', false);
                            } else {
                                $clientRefId =  CommonHelper::getRandomString('MCW', false);
                            }

                        $data = [
                            "merchantCode"  => $response['response']['response']->merchantcode,
                            "merchantPhone" => $response['response']['response']->merchantphoneno,
                            "merchantEmail" => $response['response']['response']->merchantemail,
                            "mid"  => $response['response']['response']->mid,
                            "cpid" => $response['response']['response']->cpid,
                            "tid"  => $response['response']['response']->tid,
                            "minAmt" => $response['response']['response']->miniamt,
                            "maxAmt" => $response['response']['response']->maxamt,
                            "clientRefId" => $clientRefId,
                        ];
                        $message = $response['response']['response']->statuscode . ": Record fetched successfully.";
                        $resp = Response::success($message, $data, 200);
                    } else {
                        $message = $response['response']['response']->statuscode . ': ' . $response['response']['response']->message;
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
     * Method txnInit
     *
     * @param Request $request [explicite description]
     * @param MATMService $service [explicite description]
     *
     * @return void
     */
    public function txnInit(Request $request, MATMService $service)
    {
        try {

            $validations = Validations::init($request, 'txnInit');

            if ($validations['status'] == true) {
           
                if ($this->isValidIMEI($request->imei == false)) {
                    return Response::failed('The imei code is invalid.');
                }

                if ($this->validateIMSI($request->imsi == false)) {
                    return Response::failed('The imsi code is invalid.');
                }

                if (DB::table('matm_transactions')->where('client_ref_id', $request->clientRefId)->count()) {
                    return Response::failed('The client ref id is allready taken.');
                }
                $agent = DB::table('agents')
                            ->select('mid', 'tid')
                            ->where('user_id', $request['auth_data']['user_id'])
                            ->where('merchant_code', $request->merchantCode)
                            ->first();
                if (!isset($agent)) {
                    return Response::failed('The merchant code is invalid.');
                } else {
                    if (empty($agent->tid)) {
                        return Response::failed('The tid field is required.');
                    }
                }
                if ($request->txnType == 'sdkenquiry') {
                    $orderRefId =  CommonHelper::getRandomString('MBE', false);
                    $margin = '';
                    $commission = 0;
                    $tds = 0;
                    $txnType = 'be';
                } else {
                    $orderRefId =  CommonHelper::getRandomString('MCW', false);
                    $taxData = self::getFeeAndTaxs($request['auth_data']['user_id'], 'matm');
                    $margin = $taxData['margin'];
                    $commission = $taxData['fee'];
                    $tds = $taxData['tax'];
                    $txnType = 'cw';
                }

                $routeType = 'sdk';
                $request->request->add(['bankrefno' => rand(1, 9999999999)]);
                $request->request->add(['orderRefId' => $orderRefId]);
                
                $body = [
                    "merchantcode" => $request->merchantCode,
                    "serialno" => $request->serialNo,
                    "mid" => $agent->mid,
                    "tid" => $agent->tid,
                    "amount" => $request->amount,
                    "macaddress" =>$request->macAddress,
                    "routeType" => $routeType,
                    "typeOfTxn" => $request->txnType,
                    "imei" => $request->imei,
                    "imsi" => $request->imsi,
                    "bankrefno" => $request->bankrefno,
                    "clientrefid" => $orderRefId,
                    "lattitude" => $request->latitude,
                    "longitude" => $request->longitude,
                    "udf4" => $request->udf1,
                ];

                $orderInserted = MatmTransaction::create($request['auth_data']['user_id'], $request->all(), $orderRefId, $commission, $tds, $margin, $txnType);
                    if ($orderInserted['status']) {


                    $response = $service->init($body, self::URI . '/microatmInsertionAPI_UDF', 'txnInit', $request['auth_data']['user_id'], 'yes', 'matm', 'POST');

                    if (isset($response['response']['response']->statuscode)) {
                        if ($response['response']['response']->statuscode == "000") {
                            $message = $response['response']['response']->statuscode . ": Record fetched successfully.";
                            MatmTransaction::updateRecord(
                                ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                                [
                                    'stanno' => @$response['response']['response']->stanno,
                                    'tmlogid' => @$response['response']['response']->tmlogid,
                                    'rrnno' => @$response['response']['response']->rrnno
                                    ]
                            );
                            $data = [
                                "clientRefId" => $request->clientRefId,
                                "orderRefId" => $response['response']['response']->clientrefid,
                                "stanno" => $response['response']['response']->stanno,
                                "bankRefNo" => $response['response']['response']->bankrefno,
                                "tmlogid" => $response['response']['response']->tmlogid,
                                "tid" =>  $response['response']['response']->tid,
                                "mid" =>  $response['response']['response']->mid,
                                "typeoftxn" =>  $response['response']['response']->typeoftxn,
                                "amount" =>  $response['response']['response']->amount,
                            ];

                            return Response::success($message, $data);
                        } else if ($response['response']['response']->statuscode == "002" || $response['response']['response']->statuscode == "999"){
                            MatmTransaction::updateRecord(
                                ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                                [
                                    'stanno' => @$response['response']['response']->stanno,
                                    'tmlogid' => @$response['response']['response']->tmlogid,
                                    'rrnno' => @$response['response']['response']->rrnno
                                    ]
                            );

                            $data = [
                                "clientRefId" => $request->clientRefId,
                                "orderRefId" => @$orderRefId,
                                "stanno" => @$response['response']['response']->stanno,
                                "bankRefNo" => @$request->bankrefno,
                                "tmlogid" => @$response['response']['response']->tmlogid,
                                "tid" =>  @$agent->tid,
                                "mid" =>  @$agent->mid,
                                "typeoftxn" =>  $request->txnType,
                                "amount" =>   $request->amount,
                            ];
                            $message = $response['response']['response']->statuscode . ': ' . $response['response']['response']->message;
                            return Response::pending($message);
                        } else {
                            MatmTransaction::updateRecord(
                                ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                                [
                                    'status' => 'failed',
                                    'stanno' => @$response['response']['response']->stanno,
                                    'tmlogid' => @$response['response']['response']->tmlogid,
                                    'rrnno' => @$response['response']['response']->rrnno
                                    ]
                            );

                            $data = [
                                "clientRefId" => $request->clientRefId,
                                "orderRefId" => @$orderRefId,
                                "stanno" => @$response['response']['response']->stanno,
                                "bankRefNo" => @$request->bankrefno,
                                "tmlogid" => @$response['response']['response']->tmlogid,
                                "tid" =>  @$agent->tid,
                                "mid" =>  @$agent->mid,
                                "typeoftxn" =>  $request->txnType,
                                "amount" =>   $request->amount,
                            ];
                            $message = $response['response']['response']->statuscode . ': ' . $response['response']['response']->message;
                            return Response::failed($message);
                        }
                    } else {
                        $message = "Something went wrong";
                        return Response::failed($message, $response);
                    }
   
                } else {
                    return Response::failed($orderInserted['message']);
                }

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
     * Method txnInit
     *
     * @param Request $request [explicite description]
     * @param MATMService $service [explicite description]
     *
     * @return void
     */
    public function txnUpdate(Request $request, MATMService $service)
    {
        try {

            $validations = Validations::init($request, 'txnUpdate');

            if ($validations['status'] == true) {

                $status  = 'pending';
                if (isset($request->bankresponsecode)) {
                    $this->userId = $request['auth_data']['user_id'];
                    $this->orderRefId = $request->clientrefid;

                   // $matm = DB::table('matm_transactions')->where(['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $response['response']['response']->clientrefid])->first();

                    if ($request->bankresponsecode == '000') {
                        $status  = 'processed';
                        MatmTransaction::updateRecord(
                            ['user_id' => $this->userId, 'order_ref_id' => $this->orderRefId],
                            [
                                'status' => 'processed',
                                'stanno' => @$request->stanno,
                                'tmlogid' => @$request->tmlogid,
                                'bank_response_code' => @$request->bankresponsecode,
                                'rrnno' => @$request->rrnno,
                                'auth_id' => @$request->authId,
                                'invoice_no' => @$request->invoiceno,
                                'cardno' => @$request->cardno,
                                'microatm_bank_response' => @$request->microatmbankresponse,
                                'batch_no' => @$request->batchno,
                                'bank_name' => @$request->bankname,
                                'card_type' => @$request->cardtype,
                            ]
                        );

                    } else if ($request->bankresponsecode == "002" || $request->bankresponsecode == "999"){
                        MatmTransaction::updateRecord(
                            ['user_id' => $this->userId, 'order_ref_id' => $this->orderRefId],
                            [
                                'stanno' => @$request->stanno,
                                'tmlogid' => @$request->tmlogid,
                                'bank_response_code' => @$request->bankresponsecode,
                                'rrnno' => @$request->rrnno,
                                'auth_id' => @$request->authId,
                                'invoice_no' => @$request->invoiceno,
                                'cardno' => @$request->cardno,
                                'microatm_bank_response' => @$request->microatmbankresponse,
                                'batch_no' => @$request->batchno,
                                'bank_name' => @$request->bankname,
                                'card_type' => @$request->cardtype,
                            ]
                        );
                    } else {
                        if ($request->bankresponsecode == "001") {
                            $status  = 'failed';
                            MatmTransaction::updateRecord(
                                ['user_id' => $this->userId, 'order_ref_id' => $this->orderRefId],
                                [
                                    'stanno' => @$request->stanno,
                                    'status' => 'failed',
                                    'tmlogid' => @$request->tmlogid,
                                    'failed_message' => @$request->bankmessage,
                                    'bank_response_code' => @$request->bankresponsecode,
                                    'rrnno' => @$request->rrnno,
                                    'auth_id' => @$request->authId,
                                    'invoice_no' => @$request->invoiceno,
                                    'cardno' => @$request->cardno,
                                    'microatm_bank_response' => @$request->microatmbankresponse,
                                    'batch_no' => @$request->batchno,
                                    'bank_name' => @$request->bankname,
                                    'card_type' => @$request->cardtype,
                                ]
                            );
                        } else {
                            MatmTransaction::updateRecord(
                                ['user_id' => $this->userId, 'order_ref_id' => $this->orderRefId],
                                [
                                    'stanno' => @$request->stanno,
                                    'tmlogid' => @$request->tmlogid,
                                    'bank_response_code' => @$request->bankresponsecode,
                                    'rrnno' => @$request->rrnno,
                                    'auth_id' => @$request->authId,
                                    'invoice_no' => @$request->invoiceno,
                                    'cardno' => @$request->cardno,
                                    'microatm_bank_response' => @$request->microatmbankresponse,
                                    'batch_no' => @$request->batchno,
                                    'bank_name' => @$request->bankname,
                                    'card_type' => @$request->cardtype,
                                ]
                            );
                        }
                    }
                    $response = $service->init($request->all(), self::URI . '/microatmUpdateAPI_UDF', 'txnUpdate', $request['auth_data']['user_id'], 'yes', 'matm', 'POST');
                    if ($status == 'processed') {
                        return Response::success('Transaction successful.', @$response['response']['response']);
                    } else if ($status == 'failed') {
                        return Response::failed('Transaction failed.', @$response['response']['response']);
                    } else {
                        return Response::pending('Transaction pending.', @$response['response']['response']);
                    }

                } else {
                    return Response::failed('Invalid request');
                }

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
     * @param MATMService $service [explicite description]
     *
     * @return void
     */
    public function txnStatus(Request $request, MATMService $service)
    {
        try {

            $validations = Validations::init($request, 'txnStatus');

            if ($validations['status'] == true) {
                $txn = DB::table('matm_transactions')->select('order_ref_id')
                        ->where('user_id', $request['auth_data']['user_id'])
                        ->where('client_ref_id', $request->clientRefId)
                        ->orWhere('order_ref_id', $request->clientRefId)
                        ->first();
                if (!isset($txn)) {
                    return Response::failed('The client ref id is invalid.');
                }
                $body = [
                    "refernceno" =>  $txn->order_ref_id
                ];

                $response = $service->init($body, '/statuscheck/getmatmstatus', 'statusCheck', $request['auth_data']['user_id'], 'yes', 'matm');

                if (isset($response['response']['response']->statuscode)) {
                    if ($response['response']['response']->statuscode == "000") {
                        MatmTransaction::updateRecord(
                            ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => @$txn->order_ref_id],
                            [
                                'status' => 'processed',
                                'stanno' => @$response['response']['response']->stanno,
                                'rrnno' => @$response['response']['response']->rrnno,
                                'cardno' => @$response['response']['response']->cardno,
                                'card_type' => @$response['response']['response']->cardtype,
                                'bank_ref_no' => @$response['response']['response']->bankrefno,
                            ]
                        );

                        $message = @$response['response']['response']->statuscode . ": Record fetched successfully.";
                        $resp = Response::success($message, @$response['response']['response']);
                    } else if ($response['response']['response']->statuscode == "001") {

                        $failedmessage = isset($response['response']['response']->bankmessage) ? $response['response']['response']->bankmessage : $response['response']['response']->message;
                        MatmTransaction::updateRecord(
                            ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' =>  @$txn->order_ref_id],
                            [
                                'status' => 'failed',
                                'stanno' => @$response['response']['response']->stanno,
                                'failed_message' =>  @$failedmessage,
                                'rrnno' => @$response['response']['response']->rrnno,
                                'cardno' => @$response['response']['response']->cardno,
                                'card_type' => @$response['response']['response']->cardtype,
                                'bank_ref_no' => @$response['response']['response']->bankrefno,
                            ]
                        );
                        $resp = Response::failed($failedmessage, @$response['response']['response']);
                    } else {
                        $failedmessage = isset($response['response']['response']->bankmessage) ? $response['response']['response']->bankmessage : $response['response']['response']->message;
                        $resp = Response::pending($failedmessage, @$response['response']['response']);
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

    
    public function isValidIMEI($imei)
    {
            // Remove any non-digits from the IMEI number
            $imei = preg_replace('/[^0-9]/', '', $imei);
            // Check the length of the IMEI number
            if (strlen($imei) != 15) {
                return false;
            }
            // Calculate the Luhn checksum
            $sum = 0;
            for ($i = 0; $i < 14; $i++) {
                $digit = $imei[$i];
                if ($i % 2 == 0) {
                    $digit *= 2;
                    if ($digit > 9) {
                        $digit = ($digit % 10) + 1;
                    }
                }
                $sum += $digit;
            }
            $checksum = (10 - ($sum % 10)) % 10;
            // Compare the calculated checksum with the last digit of the IMEI number
            return $checksum == $imei[14];
        }



    public function validateIMSI($imsi) {
        // Remove any non-numeric characters
        $imsi = preg_replace('/[^0-9]/', '', $imsi);

        // IMSI should be exactly 15 digits long
        if (strlen($imsi) != 15) {
            return false;
        }

        // The first three digits should be between 001 and 999
        $mcc = substr($imsi, 0, 3);
        if ($mcc < 1 || $mcc > 999) {
            return false;
        }

        // The next two digits should be between 01 and 99
        $mnc = substr($imsi, 3, 2);
        if ($mnc < 1 || $mnc > 99) {
            return false;
        }

        // The remaining 10 digits should be numeric
        $msin = substr($imsi, 5);
        if (!is_numeric($msin)) {
            return false;
        }

        // IMSI is valid
        return true;
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
            $getProductId = CommonHelper::getProductId('matm_' . $slug, 'matm');
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
}
