<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\RazorPaySmartCollectHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\TransactionHelper;
use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\Validation;
use App\Services\DocVerify\DocService;
use App\Services\DocVerify\DocVerifyBO;
use App\Services\DocVerify\DocVerifyTechnoApi;
use App\Services\DocVerify\DocVerifyZoopApi;
use App\Services\OCRService;
use App\Services\OpenBank\OBApiService;
use App\Services\OpenBank\OpenBankBO;
use App\Validations\VerificationValidation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ValidationController extends Controller
{
    protected const DOCUMENT_URI = '/v3/tasks/sync/extract';
    protected const PENNY_DROP_URI = '/v3/tasks/async/verify_with_source';
    protected const TASK_URI = '/v3/tasks';

    private const BANK_GROUP_ID = 'XTL_BANK_';
    private const PAN_GROUP_ID = 'XTL_PAN_';
    private const UPI_GROUP_ID = 'XTL_UPI_';
    private const AADHAAR_GROUP_ID = 'XTL_ADR_';


    /**
     * Bank Account Validation
     */
    public function ifscValidation(Request $request)
    {
        try {

            $validation = new VerificationValidation();

            //validate service status
            $validations = $validation->serviceStatus($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::failed($validations['message']);
            }

            //validate request params
            $validations = $validation->ifsc($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::missing($validations['message']);
            }


            if ($validations['status'] === true) {

                $orderRefId = CommonHelper::getRandomString('vfc', false);
                $accountIfsc = strtoupper(trim($request->ifsc));

                $body2Store['task_id'] = $orderRefId;
                $body2Store['param1'] = $accountIfsc;

                $taxData = $this->getFeeAndTaxs($request['auth_data']['user_id'], 'ifsc');

                $orderInserted = Validation::create(
                    $request['auth_data']['user_id'],
                    $body2Store,
                    'ifsc',
                    $taxData
                );

                if ($orderInserted['status']) {
                    $processOrder = Validation::moveOrderToProcessingByOrderId(
                        $request['auth_data']['user_id'],
                        $orderRefId,
                        $taxData['fee'],
                        $taxData['tax'],
                        'verification_ifsc_debit'
                    );

                    if ($processOrder['status']) {

                        $bo = new DocVerifyBO();
                        $bo->http = 'GET';
                        $bo->param = [
                            'ifsc' => $accountIfsc
                        ];
                        $bo->userId = $request['auth_data']['user_id'];
                        $bo->slug = 'validation_ifsc';
                        $bo->clientRefId = $orderRefId;

                        //getting ifsc info
                        $ifscInfo = RazorPaySmartCollectHelper::bankInfoViaIfsc($bo);

                        $apiStatus = !empty($ifscInfo['status']) ? $ifscInfo['status'] : '';

                        if ($apiStatus === 'success') {
                            $clientResponse = [
                                'requestId' => $orderRefId,
                                'ifsc' => $accountIfsc,
                                "currentStatus" => "FOUND",
                                "response" => [
                                    // 'ifsc' => @$ifscInfo['data']->IFSC,
                                    'branch' => @$ifscInfo['data']->BRANCH,
                                    'address' => @$ifscInfo['data']->ADDRESS,
                                    //'district' => @$ifscInfo['data']->DISTRICT,
                                    'city' => @$ifscInfo['data']->CITY,
                                    'state' => @$ifscInfo['data']->STATE,
                                    'bankName' => @$ifscInfo['data']->BANK,
                                    'bankCode' => @$ifscInfo['data']->BANKCODE,
                                ]
                            ];

                            Validation::updateRecord(
                                ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                                ['status' => 'success']
                            );

                            return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                        } else if ($apiStatus === 'failed') {

                            $clientResponse = [
                                'requestId' => $orderRefId,
                                'ifsc' => $accountIfsc,
                                "currentStatus" => "NOT_FOUND"
                            ];

                            Validation::updateRecord(
                                ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                                ['status' => 'success']
                            );

                            return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                        } else if ($apiStatus === 'error') {

                            Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, 'Service down', 'verification_ifsc_refund');

                            return ResponseHelper::failed($ifscInfo['message']);
                        } else {

                            Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, 'Service down', 'verification_ifsc_refund');
                            return ResponseHelper::failed('Service down, Something went wrong');
                        }
                    } else {
                        Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, 'Service down', 'verification_ifsc_refund');

                        return ResponseHelper::failed($processOrder['message'], []);
                    }
                } else {
                    Validation::updateRecord(
                        ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                        ['status' => 'failed']
                    );

                    return ResponseHelper::failed($orderInserted['message']);
                }
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * Bank Account Validation
     */
    public function bankValidation(Request $request, OCRService $ocrService)
    {
        try {

            $validation = new VerificationValidation();

            //validate service status
            $validations = $validation->serviceStatus($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::failed($validations['message']);
            }

            //validate request params
            $validations = $validation->bank($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::missing($validations['message']);
            }


            $rootType = $this->getApiRoot('bank');


            if ($validations['status'] === true && $rootType === '1') {


                $groupId = self::BANK_GROUP_ID . date('Ymd');
                $orderRefId = CommonHelper::getRandomString('vba', false);

                $accountNumber = trim($request->accountNumber);
                $accountIfsc = strtoupper(trim($request->ifsc));

                // $data = $request->all();
                $body = [
                    "task_id" => $orderRefId,
                    "group_id" => $groupId,
                    "data" => [
                        "bank_account_no" => $accountNumber,
                        "bank_ifsc_code" => $accountIfsc,
                    ]
                ];

                $body2Store = $body;
                $body2Store['param1'] = $accountNumber;
                $body2Store['param2'] = $accountIfsc;

                $taxData = $this->getFeeAndTaxs($request['auth_data']['user_id'], 'bank');

                $orderInserted = Validation::create(
                    $request['auth_data']['user_id'],
                    $body2Store,
                    'bank',
                    $taxData
                );

                // print_r($orderInserted);

                if ($orderInserted['status']) {
                    $ocr = Validation::moveOrderToProcessingByOrderId(
                        $request['auth_data']['user_id'],
                        $orderRefId,
                        $taxData['fee'],
                        $taxData['tax'],
                        'verification_bank_debit'
                    );

                    // print_r($ocr);

                    if ($ocr['status']) {
                        $validation = DB::table('acc_validation_logs')
                            ->where([
                                'user_id' => $request['auth_data']['user_id'],
                                'account_no' => $accountNumber
                            ])
                            ->first();

                        if (!empty($validation)) {
                            $res = json_decode($validation->response, 1);

                            $response['response']['response'] = $res[0];
                            $clientResponse = [
                                'requestId' => $validation->ref_no
                            ];

                            Validation::updateRecord(
                                ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                                ['request_id' => $response['response']['response']['request_id']]
                            );
                            return ResponseHelper::success('Request send successfully.', $clientResponse);
                        } else {
                            $response = $ocrService->init(
                                $body,
                                self::PENNY_DROP_URI . '/validate_bank_account',
                                'verification_bank_debit',
                                $request['auth_data']['user_id'],
                                'yes',
                                'verification'
                            );
                        }


                        if (isset($response['response']['response']->request_id)) {
                            Validation::updateRecord(
                                ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                                ['status' => 'success', 'request_id' => $response['response']['response']->request_id]
                            );

                            // $responseTaskDetails = $this->getTask(
                            //     $response['response']['response']->request_id,
                            //     $request['auth_data']['user_id'],
                            //     $ocrService
                            // );

                            $clientResponse = [
                                'requestId' => $orderRefId
                            ];

                            return ResponseHelper::success('Record found successful.', $clientResponse);
                        } else {
                            Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'verification_bank_refund');
                            return ResponseHelper::failed('Bank account not found.', $response['response']['response']);
                        }
                    } else {
                        Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_bank_refund');
                        return ResponseHelper::failed($ocr['message']);
                    }
                } else {
                    return ResponseHelper::failed($orderInserted['message']);
                }
            } else if ($validations['status'] === true && $rootType === '2') {

                $groupId = self::BANK_GROUP_ID . date('Ymd');
                $orderRefId = CommonHelper::getRandomString('vba', false);

                $accountNumber = trim($request->accountNumber);
                $accountIfsc = strtoupper(trim($request->ifsc));

                // $body = [
                //     "bene_account_number" => $accountNumber,
                //     "ifsc_code" => $accountIfsc,
                //     "merchant_ref_id" => $orderRefId
                //     // "data" => [
                //     //     "bank_account_no" => $accountNumber,
                //     //     "bank_ifsc_code" => $accountIfsc,
                //     // ]
                // ];

                // $body2Store = $body;
                $body2Store["task_id"] = $orderRefId;
                $body2Store["group_id"] = $groupId;
                $body2Store['param1'] = $accountNumber;
                $body2Store['param2'] = $accountIfsc;
                $body2Store['rootType'] = $rootType;


                $taxData = $this->getFeeAndTaxs($request['auth_data']['user_id'], 'bank');

                $orderInserted = Validation::create(
                    $request['auth_data']['user_id'],
                    $body2Store,
                    'bank',
                    $taxData
                );

                if ($orderInserted['status']) {
                    $ocr = Validation::moveOrderToProcessingByOrderId(
                        $request['auth_data']['user_id'],
                        $orderRefId,
                        $taxData['fee'],
                        $taxData['tax'],
                        'verification_bank_debit'
                    );

                    if ($ocr['status']) {

                        Validation::updateRecord(
                            ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                            ['status' => 'pending', 'request_id' => '']
                        );

                        $clientResponse = [
                            'requestId' => $orderRefId
                        ];

                        return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                    } else {
                        Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_bank_refund');
                        return ResponseHelper::failed($ocr['message'], []);
                    }
                } else {
                    return ResponseHelper::failed($orderInserted['message'], []);
                }
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * PAN Validation
     */
    public function panValidation(Request $request, OCRService $ocrService)
    {
        try {

            $validation = new VerificationValidation();

            //validate service status
            $validations = $validation->serviceStatus($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::failed($validations['message']);
            }

            //validate request params
            $validations = $validation->pan($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::missing($validations['message']);
            }

            $rootType = $this->getApiRoot('pan');
            $panNumber = strtoupper(trim($request->pan));

            //check the DB, if same PAN success record exist within 30 days

            $previousPanRecord = DB::table('validations')
                ->select('id')
                ->where('status', 'success')
                ->where('type', 'pan')
                ->whereNotNull('response')
                // ->where('user_id', $request['auth_data']['user_id'])
                ->where('param_1', $panNumber)
                ->whereDate('created_at', '>=', date('Y-m-d', strtotime("-30 day", time())))
                ->first();

            if (!empty($previousPanRecord)) {

                // $groupId = $previousPanRecord->id;
                $orderRefId = CommonHelper::getRandomString('vpn', false);

                $body = [
                    "task_id" => $orderRefId,
                    // "group_id" => $groupId,
                    "data" => [
                        "id_number" => $panNumber
                    ]
                ];


                $body2Store = $body;
                $body2Store['param1'] = $panNumber;
                $body2Store['rootType'] = $rootType;

                $taxData = $this->getFeeAndTaxs($request['auth_data']['user_id'], 'pan');

                $orderInserted = Validation::create(
                    $request['auth_data']['user_id'],
                    $body2Store,
                    'pan',
                    $taxData
                );

                if ($orderInserted['status']) {
                    $ocr = Validation::moveOrderToProcessingByOrderId(
                        $request['auth_data']['user_id'],
                        $orderRefId,
                        $taxData['fee'],
                        $taxData['tax'],
                        'verification_pan_debit'
                    );

                    if ($ocr['status']) {

                        Validation::updateRecord(
                            ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                            [
                                'status' => 'pending',
                                'request_id' => '',
                                'previous_id' => $previousPanRecord->id
                            ]
                        );

                        $clientResponse = [
                            'requestId' => $orderRefId
                        ];

                        return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                    } else {
                        Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_pan_refund');
                        return ResponseHelper::failed($ocr['message'], []);
                    }
                } else {
                    return ResponseHelper::failed($orderInserted['message'], []);
                }
            } else if ($validations['status'] === true && $rootType === '1') {

                $groupId = self::PAN_GROUP_ID . date('Ymd');
                $orderRefId = CommonHelper::getRandomString('vpn', false);
                // $panNumber = strtoupper(trim($request->pan));

                $body = [
                    "task_id" => $orderRefId,
                    "group_id" => $groupId,
                    "data" => [
                        "id_number" => $panNumber
                    ]
                ];

                $body2Store = $body;
                $body2Store['param1'] = $panNumber;
                $body2Store['rootType'] = $rootType;

                $taxData = $this->getFeeAndTaxs($request['auth_data']['user_id'], 'pan');

                $orderInserted = Validation::create(
                    $request['auth_data']['user_id'],
                    $body2Store,
                    'pan',
                    $taxData
                );

                if ($orderInserted['status']) {
                    $ocr = Validation::moveOrderToProcessingByOrderId(
                        $request['auth_data']['user_id'],
                        $orderRefId,
                        $taxData['fee'],
                        $taxData['tax'],
                        'verification_pan_debit'
                    );

                    if ($ocr['status']) {

                        $response = $ocrService->init(
                            $body,
                            self::PENNY_DROP_URI . '/ind_pan',
                            'panValidation',
                            $request['auth_data']['user_id'],
                            'yes',
                            'verification'
                        );

                        if (isset($response['response']['response']->request_id)) {
                            Validation::updateRecord(
                                ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                                ['status' => 'success', 'request_id' => $response['response']['response']->request_id]
                            );

                            $clientResponse = [
                                'requestId' => $orderRefId
                            ];

                            return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                        } else {
                            Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'verification_pan_refund');
                            return ResponseHelper::failed('Record not fetched.', $response['response']['response']);
                        }
                    } else {
                        Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_pan_refund');
                        return ResponseHelper::failed($ocr['message'], []);
                    }
                } else {
                    return ResponseHelper::failed($orderInserted['message'], []);
                }
            } else if ($validations['status'] === true && $rootType === '2') {

                $groupId = self::PAN_GROUP_ID . date('Ymd');
                $orderRefId = CommonHelper::getRandomString('vpn', false);
                // $panNumber = strtoupper(trim($request->pan));

                $body = [
                    "task_id" => $orderRefId,
                    "group_id" => $groupId,
                    "data" => [
                        "id_number" => $panNumber
                    ]
                ];

                $body2Store = $body;
                $body2Store['param1'] = $panNumber;
                $body2Store['rootType'] = $rootType;

                $taxData = $this->getFeeAndTaxs($request['auth_data']['user_id'], 'pan');

                $orderInserted = Validation::create(
                    $request['auth_data']['user_id'],
                    $body2Store,
                    'pan',
                    $taxData
                );

                if ($orderInserted['status']) {
                    $ocr = Validation::moveOrderToProcessingByOrderId(
                        $request['auth_data']['user_id'],
                        $orderRefId,
                        $taxData['fee'],
                        $taxData['tax'],
                        'verification_pan_debit'
                    );

                    if ($ocr['status']) {

                        // $response = $ocrService->init(
                        //     $body,
                        //     self::PENNY_DROP_URI . '/ind_pan',
                        //     'verification_pan_debit',
                        //     $request['auth_data']['user_id'],
                        //     'yes',
                        //     'verification'
                        // );

                        // if (isset($response['response']['response']->request_id)) {
                        Validation::updateRecord(
                            ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                            ['status' => 'pending', 'request_id' => ''] //$response['response']['response']->request_id
                        );

                        $clientResponse = [
                            'requestId' => $orderRefId
                        ];

                        return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                    } else {
                        Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_pan_refund');
                        return ResponseHelper::failed($ocr['message'], []);
                    }
                } else {
                    return ResponseHelper::failed($orderInserted['message'], []);
                }
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }



    /**
     * AADHAAR Lite Validation
     */
    // public function aadhaarLiteValidation(Request $request, OCRService $ocrService)
    // {
    //     try {

    //         $validation = new VerificationValidation();

    //         //validate service status
    //         $validations = $validation->serviceStatus($request);

    //         if ($validations['status'] !== true) {
    //             return ResponseHelper::failed($validations['message']);
    //         }

    //         //validate request params
    //         $validations = $validation->aadhaar($request);

    //         if ($validations['status'] !== true) {
    //             return ResponseHelper::missing($validations['message']);
    //         }


    //         if ($validations['status'] === true) {

    //             $groupId = self::AADHAAR_GROUP_ID . date('Ymd');
    //             $orderRefId = CommonHelper::getRandomString('val', false);

    //             $aadhaarNumber = trim($request->aadhaarNumber);

    //             $body = [
    //                 "task_id" => $orderRefId,
    //                 "group_id" => $groupId,
    //                 "data" => [
    //                     "aadhaar_number" => $aadhaarNumber
    //                 ]
    //             ];

    //             $body2Store = $body;
    //             $body2Store['param1'] = $aadhaarNumber;

    //             $taxData = $this->getFeeAndTaxs($request['auth_data']['user_id'], 'aadhaar');

    //             $orderInserted = Validation::create(
    //                 $request['auth_data']['user_id'],
    //                 $body2Store,
    //                 'aadhaar_lite',
    //                 $taxData
    //             );

    //             if ($orderInserted['status']) {
    //                 $ocr = Validation::moveOrderToProcessingByOrderId(
    //                     $request['auth_data']['user_id'],
    //                     $orderRefId,
    //                     $taxData['fee'],
    //                     $taxData['tax'],
    //                     'verification_aadhaar_lite'
    //                 );

    //                 if ($ocr['status']) {

    //                     $response = $ocrService->init(
    //                         $body,
    //                         self::PENNY_DROP_URI . '/aadhaar_lite',
    //                         'aadhaarLiteValidation',
    //                         $request['auth_data']['user_id'],
    //                         'yes',
    //                         'verification'
    //                     );

    //                     if (isset($response['response']['response']->request_id)) {
    //                         Validation::updateRecord(
    //                             ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
    //                             ['status' => 'success', 'request_id' => $response['response']['response']->request_id]
    //                         );

    //                         $clientResponse = [
    //                             'requestId' => $orderRefId
    //                         ];

    //                         return ResponseHelper::success('Record fetched successfully.', $clientResponse);
    //                     } else {
    //                         Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'verification_aadhaar_refund');
    //                         return ResponseHelper::failed('Record not fetched.', $response['response']['response']);
    //                     }
    //                 } else {
    //                     Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_aadhaar_refund');
    //                     return ResponseHelper::failed($ocr['message']);
    //                 }
    //             } else {
    //                 return ResponseHelper::failed($orderInserted['message']);
    //             }
    //         }
    //     } catch (Exception $e) {
    //         return ResponseHelper::failed($e->getMessage());
    //     }
    // }


    /**
     * VPA Validation
     */
    public function vpaValidation(Request $request, OCRService $ocrService)
    {
        try {

            $validation = new VerificationValidation();

            //validate service status
            $validations = $validation->serviceStatus($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::failed($validations['message']);
            }

            //validate request params
            $validations = $validation->vpa($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::missing($validations['message']);
            }


            if ($validations['status'] === true) {

                $groupId = self::UPI_GROUP_ID . date('Ymd');
                $orderRefId = CommonHelper::getRandomString('vpa', false);

                $vpaAddress = trim($request->vpa);

                $body = [
                    "task_id" => $orderRefId,
                    "group_id" => $groupId,
                    "data" => [
                        "vpa" => $vpaAddress
                    ]
                ];

                $body2Store = $body;
                $body2Store['param1'] = $vpaAddress;

                $taxData = $this->getFeeAndTaxs($request['auth_data']['user_id'], 'vpa');

                $orderInserted = Validation::create(
                    $request['auth_data']['user_id'],
                    $body2Store,
                    'upi',
                    $taxData
                );

                if ($orderInserted['status']) {
                    $ocr = Validation::moveOrderToProcessingByOrderId(
                        $request['auth_data']['user_id'],
                        $orderRefId,
                        $taxData['fee'],
                        $taxData['tax'],
                        'verification_vpa_debit'
                    );


                    if ($ocr['status']) {

                        $response = $ocrService->init(
                            $body,
                            self::PENNY_DROP_URI . '/ind_vpa',
                            'vpaValidation',
                            $request['auth_data']['user_id'],
                            'yes',
                            'verification'
                        );

                        if (isset($response['response']['response']->request_id)) {
                            Validation::updateRecord(
                                ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                                ['status' => 'success', 'request_id' => $response['response']['response']->request_id]
                            );

                            $clientResponse = [
                                'requestId' => $orderRefId
                            ];

                            return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                            // return ResponseHelper::success('Record fetched successfully.', $response['response']['response']);clientResponse
                        } else {
                            Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'verification_vpa_refund');
                            return ResponseHelper::failed('Record not fetched.', $response['response']['response']);
                        }
                    } else {
                        Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_vpa_refund');
                        return ResponseHelper::failed($ocr['message']);
                    }
                } else {
                    return ResponseHelper::failed($orderInserted['message']);
                }
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * AADHAAR Validation
     */
    public function aadhaarValidation(Request $request)
    {
        try {

            $validation = new VerificationValidation();

            //validate service status
            $validations = $validation->serviceStatus($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::failed($validations['message']);
            }

            //validate request params
            $validations = $validation->aadhaar($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::missing($validations['message']);
            }


            if ($validations['status'] === true) {

                $userId = $request['auth_data']['user_id'];
                $orderRefId = CommonHelper::getRandomString('vad', false);
                $aadhaarNumber = trim($request->aadhaarNumber);

                //fee and tax calculation
                $taxData = $this->getFeeAndTaxs($userId, 'aadhaar');

                $rootType = $this->getApiRoot('aadhaar');

                $bo = new DocVerifyBO();
                $bo->param = [
                    'aadhaar' => $aadhaarNumber,
                    'clientRefId' => $orderRefId
                ];
                $bo->userId = $userId;
                $bo->slug = 'aadhaar';
                $bo->clientRefId = $orderRefId;
                $bo->uri = 'aadhaar';

                if ($rootType === '2') {

                    $docService = (new DocService(new DocVerifyZoopApi()))->getService();

                    $orderInserted = $docService->create(
                        $userId,
                        [
                            'root' => '2',
                            'param1' => $aadhaarNumber,
                            'order_ref_id' => $orderRefId,
                            'type' => $bo->slug
                        ],
                        $taxData
                    );

                    if ($orderInserted['status']) {
                        $ocr = Validation::moveOrderToProcessingByOrderId(
                            $userId,
                            $orderRefId,
                            $taxData['fee'],
                            $taxData['tax'],
                            'verification_aadhaar_debit'
                        );

                        if ($ocr['status']) {

                            $response = $docService->send($bo);

                            $httpStatusCode = $response['response']['statusCode'];

                            // Storage::append('response.txt', print_r($response, 1));

                            if ($httpStatusCode === 200) {

                                $requestId = @$response['response']['response']->request_id;
                                $taskId = @$response['response']['response']->task_id;
                                $groupId = @$response['response']['response']->group_id;
                                // $success = @$response['response']['response']->success;
                                // $responseCode = @$response['response']['response']->response_code;
                                $responseMessage = @$response['response']['response']->response_message;
                                $resData = @$response['response']['response']->result;

                                // if (!empty($resData->is_otp_sent)) {

                                // $responseId = @$resData->mahareferid;

                                $docService->update(
                                    ['user_id' => $userId, 'order_ref_id' => $orderRefId],
                                    [
                                        'status' => 'success',
                                        'request_id' => $requestId,
                                        'group_id' => $groupId,
                                        'client_ref_id' => $taskId,
                                    ]
                                );

                                $userResponse = [
                                    'requestId' => $orderRefId
                                ];

                                return ResponseHelper::success('OTP sent successfully.', $userResponse);
                                // }else{
                                //     return ResponseHelper::success('OTP is not sent successfully.');
                                // }
                            } else {

                                $responseMessage = @$response['response']['response']->response_message;

                                Validation::fundRefunded($userId, $orderRefId, $responseMessage, 'verification_aadhaar_refund');

                                return ResponseHelper::failed("Something went wrong, please try after sometime.");
                            }
                        } else {
                            // Validation::fundRefunded($userId, $orderRefId, @$ocr['message'], 'verification_aadhaar_refund');
                            return ResponseHelper::failed($ocr['message']);
                        }
                    } else {
                        return ResponseHelper::failed($orderInserted['message']);
                    }
                } else if ($rootType === '1') {

                    $docService = (new DocService(new DocVerifyTechnoApi()))->getService();

                    $orderInserted = $docService->create(
                        $userId,
                        [
                            'param1' => $aadhaarNumber,
                            'order_ref_id' => $orderRefId,
                            'type' => $bo->slug
                        ],
                        $taxData
                    );

                    if ($orderInserted['status']) {
                        $ocr = Validation::moveOrderToProcessingByOrderId(
                            $request['auth_data']['user_id'],
                            $orderRefId,
                            $taxData['fee'],
                            $taxData['tax'],
                            'verification_aadhaar_debit'
                        );

                        if ($ocr['status']) {

                            $response = $docService->send($bo);

                            $statusCode = isset($response['response']['response']->statuscode) ? $response['response']['response']->statuscode : '';
                            $message = isset($response['response']['response']->message) ? $response['response']['response']->message : '';
                            $resData = isset($response['response']['response']->Data[0]) ? $response['response']['response']->Data[0] : [];

                            if ($statusCode === '000' && isset($resData->mahareferid)) {

                                $responseId = @$resData->mahareferid;

                                $docService->update(
                                    ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                                    [
                                        'status' => 'success',
                                        'request_id' => $responseId
                                    ]
                                );

                                // Validation::updateRecord(
                                //     ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                                //     ['status' => 'success', 'request_id' => $response['response']['response']->mahareferid]
                                // );

                                $userResponse = [
                                    // 'clientRefId' => $clientRefId,
                                    'requestId' => $orderRefId
                                ];

                                // $response['response']['response']

                                return ResponseHelper::success('Record fetched successfully.', $userResponse);
                            } else {
                                Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'verification_aadhaar_refund');
                                // return ResponseHelper::failed('Record not fetched.', $response['response']['response']);
                                return ResponseHelper::failed($message);
                            }
                        } else {
                            Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_aadhaar_refund');
                            return ResponseHelper::failed($ocr['message']);
                        }
                    } else {
                        return ResponseHelper::failed($orderInserted['message']);
                    }
                }

                return ResponseHelper::failed('Invalid root for varification');
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }



    /**
     * AADHAAR Lite Validation
     */
    public function aadhaarValidationOtp(Request $request)
    {
        try {

            $validation = new VerificationValidation();

            //validate service status
            $validations = $validation->serviceStatus($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::failed($validations['message']);
            }

            //validate request params
            $validations = $validation->aadhaarOtp($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::missing($validations['message']);
            }


            if ($validations['status'] === true) {

                $userId = $request['auth_data']['user_id'];

                $service = DB::table('validations')
                    ->select('id', 'root_type', 'request_id', 'order_ref_id', 'client_ref_id')
                    ->where('user_id', $userId)
                    ->where('order_ref_id', trim($request->requestId))
                    ->first();

                if (empty($service)) {
                    return ResponseHelper::failed('Invalid clientRefId or requestId.');
                }

                $orderRefId = trim($request->requestId);
                $requestId = $service->request_id;
                $taskId = $service->client_ref_id;
                $otp = trim($request->otp);
                $rootType = $service->root_type;

                $bo = new DocVerifyBO();

                $bo->param = [
                    'requestId' => $requestId,
                    'clientRefId' => $orderRefId,
                    'otp' => $otp,
                    'taskId' => $taskId
                ];

                $bo->userId = $userId;
                $bo->slug = 'aadhaarOtp';
                $bo->clientRefId = $orderRefId;
                $bo->uri = 'aadhaarOtp';


                if ($rootType === '2') {
                    $docService = (new DocService(new DocVerifyZoopApi()))->getService();
                    $response = $docService->send($bo);

                    // Storage::append('response_otp.txt', print_r($response, 1));

                    $httpStatusCode = $response['response']['statusCode'];

                    if ($httpStatusCode === 200) {

                        $requestId = @$response['response']['response']->request_id;
                        $taskId = @$response['response']['response']->task_id;
                        $groupId = @$response['response']['response']->group_id;
                        // $success = @$response['response']['response']->success;
                        // $responseCode = @$response['response']['response']->response_code;
                        $responseMessage = @$response['response']['response']->response_message;
                        $resData = @$response['response']['response']->result;

                        $docService->update(
                            ['user_id' => $userId, 'order_ref_id' => $orderRefId],
                            [
                                'query_status' => 'success',
                                'updated_at' => date('Y-m-d H:i:s')
                            ]
                        );

                        if (!empty($resData)) {
                            $clientResponse = [
                                "requestId" => $orderRefId,
                                "currentStatus" =>   $this->getStatus('success'),
                                "response" => [
                                    "fullName" => $resData->user_full_name,
                                    "dob" => $resData->user_dob,
                                    "gender" => $resData->user_gender,
                                    "zip" => $resData->address_zip,
                                    "house" => $resData->user_address->house,
                                    "district" => $resData->user_address->dist,
                                    "vtc" => $resData->user_address->vtc,
                                    "loc" => $resData->user_address->loc,
                                    "subdistrict" => $resData->user_address->subdist,
                                    "country" => $resData->user_address->country,
                                    "po" => $resData->user_address->po,
                                    "state" => $resData->user_address->state,
                                    "street" => $resData->user_address->street,
                                    "profileImage" => @$resData->user_profile_image
                                ]
                            ];

                            return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                        }

                        return ResponseHelper::success('UUID: ' . $responseMessage);
                    } else {

                        $responseMessage = @$response['response']['response']->response_message;

                        return ResponseHelper::failed("UUID message: " . $responseMessage);
                    }
                } else if ($rootType === '1') {

                    $docService = (new DocService(new DocVerifyTechnoApi()))->getService();
                    $response = $docService->send($bo);

                    $statusCode = isset($response['response']['response']->statuscode) ? $response['response']['response']->statuscode : '';
                    $message = isset($response['response']['response']->message) ? $response['response']['response']->message : '';
                    $resData = isset($response['response']['response']->Data[0]) ? $response['response']['response']->Data[0] : [];

                    if ($statusCode === '000' && !empty($resData)) {

                        $responseId = @$resData->mahareferid;

                        $docService->update(
                            ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                            ['query_status' => 'success', 'request_id' => $responseId]
                        );

                        $clientResponse = [
                            "requestId" => $orderRefId,
                            "currentStatus" =>  isset($resData->uidaimessage) ? $this->getStatus($resData->uidaimessage) : $resData->uidaimessage,
                            "response" => [
                                "fullName" => $resData->full_name,
                                "dob" => $resData->dob,
                                "gender" => $resData->gender,
                                "zip" => $resData->zip,
                                "house" => $resData->house,
                                "district" => $resData->district,
                                "vtc" => $resData->vtc,
                                "loc" => $resData->loc,
                                "subdistrict" => $resData->subdistrict,
                                "country" => $resData->country,
                                "po" => $resData->po,
                                "state" => $resData->state,
                                "street" => $resData->street,
                                "profileImage" => $resData->profile_image,
                                // "mahareferid" => $resData->mahareferid,
                                // "client_refid" => $resData->client_refid,
                                // "uidaimessage" => $resData->uidaimessage,
                            ]
                        ];


                        return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                    } else {
                        // Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'verification_aadhaar_refund');
                        // return ResponseHelper::failed('Record not fetched.', $response['response']['response']);
                        return ResponseHelper::failed($message);
                    }
                }

                return ResponseHelper::failed('Invalid root for varification');
            }
            // else {
            //     // Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'verification_aadhaar_refund');
            //     return ResponseHelper::failed($ocr['message']);
            // }
            // } 
            // else {
            return ResponseHelper::failed('Failed!!!');
            // }
            // }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * Task Details
     */
    public function getTaskDetails(Request $request, $reqType)
    {

        $requestTypeArr = ['bank', 'pan', 'vpa', 'aadhaar-lite'];

        try {

            if (!in_array($reqType, $requestTypeArr)) {
                return ResponseHelper::failed('Invalid request.');
            }

            $validation = new VerificationValidation();

            //validate service status
            $validations = $validation->serviceStatus($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::failed($validations['message']);
            }

            //validate request params
            $validations = $validation->task($request);

            if ($validations['status'] !== true) {
                return ResponseHelper::missing($validations['message']);
            }

            $userId = $request['auth_data']['user_id'];
            $requestId = trim($request->requestId);
            $reqTypeStr = $reqType;

            switch ($reqType) {
                case 'bank':
                    $apiLogMethod = 'verification_bank_status';
                    break;
                case 'pan':
                    $apiLogMethod = 'verification_pan_status';
                    break;
                case 'aadhaar-lite':
                    $reqTypeStr = 'aadhaar_lite';
                    $apiLogMethod = 'verification_aadhaar_lite_status';
                    break;
                case 'vpa':
                    $reqTypeStr = 'upi';
                    $apiLogMethod = 'verification_vpa_status';
                    break;
            }

            //check task id avialable in database
            $taskData = DB::table('validations')
                ->select('*')
                ->where('user_id', $userId)
                ->where('type', $reqTypeStr)
                ->where('order_ref_id', $requestId)
                ->first();

            if (empty($taskData)) {
                return ResponseHelper::failed('Invalid requestId.');
            }


            $ocrService = new OCRService();

            $data['task_id'] = $taskData->order_ref_id;

            $apiLogMethod = 'getTaskDetails';

            //check response is send from previous DB record

            if (!empty($taskData->previous_id) && $reqType === 'pan') {

                //fetching response from previous record
                $previousRecord = DB::table('validations')
                    ->select('response')
                    ->where('id', $taskData->previous_id)
                    ->first();

                if (!empty($previousRecord->response)) {
                    //'user_id' => $userId,

                    $clientResponse = json_decode($previousRecord->response);

                    $clientResponse->requestId = $taskData->order_ref_id;

                    Validation::updateRecord(
                        ['id' => $taskData->id],
                        [
                            'query_status' => 'success',
                            'status' => 'success'
                        ]
                    );

                    return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                }
            }


            if ($taskData->root_type == '1') {

                $pennyDropCheck = DB::table('acc_validation_logs')
                    ->where(['ref_no' => @$requestId, 'user_id' => $userId])
                    ->first();
                if (isset($pennyDropCheck) && !empty($pennyDropCheck->response)) {
                    $res = (object) json_decode($pennyDropCheck->response);

                    $response['response']['response'] = @$res;
                    $response['response']['message'] = 'Record fetched successful.';
                } else {
                    $response = $ocrService->init(
                        $data,
                        self::TASK_URI . '?request_id=' . $taskData->request_id,
                        $apiLogMethod,
                        $userId,
                        'yes',
                        'verification',
                        'GET'
                    );
                }

                $apiMessage = !empty($response['response']['response']->message) ? $response['response']['response']->message : '';

                if (isset($response['response']['response']) && empty($apiMessage)) {
                    $status = "";
                    $taskType = '';
                    $apiResponse = null;

                    foreach ($response['response']['response'] as $val) {

                        $status = strtolower($val->status);
                        $taskType = @$val->type;
                        $apiResponse = $val;
                        // $apiResponse->requestId = $taskData->request_id;

                        if ($val->status == 'completed') {
                            Validation::updateRecord(
                                ['user_id' => $userId, 'request_id' => $val->request_id],
                                [
                                    'query_status' => 'success',
                                    'status' => 'success'
                                ]
                            );
                            if ($reqType == 'bank') {
                                $validation = DB::table('validations')
                                    ->where(['user_id' => $userId, 'request_id' => $val->request_id])
                                    ->first();
                                $penny = DB::table('acc_validation_logs')
                                    ->where(['ref_no' => @$requestId, 'user_id' => $userId])
                                    ->count();
                                if (!$penny) {
                                    DB::table('acc_validation_logs')->insert([
                                        'root_type' => 'idfy',
                                        'ref_no' => @$requestId,
                                        'user_id' => $userId,
                                        'account_no' => @$validation->param_1,
                                        'ifsc' => @$validation->param_2,
                                        'beneficiary_name' => @$val->result->name_at_bank,
                                        'status' => 'success',
                                        'response' => json_encode($response['response']['response']),
                                    ]);
                                }
                            }
                        }

                        if ($val->status == 'failed') {
                            Validation::updateRecord(
                                ['user_id' => $userId, 'request_id' => $val->request_id],
                                ['query_status' => 'failed', 'status' => 'failed']
                            );
                        }
                    }


                    $clientResponse = $this->generateResponse($taskType, $apiResponse, $taskData);

                    if ($status == 'completed') {
                        Validation::updateRecord(
                            ['user_id' => $userId, 'request_id' => $val->request_id],
                            [
                                'response' => json_encode($clientResponse)
                            ]
                        );
                    }

                    if ($status == 'failed') {
                        return ResponseHelper::failed('Verification failed.', $clientResponse);
                    } else if ($status == 'in_progress') {
                        return ResponseHelper::pending('Verification is in process.', $clientResponse);
                    } else {
                        return ResponseHelper::success('Verification successful.', $clientResponse);
                    }
                } else {
                    return ResponseHelper::failed('Failed. ' . $apiMessage);
                }
            } else if ($taskData->root_type === '2' && $taskData->type === 'pan') {

                if (!empty($taskData->status === 'failed')) {
                    return ResponseHelper::failed($taskData->failed_message);
                }

                if (!empty($taskData->response)) {
                    return ResponseHelper::success('Record fetched successfully.', json_decode($taskData->response));
                }

                $orderRefId = $requestId;
                // $requestId = $service->request_id;
                // $otp = trim($request->otp);

                $bo = new DocVerifyBO();

                $bo->param = [
                    'panno' => $taskData->param_1,
                    'clientRefId' => $orderRefId
                ];

                $bo->userId = $taskData->user_id;
                $bo->slug = 'pan';
                $bo->clientRefId = $orderRefId;
                $bo->uri = 'pan';

                $docService = (new DocService(new DocVerifyTechnoApi()))->getService();
                $response = $docService->send($bo);

                $statusCode = isset($response['response']['response']->statuscode) ? $response['response']['response']->statuscode : '';
                $message = isset($response['response']['response']->message) ? $response['response']['response']->message : '';
                $resData = isset($response['response']['response']->Data[0]) ? $response['response']['response']->Data[0] : [];

                if ($statusCode === '000' && !empty($resData)) {

                    $responseId = @$resData->mahareferid;

                    $apiResponse = [];

                    $clientResponse = $this->generateResponse('pan_kd', $resData, $taskData);


                    $docService->update(
                        ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                        [
                            'query_status' => 'success',
                            'status' => 'success',
                            'request_id' => $responseId,
                            'response' => json_encode($clientResponse)
                        ]
                    );


                    return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                } else {
                    $message = !empty($message) ? $message : 'Something went wrong, try after some time.';
                    Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, $message, 'verification_pan_refund');
                    // return ResponseHelper::failed('Record not fetched.', $response['response']['response']);
                    return ResponseHelper::failed($message);
                }
            } else if ($taskData->root_type === '2' && $taskData->type === 'bank') {

                if (!empty($taskData->status === 'failed')) {
                    return ResponseHelper::failed($taskData->failed_message);
                }

                if (!empty($taskData->response)) {
                    return ResponseHelper::success('Record fetched successfully.', json_decode($taskData->response));
                }

                $orderRefId = $requestId;

                $bo = new OpenBankBO();
                $bo->userId = $taskData->user_id;
                $bo->slug = 'bank';
                $bo->clientRefId = $orderRefId;
                $bo->uri = 'bank_verify';
                $bo->param = (object) [
                    "accountNumber" => $taskData->param_1,
                    "ifscCode" => $taskData->param_2,
                    "refId" => $orderRefId
                ];

                $apiService = new OBApiService();
                $response = $apiService->send($bo);

                $statusCode = isset($response['response']['response']->status) ? $response['response']['response']->status : '';
                $message = isset($response['response']['response']->message) ? $response['response']['response']->message : '';
                $resData = isset($response['response']['response']->data[0]) ? $response['response']['response']->data[0] : [];

                if ($statusCode === 200 && !empty($resData)) {

                    $responseId = @$resData->open_transaction_ref_id;

                    $apiResponse = [];

                    $clientResponse = $this->generateResponse('bank_ob', $resData, $taskData);

                    $apiService->update(
                        ['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId],
                        [
                            'query_status' => 'success',
                            'status' => 'success',
                            'request_id' => $responseId,
                            'response' => json_encode($clientResponse)
                        ]
                    );

                    return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                } else {
                    $message = !empty($message) ? $message : 'Something went wrong, try after some time.';
                    Validation::fundRefunded($request['auth_data']['user_id'], $orderRefId, $message, 'verification_bank_refund');

                    return ResponseHelper::failed($message);
                }
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * get fee and tax
     */
    private function getFeeAndTaxs($userId, $slug)
    {
        $resp['fee'] = 0;
        $resp['tax'] = 0;
        $resp['margin'] = "";
        $resp['product_id'] = "";
        $resp['message'] = "init";
        try {
            $getProductId = CommonHelper::getProductId($slug, VALIDATE_SERVICE_SLUG);
            $productId = isset($getProductId->product_id) ? $getProductId->product_id : "";
            $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, 0, $userId);
            $resp['fee'] = isset($getFeesAndTaxes['fee']) ? $getFeesAndTaxes['fee'] : 0;
            $resp['tax'] = isset($getFeesAndTaxes['tax']) ? $getFeesAndTaxes['tax'] : 0;
            $resp['margin'] = isset($getFeesAndTaxes['margin']) ? $getFeesAndTaxes['margin'] : "";
            $resp['product_id'] = isset($productId) ? $productId : "";
            $resp['message'] = "success";
        } catch (Exception  $e) {
            $resp['fee'] = 0;
            $resp['tax'] = 0;
            $resp['margin'] = "";
            $resp['message'] = "no record found. " . $e->getMessage();
        }

        return $resp;
    }


    /**
     * Generate Client Response
     */
    private function generateResponse($type, $params, $tableData = null)
    {
        $response = [];

        switch ($type) {
            case 'validate_bank_account':

                if ($params->status === 'completed') {
                    $response = [
                        'requestId' => $tableData->order_ref_id,
                        "currentStatus" => isset($params->result->status) ? $this->getStatus($params->result->status) : $params->status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $response['response'] = [
                            "accountNumber" => $params->result->bank_account_number,
                            "ifscCode" => $params->result->ifsc_code,
                            "fullName" => $params->result->name_at_bank,
                        ];
                    }
                } else if ($params->status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => @$params->status,
                    ];
                }

                break;

            case 'bank_ob':

                if ($params->verification_status === 'success') {
                    $response = [
                        'requestId' => $tableData->order_ref_id,
                        "currentStatus" => isset($params->verification_status) ? $this->getStatus($params->verification_status) : $params->verification_status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $response['response'] = [
                            "accountNumber" => $params->bene_account_number,
                            "ifscCode" => $params->ifsc_code,
                            "fullName" => $params->recepient_name,
                        ];
                    }
                } else if ($params->verification_status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => isset($params->verification_status) ? $this->getStatus($params->verification_status) : $params->verification_status,
                    ];
                }

                break;

            case 'ind_pan':

                if ($params->status === 'completed') {
                    $response = [
                        'requestId' =>  $tableData->order_ref_id,
                        "currentStatus" => isset($params->result->source_output->status) ? $this->getStatus($params->result->source_output->status) : $params->status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $arrResp = self::getFirstAndLastName($params->result->source_output->first_name, $params->result->source_output->last_name);
                        $response['response'] = [
                            "pan" => $params->result->source_output->id_number,
                            "type" => (new ValidationHelper())->getPanType($params->result->source_output->id_number),
                            "firstName" => $arrResp['fname'],
                            "lastName" => $arrResp['lname'],
                            "middleName" => !empty($params->result->source_output->middle_name) ? $params->result->source_output->middle_name : '',
                            "fullName" => $params->result->source_output->name_on_card
                        ];
                    }
                } else if ($params->status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => @$params->status,
                    ];
                }

                break;

            case 'pan_kd':

                // if ($params->status === 'completed') {
                $response = [
                    'requestId' =>  $tableData->order_ref_id,
                    "currentStatus" => !empty($params->full_name) ? 'FOUND' : 'NOT_FOUND',
                ];

                if ($response['currentStatus'] === 'FOUND') {

                    $helper = new ValidationHelper();
                    $nameParts = $helper->breakNameString($params->full_name);
                    $arrResp = self::getFirstAndLastName($nameParts['firstName'], $nameParts['lastName']);
                    $response['response'] = [
                        "pan" => @$params->pan_number,
                        "type" => $helper->getPanType($params->pan_number),
                        "firstName" => $arrResp['fname'],
                        "lastName" => $arrResp['lname'],
                        "middleName" => $nameParts['middleName'],
                        "fullName" => @$params->full_name
                    ];
                }
                // } else if ($params->status === 'failed') {
                //     $response = [
                //         'requestId' => @$tableData->order_ref_id,
                //         'currentStatus' => @$params->status,
                //     ];
                // }

                break;

            case 'ind_vpa':

                if ($params->status === 'completed') {
                    $response = [
                        'requestId' =>  $tableData->order_ref_id,
                        "currentStatus" => isset($params->result->status) ? $this->getStatus($params->result->status) : $params->status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $response['response'] = [
                            "vpa" => $params->result->vpa,
                            "fullName" => $params->result->name_at_bank
                        ];
                    }
                } else if ($params->status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => @$params->status,
                    ];
                }

                break;


            case 'aadhaar_lite':

                if ($params->status === 'completed') {
                    $response = [
                        'requestId' =>  $tableData->order_ref_id,
                        "currentStatus" => isset($params->result->status) ? $this->getStatus($params->result->status) : $params->status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $response['response'] = [
                            "gender" => $params->result->source_output->gender,
                            "state" => $params->result->source_output->state
                        ];
                    }
                } else if ($params->status === 'in_progress') {

                    $response = [
                        'requestId' =>  $tableData->order_ref_id,
                        "currentStatus" => strtoupper($params->status),
                    ];
                } else if ($params->status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => @$params->status,
                    ];
                }

                break;
        }

        return $response;
    }


    private function getStatus($status)
    {
        $status = strtolower($status);

        $return = $status;

        switch ($status) {
            case 'id_found':
            case 'success':
                $return = 'FOUND';
                break;

            case 'id_not_found':
            case 'failed':
                $return = 'NOT_FOUND';
                break;
        }

        return $return;
    }


    /**
     * Get API root from DB
     */
    private function getApiRoot($service)
    {
        $root = '1';

        switch ($service) {
            case 'aadhaar':
                $globalConfig = DB::table('global_config')
                    ->select('attribute_3')
                    ->where('slug', 'verification_api_root')
                    ->first();

                if (!empty($globalConfig)) {
                    $root = $globalConfig->attribute_3;
                }

                break;

            case 'bank':
                $globalConfig = DB::table('global_config')
                    ->select('attribute_2')
                    ->where('slug', 'verification_api_root')
                    ->first();

                if (!empty($globalConfig)) {
                    $root = $globalConfig->attribute_2;
                }

                break;

            case 'pan':
                $globalConfig = DB::table('global_config')
                    ->select('attribute_1')
                    ->where('slug', 'verification_api_root')
                    ->first();

                if (!empty($globalConfig)) {
                    $root = $globalConfig->attribute_1;
                }

                break;
        }

        return $root;
    }


    /**
     * Task Details
     */
    // private function getTask($taskId, $userId, OCRService $ocrService)
    // {
    //     try {
    //         $data['task_id'] = $taskId;
    //         $response = $ocrService->init(
    //             $data,
    //             self::TASK_URI . '?request_id=' . $taskId,
    //             'getTaskDetails',
    //             $userId,
    //             'yes',
    //             'validate',
    //             'GET'
    //         );

    //         if (isset($response['response']['response'])) {
    //             $status = "";
    //             foreach ($response['response']['response'] as $val) {
    //                 $status = $val->status;
    //                 if ($val->status == 'completed') {
    //                     Validation::updateRecord(
    //                         ['user_id' => $userId, 'request_id' => $val->request_id],
    //                         ['status' => 'success']
    //                     );
    //                 }

    //                 if ($val->status == 'failed') {
    //                     Validation::updateRecord(
    //                         ['user_id' => $userId, 'request_id' => $val->request_id],
    //                         ['query_status' => 'failed']
    //                     );
    //                 }
    //             }

    //             if ($status == 'failed') {
    //                 return ['status' => false, 'response' => $response];
    //             } else {
    //                 return ['status' => true, 'response' => $response['response']['response']];
    //             }
    //         } else {
    //             return ['status' => false, 'response' => $response];
    //         }
    //     } catch (Exception $e) {
    //         throw new Exception($e->getMessage());
    //     }
    // }

    /**
     * Method getFirstAndLastName
     *
     * @param $firstName  [explicite description]
     * @param $lastName  [explicite description]
     *
     * @return void
     */
    public static function getFirstAndLastName($firstName, $lastName)
    {
        $resp['fname'] = "";
        $resp['lname'] = "";
        if (isset($firstName) && !empty($firstName)) {
            $resp['fname'] = $firstName;
            $resp['lname'] = $lastName;
        } else {
            $arr = explode(' ', $lastName);
            if (count($arr) > 0) {
                $resp['fname'] = @$arr[0];
                $resp['lname'] = @$arr[1];
            } else {
                $resp['fname'] = $lastName;
                $resp['lname'] = "";
            }
        }
        return $resp;
    }
}
