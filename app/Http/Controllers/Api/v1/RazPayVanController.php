<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\RazorPaySmartCollectHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RazPayVanController extends Controller
{
    /**
     * Generate VAN
     */
    public function generateVan(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = ($validator->errors()->get('user_id'));
                return ResponseHelper::missing($message);
            }

            try {
                $userId = decrypt($request->user_id);
            } catch (Exception $e) {
                //$message = $e->getMessage();
                return ResponseHelper::missing("Invalid token value.");
            }

            //fetching user details
            $userInfo = DB::table('users')
                ->select('id', 'account_number')
                ->where('is_active', 1)
                ->find($userId);

            if (empty($userInfo)) {
                return ResponseHelper::failed("Invalid user ID.");
            }


            //fetching van and bank details
            $businessInfo = DB::table('business_infos')
                ->select('id', 'business_name', 'gstin')
                ->where('is_active', '1')
                ->where('is_kyc_updated', '1')
                ->where('user_id', $userId)
                ->first();


            if (empty($businessInfo)) {
                return ResponseHelper::failed("KYC is pending.");
            } else if (empty($businessInfo->business_name)) {
                return ResponseHelper::failed("Business Name is pending.");
            }


            $virtualAccount = DB::table('user_van_accounts')
                ->select('id')
                ->where('user_id', $userId)
                ->where('root_type', 'raz_van')
                ->first();

            if (!empty($virtualAccount)) {
                return ResponseHelper::failed("Virtual account already generated.");
            }


            $virtualAccId = substr($userInfo->account_number, -8);


            $userBankInfo = DB::table('user_bank_infos')
                ->select('*')
                ->where('is_active', '1')
                ->where('is_verified', '1')
                ->where('user_id', $userId)
                ->get();

            if ($userBankInfo->isEmpty()) {
                return ResponseHelper::failed("Bank accounts are not updated yet.");
            }

            $count = 0;
            $userBanks = [];
            foreach ($userBankInfo as $row) {
                if ($count < 10) {
                    $userBanks[] = [
                        "type" => "bank_account",
                        "bank_account" => [
                            "account_number" => $row->account_number,
                            "ifsc" => $row->ifsc
                        ]
                    ];
                    $count++;
                }
            }


            $vanHelper = new RazorPaySmartCollectHelper();

            $params = [
                "name" => $businessInfo->business_name,
                "email" => $virtualAccId . "@example.com",
                "gstin" => $businessInfo->gstin,
                "fail_existing" => "0",
            ];


            $result = $vanHelper->apiCaller($params, '/customers', Auth::user()->id);

            if ($result['code'] == 200) {

                $apiResponse = json_decode($result['response']);

                //when response is success
                if (!empty($apiResponse->id)) {

                    //if customer ID is not empty, create van params

                    $vanParams = [
                        "receivers" => [
                            "types" => [
                                "bank_account"
                            ]
                        ],
                        "allowed_payers" => $userBanks,
                        "description" => "Virtual Account created for " . $businessInfo->business_name,
                        "customer_id" => $apiResponse->id,
                        // "close_by": 1681615838,
                        // "notes": {
                        //     "project_name": "Banking Software"
                        // }
                    ];

                    $result = $vanHelper->apiCaller($vanParams, '/virtual_accounts', Auth::user()->id);

                    if ($result['code'] == 200) {
                        $apiResponse = json_decode($result['response']);

                        if (!empty($apiResponse->receivers)) {
                            DB::table('user_van_accounts')->insert([
                                'root_type' => 'raz_van',
                                'user_id' => $userId,
                                'customer_id' => $apiResponse->customer_id,
                                'account_holder_name' => $businessInfo->business_name,
                                // 'account_number_prefix' => $virtualAccId,
                                'account_id' => $apiResponse->id,
                                'account_number' => $apiResponse->receivers[0]->account_number,
                                'ifsc' => $apiResponse->receivers[0]->ifsc,
                                'authorized_remitters' => json_encode($apiResponse->allowed_payers),
                                'status' => ($apiResponse->status == 'active') ? '1' : '0',
                                'created_at' => date('Y-m-d H:i:s')
                            ]);

                            return ResponseHelper::success("VAN created successfully.");
                        }
                    } else if ($result['code'] == 400) {
                        $apiResponse = json_decode($result['response']);

                        if (!empty($apiResponse->error)) {
                            return ResponseHelper::failed($apiResponse->error->description);
                        }
                    }
                }

                return ResponseHelper::failed("VAN Creation Failed.");
            } else if ($result['code'] == 400) {
                $apiResponse = json_decode($result['response']);

                if (!empty($apiResponse->error)) {
                    return ResponseHelper::failed($apiResponse->error->description);
                }
            }

            return ResponseHelper::failed("Something went wrong.", $result);
        } catch (Exception $e) {
            return ResponseHelper::missing("Error: " . $e->getMessage());
        }
    }
}
