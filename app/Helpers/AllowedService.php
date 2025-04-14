<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class AllowedService
{

    // check user web or api is enable and then check web_value = NEFT,RTGS,IMPS or COLLECT_RECEIVE,COLLECT or{"ICICI":"CW,BE,CD","SBM":"CW"} api_value = NEFT,RTGS,IMPS or COLLECT_RECEIVE,COLLECT or {"ICICI":"CW,BE,CD","SBM":"CW"}
    /**
     * enableUserService function 
     * $area => 00: web , 11:api
     * $route => SBM,ICICI,IMPS,RTGS,NEFT,UPI_COLLECT,UPI_RECEIVE,UPI,VAN
     */
    public static function enableUserService($userId, $serviceId, $area, $route, $search = "")
    {
        $resp['status'] = true;
        $resp['message'] = "All services access allow";
        $route = isset($route) ? CommonHelper::case($route, 'l') : "";
        $search = isset($search) ? CommonHelper::case($search, 'l') : "";
        if($area == "00") {
            $data = DB::table('user_services')->select('is_web_enable')->where('user_id', $userId)->where('service_id', $serviceId)->first();
            if(isset($data) && $data->is_web_enable == '1') {
               $serivceSlug = DB::table('global_services')->where('service_id', $serviceId)->first()->service_slug;
               if($serivceSlug == 'payout') {
                    $serivceWebValue = DB::table('user_services')
                        ->select('web_value')
                        ->where('service_id', $serviceId)
                        ->where('user_id', $userId)
                        ->first()
                        ->web_value;
                    if(isset($serivceWebValue) && str_contains($serivceWebValue->web_value, $route) == false) {
                        $resp['status'] = false;
                        $resp['message'] = $route." paymode not activated";
                        $resp['area'] = 0;
                    }
               }else if($serivceSlug == 'aeps' && !empty($route)) {
                    if (in_array($route, ['sbm', 'airtel', 'icici', 'paytm'])) {
                        $serivceWebValue = DB::table('user_services')
                            ->select(DB::raw("json_extract(api_value, '$.$route') as route"))
                            ->where('user_id', $userId)
                            ->where('service_id', $serviceId)
                            ->first();
                        if (isset($serivceWebValue) && !str_contains($serivceWebValue->route, $search)) {
                            $resp['status'] = false;
                            $resp['message'] = $route . " route is not activated";
                            $resp['area'] = 0;
                        }
                        $routeCheck = self::aepsRouteCheck($userId, $route);
                        if ($routeCheck['status'] == false) {
                            $resp['status'] = false;
                            $resp['message'] = $routeCheck['message'];
                            $resp['area'] = 0;
                        }
                    } else {
                        $resp['status'] = false;
                        $resp['message'] = 'Invalid route type';
                        $resp['area'] = 0;
                    }
               }else if($serivceSlug == 'upi_collect') {
                    $serivceWebValue = DB::table('user_services')
                        ->select('web_value')
                        ->where('service_id', $serviceId)
                        ->where('user_id', $userId)
                        ->first();
                    if(isset($serivceWebValue) && !str_contains($serivceWebValue->web_value, $route)) {
                        $resp['status'] = false;
                        $resp['message'] = $route."  not activated";
                        $resp['area'] = 0;
                    }
               }
            }else {
                $resp['status'] = false;
                $resp['message'] = "Currently web service unavailable";
                $resp['area'] = 0;
            }
        }else if($area == "11") {
            $data = DB::table('user_services')->select('is_api_enable', 'api_value')->where('user_id', $userId)->where('service_id', $serviceId)->first();
            if(isset($data) && $data->is_api_enable == '1') {
                $serivceSlug = DB::table('global_services')->where('service_id', $serviceId)->first()->service_slug;
                if($serivceSlug == 'payout') {
                     $serivceWebValue = DB::table('user_services')
                         ->select('api_value')
                         ->where('service_id', $serviceId)
                         ->where('user_id', $userId)
                         ->first()
                         ->api_value;
                     if(!str_contains($serivceWebValue, $search)) {
                         $resp['status'] = false;
                         $resp['message'] = $search." paymode not activated";
                         $resp['area'] = 0;
                     }
                }else if($serivceSlug == 'aeps' && !empty($route)) {

                    if (in_array($route, ['sbm', 'airtel', 'icici', 'paytm'])) {
                        $serivceWebValue = DB::table('user_services')
                            ->select(DB::raw("json_extract(api_value, '$.$route') as route"))
                            ->where('user_id', $userId)
                            ->where('service_id', $serviceId)
                            ->first();
                        if (isset($serivceWebValue) && !str_contains($serivceWebValue->route, $search)) {
                            $resp['status'] = false;
                            $resp['message'] = $route . " route is not activated";
                            $resp['area'] = 0;
                        }
                        $routeCheck = self::aepsRouteCheck($userId, $route);
                        if ($routeCheck['status'] == false) {
                            $resp['status'] = false;
                            $resp['message'] = $routeCheck['message'];
                            $resp['area'] = 0;
                        }
                    } else {
                        $resp['status'] = false;
                        $resp['message'] = 'Invalid route type';
                        $resp['area'] = 0;
                    }

                }else if($serivceSlug == 'upi_collect') {
                     $serivceWebValue = DB::table('user_services')
                         ->select('api_value')
                         ->where('service_id', $serviceId)
                         ->where('user_id', $userId)
                         ->first()
                         ->api_value;
                     if(!str_contains($serivceWebValue, $route)) {
                         $resp['status'] = false;
                         $resp['message'] = $route."  not activated";
                         $resp['area'] = 0;
                     }
                }else if($serivceSlug == 'smart_collect') {
                    $serivceWebValue = DB::table('user_services')
                        ->select('api_value')
                        ->where('service_id', $serviceId)
                        ->where('user_id', $userId)
                        ->first()
                        ->api_value;
                    if(!str_contains($serivceWebValue, $route)) {
                        $resp['status'] = false;
                        $resp['message'] = strtoupper($route) . " collect service is not activated for this account.";
                        $resp['area'] = 0;
                    }
               } else if($serivceSlug == SRV_SLUG_VA) {
                    if(!str_contains($data->api_value, $route)) {
                        $resp['status'] = false;
                        $resp['message'] = "Service is not activated for this account.";
                        $resp['area'] = 0;
                    }
               }
            }else {
                $resp['status'] = false;
                $resp['message'] = "Currently api service unavailable";
                $resp['area'] = 0;
            }
        }
        return $resp;
    }

    public static function allowedService($userId, $serviceId, $area, $slug, $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";
        $route = "";
        $search = "";
        $requestArray = $request->all();
        if($slug == 'payout') {
            if(isset($requestArray['mode'])) {
                $route = $requestArray['mode'];
                $search = $requestArray['mode'];
            }
        }else if($slug == 'aeps') {
            if(isset($requestArray['routeType'])) {
                $route = $requestArray['routeType'];
                if(collect(request()->segments())->last() == 'statement')
                {
                    $search = "ms";
                }else if(collect(request()->segments())->last() == 'getBalance')
                {
                    $search = "be";
                }else if(collect(request()->segments())->last() == 'withdrawal')
                {
                    $search = "cw";
                }else if(collect(request()->segments())->last() == 'aadharPay')
                {
                    $search = "ap";
                }
            }
        }else if($slug == 'upi') {
            if(collect(request()->segments())->last() == 'collect')
            {
                $search = "upi_collect";
                $route = "upi_collect";
            }else if(collect(request()->segments())->last() == 'merchant') {
                $search = "upi_receive";
                $route = "upi_receive";
            }
        }else if($slug == 'collect') {
            if (collect(request()->segments())->last() == 'merchant') {
                $search = "";
                $route = $request->serviceType;
            }
        }else if($slug == SRV_SLUG_VA) {
            if($request->segment(4) === 'vpa'){
                $route = "upi";
            }
        }

        $checkUserService = self::enableUserService($userId, $serviceId, $area, $route, $search);
        if(isset($route) && $checkUserService['status'] == false) {
            $resp['status'] = false;
            $resp['message'] = $checkUserService['message'];
        }else {
            $resp['status'] = true;
            $resp['message'] = "Access allowed";
        }
       return $resp;
    }

    public static function aepsRouteCheck($userId, $route)
    {
        $resp['status'] = true;
        $resp['message'] = "This route is enable";
        try {
            $globalConfig = DB::table('global_config')
                ->where('slug', 'aeps_active_routes')
                ->select('attribute_1', 'attribute_2', 'attribute_3')->first();
            if (isset($globalConfig) && !empty($globalConfig) && isset($route) && !empty($route)) {
                $route = CommonHelper::case($route, 'l');
                if (!empty($globalConfig->attribute_1) && $globalConfig->attribute_1 == '1') {
                    if (!empty($globalConfig->attribute_2)) {
                        $routes = explode(",", $globalConfig->attribute_2);
                        if (!in_array($route, $routes)) {
                            if (!empty($globalConfig->attribute_3)) {
                                $userWiseRoute = json_decode($globalConfig->attribute_3, true);
                                if (isset($userWiseRoute) && count($userWiseRoute) > 0) {
                                    if (isset($userWiseRoute[$userId]) && !empty($userWiseRoute[$userId]) ) {
                                        $route = CommonHelper::case($route, 'l');
                                        $routes = explode(",", $userWiseRoute[$userId]);
                                        if (in_array($route, $routes)) {
                                            $resp['status'] = true;
                                            $resp['message'] = "$route bank route is allowed";
                                        } else {
                                            $resp['status'] = false;
                                            $resp['message'] = "$route bank route is not active";
                                        }
                                    } else {
                                        $resp['status'] = false;
                                        $resp['message'] = "$route bank route is not active";
                                    }
                                } else {
                                    $resp['status'] = false;
                                    $resp['message'] = "$route bank route is not active";
                                }
                            } else {
                                $resp['status'] = false;
                                $resp['message'] = "$route bank route is not active";
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Storage::put('errorlog_public/aepsRouteError.txt', $e->getMessage());
        }
       return $resp;
    }
}