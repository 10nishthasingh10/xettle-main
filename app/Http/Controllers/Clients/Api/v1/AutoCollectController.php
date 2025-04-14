<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrimaryFundCredit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AutoCollectController extends Controller
{
    private $isMultiVan;
    private $isMultiUpi;


    public function __construct()
    {
        $this->isMultiVan = 0;
        $this->isMultiUpi = 0;
    }


    /**
     * Generate VPA or VAN using API
     */
    public function generateVirtualAccount(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'serviceType' => "required|in:upi,van",
                'businessName' => "required|min:5|max:30",
                'vpaAddress' => 'nullable|min:3|max:6',
                'panNo' => "required|size:10",
                'bankAccountNo' => "required|digits_between:8,20",
                'bankIfsc' => "required|size:11|regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/",
                'contactEmail' => "required|email",
                'gstn' => "nullable|alpha_num|size:15",
                'mobile' => "required|digits:10",
                'address' => "required",
                'state' => "required|digits_between:1,2",
                'city' => "required",
                'pinCode' => "required|digits:6"
            ],
            [
                'serviceType.in' => "Invalid serviceType, only upi or van accepted.",
                'contactEmail.required' => "Email field is required.",
                'contactEmail.email' => "Email must be a valid email address.",
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing('Some params are missing.', $message);
        }

        try {

            //get user id
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }

            //check email and mobile number is already registered
            $countEmail = DB::table('cf_merchants')->select('id')
                ->where('contact_email', strtolower($request->contactEmail))
                ->where('service_type', $request->serviceType)
                ->where('user_id', $userId)
                ->count();
            if ($countEmail > 0) {
                return ResponseHelper::missing('Some params are missing.', ['contactEmail' => ['Email already registered.']]);
            }

            $countMobile = DB::table('cf_merchants')->select('id')
                ->where('mobile', $request->mobile)
                ->where('service_type', $request->serviceType)
                ->where('user_id', $userId)
                ->count();
            if ($countMobile > 0) {
                return ResponseHelper::missing('Some params are missing.', ['mobile' => ['Mobile number already registered.']]);
            }

            switch ($request->serviceType) {
                case 'upi':
                    //generate VPA
                    if (empty($request->input('vpaAddress'))) {
                        return ResponseHelper::missing('Some params are missing.', ["vpaAddress" => [
                            "vpaAddress field can't be empty."
                        ]]);
                    } else {
                        return $this->createVPA($request, $userId);
                    }
                    break;
                case 'van':
                    //generate VAN
                    return $this->createVAN($request, $userId);
                    break;
            }
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }



    /**
     * Update VAN using API
     */
    public function updateVirtualAccount(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                // 'serviceType' => "required|in:van",
                'vanAccId' => "required|min:4|max:16",
                'bankAccountNo' => "required|digits_between:8,20",
                'bankIfsc' => "required|size:11|regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/",
            ],
            [
                'required' => "This param is required.",
                // 'serviceType.in' => "Invalid serviceType, only van accepted.",
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing('Some params are missing.', $message);
        }

        try {

            //get user id
            if (!empty($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {

            $isServiceActive = CommonHelper::checkIsApiRootActive($userId, 'smart_collect', 'update');

            if (!$isServiceActive) {
                return ResponseHelper::failed('This API is not enabled.');
            }

            //check email and mobile number is already registered
            $cfMerchant = DB::table('cf_merchants')
                ->select('*')
                ->where('v_account_id', ($request->vanAccId))
                // ->where('service_type', 'van')
                ->where('user_id', $userId)
                ->first();

            if (empty($cfMerchant)) {
                return ResponseHelper::failed('vanAccId is not available.');
            }

            if ($cfMerchant->van_status != '1') {
                return ResponseHelper::failed('vanAccId is not active.');
            }

            if ($cfMerchant->bank_account_no == $request->bankAccountNo) {
                return ResponseHelper::failed('This bank account is already added.');
            }

            //update VAN
            $params = [
                "vAccountId" => $cfMerchant->v_account_id,
                "remitterAccount" => $request->bankAccountNo,
                "remitterIfsc" => strtoupper($request->bankIfsc),
            ];


            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();

            $result = $vanHelper->vanManager($params, '/cac/v1/editVA', $userId, 'POST', 'updateVan');

            if ($result['code'] == 200) {
                $cashfreeResponse = json_decode($result['response']);


                //when response is success
                if ($cashfreeResponse->subCode === "200") {

                    $updateData = [
                        'bank_account_no' => $request->bankAccountNo,
                        'bank_ifsc' => strtoupper($request->bankIfsc),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    DB::table('cf_merchants')
                        // ->where('v_account_id', ($request->vanAccId))
                        // ->where('service_type', $request->serviceType)
                        // ->where('user_id', $userId)
                        ->where('id', $cfMerchant->id)
                        ->update($updateData);

                    $userResponse = [
                        'vanAccId' => $request->vanAccId,
                        "bankAccountNo" => $request->bankAccountNo,
                        "bankIfsc" => strtoupper($request->bankIfsc),
                    ];

                    return ResponseHelper::success("Virtual account updated successfully", $userResponse);
                } else if ($cashfreeResponse->subCode === "412") {
                    return ResponseHelper::failed('Create Multiple Accounts feature not enabled.');
                } else if ($cashfreeResponse->subCode === "403") {
                    return ResponseHelper::failed('Something went wrong, try after some time.');
                }


                return ResponseHelper::failed($cashfreeResponse->message);
            }

            return ResponseHelper::failed("VAN Creation Failed.");
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }



    /**
     * Create Dynamic QR Code
     */
    public function generateDynamicQrCode(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vpaAddress' => 'required|min:3',
                'amount' => 'required|numeric|min:1',
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing('Some params are missing.', $message);
        }

        try {

            //get user id
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }


            $upiAddress = strtolower($request->vpaAddress);
            $amount = strtolower($request->amount);

            //check UPI address is available or not
            $count = DB::table('cf_merchants')->select('id')
                ->where('user_id', $userId)
                ->where(function ($sql) use ($upiAddress) {
                    $sql->where('vpa_1', $upiAddress)
                        ->orWhere('vpa_2', $upiAddress);
                })
                ->count();

            if ($count == 0) {
                return ResponseHelper::failed('Invalid UPI address');
            }

            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();
            $result = $vanHelper->vanManager([], '/cac/v1/createDynamicQRCode?virtualVPA=' . $upiAddress . "&amount=" . $amount, $userId, 'GET', 'generateDynamicQrCode');

            if ($result['code'] == 200) {

                $cashfreeResponse = json_decode($result['response']);

                if ($cashfreeResponse->subCode === "200") {
                    return ResponseHelper::success("QR Code generated succesfully", ['qrCode' => $cashfreeResponse->qrCode]);
                } else if ($cashfreeResponse->subCode === "403") {
                    return ResponseHelper::failed('Something went wrong, try after some time.');
                }

                return ResponseHelper::failed($cashfreeResponse->message);
            }

            return ResponseHelper::failed("UPI Creation Failed.");
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }



    /**
     * Create QR Code
     */
    public function generateQrCode(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'vpaAddress' => 'required|min:3',
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing('Some params are missing.', $message);
        }


        try {

            //get user id
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }


            $upiAddress = strtolower($request->vpaAddress);

            //check UPI address is available or not
            $count = DB::table('cf_merchants')->select('id')
                ->where('user_id', $userId)
                ->where(function ($sql) use ($upiAddress) {
                    $sql->where('vpa_1', $upiAddress)
                        ->orWhere('vpa_2', $upiAddress);
                })
                ->count();

            if ($count == 0) {
                return ResponseHelper::failed('Invalid UPI address');
            }

            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();
            $result = $vanHelper->vanManager([], '/cac/v1/createQRCode?virtualVPA=' . $upiAddress, $userId, 'GET', 'createQRCode');

            if ($result['code'] == 200) {

                $cashfreeResponse = json_decode($result['response']);

                if ($cashfreeResponse->subCode === "200") {
                    return ResponseHelper::success("QR Code generated succesfully", ['qrCode' => $cashfreeResponse->qrCode]);
                } else if ($cashfreeResponse->subCode === "403") {
                    return ResponseHelper::failed('Something went wrong, try after some time.');
                }

                return ResponseHelper::failed($cashfreeResponse->message);
            }

            return ResponseHelper::failed("UPI Creation Failed.");
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * search Transaction By Utr
     */
    public function searchTxnByUtr(Request $request, $utr)
    {

        try {

            //get user id
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }


            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();
            $result = $vanHelper->vanManager([], '/cac/v1/searchUTR/' . $utr, $userId, 'GET', 'searchByUtr');


            if ($result['code'] == 200) {

                $cashfreeResponse = json_decode($result['response']);

                if ($cashfreeResponse->subCode === "200") {

                    $response = [
                        "amount" => $cashfreeResponse->data->payment->amount,
                        "referenceId" => $cashfreeResponse->data->payment->referenceId,
                        "utr" => $cashfreeResponse->data->payment->utr,
                        "creditRefNo" => $cashfreeResponse->data->payment->creditRefNo,
                        "remitterAccount" => $cashfreeResponse->data->payment->remitterAccount,
                        "remitterName" => $cashfreeResponse->data->payment->remitterName,
                        "paymentTime" => $cashfreeResponse->data->payment->paymentTime,
                    ];

                    return ResponseHelper::success("UTR found successfully.", $response);
                } else if ($cashfreeResponse->subCode === "403") {
                    return ResponseHelper::failed('Something went wrong, try after some time.');
                }

                return ResponseHelper::failed($cashfreeResponse->message);
            }

            return ResponseHelper::failed("Search transaction by UTR Failed.");
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }



    /**
     * Get Merchant List
     */
    public function getMerchantList($type)
    {
        try {
            //get user id
            if (isset(request()->auth_data['user_id'])) {
                $userId = request()->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret(request()->header());
            }

            switch ($type) {
                case 'van':

                    $list = DB::table('cf_merchants')
                        ->where('service_type', 'van')
                        ->where('user_id', $userId)
                        ->where('van_status', '<>', '0')
                        ->get();

                    $data = [];

                    foreach ($list as $row) {
                        $key = $this->getVanBank($row->van_1_ifsc);
                        $data[] = [
                            'requestId' => $row->request_id,
                            'vanAccId' => $row->v_account_id,
                            'businessName' => $row->business_name,
                            'contactEmail' => strtolower($row->contact_email),
                            'mobile' => $row->mobile,
                            'panNo' => strtoupper($row->pan_no),
                            'van' => [
                                $key => [
                                    'accountNumber' => $row->van_1,
                                    'ifsc' => $row->van_1_ifsc,
                                ],
                                // 'IDFB' => [
                                //     'accountNumber' => $row->van_2,
                                //     'ifsc' => $row->van_2_ifsc,
                                // ]
                            ]
                        ];
                    }

                    break;

                case 'vpa':
                    $list = DB::table('cf_merchants')
                        ->where('service_type', 'upi')
                        ->where('user_id', $userId)
                        ->where(function ($sql) {
                            return $sql->where('van_status', '<>', '0')
                                ->orWhereNull('van_status');
                        })
                        ->get();

                    $data = [];

                    foreach ($list as $row) {
                        $key = $this->getVpaBank($row->vpa_1);
                        $data[] = [
                            'requestId' => $row->request_id,
                            'vpaAccId' => $row->v_account_id,
                            'businessName' => $row->business_name,
                            'contactEmail' => strtolower($row->contact_email),
                            'mobile' => $row->mobile,
                            'panNo' => strtoupper($row->pan_no),
                            'upi' => [
                                $key => [
                                    'vpa' => $row->vpa_1
                                ],
                                // 'icici' => [
                                //     'vpa' => $row->vpa_2
                                // ]
                            ]
                        ];
                    }

                    break;

                default:
                    return response('', 404);
            }


            return ResponseHelper::success("List of merchants", ['list' => $data, 'count' => count($data)]);
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * Create VPA
     */
    private function createVPA($request, $userId)
    {
        try {
            $vpaAddress = strtolower($request->input('vpaAddress')) . 'xttl';

            //create params
            $params = [
                "virtualVpaId" => $vpaAddress,
                "name" => $request->businessName,
                "phone" => "9876543210",
                "email" => "payments.cf@example.com",
            ];

            if ($this->isMultiUpi) {
                $params['createMultiple'] = 1;
            }

            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();

            $result = $vanHelper->vanManager($params, '/cac/v1/createVA', $userId, 'POST', 'autoCollect');

            if ($result['code'] == 200) {
                $cashfreeResponse = json_decode($result['response']);

                //when response is success
                if ($cashfreeResponse->subCode === "200") {

                    if ($this->isMultiUpi) {

                        $tempVanAccounts = [];

                        foreach ($cashfreeResponse->data as $row) {
                            $tempVanAccounts[] = $row;
                        }

                        $vpaResponse = [
                            'vpa_1' => $tempVanAccounts[0]->vpa,
                            'vpa_2' => $tempVanAccounts[1]->vpa,
                        ];
                    } else {

                        $vpaResponse = [
                            'vpa_1' => $cashfreeResponse->data->vpa,
                        ];
                    }


                    $requestId = CommonHelper::getRandomString('REQ', false);

                    $insertData = [
                        'request_id' => $requestId,
                        'user_id' => $userId,
                        'v_account_id' => $vpaAddress,
                        'van_status' => '1',
                        'business_name' => $request->businessName,
                        'pan_no' => $request->panNo,
                        'bank_account_no' => $request->bankAccountNo,
                        'bank_ifsc' => $request->bankIfsc,
                        'contact_email' => strtolower($request->contactEmail),
                        'gstn' => $request->gstn,
                        'mobile' => $request->mobile,
                        'address' => $request->address,
                        'state' => $request->state,
                        'city' => $request->city,
                        'pin_code' => $request->pinCode,
                        'service_type' => $request->serviceType,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];

                    $insertData = array_merge($insertData, $vpaResponse);

                    $rowId = DB::table('cf_merchants')->insertGetId($insertData);

                    $data['rowId'] = $rowId;
                    $data['user_id'] = $userId;
                    $data['identifier'] = 'smart_collect_vpa_fee';
                    $data['txnType'] = 'smart_collect';
                    $data['slug'] = 'vpa_create';
                    $data['requestId'] = $requestId;

                    //deduct VPA API Creation cost
                    PrimaryFundCredit::dispatch((object) $data, 'smart_collect_fee')->onQueue('primary_fund_queue');

                    if ($this->isMultiUpi) {
                        $userResponse = [
                            'requestId' => $requestId,
                            'businessName' => $request->businessName,
                            'vpaAccId' => $vpaAddress,
                            'upi' => $cashfreeResponse->data
                        ];
                    } else {
                        $key = $this->getVpaBank($insertData['vpa_1']);
                        $userResponse = [
                            'requestId' => $requestId,
                            'businessName' => $request->businessName,
                            'vpaAccId' => $vpaAddress,
                            'upi' => [
                                $key => [
                                    'vpa' => $insertData['vpa_1']
                                ]
                            ]
                        ];
                    }

                    return ResponseHelper::success("UPI created successfully", $userResponse);
                } else if ($cashfreeResponse->subCode == "412") {
                    return ResponseHelper::failed('Create Multiple Accounts feature not enabled.');
                } else if ($cashfreeResponse->subCode == "403") {
                    return ResponseHelper::failed('Something went wrong, try after some time.');
                } else if ($cashfreeResponse->subCode == "409") {
                    return ResponseHelper::failed('UPI address already exists.');
                }

                return ResponseHelper::failed($cashfreeResponse->message);
            }

            return ResponseHelper::failed("UPI Creation Failed.");
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }



    /**
     * Create VAN
     */
    private function createVAN($request, $userId)
    {
        try {
            $virtualAccId = CommonHelper::newVanApiAccNumber();


            //create params for VAN
            $params = [
                "vAccountId" => $virtualAccId,
                "name" => $request->businessName,
                "phone" => "9876543210",
                "email" => "payments.cf@example.com",
                "remitterAccount" => $request->bankAccountNo,
                "remitterIfsc" => strtoupper($request->bankIfsc),
            ];


            if ($this->isMultiVan) {
                $params['createMultiple'] = 1;
            }

            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();

            $result = $vanHelper->vanManager($params, '/cac/v1/createVA', $userId, 'POST', 'autoCollect');

            if ($result['code'] == 200) {
                $cashfreeResponse = json_decode($result['response']);


                //when response is success
                if ($cashfreeResponse->subCode === "200") {

                    if ($this->isMultiVan) {

                        $tempVanAccounts = [];

                        foreach ($cashfreeResponse->data as $row) {
                            $tempVanAccounts[] = $row;
                        }

                        $vanResponse = [
                            'van_1' => $tempVanAccounts[0]->accountNumber,
                            'van_1_ifsc' => $tempVanAccounts[0]->ifsc,
                            'van_2' => $tempVanAccounts[1]->accountNumber,
                            'van_2_ifsc' => $tempVanAccounts[1]->ifsc,
                        ];
                    } else {

                        $vanResponse = [
                            'van_1' => $cashfreeResponse->data->accountNumber,
                            'van_1_ifsc' => $cashfreeResponse->data->ifsc,
                        ];
                    }


                    $requestId = CommonHelper::getRandomString('REQ', false);

                    $insertData = [
                        'request_id' => $requestId,
                        'user_id' => $userId,
                        'van_acc_id' => $virtualAccId,
                        'v_account_id' => $virtualAccId,
                        'van_status' => '1',
                        'business_name' => $request->businessName,
                        'pan_no' => $request->panNo,
                        'bank_account_no' => $request->bankAccountNo,
                        'bank_ifsc' => strtoupper($request->bankIfsc),
                        'contact_email' => strtolower($request->contactEmail),
                        'gstn' => $request->gstn,
                        'mobile' => $request->mobile,
                        'address' => $request->address,
                        'state' => $request->state,
                        'city' => $request->city,
                        'pin_code' => $request->pinCode,
                        'service_type' => $request->serviceType,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];

                    $insertData = array_merge($insertData, $vanResponse);

                    $rowId = DB::table('cf_merchants')->insertGetId($insertData);


                    //deduct VAN API Creation cost
                    $data['rowId'] = $rowId;
                    $data['user_id'] = $userId;
                    $data['identifier'] = 'smart_collect_van_fee';
                    $data['txnType'] = 'smart_collect';
                    $data['slug'] = 'van_create';
                    $data['requestId'] = $requestId;

                    //deduct VAN API Creation cost
                    PrimaryFundCredit::dispatch((object) $data, 'smart_collect_fee')->onQueue('primary_fund_queue');


                    if ($this->isMultiVan) {
                        $userResponse = [
                            'requestId' => $requestId,
                            'businessName' => $request->businessName,
                            'vanAccId' => $virtualAccId,
                            'van' => $cashfreeResponse->data
                        ];
                    } else {
                        $key = $this->getVanBank($insertData['van_1_ifsc']);
                        $userResponse = [
                            'requestId' => $requestId,
                            'businessName' => $request->businessName,
                            'vanAccId' => $virtualAccId,
                            'van' => [
                                $key => [
                                    "accountNumber" => $insertData['van_1'],
                                    "ifsc" => $insertData['van_1_ifsc']
                                ]
                            ]
                        ];
                    }

                    return ResponseHelper::success("Virtual account created successfully", $userResponse);
                } else if ($cashfreeResponse->subCode == "412") {
                    return ResponseHelper::failed('Create Multiple Accounts feature not enabled.');
                } else if ($cashfreeResponse->subCode == "403") {
                    return ResponseHelper::failed('Something went wrong, try after some time.');
                }


                return ResponseHelper::failed($cashfreeResponse->message);
            }

            return ResponseHelper::failed("VAN Creation Failed.");
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Get Bank name of the VPA
     */
    private function getVpaBank($vpa)
    {
        return strtolower(substr($vpa, strpos($vpa, '@') + 1));
    }


    /**
     * Get VAN account Bank name
     */
    private function getVanBank($ifsc)
    {
        return strtoupper(substr($ifsc, 0, 4));
    }
}
