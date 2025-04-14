<?php

namespace App\Http\Controllers\Api\v1;


use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\OpenBank\OBApiService;
use App\Services\OpenBank\OpenBankBO;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class OBVanController extends Controller
{

    private String $contactType = 'Customer';

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
                    return ResponseHelper::missing("Invalid user token.");
                }

                //fetching user details
                $userInfo = DB::table('users')
                    ->select('id', 'mobile', 'account_number')
                    ->where('is_active', 1)
                    ->find($userId);

                if (empty($userInfo)) {
                    return ResponseHelper::failed("Invalid user ID.");
                }


                //fetching van and bank details
                $businessInfo = DB::table('business_infos')
                    ->select('id', 'name', 'business_name', 'gstin')
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
                    ->where('root_type', OPEN_BANK_VAN)
                    ->first();

                if (!empty($virtualAccount)) {
                    return ResponseHelper::failed("Virtual account already generated.");
                }

                // $virtualAccId = substr($userInfo->account_number, -8);

                //creating biz object
                $bizObject = new OpenBankBO();
                $bizObject->uri = 'van_create';
                $bizObject->http = 'post';
                $bizObject->userId = $userId;
                $bizObject->table = 'partner_van_ob';
                $bizObject->slug = 'create_van';
                $bizObject->clientRefId = '';
                $bizObject->param = (object) [
                    'businessName' => $businessInfo->business_name,
                    "name" => $businessInfo->name,
                    "contactType" => $this->contactType,
                    "email" => "example@example.com",
                    "mobile" => $userInfo->mobile
                ];


                $apiService = new OBApiService();
                $apiResponse = $apiService->send($bizObject);

                // dd($apiResponse);

                if ($apiResponse['code'] == 200) {

                    $apiResponseData = ($apiResponse['response']['response']);

                    $apiResponseDataStatus = @$apiResponseData->status;

                    if ($apiResponseDataStatus == 200) {

                        DB::table('user_van_accounts')->insert([
                            'root_type' => OPEN_BANK_VAN,
                            'user_id' => $userId,
                            // 'customer_id' => $apiResponseData->virtual_accounts_id,
                            'account_holder_name' => $apiResponseData->data->name,
                            // 'account_number_prefix' => $virtualAccId,
                            'account_id' => $apiResponseData->data->virtual_accounts_id,
                            'account_number' => $apiResponseData->data->virtual_account_number,
                            'ifsc' => $apiResponseData->data->virtual_account_ifsc_code,
                            // 'authorized_remitters' => json_encode($apiResponse->allowed_payers),
                            'status' => '1',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        return ResponseHelper::success("VAN created successfully.");
                    } else {
                        $message = isset($apiResponseData->message) ? $apiResponseData->message : 'API: something went wrong.';
                        return ResponseHelper::failed($message);
                    }
                } else {
                    $apiResponseData = isset($apiResponse['response']['response']) ? ($apiResponse['response']['response']) : null;

                    $message = isset($apiResponseData->message) ? $apiResponseData->message : 'API: something went wrong.';

                    return ResponseHelper::failed($message);
                }
            } else {
                return ResponseHelper::unauthorized("You are not authorised to perform this task.");
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }
}
