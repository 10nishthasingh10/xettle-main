<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ActivityLogHelper;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validations\UserValidation as Validations;
use App\Models\User;
use App\Models\BusinessInfo;
use App\Models\IpWhitelist;
use App\Models\Webhook;
use App\Models\UserService;
use App\Jobs\SendTransactionEmailJob;
use App\Models\Agent;
use App\Models\UserConfig;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function fetchByProductId($id)
    {

        $User = User::where('id', $id)->first();
        $this->message = "Record found Successfull";
        $this->data = ['user' => $User];

        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => 200,
                'data'      => $this->data
            ])
        );
    }



    /**
     * Profile Update
     */
    public function updateProfile(Request $request)
    {
        try {
            $id = decrypt($request->user_id);
            $validation = new Validations($request);
            $validator = $validation->updateProfile();

            $validator->after(function ($validator) use ($id) {
                $user = User::where('id', $id)->first();

                if (empty($user)) {
                    $validator->errors()->add('user_id', "Invalid User.");
                } else {
                    if($user->is_active != '1'){
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        $validator->errors()->add('user_id', $message);
                    }
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {
                $user = User::where('id', $id)->first();
                $user->name = trim($request->name);
                $user->is_profile_updated = '1';
                $user->mobile = trim($request->mobile);
                $user->save();

                $this->status   = true;
                $this->modal    = true;
                $this->alert    = true;
                $this->message  = "Profile Updated Successfull";
                $this->redirect = true;
                return $this->populateresponse();
            }
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => false,
                    'data'      => $this->message
                ])
            );

        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Change user profile password
     */
    public function profileChangePassword(Request $request)
    {
        try {
            $userId = decrypt($request->user_id);
            $validation = new Validations($request);
            $validator = $validation->profileChangePassword();

            $validator->after(function ($validator) use ($request, $userId) {
                $user = User::select('password', 'is_active')
                    ->where('id', $userId)->first();
                if (empty($user)) {
                    $validator->errors()->add('user_id', "Invalid user");
                } else {

                    if ($user->is_active != '1' && $user->is_active !== '0') {
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        $validator->errors()->add('user_id', $message);
                    } else if (!Hash::check($request->old_password, $user->password)) {
                        $validator->errors()->add('old_password', 'Old Password is not valid');
                    }
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {
                $user = User::where('id', $userId)->whereIn('is_active', ['1', '0'])->first();
                $user->password = Hash::make($request->password);
                $user->save();

                $this->status_code = '200';
                $this->status   = true;
                $this->modal    = true;
                $this->alert    = true;
                $this->title = "Password Changed";
                $this->message  = "Password updated Successfully";
                $this->redirect = false;
                return $this->populateresponse();
            }
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => false,
                    'data'      => $this->message
                ])
            );

        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Generate APIs Keys for the user services
     */
    public function apikeyGenerate(Request $request)
    {
        // try {
            $id = decrypt($request->user_id);
           

            $validation = new Validations($request);
            $validator = $validation->apikeyGenerate();

            $validator->after(function ($validator) use ($request, $id) {

                $user = DB::table('users')
                    ->select('is_active')
                    ->where('id', $id)
                    ->first();

                if (!empty($user)) {
                    if ($user->is_active != '1') {
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        $validator->errors()->add('user_id', $message);
                    }
                } else {
                    $validator->errors()->add('user_id', "Invalid user ID");
                }
              
           
            
                $authCount = DB::table('oauth_clients')
                    ->where('user_id', $id)
                    ->where('service_id', trim($request->service_id))
                    ->count('id');
                
                $apiKeyLimit = CommonHelper::getUserConfig('api_key_limit', $id);

                $apiKeyLimit = !empty($apiKeyLimit->api_key_limit) ? $apiKeyLimit->api_key_limit : 0;

                //check global limit is reached or not
                if($authCount >= $apiKeyLimit) {
                    $request->apiKeyLimitReached = true;
                    $validator->errors()->add('service_id', 'API Key generation limit reached. Please contact your account manager.');
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {
                \App\Helpers\NishthaHelper::logUserActivity($id, 'Key Generated', 'generated_key', now());
                // \ActivityLog::addToLog('generated_key' , $id);
                $keyCode = CommonHelper::getRandomString('', false, 16);
                $keySecret = CommonHelper::getRandomString('', false, 32);
                $secretkey = 'SAFEE_' . $keyCode;
                $hash = hash('sha512', $keySecret);
            
                

                DB::beginTransaction();

                DB::table('oauth_clients')
                    ->where('user_id', $id)
                    ->where('service_id', trim($request->service_id))
                    ->where('is_active', '1')
                    ->update([
                        'is_active' => '0',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                $insert = DB::table('oauth_clients')
                    ->insert([
                        'user_id' => $id,
                        'service_id' => trim($request->service_id),
                        'client_key' => $secretkey,
                        'client_secret' => $hash,
                        'is_active' => '1',
                        'scope' => "*",
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                DB::commit();

                if($insert) {
                    
                    //get user name and email
                    $user = DB::table('users')
                        ->select('email', 'name')
                        ->where('id', $id)
                        ->first();

                    //get service name
                    $service = DB::table('global_services')
                        ->select('service_name')
                        ->where('service_id', trim($request->service_id))
                        ->first();

                    $mailParms = [
                        'email' => $user->email,
                        'name' => $user->name,
                        'serviceName' => $service->service_name,
                        'clientId' => $secretkey,
                        'clientSecret' => $keySecret
                    ];
                    //dispatch(new SendTransactionEmailJob((object) $mailParms, 'apiKeyCredentials'));

                    ActivityLogHelper::addToLog('api_key_generate', $id, "New API key generated for - $service->service_name", $id);
                }
                
                $this->status_code = '200';
                $this->jsondata   = ['apikey' => true, 'key' => $secretkey, 'secret' => $keySecret];
                $this->status   = true;
                $this->modal    = false;
                $this->alert    = true;
                $this->title    = "Key Generated";
                $this->message  = "API keys generated successfully";
                $this->redirect = false;
                return $this->populateresponse();
            }

            if(!empty($request->apiKeyLimitReached)) {
                $statusCode = '101';
            } else {
                $statusCode = '400';
            }
           // dd("87877887");
            return response()->json(
                $this->populate([
                    'status_code' => $statusCode,
                    'message'   => $this->message,
                    'status'    => false,
                    'title'     => 'Keys Created',
                    'data'      => $this->message
                ])
            );
        // } catch(Exception $e) {
        //     DB::rollBack();

        //     $this->status_code = '100';
        //     $this->status = true;
        //     $this->modal = true;
        //     $this->alert = true;
        //     $this->message_object = true;
        //     $this->message  = array('message' => "Error: Something went wrong.");
        //     // $this->title = "";
        //     $this->redirect = false;
        //     return $this->populateresponse();
        // }
    }


    /**
     * Generate SDK API Key
     */
    public function sdkApiKey(Request $request)
    {
        try {
            $userId = decrypt($request->user_id);
            if (!empty($userId)) {
                $userConfig = UserConfig::where('user_id', $userId)->where('is_sdk_enable', '1')->first();
                if (!empty($userConfig)) {
                    $keyCode = CommonHelper::getRandomString('xtl_', false, 16);
                    $keySecret = CommonHelper::getRandomString('', false, 32);
                    $secretkey = $keyCode;
                    $hash = hash('sha256', $keySecret);
                    $userConfig->app_id = $secretkey;
                    $userConfig->app_secret = $hash;
                    $userConfig->app_cred_created_at = date('Y-m-d H:i:s');
                    if ($userConfig->save()) {
                        ActivityLogHelper::addToLog('sdk_app_credentials', $userId, "SDK App credentials Updated.", $userId);
                        self::sendUserSDKCredEmail($userId, $secretkey, $keySecret, 'AEPS');
                        $data['status'] = true;
                        $data['sdkKey'] = $secretkey;
                        $data['updatedAt'] = $userConfig->app_cred_created_at;
                        $data['message'] = "An Email with newly generated AEPS SDK credentials sent to your registered email";
                    } else {
                        $data['status'] = false;
                        $data['message'] = "SDk Key not updated.";
                    }
                } else {
                    $data['status'] = false;
                    $data['message'] = "User config record not found.";
                }
            } else {
                $data['status'] = false;
                $data['message'] = "User Id not valid.";
            }
            return $data;

        } catch(Exception $e) {
            $data['status'] = false;
            $data['message'] = "Error: Something went wrong.";

            return $data;
        }
    }


    /**
     * Generate SDK API Key
     */
    public function matmSdkApiKey(Request $request)
    {
        try {
            $userId = decrypt($request->user_id);
            if (!empty($userId)) {
                $userConfig = UserConfig::where('user_id', $userId)->where('is_matm_enable' , '1')->first();
                $userService= UserService::where('user_id', $userId)->first();
                if (!empty($userConfig) && !empty($userService)) {
                    $keyCode = CommonHelper::getRandomString('xtl_', false, 16);
                    $keySecret = CommonHelper::getRandomString('', false, 32);
                    $secretkey = $keyCode;
                    $hash = hash('sha256', $keySecret);
                    $userConfig->matm_app_id = $secretkey;
                    $userConfig->matm_app_secret = $hash;
                    $userConfig->matm_app_cred_created_at = date('Y-m-d H:i:s');
                    if ($userConfig->save()) {
                        ActivityLogHelper::addToLog('matm_sdk_app_credentials', $userId, "MATM SDK App credentials Updated.", $userId);
                        self::sendUserSDKCredEmail($userId, $secretkey, $keySecret, 'MATM');
                        $data['status'] = true;
                        $data['sdkKey'] = $secretkey;
                        $data['updatedAt'] = $userConfig->matm_app_cred_created_at;
                        $data['message'] = "An Email with newly generated MATM SDK credentials sent to your registered email";
                    } else {
                        $data['status'] = false;
                        $data['message'] = "SDk Key not updated.";
                    }
                } else {
                    $data['status'] = false;
                    $data['message'] = "Your Service is not activated.";
                }
            } else {
                $data['status'] = false;
                $data['message'] = "User Id not valid.";
            }
            return $data;

        } catch(Exception $e) {
            $data['status'] = false;
            $data['message'] = "Error: Something went wrong.";

            return $data;
        }
    }


    /**
     * Webhook update
     */
    public function webhookUpdate(Request $request)
    {
        try {
            $id = decrypt($request->user_id);
            $validation = new Validations($request);
            $validator = $validation->webhookUpdate();

            $validator->after(function ($validator) use ($request, $id) {
                $user = DB::table('users')->where('id', $id)->first();
                if (empty($user)) {
                    $validator->errors()->add('webhook_url', "Invalid user");
                } else {
                    if ($user->is_active != '1') {
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        $validator->errors()->add('webhook_url', $message);
                    }
                }
                if (!empty($request->add_header_key_value) && empty($request->headerKey)) {
                    $validator->errors()->add('headerKey', 'header Key is required');
                }
                if (!empty($request->add_header_key_value) && empty($request->headerValue)) {
                    $validator->errors()->add('headerValue', 'header Value is required');
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {
                $check = false;
                $WebHook = Webhook::where(['user_id' => $id])->first();
                if (!isset($WebHook)) {
                    $WebHook = new Webhook;
                    $check = true;
                }
                $WebHook->user_id = $id;
                $WebHook->secret = trim($request->secret);
                if (isset($request->headerKey)) {
                    $WebHook->header_key = trim($request->headerKey);
                } else {
                    $WebHook->header_key = NULL;
                }

                if (isset($request->headerValue)) {
                    $WebHook->header_value = trim($request->headerValue);
                } else {
                    $WebHook->header_value = NULL;
                }
                $WebHook->webhook_url = trim($request->webhook_url);
                $WebHook->save();

                $this->status   = true;
                $this->modal    = false;
                $this->alert    = true;
                if ($check) {
                    // \App\Helpers\NishthaHelper::logUserActivity($id, 'Add Webhook', 'add_webhook', now());
                    ActivityLogHelper::addToLog('add_webhook', $id, "Add Webhook", $id);
                    $this->message  = "Webhook URL Added Successfully";
                    $this->title    = "Webhook Added";
                } else {
                    // \App\Helpers\NishthaHelper::logUserActivity($id, 'Update Webhook', 'update_webhook', now());
                    ActivityLogHelper::addToLog('update_webhook', $id, "Update Webhook", $id);
                    $this->message  = "Webhook URL Updated Successfully";
                    $this->title    = "Webhook Updated";
                }
                $this->redirect = false;
                $this->modalId = "exampleModal1";
                $this->modalClose = true;
                $this->status_code = '200';
                return $this->populateresponse();
            }
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => false,
                    'title'    => 'Webhook Updated',
                    'data'      => $this->message
                ])
            );

        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Add IP address
     */
    public function addIp(Request $request)
    {
        try {

            $id = decrypt($request->user_id);

            $validation = new Validations($request);            
            $validator = $validation->addIp();

            $validator->after(function ($validator) use ($request, $id) {
                
                $user = DB::table('users')
                    ->select('is_active')
                    ->where('id', $id)
                    ->first();

                if(!empty($user)) {

                    if ($user->is_active != '1') {
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        $validator->errors()->add('user_id', $message);
                    }

                } else {
                    $validator->errors()->add('user_id', 'Invalid user id.');
                }


                $ipWhitelist = DB::table('ip_whitelists')
                    ->where(
                        [
                            'user_id' => $id, 
                            'ip' => trim($request->ip), 
                            'service_id' => trim($request->service_id),
                            'is_active' => '1'
                        ]
                    )->first();

                if (!empty($ipWhitelist)) {
                    $validator->errors()->add('ip', 'IP address already exists');
                } else {

                    $ipCounts = DB::table('ip_whitelists')
                        ->where('user_id', $id)
                        ->where('service_id', trim($request->service_id))
                        ->where('is_active', '1')
                        ->count('id');

                    if($ipCounts >= LIMIT_IP_WHITELIST) {
                        $validator->errors()->add('ip', 'IP whitelist limit reached.');
                    }

                }
                
            });


            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {
                // \App\Helpers\NishthaHelper::logUserActivity($id, 'IP Whitelisted', 'add_ip', now());
                ActivityLogHelper::addToLog('add_ip', $id, "IP Whitelisted", $id);
                $OauthClient = new IpWhitelist;
                $OauthClient->user_id = $id;
                $OauthClient->service_id = $request->service_id;
                $OauthClient->ip = $request->ip;
                $OauthClient->is_active = '1';
                $OauthClient->save();

                $this->status_code = '200';
                $this->status   = true;
                $this->modal    = false;
                $this->alert    = true;
                $this->title    = "IP Whitelisted";
                $this->message  = "IP Added Successfully";
                $this->redirect = false;
                $this->modalId = "kt_modal_create_ipwhite";
                $this->modalClose = true;
                return $this->populateresponse();
            }

            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => false,
                    'title'    => 'IP Whitelisted',
                    'data'      => $this->message
                ])
            );

        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Inactive IP Address
     */
    public function ipDelete($id)
    {
        try{
            if (!is_numeric($id)) {
                $this->status_code = '100';
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "Invalid ID.");
                // $this->title = "";
                $this->redirect = false;
                return $this->populateresponse();
            }


            $userId = Auth::user()->id;
            $authClientCheck = DB::table('ip_whitelists')
                // ->select('service_id', 'is_active')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->count('id');

            // \App\Helpers\NishthaHelper::logUserActivity($userId, 'IP deleted', 'delete_ip', now());
            ActivityLogHelper::addToLog('delete_ip', $id, "IP deleted", $id);
            if($authClientCheck <= 0) {
                $this->status_code = '100';
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "Invalid ID.");
                // $this->title = "";
                $this->redirect = false;
                return $this->populateresponse();
            }


            DB::table('ip_whitelists')
                ->where('id', trim($id))
                ->update(
                    [
                        'is_active' => '0',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                );

            $this->status   = true;
            $this->modal    = false;
            $this->alert    = true;
            $this->title    = 'IP Deleted';
            $this->message  = "IP deleted successfully";
            $this->redirect = false;
            return $this->populateresponse();            
            
        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Handle and update user business profile
     */
    public function businessProfileUpdate(Request $request)
    {
        try {
            $id = decrypt($request->user_id);
            $validation = new Validations($request);
            $validator = $validation->businessProfileUpdate();

            $validator->after(function ($validator) use ($id) {
                $user = DB::table('users')->where('id', $id)->first();
                if (empty($user)) {
                    $validator->errors()->add('name', "Invalid user");
                } else {
                    if ($user->is_active != '1' && $user->is_active !== '0') {
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        $validator->errors()->add('user_id', $message);
                    }
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {
                // $data['resellers'] = DB::table('resellers')->select('id','name')->where('status', '1')->get();

                // $data['resellerName'] = $reseller ? $reseller->name : null;
                // $userdata = User::find($id);
                // $userdata->reseller = $request->reseller;
                // $userdata->save();
                $BusinessInfo = BusinessInfo::where('user_id', $id)->first();
                $data = $request->all();
                unset($data['_token']);
                $data['mobile'] = $data['contact_number'];
                $data['email'] = $data['contact_email'];
                $data['is_kyc_updated'] = '1';
                $data['business_category_id'] = $data['business_category'];
                $data['mcc'] = $data['business_mcc'];

                if (isset($data['business_subcategory'])) {
                    $data['business_subcategory_id'] = $data['business_subcategory'];
                    unset($data['business_subcategory']);
                } else {
                    $data['business_subcategory_id'] = null;
                }
                unset($data['contact_number']);
                unset($data['contact_email']);
                unset($data['business_category']);
                unset($data['re_eneter_account_number']);
                unset($data['business_mcc']);
                unset($data['reseller']);

                if ($request->hasFile('business_proof')) {
                    $avatar = time() . $request->business_proof->getClientOriginalName();
                    $request->business_proof->move(public_path('uploads/business/profie'), $avatar);
                    $data['business_registration_proof'] = $avatar;
                }
                if ($request->hasFile('pan_id')) {
                    $avatar = time() . $request->pan_id->getClientOriginalName();
                    $request->pan_id->move(public_path('uploads/business/profie'), $avatar);
                    $data['pan_doc'] = $avatar;
                }


                if (!empty($request->web_url)) {
                    $url = parse_url($request->web_url);
                    $data['web_url'] = $url['scheme'] . '://' . $url['host'];
                }
                $data['app_url'] = $request->app_url;

                $updatedById = 0;
                if(!empty($request->update_by_user_id)) {
                    $updatedById = decrypt($request->update_by_user_id);
                    unset($data['update_by_user_id']);
                }

                if (isset($BusinessInfo)) {
                    unset($data['business_proof']);
                    unset($data['pan_id']);
                    unset($data['user_id']);
                    $BusinessInfo = BusinessInfo::where('user_id', $id)->update($data);
                } else {
                    $data['is_kyc_updated'] = '1';
                    $data['user_id'] = $id;
                    $BusinessInfo = BusinessInfo::create($data);
                }
                if ($updatedById == $id) {
                    $message = "Business profile update by user";
                } else {
                    $message = "Business profile update by admin";
                }
                
                ActivityLogHelper::addToLog('business_profile_update', $id, $message, $updatedById);
                $this->status   = true;
                $this->modal    = true;
                $this->alert    = true;
                $this->message  = "Business Profile updated Successfully";
                $this->redirect = true;
                return $this->populateresponse();
            }
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => false,
                    'data'      => $this->message
                ])
            );
        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Add Primary Account, filled by user at profile
     */
    public function updateBankDetails(Request $request)
    {
        try {
            if (empty($request->user_id)) {
                $this->status   = true;
                $this->modal    = true;
                $this->alert    = true;
                $this->message_object = true;
                $this->message  = array('message' => "Invalid User Info.");
                $this->redirect = false;

                return $this->populateresponse();
            }

            $userId = decrypt($request->user_id);

            $validation = new Validations($request);
            $validator = $validation->updateBankDetails();

            $validator->after(function ($validator) use ($userId) {
                
                $userInfo = User::where('id', $userId)->first();
                
                if (empty($userInfo)) {
                    $validator->errors()->add('user_id', "User is not activated.");
                } else {
                    
                    if ($userInfo->is_active != '1' && $userInfo->is_active !== '0') {
                        $message = CommonHelper::getUserStatusMessage($userInfo->is_active);
                        $validator->errors()->add('user_id', $message);
                    }
                    
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {
                $check = DB::table('user_bank_infos')
                    ->select('id')
                    ->where('user_id', $userId)
                    ->count();

                if ($check > 0) {
                    $this->status   = true;
                    $this->modal    = true;
                    $this->alert    = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "User Bank already added.");
                    $this->redirect = false;

                    return $this->populateresponse();
                }

                //insert bank info to user_bank_infos
                $insert = DB::table('user_bank_infos')->insert([
                    'user_id' => $userId,
                    'beneficiary_name' => ucwords($request->beneficiary_name),
                    'account_number' => $request->account_number,
                    'ifsc' => strtoupper($request->ifsc),
                    'is_active' => '1',
                    'is_verified' => '0',
                    'is_primary' => '1',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                if ($insert) {
                    $businessInfo = DB::table('business_infos')
                        ->select('id', 'user_id')
                        ->where('user_id', $userId)
                        ->first();

                    if (!empty($businessInfo)) {
                        DB::table('business_infos')
                            ->where('user_id', $userId)
                            ->update([
                                'is_bank_updated' => '1',
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        DB::table('business_infos')
                            ->where('user_id', $userId)
                            ->insert([
                                'user_id' => $userId,
                                'is_bank_updated' => '1',
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                    }
                }

                $this->status   = true;
                $this->modal    = true;
                $this->alert    = true;
                $this->message  = "Bank Details updated Successfully";
                $this->redirect = true;
                return $this->populateresponse();
            }

            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => false,
                    'data'      => $this->message
                ])
            );

        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Handle service activation request by user
     */
    public function serviceActivate(Request $request)
    {
        try {
            $id = trim($request->service_id);
            $userId = decrypt($request->user_id);

            $userServiceAccount = DB::table('user_services')->where('service_id', $id)->where('user_id', $userId)->first();
            if (!empty($userServiceAccount)) {
                $this->status_code = '100';
                $this->status = false;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "You requested for this service already.");
                // $this->title = "";
                $this->redirect = false;
                return $this->populateresponse();
            } else {
                $UserService = new UserService;
                $UserService->user_id = $userId;
                $UserService->service_id = $id;
                $UserService->service_account_number = null;
                $UserService->locked_amount = 0;
                $UserService->transaction_amount = 0;
                $UserService->is_active = '0';
                $UserService->save();

                CommonHelper::sendSlackRequestData($id, $userId);
                #check user salt
                $salt = base64_encode(CommonHelper::getRandomString('', false, 10));
                $user_salt = UserConfig::where('user_id', $userId)->first();

                $salt = base64_encode(CommonHelper::getRandomString('', false, 10));
                if (empty($user_salt)) {
                    #save salt in user_config

                    $userConfig = new UserConfig;
                    $userConfig->user_id = $userId;
                    $userConfig->user_salt = $salt;
                    $userConfig->save();
                } else if (!empty($user_salt) && $user_salt['user_salt'] == '') {
                    $user_salt->user_salt = $salt;
                    $user_salt->save();
                }

                $this->status_code = '200';
                $this->jsondata   = ['service_account_number' => $UserService->service_account_number];
                $this->status   = true;
                $this->modal    = false;
                $this->alert    = true;
                $this->title    = "Service Activation";
                $this->message  = "Service activation request accepted.";
                $this->redirect = false;
                return $this->populateresponse();
            }
        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Transfer Amount Main Account to Service Account
     *
     * @param Request $request
     * @return void
     */
    public function transferAmount(Request $request)
    {
        try {
            $id = !(empty($request->user_id)) ? decrypt($request->user_id) : '';
            $serviceId = !(empty($request->service_id)) ? decrypt($request->service_id) : '';

            $validation = new Validations($request);
            $validator = $validation->transferAmount();
            
            $validator->after(function ($validator) use ($request, $id, $serviceId) {
                
                $globalInternalTransfer = DB::table('global_config')
                    ->select('attribute_1')
                    ->where('slug', 'internal_tranfer_enable')
                    ->first()->attribute_1;
                if ($globalInternalTransfer == '0') {
                    $validator->errors()->add('service_id', 'Internal transfer down for some time.');
                }
                $user = DB::table('users')->where('id', $id)->first();
                if(empty($user)) {
                    $validator->errors()->add('transfer_amount', 'Invalid user');
                } else {

                    if ($user->is_active != '1') {
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        $validator->errors()->add('transfer_amount', $message);
                    } else {

                        $userServices = DB::table('user_services')
                            ->where('user_id', $id)
                            ->whereIn('service_id', [PAYOUT_SERVICE_ID, RECHARGE_SERVICE_ID,  VALIDATE_SERVICE_ID, DMT_SERVICE_ID, PAN_CARD_SERVICE_ID])
                            ->where('id', $serviceId)
                            ->where('is_active', '1')->first();

                        if (empty($userServices)) {
                            $validator->errors()->add('service_id', 'Service account not active');
                        } else {
        
                            if ($request->transfer_amount > 0) {
                                $userConfig = DB::table('user_config')
                                    ->select('threshold')
                                    ->where('user_id', $id)
                                    ->first();
                                $thresholdAmount = isset($userConfig->threshold) ? $userConfig->threshold : 0;
                                if (floatval($user->transaction_amount) < floatval($request->transfer_amount + $thresholdAmount)) {
                                    $getResp = CommonHelper::internalTransaferAmountCheck($user->transaction_amount, $request->transfer_amount, $thresholdAmount);
                                    $validator->errors()->add('transfer_amount', $getResp['message']);
                                }
                            } else {
                                $validator->errors()->add('transfer_amount', 'Please enter transfer amount greater then 0.');
                            }
                        }
                    }
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {
                // \App\Helpers\NishthaHelper::logUserActivity($id, ' Amount shifted ₹ ' . $request->transfer_amount, 'transfer_amount', now());
                /**  Add Transaction Details */
                $UserService = DB::table('user_services')->where(['id' => $serviceId, 'user_id' => $id])->first();
               
                $txnDr = CommonHelper::getRandomString('txn', false);
                $txnCr = CommonHelper::getRandomString('txn', false);
                $remarks = isset($request->remarks) ? $request->remarks : '';
                ActivityLogHelper::addToLog('internal_transfer', $id, "Amount shifted ₹  - $request->transfer_amount ", $id);
                DB::select("CALL internalTransfer($id, '" . $UserService->service_id . "', $request->transfer_amount, '" . $txnDr . "', '" . $txnCr . "', '" . $remarks . "', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);
                if ($response['status'] == '1') {
                    $this->message  = "Amount Transfer Successfully.";
                } else {
                    $this->message  = $response['message'];
                    $this->message_object = true;
                }

                $this->title = "Amount Transfered";
                $this->status   = true;
                $this->modal    = true;
                $this->alert    = false;
                $this->redirect = true;
                return $this->populateresponse();
            }
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => false,
                    'data'      => $this->message
                ])
            );
        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }

    public function adminTransferAmount(Request $request)
    {
        try {
            if(Auth::user()->hasRole('super-admin')) {

                $id = decrypt($request->user_id);
                $transferBy = decrypt($request->transfer_by);
                $validation = new Validations($request);
                $validator = $validation->transferAmount();
                $validator->after(function ($validator) use ($request, $id) {
                    $userServices = UserService::where('user_id', $id)->where('is_active', '1')->first();

                    $User = User::where('id', $id)->first();
                    if (isset($User) && $User->is_active != '1') {
                        $message = CommonHelper::getUserStatusMessage($User->is_active);
                        $validator->errors()->add('user_id', $message);
                    }

                    if (empty($userServices)) {
                        $validator->errors()->add('service_id', 'Service account not active');
                    } else {
                        if ($request->transfer_amount > 0) {
                                $userConfig = DB::table('user_config')
                                    ->select('threshold')
                                    ->where('user_id', $id)
                                    ->first();
                                $thresholdAmount = isset($userConfig->threshold) ? $userConfig->threshold : 0;
                                if (floatval($User->transaction_amount) < floatval($request->transfer_amount + $thresholdAmount)) {
                                    $getResp = CommonHelper::internalTransaferAmountCheck($User->transaction_amount, $request->transfer_amount, $thresholdAmount);
                                    $validator->errors()->add('transfer_amount', $getResp['message']);
                                }
                        } else {
                            $validator->errors()->add('transfer_amount', 'Please enter transfer amount greater then 0.');
                        }
                    }
                });
                if ($validator->fails()) {
                    $this->message = $validator->errors();
                } else {

                    /**  Add Transaction Details */
                    $UserService = UserService::where(['id' => $request->service_id, 'user_id' => $id])->first();
                    $txnDr = CommonHelper::getRandomString('txn', false);
                    $txnCr = CommonHelper::getRandomString('txn', false);
                    $remarks = isset($request->remarks) ? $request->remarks : '';
                    ActivityLogHelper::addToLog('internal_transfer', $id, "$request->transfer_amount Amount transfer by admin this txn id : $txnCr", $transferBy);
                    DB::select("CALL internalTransfer($id, '" . $UserService->service_id . "', $request->transfer_amount, '" . $txnDr . "', '" . $txnCr . "', '" . $remarks . "', @json)");
                    $results = DB::select('select @json as json');
                    $response = json_decode($results[0]->json, true);
                    if ($response['status'] == '1') {
                        $this->message  = "Amount Transfer Successfully.";
                    } else {
                        $this->message  = $response['message'];
                        $this->message_object = true;
                    }

                    $this->status   = true;
                    $this->modal    = true;
                    $this->alert    = false;
                    $this->redirect = true;
                    return $this->populateresponse();
                }
                return response()->json(
                    $this->populate([
                        'message'   => $this->message,
                        'status'    => false,
                        'data'      => $this->message
                    ])
                );

            } else {
                return abort(404);
            }
        
        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: Something went wrong.");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Transfer Amount Service Account to Main Account
     *
     * @param Request $request
     * @return void
     */

    public function transAmtToMainAcc(Request $request)
    {
        $id = decrypt($request->user_id);
        $validation = new Validations($request);
        $validator = $validation->transAmtToMainAcc();
        $validator->after(function ($validator) use ($request, $id) {
            $userServices = UserService::where('user_id', $id)->where('service_id', $request->service_id)->where('is_active', '1')->first();
            $User = User::where('id', $id)->where('is_active', '1')->first();
            if (empty($userServices)) {
                $validator->errors()->add('account_number', 'Service account not active');
            } else {
                if ($request->transfer_amount > 0) {
                    if (floatval($userServices->transaction_amount) < floatval($request->transfer_amount)) {
                        $validator->errors()->add('transfer_amount', 'Insufficient Service Balance your balance is :' . $userServices->transaction_amount);
                    }
                } else {
                    $validator->errors()->add('transfer_amount', 'Please enter transfer amount greater then 0.');
                }
            }
        });
        if ($validator->fails()) {
            $this->message = $validator->errors();
        } else {

            /**  Add Transaction Details */
            $UserAccount = User::where('id', $id)->where('is_active', '1')->first();
            $UserService = UserService::where(['service_id' => $request->service_id, 'user_id' => $id])->first();

            TransactionHelper::internalTransferToMainAcc($id, $UserService->service_id, $UserService->service_account_number, $UserAccount->account_number, $request->transfer_amount, $request->remarks);

            $this->status   = true;
            $this->modal    = true;
            $this->alert    = false;
            $this->message  = "Amount Transfer Successfull";
            $this->redirect = true;
            return $this->populateresponse();
        }
        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => false,
                'data'      => $this->message
            ])
        );
    }

    public function chartData($search, $userId)
    {
        $userId = decrypt($userId);
        $chart = CommonHelper::updateChart($userId, 'transactions', $search, 'internal_transfer');
        return json_encode($chart['data']);
    }

    public function orderChartData($search, $userId)
    {
        $userId = decrypt($userId);
        $chart = CommonHelper::updateChart($userId, 'orders', $search);
        return json_encode($chart['data']);
    }

    public function payoutChartData($search, $userId)
    {
        $userId = decrypt($userId);
        $chartCr = CommonHelper::payoutUpdateChart($userId, 'transactions', $search, 'internal_transfer', 'cr');
        $chartDr = CommonHelper::payoutUpdateChart($userId, 'transactions', $search, '', 'dr');
        $resp['cr'] = $chartCr['data'];
        $resp['dr'] = $chartDr['data'];
        return json_encode($resp);
        // dd($resp);
    }

    public function callbackData($search, $userId)
    {
        $userId = decrypt($userId);
        $chart = CommonHelper::updateChart($userId, 'callback', $search, 'internal_transfer');
        return json_encode($chart['data']);
    }
    public function callbackUserData($search, $userId)
    {
        $userId = decrypt($userId);
        $chart = CommonHelper::updateChart($userId, 'callbackuserdata', $search, 'internal_transfer');
        return json_encode($chart['data']);
    }

    public function dashboardCardData($type, $userId)
    {
        $userId = decrypt($userId);
        return CommonHelper::aepsDashboard($userId, $type);
    }

    public function upiDashboardChart($type, $userId)
    {
        $userId = decrypt($userId);
        return CommonHelper::upiDashboardChart($userId, $type);
    }

    public function aepsDashboardChart($search, $fetcheType, $userId)
    {
        $userId = decrypt($userId);
        return CommonHelper::aepsDashboardChart($userId, $search, $fetcheType);
    }

    public function aepsChartByBank($userId)
    {
        $userId = decrypt($userId);
        return CommonHelper::aepsChartByBank($userId);
    }


    /**
     * Claim Back (Payout To Main)
     */
    public function claimback(Request $request)
    {
        try {

            if(Auth::user()->hasRole('super-admin')) {

                $userId = decrypt($request->user_id);
                $userIdClaimBy = decrypt($request->claim_by);
                $serviceId = decrypt($request->service_account);

                $validation = new Validations($request);
                $validator = $validation->claimback();

                $validator->after(function ($validator) use ($request, $userId, $userIdClaimBy, $serviceId) {

                    $user = DB::table('users')->where('id', $userId)->first();

                    $claimBy = DB::table('users')
                        ->select('id')
                        ->where('id', $userIdClaimBy)
                        ->where('is_active', '1')
                        ->where('is_admin', '1')
                        ->first();

                    $userService = DB::table('user_services')
                        ->where(['id' => $serviceId, "user_id" => $userId])
                        ->first();

                    if (empty($user)) {
                        $validator->errors()->add('user_id', "Invalid user");
                    } else if(empty($claimBy)) {
                        $validator->errors()->add('user_id', "Invalid claim by admin");
                    } else if(empty($userService)) {
                        $validator->errors()->add('service_account', 'Service account dose not exits.');
                    } else if($request->amount <= 0) {
                        $validator->errors()->add('amount', 'Please give amount grater than 0.');
                    } else if ($userService->transaction_amount < $request->amount) {
                        $validator->errors()->add('amount', 'Insufficient fund.');
                    } else {

                        if ($user->is_active != '1') {
                            $message = CommonHelper::getUserStatusMessage($user->is_active);
                            $validator->errors()->add('user_id', $message);
                        }

                    }
                });

                if ($validator->fails()) {
                    $this->message = $validator->errors();
                } else {
                    
                    $userService = DB::table('user_services')
                        ->where(['id' => $serviceId, "user_id" => $userId])
                        ->first();

                    $claimBy = DB::table('users')
                        ->where('id', $userIdClaimBy)
                        ->where('is_active', '1')
                        ->where('is_admin', '1')
                        ->first();

                    TransactionHelper::claimBackTransfer(
                        $userId,
                        $userService->service_id,
                        $request->amount,
                        trim($request->remarks), 
                        $claimBy
                    );
                    

                    $this->status_code = '200';
                    $this->status   = true;
                    $this->modal    = true;
                    $this->alert    = false;
                    $this->message  = "Cliam back operation successfully.";
                    $this->redirect = true;
                    return $this->populateresponse();
                }

                return response()->json(
                    $this->populate([
                        'message'   => $this->message,
                        'status'    => false,
                        'data'      => $this->message
                    ])
                );

            } else {
                return abort(404);
            }

        } catch(Exception $e) {

            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();

        }

    }


    /**
     * Threshold Amount Set
     */
    public function threshold(Request $request)
    {
        try {

            if(Auth::user()->hasRole('super-admin')) {

                $userId = decrypt($request->user_id);
                $userIdClaimBy = decrypt($request->created_by);

                $validation = new Validations($request);
                $validator = $validation->threshold();

                $validator->after(function ($validator) use ($request, $userId, $userIdClaimBy) {

                    $user = DB::table('users')->where('id', $userId)->first();

                    $claimBy = DB::table('users')
                        ->select('id')
                        ->where('id', $userIdClaimBy)
                        ->where('is_active', '1')
                        ->where('is_admin', '1')
                        ->first();

                    if (empty($user)) {
                        $validator->errors()->add('user_id', "Invalid user");
                    } else if(empty($claimBy)) {
                        $validator->errors()->add('user_id', "Invalid claim by admin");
                    } else if($request->threshold_amount < 0) {
                        $validator->errors()->add('threshold_amount', 'Please give amount grater than 0.');
                    } else {

                        if ($user->is_active != '1') {
                            $message = CommonHelper::getUserStatusMessage($user->is_active);
                            $validator->errors()->add('user_id', $message);
                        }

                    }

                });

                if ($validator->fails()) {
                    $this->message = $validator->errors();
                } else {
                    ActivityLogHelper::addToLog('threshold_amount', $userId, "amount :  $request->threshold_amount updated", $userIdClaimBy);

                    UserConfig::where('user_id', $userId)->update(['threshold' => $request->threshold_amount]);

                    $this->status_code = '200';
                    $this->status   = true;
                    $this->modal    = true;
                    $this->alert    = false;
                    $this->message  = "Threshold operation successfully.";
                    $this->redirect = true;
                    return $this->populateresponse();
                }
                return response()->json(
                    $this->populate([
                        'message'   => $this->message,
                        'status'    => false,
                        'data'      => $this->message
                    ])
                );

            } else {
                return abort(404);
            }

        } catch(Exception $e) {

            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();

        }
    }

    /**
     * Transfer Amount Main Account to Service Account
     *
     * @param Request $request
     * @return void
     */

    public function aepsTransferAmount(Request $request)
    {
        $id = decrypt($request->user_id);
        $validation = new Validations($request);
        $validator = $validation->aepsTransferAmount();
        $validator->after(function ($validator) use ($request, $id) {
            $userServices = UserService::where(['user_id' => $id, 'service_id' => PAYOUT_SERVICE_ID])->where('is_active', '1')->first();
            $User = UserService::where(['user_id' => $id, 'service_id' => AEPS_SERVICE_ID])->where('is_active', '1')->first();
            if (empty($userServices)) {
                $validator->errors()->add('service_id', 'Service account not active');
            } else {
                if ($request->transfer_amount > 0) {
                    if (floatval($User->transaction_amount) < floatval($request->transfer_amount)) {
                        $validator->errors()->add('transfer_amount', 'Insufficient Main Balance your balance is :' . $User->transaction_amount);
                    }
                } else {
                    $validator->errors()->add('transfer_amount', 'Please enter transfer amount greater then 0.');
                }
            }
        });
        if ($validator->fails()) {
            $this->message = $validator->errors();
        } else {

            /**  Add Transaction Details */
            $txnDr = CommonHelper::getRandomString('txn', false);
            $txnCr = CommonHelper::getRandomString('txn', false);
            $remarks = isset($request->remarks) ? $request->remarks : '';
            DB::select("CALL aepsInternalTransfer($id, '" . AEPS_SERVICE_ID . "', '" . PAYOUT_SERVICE_ID . "', $request->transfer_amount, '" . $txnDr . "', '" . $txnCr . "', '" . $remarks . "', @json)");
            $results = DB::select('select @json as json');
            $response = json_decode($results[0]->json, true);
            if ($response['status'] == '1') {
                $this->message  = "Amount Transfer Successfully.";
            } else {
                $this->message  = array('message' => $response['message']);
                $this->message_object = true;
            }
            $this->status   = true;
            $this->modal    = true;
            $this->alert    = false;
            $this->redirect = true;
            return $this->populateresponse();
        }
        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => false,
                'data'      => $this->message
            ])
        );
    }


    public  function sendKYCAttachment(Request $req)
    {
        try {

            if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {

                $userId = decrypt($req->userId);
                $id = $req->id;

                $GlobalConfig = DB::table('global_config')
                    ->select('attribute_1', 'attribute_2', 'attribute_3')
                    ->where(['slug' => 'aeps_kyc_attachment'])
                    ->first();
                $isActive = 0;
                $toEmails = [];
                $cc = [];

                if (isset($GlobalConfig)) {
                    $isActive = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;

                    if (!empty($GlobalConfig->attribute_2)) {
                        $toEmails = explode(',', $GlobalConfig->attribute_2);
                    }
                    if (!empty($GlobalConfig->attribute_3)) {
                        $cc = explode(',', $GlobalConfig->attribute_3);
                    }
                }
                if ($isActive == 1) {
                    $agent = DB::table('agents')
                        ->select('first_name', 'last_name', 'middle_name', 'mobile', 'email_id', 'merchant_code', 'user_id')

                        ->where('id', (int)$id)
                        ->first();

                    $BusinessInfo = BusinessInfo::where('user_id', $agent->user_id)->first();
                    $name = $agent->first_name . ' ' . $agent->last_name;
                    $merchantCode = $agent->merchant_code;
                    $message = "Please generate a TID for the merchant. Merchant details are shared below.";
                    $url = env('AEPS_KYC_URL') . '/api/document_list';
                    $request = ['merchant_code' => $merchantCode];
                    $result = CommonHelper::curl($url, "POST", json_encode($request), ["Content-Type: application/json"], 'yes', 1, '', '');
                    $response = json_decode($result['response']);
                    $files = [];
                    if (isset($response) && $response->success && !empty($response->data)) {
                        $files = [
                            env('AEPS_KYC_URL') . '/public/storage/' . str_replace('public/', '', $response->data->aadhaar_front_url),
                            env('AEPS_KYC_URL') . '/public/storage/' . str_replace('public/', '', $response->data->aadhaar_back_url),
                            env('AEPS_KYC_URL') . '/public/storage/' . str_replace('public/', '', $response->data->pan_front_url),
                            env('AEPS_KYC_URL') . '/public/storage/' . str_replace('public/', '', $response->data->shop_photo_url),
                            env('AEPS_KYC_URL') . '/public/storage/' . str_replace('public/', '', $response->data->photo_url),
                        ];
                    }
                    $mailParms = [
                        'email' =>  $toEmails,
                        'name' => $name,
                        'first_name' => $agent->first_name,
                        'last_name' => $agent->last_name,
                        'middle_name' => $agent->middle_name,
                        'message' => $message,
                        'attachment' =>  $files,
                        'cc' => $cc,
                        'mobile' => $agent->mobile,
                        'email_id' => $agent->email_id,
                        'merchant_code' => $merchantCode,
                        'mid' => AEPS_MID_ID,
                        'business_name' => (isset($BusinessInfo->business_name) && $BusinessInfo->business_name) ? $BusinessInfo->business_name : 'NA'
                    ];
                    dispatch(new SendTransactionEmailJob((object) $mailParms, 'AEPSKycAttachment'));
                    Agent::where('id', (int)$id)->update(['is_attachment_send' => '1']);

                    // Activitiy Log
                    ActivityLogHelper::addToLog('send_kyc_attachement', $id, 'Attachement Email send Successfully.', $userId);
                    // Activity Log
                    $this->message  = "Attachement Email send Successfully.";

                    $this->status   = true;
                    $this->modal    = true;
                    $this->alert    = false;
                    $this->redirect = true;
                    return $this->populateresponse();
                } else {
                    $this->message  = "KYC Attachement not active";
                    return response()->json(
                        $this->populate([
                            'message'   => $this->message,
                            'status'    => false,
                            'data'      => []
                        ])
                    );
                }

            } else {
                return abort(404);
            }

        } catch(Exception $e) {

            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();

        }
    }


    /**
     * Method sendUserSDKCredEmail
     *
     * @param $userId $userId [explicite description]
     * @param $appId $appId [explicite description]
     * @param $appSecret $appSecret [explicite description]
     * @param $type $type [explicite description]
     *
     * @return void
     */
    public static function sendUserSDKCredEmail($userId, $appId, $appSecret, $type)
    {
        $user = DB::table('users')
            ->select('users.email as email', 'users.name as name')
            ->where('users.id', $userId)
            ->first();
        if (!empty($user->email)) {

            $mailParms = [
                'email' => $user->email,
                'name' => $user->name,
                'appId' => $appId,
                'appSecret' => $appSecret
            ];
            if ($type == 'AEPS') {
                dispatch(new SendTransactionEmailJob((object) $mailParms, 'sdkAppCred'));
            } else {
                dispatch(new SendTransactionEmailJob((object) $mailParms, 'matmSdkAppCred'));
            }

        }
    }

    /**
     * Update AEPS SDK Status
     */
    public function sdkStatus(Request $request)
    {
        try {
            if(Auth::user()->hasRole('super-admin')) {

                $userId = decrypt($request->user_id);

                $UserService = UserService::select('user_id')
                    ->where('user_id', $userId)
                    ->where('service_id', AEPS_SERVICE_ID)
                    ->first();

                if (empty($UserService)) {
                    return ResponseHelper::failed("User service request not found.");
                }

                //fetching van id details
                $UserConfig = UserConfig::select('is_sdk_enable')
                    ->where('user_id', $userId)
                    ->first();

                if (empty($UserConfig)) {
                    return ResponseHelper::failed("User config details not found.");
                }

                if ($UserConfig->is_sdk_enable === '1') {
                    $status = 'INACTIVE';
                } else {
                    $status = 'ACTIVE';
                }

                //updated record
                UserConfig::where('user_id', $userId)->update([
                    'is_sdk_enable' => ($status === "ACTIVE") ? '1' : '0'
                ]);

                return ResponseHelper::success('SDK status updated successfully.', ['status' => $status]);

            } else {
                return abort(404);
            }
        }
        catch (Exception $e) {
            $message = $e->getMessage();
            return ResponseHelper::missing($message);
        }
    }

    /**
     * Update MATM SDK Status
     */
    public function matmStatus(Request $request)
    {
        try {
            if(Auth::user()->hasRole('super-admin')) {

                $userId = decrypt($request->user_id);

                //fetching van id details
                $UserService = UserService::select('user_id')
                    ->where('user_id', $userId)
                    ->where('service_id', MATM_SERVICE_ID)
                    ->first();
 
                if (empty($UserService)) {
                    return ResponseHelper::failed("User service request not found.");
                }

                //fetching van id details
                $UserConfig = UserConfig::select('is_matm_enable')
                    ->where('user_id', $userId)
                    ->first();

                if (empty($UserConfig)) {
                    return ResponseHelper::failed("User config details not found.");
                }

                if ($UserConfig->is_matm_enable === '1') {
                    $status = 'INACTIVE';
                } else {
                    $status = 'ACTIVE';
                }

                //updated record
                UserConfig::where('user_id', $userId)->update([
                    'is_matm_enable' => ($status === "ACTIVE") ? '1' : '0'
                ]);

                return ResponseHelper::success('MATM SDK status updated successfully.', ['status' => $status]);

            } else {
                return abort(404);
            }
        }
        catch (Exception $e) {
            $message = $e->getMessage();
            return ResponseHelper::missing($message);
        }
    }


    /**
     *autoSettlementStatus Status
     */
    public function autoSettlementStatus(Request $request)
    {
        try {
            if(Auth::user()->hasRole('super-admin')) {

                $userId = decrypt($request->user_id);
        
                //fetching van id details
                $UserConfig = UserConfig::select('is_auto_settlement')
                    ->where('user_id', $userId)
                    ->first();

                if (empty($UserConfig)) {
                    return ResponseHelper::failed("User config details not found.");
                }

                if ($UserConfig->is_auto_settlement === '1') {
                    $status = 'INACTIVE';
                } else {
                    $status = 'ACTIVE';
                }

                //updated record
                UserConfig::where('user_id', $userId)->update([
                    'is_auto_settlement' => ($status === "ACTIVE") ? '1' : '0'
                ]);

                return ResponseHelper::success('Auto Settlement status updated successfully.', ['status' => $status]);

            } else {
                return abort(404);
            }

        } catch (Exception $e) {
            $message = $e->getMessage();
            return ResponseHelper::missing($message);
        }
    }


    /**
     * Handle internal fund transfer status
     */
    public function internalTransferStatus(Request $request)
    {
        try {
            if(Auth::user()->hasRole('super-admin')) {

                if (empty($request->user_id)) {
                    return ResponseHelper::failed("User id can't be empty.");
                }

                $userId = decrypt($request->user_id);
        
                //fetching van id details
                $UserConfig = DB::table('user_config')
                    ->select('is_internal_transfer_enable')
                    ->where('user_id', $userId)
                    ->first();

                if (empty($UserConfig)) {
                    return ResponseHelper::failed("User details not found.");
                }

                if ($UserConfig->is_internal_transfer_enable === '0') {
                    $status = 'ACTIVE';
                } else {
                    $status = 'INACTIVE';
                }

                //updated record
                DB::table('user_config')
                    ->where('user_id', $userId)
                    ->update([
                        'is_internal_transfer_enable' => ($status === "ACTIVE") ? '1' : '0',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                return ResponseHelper::success('Internal transfer stutas updated successfully.', ['status' => $status]);

            } else {
                return abort(404);
            }

        } catch (Exception $e) {
            return ResponseHelper::missing($e->getMessage());
        }
    }

    /**
     * Change VAN Status
     */
    public function loadMoneyStatus(Request $request)
    {
        try {
            if(Auth::user()->hasRole('super-admin')) {

                $userId = decrypt($request->user_id);
        
                //fetching van id details
                $UserConfig = UserConfig::select('load_money_request')
                    ->where('user_id', $userId)
                    ->first();

                if (empty($UserConfig)) {
                    return ResponseHelper::failed("User config details not found.");
                }

                if ($UserConfig->load_money_request == '1') {
                    $status = 'INACTIVE';
                } else {
                    $status = 'ACTIVE';
                }

                //updated record
                UserConfig::where('user_id', $userId)->update([
                    'load_money_request' => ($status === "ACTIVE") ? '1' : '0'
                ]);

                return ResponseHelper::success('Load Money status updated successfully.', ['status' => $status]);

            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            return ResponseHelper::missing($message);
        }
    }
}
