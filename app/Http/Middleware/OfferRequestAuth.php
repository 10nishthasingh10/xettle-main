<?php

namespace App\Http\Middleware;

use App\Helpers\Offers\OfferRequestHelper;
use App\Helpers\ResponseHelper;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class OfferRequestAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        try {
            $requestHash = $request->header('requesthash');

            if (empty($requestHash)) {
                return ResponseHelper::unauthorized('Unauthorized Request.');
            }

            $resp = (new OfferRequestHelper())->validateRequest(
                $requestHash,
                last($request->segments())
            );

            if ($resp['status'] === 'SUCCESS') {
                return $next($request);
            }
            return ResponseHelper::unauthorized($resp['message']);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Somthing went wrong.', ['message' => $e->getMessage()]);
        }
    }
}
