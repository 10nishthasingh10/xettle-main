<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValidateCreationApiLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $service = $request->segment(3);
        $userId = @$request['auth_data']['user_id'];
        $limit = 0;

        switch ($service) {

                /**
             * **************************
             * for smart collect service
             * **************************
             */
            case 'collect':

                $serviceType = $request->serviceType;

                switch ($serviceType) {
                    case 'van':

                        //select limit from user config
                        $userConfig = DB::table('user_config')
                            ->select('smart_collect_vpa_van_limit')
                            ->where('user_id', $userId)
                            ->first();

                        if (!empty($userConfig->smart_collect_vpa_van_limit)) {
                            $limitJson = $userConfig->smart_collect_vpa_van_limit;
                            $limitJson = json_decode($limitJson);

                            if (!empty($limitJson->van)) {
                                $limit = $limitJson->van;
                            }
                        }

                        //when user config is not set, get from global config
                        if ($limit === 0) {
                            $globalConfig = DB::table('global_config')
                                ->select('attribute_3')
                                ->where('slug', 'smart_collect')
                                ->first();

                            $limitJson = $globalConfig->attribute_3;
                            $limitJson = json_decode($limitJson);

                            if (!empty($limitJson->van)) {
                                $limit = $limitJson->van;
                            }
                        }

                        //fetch number of VANs created by the user
                        $vanCount = DB::table('cf_merchants')
                            ->where('service_type', 'van')
                            ->where('user_id', $userId)
                            ->count();

                        // dd($vanCount, $limit);

                        if ($vanCount < $limit) {
                            return $next($request);
                        }

                        return ResponseHelper::failed('VAN creation limit has been reached. Please contact us for more details.');
                        break;

                    case 'upi':

                        //select limit from user config
                        $userConfig = DB::table('user_config')
                            ->select('smart_collect_vpa_van_limit')
                            ->where('user_id', $userId)
                            ->first();

                        if (!empty($userConfig->smart_collect_vpa_van_limit)) {
                            $limitJson = $userConfig->smart_collect_vpa_van_limit;
                            $limitJson = json_decode($limitJson);

                            if (!empty($limitJson->upi)) {
                                $limit = $limitJson->upi;
                            }
                        }

                        //when user config is not set, get from global config
                        if ($limit === 0) {
                            $globalConfig = DB::table('global_config')
                                ->select('attribute_3')
                                ->where('slug', 'smart_collect')
                                ->first();

                            $limitJson = $globalConfig->attribute_3;
                            $limitJson = json_decode($limitJson);

                            if (!empty($limitJson->upi)) {
                                $limit = $limitJson->upi;
                            }
                        }

                        //fetch number of VANs created by the user
                        $vanCount = DB::table('cf_merchants')
                            ->where('service_type', 'upi')
                            ->where('user_id', $userId)
                            ->count();

                        // dd($vanCount, $limit);

                        if ($vanCount < $limit) {
                            return $next($request);
                        }

                        return ResponseHelper::failed('UPI creation limit has been reached. Please contact us for more details.');
                        break;
                }

                break;


                /**
                 * ***********************
                 * for UPI Stack Service
                 * ***********************
                 */
            case 'upi':
                $serviceType = $request->root;

                switch ($serviceType) {

                    case 'indus':

                        //select limit from user config
                        $userConfig = DB::table('user_config')
                            ->select('upi_stack_vpa_limit')
                            ->where('user_id', $userId)
                            ->first();

                        if (!empty($userConfig->upi_stack_vpa_limit)) {
                            $limitJson = $userConfig->upi_stack_vpa_limit;
                            $limitJson = json_decode($limitJson);

                            if (!empty($limitJson->ibl)) {
                                $limit = $limitJson->ibl;
                            }
                        }

                        //when user config is not set, get from global config
                        if ($limit === 0) {
                            $globalConfig = DB::table('global_config')
                                ->select('attribute_3')
                                ->where('slug', 'upi_collect')
                                ->first();

                            $limitJson = $globalConfig->attribute_3;
                            $limitJson = json_decode($limitJson);

                            if (!empty($limitJson->ibl)) {
                                $limit = $limitJson->ibl;
                            }
                        }

                        //fetch number of VANs created by the user
                        $iblCount = DB::table('upi_merchants')
                            ->where('root_type', 'ibl')
                            ->where('user_id', $userId)
                            ->count();

                        // dd($iblCount, $limit);

                        if ($iblCount < $limit) {
                            return $next($request);
                        }

                        return ResponseHelper::failed('UPI Creation Limit has been reached. Please contact us for more details.');
                        break;

                    default:

                        //select limit from user config
                        $userConfig = DB::table('user_config')
                            ->select('upi_stack_vpa_limit')
                            ->where('user_id', $userId)
                            ->first();

                        if (!empty($userConfig->upi_stack_vpa_limit)) {
                            $limitJson = $userConfig->upi_stack_vpa_limit;
                            $limitJson = json_decode($limitJson);

                            if (!empty($limitJson->fpay)) {
                                $limit = $limitJson->fpay;
                            }
                        }

                        //when user config is not set, get from global config
                        if ($limit === 0) {
                            $globalConfig = DB::table('global_config')
                                ->select('attribute_3')
                                ->where('slug', 'upi_collect')
                                ->first();

                            $limitJson = $globalConfig->attribute_3;
                            $limitJson = json_decode($limitJson);

                            if (!empty($limitJson->fpay)) {
                                $limit = $limitJson->fpay;
                            }
                        }

                        //fetch number of VANs created by the user
                        $fpayCount = DB::table('upi_merchants')
                            ->where('root_type', 'fpay')
                            ->where('user_id', $userId)
                            ->count();

                        // dd($fpayCount, $limit);

                        if ($fpayCount < $limit) {
                            return $next($request);
                        }

                        return ResponseHelper::failed('UPI Creation limit has been reached. Please contact us for more details.');
                        break;
                }

                break;
        }

        return ResponseHelper::swwrong('Invalid request. Please contact us for more details.');
    }
}
