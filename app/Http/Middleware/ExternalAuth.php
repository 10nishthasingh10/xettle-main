<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;

class ExternalAuth
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
        
        // $body = $request->getContent();
        $ip = isset($header['cf-connecting-ip']) ? $header['cf-connecting-ip'] : $request->ip();
        if(!isset($header['content-type']) || $header['content-type'][0] != 'application/json') {
            $res["code"] = "0x0201";
            $res["message"] = "Invalid content type";
            $res["status"] = "FAILURE";
            return response()->json($res, 401);
        }

        return $next($request);
    }
}