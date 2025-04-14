<?php

namespace App\Http\Controllers\Spa\Offers\v1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\DB;

class UserSpaController extends Controller
{

    /**
     * Agent Info
     */
    public function agentInfo()
    {
        try {
            $agentId = request()->get('userAgentId');

            $agentInfo = DB::table('web_agents')
                ->select('user_id', 'name', 'email', 'mobile')
                ->where('id', $agentId)
                ->first();

            $responseData['name'] = $agentInfo->name;
            $responseData['email'] = $agentInfo->email;
            $responseData['mobile'] = $agentInfo->mobile;

            return ResponseHelper::success('Agent info found', $responseData);
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG, CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * Logout
     */
    public function logout()
    {
        try {

            // $agentId = request()->get('userAgentId');
            $agentLogId = request()->get('userAgentLogId');

            DB::table('web_auth')
                ->where('id', $agentLogId)
                ->update([
                    'session_end' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            DB::table('web_agent_tokens')
                // ->where('agent_id', $agentId)
                ->where('agent_log_id', $agentLogId)
                ->delete();

            return ResponseHelper::success('Logout successfully.');
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG, CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }
}
