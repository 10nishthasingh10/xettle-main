<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Agent;
use App\Models\GlobalConfig;
use App\Models\UserConfig;
use App\Models\UserService;
use File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

class SDKauth
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
        $header = $request->header();
        if ($header['content-type'][0] != 'application/json') {
            $res["code"] = "0x0201";
            $res["message"] = "Invalid content type";
            $res["status"] = "FAILURE";
            return response()->json($res, 401);
        }
        if (isset($header)) {
            if ((isset($request->sdkVer) && !empty($request->sdkVer)) &&  (isset($request->reference) && !empty($request->reference))) {
                if (isset($header['appid'][0]) && isset($header['appsecret'][0])) {
                    $appId = $header['appid'][0];
                    $appSecret = hash('sha256', $header['appsecret'][0]);
                    $userConfig = UserConfig::select('user_id','is_sdk_enable', 'app_id', 'app_secret')
                        ->where(['app_id' => $appId])
                        ->first();
                    if (isset($userConfig) && hash_equals($appSecret, $userConfig->app_secret)) {
                        if ($userConfig->is_sdk_enable == 1) {
                            $userStatus = User::select('id', 'is_active')
                                ->where(['id' => $userConfig->user_id])
                                ->first();
                            if (isset($userStatus)) {

                                if ($userStatus->is_active == 1) {
                                    if (isset($request->merchantCode) && !empty($request->merchantCode)) {
                                        $userService = UserService::where(['user_id' => $userConfig->user_id, 'service_id' => AEPS_SERVICE_ID])
                                            ->where('is_active' , '1')
                                            ->first();
                                            if (isset($userService) && !empty($userService)) {
                                                $agent = Agent::where(['user_id' => $userConfig->user_id, 'merchant_code' => $request->merchantCode])
                                                    ->first();
                                                    if (isset($agent) && !empty($agent)) {
                                                        if ($agent->is_active == "1") {
                                                            $data = json_decode($agent->ekyc, TRUE);
                                                            if (!empty($data) && $data != 1 && count($data)) {
                                                                $ekycStatusCheck = 0;
                                                                $i = 0;
                                                                $route = [];
                                                                foreach ($data as $key => $datas) {

                                                                    if ($datas['is_ekyc'] == 1) {
                                                                        $i++;
                                                                        $ekycStatusCheck = 1;
                                                                        if (in_array($key, ['airtel', 'icici', 'paytm', 'sbm'])) {
                                                                            $imgPath = url("public/images/banks/$key.png");
                                                                        }
                                                                        $routeName = self::getRouteName($key);
                                                                        if ($i  == 1) {
                                                                            $route[] = ['routeName' => $routeName, 'routeSlug' => $key, 'routeType' => 'recommended', 'routeImage' => $imgPath];
                                                                        } else {
                                                                            if ($i == 2) {
                                                                                $route[] = ['routeName' => $routeName, 'routeSlug' => $key, 'routeType' => 'primary', 'routeImage' => $imgPath];
                                                                            }
                                                                            if ($i == 3) {
                                                                                $route[] = ['routeName' => $routeName, 'routeSlug' => $key, 'routeType' => 'secondary', 'routeImage' => $imgPath];
                                                                            }
                                                                            if ($i == 4) {
                                                                                $route[] = ['routeName' => $routeName, 'routeSlug' => $key, 'routeType' => 'tertiary', 'routeImage' => $imgPath];
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                if ($ekycStatusCheck) {
                                                                    $segment = Request::segment(3);
                                                                    if ($segment == 'init') {
                                                                        $request['ekycList'] = $route;
                                                                    }
                                                                    $request['auth_data'] = ['user_id' => $userConfig->user_id];
                                                                    return $next($request);
                                                                } else {
                                                                    $res["code"] = "0x0201";
                                                                    $res["message"] = "ekyc not activated.";
                                                                    $res["status"] = "FAILURE";
                                                                    return response()->json($res, 401);
                                                                }

                                                            } else {
                                                                $res["code"] = "0x0201";
                                                                $res["message"] = "No ekyc details found.";
                                                                $res["status"] = "FAILURE";
                                                                return response()->json($res, 401);
                                                            }
                                                        } else {
                                                            $res["code"] = "0x0201";
                                                            $res["message"] = "Inactive merchant";
                                                            $res["status"] = "FAILURE";
                                                            return response()->json($res, 401);
                                                        }
                                                    } else {
                                                        $res["code"] = "0x0201";
                                                        $res["message"] = "Invalid merchant code.";
                                                        $res["status"] = "FAILURE";
                                                        return response()->json($res, 401);
                                                    }
                                            } else {
                                                $res["code"] = "0x0201";
                                                $res["message"] = "Aeps service not enable";
                                                $res["status"] = "FAILURE";
                                                return response()->json($res, 401);
                                            }
                                    } else {
                                        $res["code"] = "0x0201";
                                        $res["message"] = "Merchant code is required";
                                        $res["status"] = "FAILURE";
                                        return response()->json($res, 401);
                                    }
                                } else {
                                    $GlobalConfig = GlobalConfig::select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4', 'attribute_5')
                                            ->where(['slug' => 'user_suspended_message'])->first();
                                    $message = "";

                                    if ($userStatus->is_active == '0') {
                                        $message = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : "Your account is initiate. Please contact  to your Account Coordinator";
                                    } else if ($userStatus->is_active == '2') {
                                        $message = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "Your account is inactive. Please contact  to your Account Coordinator";
                                    } else if ($userStatus->is_active == '3') {
                                        $message = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : "Your account is suspended. Please contact  to your Account Coordinator";
                                    } else if ($userStatus->is_active == '4') {
                                        $message = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : "Your account is permanently blocked. Please contact  to your Account Coordinator";
                                    }
                                    $res["code"] = "0x0201";
                                    $res["message"] = $message;
                                    $res["status"] = "FAILURE";
                                    return response()->json($res, 401);
                                }
                            } else {
                                $res["code"] = "0x0201";
                                $res["message"] = "User account not found";
                                $res["status"] = "FAILURE";
                                return response()->json($res, 401);
                            }
                        } else {
                            $res["code"] = "0x0201";
                            $res["message"] = "SDK is not enable.";
                            $res["status"] = "FAILURE";
                            return response()->json($res, 401);
                        }
                    } else {
                        $res["code"] = "0x0201";
                        $res["message"] = "Invalid app id and app secret.";
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
                $res["message"] = "SDK Version and Reference both required.";
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

    /**
     * getRouteName
     *
     * @param  mixed $route
     * @return void
     */
    public static function getRouteName($route)
    {
        if ($route == 'sbm') {
            return 'SBM Bank';
        } elseif ($route == 'airtel') {
            return 'Airtel Bank';
        } elseif ($route == 'paytm') {
            return 'Paytm Bank';
        } elseif ($route == 'icici') {
            return 'ICICI Bank LTD';
        }
        return '';
    }
}