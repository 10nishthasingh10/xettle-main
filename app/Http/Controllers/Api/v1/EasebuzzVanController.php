<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\EasebuzzInstaCollectHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EasebuzzVanController extends  Controller
{

    /**
     * Generate VAN
     */
    public function generateVan(Request $request)
    {

        try {

            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'user_id' => "required|numeric|min:1",
                        // 'category_code' => "required|numeric",
                        // 'business_type_code' => "required|numeric",
                    ]
                );

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }

                // try {
                //     $userId = decrypt($request->user_id);
                // } catch (Exception $e) {
                //     //$message = $e->getMessage();
                //     return ResponseHelper::missing("Invalid token value.");
                // }

                $userId = $request->user_id;

                //fetching user details
                $userInfo = DB::table('users')->select('id', 'account_number')
                    ->where('is_active', '1')
                    ->find($userId);

                if (empty($userInfo)) {
                    // return ResponseHelper::failed("Invalid user ID.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "User is not Active.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                //fetching van and bank details
                $businessInfo = DB::table('business_infos')
                    ->select('*')
                    ->where('is_active', '1')
                    ->where('is_kyc_updated', '1')
                    ->where('user_id', $userId)
                    ->first();


                if (empty($businessInfo)) {
                    // return ResponseHelper::failed("KYC is pending.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "KYC is pending.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;

                    return $this->populateresponse();
                } else if (empty($businessInfo->business_name)) {
                    // return ResponseHelper::failed("Business Name is pending.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Business Name is pending.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                }


                $virtualAccount = DB::table('user_van_accounts')
                    ->select('id')
                    ->where('user_id', $userId)
                    ->where('root_type', 'eb_van')
                    ->first();

                if (!empty($virtualAccount)) {
                    // return ResponseHelper::failed("Virtual account already generated.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Virtual account already generated.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                $virtualAccId = EasebuzzInstaCollectHelper::VAN_PREFIX . substr($userInfo->account_number, -6);

                $userBankInfo = DB::table('user_bank_infos')
                    ->select('*')
                    ->where('is_active', '1')
                    ->where('is_verified', '1')
                    ->where('user_id', $userId)
                    ->first();

                if (empty($userBankInfo)) {
                    // if ($userBankInfo->isEmpty()) {
                    // return ResponseHelper::failed("Bank accounts are not updated yet.");

                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Bank accounts are not updated yet.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                // $count = 0;
                // $userBanks = [];
                // foreach ($userBankInfo as $row) {
                //     if ($count < 1) {
                //         $userBanks[] = [
                //             "account_number" => $row->account_number,
                //             "account_ifsc" => $row->ifsc
                //         ];
                //         $count++;
                //     }
                // }


                $userBanks[] = [
                    "account_number" => $userBankInfo->account_number,
                    "account_ifsc" => $userBankInfo->ifsc
                ];

                //fetching state name
                $state = DB::table('states')
                    ->select('state_name')
                    ->where('id', $businessInfo->state)
                    ->first();

                if (empty($state)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Fetching state info failed.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $vanHelper = new EasebuzzInstaCollectHelper();

                $params = [
                    "key" => $vanHelper->getKey(),
                    "label" => $businessInfo->business_name,
                    "virtual_account_number" => $virtualAccId,
                    "virtual_payment_address" => 'xettle' . $virtualAccId,
                    "authorized_remitters" => $userBanks,
                    "kyc_flow" => true,
                    "profile" => [
                        "email" => "payments.eb@example.com", //strtolower($businessInfo->email),
                        "phone" => "9999999999", //strval($businessInfo->mobile),
                        "business_name" => $businessInfo->business_name,
                        "account_number" => $userBankInfo->account_number, //$businessInfo->account_number,
                        "account_ifsc" => $userBankInfo->ifsc, //$businessInfo->ifsc,
                        "name_on_bank" => $userBankInfo->beneficiary_name, //$businessInfo->beneficiary_name,
                        "category_code" => $businessInfo->mcc, //$request->category_code, //4816, //$businessInfo->mcc,
                        "pan_number" => !empty($businessInfo->business_pan) ? $businessInfo->business_pan : $businessInfo->pan_number,
                        "business_type_code" => $vanHelper->getBusinessTypeCode($businessInfo->business_type), //$request->business_type_code, //41, //$businessInfo->business_type,
                        "business_address" => preg_replace('/[^A-Za-z0-9 \,]+/', '', $businessInfo->address),
                        "gstin" => $businessInfo->gstin,
                        "city" => $businessInfo->city,
                        "state" => $state->state_name, //$businessInfo->state,
                        "pincode" => strval($businessInfo->pincode),
                    ]
                ];

                // dd($params);

                $authParams[] = $params['label'];

                $result = $vanHelper->apiCaller($params, '/insta-collect/virtual_accounts/', $authParams, Auth::user()->id);

                if ($result['code'] == 200) {

                    $apiResponse = json_decode($result['response']);

                    //when response is success
                    if (!empty($apiResponse->success)) {

                        if ($apiResponse->success == true) {
                            DB::table('user_van_accounts')->insert([
                                'root_type' => 'eb_van',
                                'user_id' => $userId,
                                'account_holder_name' => $apiResponse->data->virtual_account->label,
                                'account_number_prefix' => $virtualAccId,
                                'account_id' => $apiResponse->data->virtual_account->id,
                                'account_number' => $apiResponse->data->virtual_account->virtual_account_number,
                                'ifsc' => $apiResponse->data->virtual_account->virtual_ifsc_number,
                                'vpa_address' => $apiResponse->data->virtual_account->virtual_upi_handle,
                                'authorized_remitters' => json_encode($apiResponse->data->virtual_account->authorized_remitters),
                                'status' => ($apiResponse->data->virtual_account->is_active == true) ? '1' : '0',
                                'created_at' => date('Y-m-d H:i:s')
                            ]);

                            // return ResponseHelper::success("VAN created successfully.");
                            $this->status = true;
                            $this->modal = true;
                            $this->alert = true;
                            $this->message = "VAN created successfully.";
                            $this->title = "Ebz Partner VAN";
                            $this->redirect = true;
                            return $this->populateresponse();
                        }
                    }

                    // return ResponseHelper::failed("VAN Creation Failed.", $apiResponse);
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "VAN Creation Failed.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                } else if (!empty($result['response'])) {
                    $apiResponse = json_decode($result['response']);
                    $message = isset($apiResponse->message) ? $apiResponse->message : "Something went wrong.";
                } else {
                    $message =  "Something going wrong.";
                }

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => $message);
                $this->title = 'Ebz Partner VAN';
                $this->redirect = false;
                return $this->populateresponse();
            } else {
                return abort('401');
            }
        } catch (Exception $e) {
            //$message = $e->getMessage();
            // return ResponseHelper::missing("Error: " . $e->getMessage());
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            $this->title = 'Ebz Partner VAN';
            $this->redirect = false;
            return $this->populateresponse();
        }
    }

    public function generateInfo(Request $request)
    {

        try {

            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'user_id' => "required|numeric|min:1",
                        // 'category_code' => "required|numeric",
                        // 'business_type_code' => "required|numeric",
                    ]
                );

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }

                $userId = $request->user_id;

                //fetching user details
                $userInfo = DB::table('users')->select('id', 'account_number')
                    ->where('is_active', '1')
                    ->find($userId);

                if (empty($userInfo)) {
                    // return ResponseHelper::failed("Invalid user ID.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "User is not Active.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                //fetching van and bank details
                $businessInfo = DB::table('business_infos')
                    ->select('*')
                    ->where('is_active', '1')
                    ->where('is_kyc_updated', '1')
                    ->where('user_id', $userId)
                    ->first();


                if (empty($businessInfo)) {
                    // return ResponseHelper::failed("KYC is pending.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "KYC is pending.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;

                    return $this->populateresponse();
                } else if (empty($businessInfo->business_name)) {
                    // return ResponseHelper::failed("Business Name is pending.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Business Name is pending.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                }


                $virtualAccount = DB::table('user_van_accounts')
                    ->select('id')
                    ->where('user_id', $userId)
                    ->where('root_type', 'eb_van')
                    ->first();

                if (!empty($virtualAccount)) {
                    // return ResponseHelper::failed("Virtual account already generated.");
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Virtual account already generated.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                $virtualAccId = EasebuzzInstaCollectHelper::VAN_PREFIX . substr($userInfo->account_number, -6);

                $userBankInfo = DB::table('user_bank_infos')
                    ->select('*')
                    ->where('is_active', '1')
                    ->where('is_verified', '1')
                    ->where('user_id', $userId)
                    ->first();

                if (empty($userBankInfo)) {
                    // if ($userBankInfo->isEmpty()) {
                    // return ResponseHelper::failed("Bank accounts are not updated yet.");

                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Bank accounts are not updated yet.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                $userBanks[] = [
                    "account_number" => $userBankInfo->account_number,
                    "account_ifsc" => $userBankInfo->ifsc
                ];

                //fetching state name
                $state = DB::table('states')
                    ->select('state_name')
                    ->where('id', $businessInfo->state)
                    ->first();

                if (empty($state)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Fetching state info failed.");
                    $this->title = 'Ebz Partner VAN';
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $params = [
                    // "key" => $vanHelper->getKey(),
                    "user_id" => $userId,
                    "label" => $businessInfo->business_name,
                    "virtual_account_number" => $virtualAccId,
                    "virtual_payment_address" => 'xettle' . $virtualAccId,
                    "authorized_remitters" => $userBanks,
                    "kyc_flow" => true,
                    "profile" => [
                        "email" => "payments.eb@example.com", //strtolower($businessInfo->email),
                        "phone" => "9999999999", //strval($businessInfo->mobile),
                        "business_name" => $businessInfo->business_name,
                        "account_number" => $userBankInfo->account_number, //$businessInfo->account_number,
                        "account_ifsc" => $userBankInfo->ifsc, //$businessInfo->ifsc,
                        "name_on_bank" => $userBankInfo->beneficiary_name, //$businessInfo->beneficiary_name,
                        "category_code" => $businessInfo->mcc, //$request->category_code, //4816, //$businessInfo->mcc,
                        "pan_number" => !empty($businessInfo->business_pan) ? $businessInfo->business_pan : $businessInfo->pan_number,
                        "business_type_code" => EasebuzzInstaCollectHelper::getBusinessTypeCode($businessInfo->business_type), //$request->business_type_code, //41, //$businessInfo->business_type,
                        "business_address" => preg_replace('/[^A-Za-z0-9 \,]+/', '', $businessInfo->address),
                        "gstin" => $businessInfo->gstin,
                        "city" => $businessInfo->city,
                        "state" => $state->state_name, //$businessInfo->state,
                        "pincode" => strval($businessInfo->pincode),
                    ]
                ];

                $this->status = true;
                $this->modal = false;
                $this->alert = false;
                $this->modalStatus = false;
                $this->message = "VAN Info fetched.";
                $this->title = "Ebz Partner VAN";
                $this->redirect = false;
                $this->jsondata = $params;
                return $this->populateresponse();
            } else {
                return abort('401');
            }
        } catch (Exception $e) {
            //$message = $e->getMessage();
            // return ResponseHelper::missing("Error: " . $e->getMessage());
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            $this->title = 'Ebz Partner VAN';
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Update VAN Status
     */
    public function updateVanInfo(Request $request)
    {
        try {

            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'vId' => "required",
                    ]
                );

                if ($validator->fails()) {
                    $message = ($validator->errors()->get('vId'));
                    return ResponseHelper::missing($message);
                }

                try {
                    $id = decrypt($request->vId);
                } catch (Exception $e) {
                    //$message = $e->getMessage();
                    return ResponseHelper::missing("Invalid token value.");
                }


                $virtualAccount = DB::table('user_van_accounts')
                    ->select('id', 'user_id', 'account_holder_name', 'account_id', 'status')
                    ->where('id', $id)
                    ->first();

                if (empty($virtualAccount)) {
                    return ResponseHelper::failed("Virtual account not generated yet.");
                }


                $userBankInfo = DB::table('user_bank_infos')
                    ->select('*')
                    ->where('is_active', '1')
                    ->where('is_verified', '1')
                    ->where('user_id', $virtualAccount->user_id)
                    ->get();


                if ($userBankInfo->isEmpty()) {
                    return ResponseHelper::failed("Bank accounts are not updated yet.");
                }

                $count = 0;
                $userBanks = [];
                foreach ($userBankInfo as $row) {
                    if ($count < 3) {
                        $userBanks[] = [
                            "account_number" => $row->account_number,
                            "account_ifsc" => $row->ifsc
                        ];
                        $count++;
                    }
                }


                $vanHelper = new EasebuzzInstaCollectHelper();

                $params = [
                    "key" => $vanHelper->getKey(),
                    "label" => $virtualAccount->account_holder_name,
                    // "virtual_account_number" => $virtualAccId,
                    // "virtual_payment_address" => $virtualAccId,
                    "authorized_remitters" => $userBanks,
                ];

                $authParams[] = $virtualAccount->account_id;
                $authParams[] = $virtualAccount->account_holder_name;

                $result = $vanHelper->apiCaller($params, "/insta-collect/virtual_accounts/{$virtualAccount->account_id}/", $authParams, Auth::user()->id, 'PUT');


                if ($result['code'] == 200) {

                    $apiResponse = json_decode($result['response']);

                    //when response is success
                    if (!empty($apiResponse->success)) {

                        if ($apiResponse->success == true) {
                            DB::table('user_van_accounts')
                                ->where('id', $virtualAccount->id)
                                ->where('account_id', $apiResponse->data->virtual_account->id)
                                ->update([
                                    'account_holder_name' => $apiResponse->data->virtual_account->label,
                                    'authorized_remitters' => json_encode($apiResponse->data->virtual_account->authorized_remitters),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);

                            return ResponseHelper::success("VAN updated successfully.");
                        }
                    }

                    return ResponseHelper::failed("VAN updation Failed.", $apiResponse);
                }

                return ResponseHelper::failed("VAN Status Update Failed.", $result);
            } else {
                return abort('401');
            }
        } catch (Exception $e) {
            //$message = $e->getMessage();
            return ResponseHelper::missing("Error: " . $e->getMessage());
        }
    }


    /**
     * Update VAN Status
     */
    public function updateVanStatus(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {

            try {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'vId' => "required",
                    ]
                );

                if ($validator->fails()) {
                    $message = ($validator->errors()->get('vId'));
                    return ResponseHelper::missing($message);
                }

                try {
                    $id = decrypt($request->vId);
                } catch (Exception $e) {
                    //$message = $e->getMessage();
                    return ResponseHelper::missing("Invalid token value.");
                }


                $virtualAccount = DB::table('user_van_accounts')
                    ->select('id', 'account_id', 'status')
                    ->where('id', $id)
                    ->first();

                if (empty($virtualAccount)) {
                    return ResponseHelper::failed("Virtual account not generated yet.");
                }


                $status = ($virtualAccount->status === '0') ? true : false;


                $vanHelper = new EasebuzzInstaCollectHelper();

                $params = [
                    "key" => $vanHelper->getKey(),
                    "is_active" => $status
                ];

                $authParams[] = $virtualAccount->account_id;

                $result = $vanHelper->apiCaller($params, "/insta-collect/virtual_accounts/{$virtualAccount->account_id}/update_status/", $authParams, Auth::user()->id);

                if ($result['code'] == 200) {

                    $apiResponse = json_decode($result['response']);

                    //when response is success
                    if (!empty($apiResponse->success)) {

                        if ($apiResponse->success == true) {

                            DB::table('user_van_accounts')
                                ->where('id', $virtualAccount->id)
                                ->where('account_id', $apiResponse->data->virtual_account->id)
                                ->update([
                                    'status' => ($apiResponse->data->virtual_account->is_active == true) ? '1' : '0',
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);

                            return ResponseHelper::success("VAN status updated successfully.", ['status' => ($apiResponse->data->virtual_account->is_active == true) ? 'ACTIVE' : 'INACTIVE']);
                        }
                    }

                    return ResponseHelper::failed($apiResponse->message, $apiResponse);
                }

                return ResponseHelper::failed("VAN Status Update Failed.", $result);
            } catch (Exception $e) {
                //$message = $e->getMessage();
                return ResponseHelper::missing("Error: " . $e->getMessage());
            }
        } else {
            return abort('401');
        }
    }
}
