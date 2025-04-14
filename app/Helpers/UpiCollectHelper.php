<?php

namespace App\Helpers;

use App\Jobs\PrimaryFundCredit;
use Illuminate\Support\Facades\DB;

class UpiCollectHelper
{

    /**
     * Settle UPI Stack Amount
     * Credit amount to user's primary account
     */
    public static function upiStackSettle2Primary($trnData)
    {

        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('tr_reference', $trnData->timestamp)
            ->count();

        if ($isTransactions > 0) {
            return "Amounts already settled";
        }

        // $rowId = $trnData->id;
        $txnId = $trnData->txn_id; //CommonHelper::getRandomString('txn', false);
        $txnReferenceId = $trnData->txn_id;

        //getting service ID
        $products = CommonHelper::getProductId('upi_collect', 'upi_collect');
        $serviceId = $products->service_id;

        //fee and tax on fee calculation
        //$taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $trnData->total_amount, $trnData->user_id);
        //$feeRate = $taxFee->margin;

        $fee = round($taxFee->fee, 2);
        $tax = round($taxFee->tax, 2);
        // $feeRate = '';
        $fee = $trnData->total_fee;
        $tax = $trnData->total_tax;

        $afterFeeTaxAmount = $trnData->total_amount;

        $signedAfterFeeTaxAmount = ($afterFeeTaxAmount >= 0) ? '+' . $afterFeeTaxAmount : $afterFeeTaxAmount;
        $txnNarration = $signedAfterFeeTaxAmount . ' credited to Primary Wallet.';

        //dd([$trnData->timestamp, $trnData->user_id,  $trnData->counts, $serviceId, $txnId, $txnReferenceId, $signedAfterFeeTaxAmount, $afterFeeTaxAmount, $trnData->total_amount, $fee, $tax, $txnNarration, $trnData->frequency]);
        $query = DB::select("CALL UpiStackFundSettleTxnJob('$trnData->timestamp', $trnData->user_id,  '$trnData->counts', '$serviceId', '$txnId', '$txnReferenceId', '$signedAfterFeeTaxAmount', $afterFeeTaxAmount, $trnData->total_amount, $fee, $tax, '$txnNarration', '$trnData->frequency', @outData)");
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        // Output SQL and bindings
        print_r($sql);
        die();
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }

    /**
     * Received payment via UPI,
     * Credit amount to user's primary account
     */
    public static function upiFundCredit($trnData)
    {

        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('order_id', $trnData->customer_ref_id)->count();

        if ($isTransactions > 0) {
            return "Transaction already credited";
        }

        $rowId = $trnData->id;
        $txnId = !empty($trnData->txnId) ? $trnData->txnId : CommonHelper::getRandomString('txn', false);
        $txnReferenceId = 'UT_' . $trnData->customer_ref_id;

        //getting service ID
        $products = CommonHelper::getProductId('upi_collect', 'upi_collect');
        $serviceId = $products->service_id;

        //fee and tax on fee calculation
        if (!empty($trnData->fee) && !empty($trnData->tax)) {
            $feeRate = $trnData->fee_rate;
            $fee = $trnData->fee;
            $tax = $trnData->tax;

            $totAmount = $trnData->cr_amount;

            $txnTotalAmount = ($totAmount >= 0) ? '+' . $totAmount : $totAmount;
            $txnNarration = $txnTotalAmount . ' credited against ' . $trnData->customer_ref_id;
        } else {
            $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $trnData->amount, $trnData->user_id);
            $feeRate = $taxFee->margin;

            $fee = round($taxFee->fee, 2);
            $tax = round($taxFee->tax, 2);

            $totAmount = $trnData->amount - $fee - $tax;

            $txnTotalAmount = ($totAmount >= 0) ? '+' . $totAmount : $totAmount;
            $txnNarration = $txnTotalAmount . ' credited against ' . $trnData->customer_ref_id;
        }



        // if (!empty($trnData->is_upi_collect)) {
        // DB::select("CALL UpiCollectCreditTransaction($rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', $trnData->amount, '$txnNarration', @outData)");
        // $results = DB::select('select @outData as outData');
        // $response = json_decode($results[0]->outData);
        // } else {
        // DB::select("CALL UpiStaticQrCreditTransaction($trnData->user_id, '$trnData->customer_ref_id', '$serviceId', $rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', $trnData->amount, '$txnNarration', @outData)");
        // $results = DB::select('select @outData as outData');
        // $response = json_decode($results[0]->outData);
        // }

