<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OffersAuthController extends Controller
{
    /**
     * Generate token
     */
    public function generateAuthToken(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'mobile' => "required|digits:10",
                    'email' => "required|email|max:150",
                    'name' => "required|max:100|regex:/^([a-zA-Z0-9 ]+)$/"
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Missing Parameters', $message);
            }

            //getting user_id
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }

            $mobile = trim($request->mobile);
            $name = trim($request->name);
            $email = strtolower(trim($request->email));

            $token = CommonHelper::getRandomString('OTKN', false, 26);

            $tokenSupport = CommonHelper::getRandomString('', false, 8);

            $createdAt = date('Y-m-d H:i:s');
            $responseToken = trim(base64_encode(time() . "|" . $userId . "|" . $token . "|" . $tokenSupport), '=');

            DB::beginTransaction();

            //check offer agent
            $offerAgent = DB::table('web_agents')
                ->select('id')
                ->where('email', $email)
                ->where('mobile', $mobile)
                ->where('user_id', $userId)
                ->first();

            if (empty($offerAgent)) {
                $agentId = DB::table('web_agents')
                    ->insertGetId([
                        'user_id' => $userId,
                        'mobile' => $mobile,
                        'email' => $email,
                        'name' => $name,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt
                    ]);
            } else {
                $agentId = $offerAgent->id;
            }


            DB::table('web_auth')
                ->insert([
                    'user_id' => $userId,
                    'token' => $token,
                    'agent_id' => $agentId,
                    'mobile' => $mobile,
                    'email' => $email,
                    'name' => $name,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);

            DB::commit();

            return ResponseHelper::success('Authorization success', ['token' => $responseToken]);
        } catch (Exception $e) {
            DB::rollBack();
            if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }

        /**
     * Generate token
     */
    public static function generateAuthTokenForPan($request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'psaId' => "required|max:100|regex:/^([a-zA-Z0-9 ]+)$/"
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Missing Parameters', $message);
            }

            //getting user_id
            if (isset($request->auth_data['user_id'])) {
                $userId = $request->auth_data['user_id'];
            } else {
                $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
            }

            $psaId = trim($request->psaId);


            $token = CommonHelper::getRandomString('PTKN', false, 26);

            $tokenSupport = CommonHelper::getRandomString('', false, 8);

            $createdAt = date('Y-m-d H:i:s');
            $responseToken = trim(base64_encode(time() . "|" . $userId . "|" . $token . "|" . $tokenSupport), '=');


            DB::beginTransaction();
            $mobile = trim($request->mobile);
            $name = "";
            $email = strtolower(trim($request->email));
            //check offer agent
            $offerAgent = DB::table('web_agents')
                ->select('id')
                ->where('email', $email)
                ->where('mobile', $mobile)
                ->where('user_id', $userId)
                ->first();

            if (empty($offerAgent)) {
                $agentId = DB::table('web_agents')
                    ->insertGetId([
                        'user_id' => $userId,
                        'mobile' => $mobile,
                        'email' => $email,
                        'name' => $name,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt
                    ]);
            } else {
                $agentId = $offerAgent->id;
            }


            DB::table('web_auth')
                ->insert([
                    'user_id' => $userId,
                    'token' => $token,
                    'agent_id' => $agentId,
                    'mobile' => $mobile,
                    'email' => $email,
                    'name' => $name,
                    'type' => 'pan',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);

            DB::commit();

            $orderRefId = CommonHelper::getRandomString('ORD', false);
            return ResponseHelper::success('Authorization success',
            [
                'url' => env('XT_NSDL_WEB_URL')."?token=".$responseToken.'&orderRefId='.$orderRefId.'&psaId='.$psaId,
                'orderRefId' => $orderRefId,
                'psaId' => $psaId,
                ]
        );
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }
}
