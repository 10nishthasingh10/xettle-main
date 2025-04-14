<?php

namespace App\Http\Controllers\Clients\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Services\OCRService;
use Validations\OCRValidation as Validations;
use Exception;
use App\Helpers\ResponseHelper as Response;
use App\Helpers\TransactionHelper;
use App\Models\Validation;


class OCRController extends Controller
{

    protected const DOCUMENT_URI = '/v3/tasks/sync/extract';
    protected const PENNY_DROP_URI = '/v3/tasks/async/verify_with_source';
    protected const TASK_URI = '/v3/tasks';


    /**
     * PAN OCR
     */
    public function getPanDetails(Request $request, OCRService $ocrService)
    {
        try {

            $validations = Validations::init($request, 'pan');

            if ($validations['status'] == true) {

                $orderRefId = CommonHelper::getRandomString('ocp', false);
                $data = $request->all();

                $taxData = self::getFeeAndTaxs($request['auth_data']['user_id'], 'ocr_pan');
                $body = [
                    "task_id" => @$data['clientRefId'],
                    "group_id" => $orderRefId,
                    "data" => [
                        "document1" => $request->pan
                    ]
                ];

                // $orderInserted = Ocr::create($request['auth_data']['user_id'], $body, 'pan', $taxData);
                $orderInserted = Validation::ocrCreate($request['auth_data']['user_id'], $body, 'ocr_pan', $taxData);

                if ($orderInserted['status']) {

                    // $ocr = Ocr::moveOrderToProcessingByOrderId($request['auth_data']['user_id'], $orderRefId, $taxData['fee'], $taxData['tax'], 'ocr_pan_debit');
                    $ocr = Validation::ocrMoveOrderToProcessingByOrderId($request['auth_data']['user_id'], $orderRefId, $taxData['fee'], $taxData['tax'], 'ocr_pan_debit');

                    if ($ocr['status']) {

                        $response = $ocrService->init($body, self::DOCUMENT_URI . '/ind_pan', 'getPanDetails', $request['auth_data']['user_id'], 'yes');

                        if (isset($response['response']['response']->status) && $response['response']['response']->status == 'completed') {
                            // Ocr::updateRecord(['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId], ['status' => 'success',  'request_id' => $response['response']['response']->request_id]);
                            Validation::ocrUpdateRecord(['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId], ['status' => 'success',  'request_id' => $response['response']['response']->request_id]);

                            $generatedResponse = $this->generateSuccessResponse($response['response']['response'], 'pan');

                            return Response::success('Record extract successfully.', $generatedResponse);
                        } else {
                            // Ocr::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message,  'ocr_pan_refund');
                            Validation::ocrFundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message,  'ocr_pan_refund');

                            $generatedResponse = $this->generateFailedResponse($response['response']['response'], 'pan');

                            return Response::failed($generatedResponse);
                        }
                    } else {
                        // Ocr::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'ocr_pan_refund');
                        Validation::ocrFundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'ocr_pan_refund');
                        return ResponseHelper::failed($ocr['message'], []);
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
     * AADHAAR OCR
     */
    public function getAadhaarDetails(Request $request, OCRService $ocrService)
    {
        try {
            $validations = Validations::init($request, 'aadhaar');
            if ($validations['status'] == true) {

                $orderRefId = CommonHelper::getRandomString('oca', false);
                $data = $request->all();
                $taxData = self::getFeeAndTaxs($request['auth_data']['user_id'], 'ocr_aadhaar');

                $body = [
                    "task_id" => @$data['clientRefId'],
                    "group_id" => $orderRefId,
                    "data" => [
                        "document1" => $request->aadhaarFront,
                        "document2" => $request->aadhaarBack,
                        "consent" => "yes"
                    ]
                ];

                // $orderInserted = Ocr::create($request['auth_data']['user_id'], $body, 'aadhaar', $taxData);
                $orderInserted = Validation::ocrCreate($request['auth_data']['user_id'], $body, 'ocr_aadhaar', $taxData);

                if ($orderInserted['status']) {

                    // $ocr = Ocr::moveOrderToProcessingByOrderId($request['auth_data']['user_id'], $orderRefId, $taxData['fee'], $taxData['tax'], 'ocr_aadhaar_debit');
                    $ocr = Validation::ocrMoveOrderToProcessingByOrderId($request['auth_data']['user_id'], $orderRefId, $taxData['fee'], $taxData['tax'], 'ocr_aadhaar_debit');

                    if ($ocr['status']) {

                        $response = $ocrService->init($body, self::DOCUMENT_URI . '/ind_aadhaar', 'getAadhaarDetails', $request['auth_data']['user_id'], 'yes');
                        if (isset($response['response']['response']->status) && $response['response']['response']->status == 'completed') {

                            // Ocr::updateRecord(['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId], ['status' => 'success', 'request_id' => $response['response']['response']->request_id]);
                            Validation::ocrUpdateRecord(['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId], ['status' => 'success', 'request_id' => $response['response']['response']->request_id]);

                            $generatedResponse = $this->generateSuccessResponse($response['response']['response'], 'aadhaar');

                            return Response::success('Record extract successfully.', $generatedResponse);
                        } else {
                            // Ocr::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'ocr_aadhaar_refund');
                            Validation::ocrFundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'ocr_aadhaar_refund');

                            $generatedResponse = $this->generateFailedResponse($response['response']['response'], 'aadhaar');

                            return Response::failed($generatedResponse);
                        }
                    } else {
                        // Ocr::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'ocr_aadhaar_refund');
                        Validation::ocrFundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'ocr_aadhaar_refund');
                        return ResponseHelper::failed($ocr['message'], []);
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
     * Cheque OCR
     */
    public function getChequeDetails(Request $request, OCRService $ocrService)
    {
        try {
            $validations = Validations::init($request, 'cheque');

            if ($validations['status'] == true) {

                $orderRefId = CommonHelper::getRandomString('che', false);
                $data = $request->all();
                $taxData = self::getFeeAndTaxs($request['auth_data']['user_id'], 'ocr_cheque');

                $body = [
                    "task_id" => @$data['clientRefId'],
                    "group_id" => $orderRefId,
                    "data" => [
                        "document1" => $request->cheque,
                        "micr_details" => "true"
                    ]
                ];

                // $orderInserted = Ocr::create($request['auth_data']['user_id'], $body, 'cheque', $taxData);
                $orderInserted = Validation::ocrCreate($request['auth_data']['user_id'], $body, 'ocr_cheque', $taxData);

                if ($orderInserted['status']) {

                    // $ocr = Ocr::moveOrderToProcessingByOrderId($request['auth_data']['user_id'], $orderRefId, $taxData['fee'], $taxData['tax'], 'ocr_cheque_debit');
                    $ocr = Validation::ocrMoveOrderToProcessingByOrderId($request['auth_data']['user_id'], $orderRefId, $taxData['fee'], $taxData['tax'], 'ocr_cheque_debit');

                    if ($ocr['status']) {

                        $response = $ocrService->init($body, self::DOCUMENT_URI . '/ind_cheque', 'getChequeDetails', $request['auth_data']['user_id'], 'yes');
                        if (isset($response['response']['response']->status) && $response['response']['response']->status == 'completed') {

                            // Ocr::updateRecord(['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId], ['status' => 'success', 'request_id' => $response['response']['response']->request_id]);
                            Validation::ocrUpdateRecord(['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId], ['status' => 'success', 'request_id' => $response['response']['response']->request_id]);

                            $generatedResponse = $this->generateSuccessResponse($response['response']['response'], 'cheque');

                            return Response::success('Record extract successfully.', $generatedResponse);
                        } else {
                            // Ocr::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'ocr_cheque_refund');
                            Validation::ocrFundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'ocr_cheque_refund');

                            $generatedResponse = $this->generateFailedResponse($response['response']['response'], 'cheque');

                            return Response::failed($generatedResponse);
                        }
                    } else {
                        // Ocr::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'ocr_cheque_refund');
                        Validation::ocrFundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'ocr_cheque_refund');
                        return ResponseHelper::failed($ocr['message'], []);
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


    /*
    public function getPassportDetails(Request $request, OCRService $ocrService)
    {
        try {
            $validations = Validations::init($request, 'passport');
            if ($validations['status'] == true) {
                $orderRefId = CommonHelper::getRandomString('ocp', false);
                $data = $request->all();
                $body = [
                    "task_id" => @$data['clientRefId'],
                    "group_id" => $orderRefId,
                    "data" => [
                        "document1" => $request->passport
                    ]
                ];
                $taxData = self::getFeeAndTaxs($request['auth_data']['user_id'], 'ocr_passport');

                $orderInserted = Ocr::create($request['auth_data']['user_id'], $body, 'passport', $taxData);
                if ($orderInserted['status']) {
                    $ocr = Ocr::moveOrderToProcessingByOrderId($request['auth_data']['user_id'], $orderRefId, $taxData['fee'], $taxData['tax'], 'ocr_passport_debit');
                    if ($ocr['status']) {

                        $response = $ocrService->init($body, self::DOCUMENT_URI . '/ind_passport', 'getPassportDetails', $request['auth_data']['user_id'], 'yes');

                        if (isset($response['response']['response']->status) && $response['response']['response']->status == 'completed') {
                            Ocr::updateRecord(['user_id' => $request['auth_data']['user_id'], 'order_ref_id' => $orderRefId], ['status' => 'success', 'request_id' => $response['response']['response']->request_id]);
                            return Response::success('Record extract successfully.', $response['response']['response']);
                        } else {
                            Ocr::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$response['response']['response']->message, 'ocr_passport_refund');
                            return Response::failed('Record not fetched.', $response['response']['response']);
                        }
                    } else {
                        Ocr::fundRefunded($request['auth_data']['user_id'], $orderRefId, @$ocr['message'], 'ocr_passport_refund');
                        return ResponseHelper::failed($ocr['message'], []);
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
    //*/



    /**
     * Generate Success Resposne
     */
    private function generateSuccessResponse($responseData, $responseType)
    {
        $res = [];

        switch ($responseType) {
            case 'cheque':
                $res = [
                    "ocrRefId" => @$responseData->group_id,
                    "ocrStatus" => @$responseData->status,
                    "clientRefId" => @$responseData->task_id,
                    "extractionOutput" => [
                        "accountHolderName" => @$responseData->result->extraction_output->account_name,
                        "accountNo" => @$responseData->result->extraction_output->account_no,
                        "bankName" => @$responseData->result->extraction_output->bank_name,
                        "ifsc" => @$responseData->result->extraction_output->ifsc_code,
                        "bankAddress" => @$responseData->result->extraction_output->bank_address,
                        "dateOfIssue" => @$responseData->result->extraction_output->date_of_issue,
                        // "is_scanned" => @$responseData->result->extraction_output->is_scanned,
                        "micrChequeNumber" => @$responseData->result->extraction_output->micr_cheque_number,
                        "micrCode" => @$responseData->result->extraction_output->micr_code,
                    ],
                ];
                break;


            case 'pan':
                $res = [
                    "ocrRefId" => @$responseData->group_id,
                    "ocrStatus" => @$responseData->status,
                    "clientRefId" => @$responseData->task_id,
                    "extractionOutput" => [
                        "age" => @$responseData->result->extraction_output->age,
                        "dateOfBirth" => @$responseData->result->extraction_output->date_of_birth,
                        "dateOfIssue" => @$responseData->result->extraction_output->date_of_issue,
                        "fatherName" => @$responseData->result->extraction_output->fathers_name,
                        "idNumber" => @$responseData->result->extraction_output->id_number,
                        "minor" => @$responseData->result->extraction_output->minor,
                        "nameOnCard" => @$responseData->result->extraction_output->name_on_card,
                        "panType" => @$responseData->result->extraction_output->pan_type,
                    ],
                ];
                break;


            case 'aadhaar':
                $res = [
                    "ocrRefId" => @$responseData->group_id,
                    "ocrStatus" => @$responseData->status,
                    "clientRefId" => @$responseData->task_id,
                    "extractionOutput" => [
                        "address" => @$responseData->result->extraction_output->address,
                        "dateOfBirth" => @$responseData->result->extraction_output->date_of_birth,
                        "district" => @$responseData->result->extraction_output->district,
                        "fatherName" => @$responseData->result->extraction_output->fathers_name,
                        "gender" => @$responseData->result->extraction_output->gender,
                        "houseNumber" => @$responseData->result->extraction_output->house_number,
                        "idNumber" => @$responseData->result->extraction_output->id_number,
                        "nameOnCard" => @$responseData->result->extraction_output->name_on_card,
                        "pincode" => @$responseData->result->extraction_output->pincode,
                        "state" => @$responseData->result->extraction_output->state,
                        "streetAddress" => @$responseData->result->extraction_output->street_address,
                        "yearOfBirth" => @$responseData->result->extraction_output->year_of_birth,
                    ],
                    "qrOutput" => [
                        "address" => @$responseData->result->qr_output->address,
                        "dateOfBirth" => @$responseData->result->qr_output->date_of_birth,
                        "district" => @$responseData->result->qr_output->district,
                        "gender" => @$responseData->result->qr_output->gender,
                        "houseNumber" => @$responseData->result->qr_output->house_number,
                        "idNumber" => @$responseData->result->qr_output->id_number,
                        "nameOnCard" => @$responseData->result->qr_output->name_on_card,
                        "pincode" => @$responseData->result->qr_output->pincode,
                        "state" => @$responseData->result->qr_output->state,
                        "streetAddress" => @$responseData->result->qr_output->street_address,
                        "yearOfBirth" => @$responseData->result->qr_output->year_of_birth,
                    ]
                ];
                break;
        }


        return $res;
    }


    /**
     * Generate Failed Resposne
     */
    private function generateFailedResponse($responseData, $responseType)
    {
        return isset($responseData->message) ? $responseData->message : @$responseData->error;

        // $res = [];

        // switch ($responseType) {
        //     case 'cheque':
        //         $res = [
        //             "ocrRefId" => @$responseData->group_id,
        //             "ocrStatus" => @$responseData->status,
        //             "clientRefId" => @$responseData->task_id,
        //             "message" => @$responseData->message,
        //             "error" => @$responseData->error,
        //         ];
        //         break;


        //     case 'pan':
        //         $res = [
        //             "ocrRefId" => @$responseData->group_id,
        //             "ocrStatus" => @$responseData->status,
        //             "clientRefId" => @$responseData->task_id,
        //             "message" => @$responseData->message,
        //             "error" => @$responseData->error,
        //         ];
        //         break;


        //     case 'driving':
        //         $res = [
        //             "ocrRefId" => @$responseData->group_id,
        //             "ocrStatus" => @$responseData->status,
        //             "clientRefId" => @$responseData->task_id,
        //             "message" => @$responseData->message,
        //             "error" => @$responseData->error,
        //         ];
        //         break;


        //     case 'aadhaar':
        //         $res = [
        //             "ocrRefId" => @$responseData->group_id,
        //             "ocrStatus" => @$responseData->status,
        //             "clientRefId" => @$responseData->task_id,
        //             "message" => @$responseData->message,
        //             "error" => @$responseData->error,
        //         ];
        //         break;
        // }


        // return $res;
    }


    public static function getFeeAndTaxs($userId, $slug)
    {
        $resp['fee'] = 0;
        $resp['tax'] = 0;
        $resp['margin'] = "";
        $resp['product_id'] = "";
        $resp['message'] = "init";
        try {
            //code...
            $getProductId = CommonHelper::getProductId($slug, 'verification');
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
