<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ValidationSuiteHelper
{
    /**
     * Charge for Validation suite fee
     */
    public static function chargeValidationFee($trnData)
    {
        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')
            ->select('id')
            ->where('txn_ref_id', $trnData->request_id)
            ->count();

        if ($isTransactions > 0) {
            return "Fee already charged.";
        }

        $rowId = $trnData->id;
        $txnId = CommonHelper::getRandomString('txn', false);
        $txnReferenceId = $trnData->request_id;
        $identifier = $trnData->identifier;

        $totAmount = 0 - $trnData->fee - $trnData->tax;

        $txnTotalAmount = ($totAmount >= 0) ? '+' . $totAmount : $totAmount;
        $txnNarration = ($trnData->fee + $trnData->tax) . ' debited against ' . $trnData->request_id;

        DB::select("CALL ValidationSuiteFeeChargeTxnJob($trnData->user_id, $rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', 0, $trnData->fee, $trnData->tax, '$txnNarration', '$identifier', 'dr', '$trnData->serviceId', '$trnData->feeRate', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }



    /**
     * Check User primary Wallet Ballance
     */
    public static function checkPrimaryBalance($userId)
    {
        $userBal = DB::table('users')
            ->select('transaction_amount')
            ->where('id', $userId)
            ->first();

        if (!empty($userBal)) {
            return $userBal->transaction_amount;
        }

        return 0;
    }
}
