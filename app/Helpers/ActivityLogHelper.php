<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ActivityLogHelper
{
    public static function addToLog($type, $userId = 0, $message = "", $updatedBy = 1)
    {
		$loggedUserId = 0;
		if (isset(auth()->user()->id)) {
			$loggedUserId = auth()->user()->id;
		}
    	$log = [];
    	$log['url'] = Request::fullUrl();
    	$log['method'] = Request::method();
		$log['message'] = $message;
		$log['type'] = $type;
    	$log['ip'] = Request::ip();
		$log['agent'] = Request::header('user-agent');
    	$log['user_id'] = @$userId ? $userId : $loggedUserId;
		$log['updated_by'] = auth()->check() ? auth()->user()->id : $updatedBy;
    	ActivityLog::create($log);
    }


    public static function ActivityLists()
    {
    	return ActivityLog::latest()->get();
    }

}
