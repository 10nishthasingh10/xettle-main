<?php

namespace App\Http\Controllers;

use App\Helpers\UpiCollectHelper;
use Illuminate\Support\Facades\DB;

class BulkUpiCreditCtrl extends Controller
{
    /**
     * Credit by User
     */
    public function index($userId)
    {
        $response = [];

        if(isset($userId)) {
            $callbacks = DB::table('upi_callbacks')
            ->select('*')
            ->where('user_id', $userId)
            ->where('is_trn_credited', '0')
            ->whereNull('trn_credited_at')
            ->get();
            $i=0;
            foreach ($callbacks as $row) {
                $i = $i+1;
                $response[] = [
                    'id' => $row->id,
                    'utr' =>  $row->customer_ref_id,
                    'res' => UpiCollectHelper::upiCredit($row)
                ];
            }
            $response[] = ['count' => $i];
        } else {
            $response['message'] = ['Not Found'];
        }
        return response()->json($response);
    }


    /**
     * Credit By UTR
     */
    public function upiCreditUtr($utr)
    {
        $response = [];

        if (isset($utr)) {
            $callbacks = DB::table('upi_callbacks')
                ->select('*')
                ->where('customer_ref_id', $utr)
                ->where('is_trn_credited', '0')
                ->whereNull('trn_credited_at')
                ->first();

            if (!empty($callbacks)) {
                $response = [
                    'id' => $callbacks->id,
                    'user_id' => $callbacks->user_id,
                    'utr' =>  $callbacks->customer_ref_id,
                    'res' => UpiCollectHelper::upiCredit($callbacks)
                ];
            } else {
                $response['message'] = ['UTR Not Found'];
            }
        } else {
            $response['message'] = ['Empty Params'];
        }
        return response()->json($response);
    }
}
