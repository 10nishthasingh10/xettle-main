<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Models\UPIMerchant;
use Illuminate\Support\Facades\Auth;
use App\Models\UPICollect;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TestController extends Controller
{
    public function bankdata(Request $request) {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => "required",
                    'fromDate' => "required",
                    'toDate' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $upicollects = UPICollect::where('user_id', $request->user_id)->where('status', 'success')
                           ->select('user_id', 'integration_id', 'amount', 'status', 'created_at', 'updated_at')->first();

            echo $upicollects;
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }
}