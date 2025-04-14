<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Validations\IBLUPIValidation as Validations;
use App\Helpers\CommonHelper;
use App\Models\UPIMerchant;
use App\Models\UPICollect;
class IBLUPIController extends Controller
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
        $this->baseUrl = env('IBL_BASE_URL');
        $this->url = env('IBL_URL');
        $this->key = base64_decode(env('IBL_CLIENT_ID'));
        $this->secret = base64_decode(env('IBL_CLIENT_SECRET'));
        $this->decr_key = base64_decode(env('IBL_DECR_KEY'));
        $this->header = ["Content-Type: application/json", "X-IBM-Client-Id: ".$this->key, "X-IBM-Client-Secret: ".$this->secret];
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

        if($validator->fails()) {
            $resp['code']       = "0x0100";
            $resp['message']    = json_decode(json_encode($validator->errors()), true);
            $resp['status']     = $this::ERROR_STATUS;
            return response()->json($resp);
        }
        $merchantTxnRefId = $this->getTxnId();
        $invoice_no = $this->getInvoiceId();
        $params = [
                    "requestInfo"=> [ 
                    "pgMerchantId"=>$request->pgMerchantId, 
                    "pspRefNo"=>$merchantTxnRefId],
                    "amount"=>$request->amount,
                    "transactionNote"=> $request->transactionNote,
                    "addInfo"=> [ 
                    "addInfo9"=>"", 
                    "addInfo10"=>""],
                    "upiTransRefNo"=>"0", 
                    "payerType"=> [
                    "virtualAddress"=> $request->virtualAddress, 
                    "isMerchant"=> "false", 
                    "showMerchant"=> "false", 
                    "defVPAStatus"=> "false"],
                    "expiryTime"=>120,
                    
                                         
                     ];

        $requestType = 'collect';
        $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        $modal = 'ibl';
        $reqType = 'collect';
        
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);
        //print_r($dec_response);exit;
        if(($dec_response['statuscode']=='000')) {
                if($dec_response['text']['apiResp']['status'] === "S") {
                /* Insert Record In UPI Collect */
                $upiCollect = new UPICollect;
                $upiCollect->user_id = $userId;
                $upiCollect->txn_note = isset($request->transactionNote) ? $request->transactionNote : " ";
                $upiCollect->amount = isset($request->amount) ? $request->amount : '';
                $upiCollect->resp_code = isset($dec_response['statuscode']) ? $dec_response['statuscode'] : " ";
                $upiCollect->description = isset($dec_response['text']['apiResp']['statusDesc']) ? $dec_response['text']['apiResp']['statusDesc'] : " ";
                $upiCollect->payee_vpa = isset($dec_response['text']['apiResp']['payeeVPA']) ? $dec_response['text']['apiResp']['payeeVPA'] : " ";
                $upiCollect->customer_ref_id = isset($dec_response['text']['apiResp']['custRefNo']) ? $dec_response['text']['apiResp']['custRefNo'] : " ";
                $upiCollect->merchant_txn_ref_id = isset($dec_response['text']['apiResp']['pspRefNo']) ? $dec_response['text']['apiResp']['pspRefNo'] : " ";
                $upiCollect->txn_id = isset($response['trxn_id']) ? $response['trxn_id'] : " ";
                $upiCollect->original_order_id = isset($response['originalOrderId']) ? $response['originalOrderId'] : " ";
                $upiCollect->bank_txn_id = isset($response['bankTrxnId']) ? $response['bankTrxnId'] : " ";
                $upiCollect->payer_vpa = isset($dec_response['text']['apiResp']['payerVPA']) ? $dec_response['text']['apiResp']['payerVPA'] : " ";
                $upiCollect->upi_txn_id = isset($dec_response['text']['apiResp']['upiTransRefNo']) ? $dec_response['text']['apiResp']['upiTransRefNo'] : " ";
                $upiCollect->npci_txn_id = isset($dec_response['text']['apiResp']['npciTransId'])?$dec_response['text']['apiResp']['npciTransId']:"";
                $upiCollect->status = "pending";
                //$upiCollect->save();
                 /* End */
                $data = [
                    'amount' => $dec_response['text']['apiResp']['amount'],
                    //'txnNote' => $response['trxnNote'],
                    'payeeVPA' => $dec_response['text']['apiResp']['payeeVPA'],
                    'customerRefId' => $dec_response['text']['apiResp']['custRefNo'],
                    'merchantTxnRefId' => $dec_response['text']['apiResp']['pspRefNo'],
                    //'txnId' => $response['trxn_id'],
                    //'originalOrderId' => $response['originalOrderId'],
                    //'bankTxnId' => $response['bankTrxnId'],
                    'payorVPA' => $dec_response['text']['apiResp']['payerVPA'],
                    'UPITxnId' => $dec_response['text']['apiResp']['upiTransRefNo'],
                    'npci_txn_id' => isset($dec_response['text']['apiResp']['npciTransId'])?$dec_response['text']['apiResp']['npciTransId']:""
                ];
                $code = '0x0200';
                $this->message = $dec_response['text']['apiResp']['statusDesc'];
                $status = $this::SUCCESS_STATUS;
            } 
            // elseif($response['code'] === "0x0203") {
            //     if(isset($response['merchantTrxnRefId'])) {
            //         $data['merchantTxnRefId'] = $response['merchantTrxnRefId'];
            //     }
            //     if(isset($response['trxn_id'])) {
            //         $data['txnId'] = $response['trxn_id'];
            //     }
            //     $code = $response['code'];
            //     $this->message = $response['description'];
            //     $status = $this::FAILED_STATUS;
            // } elseif($response['code'] === "0x0201") {
            //     $code = $response['code'];
            //     $this->message = 'Something went wrong please try again';
            //     $status = $this::FAILED_STATUS;
            // }
             else {
                if(isset($dec_response['text']['apiResp']['pspRefNo'])) {
                    $data['merchantTxnRefId'] = $dec_response['text']['apiResp']['pspRefNo'];
                }
                if(isset($response['trxn_id'])) {
                    $data['txnId'] = $response['trxn_id'];
                }
                $code = '0x0201';
                $this->message = $dec_response['text']['apiResp']['statusDesc'];
                $status = $this::FAILED_STATUS;
            }
        }

        $resp['code']       = $code;
        $resp['message']    = $this->message;
        $resp['status']     = $status;
        if(isset($data)) {
            $resp['data']   = $data;
        }

        return response()->json($resp);
    }

    /**
     * Create Sub Merchant function
     *
     * @param Request $request
     * @return void
     */
    public function addSubMerchant(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->merchant();
        if($validator->fails()) {
            $resp['code']       = "0x0100";
            $resp['message']    = json_decode(json_encode($validator->errors()), true);
            $resp['status']     = $this::ERROR_STATUS;
            return response()->json($resp);
        }
        $merchantTxnRefId = $this->getTxnId();
        $mcc = '6012';
        $params = [
            "pgMerchantId" => "INDB000001530781",
            "mebussname"=> $request->mebussname,
            "legalStrName"=> $request->legalStrName,
            "merVirtualAdd"=>$request->merVirtualAdd,
            "awlmcc"=>$mcc,
            "strCntMobile"=>$request->strCntMobile,
            "requestUrl1"=>"https://app.xettle.io/api/callbacks/ibl",
            "requestUrl2"=>"https://app.xettle.io/api/callbacks/ibl",
            "merchantType"=>"AGGMER",
            "integrationType"=>"WEBAPI",
            "settleType"=> $request->settleType,
            "panNo"=>$request->panNo,
            "extMID"=>$merchantTxnRefId,
            "extTID"=> $request->extTID,
            "accNo"=> $request->accNo,
            "meEmailID"=> $request->meEmailID,
            "gstin"=> $request->gstin,
            "gstConsentFlag"=> $request->gstConsentFlag
        ];
        //dd(json_encode(json_encode($params)));
        $requestType = 'merchant';
        if(isset($request->auth_data['user_id'])) {
            $userId = $request->auth_data['user_id'];
        } else {
            $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        }
        $modal = 'ibl';
        $reqType = 'addSubMerchant';
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);

            
            //$res = preg_replace("!\r?\n!", "", $dec_response['response']);
            //$res = str_replace(['"{','}"'], ['{','}'], $dec_response['response']);
            //$res = explode(',',$res);
            //$response = (json_decode(trim($res,'"'), true));
                

            if(($dec_response['statuscode']=='000')) {
                if($dec_response['text']['statusDesc'] === "SUCCESS") {
                    $merchant = new UPIMerchant;
                    $merchant->user_id = $userId;
                    $merchant->merchant_business_name = $dec_response['text']['mebussname'];
                    $merchant->merchant_virtual_address = $dec_response['text']['merVirtualAdd'];
                    $merchant->request_url = $request->requestUrl;
                    $merchant->pan_no = $request->panNo;
                    $merchant->contact_email = $request->meEmailID;
                    $merchant->gstn = $request->gstin;
                    $merchant->merchant_business_type = 'AGGMER';
                    $merchant->per_day_txn_count = $request->perDayTxnCount?$request->perDayTxnCount:'';
                    $merchant->per_day_txn_lmt = $request->perDayTxnLmt?$request->perDayTxnLmt:'';
                    $merchant->per_day_txn_amt = $request->perDayTxnAmt?$request->perDayTxnAmt:'';
                    $merchant->mobile = $request->strCntMobile?$request->strCntMobile:'';
                    $merchant->address = $request->address?$request->address:'';
                    $merchant->state = $request->state?$request->state:'';
                    $merchant->city = $request->city?$request->city:'';
                    $merchant->pin_code = $request->pinCode?$request->pinCode:'';
                    $merchant->sub_merchant_id = isset($dec_response['text']['pgMerchantID'])?$dec_response['text']['pgMerchantID']:'';
                    $merchant->merchant_txn_ref_id = $merchantTxnRefId;
                    $merchant->mcc = $mcc; //$request->mcc;
                    $merchant->request_id = isset($dec_response['text']['requestId'])?$dec_response['text']['requestId']:'';
                    $merchant->crt_date = isset($dec_response['text']['crtDate'])?$dec_response['text']['crtDate']:'';
                    //$merchant->save();
                    $data = [
                        //'loginAccess' => $response['loginaccess'],
                        'subMerchantId' => isset($dec_response['text']['pgMerchantID'])?$dec_response['text']['pgMerchantID']:'',
                        'merchantBusinessName' => $dec_response['text']['mebussname'],
                        'merchantVirtualAddress' => $dec_response['text']['merVirtualAdd'],
                        'merchantKey' => isset($dec_response['text']['merchantKey'])?$dec_response['text']['merchantKey']:'',
                        'crtDate' => isset($dec_response['text']['crtDate'])?$dec_response['text']['crtDate']:'',
                        //'action' => $dec_response['text']['action']
                    ];
                    $code = '0x0200';
                    $this->message = "Merchant added successfully.";//$response['description'];
                    $status = $this::SUCCESS_STATUS;
                } else {
                    $code = '0x0201';
                    $this->message = $dec_response['text']['statusDesc'];
                    $status = $this::FAILED_STATUS;
                }
            }else
            {
                $code = '0x0201';
                $this->message = 'Something Went Wrong.';
                $status = $this::FAILED_STATUS;
            }

        $resp['code']       = $code;
        $resp['message']    = $this->message;
        $resp['status']     = $status;
        if(isset($data)) {
            $resp['data']   = $data;
        }
        return response()->json($resp);
    }

    public function meTranStatusQueryWeb(Request $request)
    {
        $params = [

            "requestInfo"=>[
                            "pspRefNo"=>$request->merchantTxnRefId,
                            "pgMerchantId"=>$request->pgMerchantId
                        ],
            "custRefNo"=>$request->customer_ref_id,
            "npciTranId"=>$request->npci_txn_id
        ];
        
        $requestType = 'transaction_status';
        if(isset($request->auth_data['user_id'])) {
            $userId = $request->auth_data['user_id'];
        } else {
            $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        }
        $modal = 'ibl';
        $reqType = 'transaction_status';
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);
        
        if(($dec_response['statuscode']=='000')) {

            $data = [
                    'amount' => $dec_response['text']['apiResp']['amount'],
                    //'txnNote' => $response['trxnNote'],
                    'payeeVPA' => $dec_response['text']['apiResp']['payeeVPA'],
                    'customerRefId' => $dec_response['text']['apiResp']['custRefNo'],
                    'merchantTxnRefId' => $dec_response['text']['apiResp']['pspRefNo'],
                    'payorVPA' => $dec_response['text']['apiResp']['payerVPA'],
                    'UPITxnId' => $dec_response['text']['apiResp']['upiTransRefNo'],
                    'npci_txn_id' => isset($dec_response['text']['apiResp']['npciTransId'])?$dec_response['text']['apiResp']['npciTransId']:""
                ];

                if($dec_response['text']['apiResp']['status']=='S')
                {
                    $code = '0x0200';
                    $this->message = $dec_response['text']['apiResp']['statusDesc'];
                    $status = $this::SUCCESS_STATUS;
                }
                else {
                    $code = '0x0201';
                    $this->message = $dec_response['text']['apiResp']['statusDesc'];
                    $status = $this::FAILED_STATUS;
                }
        }
        else 
        {
            $code = '0x0201';
            $this->message = "SOMETHING WENT WRONG";
            $status = $this::FAILED_STATUS;
        }

        $resp['code']       = $code;
        $resp['message']    = $this->message;
        $resp['status']     = $status;
        if(isset($data)) {
            $resp['data']   = $data;
        }
        return response()->json($resp);
    }

    public function meRefundService(Request $request)
    {
        $merchantTxnRefId = $this->getTxnId();
        $params = [
                    "pgMerchantId" => $request->pgMerchantId,
                    "orderNo" => $merchantTxnRefId,
                    "orgOrderNo" => $request->npci_txn_id,
                    "orgINDrefNo" => "",
                    "orgCustRefNo" => $request->customer_ref_id,
                    "txnNote" => $request->txnNote,
                    "txnAmount" => $request->amount,
                    "currencyCode" => "INR",
                    "payType" => "P2P",
                    "txnType" => "PAY"
                    ];
        
        $requestType = 'transaction_refund';
        if(isset($request->auth_data['user_id'])) {
            $userId = $request->auth_data['user_id'];
        } else {
            $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        }
        $modal = 'ibl';
        $reqType = 'transaction_refund';
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);

        if(($dec_response['statuscode']=='000')) {
            if($dec_response['text']['status']=='F')
            {
                $data = $dec_response['text'];
                $code = '0x0200';
                $this->message = $dec_response['text']['statusDesc'];
                $status = $this::FAILED_STATUS;;
            }else if($dec_response['text']['status']=='S')
            {

                $data = $dec_response['text'];
                $code = '0x0200';
                $this->message = $dec_response['text']['statusDesc'];
                $status = $this::SUCCESS_STATUS;
                
            }
        }
        else 
        {
            $code = '0x0201';
            $this->message = "SOMETHING WENT WRONG";
            $status = $this::FAILED_STATUS;
        }

        $resp['code']       = $code;
        $resp['message']    = $this->message;
        $resp['status']     = $status;
        if(isset($data)) {
            $resp['data']   = $data;
        }
        return response()->json($resp);
    }

    public function meTransactionHistoryWeb(Request $request)
    {
        $params = [
                    "paginationConfig"=>
                    [
                        "from_date"=>$request->from_date,
                        "to_date"=>$request->to_date,
                        "from_index"=>$request->from_index,
                        "to_index"=>$request->to_index
                    ],
                    "pgMerchantId"=>$request->pgMerchantId
            ];
        $requestType = 'transaction_history';
        if(isset($request->auth_data['user_id'])) {
            $userId = $request->auth_data['user_id'];
        } else {
            $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        }
        $modal = 'ibl';
        $reqType = 'transaction_history';
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);
        //print_r($dec_response['text']);
        if(($dec_response['statuscode']=='000')) {
                if($dec_response['text']['status'] === "S") {
                    $data['transDetails'] = $dec_response['text']['transDetails'];
                    $data['paginationConfig'] = $dec_response['text']['paginationConfig'];  
                    $code = '0x0200';
                    $this->message = "Records fetched successfully.";//$response['description'];
                    $status = $this::SUCCESS_STATUS;
                }
                else {
                    $code = '0x0201';
                    $this->message = $dec_response['text']['statusDesc'];
                    $status = $this::FAILED_STATUS;
                }
            }
            else {
                    $code = '0x0201';
                    $this->message = $dec_response['text']['statusDesc'];
                    $status = $this::FAILED_STATUS;
                }
        $resp['code']       = $code;
        $resp['message']    = $this->message;
        $resp['status']     = $status;
        if(isset($data)) {
            $resp['data']   = $data;
        }
        return response()->json($resp);
    }

    public function deActivateMerchant(Request $request)
    {
        $params = [
                "pgMerchantId"=>"INDB000001530781",
                "subMerchantId"=>$request->subMerchantId,
                "subMerVirtualAdd"=>$request->subMerVirtualAdd,
                "merchantType"=>"AGGMER",
                "action"=> "D",
                "remarks"=> $request->remarks
        ];
        $requestType = 'deactivate_merchant';
        if(isset($request->auth_data['user_id'])) {
            $userId = $request->auth_data['user_id'];
        } else {
            $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        }
        $merchant = UPIMerchant::where('user_id',$userId)->where('sub_merchant_id',$request->subMerchantId);
        if($merchant->count()==0)
        {
            $resp['code']       = "0x0100";
            $resp['message']    = "Merchant is not found";
            $resp['status']     = $this::ERROR_STATUS;
            return response()->json($resp);
        }
        $modal = 'ibl';
        $reqType = 'deactivate_merchant';
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);
        
        if(($dec_response['statuscode']=='000')) {
                if($dec_response['text']['statusDesc'] === "S") {
                    $merchant = UPIMerchant::where('user_id',$userId)->where('sub_merchant_id',$request->subMerchantId)->update(['is_active'=>0]);
                    
                    $data = [
                        //'loginAccess' => $response['loginaccess'],
                        'subMerchantId' => isset($dec_response['text']['pgMerchantID'])?$dec_response['text']['pgMerchantID']:'',
                        'subMerchantId' => isset($dec_response['text']['subMerchantId'])?$dec_response['text']['subMerchantId']:'',
                        'subMerVirtualAdd' => isset($dec_response['text']['subMerVirtualAdd'])?$dec_response['text']['subMerVirtualAdd']:'',
                        //'action' => $dec_response['text']['action']
                    ];
                    $code = '0x0200';
                    $this->message = "Merchant deactivated successfully.";//$response['description'];
                    $status = $this::SUCCESS_STATUS;
                }
                else {
                    $code = '0x0201';
                    $this->message = $dec_response['text']['statusDesc'];
                    $status = $this::FAILED_STATUS;
                }
            }
        $resp['code']       = $code;
        $resp['message']    = $this->message;
        $resp['status']     = $status;
        if(isset($data)) {
            $resp['data']   = $data;
        }
        return response()->json($resp);
    }

    public function mePayPayout(Request $request)
    {
        $merchantTxnRefId = $this->getTxnId();
        $mcc = '6012';
        $params = [
                "pgMerchantId"=>"INDB000001530781",
                "orderNo"=>$merchantTxnRefId,
                "txnNote"=>$request->txnNote,
                "txnAmount"=>$request->txnAmount,
                "currencyCode"=>"INR",
                "paymentType"=>"P2P",
                "txnType"=>"Pay",
                "mcc"=>$mcc,
                "payeeName"=>$request->payeeName,
                "payerAccNo"=>$request->payerAccNo,
                "payeeVPAType"=>$request->payeeVPAType,
                "merchantType"=>"AGGMER",
                "action"=> "D"
        ];
        if($request->payeeVPAType=='VPA')
        {
            $params['payeeMobNo'] = $request->payeeMobNo;
            $params['PayeeMmId'] = $request->PayeeMmId;
        }else if($request->payeeVPAType=='IFSC')
        {
            $params['payeeAccNo'] = $request->payeeAccNo;
            $params['payeeIfsc'] = $request->payeeIfsc;
        }

        $requestType = 'mepayout';
        if(isset($request->auth_data['user_id'])) {
            $userId = $request->auth_data['user_id'];
        } else {
            $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        }
        $modal = 'ibl';
        $reqType = 'mepayout';
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);
        print_r($dec_response);
    }

    /**
     * Create Merchant function
     *
     * @param Request $request
     * @return void
     */
    public function addMerchant(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->addMerchant();
        if($validator->fails()) {
            $resp['code']       = "0x0100";
            $resp['message']    = json_decode(json_encode($validator->errors()), true);
            $resp['status']     = $this::ERROR_STATUS;
            return response()->json($resp);
        }
        $merchantTxnRefId = $this->getTxnId();
        $mcc = '6012';
        $params = [
            "pgMerchantId" => "INDB000001530781",
            "mebussname"=> $request->mebussname,
            "legalStrName"=> $request->legalStrName,
            "merVirtualAdd"=>$request->merVirtualAdd,
            "awlmcc"=>$mcc,
            "strCntMobile"=>$request->mobile,
            "requestUrl1"=>"https://app.xettle.io/api/callbacks/ibl",
            "requestUrl2"=>"https://app.xettle.io/api/callbacks/ibl",
            "requestUrl3"=>"https://app.xettle.io/api/callbacks/ibl",
            "requestUrl4"=>"https://app.xettle.io/api/callbacks/ibl",
            "merchantType"=>"DIRMER",
            "integrationType"=>"WEBAPI",
            "settleType"=> $request->settleType,
            "panNo"=>$request->panNo,
            "stCity"=>$request->city,
            "stState"=>$request->state,
            "stPincode"=>$request->pincode,
            "strCntName"=>$request->contactName,
            "strCntPhone"=>$request->strCntPhone,
            "cntEmail"=>$request->contactEmail,
            "strEmailId"=>$request->loginEmail,
            // "extMID"=>$merchantTxnRefId,
            // "extTID"=> $request->extTID,
            "accNo"=> $request->accNo,
            "ifscCode"=>$request->ifscCode,
            "issueBnk"=>$request->issueBnk,
            "stAdd1"=>$request->stAdd1,
            "stAdd2"=>$request->stAdd2,
            "stAdd3"=>$request->stAdd3,
            "billingFax"=>$request->billingFax,
            "gstin"=> $request->gstin,
            "gstConsentFlag"=> $request->gstConsentFlag
        ];
        //dd(json_encode(json_encode($params)));
        $requestType = 'addmerchant';
        if(isset($request->auth_data['user_id'])) {
            $userId = $request->auth_data['user_id'];
        } else {
            $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        }
        $modal = 'ibl';
        $reqType = 'addMerchant';
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);

            
            //$res = preg_replace("!\r?\n!", "", $dec_response['response']);
            //$res = str_replace(['"{','}"'], ['{','}'], $dec_response['response']);
            //$res = explode(',',$res);
            //$response = (json_decode(trim($res,'"'), true));
                

            if(($dec_response['statuscode']=='000')) {
                if($dec_response['text']['statusDesc'] === "SUCCESS") {
                    $merchant = new UPIMerchant;
                    $merchant->user_id = $userId;
                    $merchant->merchant_business_name = $dec_response['text']['mebussname'];
                    $merchant->merchant_virtual_address = $dec_response['text']['merVirtualAdd'];
                    $merchant->request_url = $request->requestUrl;
                    $merchant->pan_no = $request->panNo;
                    $merchant->contact_email = $request->meEmailID;
                    $merchant->gstn = $request->gstin;
                    $merchant->merchant_business_type = 'AGGMER';
                    $merchant->per_day_txn_count = $request->perDayTxnCount?$request->perDayTxnCount:'';
                    $merchant->per_day_txn_lmt = $request->perDayTxnLmt?$request->perDayTxnLmt:'';
                    $merchant->per_day_txn_amt = $request->perDayTxnAmt?$request->perDayTxnAmt:'';
                    $merchant->mobile = $request->strCntMobile?$request->strCntMobile:'';
                    $merchant->address = $request->address?$request->address:'';
                    $merchant->state = $request->state?$request->state:'';
                    $merchant->city = $request->city?$request->city:'';
                    $merchant->pin_code = $request->pinCode?$request->pinCode:'';
                    $merchant->sub_merchant_id = isset($dec_response['text']['subMerchantId'])?$dec_response['text']['subMerchantId']:'';
                    $merchant->merchant_txn_ref_id = $merchantTxnRefId;
                    $merchant->mcc = $mcc; //$request->mcc;
                    $merchant->request_id = isset($dec_response['text']['requestId'])?$dec_response['text']['requestId']:'';
                    $merchant->crt_date = isset($dec_response['text']['crtDate'])?$dec_response['text']['crtDate']:'';
                    $merchant->save();
                    $data = [
                        //'loginAccess' => $response['loginaccess'],
                        'subMerchantId' => isset($dec_response['text']['pgMerchantID'])?$dec_response['text']['pgMerchantID']:'',
                        'merchantBusinessName' => $dec_response['text']['mebussname'],
                        'merchantVirtualAddress' => $dec_response['text']['merVirtualAdd'],
                        'merchantKey' => isset($dec_response['text']['merchantKey'])?$dec_response['text']['merchantKey']:'',
                        'crtDate' => isset($dec_response['text']['crtDate'])?$dec_response['text']['crtDate']:'',
                        //'action' => $dec_response['text']['action']
                    ];
                    $code = '0x0200';
                    $this->message = "Merchant added successfully.";//$response['description'];
                    $status = $this::SUCCESS_STATUS;
                } 
                // elseif($dec_response['text']['statusCode'] === "0x0201") {
                //     $code = $response['code'];
                //     $this->message = 'Something went wrong please try again';
                //     $status = $this::FAILED_STATUS;
                // }
                 else {
                    $code = '0x0201';
                    $this->message = $dec_response['text']['statusDesc'];
                    $data = $dec_response['text'];
                    $status = $this::FAILED_STATUS;
                }
            }

        $resp['code']       = $code;
        $resp['message']    = $this->message;
        $resp['status']     = $status;
        if(isset($data)) {
            $resp['data']   = $data;
        }
        return response()->json($resp);
    }
    /**
     * transaction status check function
     *
     * @param Request $request
     * @return void
     */
    public function status(Request $request, $txnId)
    {
        if(!$txnId) {
            $resp['code']       = "0x0100";
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
        $response = $this->UPICaller($params, $requestType, $userId, $modal, $reqType);

        if(isset($response['code']) && $response['code'] != "0x0203") {
            if($response['code'] === "0x0200") {
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
            } elseif($response['code'] === "0x0201") {
                $code = $response['code'];
                $this->message = 'Something went wrong please try again';
                $status = $this::FAILED_STATUS;
            } else {
                if(isset($response['originalOrderId'])) {
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
        if(isset($data)) {
            $resp['data']   = $data;
        }

        return response()->json($resp);
    }

    public function verify(Request $request)
    {
        if(!$request->virtualAddress) {
            $resp['code']       = "0x0100";
            $resp['message']    = "VPA field is required.";
            $resp['status']     = $this::ERROR_STATUS;
            return response()->json($resp);
        }

        $params = [
            'requestInfo'=>[
                'pgMerchantId'=>'INDB000001530781',
                'pspRefNo' => $this->getTxnId()
            ],
            'payeeType' => [
                'virtualAddress'=>$request->virtualAddress
            ],
            "vAReqType"=>"T"
        ];

        $requestType = 'verify';
        $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        $modal = 'ibl';
        $reqType = 'verifyVPA';
        $dec_response = $this->convertRequestResponse($params,$userId,$requestType,$modal,$reqType);
       // $response = $this->UPICaller($params, $requestType, $userId, $modal, $reqType);

            //print_r($dec_response);exit;
            if($dec_response['statuscode'] === "000") {
                $data = [
                    'name' => $dec_response['text']['payeeType']['name'],
                    'bankTxnId' => $dec_response['text']['requestInfo']['pspRefNo'],
                    'virtualAddress' => $dec_response['text']['payeeType']['virtualAddress'],
                    //'status' => $dec_response['text']['requestInfo'],
                ];

                if(isset($dec_response['text']['statusDesc'])) {
                    $description = $dec_response['text']['statusDesc'];
                }

                // if(isset($response['description'])) {
                //     $description = $response['description'];
                // }

                $code = '0x0200';
                $this->message = $description;
                $status = $this::SUCCESS_STATUS;
            } elseif($dec_response['code'] === "0x0201") {
                $code = $dec_response['code'];
                $this->message = 'Something went wrong please try again';
                $status = $this::FAILED_STATUS;
            } else {
                $code = '0x0201';
                $this->message = $dec_response['text']['statusDesc'];
                $status = $this::FAILED_STATUS;
            }

        if(isset($code)) {
            $resp['code']       = $code;
            $resp['status']     = $status;
            $resp['message']    = $this->message;
        } else {
            $resp['code']       = "0x0201";
            $resp['status']     = $this::FAILED_STATUS;
            $resp['message']    = 'Something went wrong please try again';
        }
        
        
        if(isset($data)) {
            $resp['data']   = $data;
        }

        return response()->json($resp);
    }

    public function getTxnId()
    {
        return CommonHelper::getRandomString();
    }
    public function getInvoiceId()
    {
        return CommonHelper::getRandomString('INV',true,4);
    }
    public function UPICaller($params, $requestType, $userId, $modal, $reqType)
    {
        switch ($requestType) {
            case 'collect':
                $request = $params;
                $url = $this->baseUrl."/upijson/meCollectInitiateWeb";
            break;

            case 'merchant':
                $request = $params;

                $url = $this->baseUrl.'/api/onBoardSubMerchant';

            break;
            case 'addmerchant':
                $request = $params;

                $url = $this->baseUrl.'/web/onBoardDirectMerchant';

            break;

            case 'status':
                $request = [];
                $url = $this->baseUrl."/status/".$params['txnId'];
            break;

            case 'verify':
                $request = $params;
                $url = $this->baseUrl.'/upijson/validateVPAWeb';
            break;

            case 'transaction_status':
                $request = $params;
                $url = $this->baseUrl.'/upijson/meTranStatusQueryWeb';
            break;

            case 'transaction_refund':
                $request = $params;
                $url = $this->baseUrl.'/upi/meRefundJsonService';
            break;

            case 'transaction_history':
                $request = $params;
                $url = $this->baseUrl.'/upijson/metransactionhistoryweb';
            break;

            case 'deactivate_merchant':
                $request = $params;
                $url = $this->baseUrl.'/api/deActivateMerchant';
            break;

            case 'mepayout':
                $request = $params;
                $url = $this->baseUrl.'/upi/mePayServerApi';
            break;
        }

        $result = CommonHelper::curl($url, "POST", json_encode($request) , $this->header, 'yes', $userId, $modal, $reqType);
        
        if($reqType=='deactivate_merchant')
        {
            $result['response'] = json_encode(['resp'=>$result['response']]);
            //$result['response']['resp'] = $result['response'];
        }
        
        $response = json_decode($result['response'], 1);
        return $response;
    }

    public function encryptUPIdata($data,$userId)
    {
        $url = 'http://45.249.111.172/XettleUPI/Indusind/GetEncryptUPIdata';
        $string = (json_encode($data));
        $request = [
            'message' => $string,
            'decr_key'=> $this->decr_key
            ];
        $modal='';
        $reqType='';
        $result = CommonHelper::curl($url, "POST", json_encode($request) , ["Content-Type: application/json"], 'no', $userId, $modal, $reqType);
        //print_r($result);
        $response = json_decode($result['response'], 1);
        return $response;
    }

    public function decryptUPIdata($string,$userId)
    {
        $url = 'http://45.249.111.172/XettleUPI/Indusind/GetDecryptUPIdata';
        //$string = json_encode(json_encode($data));
        $request = [
            'message' => $string,
            'decr_key'=> $this->decr_key
            ];
        $modal='';
        $reqType='';
        $result = CommonHelper::curl($url, "POST", json_encode($request) , ["Content-Type: application/json"], 'no', $userId, $modal, $reqType);
        //print_r($result);
        $response = json_decode($result['response'], 1);
        return $response;
    }

    public function convertRequestResponse($params,$userId,$requestType,$modal,$reqType)
    {
        $result = $this->encryptUPIdata($params,$userId);
        if($result['statuscode']=='000')
        {
            $params = [
                "pgMerchantId" => "INDB000001530781",
                "requestMsg" => $result['text']
            ];
             
            $response = $this->UPICaller($params, $requestType, $userId, $modal, $reqType);
             
             if($reqType=='transaction_refund')
            {
                $response = (['resp'=>$response['apiResp']]);
            }
            
            $dec_response = $this->decryptUPIdata($response['resp'],$userId);
                        
            return $dec_response;

        }
    }

}
