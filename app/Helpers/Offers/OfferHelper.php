<?php

namespace App\Helpers\Offers;

use Illuminate\Support\Facades\DB;

class OfferHelper
{
    public static function fetchUserId($id)
    {
        $data = DB::table('web_auth')
            ->select('*')
            ->where('id', $id)
            // ->whereNull('session_end')
            ->first();

        return $data;
    }

    public static function fetchAgentUserId($userInfo)
    {
        $data = DB::table('web_agents')
            ->select('*')
            ->where('user_id', $userInfo->user_id)
            ->where('mobile', $userInfo->mobile)
            ->where('email', $userInfo->email)
            ->first();

        return $data;
    }



    public static function fetchAgentInfo($id)
    {
        $data = DB::table('web_agents')
            ->select('*')
            ->where('id', $id)
            ->first();

        return $data;
    }


    public static function generateToken($userId, $agentId, $agentLigId = 0)
    {
        //timestamp|user_id|agent_id|uniqid

        $tokenString = time() . "|" . $userId . "|" . $agentId . "|" . uniqid();

        $hashHelper = new OfferHashHelper();
        $tokenString = $hashHelper->encrypt($tokenString);
        $tokenExpireAfter = strtotime("+4 hour", time());

        //insert auth token record
        $insertId = DB::table('web_agent_tokens')
            ->insertGetId([
                'user_id' => $userId,
                'agent_id' => $agentId,
                'agent_log_id' => $agentLigId,
                'token' => $tokenString,
                'expire_at' => $tokenExpireAfter,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $tokenString = trim(base64_encode($insertId . "|" . $tokenString), '=');

        return $tokenString;
    }
}
