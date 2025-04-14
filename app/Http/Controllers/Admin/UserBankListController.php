<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonHelper;
use App\Helpers\InstantPayHelper;
use App\Helpers\PennyDropApiHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserBankListController extends Controller
{
    /**
     * View for List to show Users Bank Account 
     */
    public function bankListView($id = null)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {
                $data['page_title'] =  "Users Bank";
                $data['site_title'] =  "Users Bank List";
                $data['view']       = ADMIN . ".user_bank_list";

                $data['userList'] = DB::table('users')->select('id', 'name', 'email')
                    ->where('is_admin', '0')
                    ->orderBy('id', 'ASC')
                    ->get();

                if (!empty($id)) {
                    $id = decrypt($id);

                    $data['userBanks'] = DB::table('user_bank_infos')
                        ->where('id', $id)
                        ->where('is_verified', '0')
                        ->first();

                    if (empty($data['userBanks'])) {
                        return abort(404);
                    }

                    $data['userId'] = $data['userBanks']->user_id;
                    $data['page_title'] =  "Update Bank Info";
                }

                return view($data['view'])->with($data);
            } else {
                $data['url'] = url('admin/dashboard');
                return view('errors.401')->with($data);
            }
        } catch (Exception $e) {
            return view('errors.401');
        }
    }


    /**
     * Add new users banks
     */
    public function addNewBanks(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {
                $userId = !empty($request->user_id) ? decrypt($request->user_id) : '';
                $validator = Validator::make(
                    $request->all(),
                    [
                        'user_id' => "required",
                        'beneficiary_name.*' => "required|min:3|max:30",
                        'ifsc.*' => "required|size:11|regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/",
                        'account_number.*' => [
                            'required',
                            'digits_between:8,20',
                            function ($attr, $val, $fail) use ($userId) {
                                $count = DB::table('user_bank_infos')
                                    ->where('account_number', $val)
                                    ->where('user_id', $userId)
                                    ->count();

                                if ($count > 0) {
                                    $fail('Account Number is already added');
                                }
                            },
                            function ($attr, $val, $fail) use ($request) {
                                if (count($request->account_number) !== count(array_unique($request->account_number))) {
                                    $fail('Account Numbers are duplicate');
                                }
                            }
                        ]
                    ],
                    [
                        'required' => "",
                        'size' => 'Must be 11 characters',
                        'regex' => 'Format is invalid',
                        'min' => 'Must be at least 5 characters',
                        'max' => 'Not be greater than 30 characters'

                    ]
                );


                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }


                DB::beginTransaction();

                $timestamp = date('Y-m-d H:i:s');

                for ($i = 0; $i < count($request->beneficiary_name); $i++) {

                    // $reqId = CommonHelper::getRandomString('REQN', false);

                    $tempInArr[$i] = [
                        'user_id' => $userId,
                        'beneficiary_name' => $request->beneficiary_name[$i],
                        'account_number' => $request->account_number[$i],
                        'ifsc' => strtoupper($request->ifsc[$i]),
                        'is_active' => '0',
                        'is_verified' => '0',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                $in = DB::table('user_bank_infos')->insert($tempInArr);

                if ($in) {
                    DB::commit();

                    $this->status_code = '200';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "Users bank added successfully";
                    $this->title = "User Banks";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::failed("Exception: " . $e->getMessage());
        }
    }


    /**
     * Get User Bank Info
     */
    public function getUserBank(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'rowId' => "required",
                    ],
                );


                if ($validator->fails()) {
                    // $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing("Bank ID is required.");
                }


                $rowId = decrypt($request->rowId);

                $bankInfo = DB::table('user_bank_infos')
                    ->select('account_number', 'ifsc', 'beneficiary_name', 'user_id')
                    ->where('is_verified', '0')
                    ->find($rowId);

                if (empty($bankInfo)) {
                    return ResponseHelper::failed("Bank ID is not available.");
                }

                $bankInfo->user_id = encrypt($bankInfo->user_id);


                return ResponseHelper::success('SUCCESS', $bankInfo);
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::failed($e->getMessage());
        }
    }

    /**
     * Update Bank Info
     */
    public function updateBanksInfo(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $userId = !empty($request->user_id) ? decrypt($request->user_id) : '';
                $rowId = !empty($request->row_id) ? decrypt($request->row_id) : '';

                $validator = Validator::make(
                    $request->all(),
                    [
                        'user_id' => "required",
                        'row_id' => "required",
                        'beneficiary_name' => "required|max:100|regex:/^([a-zA-Z0-9 ]+)$/",
                        'account_number' => "required|digits_between:8,20|regex:/^(\d)*$/",
                        'ifsc' => "required|size:11|regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/",
                    ],
                );

                $validator->after(function ($validator) use ($request, $userId, $rowId) {
                    $count = DB::table('user_bank_infos')
                        ->where('account_number', $request->account_number)
                        ->where('ifsc', strtoupper(trim($request->ifsc)))
                        ->where('user_id', $userId)
                        ->where('id', '<>', $rowId)
                        ->count();

                    if ($count > 0) {
                        $validator->errors()->add('account_number', 'Account Number is already added');
                    }
                });

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }


                $timestamp = date('Y-m-d H:i:s');

                DB::beginTransaction();


                $tempInArrUpdate = [
                    'id' => ($rowId),
                    'beneficiary_name' => trim($request->beneficiary_name),
                    'account_number' => $request->account_number,
                    'ifsc' => strtoupper($request->ifsc),
                    // 'is_active' => '1',
                    // 'is_verified' => '1',
                    'updated_at' => $timestamp,
                ];

                DB::table('user_bank_infos')->upsert($tempInArrUpdate, ['id']);


                DB::commit();

                $this->status_code = '200';
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = "Bank Account info updated successfully";
                $this->title = "User Banks";
                $this->redirect = false;
                return $this->populateresponse();
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::failed($e->getMessage());
        }
    }



    /**
     * Update Bank Status
     */
    public function updateBankStatus(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'vId' => "required"
                    ]
                );

                if ($validator->fails()) {
                    $message = ($validator->errors()->get('vId'));
                    return ResponseHelper::missing($message);
                }

                $vId = decrypt($request->vId);

                $bankInfo = DB::table('user_bank_infos')
                    ->select('is_active', 'is_verified')
                    ->find($vId);

                if (empty($bankInfo)) {
                    return ResponseHelper::failed("Bank info not found.");
                } else if ($bankInfo->is_verified !== '1') {
                    return ResponseHelper::failed("Bank is not verified yet.");
                }

                if ($bankInfo->is_active === '0') {
                    $isActive = '1';
                    $status = 'Active';
                } else {
                    $isActive = '0';
                    $status = 'Inactive';
                }

                $timestamp = date('Y-m-d H:i:s');

                $bankInfo = DB::table('user_bank_infos')
                    ->where('id', $vId)
                    ->update(['is_active' => $isActive, 'updated_at' => $timestamp]);

                if ($bankInfo) {
                    return ResponseHelper::success('Bank status updated.', ['status' => $status]);
                } else {
                    return ResponseHelper::failed("Bank info updation failed.");
                }
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return ResponseHelper::failed($e->getMessage());
        }
    }


    /**
     * Update Bank Status
     */
    public function updatePrimaryStatus(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'vId' => "required",
                        'uId' => "required"
                    ]
                );

                if ($validator->fails()) {
                    // $message = ($validator->errors()->get('vId'));
                    return ResponseHelper::missing();
                }

                $vId = decrypt($request->vId);
                $uId = decrypt($request->uId);

                $bankInfo = DB::table('user_bank_infos')
                    ->select('is_primary', 'is_verified')
                    ->find($vId);

                if (empty($bankInfo)) {
                    return ResponseHelper::failed("Bank info not found.");
                } else if ($bankInfo->is_verified !== '1') {
                    return ResponseHelper::failed("Bank is not verified yet.");
                } elseif ($bankInfo->is_primary === '1') {
                    return ResponseHelper::failed("You can't change primary account.");
                }


                if ($bankInfo->is_primary === '0') {
                    $isActive = '1';
                    $status = 'Primary';
                }
                // else {
                //     $isActive = '0';
                //     $status = 'Not Primary';
                // }

                $timestamp = date('Y-m-d H:i:s');

                $bankInfo = DB::table('user_bank_infos')
                    ->where('id', $vId)
                    ->update(['is_primary' => $isActive, 'updated_at' => $timestamp]);

                if ($bankInfo) {

                    if ($isActive === '1') {
                        DB::table('user_bank_infos')
                            ->where('user_id', $uId)
                            ->where('id', '!=', $vId)
                            ->update(['is_primary' => '0', 'updated_at' => $timestamp]);
                    }

                    return ResponseHelper::success('Bank marked as primary.', ['status' => $status]);
                } else {
                    return ResponseHelper::failed("Bank info updation failed.");
                }
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return ResponseHelper::failed($e->getMessage());
        }
    }


    /**
     * request verify bank account
     */
    public function verifyBankAccount(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'user_bank_id' => "required"
                    ]
                );

                if ($validator->fails()) {
                    $message = ($validator->errors()->get('user_bank_id'));
                    return ResponseHelper::missing($message);
                }

                $vId = decrypt($request->user_bank_id);

                $bankInfo = DB::table('user_bank_infos')
                    ->select('*')
                    ->find($vId);

                if (empty($bankInfo)) {
                    return ResponseHelper::failed("Bank info not found.");
                }

                if ($bankInfo->is_verified === '1') {
                    return ResponseHelper::failed("Bank Account already verified.");
                }

                // $pennyDrop = new PennyDropApiHelper();
                // $result = $pennyDrop->initPennyDropEb($bankInfo);

                // if ($result['code'] == 200) {

                //     $apiResponse = json_decode($result['response']);

                //     if (!empty($apiResponse->success)) {
                //         if (!empty($apiResponse->data)) {

                //             $isValid = isset($apiResponse->data->is_valid) ? $apiResponse->data->is_valid : false;

                //             if ($isValid && $result['flag'] === 'api') {
                //                 $inData = [
                //                     'root_type' => $result['root_type'],
                //                     'account_no' => $apiResponse->data->account_number,
                //                     'ifsc' => $apiResponse->data->ifsc,
                //                     'response' => json_encode($apiResponse),
                //                     'created_at' => date('Y-m-d H:i:s'),
                //                     'updated_at' => date('Y-m-d H:i:s')
                //                 ];

                //                 DB::table('acc_validation_logs')->insert($inData);
                //             }

                //             $retData = [
                //                 'isValid' => $isValid,
                //                 'accountName' => $apiResponse->data->account_name,
                //                 'accountNo' => $apiResponse->data->account_number,
                //                 'ifsc' => $apiResponse->data->ifsc,
                //                 'token' => encrypt($apiResponse->data->account_number)
                //             ];

                //             return ResponseHelper::success('Bank verify response.', $retData);
                //         }
                //     } else {
                //         $msg = "Bank verification error." . isset($apiResponse->message) ? $apiResponse->message : '';
                //         return ResponseHelper::failed($msg);
                //     }
                // }

                $bankInfoDb = PennyDropApiHelper::getBankInfo(
                    $bankInfo->account_number,
                    $bankInfo->ifsc,
                    PennyDropApiHelper::ROOT_IPAY
                );

                $record2Db = true;

                if (!empty($bankInfoDb)) {
                    $result = $bankInfoDb;
                    $record2Db = false;
                    $refNo = $bankInfoDb['refNo'];
                } else {
                    $refNo = CommonHelper::getRandomString('IPYPD');
                    $instantPay = new InstantPayHelper();
                    $result = $instantPay->verifyBankAccount($bankInfo, $refNo, Auth::user()->id);
                }

                // $refNo = CommonHelper::getRandomString('IPYPD');
                // $instantPay = new InstantPayHelper();
                // $result = $instantPay->verifyBankAccount($bankInfo, $refNo, Auth::user()->id);

                if ($result['code'] == 200) {

                    $apiResponse = json_decode($result['response']);

                    $statusCode = @$apiResponse->statuscode;
                    $externalRef = @$apiResponse->data->externalRef;
                    $message = @$apiResponse->status;

                    $bankStatus = '';
                    $nameAtBank = '';

                    if ($statusCode === 'TXN' && $externalRef == $refNo) {

                        $bankStatus = 'valid';
                        $nameAtBank = @$apiResponse->data->payee->name;
                        $accountNumber = @$apiResponse->data->payee->account;

                        if ($record2Db) {
                            $inData = [
                                'root_type' => PennyDropApiHelper::ROOT_IPAY,
                                'ref_no' => $refNo,
                                'user_id' => $bankInfo->user_id,
                                'account_no' => $bankInfo->account_number,
                                'ifsc' => $bankInfo->ifsc,
                                'beneficiary_name' => $nameAtBank,
                                'status' => $bankStatus,
                                'response' => json_encode($apiResponse),
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];

                            DB::table('acc_validation_logs')->insert($inData);
                        }

                        $retData = [
                            'isValid' => true,
                            'accountName' => $nameAtBank,
                            'accountNo' => $accountNumber,
                            'ifsc' => $bankInfo->ifsc,
                            'token' => encrypt($bankInfo->account_number)
                        ];

                        return ResponseHelper::success('Bank verify response.', $retData);
                    } else {
                        $msg = "Error: " . $message;
                        return ResponseHelper::failed($msg);
                    }
                }

                return ResponseHelper::failed("Bank verification API Error. Error Code: " . $refNo);
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return ResponseHelper::failed("Error: " . $e->getMessage());
        }
    }


    /**
     * request verify bank account
     */
    public function verifyBankAccountApprove(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'acc_id' => "required",
                        'acc_token' => "required",
                        'acc_holder_name' => "required"
                    ]
                );

                if ($validator->fails()) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Some parameters are missing.");
                    $this->title = "Account Verification";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $vId = decrypt($request->acc_id);
                $userAccountNumber = decrypt($request->acc_token);
                $accHolderName = $request->acc_holder_name;

                $bankInfo = DB::table('user_bank_infos')
                    ->select('*')
                    ->where('id', $vId)
                    ->where('account_number', $userAccountNumber)
                    ->first();

                if (empty($bankInfo)) {
                    // return ResponseHelper::failed("Bank info not found.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Invalid Bank Account Number.");
                    $this->title = "Account Verification";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                if ($bankInfo->is_verified === '1') {
                    // return ResponseHelper::failed("Bank Account already verified.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Bank Account already verified.");
                    $this->title = "Account Verification";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $update = DB::table('user_bank_infos')
                    ->where('id', $vId)
                    ->update(['beneficiary_name' => $accHolderName, 'is_verified' => '1']);

                if (empty($update)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Something went wrong.");
                    $this->title = "Account Verification";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = "Bank Account verified successfully.";
                $this->title = "Account Verification";
                $this->redirect = false;
                return $this->populateresponse();
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return ResponseHelper::failed($e->getMessage());
        }
    }


    /**
     * Delete
     */
    public function deleteActions(Request $request, $action)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {
                $delete = 0;

                $validator = Validator::make(
                    $request->all(),
                    [
                        'bank_id' => "required"
                    ]
                );


                if ($validator->fails()) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => $validator->errors()->get('bank_id'));
                    $this->title = "Delete Action";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $bankId = decrypt($request->bank_id);

                $delete = DB::table('user_bank_infos')
                    ->where('id', $bankId)
                    ->where('is_verified', '0')
                    ->delete();

                if ($delete) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "Bank Info Deleted Successfully";
                    $this->title = "Delete Action";
                    $this->redirect = false;
                    return $this->populateresponse();
                } else {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Verified account can't be deleted.");
                    $this->title = "Delete Action";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return ResponseHelper::failed($e->getMessage());
        }
    }



    /**
     * Payout Report For All Users and Total amount filtered by date range
     */
    public function reportsAll(Request $request, $service, $userId = null)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $request['return'] = 'all';
            $request->orderIdArray = [];
            $request->serviceIdArray = [];
            $request->userIdArray = [];
            $request['returnType'] = 'all';
            // $parentData = session('parentData');
            $request['where'] = 0;


            $toDate = $fromDate = date('Y-m-d');

            if (!empty($request->from)) {
                $fromDate = $request->from;
            }

            if (!empty($request->to)) {
                $toDate = $request->to;
            }

            switch ($service) {

                case 'bank-info':
                    $searchData = ['users.name', 'users.email', 'users.mobile', 'user_bank_infos.beneficiary_name', 'user_bank_infos.account_number', 'user_bank_infos.ifsc', 'user_bank_infos.is_active', 'user_bank_infos.is_verified'];
                    $sqlQuery = DB::table('user_bank_infos')
                        ->select(
                            'user_bank_infos.id',
                            'user_bank_infos.user_id',
                            'user_bank_infos.beneficiary_name',
                            'user_bank_infos.ifsc',
                            'user_bank_infos.account_number',
                            'user_bank_infos.is_active',
                            'user_bank_infos.is_verified',
                            'user_bank_infos.is_primary',
                            'users.name',
                            'users.email',
                            'users.mobile',
                        )
                        ->leftJoin('users', 'user_bank_infos.user_id', 'users.id');

                    if (!empty($userId)) {
                        $sqlQuery->where('user_bank_infos.user_id', trim($userId));
                    } else if (!empty($request->user_id)) {
                        $sqlQuery->where('user_bank_infos.user_id', $request->user_id);
                    }

                    // $sqlQuery->orderBy('users.name', 'ASC');
                    break;

                default:
                    return abort(404);
                    break;
            }


            if (!empty($request->search['value'])) {
                $searchValue = trim($request->search['value']);
                $sqlQuery->where(function ($sql) use ($searchValue, $searchData) {
                    foreach ($searchData as $value) {
                        $sql->orWhere($value, 'like', '%' . $searchValue . '%');
                    }
                });
            }

            if (!empty($request->order[0]['column'])) {
                $filterColumn = $request->columns[$request->order[0]['column']]['data'];
                $orderBy = $request->order[0]['dir'];
                $sqlQuery->orderBy($filterColumn, $orderBy);
            } else {
                $sqlQuery->orderBy('users.id', 'ASC');
            }


            $sqlQueryCount = $sqlQuery;
            $sqlQueryCount = $sqlQueryCount->get();

            if ($request['length'] != -1) {
                $sqlQuery->skip($request->start)->take($request->length);
            }
            $result = $sqlQuery->get();

            if ($result->isNotEmpty()) {
                foreach ($result as $row) {
                    $row->id = encrypt($row->id);
                    $row->user_id = encrypt($row->user_id);
                }
            }

            if ($request->return == "all") {
                $json_data = array(
                    "draw"            => intval($request['draw']),
                    "recordsTotal"    => intval(count($sqlQueryCount)),
                    "recordsFiltered" => intval(count($sqlQueryCount)),
                    "data"            => $result,
                    "from_date" => $fromDate,
                    "to_date" => $toDate,
                    "start" => $request->start,
                    "length" => $request->length,
                );
                echo json_encode($json_data);
            } else {
                return response()->json($result);
            }
        } else {
            return abort(404);
        }
    }
}
