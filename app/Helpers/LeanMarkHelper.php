<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;

class LeanMarkHelper
{

    /**
     * Handeling Lean Mark Balance with primary Wallet
     */
    public function handleLeanBalance($data)
    {
        
        // Storage::prepend('lean-job-log.txt', print_r($data, true));


        //check record available in DB or not
        $check = DB::table('lean_mark_transactions')
            ->where('id', $data->id)
            ->where('status', '0')
            ->count('id');

        if ($check === 0) {
            // Storage::prepend('lean-job-log.txt', "ERROR: Invalid record, data not found");
            return "Invalid record, data not found";
        }


        //check lean mark total amount and released lean mark amount
        if ($data->txn_type == 'cr') {

            $drBal = 0;
            $crBal = 0;

            $checkBal = DB::table('lean_mark_transactions')
                ->select(
                    DB::raw("`user_id`, `txn_type`, sum(`amount`) as amt")
                )
                ->where('user_id', $data->user_id)
                ->where('status', '1')
                ->groupBy('txn_type')
                ->get();


            if ($checkBal->isEmpty()) {
                // Storage::prepend('lean-job-log.txt', "ERROR: No Lean Balance found.");
                return ResponseHelper::failed("No Lean Balance found.");
            }


            foreach ($checkBal as  $row) {
                if ($row->txn_type == "dr") {
                    $drBal = round($row->amt, 2);
                } else if ($row->txn_type == "cr") {
                    $crBal = round($row->amt, 2);
                    $crBal = abs($crBal);
                }
            }

            $remainLeanBalance = round($drBal - $crBal, 2);

            if (abs($data->amount) > ($remainLeanBalance)) {
                // Storage::prepend('lean-job-log.txt', "ERROR: Lean Amount Balance is less than releasing amount");
                return "Lean Amount Balance is less than releasing amount";
            }
        }


        // Storage::prepend('lean-job-log.txt', "Before Procedure");

        //perform lean balance management
        DB::select("CALL LeanMarkBalanceJob($data->id, $data->user_id, $data->amount, @outData)");

        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        // Storage::prepend('lean-job-log.txt', print_r($response, true));

        return true;
    }
}
