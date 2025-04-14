<?php

namespace App\Http\Controllers\Spa\Offers\v1;

use App\Helpers\Offers\OfferHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Clients\Api\v1\PanCardController;
use App\Http\Controllers\Controller;
use App\Services\PanCard\PanCardService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validations\PanCardValidation as Validations;
class TokenAuthSpaController extends Controller
{

    /**
     * Authorize Token
     */
    public function authorizeToken(Request $request)
    {
        try {

            if (empty($request->authToken)) {
                return ResponseHelper::failed('Invalid or empty token');
            }

            $token = trim($request->authToken);
            $token = base64_decode($token);
            $token = explode('|', $token);

            if (count($token) !== 4) {
                return ResponseHelper::failed('Invalid token');
            }

            // $timestamp = @$token[0];
            $userId = @$token[1];
            $token = @$token[2];

            $userToken = DB::table('web_auth')
                ->select('*')
                ->where('user_id', $userId)
                ->where('token', $token)
                ->whereNull('session_start')
                ->first();

            if (empty($userToken)) {
                return ResponseHelper::failed('Token is invalid ot not found');
            }

            DB::beginTransaction();

            if (!empty($userToken->agent_id)) {
                $agentId = OfferHelper::fetchAgentInfo($userToken->agent_id);
            } else {
                $agentId = OfferHelper::fetchAgentUserId($userToken);
            }

            $spaToken = OfferHelper::generateToken($userToken->user_id, $agentId->id, $userToken->id);


            DB::table('web_auth')
                ->where('id', $userToken->id)
                ->update([
                    'session_start' => date('Y-m-d H:i:s'),
                    'ip' => request()->ip(),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            DB::commit();

            return ResponseHelper::success('Token authorised', [
                'accessToken' => $spaToken,
                'name' => $agentId->name,
                'email' => $agentId->email,
                'mobile' => $agentId->mobile,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Authorize Token
     */
    public function authorizeTokenForPan(Request $request)
    {
        try {

            if (empty($request->authToken)) {
                return ResponseHelper::failed('Invalid or empty token');
            }

            $token = trim($request->authToken);
            $token = base64_decode($token);
            $token = explode('|', $token);

            if (count($token) !== 4) {
                return ResponseHelper::failed('Invalid token');
            }

            // $timestamp = @$token[0];
            $userId = @$token[1];
            $token = @$token[2];

            $userToken = DB::table('web_auth')
                ->select('*')
                ->where('user_id', $userId)
                ->where('token', $token)
                //->whereNull('session_start')
                ->first();

            if (empty($userToken)) {
                return ResponseHelper::failed('Token is invalid ot not found');
            }

            DB::beginTransaction();

            // if (!empty($userToken->agent_id)) {
            //     $agentId = OfferHelper::fetchAgentInfo($userToken->agent_id);
            // } else {
            //     $agentId = OfferHelper::fetchAgentUserId($userToken);
            // }

            // $spaToken = OfferHelper::generateToken($userToken->user_id, $agentId->id, $userToken->id);


            DB::table('web_auth')
                ->where('id', $userToken->id)
                ->update([
                    'session_start' => date('Y-m-d H:i:s'),
                    'ip' => request()->ip(),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            DB::commit();

            return ResponseHelper::success('Token authorised', [
                'status' => 'success'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::swwrong('Somthing went wrong.', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Authorize Token
     */
    public function panFormSubmit(Request $request)
    {
        try {


            if (empty($request->authToken)) {
                return ResponseHelper::failed('Invalid or empty token');
            }

            $token = trim($request->authToken);
            $token = base64_decode($token);
            $token = explode('|', $token);

            if (count($token) !== 4) {
                return ResponseHelper::failed('Invalid token');
            }

            // $timestamp = @$token[0];
            $userId = @$token[1];
            $token = @$token[2];

            $userToken = DB::table('web_auth')
                ->select('*')
                ->where('user_id', $userId)
                ->where('token', $token)
                ->whereNotNull('session_start')
                //->where('created_at', '>=',DB::raw('DATE_SUB(NOW(),INTERVAL 1 HOUR)'))
                ->first();

            if (empty($userToken)) {
                return ResponseHelper::failed('Token is invalid or not found');
            }


            $validations = Validations::init($request, 'txnInitFromNSDl');

            if ($validations['status'] == true) {
                $txn = DB::table('pan_txns')
                        ->where('order_ref_id', $request->orderRefId)
                        ->first();
                if (!empty($txn)) {
                    return ResponseHelper::failed('The order ref id is invalid.');
                }

                $agent = DB::table('pan_agents')
                    ->where('psa_id', $request->psaId)
                    ->where('user_id', $userId)
                    ->first();
                if (empty($agent)) {
                    return ResponseHelper::failed('The psaId is invalid.');
                }


                return PanCardController::txnInitFromNSDl($request, new PanCardService, $userId);

        } else {
            return ResponseHelper::failed($validations['message']);
        }
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::swwrong('Somthing went wrong.', ['message' => $e->getMessage()]);
        }
    }
}
