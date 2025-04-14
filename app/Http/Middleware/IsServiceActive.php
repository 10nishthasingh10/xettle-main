<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use CommonHelper;
use Auth;
class IsServiceActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $service)
    {
        if (! CommonHelper::isServiceActive(Auth::user()->id, $service)) {
            return redirect('user/dashboard');
        }

        return $next($request);
    }
}
