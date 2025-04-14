<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NishthaHelper
{
    public static function logUserActivity($userId, $message, $type, $created_at) {
        UserActivityLog::create([
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
            'created_at' => $created_at
        ]);
    }
    
}