        // DB::select("CALL UpiStackCreditTransaction($trnData->user_id, '$trnData->customer_ref_id', '$serviceId', $rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', $totAmount, $trnData->amount, $fee, $tax, '$txnNarration', '$feeRate', @outData)");
        DB::select("CALL UpiStackCreditTxnJob($trnData->user_id, '$trnData->customer_ref_id', '$serviceId', $rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', $totAmount, $trnData->amount, $fee, $tax, '$txnNarration', '$feeRate', '$trnData->frequency', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }


    /**
     * fn() handels VPA creation charges
     */
    public static function upiStackDebitTxn($trnData)
    {
        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('txn_ref_id', $trnData->merchant_txn_ref_id)->count();

        if ($isTransactions > 0) {
            return "Transaction already debited";
        }

        $rowId = $trnData->id;
        $txnId = CommonHelper::getRandomString('txn', false);
        $txnReferenceId = $trnData->merchant_txn_ref_id;
        $identifier = $trnData->identifier;

        //getting service ID
        $products = CommonHelper::getProductId($trnData->slug, $trnData->type);
        $serviceId = $products->service_id;

        //fee and tax on fee calculation
        $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, 1, $trnData->user_id);
        $feeRate = $taxFee->margin;

        $fee = round($taxFee->fee, 2);
        $tax = round($taxFee->tax, 2);
        $totAmount = 0 - $fee - $tax;

        $txnTotalAmount = ($totAmount >= 0) ? '+' . $totAmount : $totAmount;
        $txnNarration = ($fee + $tax) . ' debited against ' . $txnReferenceId;

        // DB::select("CALL UpiStackFeeDebitTransaction($rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', 0, $fee, $tax, '$txnNarration', '$identifier', 'dr', '$serviceId', '$feeRate', @outData)");
        DB::select("CALL UpiStackVpaFeeDebitTxnJob($trnData->user_id, $rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', 0, $fee, $tax, '$txnNarration', '$identifier', 'dr', '$serviceId', '$feeRate', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }


    /**
     * fn() handels VPA Verify charges
     */
    public static function upiStackVpaVerifyDebitTxn($trnData)
    {
        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('txn_ref_id', $trnData->request_id)->count();

        if ($isTransactions > 0) {
            return "Transaction already debited";
        }

        $rowId = $trnData->id;
        $txnId = CommonHelper::getRandomString('txn', false);
        $txnReferenceId = $trnData->request_id;
        $identifier = $trnData->identifier;

        //getting service ID
        $products = CommonHelper::getProductId($trnData->slug, $trnData->type);
        $serviceId = $products->service_id;

        //fee and tax on fee calculation
        $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, 1, $trnData->user_id);
        $feeRate = $taxFee->margin;

        $fee = round($taxFee->fee, 2);
        $tax = round($taxFee->tax, 2);
        $totAmount = 0 - $fee - $tax;

        $txnTotalAmount = ($totAmount >= 0) ? '+' . $totAmount : $totAmount;
        $txnNarration = ($fee + $tax) . ' debited against ' . $trnData->request_id;

        // DB::select("CALL UpiStackVerifyFeeDebitTransaction($rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', 0, $fee, $tax, '$txnNarration', '$identifier', 'dr', '$serviceId', '$feeRate', @outData)");
        DB::select("CALL UpiStackVerifyFeeDebitTxnJob($trnData->user_id, $rowId, '$txnId', '$txnReferenceId', '$txnTotalAmount', 0, $fee, $tax, '$txnNarration', '$identifier', 'dr', '$serviceId', '$feeRate', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }


    /**
     * Direct Credit UPI Amount to Primary Banance
     */
    public static function upiCredit($trnData)
    {
        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('order_id', $trnData->customer_ref_id)->count();

        if ($isTransactions > 0) {
            return "Transaction already exist";
        }

        $trnData->frequency = 'manually';

        PrimaryFundCredit::dispatch((object) $trnData, 'upi_stack_credit')->onQueue('primary_fund_queue');

        return true;
    }



    /**
     * Reversed Amount of UPI Stack,
     * Debit amount to user's primary account
     */
    public static function upiStackDisputedTxn($trnData)
    {
        //getting transaction info
        if (!empty($trnData->batch_id)) {
            $utrTxn = DB::table('transactions')->select('id', 'txn_id', 'service_id')
                ->where('tr_identifiers', 'upi_inward_credit')
                ->where('user_id', $trnData->user_id)
                ->where('txn_ref_id', $trnData->batch_id)
                ->where('txn_id', $trnData->txn_id)
                ->first();
        } else {
            $utrTxn = DB::table('transactions')->select('id', 'txn_id', 'service_id')
                ->where('tr_identifiers', 'upi_inward_credit')
                ->where('user_id', $trnData->user_id)
                ->where(function ($sql) use ($trnData) {
                    return $sql->where('order_id', $trnData->customer_ref_id)
                        ->orWhere('txn_ref_id', 'UT_' . $trnData->customer_ref_id);
                })
                ->first();
        }


        if (empty($utrTxn)) {
            return 'Invalid Transaction.';
        }


        $txnId = CommonHelper::getRandomString('txn', false);
        $txnRefId = 'RUT_' . $trnData->customer_ref_id;


        // $rvTxnAmount = $utrTxn->tr_amount - $utrTxn->tr_fee - $utrTxn->tr_tax;
        $txnNarration = $trnData->amount . ' debited against disputed UTR ' . $trnData->customer_ref_id;
        $adminId = "ADMIN::" . $trnData->admin_id;


        DB::select("CALL UpiStackDisputeTxnJob($trnData->user_id, $trnData->id, '$txnId', '$txnRefId', '$trnData->customer_ref_id', $trnData->amount, $trnData->amount, 0, 0, '$txnNarration', '$utrTxn->service_id', '$utrTxn->txn_id', '$adminId', @outData)");
        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);

        return $response->status;
    }

    public static function upiCollectEncryption($params, $requestType, $userId, $modal, $reqType, $header = '')
    {
        $request = $params;
        $url = 'https://indicpay.in/api/encryption';
        $method = 'POST';
        $header = ["Content-Type: application/json"];
        $result = CommonHelper::curl($url, $method, json_encode($request), $header, 'yes', $userId, $modal, $reqType);
        $response = $result['response'];
        return $response;
    }
}
