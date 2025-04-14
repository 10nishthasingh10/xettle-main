<?php

namespace App\Http\Middleware;

use App\Helpers\RequestKeeper;
use App\Helpers\ResponseHelper;
use Closure;


class HashRequest
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



        // $getResposne =  ___globalSetting(['is_maintenance'], true);
        // if (isset($getResposne->attribute_1) && $getResposne->attribute_1 == 1) {
        //     return  ResponseHelper::maintenance($getResposne->attribute_2);
        // }

        // if (isset($request->mobile) && $request->mobile == '9651807986' || $request->mobile == '6393784138') {
        //     return $next($request);
        // }

        if (in_array($request->area, ['app', 'web'])) {

            // $isWebLoginEnable =  ___globalSetting(['is_web_login_enable'], true);

            // if ($request->area == 'web' && isset($isWebLoginEnable->attribute_1) && $isWebLoginEnable->attribute_1 == '0') {
            //     $exp = explode(',', $isWebLoginEnable->attribute_2);
            // if (auth('sanctum')->check()) {
            //     $mobile = auth('sanctum')->user()->mobile;
            // } else {
            //     $mobile = $request->mobile;
            // }

            // if (isset($mobile) && !empty($mobile) && !in_array($mobile, $exp)) {
            //     return  ResponseHelper::maintenance('The Server is Down for Maintenance.');
            // }
            // }

            if (!empty($header['requesthash'][0])) {

                $resp = (new RequestKeeper())->validateRequest(
                    $header['requesthash'][0],
                    last($request->segments()),
                    $request->area,
                    $request
                );

                if ($resp['status'] == 'SUCCESS') {
                    return $next($request);
                } else {
                    return ResponseHelper::unauthorized($resp['message']);
                }
            } else {
                return ResponseHelper::failed('Invalid request');
            }
        } else {
            return ResponseHelper::failed('Please send area.');
        }

        return ResponseHelper::failed('Invalid request');
    }
}
