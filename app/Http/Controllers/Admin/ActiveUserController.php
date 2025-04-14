<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminCommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ActiveUserController extends Controller
{

    /**
     * Active Txn By User
     */
    public function getActiveTxnByUser(Request $request)
    {
        try {
            if (!Auth::user()->hasRole('aeps-support')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'user_id' => "nullable|numeric",
                        'fromDate' => "nullable|date|before_or_equal:" . date('Y-m-d'),
                    ]
                );


                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing($message);
                }


                /**
                 * getting date form user to apply filter
                 */
                $fromDate = trim($request->fromDate);
                $crrDate = date('Y-m-d');

                // if (!empty($request->fromDate)) {
                if ($fromDate != $crrDate) {
                    // $crrDate = trim($request->fromDate);
                    $dateFilterSql = "WHERE DATE(created_at) = '$fromDate'";
                    $dateFilterSqlUpi = "AND DATE(created_at) = '$fromDate'";
                    $currentDateFilter = '';
                } else {
                    // $crrDate = date('Y-m-d');
                    $dateFilterSql = '';
                    $dateFilterSqlUpi = '';
                    $currentDateFilter = "WHERE txndate = '$crrDate'";
                }


                /**
                 * getting user ID form user to apply filter
                 */
                $userFilter = '';
                $userFilterUpi = '';
                if (!empty($request->user_id)) {
                    if (empty($dateFilterSql)) {
                        $userFilter = " WHERE `user_id` = " . trim($request->user_id);
                    } else {
                        $userFilter = " AND `user_id` = " . trim($request->user_id);
                    }

                    $userFilterUpi = " AND `user_id` = " . trim($request->user_id);
                }



                /**
                 * Apply Limit for the query
                 */
                $limit = '';
                if ($request['length'] != -1) {
                    $limit = " LIMIT " . trim($request->start) . ", " . trim($request->length);
                }


                $sqlQuery = '';

                if (!empty($request->service_id)) {
                    switch (trim($request->service_id)) {
                        case PAYOUT_SERVICE_ID:
                        case 'payout':
                            $sqlQuery = "SELECT * FROM
                                (
                                SELECT
                                    user_id,
                                    DATE_FORMAT(orders.created_at, '%Y-%m-%d') as txndate,
                                    users.name, users.email, users.mobile,
                                    CONCAT('payout') as service
                                FROM orders
                                    INNER JOIN 
                                    (SELECT MAX(id) as id FROM orders 
                                    $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                                    ON last_updates.id = orders.id 
                                    LEFT JOIN `users` ON `users`.`id` = `orders`.`user_id`
                                    
                                ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            break;

                        case AEPS_SERVICE_ID:
                        case 'aeps':
                            $sqlQuery = "SELECT * FROM
                                (                        
                                SELECT
                                    user_id,
                                    DATE_FORMAT(aeps_transactions.created_at, '%Y-%m-%d') as txndate,
                                    users.name, users.email, users.mobile,
                                    CONCAT('aeps') as service
                                FROM aeps_transactions
                                    INNER JOIN 
                                    (SELECT MAX(id) as id FROM aeps_transactions 
                                        $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                                    ON last_updates.id = aeps_transactions.id 
                                    LEFT JOIN `users` ON `users`.`id` = `aeps_transactions`.`user_id`
                                    
                                ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            break;

                            // case UPI_SERVICE_ID:
                            // case 'upi_stack':
                            //     $sqlQuery = "SELECT * FROM
                            //         (

                            //         SELECT
                            //             user_id,
                            //             DATE_FORMAT(upi_callbacks.created_at, '%Y-%m-%d') as txndate,
                            //             users.name, users.email, users.mobile,
                            //             CONCAT('upi_stack') as service
                            //         FROM upi_callbacks
                            //             INNER JOIN 
                            //             (SELECT MAX(id) as id FROM upi_callbacks 
                            //                 WHERE root_type IN ('fpay', 'ibl') 
                            //                 $dateFilterSqlUpi $userFilterUpi GROUP BY user_id) last_updates 
                            //             ON last_updates.id = upi_callbacks.id 
                            //             LEFT JOIN `users` ON `users`.`id` = `upi_callbacks`.`user_id`

                            //         ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            //     break;

                            // case AUTO_COLLECT_SERVICE_ID:
                            // case 'smart_collect':
                            //     $sqlQuery = "SELECT * FROM
                            //         (

                            //         SELECT
                            //             user_id,
                            //             DATE_FORMAT(cf_merchants_fund_callbacks.created_at, '%Y-%m-%d') as txndate,
                            //             users.name, users.email, users.mobile,
                            //             CONCAT('smart_collect') as service
                            //         FROM cf_merchants_fund_callbacks
                            //             INNER JOIN 
                            //             (SELECT MAX(id) as id FROM cf_merchants_fund_callbacks 
                            //                 $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                            //             ON last_updates.id = cf_merchants_fund_callbacks.id 
                            //             LEFT JOIN `users` ON `users`.`id` = `cf_merchants_fund_callbacks`.`user_id`
                            //             where date(cf_merchants_fund_callbacks.created_at) = '$crrDate'

                            //         ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            //     break;

                            // case VA_SERVICE_ID:
                            // case 'upi_tpv':
                            //     $sqlQuery = "SELECT * FROM
                            //         (

                            //         SELECT
                            //             user_id,
                            //             DATE_FORMAT(upi_callbacks.created_at, '%Y-%m-%d') as txndate,
                            //             users.name, users.email, users.mobile,
                            //             CONCAT('upi_tpv') as service
                            //         FROM upi_callbacks
                            //             INNER JOIN 
                            //             (SELECT MAX(id) as id FROM upi_callbacks 
                            //                 WHERE root_type = 'ibl_tpv' 
                            //                 $dateFilterSqlUpi $userFilterUpi GROUP BY user_id) last_updates 
                            //             ON last_updates.id = upi_callbacks.id 
                            //             LEFT JOIN `users` ON `users`.`id` = `upi_callbacks`.`user_id`

                            //         ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            //     break;

                            // case VALIDATE_SERVICE_ID:
                            // case 'validation_suite':
                            //     $sqlQuery = "SELECT * FROM
                            //         (

                            //         SELECT
                            //             user_id,
                            //             DATE_FORMAT(validation_suite.created_at, '%Y-%m-%d') as txndate,
                            //             users.name, users.email, users.mobile,
                            //             CONCAT('validation_suite') as service
                            //         FROM validation_suite
                            //             INNER JOIN 
                            //             (SELECT MAX(id) as id FROM validation_suite 
                            //                 $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                            //             ON last_updates.id = validation_suite.id 
                            //             LEFT JOIN `users` ON `users`.`id` = `validation_suite`.`user_id`

                            //         ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            //     break;

                        case VALIDATE_SERVICE_ID:
                        case 'validation':
                            $sqlQuery = "SELECT * FROM
                                    (
                                        
                                    SELECT
                                        user_id,
                                        DATE_FORMAT(validations.created_at, '%Y-%m-%d') as txndate,
                                        users.name, users.email, users.mobile,
                                        CONCAT('validations') as service
                                    FROM validations
                                        INNER JOIN 
                                        (SELECT MAX(id) as id FROM validations 
                                            $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                                        ON last_updates.id = validations.id 
                                        LEFT JOIN `users` ON `users`.`id` = `validations`.`user_id`
                                    ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            break;


                        case MATM_SERVICE_ID:
                        case 'matm':
                            $sqlQuery = "SELECT * FROM
                                        (
                                        SELECT
                                            user_id,
                                            DATE_FORMAT(matm_transactions.created_at, '%Y-%m-%d') as txndate,
                                            users.name, users.email, users.mobile,
                                            CONCAT('matm_transactions') as service
                                        FROM matm_transactions
                                            INNER JOIN 
                                            (SELECT MAX(id) as id FROM matm_transactions 
                                                $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                                            ON last_updates.id = matm_transactions.id 
                                            LEFT JOIN `users` ON `users`.`id` = `matm_transactions`.`user_id` 
                                        ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            break;

                        case DMT_SERVICE_ID:
                        case 'dmt':
                            $sqlQuery = "SELECT * FROM
                                        ( 
                                        SELECT
                                            user_id,
                                            DATE_FORMAT(dmt_fund_transfers.created_at, '%Y-%m-%d') as txndate,
                                            users.name, users.email, users.mobile,
                                            CONCAT('dmt_fund_transfers') as service
                                        FROM dmt_fund_transfers
                                            INNER JOIN 
                                            (SELECT MAX(id) as id FROM dmt_fund_transfers 
                                                $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                                            ON last_updates.id = dmt_fund_transfers.id 
                                            LEFT JOIN `users` ON `users`.`id` = `dmt_fund_transfers`.`user_id` 
                                        ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            break;

                        case PAN_CARD_SERVICE_ID:
                        case 'pan':
                            $sqlQuery = "SELECT * FROM
                                        ( 
                                        SELECT
                                            user_id,
                                            DATE_FORMAT(pan_txns.created_at, '%Y-%m-%d') as txndate,
                                            users.name, users.email, users.mobile,
                                            CONCAT('pan_txns') as service
                                        FROM pan_txns
                                            INNER JOIN 
                                            (SELECT MAX(id) as id FROM pan_txns 
                                                $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                                            ON last_updates.id = pan_txns.id 
                                            LEFT JOIN `users` ON `users`.`id` = `pan_txns`.`user_id` 
                                        ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            break;

                        case RECHARGE_SERVICE_ID:
                        case 'recharge':
                            $sqlQuery = "SELECT * FROM
                                                ( 
                                                SELECT
                                                    user_id,
                                                    DATE_FORMAT(recharges.created_at, '%Y-%m-%d') as txndate,
                                                    users.name, users.email, users.mobile,
                                                    CONCAT('recharges') as service
                                                FROM recharges
                                                    INNER JOIN 
                                                    (SELECT MAX(id) as id FROM recharges 
                                                        $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                                                    ON last_updates.id = recharges.id 
                                                    LEFT JOIN `users` ON `users`.`id` = `recharges`.`user_id` 
                                                ) AS dt $currentDateFilter ORDER BY user_id ASC";
                            break;
                    }
                } else {
                    $sqlQuery = "SELECT * FROM
                    (
                    SELECT
                        user_id,
                        DATE_FORMAT(orders.created_at, '%Y-%m-%d') as txndate,
                        users.name, users.email, users.mobile,
                        CONCAT('payout') as service
                    FROM orders
                        INNER JOIN 
                        (SELECT MAX(id) as id FROM orders 
                            $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                        ON last_updates.id = orders.id 
                        LEFT JOIN `users` ON `users`.`id` = `orders`.`user_id`
                        
                    UNION ALL
                        
                    SELECT
                        user_id,
                        DATE_FORMAT(aeps_transactions.created_at, '%Y-%m-%d') as txndate,
                        users.name, users.email, users.mobile,
                        CONCAT('aeps') as service
                    FROM aeps_transactions
                        INNER JOIN 
                        (SELECT MAX(id) as id FROM aeps_transactions 
                            $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                        ON last_updates.id = aeps_transactions.id 
                        LEFT JOIN `users` ON `users`.`id` = `aeps_transactions`.`user_id`

                    UNION ALL

                    SELECT
                        user_id,
                        DATE_FORMAT(dmt_fund_transfers.created_at, '%Y-%m-%d') as txndate,
                        users.name, users.email, users.mobile,
                        CONCAT('dmt') as service
                    FROM dmt_fund_transfers
                        INNER JOIN 
                        (SELECT MAX(id) as id FROM dmt_fund_transfers 
                            $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                        ON last_updates.id = dmt_fund_transfers.id 
                        LEFT JOIN `users` ON `users`.`id` = `dmt_fund_transfers`.`user_id`

                    UNION ALL
                    
                        SELECT
                        user_id,
                        DATE_FORMAT(matm_transactions.created_at, '%Y-%m-%d') as txndate,
                        users.name, users.email, users.mobile,
                        CONCAT('matm') as service
                    FROM matm_transactions
                        INNER JOIN 
                        (SELECT MAX(id) as id FROM matm_transactions 
                            $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                        ON last_updates.id = matm_transactions.id 
                        LEFT JOIN `users` ON `users`.`id` = `matm_transactions`.`user_id`

                    UNION ALL
                    
                    SELECT
                        user_id,
                        DATE_FORMAT(pan_txns.created_at, '%Y-%m-%d') as txndate,
                        users.name, users.email, users.mobile,
                        CONCAT('pan') as service
                    FROM pan_txns
                        INNER JOIN 
                        (SELECT MAX(id) as id FROM pan_txns 
                            $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                        ON last_updates.id = pan_txns.id 
                        LEFT JOIN `users` ON `users`.`id` = `pan_txns`.`user_id`


                    UNION ALL
                    
                    SELECT
                        user_id,
                        DATE_FORMAT(recharges.created_at, '%Y-%m-%d') as txndate,
                        users.name, users.email, users.mobile,
                        CONCAT('recharge') as service
                    FROM recharges
                        INNER JOIN 
                        (SELECT MAX(id) as id FROM recharges 
                            $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                        ON last_updates.id = recharges.id 
                        LEFT JOIN `users` ON `users`.`id` = `recharges`.`user_id`


                    UNION ALL
                    
                    SELECT
                        user_id,
                        DATE_FORMAT(validations.created_at, '%Y-%m-%d') as txndate,
                        users.name, users.email, users.mobile,
                        CONCAT('validation') as service
                    FROM validations
                        INNER JOIN 
                        (SELECT MAX(id) as id FROM validations 
                            $dateFilterSql $userFilter GROUP BY user_id) last_updates 
                        ON last_updates.id = validations.id 
                        LEFT JOIN `users` ON `users`.`id` = `validations`.`user_id`
                        
                    ) AS dt $currentDateFilter ORDER BY user_id ASC";
                }

                $sqlQueryCount = str_replace("SELECT * FROM", "SELECT count(*) AS counts FROM", $sqlQuery);

                $activeUsers = DB::select($sqlQuery . $limit);

                $sqlQueryCount = DB::select($sqlQueryCount);
                if (!empty($sqlQueryCount[0]->counts)) {
                    $recordsTotal = $sqlQueryCount[0]->counts;
                } else {
                    $recordsTotal = 0;
                }


                $jsonData = array(
                    "draw" => intval($request['draw']),
                    "recordsTotal" => $recordsTotal,
                    "recordsFiltered" => $recordsTotal,
                    "data" => $activeUsers,
                    "start" => $request->start,
                    "length" => $request->length,
                    "filterDate" => $crrDate,
                );

                return response()->json($jsonData);
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }


    /**
     * Fetch last transaction date and info
     * by the user date wise
     */
    public function getActiveTxnDetail(Request $request)
    {
        try {
            if (!Auth::user()->hasRole('aeps-support')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'rowNum' => "required|numeric|min:1",
                        'date' => "required|date",
                        'service' => "required"
                    ]
                );

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing($message);
                }

                $date = trim($request->date);
                $userId = trim($request->rowNum);
                $service = trim($request->service);


                $dataInfo = (new AdminCommonHelper())->getActiveTxnInfo($service, $userId, $date);

                return ResponseHelper::success('success', $dataInfo);
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }

    public function getLatestUserSignup(Request $request)
    {
        $request['return'] = 'all';
        $request->orderIdArray = [];
        $request->serviceIdArray = [];
        $request->userIdArray = [];
        $request['returnType'] = 'all';
        // $parentData = session('parentData');
        $request['where'] = 0;


        $toDate = $fromDate = date('Y-m-d');

        if (!empty($request->from)) {
            $fromDate = $request->from;
        }

        if (!empty($request->to)) {
            $toDate = $request->to;
        }

        $result = DB::table('users')->whereBetween(DB::raw('date(created_at)'), [$fromDate, $toDate]);
        $sqlQueryCount = $result;
        if (!empty($request->order[0]['column'])) {
            $filterColumn = $request->columns[$request->order[0]['column']]['data'];
            $orderBy = $request->order[0]['dir'];
            $result->orderBy($filterColumn, $orderBy);
            $sqlQueryCount->orderBy($filterColumn, $orderBy);
        } else {
            $result->orderBy('created_at', 'DESC');
            $sqlQueryCount->orderBy('created_at', 'DESC');
        }




        $sqlQueryCount = $sqlQueryCount->get();

        if ($request['length'] != -1) {
            $result->skip($request->start)->take($request->length);
        }
        $result = $result->get();
        //dd(\DB::getQueryLog());


        if ($request->return == "all" || $returnType == "all") {
            $json_data = array(
                "draw"            => intval($request['draw']),
                "recordsTotal"    => intval(count($sqlQueryCount)),
                "recordsFiltered" => intval(count($sqlQueryCount)),
                "data"            => $result,
                "from_date" => $fromDate,
                "to_date" => $toDate,
                "start" => $request->start,
                "length" => $request->length,
            );
            echo json_encode($json_data);
        } else {
            return response()->json($result);
        }
    }
}
