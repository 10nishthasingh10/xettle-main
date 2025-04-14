<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\FidyPayUpiHelper;
use App\Helpers\IBLUpiHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrimaryFundCredit;
use App\Models\UPIMerchant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Validations\UPIValidation;

class UpiStackController extends Controller
{


    /**
     * Add merchant
     */
    public function addMerchant(Request $request)
    {
        try {

            $validation = new UPIValidation($request);
            $validator = $validation->merchant();

            if ($validator->fails()) {
                $resp['code']       = "0x0100";
                $resp['status']     = $this::ERROR_STATUS;
                $resp['message']    = json_decode(json_encode($validator->errors()), true);
                return response()->json($resp);
            }


            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }

            $panNumber = strtoupper(trim($request->panNo));

            //check email and mobile number is already registered
            $checkPan = DB::table('upi_merchants')
                ->select('id', 'merchant_virtual_address')
                ->where('pan_no', $panNumber)
                ->where('user_id', $userId)
                ->whereIn('root_type', ['ibl', 'fpay'])
                ->first();

            if (!empty($checkPan)) {
                return ResponseHelper::failed('PAN number already used.', ['vpa' => $checkPan->merchant_virtual_address]);
            }

            $rootType = $request->root;

            switch ($rootType) {
                case 'indus':

                    $iblHelper = new IBLUpiHelper();
                    $merchantTxnRefId = CommonHelper::getRandomString();

                    if (strlen($merchantTxnRefId) > 20) {
                        $merchantTxnRefId = substr($merchantTxnRefId, 0, 20);
                    }

                    $mcc = '6012';
                    $params = [
                        "pgMerchantId" => $iblHelper->getMerchantId(),
                        "mebussname" => trim($request->merchantBusinessName),
                        "legalStrName" => trim($request->merchantBusinessName),
                        "merVirtualAdd" => strtolower(trim($request->merchantVirtualAddress)) . 'xettle@indus',
                        "awlmcc" => $request->mcc ? trim($request->mcc) : $mcc,
                        "strCntMobile" => '9999999999', //$request->mobile,
                        "requestUrl1" => "https://app.xettle.io/api/callbacks/ibl",
                        "requestUrl2" => "https://app.xettle.io/api/callbacks/ibl",
                        "merchantType" => "AGGMER",
                        "integrationType" => "WEBAPI",
                        "settleType" => 'NET', //$request->settleType,
                        "panNo" => $panNumber,
                        "extMID" => $merchantTxnRefId,
                        // "extTID" => $request->extTID,
                        // "accNo" => $request->accNo,
                        // "meEmailID" => $request->contactEmail,
                        "gstin" => $request->gstin,
                        "gstConsentFlag" => 'Y', //$request->gstConsentFlag
                    ];

                    $requestType = 'merchant';

                    $modal = 'ibl';
                    $reqType = 'addSubMerchant';
                    $dec_response = $iblHelper->convertRequestResponse($params, $userId, $requestType, $modal, $reqType);


                    if (($dec_response['statuscode'] == '000')) {
                        if ($dec_response['text']['statusDesc'] === "SUCCESS") {
                            $merchant = new UPIMerchant;
                            $merchant->root_type = 'ibl';
                            $merchant->user_id = $userId;
                            $merchant->merchant_business_name = $dec_response['text']['mebussname'];
                            $merchant->merchant_virtual_address = $dec_response['text']['merVirtualAdd'];
                            $merchant->request_url = $request->requestUrl;
                            $merchant->pan_no = $panNumber;
                            $merchant->contact_email = strtolower($request->contactEmail);
                            $merchant->gstn = strtoupper($request->gstin);
                            $merchant->merchant_business_type = 'AGGMER';
                            $merchant->per_day_txn_count = $request->perDayTxnCount ? $request->perDayTxnCount : '';
                            $merchant->per_day_txn_lmt = $request->perDayTxnLmt ? $request->perDayTxnLmt : '';
                            $merchant->per_day_txn_amt = $request->perDayTxnAmt ? $request->perDayTxnAmt : '';
                            $merchant->mobile = $request->mobile ? $request->mobile : '';
                            $merchant->address = $request->address ? $request->address : '';
                            $merchant->state = $request->state ? $request->state : '';
                            $merchant->city = $request->city ? $request->city : '';
                            $merchant->pin_code = $request->pinCode ? $request->pinCode : '';
                            $merchant->sub_merchant_id = isset($dec_response['text']['pgMerchantID']) ? $dec_response['text']['pgMerchantID'] : '';
                            $merchant->merchant_txn_ref_id = $merchantTxnRefId;
                            $merchant->mcc = $request->mcc ? $request->mcc : $mcc;
                            $merchant->request_id = isset($dec_response['text']['merchantKey']) ? $dec_response['text']['merchantKey'] : ''; //isset($dec_response['text']['requestId']) ? $dec_response['text']['requestId'] : '';
                            $merchant->crt_date = isset($dec_response['text']['crtDate']) ? $dec_response['text']['crtDate'] : '';

                            $merchant->save();

                            $data = [
                                //'loginAccess' => $response['loginaccess'],
                                'subMerchantId' => isset($dec_response['text']['pgMerchantID']) ? $dec_response['text']['pgMerchantID'] : '',
                                'merchantBusinessName' => $dec_response['text']['mebussname'],
                                'merchantVirtualAddress' => $dec_response['text']['merVirtualAdd'],
                                'merchantKey' => isset($dec_response['text']['merchantKey']) ? $dec_response['text']['merchantKey'] : '',
                                'crtDate' => isset($dec_response['text']['crtDate']) ? $dec_response['text']['crtDate'] : '',
                                //'action' => $dec_response['text']['action']
                            ];

                            $code = '0x0200';
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
                        } else {
                            $code = '0x0201';
                            $this->message = $dec_response['text']['statusDesc'];
                            $status = $this::FAILED_STATUS;
                        }
                    }

                    $resp['code']       = $code;
                    $resp['message']    = $this->message;
                    $resp['status']     = $status;
                    if (isset($data)) {
                        $resp['data']   = $data;
                    }
                    return response()->json($resp);

                    break;

                case "manual":

                    //manual
                    $merchantTxnRefId = CommonHelper::getRandomString();
                    $mcc = '7299';
                    if (empty($request->merchantGenre)) {
                        $merchantGenre = 'OFFLINE';
                    } else {
                        $merchantGenre = strtoupper($request->merchantGenre);
                    }
                    if (isset($request->auth_data['user_id'])) {
                        $userId = $request->auth_data['user_id'];
                    } else {
                        $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
                    }
                    $merchant = new UPIMerchant();
                    $merchant->root_type = 'fpay';
                    $merchant->user_id = $userId;
                    $merchant->merchant_business_name = $request->merchantBusinessName;
                    $merchant->merchant_virtual_address = strtolower($request->merchantVirtualAddress) . 'xettle@yesbank';
                    $merchant->request_url = $request->requestUrl;
                    $merchant->pan_no = $panNumber;
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
                    $merchant->is_active = 0;
                    $merchant->pin_code = $request->pinCode;
                    $merchant->sub_merchant_id = $request->subMerchantId;
                    $merchant->merchant_txn_ref_id = $merchantTxnRefId;
                    $merchant->mcc = $mcc;
                    $merchant->request_id = $request->requestId;

                    if ($merchant->save()) {
                        $resp['message']    =  "Merchant added successfully.";
                        $resp['status']     = $this::SUCCESS_STATUS;
                    } else {
                        $resp['message']    = "Merchant not added.";
                        $resp['status']     = $this::FAILED_STATUS;
                    }
                    return response()->json($resp);
                    break;
                default:
                    //default yesbank

                    $merchantTxnRefId = CommonHelper::getRandomString();
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
                        'panNo' => $panNumber,
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
                        'mcc' => $request->mcc ? $request->mcc : $mcc,
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

                    $fidyPayHelper = new FidyPayUpiHelper();
                    $response = $fidyPayHelper->upiCaller($params, $requestType, $userId, $modal, $reqType);

                    if (isset($response['code'])) {
                        if ($response['code'] === "0x0200") {
                            $merchant = new UPIMerchant();
                            $merchant->root_type = 'fpay';
                            $merchant->user_id = $userId;
                            $merchant->merchant_business_name = $response['merchantBussinessName'];
                            $merchant->merchant_virtual_address = $response['merchantVirtualAddress'];
                            $merchant->request_url = $request->requestUrl;
                            $merchant->pan_no = $panNumber;
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
                    $resp['code']       = $code;
                    $resp['message']    = $this->message;
                    $resp['status']     = $status;
                    if (isset($data)) {
                        $resp['data']   = $data;
                    }
                    return response()->json($resp);
                    break;
            }
        } catch (Exception $e) {
            $resp['code']       = "0x0202";
            $resp['message']    = "Error: " . $e->getMessage();
            $resp['status']     = $this::FAILED_STATUS;
            return response()->json($resp);
        }
    }


    /**
     * Veyify VPAs
     */
    public function verify(Request $request, $vpa)
    {
        try {
            if (empty($vpa)) {
                $resp['code']       = "0x0100";
                $resp['message']    = "VPA field is required.";
                $resp['status']     = $this::ERROR_STATUS;
                return response()->json($resp);
            }


            $iblHelper = new IBLUpiHelper();

            $params = [
                'requestInfo' => [
                    'pgMerchantId' => $iblHelper->getMerchantId(),
                    'pspRefNo' =>  CommonHelper::getRandomString()
                ],
                'payeeType' => [
                    'virtualAddress' => strtolower($vpa)
                ],
                "vAReqType" => "T"
            ];

            $requestType = 'verify';
            $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            $modal = 'ibl';
            $reqType = 'verifyVPA';

            $dec_response = $iblHelper->convertRequestResponse($params, $userId, $requestType, $modal, $reqType);


            if ($dec_response['statuscode'] === "000") {
                $data = [
                    'name' => isset($dec_response['text']['payeeType']['name']) ? $dec_response['text']['payeeType']['name'] : '',
                    'bankTxnId' => $dec_response['text']['requestInfo']['pspRefNo'],
                    'virtualAddress' => $dec_response['text']['payeeType']['virtualAddress'],
                    'status' => isset($dec_response['text']['status']) ? $dec_response['text']['status'] : '',
                ];

                if (isset($dec_response['text']['statusDesc'])) {
                    $description = $dec_response['text']['statusDesc'];
                }

                $code = '0x0200';
                $this->message = $description;
                $status = $this::SUCCESS_STATUS;


                //insert request record
                $dataInsert = [
                    'user_id' => $userId,
                    'root_type' => 'ibl',
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
            } elseif ($dec_response['code'] === "0x0201") {
                $code = $dec_response['code'];
                $this->message = 'Something went wrong please try again';
                $status = $this::FAILED_STATUS;
            } else {
                $code = '0x0201';
                $this->message = $dec_response['text']['statusDesc'];
                $status = $this::FAILED_STATUS;
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


            if (isset($data)) {
                $resp['data']   = $data;
            }

            return response()->json($resp);
        } catch (Exception $e) {
            $resp['code']       = "0x0202";
            $resp['message']    = "Error: " . $e->getMessage();
            $resp['status']     = $this::FAILED_STATUS;
            return response()->json($resp);
        }
    }



    /**
     * transaction status check function
     */
    public function status(Request $request, $txnId, $root = null)
    {
        dd("sdsdsd");

        try {
            if (empty($txnId)) {
                $resp['code'] = "0x0100";
                $resp['message'] = "UPI txnId required.";
                $resp['status'] = $this::ERROR_STATUS;

                return response()->json($resp);
            }

            switch ($root) {
                case 'indus':

                    $utrInfo = DB::table('upi_callbacks')
                        // ->leftJoin('upi_merchants', 'upi_callbacks.user_id', '=', 'upi_merchants.user_id')
                        ->leftJoin('upi_merchants', function ($join) {
                            $join->on('upi_callbacks.user_id', '=', 'upi_merchants.user_id')
                                ->on('upi_callbacks.payee_vpa', '=', 'upi_merchants.merchant_virtual_address');
                        })
                        ->selectRaw("upi_callbacks.user_id, upi_callbacks.customer_ref_id, upi_merchants.sub_merchant_id, upi_callbacks.merchant_txn_ref_id")
                        ->where('upi_callbacks.customer_ref_id', $txnId)
                        ->where('upi_callbacks.root_type', 'ibl')
                        ->where('upi_merchants.root_type', 'ibl')
                        ->first();

                    if (empty($utrInfo)) {

                        $resp['code']       = '0x0201';
                        $resp['message']    = "No transaction found";
                        $resp['status']     = $this::FAILED_STATUS;

                        return response()->json($resp);
                    }

                    // dd($utrInfo->sub_merchant_id);

                    $iblHelper = new IBLUpiHelper();

                    $params = [
                        "requestInfo" => [
                            "pspRefNo" => $utrInfo->merchant_txn_ref_id, //isset($request->pspRefNo) ? $request->pspRefNo : '',
                            "pgMerchantId" => $utrInfo->sub_merchant_id, //$iblHelper->getMerchantId()
                        ],
                        "custRefNo" => $txnId,
                        // "npciTranId" => $request->npci_txn_id
                    ];

                    $requestType = 'transaction_status';
                    if (isset($request->auth_data['user_id'])) {
                        $userId = $request->auth_data['user_id'];
                    } else {
                        $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
                    }
                    $modal = 'ibl';
                    $reqType = 'transaction_status';
                    $dec_response = $iblHelper->convertRequestResponse($params, $userId, $requestType, $modal, $reqType);

                    if (($dec_response['statuscode'] == '000')) {

                        $txnStatus = $dec_response['text']['apiResp']['status'];

                        switch ($txnStatus) {
                            case 'S':
                            case 'P':
                            case 'F':

                                $data = [
                                    "approvalNo" => $dec_response['text']['apiResp']['approvalNumber'],
                                    "status" => $iblHelper->getTxnStatus($dec_response['text']['apiResp']['status']),
                                    "statusNote" => $dec_response['text']['apiResp']['statusDesc'],
                                    "amount" => $dec_response['text']['apiResp']['amount'],
                                    "npciTxnId" => $dec_response['text']['apiResp']['npciTransId'],
                                    // "merchantTxnRefId" => $dec_response['text']['apiResp'][''],
                                    "customerRefId" => $dec_response['text']['apiResp']['custRefNo'],
                                    "txnId" => $dec_response['text']['apiResp']['pspRefNo'],
                                    // "originalOrderId" => $dec_response['text']['apiResp'][''],
                                    // "bankTrxnId" => $dec_response['text']['apiResp'][''],
                                    "payerVPA" => $dec_response['text']['apiResp']['payerVPA'],
                                    "payeeVPA" => $dec_response['text']['apiResp']['payeeVPA'],
                                    // "payerAccNo" => $dec_response['text']['apiResp'][''],
                                    // "payerIFSCCode" => $dec_response['text']['apiResp'][''],
                                    "txnAuthDate" => $dec_response['text']['apiResp']['txnAuthDate'],
                                ];

                                $code = '0x0200';
                                $this->message = $dec_response['text']['apiResp']['statusDesc'];
                                $status = $this::SUCCESS_STATUS;

                                break;

                            case 'T':
                            case 'V':

                                $data = [
                                    "status" => $iblHelper->getTxnStatus($dec_response['text']['apiResp']['status']),
                                    "statusNote" => $dec_response['text']['apiResp']['statusDesc'],
                                    "customerRefId" => $dec_response['text']['apiResp']['custRefNo'],
                                    "txnAuthDate" => $dec_response['text']['apiResp']['txnAuthDate'],
                                ];


                                $code = '0x0201';
                                $this->message = $dec_response['text']['apiResp']['statusDesc'];
                                $status = $this::FAILED_STATUS;

                                break;

                            default:
                                $data = [
                                    "status" => 'failed',
                                    "statusNote" => 'Something went wrong.',
                                    "customerRefId" => $txnId,
                                    "txnAuthDate" => date('Y-m-d H:i:s'),
                                ];

                                $code = '0x0202';
                                $this->message = 'Something went wrong.';
                                $status = $this::FAILED_STATUS;
                                break;
                        }
                    } else {
                        $code = '0x0201';
                        $this->message = "SOMETHING WENT WRONG";
                        $status = $this::FAILED_STATUS;
                    }

                    $resp['code']       = $code;
                    $resp['message']    = $this->message;
                    $resp['status']     = $status;
                    if (isset($data)) {
                        $resp['data']   = $data;
                    }
                    return response()->json($resp);

                    break;

                default:
                    $params = [
                        'txnId' => $txnId
                    ];

                    $fidyPayHelper = new FidyPayUpiHelper();

                    $requestType = 'status';
                    $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
                    $modal = 'fidypay';
                    $reqType = 'statusCheck';
                    $response = $fidyPayHelper->upiCaller($params, $requestType, $userId, $modal, $reqType);

                    if (isset($response['code']) && $response['code'] != "0x0203") {
                        if ($response['code'] === "0x0200") {
                            $data = [
                                'approvalNo' => $response['approvalNo'],
                                'txnNote' => $response['trxnNote'],
                                'amount' => $response['amount'],
                                'npciTxnId' => $response['nPCITrxnId'],
                                'merchantTxnRefId' => $response['merchantTrxnRefId'],
                                'customerRefId' => $response['customerRefId'],
                                'txnId' => $response['trxn_id'],
                                'bankTrxnId' => $response['bankTrxnId'],
                                'payerVPA' => $response['payorVPA'],
                                'payerName' => $response['payerName'],
                                'payerAccNo' => $response['payerAccNo'],
                                'payerIFSCCode' => $response['payerIFSCCode'],
                                'txnAuthDate' => $response['trxnAuthDate'],
                            ];
                            $code = $response['code'];
                            $this->message = $response['description'];
                            $status = $this::SUCCESS_STATUS;
                        } elseif ($response['code'] === "0x0201") {
                            $code = $response['code'];
                            $this->message = 'Something went wrong please try again';
                            $status = $this::FAILED_STATUS;
                        } else {
                            if (isset($response['originalOrderId'])) {
                                $data['originalOrderId'] = $response['originalOrderId'];
                            }
                            $code = $response['code'];
                            $this->message = str_replace("trxn_id ", "UPI txnId", $response['description']);
                            $status = $this::FAILED_STATUS;
                        }
                    }

                    $resp['code']       = $code;
                    $resp['message']    = $this->message;
                    $resp['status']     = $status;

                    if (isset($data)) {
                        $resp['data']   = $data;
                    }

                    return response()->json($resp);
                    break;
            }
        } catch (Exception $e) {
            $resp['code']       = "0x0202";
            $resp['message']    = "Error: " . $e->getMessage();
            $resp['status']     = $this::FAILED_STATUS;
            return response()->json($resp);
        }
    }


    /**
     * transaction status check function
     */
    public function statusForIbl(Request $request)
    {

        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'txnId' => "required|max:30",
                    'vpa' => "required|min:5|max:30|regex:/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/i"
                ],
                [
                    'vpa.regex' => 'Invalid VPA address'
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Missing Parameters', $message);
            }

            $txnId = trim($request->txnId);
            $vpa  = strtolower(trim($request->vpa));

            $vpaInfo = DB::table('upi_merchants')
                ->select('id', 'user_id', 'sub_merchant_id')
                ->where('root_type', 'ibl')
                ->where('merchant_virtual_address', $vpa)
                ->first();

            if (empty($vpaInfo)) {
                return ResponseHelper::failed('VPA not found');
            }

            $params = [
                "requestInfo" => [
                    "pspRefNo" => 'PSPREF' . uniqid(), //$utrInfo->merchant_txn_ref_id, //isset($request->pspRefNo) ? $request->pspRefNo : '',
                    "pgMerchantId" => $vpaInfo->sub_merchant_id, //$iblHelper->getMerchantId()
                ],
                "custRefNo" => $txnId,
                // "npciTranId" => $request->npci_txn_id
            ];


            $iblHelper = new IBLUpiHelper();

            $requestType = 'transaction_status';
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }

            $modal = 'ibl';
            $reqType = 'transaction_status';
            $dec_response = $iblHelper->convertRequestResponse($params, $userId, $requestType, $modal, $reqType);

            if (($dec_response['statuscode'] == '000')) {

                $txnStatus = $dec_response['text']['apiResp']['status'];

                switch ($txnStatus) {
                    case 'S':
                    case 'P':
                    case 'F':

                        $data = [
                            "approvalNo" => $dec_response['text']['apiResp']['approvalNumber'],
                            "status" => $iblHelper->getTxnStatus($dec_response['text']['apiResp']['status']),
                            "statusNote" => $dec_response['text']['apiResp']['statusDesc'],
                            "amount" => $dec_response['text']['apiResp']['amount'],
                            "npciTxnId" => $dec_response['text']['apiResp']['npciTransId'],
                            // "merchantTxnRefId" => $dec_response['text']['apiResp'][''],
                            "customerRefId" => $dec_response['text']['apiResp']['custRefNo'],
                            // "txnId" => $dec_response['text']['apiResp']['pspRefNo'],
                            // "originalOrderId" => $dec_response['text']['apiResp'][''],
                            // "bankTrxnId" => $dec_response['text']['apiResp'][''],
                            "payerVPA" => $dec_response['text']['apiResp']['payerVPA'],
                            "payeeVPA" => $dec_response['text']['apiResp']['payeeVPA'],
                            // "payerAccNo" => $dec_response['text']['apiResp'][''],
                            // "payerIFSCCode" => $dec_response['text']['apiResp'][''],
                            "txnAuthDate" => $dec_response['text']['apiResp']['txnAuthDate'],
                        ];

                        $code = '0x0200';
                        $this->message = $dec_response['text']['apiResp']['statusDesc'];
                        $status = $this::SUCCESS_STATUS;

                        break;

                    case 'T':
                    case 'V':

                        $data = [
                            "status" => $iblHelper->getTxnStatus($dec_response['text']['apiResp']['status']),
                            "statusNote" => $dec_response['text']['apiResp']['statusDesc'],
                            "customerRefId" => $dec_response['text']['apiResp']['custRefNo'],
                            "txnAuthDate" => $dec_response['text']['apiResp']['txnAuthDate'],
                        ];


                        $code = '0x0201';
                        $this->message = $dec_response['text']['apiResp']['statusDesc'];
                        $status = $this::FAILED_STATUS;

                        break;

                    default:
                        $data = [
                            "status" => 'failed',
                            "statusNote" => 'Something went wrong.',
                            "customerRefId" => $txnId,
                            "txnAuthDate" => date('Y-m-d H:i:s'),
                        ];

                        $code = '0x0202';
                        $this->message = 'Something went wrong.';
                        $status = $this::FAILED_STATUS;
                        break;
                }
            } else {
                $code = '0x0201';
                $this->message = "SOMETHING WENT WRONG";
                $status = $this::FAILED_STATUS;
            }

            $resp['code']       = $code;
            $resp['message']    = $this->message;
            $resp['status']     = $status;
            if (isset($data)) {
                $resp['data']   = $data;
            }
            
            return response()->json($resp);
        } catch (Exception $e) {
            $resp['code']       = "0x0202";
            $resp['message']    = "Error: " . $e->getMessage();
            $resp['status']     = $this::FAILED_STATUS;
            return response()->json($resp);
        }
    }



    /**
     * Generate Dynamic QR Code 
     */
    public function generateDynamicQrCode(Request $request)
    {
        //dd("sassa");
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
                $referenceId = '&tr=' . CommonHelper::getRandomString('USTD', false);
            } else {
                $referenceId = '&tr=' . rawurlencode($request->referenceId);
            }

            if (empty($request->note)) {
                $note = '&tn=' . rawurlencode('via XETTLE');
            } else {
                $note = '&tn=' . rawurlencode($request->note);
            }

            $qrCode = "upi://pay?pa={$merchantInfo->merchant_virtual_address}&pn={$merchant_business_name}&am={$request->amount}{$referenceId}{$note}";

            $qrCode = "data:image/png;base64," . base64_encode(QrCode::margin(1)->format('png')->size(320)->generate($qrCode));

            return ResponseHelper::success("QR Code generated successfully", ['qrCode' => $qrCode]);
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }



    /**
     * Generate Static QR Code 
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
}
