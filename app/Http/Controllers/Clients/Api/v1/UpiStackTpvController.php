<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\IBLUpiHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrimaryFundCredit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpiStackTpvController extends Controller
{
    /**
     * Add Account
     */
    public function addAccount(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'name'   => "required|min:5|max:25|regex:/^([a-zA-Z0-9 ]+)$/",
                    'vpa' => "required|min:2|max:12|regex:/^([a-zA-Z0-9]+)$/",
                    'pan' => "required|size:10|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/",
                    'email' => [
                        'required', 'min:5', 'max:200',
                        function ($attr, $val, $fail) {
                            if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                                $fail("Invalid email address format.");
                            }
                        }
                    ],
                    'mobile' => "required|digits:10|regex:/^(\d)*$/",
                    'mcc' => "nullable|numeric|min:1|max:99999",
                    'allowedPayers.vpa' => [
                        'required_without:allowedPayers.bank.accountNumber', 'array', 'min:1', 'max:5',
                        function ($attr, $val, $fail) {
                            foreach ($val as $row) {
                                if (empty($row)) {
                                    $fail("VPA field can't be empty.");
                                    break;
                                }
                            }
                        },
                        function ($attr, $val, $fail) {
                            $pattern = "/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/i";
                            foreach ($val as $row) {
                                if (!preg_match($pattern, $row)) {
                                    $fail("The vpa must be a valid UPI address.");
                                    break;
                                }
                            }
                        }
                    ],
                    'allowedPayers.bank.accountNumber' => "required_without:allowedPayers.vpa|required_with:allowedPayers.bank.ifsc|digits_between:8,20|regex:/^(\d)*$/",
                    'allowedPayers.bank.ifsc' => "required_without:allowedPayers.vpa|required_with:allowedPayers.bank.accountNumber|size:11|regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/",
                ],
                [
                    'mobile.regex' => "The mobile number is invalid.",
                    'allowedPayers.vpa.email' => "The vpa must be a valid UPI address.",
                    'allowedPayers.vpa.required_without' => "The vpa field is required when Bank Account is not present.",
                    'allowedPayers.bank.accountNumber.required_without' => "The Account Number field is required when VPA is not present.",
                    'allowedPayers.bank.accountNumber.regex' => "The allowed payers.bank.account number is invalid.",
                    'allowedPayers.bank.ifsc.required_without' => "The Bank IFSC field is required.",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Missing Parameters', $message);
            }


            //getting user_id
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }


            $panNumber = strtoupper(trim($request->pan));

            //check email and mobile number is already registered
            $checkPan = DB::table('upi_merchants')
                ->select('id', 'merchant_virtual_address')
                ->where('pan_no', $panNumber)
                ->where('user_id', $userId)
                ->where('root_type', 'ibl_tpv')
                ->first();

            if (!empty($checkPan)) {
                return ResponseHelper::failed('PAN number already used.', ['vpa' => $checkPan->merchant_virtual_address]);
            }

            $mcc = empty($request->mcc) ? 6051 : trim($request->mcc);

            $iblHelper = new IBLUpiHelper();
            $merchantTxnRefId = CommonHelper::getRandomString();

            if (strlen($merchantTxnRefId) > 20) {
                $merchantTxnRefId = substr($merchantTxnRefId, 0, 20);
            }

            $params = [
                "pgMerchantId" => $iblHelper->getMerchantId(),
                "mebussname" => trim($request->name),
                "legalStrName" => trim($request->name),
                "merVirtualAdd" => strtolower(trim($request->vpa)) . $this->randNumber() . '@indus',
                "awlmcc" => $mcc,
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
                // "meEmailID" => $request->email,
                // "gstin" => $request->gstin,
                "gstConsentFlag" => 'N', //$request->gstConsentFlag
            ];

            $requestType = 'merchant';


            $modal = ROOT_TYPE_VA;
            $reqType = 'addSubMerchant';
            $dec_response = $iblHelper->convertRequestResponse($params, $userId, $requestType, $modal, $reqType);


            if (($dec_response['statuscode'] == '000')) {
                if ($dec_response['text']['statusDesc'] === "SUCCESS") {

                    $insertParams = [
                        'root_type' => ROOT_TYPE_VA,
                        'user_id' => $userId,
                        'merchant_business_name' => $dec_response['text']['mebussname'],
                        'merchant_virtual_address' => $dec_response['text']['merVirtualAdd'],
                        'request_url' => $request->requestUrl,
                        'pan_no' => $panNumber,
                        'contact_email' => strtolower(trim($request->email)),
                        'merchant_business_type' => 'AGGMER',
                        'mobile' => $request->mobile ? trim($request->mobile) : '',
                        'sub_merchant_id' => isset($dec_response['text']['pgMerchantID']) ? $dec_response['text']['pgMerchantID'] : '',
                        'merchant_txn_ref_id' => $merchantTxnRefId,
                        'mcc' => $mcc,
                        'tpv_status' => '1',
                        'request_id' => isset($dec_response['text']['merchantKey']) ? $dec_response['text']['merchantKey'] : '',
                        'crt_date' => isset($dec_response['text']['crtDate']) ? $dec_response['text']['crtDate'] : '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    if (!empty($request->allowedPayers['vpa'])) {
                        $vpaArray = array_map('strtolower', $request->allowedPayers['vpa']);
                        $insertParams['allowed_vpa'] = implode(',', $vpaArray);
                    }

                    if (!empty($request->allowedPayers['bank'])) {
                        $insertParams['allowed_bank'] = $request->allowedPayers['bank']['accountNumber'] . ',' . strtoupper($request->allowedPayers['bank']['ifsc']);
                    }

                    $merchantId = DB::table('upi_merchants')->insertGetId($insertParams);

                    $data = [
                        'referenceId' => $merchantTxnRefId,
                        'name' => $dec_response['text']['mebussname'],
                        'vpa' => $dec_response['text']['merVirtualAdd'],
                        'created' => $insertParams['created_at'],
                    ];


                    $dataForDispatch['id'] = $merchantId;
                    $dataForDispatch['user_id'] = $userId;
                    $dataForDispatch['identifier'] = 'va_vpa_create_fee';
                    $dataForDispatch['type'] = SRV_SLUG_VA;
                    $dataForDispatch['slug'] = 'va_create';
                    $dataForDispatch['merchant_txn_ref_id'] = $merchantTxnRefId;

                    //apply VPA creation charges
                    PrimaryFundCredit::dispatch((object) $dataForDispatch, 'upi_stack_creation_fee')->onQueue('primary_fund_queue');

                    return ResponseHelper::success("VPA created successfully.", $data);
                } else {
                    $message = $dec_response['text']['statusDesc'];

                    return ResponseHelper::failed($message);
                }
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }


    /**
     * Update Account TPV
     */
    public function updateAccountTpv(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'vpa' => "required|min:2|max:25|regex:/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/i",
                    'allowedPayers.vpa' => [
                        'required_without:allowedPayers.bank.accountNumber', 'array', 'min:1', 'max:5',
                        function ($attr, $val, $fail) {
                            foreach ($val as $row) {
                                if (empty($row)) {
                                    $fail("VPA field can't be empty.");
                                    break;
                                }
                            }
                        },
                        function ($attr, $val, $fail) {
                            $pattern = "/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/i";
                            foreach ($val as $row) {
                                if (!preg_match($pattern, $row)) {
                                    $fail("The vpa must be a valid UPI address.");
                                    break;
                                }
                            }
                        }
                    ],
                    'allowedPayers.bank.accountNumber' => "required_without:allowedPayers.vpa|required_with:allowedPayers.bank.ifsc|digits_between:8,20|regex:/^(\d)*$/",
                    'allowedPayers.bank.ifsc' => "required_without:allowedPayers.vpa|required_with:allowedPayers.bank.accountNumber|size:11|regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/",
                ],
                [
                    'allowedPayers.vpa.required_without' => "The vpa field is required when Bank Account is not present.",
                    'allowedPayers.bank.accountNumber.required_without' => "The Account Number field is required when VPA is not present.",
                    'allowedPayers.bank.accountNumber.regex' => "The allowed payers.bank.account number is invalid.",
                    'allowedPayers.bank.ifsc.required_without' => "The Bank IFSC field is required.",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Missing Parameters', $message);
            }


            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }


            $count = DB::table('upi_merchants')
                ->select('id')
                ->where('merchant_virtual_address', $request->vpa)
                ->where('user_id', $userId)
                ->count();

            if ($count == 0) {
                return ResponseHelper::failed("VPA Address is not found.");
            }

            if (!empty($request->allowedPayers['vpa'])) {
                $vpaArray = array_map('strtolower', $request->allowedPayers['vpa']);
                $insertParams['allowed_vpa'] = implode(',', $vpaArray);
            } else if (isset($request->allowedPayers['vpa'])) {
                $insertParams['allowed_vpa'] = null;
            }

            if (!empty($request->allowedPayers['bank'])) {
                $insertParams['allowed_bank'] = $request->allowedPayers['bank']['accountNumber'] . ',' . strtoupper($request->allowedPayers['bank']['ifsc']);
            } else if (isset($request->allowedPayers['bank'])) {
                $insertParams['allowed_bank'] = null;
            }

            DB::table('upi_merchants')
                ->where('merchant_virtual_address', $request->vpa)
                ->where('user_id', $userId)
                ->update($insertParams);

            return ResponseHelper::success("Updated Successfully.");
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }



    /**
     * fetch account details
     */
    public function accountDetails(Request $request, $vpa)
    {
        try {

            $pattern = "/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/i";
            if (!preg_match($pattern, $vpa)) {
                return ResponseHelper::failed("The vpa must be a valid UPI address.");
            }

            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }

            $vpa = strtolower(trim($vpa));

            $vpaAccount = DB::table('upi_merchants')
                ->select('*')
                ->where('merchant_virtual_address', $vpa)
                ->where('user_id', $userId)
                ->first();

            if (empty($vpaAccount)) {
                return ResponseHelper::failed("VPA Address is not found.");
            }


            $responseParam = [
                "name" => $vpaAccount->merchant_business_name,
                "vpa" => $vpaAccount->merchant_virtual_address,
                "pan" => $vpaAccount->pan_no,
                "email" => $vpaAccount->contact_email,
                "mobile" => $vpaAccount->mobile,
                "created" => $vpaAccount->created_at,
            ];

            if (!empty($vpaAccount->allowed_bank)) {
                $bank = explode(',', $vpaAccount->allowed_bank);
                $responseParam['allowedPayers']['bank']['accountNumber'] = $bank[0];
                $responseParam['allowedPayers']['bank']['ifsc'] = $bank[1];
            } else {
                $responseParam['allowedPayers']['bank'] = '';
            }

            if (!empty($vpaAccount->allowed_vpa)) {
                $responseParam['allowedPayers']['vpa'] = explode(',', $vpaAccount->allowed_vpa);
            } else {
                $responseParam['allowedPayers']['vpa'] = '';
            }

            return ResponseHelper::success("Account details.", $responseParam);
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }


    /**
     * transaction status check function
     */
    public function status(Request $request)
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
                ->where('root_type', 'ibl_tpv')
                ->where('merchant_virtual_address', $vpa)
                ->first();

            if (empty($vpaInfo)) {
                return ResponseHelper::failed('VPA not found');
            }


            $iblHelper = new IBLUpiHelper();

            $params = [
                "requestInfo" => [
                    "pspRefNo" => 'PSPREF' . uniqid(), //$utrInfo->merchant_txn_ref_id, //isset($request->pspRefNo) ? $request->pspRefNo : '',
                    "pgMerchantId" => $vpaInfo->sub_merchant_id, //$iblHelper->getMerchantId()
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
                            "txnStatus" => $iblHelper->getTxnStatus($dec_response['text']['apiResp']['status']),
                            "txnNote" => $dec_response['text']['apiResp']['statusDesc'],
                            "amount" => $dec_response['text']['apiResp']['amount'],
                            "customerRefId" => $dec_response['text']['apiResp']['custRefNo'],
                            "payeeVPA" => $dec_response['text']['apiResp']['payeeVPA'],
                            "payerVPA" => $dec_response['text']['apiResp']['payerVPA'],
                            "payerName" => isset($dec_response['text']['apiResp']['addInfo']['addInfo3']) ? $dec_response['text']['apiResp']['addInfo']['addInfo3'] : '',
                            "payerAccNo" => isset($dec_response['text']['apiResp']['addInfo']['addInfo2']) ? $dec_response['text']['apiResp']['addInfo']['addInfo2'] : '',
                            "created" => $dec_response['text']['apiResp']['txnAuthDate'],
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
     * VPA List by page
     */
    public function fetchByPage($page = 0)
    {
        try {

            if (isset(request()->auth_data['user_id'])) {
                $userId = request()->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret(request()->header());
            }

            $limit = 10;

            $pageNo = empty($page) ?  1 : intval($page);
            $skip = ($pageNo - 1) * $limit;


            $rowCount = DB::table('upi_merchants')
                ->select('id')
                ->where('user_id', $userId)
                ->where('root_type', ROOT_TYPE_VA)
                ->count();

            $vpaInfo = DB::table('upi_merchants')
                ->select('*')
                ->where('user_id', $userId)
                ->where('root_type', ROOT_TYPE_VA)
                ->skip($skip)
                ->limit($limit)
                ->orderBy('id', 'asc')
                ->get();


            $vpaList = [];

            if ($vpaInfo->isNotEmpty()) {

                $count = 0;
                foreach ($vpaInfo as $row) {
                    $vpaList[$count] = [
                        "name" => $row->merchant_business_name,
                        "vpa" => $row->merchant_virtual_address,
                        "pan" => $row->pan_no,
                        "email" => $row->contact_email,
                        "mobile" => $row->mobile,
                        // "mcc" => $row->mcc,
                        "created" => $row->created_at,
                    ];

                    if (!empty($row->allowed_bank)) {
                        $bank = explode(',', $row->allowed_bank);
                        $vpaList[$count]['allowedPayers']['bank']['accountNumber'] = $bank[0];
                        $vpaList[$count]['allowedPayers']['bank']['ifsc'] = $bank[1];
                    } else {
                        $vpaList[$count]['allowedPayers']['bank'] = '';
                    }

                    if (!empty($row->allowed_vpa)) {
                        $vpaList[$count]['allowedPayers']['vpa'] = explode(',', $row->allowed_vpa);
                    } else {
                        $vpaList[$count]['allowedPayers']['vpa'] = '';
                    }
                    $count++;
                }

                $return = [
                    'totalRecords' => $rowCount,
                    'perPageRecords' => $limit,
                    'totalPages' => (ceil($rowCount / $limit)),
                    'currentPage' => $pageNo,
                    'vpaList' => $vpaList,
                ];

                return ResponseHelper::success('VPA List found.', $return);
            } else {
                return ResponseHelper::failed('VPA list not found.');
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }



    private function randNumber()
    {
        $length = 4;
        $base_str = '0123456789';

        //generate rand number
        $mt_rand = 'xt' . substr(str_shuffle($base_str), 0, $length);

        return $mt_rand;
    }
}
