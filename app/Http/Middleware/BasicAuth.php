<?php

namespace App\Http\Middleware;

use App\Helpers\ActivityLogHelper;
use Closure;
use Auth;
use App\Models\OauthClient;
use App\Models\IpWhitelist;
use App\Models\Service;
use App\Models\User;
use Storage;
use App\Helpers\AllowedService;
use App\Helpers\ResponseHelper;
use App\Models\GlobalConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
       // dd("6767667");
        $header = $request->header();
        $body = $request->getContent();
        $fourthSeg = (Request::segment(4)) ? Request::segment(4) : '';
        $ip = isset($header['cf-connecting-ip']) ? $header['cf-connecting-ip'] : $request->ip();
        
        if($header['content-type'][0] != 'application/json' && ($fourthSeg !='ekyc')) {
            
            $res["code"] = "0x0201";
            $res["message"] = "Invalid content type";
            $res["status"] = "FAILURE";
            return response()->json($res, 401);
        }
        // dd("hhjhjhj");
        if(isset($header)) {
           
            if(isset($header['php-auth-user'][0]) && isset($header['php-auth-pw'][0])) {
                $hash = hash('sha512', $header['php-auth-pw'][0]);
                $key = $header['php-auth-user'][0];
             
                $service = Request::segment(3);
                   
                if($service == 'upi') {
                    $service = 'upi_collect';
                } else if($service == 'collect') {
                    $service = 'smart_collect';
                }

                // api services checking
                $serviceCheck = self::serviceCheck($service);
                if ($serviceCheck['status'] == false) {
                    $res["code"] = "0x0503";
                    $res["message"] = $serviceCheck['message'];
                    $res["status"] = "FAILURE";
                    return response()->json($res, 503);
                }
                // end api service checking
                    
                $service = Service::select('id','service_id', 'service_slug')->where('service_slug', $service)->first();
              // dd($service);
                if(isset($service)) {
                    $oAuthClient = OauthClient::select('user_id', 'service_id', 'client_key', 'client_secret', 'scope')->where([['client_key', $key], ['service_id', $service->service_id], ['is_active', '1']])->first();
                    //dd($oAuthClient);
                    if(isset($oAuthClient)) {
                        $request['auth_data'] = ['user_id' => $oAuthClient->user_id, 'service_id' => $service->service_id, 'service_name' => $service];
                        $checkHash = hash_equals($hash, $oAuthClient->client_secret);
                        //dd($checkHash);
                        if($checkHash) {
                            if(User::where('id', '=', $oAuthClient->user_id)->where('is_active', '1')->count()) {
                                $GlobalConfig = GlobalConfig::select('attribute_1', 'attribute_2', 'attribute_3')->where(['slug' => $service->service_slug])->first();
                                $isPayoutServiceEnable = true;
                               // dd($GlobalConfig);
                                if (isset($GlobalConfig)) {
                                    $attribute_2 = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "0";
                                    $userIdArray = explode(",",$attribute_2);
                                    if (in_array("$oAuthClient->user_id", $userIdArray)) {
                                        $isPayoutServiceEnable = true;
                                    } else {
                                        if ($GlobalConfig->attribute_1 == 1) {
                                            $isPayoutServiceEnable = true;
                                        }
                                    }
                                } else {
                                    $isPayoutServiceEnable = true;
                                }
                                if ($isPayoutServiceEnable) {
                   
                                    $ipAddress = IpWhitelist::where([['ip', $ip], ['service_id', $service->service_id], ['user_id', $oAuthClient->user_id], ['is_active', '1']])->first();
                                    if(isset($ipAddress)) {
                                        $allowService = AllowedService::allowedService($oAuthClient->user_id, $service->service_id, '11', Request::segment(3), $request);
                                        if($allowService['status'] == true) {
                                            return $next($request);
                                        }else {
                                        return ResponseHelper::unauthorized($allowService['message']);
                                        }
                                    } else {
                                        $res["code"] = "0x0201";
                                        $res["message"] = "Unauthorized IP used.";
                                        $res["status"] = "FAILURE";
                                        $res["ip"] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
                                        return response()->json($res, 401);
                                    }
                                } else {
                                    $message = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : "This service is down. please try after some time.";
                                    $res["code"] = "0x0201";
                                    $res["message"] = $message;
                                    $res["status"] = "FAILURE";
                                    $res["ip"] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
                                    return response()->json($res, 401);
                                }
                            } else {
                                $GlobalConfig = GlobalConfig::select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4', 'attribute_5')
                                    ->where(['slug' => 'user_suspended_message'])->first();
                                $message = "";
                                $userCheck = User::where('id', '=', $oAuthClient->user_id)->first();
                                if (isset($userCheck) && !empty($userCheck)) {
                                    if ($userCheck->is_active == '0') {
                                        $message = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : "Your account is initiate. Please contact  to your Account Coordinator";
                                    } else if ($userCheck->is_active == '2') {
                                        $message = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "Your account is inactive. Please contact  to your Account Coordinator";
                                    } else if ($userCheck->is_active == '3') {
                                        $message = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : "Your account is suspended. Please contact  to your Account Coordinator";
                                    } else if ($userCheck->is_active == '4') {
                                        $message = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : "Your account is permanently blocked. Please contact  to your Account Coordinator";
                                    }
                                } else {
                                    $message = isset($GlobalConfig->attribute_5) ? $GlobalConfig->attribute_5 : "Your account dose not exits. Please contact  to your Account Coordinator";
                                }
                                $res["code"] = "0x0201";
                                $res["message"] = $message;
                                $res["status"] = "FAILURE";
                                return response()->json($res, 401);
                            }
                        } else {
                            $res["code"] = "0x0201";
                            $res["message"] = "Credentials doesn't match our records.";
                            $res["status"] = "FAILURE";
                            return response()->json($res, 401);
                        }
                    } else {
                        $res["code"] = "0x0201";
                        $res["message"] = "Invalid credentials used";
                        $res["status"] = "FAILURE";
                        return response()->json($res, 401);
                    }
                } else {
                    $res["code"] = "0x0201";
                    $res["message"] = "Unauthorized service request";
                    $res["status"] = "FAILURE";
                    return response()->json($res, 401);
                }
            } else {
                $res["code"] = "0x0201";
                $res["message"] = "Invalid authorization";
                $res["status"] = "FAILURE";
                return response()->json($res, 401);
            }
        } else {
            
            $res["code"] = "0x0201";
            $res["message"] = "Authorization failure";
            $res["status"] = "FAILURE";
            return response()->json($res, 401);
        }
    }

    public static function serviceCheck($slug = '')
    {
        // dd("hhjhjhj");
        $resp['status'] = false;
        $resp['message'] = "Service Unavailable";
        $arr = [];
        $obj = DB::table('global_config')
            ->where('slug','is_api_services_enable')
            ->first();
        if (isset($obj)) {
            if ($obj->attribute_1 == 2) {
                $arr = explode(',', $obj->attribute_2);
                if (in_array($slug, $arr)) {
                    $resp['status'] = true;
                }
            } else if($obj->attribute_1 == 1) {
                $resp['status'] = true;
            }
            $resp['message'] = $obj->attribute_3;
        }

        return $resp;
    }

}