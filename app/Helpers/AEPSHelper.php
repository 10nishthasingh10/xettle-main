<?php

namespace App\Helpers;

use App\Jobs\PrimaryFundCredit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AEPSHelper
{

    /**
     * Reversed Amount of AEPS,
     * Debit amount to user's primary account
     */
    public static function aepsDisputedTxn($trnData)
    {
        try {
            //getting transaction info
          
            if (!empty($trnData->trn_ref_id)) {
                $utrTxn = DB::table('transactions')->select('id', 'txn_id', 'service_id')
                    ->where('tr_identifiers', 'aeps_inward_credit')
                    ->where('txn_ref_id', $trnData->trn_ref_id)
                    ->first();
            } else {
                $utrTxn = '';
            }


            if (empty($utrTxn)) {
                return 'Invalid Transaction.';
            }


            $txnId = CommonHelper::getRandomString('txn', false);
            $txnRefId = $trnData->client_ref_id;

            $commission = 0;
            $tds = 0;
            $trAmount = 0;
            if ($trnData->is_commission_credited == '1' && !empty($trnData->commission_ref_id)) {
                $trAmount = $trnData->transaction_amount;
                $commission =  $trnData->commission;
                $tds = $trnData->tds;
                $rvTxnAmount =  $trnData->transaction_amount + $trnData->commission + $trnData->tds;
            } else {
                $trAmount = $trnData->transaction_amount;
                $rvTxnAmount =  $trnData->transaction_amount;
            }
            $txnNarration = $rvTxnAmount. ' debited against disputed UTR ' . $trnData->rrn;
            $adminId = "ADMIN::" . $trnData->admin_id;


            DB::select("CALL aepsDisputeTxnJob($trnData->user_id, $trnData->id, '$txnId', '$txnRefId', '$trnData->rrn', $rvTxnAmount, $trAmount, $commission, $tds, '$txnNarration', '$utrTxn->service_id', '$utrTxn->txn_id', '$adminId', @outData)");

            $results = DB::select('select @outData as outData');
            $response = json_decode($results[0]->outData);

            return $response->status;
            //code...
        } catch (\Exception $th) {
            $fileName = 'public/AEPSDispute'.$trnData->user_id.'.txt';
            Storage::disk('local')->put($fileName, $th->getMessage());
        }
    }
}
