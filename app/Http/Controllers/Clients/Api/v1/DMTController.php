<?php

namespace App\Http\Controllers\Clients\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\CommonHelper;
use App\Helpers\InstantPayHelper;
use App\Helpers\ResponseHelper;
use Validations\DMTValidation as Validations;
use Exception;
use App\Helpers\ResponseHelper as Response;
use App\Helpers\TransactionHelper;
use App\Helpers\WebhookHelper;
use App\Models\DMTFundTransfer;
use App\Models\DMTOutlet;
use App\Models\Remitter;
use App\Models\UserService;
use App\Models\Webhook;
use App\Services\ipay\DMTService;
use Illuminate\Support\Facades\DB;

/**
 * DMTController
 */
class DMTController extends Controller
{

    public $userId, $clientRefId, $orderRefId;

    protected const URI = '/fi/remit/out/domestic';
    protected const OUTLET_URI = '/user/outlet/signup';


    /**
     * Method remitterRegistration
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function remitterRegistration(Request $request, DMTService $service)
    {
        try {

            $validations = Validations::init($request, 'remitterRegistration');

            if ($validations['status'] == true) {
                $count = DMTOutlet::where('outlet_id', $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->first();

                $remitter = Remitter::where('outlet_id' , $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->where('mobile', $request->remitterMobile)->count();
                if ($remitter > 0) {
                    return Response::failed('The remitter mobile no already exists.');
                }

                if (isset($count) && !empty($count->outlet_id)) {
                    $body = [
                        "mobile" => $request->mobile,
                        "firstName" => $request->firstName,
                        "lastName" => $request->lastName,
                        "pinCode" => $request->pinCode,
                        "outletId" => $request->outletId
                    ];
                    $remitter = Remitter::where('outlet_id' , $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->where('mobile', $request->mobile)->first();
                    if (isset($remitter) && !empty($remitter)) {
                        $orderInserted['status'] = true;
                    } else {
                        $orderInserted = Remitter::create($request['auth_data']['user_id'], $body);
                    }

                    if ($orderInserted['status']) {
                        $response = $service->init($body, self::URI . '/remitterRegistration', 'remitterRegistration', $request['auth_data']['user_id'], 'yes');

                        if (isset($response['response']['response']->statuscode) && in_array($response['response']['response']->statuscode, ['OTP', 'TXN'])) {

                            if ($response['response']['response']->statuscode == 'TXN') {
 

                                $resp = self::responseFormat('remitterVeriryOTP', $response['response']['response']->data, "");

                                return Response::success('Remitter Added Successfully.', $resp);
                            }

                            $respData['otpReference'] = $response['response']['response']->data->otpReference;
                            return Response::success('OTP Send successfully.', $respData);
                        } else {
                            return Response::failed(@$response['response']['response']->status);
                        }
                    } else {
                        return ResponseHelper::failed(@$orderInserted['message'], []);
                    }
                } else {
                    return Response::failed('Invalid outlet Id');
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
     * Method beneficiaryRegistration
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function beneficiaryRegistration(Request $request, DMTService $service)
    {
        try {

            $validations = Validations::init($request, 'beneficiaryRegistration');

            if ($validations['status'] == true) {
                $count = DMTOutlet::where('outlet_id', $request->outletId)->first();
                $remitter = Remitter::where('outlet_id' , $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->where('mobile', $request->remitterMobile)->count();
                if ($remitter == 0) {
                    return Response::failed('Invalid remitter mobile no');
                }
                if (isset($count) && !empty($count->outlet_id)) {
                    $body = [
                        "remitterMobile" => $request->remitterMobile,
                        "firstName" => $request->name,
                        "ifsc" => $request->ifsc,
                        "accountNumber" => $request->accountNumber,
                        "bankId" => $request->bankId,
                        "outletId" => @$count->outlet_id
                    ];

                    $response = $service->init($body, self::URI . '/beneficiaryRegistration', 'beneficiaryRegistration', $request['auth_data']['user_id'], 'yes');

                    if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {
                        return Response::success('Beneficiary Added Successfully.', $response['response']['response']->data);
                    } else {
                        return Response::failed(@$response['response']['response']->status);
                    }
                } else {
                    return Response::failed('Invalid outlet Id');
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
     * Method remitterDetails
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function remitterDetails(Request $request, DMTService $service)
    {
        try {

            if (isset($request->outletId) && !empty($request->outletId)) {
                if (!isset($request->mobile) || empty($request->mobile)) {
                    return Response::failed('The mobile number is required.');
                }
                $count = DMTOutlet::where('outlet_id', $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->first();

                $remitter = Remitter::where('outlet_id' , $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->where('mobile', $request->mobile)->count();
                if ($remitter == 0) {
                    return Response::failed('Invalid remitter mobile no');
                }

                if (isset($count) && !empty($count->outlet_id)) {
                    $body = [
                        "mobile" => @$request->mobile,
                        "outletId" => @$request->outletId
                    ];

                    $response = $service->init($body, self::URI . '/remitterProfile', 'remitterProfile', $request['auth_data']['user_id'], 'yes', 'dmt', 'GET');

                    if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {
                        $resp = self::responseFormat('remitterVeriryOTP', $response['response']['response']->data, "");

                        return Response::success('Remitter Details Fetched Successfully.', $resp);
                    } else if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'OTP') {
                        $respData['otpReference'] = $response['response']['response']->data->otpReference;

                        return Response::success('OTP Sent Successfully.', $respData);
                    } else{
                        return Response::failed(@$response['response']['response']->status);
                    }
                } else {
                    return Response::failed('The outlet id is invalid.');
                }
            } else {
                return Response::failed('The outlet id is required.');
            }
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    public function banks(Request $request, DMTService $service)
    {
        try {
            $validations = Validations::init($request, 'remitterDetails');

            if ($validations['status'] == true) {

                $body = [
                    "outletId" => @$request->outletId
                ];
                $response = $service->init($body, self::URI . '/banks', 'banks', $request['auth_data']['user_id'], 'yes', 'dmt', 'GET');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {
                    return Response::success('Record fetched successfully.', $response['response']['response']->data);
                } else {
                    return Response::failed(@$response['response']['response']->status);
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
     * Method remitterTransferLimit
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function remitterTransferLimit(Request $request, DMTService $service)
    {
        try {
            $validations = Validations::init($request, 'remitterEKYC');
            $remitter = Remitter::where('outlet_id' , $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->where('mobile', $request->mobile)->count();
            if ($remitter == 0) {
                return Response::failed('Invalid remitter mobile no');
            }

            if ($validations['status'] == true) {
                $body = [
                    "mobile" => $request->mobile,
                    "outletId" => $request->outletId,
                ];
                $response = $service->init($body, self::URI . '/remitterTransferLimit', 'remitterTransferLimit', $request['auth_data']['user_id'], 'yes', 'dmt', 'GET');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {
                    return Response::success('Remitter Transfer Limits Fetched Successfully.', $response['response']['response']->data);
                } else {
                    return Response::failed(@$response['response']['response']->status);
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
     * Method outletInit
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function outletInit(Request $request, DMTService $service)
    {
        try {
            $validations = Validations::init($request, 'outletInit');

            if ($validations['status'] == true) {
                
                if (DB::table('dmt_outlets')->where('user_id', $request['auth_data']['user_id'])->where('merchant_code', $request->merchantCode)
                ->where('is_active', '1')->count()) {
                    return Response::failed('The merchant code should be unique.');
                }

                $aadhaar = self::encAadhaar($request->aadhaar);
                $body = [
                    "mobile" => $request->mobile,
                    "pan" => $request->pan,
                    "email" => $request->email,
                    "merchant_code" => $request->merchantCode,
                    "aadhaar" => $aadhaar,
                    "latitude" => $request->latitude,
                    "longitude" => $request->longitude,
                    "consent" => 'Y',
                ];
                $count = DMTOutlet::where('mobile', $request->mobile)->where(['user_id' => $request['auth_data']['user_id']])->count();
                if ($count == 0) {
                    $orderInserted = DMTOutlet::create($request['auth_data']['user_id'], $body, $request->aadhaar);
                } else {
                    $orderInserted['status'] = true;
                }
                if ($orderInserted['status']) {
                    $response = $service->init($body, self::OUTLET_URI . '/initiate', 'outletInit', $request['auth_data']['user_id'], 'yes');

                    if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {

                        DMTOutlet::updateRecord(['user_id' => $request['auth_data']['user_id'], 'mobile' => $request->mobile],
                        [
                           "pan" => $request->pan,
                           "email" => $request->email,
                           "aadhaar" => $request->aadhaar,
                           "latitude" => $request->latitude,
                           "longitude" => $request->longitude
                       ]);
                        $respData['aadhaar'] = $response['response']['response']->data->aadhaar;
                        $respData['otpReference'] = $response['response']['response']->data->otpReferenceID;
                        $respData['hash'] = $response['response']['response']->data->hash;
                        return Response::success('OTP Sent Successfully.', $respData);
                    } else {
                        if (isset($response['response']['response']->status)) {
                            if (str_contains($response['response']['response']->status, 'xxxxxx')) {
                                $message = $response['response']['response']->status." enter same mobile no";
                            } else {
                                $message =  $response['response']['response']->status;
                            }
                        } else {
                            $message = SOMETHING_WENT_WRONG;
                        }
                        return Response::failed(@$message);
                    }
                } else {
                    return ResponseHelper::failed($orderInserted['message'], []);
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
     * Method outletOTPVerify
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function outletOTPVerify(Request $request, DMTService $service)
    {
        try {

            $validations = Validations::init($request, 'outletOTPVerify');

            if ($validations['status'] == true) {
                $body = [
                    "otpReferenceID" => $request->otpReference,
                    "otp" => $request->otp,
                    "hash" => $request->hash,
                ];


                $response = $service->init($body, self::OUTLET_URI . '/validate', 'outletOTPVerify', $request['auth_data']['user_id'], 'yes');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {

                    DMTOutlet::updateRecord(
                        ['user_id' => $request['auth_data']['user_id'], 'mobile' => $request->mobile],
                        [
                            'outlet_id' => $response['response']['response']->data->outletId,
                            'name' => $response['response']['response']->data->name,
                            'dob' => $response['response']['response']->data->dateOfBirth,
                            'gender' => $response['response']['response']->data->gender,
                            'pincode' => $response['response']['response']->data->pincode,
                            'state' => $response['response']['response']->data->state,
                            'is_active' => '1',
                            'district_name' => $response['response']['response']->data->districtName,
                            'address' => $response['response']['response']->data->address,
                        ]
                    );


                    return Response::success('OTP Verified Successfully.', $response['response']['response']->data);
                } else {

                    return Response::failed(@$response['response']['response']->status);
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
     * Method beneficiaryRemove
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function beneficiaryRemove(Request $request, DMTService $service)
    {
        try {

            $validations = Validations::init($request, 'beneficiaryRemove');

            if ($validations['status'] == true) {

                $remitter = Remitter::where('outlet_id' , $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->where('mobile', $request->remitterMobile)->count();
                if ($remitter == 0) {
                    return Response::failed('Invalid remitter mobile no');
                }
                $body = [
                    "remitterMobile" => $request->remitterMobile,
                    "beneficiaryId" => $request->beneficiaryId,
                    "outletId" => $request->outletId,
                ];

                $response = $service->init($body, self::URI . '/beneficiaryDelete', 'beneficiaryDelete', $request['auth_data']['user_id'], 'yes', 'dmt', 'DELETE');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'OTP') {
                    $respData['otpReference'] = $response['response']['response']->data->otpReference;

                    return Response::success('OTP Sent Successfully.', $respData);
                } else {
                    return Response::failed(@$response['response']['response']->status);
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
     * Method beneficiaryOTPValidate
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function beneficiaryOTPValidate(Request $request, DMTService $service)
    {
        try {

            $validations = Validations::init($request, 'beneficiaryOTPValidate');

            if ($validations['status'] == true) {

                $body = [
                    "otp" => $request->otp,
                    "otpReference" => $request->otpReference,
                    "outletId" => @$request->outletId
                ];
                $response = $service->init($body, self::URI . '/otpVerification', 'beneficiaryOTPValidate', $request['auth_data']['user_id'], 'yes', 'dmt', 'POST');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {
                    return Response::success('Beneficiary Removed Successfully.', $response['response']['response']->data);
                } else {
                    return Response::failed(@$response['response']['response']->status);
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
     * Method remitterUpdate
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function remitterUpdate(Request $request, DMTService $service)
    {
        try {


            $validations = Validations::init($request, 'remitterUpdate');

            if ($validations['status'] == true) {

                $remitter = Remitter::where('outlet_id' , $request->outletId)->where(['user_id' => $request['auth_data']['user_id']])->where('mobile', $request->mobile)->count();
                if ($remitter == 0) {
                    return Response::failed('Invalid remitter mobile no');
                }

                $body = [
                    "mobile" => $request->mobile,
                    "firstName" => $request->firstName,
                    "lastName" => $request->lastName,
                    "pinCode" => $request->pinCode,
                    "outletId" => $request->outletId
                ];


                $response = $service->init($body, self::URI . '/remitterUpdate', 'remitterUpdate', $request['auth_data']['user_id'], 'yes', 'dmt', 'PUT');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'OTP') {

                    return Response::success('OTP Sent Successfully.', $response['response']['response']->data);
                } else {
                    return Response::failed(@$response['response']['response']->status);
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
     * Method remitterOTPValidate
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function remitterOTPValidate(Request $request, DMTService $service)
    {
        try {

            $validations = Validations::init($request, 'remitterOTPValidate');

            if ($validations['status'] == true) {


                $remitter = Remitter::where('outlet_id' , $request->outletId)
                    ->where(['user_id' => $request['auth_data']['user_id']])
                    ->where('mobile', $request->mobile)->first();
                if (isset($remitter) && !empty($remitter)) {
                    $body = [
                        "mobile" => @$request->mobile,
                        "otp" => $request->otp,
                        "otpReference" => $request->otpReference,
                        "outletId" => @$request->outletId,
                    ];

                    $response = $service->init($body, self::URI . '/otpVerification', 'remitterOTPValidate', $request['auth_data']['user_id'], 'yes', 'dmt', 'POST');

                    if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {

                        Remitter::updateRecord(
                            ['outlet_id' => $request->outletId, 'user_id' => $request['auth_data']['user_id'], 'mobile' => $request->mobile],
                            [
                                'first_name' => $response['response']['response']->data->firstName,
                                'last_name' => $response['response']['response']->data->lastName,
                                'pin' => $response['response']['response']->data->pincode,
                            ]
                        );
                        $resp = self::responseFormat('remitterVeriryOTP', $response['response']['response']->data, "");
                        return Response::success('Remitter Updated Successfully.', $resp);
                    } else {
                        return Response::failed(@$response['response']['response']->status);
                    }
                } else {
                    return Response::failed('Invalid otp reference.');
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
     * Method fundTransfer
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function fundTransfer(Request $request, DMTService $service)
    {
        try {

            $validations = Validations::init($request, 'fundTransfer');

            if ($validations['status'] == true) {


                $userService = UserService::where(['user_id' => $request['auth_data']['user_id'], 'service_id' => DMT_SERVICE_ID])->first();

                if (isset($userService) && !empty($userService->is_active)) {
                    $mode = 'imps';
                    $taxData = self::getFeeAndTaxs($request['auth_data']['user_id'], $mode);
                    $totalAmount =  $request->amount + $taxData['fee'];
                    if ($userService->is_active != '1') {
                        if ($userService->is_active == '0') {
                            $message = "Your service inactive.";
                        } else if ($userService->is_active == '2') {
                            $message = "Your service is suspended.";
                        }
                        if (isset($message)) {
                            return Response::failed($message);
                        } else {
                            return Response::failed('This service is down.');
                        }
                    } else if ($userService->transaction_amount < $totalAmount) {
                        return Response::failed('Insufficient funds.');
                    }
                }

                $remitter = Remitter::where(['user_id' => $request['auth_data']['user_id']])->where('mobile', $request->remitterMobile)->count();
                if ($remitter == 0) {
                    return Response::failed('Invalid remitter mobile no');
                }

                $count = Remitter::where('outlet_id' , $request->outletId)->where((['user_id' => $request['auth_data']['user_id']]))->first();
                if (isset($count) && !empty($count)) {
                    $orderRefId = CommonHelper::getRandomString('DMT', false);
                    $body = [
                        "remitterMobile" => @$request->remitterMobile,
                        "beneficiaryId" => $request->beneficiaryId,
                        "transferMode" => 'IMPS',
                        "transferAmount" => $request->amount,
                        "latitude" => $request->latitude,
                        "longitude" => $request->longitude,
                        "externalRef" => $orderRefId,
                        "outletId" => @$request->outletId,

                    ];

                    $orderInserted = DMTFundTransfer::create($request['auth_data']['user_id'], $request->all(),  $taxData['fee'], 0, $taxData['margin'], $orderRefId);
                    if ($orderInserted['status']) {


                        $this->userId = $request['auth_data']['user_id'];
                        $this->clientRefId = $request->clientRefId;
                        $this->orderRefId = $orderRefId;

                        $debit = DMTFundTransfer::moveOrderToProcessingByOrderId($this->userId, $this->orderRefId);

                        if ($debit['status']) {
                            $response = $service->init($body, '/fi/remit/out/domestic' . '/fundTransfer', 'fundTransfer', $this->userId, 'yes', 'dmt', 'POST');
                            DMTFundTransfer::updateRecord(
                                ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                                ['is_api_call' => '1', 'cron_date' => date('Y-m-d H:i:s')]
                            );

                            if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {

                                DMTFundTransfer::updateRecord(
                                    ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                                    [
                                        'status' => 'processed',
                                        'beni_name' => @$response['response']['response']->data->beneficiaryName,
                                        'bank_ifsc' => @$response['response']['response']->data->beneficiaryIfsc,
                                        'bank_account' => @$response['response']['response']->data->beneficiaryAccount,
                                    'utr' => $response['response']['response']->data->txnReferenceId]
                                );

                                DMTFundTransfer::cashbackCredit($this->userId, $this->orderRefId);

                                $resp = DMTController::responseFormat('fundTransfer', $response['response']['response']->data, $this->clientRefId);


                                return Response::success($response['response']['response']->status, $resp);
                            } else if (isset($response['response']['response']->statuscode) && in_array($response['response']['response']->statuscode, ['TUP'])) {

                                DMTFundTransfer::updateRecord(
                                    ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                                    [
                                        'beni_name' => @$response['response']['response']->data->beneficiaryName,
                                        'bank_ifsc' => @$response['response']['response']->data->beneficiaryIfsc,
                                        'bank_account' => @$response['response']['response']->data->beneficiaryAccount]);

                                return Response::pending($response['response']['response']->status, $response['response']['response']->data);
                            } else {

                                $failedArray = [
                                    'RPI', 'UAD', 'IAC', 'IAT', 'AAB', 'IAB', 'ISP', 'DID', 'DTX', 'IAN', 'IRA', 'DTB', 'RBT', 'SPE', 'SPD', 'UED', 'IEC', 'IRT', 'ITI', 'TSU', 'IPE', 'ISE', 'TRP', 'OUI', 'ODI', 'TDE', 'DLS', 'RNF', 'RAR', 'IVC',
                                    'IUA', 'SNA', 'ERR', 'FAB', 'UFC', 'OLR', 'EOP', 'ONV', 'RAB'
                                ];
                                if (isset($response['response']['response']->statuscode) && in_array($response['response']['response']->statuscode, $failedArray)) {

                                        DMTFundTransfer::updateRecord(
                                            ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                                            [
                                                'beni_name' => @$response['response']['response']->data->beneficiaryName,
                                                'bank_ifsc' => @$response['response']['response']->data->beneficiaryIfsc,
                                                'bank_account' => @$response['response']['response']->data->beneficiaryAccount]);

                                        DMTFundTransfer::fundRefunded($this->userId, $this->orderRefId, @$response['response']['response']->status, 'dmt_fund_refunded', @$response['response']['response']->statuscode);

                                        return Response::failed(@$response['response']['response']->status);
                                } else {
                                    DMTFundTransfer::updateRecord(
                                        ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                                        [
                                            'beni_name' => @$response['response']['response']->data->beneficiaryName,
                                            'bank_ifsc' => @$response['response']['response']->data->beneficiaryIfsc,
                                            'bank_account' => @$response['response']['response']->data->beneficiaryAccount]);

                                    return Response::pending($response['response']['response']->status, $response['response']['response']->data);
                                }
                            }
                        } else {
                            return Response::failed($debit['message']);
                        }
                    } else {
                        return Response::failed($orderInserted['message']);
                    }
                } else {
                    return Response::failed('Invalid outlet no.');
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
     * Method remitterEKYC
     *
     * @param Request $request [explicite description]
     * @param DMTService $service [explicite description]
     *
     * @return void
     */
    public function remitterEKYC(Request $request, DMTService $service)
    {
        try {
            $validations = Validations::init($request, 'remitterEKYC');

            if ($validations['status'] == true) {

                $remitter = Remitter::where('outlet_id' , $request->outletId)->where('mobile', $request->mobile)->count();
                if ($remitter == 0) {
                    return Response::failed('Invalid remitter mobile no');
                }

                $body = [
                    "mobile" => $request->mobile,
                    "outletId" => $request->outletId
                ];
                $response = $service->init($body, self::URI . '/remitterEkycInitiate?mobile=' . $request->mobile, 'remitterEKYC', $request['auth_data']['user_id'], 'yes', 'dmt', 'GET');

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {
                    return Response::success('Remitter E-KYC initiate successfully.', $response['response']['response']->data);
                } else {
                    return Response::failed(@$response['response']['response']->status);
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
     * Method fetchById
     *
     * @param Request $request [explicite description]
     * @param $clientRefId $clientRefId [explicite description]
     *
     * @return void
     */
    public function fetchById(Request $request, $clientRefId)
    {

        $userId = $request["auth_data"]['user_id'];

        $order = DB::table('dmt_fund_transfers')
            ->select('dmt_fund_transfers.status', 'user_id', 'cron_date', 'order_ref_id')
            ->where('dmt_fund_transfers.client_ref_id', $clientRefId)
            ->orWhere('dmt_fund_transfers.order_ref_id', $clientRefId)
            ->where('dmt_fund_transfers.user_id', $userId)
            ->first();

        $orderId = @$order->order_ref_id;

        if (isset($order->status) && $order->status == 'processing') {

            $instantPay = new InstantPayHelper;

            if (isset($order->cron_date) && !empty($order->cron_date)) {

                $cronDate = date('Y-m-d', strtotime($order->cron_date));
                $requestTransfer = $instantPay->instantpayTransferStatus($orderId, $cronDate, $order->user_id);
                if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {

                    $errorDesc  = isset($requestTransfer['data']->data->transactionStatus) ? $requestTransfer['data']->data->transactionStatus : @$requestTransfer['data']->status;
                    $bank_reference = "";
                    $failedArray =['IAN', 'FAB', 'TRP'];
                    if (isset($requestTransfer['data']->data) &&
                                ($requestTransfer['data']->data->transactionStatusCode == 'TXN' && $requestTransfer['data']->data->transactionReferenceId != '00'))
                            {

                                $bank_reference  = isset($requestTransfer['data']->data->transactionReferenceId) ? $requestTransfer['data']->data->transactionReferenceId : "";
                                DMTFundTransfer::updateRecord(
                                    ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderId],
                                    ['status' => 'processed', 'utr' => $bank_reference]
                                );

                                DMTFundTransfer::cashbackCredit($request['auth_data']['user_id'], $orderId);



                        } else if(isset($requestTransfer['data']
                        ->data->transactionStatusCode) && in_array($requestTransfer['data']
                        ->data->transactionStatusCode, $failedArray)) {


                            $statusCode = $requestTransfer['data']->data->transactionStatusCode;
                            DMTFundTransfer::fundRefunded($request['auth_data']['user_id'], $orderId, @$errorDesc, 'dmt_fund_refunded', @$statusCode);


                        } else if(isset($requestTransfer['data']
                                ->data->transactionStatusCode) && in_array($requestTransfer['data']
                                ->data->transactionStatusCode, ['ERR']) && ($requestTransfer['data']
                                ->data->transactionReferenceId == null || $requestTransfer['data']
                                ->data->transactionReferenceId == '00')) {

                                    $statusCode = $requestTransfer['data']->data->transactionStatusCode;
                                    DMTFundTransfer::fundRefunded($request['auth_data']['user_id'], $orderId, @$errorDesc, 'dmt_fund_refunded', @$statusCode);

                        }
                }
            }
        }

        $orders = DB::table('dmt_fund_transfers')
            ->select('dmt_fund_transfers.status')
            ->where('dmt_fund_transfers.client_ref_id', $clientRefId)
            ->orWhere('dmt_fund_transfers.order_ref_id', $clientRefId)
            ->where('dmt_fund_transfers.user_id', $userId)
            ->first();

        if (isset($orders)) {
            $order = DB::table('dmt_fund_transfers')
                ->select(self::columnSelectResponse($orders->status))
                ->where('dmt_fund_transfers.client_ref_id', $clientRefId)
                ->orWhere('dmt_fund_transfers.order_ref_id', $clientRefId)
                ->where('dmt_fund_transfers.user_id', $userId)
                ->first();
            if ($orders->status == 'processed') {
                return ResponseHelper::success('Record fetched successfully.', $order);
            } elseif ($orders->status == 'failed') {
                return ResponseHelper::failed('Record is failed.', $order);
            } elseif ($orders->status == 'processing' || $orders->status == 'queued' ) {
                return ResponseHelper::pending('Record is processing successfully.', $order);
            } else {
                return ResponseHelper::success('Record fetched successfully.', $order);
            }
        } else {
            return ResponseHelper::failed('No orders found.', []);
        }
    }


    /**
     * Method columnSelectResponse
     *
     * @param $ordersStatus $ordersStatus [explicite description]
     *
     * @return void
     */
    public static function columnSelectResponse($ordersStatus)
    {
        $selectingColumn = array( 'dmt_fund_transfers.client_ref_id as clientRefId', 'dmt_fund_transfers.order_ref_id as orderRefId', 'dmt_fund_transfers.outlet_id as outletId', 'dmt_fund_transfers.mobile as remitterMobile', 'merchant_code as merchantCode', 'dmt_fund_transfers.beni_id as beneficiaryId', 'dmt_fund_transfers.bank_account as bankAccount',  'dmt_fund_transfers.bank_ifsc as bankIfsc',  'dmt_fund_transfers.amount as amount', 'dmt_fund_transfers.status as status');
        if ($ordersStatus == 'processed')
        {
            array_push($selectingColumn, 'dmt_fund_transfers.utr as bankReference');
        } elseif ($ordersStatus == 'failed') {
            array_push($selectingColumn, 'dmt_fund_transfers.failed_message as failedMessage');
        } elseif ($ordersStatus == 'reversed') {
            array_push($selectingColumn, 'dmt_fund_transfers.utr as bankReference');
        } elseif ($ordersStatus == 'queued') {
            array_push($selectingColumn, 'dmt_fund_transfers.utr as bankReference');
        }
        array_push($selectingColumn, 'dmt_fund_transfers.created_at as createdAt');
        return $selectingColumn;
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
            $getProductId = CommonHelper::getProductId('dmt_' . $slug, 'dmt');
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

    /**
     * Method encAadhaar
     *
     * @ $aadhaar number [explicite description]
     *
     * @return void
     */
    public function encAadhaar($aadhaar)
    {

        $key = base64_decode(env('INSTANTPAY_ENC_KEY'));
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($aadhaar, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $encryptedData = base64_encode($iv . $ciphertext);
        return $encryptedData;
    }


    /**
     * Method responseFormat
     *
     * @param $reqType  [explicite description]
     * @param $resp  [explicite description]
     *
     * @return void
     */
    public static function responseFormat($reqType, $resp, $clientRefId = "")
    {

        $respData = [];
        if ($reqType == "fundTransfer") {
            $respData['orderRefId'] = $resp->externalRef;
            $respData['clientRefId'] = $clientRefId;
            $respData['amount'] = $resp->txnValue;
            $respData['utr'] = $resp->txnReferenceId;
            $respData['remitterMobile'] = $resp->remitterMobile;
            $respData['beneficiaryName'] = $resp->beneficiaryName;
            $respData['beneficiaryAccount'] = $resp->beneficiaryAccount;
            $respData['beneficiaryIFSC'] = $resp->beneficiaryIfsc;

        } else if ($reqType == "remitterVeriryOTP") {
            $respData['mobile'] = $resp->mobile;
            $respData['firstName'] = $resp->firstName;
            $respData['lastName'] = $resp->lastName;
            $respData['address'] = $resp->address;
            $respData['city'] = $resp->city;
            $respData['state'] = $resp->state;
            $respData['pincode'] = $resp->pincode;
            $respData['limitPerTransaction'] = $resp->limitPerTransaction;
            $respData['limitTotal'] = $resp->limitTotal;
            $respData['limitConsumed'] = $resp->limitConsumed;
            $respData['limitAvailable'] = $resp->limitAvailable;
            $respData['limitIncreaseOffer'] = $resp->limitIncreaseOffer;
            $respData['beneficiaries'] = $resp->beneficiaries;
        }

        return $respData;
    }


    /**
     * Method sendCallback
     *
     * @param $userId $userId [explicite description]
     * @param $orderRefId $orderRefId [explicite description]
     * @param $status $status [explicite description]
     *
     * @return void
     */
    public static function sendCallback ($userId, $orderRefId, $status)
    {
         //send callback
         $getWebhooks = Webhook::where('user_id', $userId)->first();
         if ($getWebhooks) {

            $order =  DB::table('dmt_fund_transfers')
                        ->select(self::columnSelectResponse($status))
                        ->where('dmt_fund_transfers.order_ref_id', $orderRefId)
                        ->where('dmt_fund_transfers.user_id', $userId)
                        ->first();

             $url = $getWebhooks['webhook_url'];
             $secret = $getWebhooks['secret'];
             if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
                 $headers = [$getWebhooks['header_key'] => $getWebhooks['header_value']];

                if ($status == 'processed') {
                    WebhookHelper::DMTWebhook( (array)$order, $url, $secret, $headers, 'dmt.transfer.success', '0x0200', 'Transaction Successful');
                } else if($status == 'failed') {
                    WebhookHelper::DMTWebhook((array)$order, $url, $secret, $headers, 'dmt.transfer.failed', '0x0202', 'Transaction Failed');
                } else if($status == 'reversed') {
                    WebhookHelper::DMTWebhook((array)$order, $url, $secret, $headers, 'dmt.transfer.reversed', '0x0207', 'Transaction Reversed');
                }

             } else {
                if ($status == 'processed') {
                    WebhookHelper::DMTWebhook((array)$order, $url, $secret, '', 'dmt.transfer.success', '0x0200', 'Transaction Successful');
                } else if($status == 'failed') {
                    WebhookHelper::DMTWebhook((array)$order, $url, $secret, '', 'dmt.transfer.failed', '0x0202', 'Transaction Failed');
                } else if($status == 'reversed') {
                    WebhookHelper::DMTWebhook((array)$order, $url, $secret, '', 'dmt.transfer.reversed', '0x0207', 'Transaction Reversed');
                }
             }
         }
         // end
    }

}
