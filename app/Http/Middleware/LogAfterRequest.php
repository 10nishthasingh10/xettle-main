<?php
namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;
class LogAfterRequest {

    /**
     * handle
     *
     * @param  mixed $request
     * @param  mixed $next
     * @return void
     */
    public function handle($request, \Closure $next)
    {
        $url = $request->fullUrl();
        $header = $request->header();
       
        $ip = isset($header['cf-connecting-ip']) ? $header['cf-connecting-ip'] : $request->ip();
        $data = $request->all();
        $serviceName = isset($data['auth_data']['service_name']) ? $data['auth_data']['service_name'] : NULL;
        $service = $request->segment(3);
        if($service == 'upi') {
            $service = 'upi_collect';
        } else if($service == 'collect') {
            $service = 'smart_collect';
        }
        $serviceName = isset($serviceName) ? $serviceName->service_slug  : $service;
        $userId = isset($data['auth_data']['user_id']) ? $data['auth_data']['user_id'] : "0";
        $method = $request->getMethod();
        $data = $request->all();
        unset($data['auth_data']);
        $headerData['user-agent'] = @$header['user-agent'][0];
        $headerData['php-auth-user'] = @$header['php-auth-user'][0];
        $request->lastInsertedId = \App\Models\MUserApiLog::insertLog($ip, $url, $method, $userId, $serviceName, json_encode($data), json_encode($headerData));

        return $next($request);
    }

    /**
     * terminate
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return void
     */
    public function terminate($request, $response)
    {
       $data = $request->all();
        \App\Models\MUserApiLog::updateLog($request->lastInsertedId, ['response' => $response->getContent()]);
        unset($data['auth_data'], $data['lastInsertedId']);
    }
}