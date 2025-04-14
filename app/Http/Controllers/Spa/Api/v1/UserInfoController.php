<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class UserInfoController extends Controller
{

    /**
     * Fetch user manager information
     */
    public function userManager(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $managerInfo = DB::table('business_infos')
                ->select('account_managers.name', 'account_managers.mobile', 'account_managers.email')
                ->leftJoin('account_managers', 'account_managers.id', '=', 'business_infos.acc_manager_id')
                ->where('business_infos.user_id', $userId)
                ->first();

            if (!empty($managerInfo)) {
                return ResponseHelper::success('User manager info found.', $managerInfo);
            }

            return ResponseHelper::failed('No manager info found.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }


    /**
     * Fetch Primary wallet balance
     */
    public function primaryBalance(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $primaryBalance = DB::table('users')
                ->select('transaction_amount')
                ->where('id', $userId)
                ->first();

            if (!empty($primaryBalance)) {
                $balance = round($primaryBalance->transaction_amount, 2);
            } else {
                $balance = 0;
            }

            return ResponseHelper::success('Primary balance info', ['balance' => $balance]);
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }



    /**
     * Internal fund transfer
     */
    public function transferAmount(Request $request)
    {
        try {

            $errorMessage = 'Something wrong!!!';

            $validator = Validator::make(
                $request->all(),
                [
                    'amount' => 'required|numeric',
                    'service' => 'required',
                    'remark'
                ]
            );

            // $id = !(empty($request->user_id)) ? decrypt($request->user_id) : '';
            // $serviceId = !(empty($request->service_id)) ? decrypt($request->service_id) : '';

            // $validation = new UserValidation($request);
            // $validator = $validation->transferAmount();

            $validator->after(function ($validator) use ($request) {

                $userId = $request->user()->id;
                $serviceId = trim($request->service);

                $globalInternalTransfer = DB::table('global_config')
                    ->select('attribute_1')
                    ->where('slug', 'internal_tranfer_enable')
                    ->first()->attribute_1;

                if ($globalInternalTransfer == '0') {
                    // $errorMessage = 'Internal transfer down for some time.';
                    return ResponseHelper::failed('Internal transfer down for some time.');
                    // $validator->errors()->add('service', 'Internal transfer down for some time.');
                }


                $user = DB::table('users')->where('id', $userId)->first();

                if (empty($user)) {
                    // $validator->errors()->add('transfer_amount', 'Invalid user');
                    return ResponseHelper::failed('Invalid user');
                } else {
                    if ($user->is_active != '1') {
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        // $validator->errors()->add('transfer_amount', $message);

                        return ResponseHelper::failed($message);
                    } else {

                        $userServices = DB::table('user_services')
                            ->where('user_id', $userId)
                            ->whereIn('service_id', [RECHARGE_SERVICE_ID, VALIDATE_SERVICE_ID, DMT_SERVICE_ID])
                            ->where('id', $serviceId)
                            ->where('is_active', '1')->first();

                        if (empty($userServices)) {
                            // $validator->errors()->add('service', 'Service account not active');

                            // $errorMessage = 'Service account not active';
                            return ResponseHelper::failed('Service account is not active.');
                        } else {

                            if ($request->transfer_amount > 0) {

                                $userConfig = DB::table('user_config')
                                    ->select('threshold')
                                    ->where('user_id', $userId)
                                    ->first();

                                $thresholdAmount = isset($userConfig->threshold) ? $userConfig->threshold : 0;

                                if (floatval($user->transaction_amount) < floatval($request->transfer_amount + $thresholdAmount)) {
                                    $getResp = CommonHelper::internalTransaferAmountCheck($user->transaction_amount, $request->transfer_amount, $thresholdAmount);
                                    // $validator->errors()->add('amount', $getResp['message']);

                                    // $errorMessage = $getResp['message'];
                                    return ResponseHelper::failed($getResp['message']);
                                }
                            }
                            // else {
                            //     $validator->errors()->add('amount', 'Please enter transfer amount greater then 0.');

                            //     $errorMessage = 'Please enter transfer amount greater then 0.';
                            // }
                        }
                    }
                }
            });

            if ($validator->fails()) {
                // $this->message = $validator->errors();
                return ResponseHelper::failed($errorMessage, $validator->errors());
            }

            $userId = $request->user()->id;

            //check user config
            $userConfig = DB::table('user_config')
                ->select('is_internal_transfer_enable')
                ->where('user_id', $userId)
                ->first();

            $isInternalTransferEnable = @$userConfig->is_internal_transfer_enable;

            if ($isInternalTransferEnable !== '1') {
                return ResponseHelper::failed("Service is down, please try after some time.");
            }


            $serviceId = trim($request->service);
            $amount = trim($request->amount);
            $remarks = trim($request->remark);
            $successMessage = '';

            /**  Add Transaction Details */
            $UserService = DB::table('user_services')
                ->where(['service_id' => $serviceId, 'user_id' => $userId])
                ->first();

            if (empty($UserService)) {
                return ResponseHelper::failed('Invalid service selected.');
            }

            $txnDr = CommonHelper::getRandomString('txn', false);
            $txnCr = CommonHelper::getRandomString('txn', false);

            DB::select("CALL internalTransfer($userId, '" . $serviceId . "', $amount, '" . $txnDr . "', '" . $txnCr . "', '" . $remarks . "', @json)");
            $results = DB::select('select @json as json');
            $response = json_decode($results[0]->json, true);

            if ($response['status'] == '1') {
                $successMessage = "Amount Transfer Successfully.";
                return ResponseHelper::success($successMessage);
            }

            return ResponseHelper::failed($response['message']);
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }
}
