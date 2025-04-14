<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class AdminCommonHelper
{
    /**
     * Get Service Last Txn and staus
     */
    public function getActiveTxnInfo($serviceId, $userId, $date = null, $toDate = null)
    {
        switch ($serviceId) {
            case PAYOUT_SERVICE_ID:
            case 'payout':
                //get payout business last transaction date
                $query = DB::connection('slave')
                    ->table('orders')
                    ->select(
                        'status',
                        DB::raw('SUM(amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date) && !empty($toDate)) {
                    $query->whereDate('created_at', '>=', $date)
                        ->whereDate('created_at', '<=', $toDate);
                } else if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('status')
                    ->orderBy('status', 'ASC')
                    ->get();
                break;

            case AEPS_SERVICE_ID:
            case 'aeps':
                $query = DB::connection('slave')
                    ->table('aeps_transactions')
                    ->select(
                        'transaction_type',
                        'status',
                        DB::raw('SUM(transaction_amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('transaction_type', 'status')
                    ->orderBy('transaction_type', 'ASC')
                    ->get();
                break;

            case UPI_SERVICE_ID:
            case 'upi_stack':
                $query = DB::connection('slave')
                    ->table('upi_callbacks')
                    ->select(
                        DB::raw('SUM(amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId)
                    ->whereIn('root_type', ['fpay', 'ibl']);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->get();
                break;

            case AUTO_COLLECT_SERVICE_ID:
            case 'smart_collect':
                $query = DB::connection('slave')
                    ->table('cf_merchants_fund_callbacks')
                    ->select(
                        'is_vpa',
                        DB::raw('SUM(amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('is_vpa')
                    ->orderBy('is_vpa', 'ASC')
                    ->get();
                break;

            case VA_SERVICE_ID:
            case 'upi_tpv':
                $query = DB::connection('slave')
                    ->table('upi_callbacks')
                    ->select(
                        DB::raw('SUM(amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId)
                    ->where('root_type', 'ibl_tpv');

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->get();
                break;

            case VALIDATE_SERVICE_ID:
            case 'validation_suite':
            case 'validation':
                $query = DB::connection('slave')
                    ->table('validations')
                    ->select(
                        'type',
                        'status',
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('type', 'status')
                    ->orderBy('created_at', 'DESC')
                    ->get();
                break;

            case DMT_SERVICE_ID:
            case 'dmt':
                $query = DB::connection('slave')
                    ->table('dmt_fund_transfers')
                    ->select(
                        'status',
                        DB::raw('SUM(amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('status')
                    ->orderBy('created_at', 'DESC')
                    ->get();
                break;


            case MATM_SERVICE_ID:
            case 'matm':
                $query = DB::connection('slave')
                    ->table('matm_transactions')
                    ->select(
                        'status',
                        DB::raw('SUM(transaction_amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('status')
                    ->orderBy('created_at', 'DESC')
                    ->get();
                break;


            case RECHARGE_SERVICE_ID:
            case 'recharge':
                $query = DB::connection('slave')
                    ->table('recharges')
                    ->select(
                        'status',
                        DB::raw('SUM(amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('status')
                    ->orderBy('created_at', 'DESC')
                    ->get();
                break;


            case PAN_CARD_SERVICE_ID:
            case 'pan':
                $query = DB::connection('slave')
                    ->table('pan_txns')
                    ->select(
                        'status',
                        DB::raw('SUM(fee) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('status')
                    ->orderBy('created_at', 'DESC')
                    ->get();
                break;


            case 'srv_1640687279':
            case 'load_money':
                $query = DB::connection('slave')
                    ->table('load_money_request')
                    ->select(
                        'status',
                        DB::raw('SUM(amount) AS sumAmt'),
                        DB::raw('COUNT(id) AS txnCounts')
                    )
                    ->where('user_id', $userId);

                if (!empty($date)) {
                    $query->whereDate('created_at', $date);
                }

                return $query->groupBy('status')
                    ->orderBy('status', 'ASC')
                    ->get();
                break;

            default:
                return null;
        }
    }
}
