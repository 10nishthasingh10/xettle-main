<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OfferTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        try {
            $accessToken = $request->header('accesstoken');

            if (empty($accessToken)) {
                return ResponseHelper::unauthorized('Unauthorized');
            }

            $accessToken = base64_decode($accessToken);
            $accessToken = explode('|', $accessToken);

            if (count($accessToken) !== 2) {
                return ResponseHelper::unauthorized('Invalid token');
            }

            $tokenId = trim(@$accessToken[0]);
            $token = trim(@$accessToken[1]);


            $authTokenInfo = DB::table('web_agent_tokens')
                ->where('id', $tokenId)
                ->where('token', $token)
                ->where('expire_at', '>', time())
                ->first();

            if (empty($authTokenInfo)) {
                return ResponseHelper::unauthorized('Token has been expired.');
            }

            $request->attributes->add(['userTokenId' => $tokenId]);
            $request->attributes->add(['userAuthToken' => $token]);
            $request->attributes->add(['userAgentId' => $authTokenInfo->agent_id]);
            $request->attributes->add(['userAgentLogId' => $authTokenInfo->agent_log_id]);
            $request->attributes->add(['userId' => $authTokenInfo->user_id]);

            return $next($request);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Somthing went wrong.', ['message' => $e->getMessage()]);
        }
    }
}
