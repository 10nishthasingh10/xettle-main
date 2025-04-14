<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Helpers\XettleUpiHelper;

class UpiCollectionController extends Controller
{

    public function upicollection(Request $request)
    {
        try{
            DB::transaction(function () use($request) {
                $reqData = array(
                    "amount" => $request->amount,
                    "referenceId" => $request->referenceId,
                    "note" => $request->note,
                    "customer_name" => $request->customer_name,
                    "customer_email" => $request->customer_email,
                    "customer_mobile" => $request->customer_mobile,
                );
            });
            return XettleUpiHelper::hits();
            // app()->make('app/Helpers/XettleUpiHelper.php');

            $status = true;
            $message = "Generated Successfully";
            $messageReadbled = "";
        } catch (\Exception $e) {
            DB::rollBack();
            $status = false;
            // $message = $e->getTraceAsString();
            // $messageReadbled = $e->getMessage();
        }

        return response()->json(['status' => $status ,'messageReadbled' => $messageReadbled,'message' => $message]);
    }
}