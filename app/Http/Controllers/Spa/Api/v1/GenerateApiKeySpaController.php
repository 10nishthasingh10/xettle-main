<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\ActivityLogHelper;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GenerateApiKeySpaController extends Controller
{


    /**
     * Get APIs Keys
     */
    public function getSdkApiKey()
    {
        try {

            $userId = Auth::user()->id;

            $userConfig = DB::table('user_config')
                ->select(
                    DB::raw('CONCAT(SUBSTR(app_id, 1, 6),REPEAT("*", CHAR_LENGTH(app_id) - 6),SUBSTR(app_id, -6)) as app_id'),
                    'app_cred_created_at',
                    DB::raw('CONCAT(SUBSTR(matm_app_id, 1, 6),REPEAT("*", CHAR_LENGTH(matm_app_id) - 6),SUBSTR(matm_app_id, -6)) as matm_app_id'),
                    'matm_app_cred_created_at'
                )
                ->where('user_id', $userId)
                ->first();

            if (!empty($userConfig)) {

                $return['aeps'] = [
                    'key' => $userConfig->app_id,
                    'created' => $userConfig->app_cred_created_at
                ];

                $return['matm'] = [
                    'key' => $userConfig->matm_app_id,
                    'created' => $userConfig->matm_app_cred_created_at
                ];

                return ResponseHelper::success('Records found.', $return);
            }

            return ResponseHelper::failed('No records found.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }



    /**
     * Generate SDK API Key
     */
    public function generateSdkApiKey(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'sdkType' => 'required|in:aeps,matm'
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }

            $userId = Auth::user()->id;
            $sdkType = trim($request->sdkType);

            switch ($sdkType) {

                case 'aeps':

                    $userConfig = DB::table('user_config')
                        ->select('is_sdk_enable')
                        ->where('user_id', $userId)
                        ->first();

                    if (!empty($userConfig->is_sdk_enable)) {
                        $keyCode = CommonHelper::getRandomString('xtl_', false, 16);
                        $keySecret = CommonHelper::getRandomString('', false, 32);
                        $secretkey = $keyCode;
                        $hash = hash('sha256', $keySecret);

                        $updateData = [
                            'app_id' => $secretkey,
                            'app_secret' => $hash,
                            'app_cred_created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        $isUpdated = DB::table('user_config')
                            ->where('user_id', $userId)
                            ->update($updateData);

                        if ($isUpdated) {
                            ActivityLogHelper::addToLog('sdk_app_credentials', $userId, "SDK App credentials Updated.", $userId);

                            UserController::sendUserSDKCredEmail($userId, $secretkey, $keySecret, 'AEPS');
                            $data['status'] = true;
                            $data['sdkKey'] = $secretkey;
                            $data['updatedAt'] = date('Y-m-d H:i:s');
                            $data['message'] = "An Email with newly generated SDK credentials sent to your registered email";

                            return ResponseHelper::success("An Email with newly generated SDK credentials sent to your registered email");
                        }

                        return ResponseHelper::failed("SDK Key not updated.");
                    }

                    return ResponseHelper::failed(strtoupper($sdkType) . " SDK is not enabled.");


                case 'matm':
                    $userConfig = DB::table('user_config')
                        ->select('is_matm_enable')
                        ->where('user_id', $userId)
                        ->first();

                    if (!empty($userConfig->is_matm_enable)) {

                        $keyCode = CommonHelper::getRandomString('xtl_', false, 16);
                        $keySecret = CommonHelper::getRandomString('', false, 32);
                        $secretkey = $keyCode;
                        $hash = hash('sha256', $keySecret);

                        $updateData = [
                            'matm_app_id' => $secretkey,
                            'matm_app_secret' => $hash,
                            'matm_app_cred_created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        $isUpdated = DB::table('user_config')
                            ->where('user_id', $userId)
                            ->update($updateData);

                        if ($isUpdated) {
                            ActivityLogHelper::addToLog('matm_sdk_app_credentials', $userId, "SDK App credentials Updated.", $userId);

                            UserController::sendUserSDKCredEmail($userId, $secretkey, $keySecret, 'MATM');
                            $data['status'] = true;
                            $data['sdkKey'] = $secretkey;
                            $data['updatedAt'] = date('Y-m-d H:i:s');
                            $data['message'] = "An Email with newly generated SDK credentials sent to your registered email";

                            return ResponseHelper::success("An Email with newly generated SDK credentials sent to your registered email");
                        }

                        return ResponseHelper::failed("SDK Key not updated.");
                    }

                    return ResponseHelper::failed(strtoupper($sdkType) . " SDK is not enabled.");
            }

            return ResponseHelper::failed("Invalid SDK type.");
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }
}
