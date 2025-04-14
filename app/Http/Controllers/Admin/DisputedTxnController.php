<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AEPSHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrimaryFundCredit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DisputedTxnController extends Controller
{
    /**
     * UPI Stack
     */
    public function upiStack()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] =  "UPI Stack Dispute Resolution";
            $data['site_title'] =  "UPI Stack Dispute Resolution";
            $data['view']       = ADMIN . ".disputed_upi_stack_txns";

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }
    /**
     * Smart Payout
     */
    public function smartPayout()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] =  "Smart payout dispute";
            $data['site_title'] =  "Smart payout Resolution";
            $data['view']       = ADMIN . ".disputed_smart_payout_txns";
            $data['userData'] = $data['user'] = $data['users'] = DB::table('users')
                ->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Fetch record by UTR
     */
    public function upiStackSubmit(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'utr' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            try {
                //fetch record from upi callback
                $utr =  $request->utr;

                $utrData = DB::table('upi_callbacks')
                    ->select('upi_callbacks.*', 'users.name', 'users.email', 'users.mobile')
                    ->leftJoin('users', 'upi_callbacks.user_id', 'users.id')
                    ->where('upi_callbacks.customer_ref_id', $utr)
                    ->first();

                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This UTR is not found.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_credited === '0' || empty($utrData->trn_credited_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is not credited yet.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_disputed === '1' || !empty($utrData->trn_disputed_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is already disputed.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                return ResponseHelper::success('UPI Stack: Record found successfully', $utrData);
            } catch (Exception $e) {
                return ResponseHelper::failed($e->getMessage());
            }
        } else {
            return abort(404);
        }
    }


    /**
     * Dispute transaction
     */
    public function upiStackFinalSubmit(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'utr' => "required",
                ]
            );

            if ($validator->fails()) {
                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => $validator->errors()->get('utr'));
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
            }

            try {
                //fetch record from upi callback
                $utr =  $request->utr;

                $utrData = DB::table('upi_callbacks')->select('*')
                    ->where('customer_ref_id', $utr)
                    ->first();

                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This UTR is not found.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_credited === '0' || empty($utrData->trn_credited_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is not credited yet.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_disputed === '1' || !empty($utrData->trn_disputed_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is already disputed.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                $utrData->admin_id = Auth::user()->id;

                PrimaryFundCredit::dispatch($utrData, 'upi_stack_dispute')->onQueue('primary_fund_queue');

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = "Job assigned Successfully";
                $this->title = "Transaction Disputed";
                $this->redirect = true;
                return $this->populateresponse();
            } catch (Exception $e) {
                return ResponseHelper::failed($e->getMessage());
            }
        } else {
            return abort(404);
        }
    }



    /**
     * Smart Collect
     */
    public function smartCollect()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] =  "Smart Collect Dispute Resolution";
            $data['site_title'] =  "Smart Collect Dispute Resolution";
            $data['view']       = ADMIN . ".disputed_smart_collect_txns";

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Fetch record by UTR
     */
    public function smartCollectSubmit(Request $request)
    {

        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'smcl_utr' => "required",
                ],
                [
                    'smcl_utr.required' => "The UTR field is required."
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            try {
                //fetch record from upi callback
                $utr =  $request->smcl_utr;

                $utrData = DB::table('cf_merchants_fund_callbacks')
                    ->select('cf_merchants_fund_callbacks.*', 'users.name', 'users.email', 'users.mobile')
                    ->leftJoin('users', 'cf_merchants_fund_callbacks.user_id', 'users.id')
                    ->where('cf_merchants_fund_callbacks.utr', $utr)
                    ->first();

                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This UTR is not found.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_vpa === '0') {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This is not an UPI transaction (UTR).");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_credited === '0' || empty($utrData->trn_credited_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is not credited yet.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_disputed === '1' || !empty($utrData->trn_disputed_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is already disputed.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                return ResponseHelper::success('Smart Collect: Record found successfully', $utrData);
            } catch (Exception $e) {
                return ResponseHelper::failed($e->getMessage());
            }
        } else {
            return abort(404);
        }
    }


    /**
     * Dispute transaction
     */
    public function smartCollectFinalSubmit(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'utr_final' => "required",
                ],
                [
                    'utr_final.required' => "The UTR field is required."
                ]
            );

            if ($validator->fails()) {
                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => $validator->errors()->get('utr_final'));
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
            }

            try {
                //fetch record from upi callback
                $utr =  $request->utr_final;

                $utrData = DB::table('cf_merchants_fund_callbacks')->select('*')
                    ->where('utr', $utr)
                    ->where('is_vpa', '1')
                    ->first();

                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This UTR is not found.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_credited === '0' || empty($utrData->trn_credited_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is not credited yet.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_disputed === '1' || !empty($utrData->trn_disputed_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is already disputed.");
                    $this->title = "Transaction Disputed";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                $utrData->admin_id = Auth::user()->id;

                PrimaryFundCredit::dispatch($utrData, 'smart_collect_dispute')->onQueue('primary_fund_queue');

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = "Job assigned Successfully";
                $this->title = "Transaction Disputed";
                $this->redirect = true;
                return $this->populateresponse();
            } catch (Exception $e) {
                return ResponseHelper::failed($e->getMessage());
            }
        } else {
            return abort(404);
        }
    }



    /**
     * Payout Report For All Users and Total amount filtered by date range
     */
    public function totalAmountReportsAll(Request $request, $service, $returnType = 'all')
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $request['return'] = 'all';
            $request->orderIdArray = [];
            $request->serviceIdArray = [];
            $request->userIdArray = [];
            $request['returnType'] = $returnType;
            // $parentData = session('parentData');
            $request['where'] = 0;


            $toDate = $fromDate = date('Y-m-d');

            if (!empty($request->from)) {
                $fromDate = $request->from;
            }

            if (!empty($request->to)) {
                $toDate = $request->to;
            }

            switch ($service) {

                case 'aeps-transactions':
                    $searchData = ['users.name', 'users.email', 'users.mobile','aeps_transactions.merchant_code', 'aeps_transactions.rrn', 'aeps_transactions.route_type'];
                    $sqlQuery = DB::table('aeps_transactions')
                        ->select('aeps_transactions.id','aeps_transactions.client_ref_id',  'aeps_transactions.merchant_code','aeps_transactions.trn_credited_at','aeps_transactions.trn_disputed_at', 'commission_ref_id','aeps_transactions.rrn','aeps_transactions.route_type','is_commission_credited','commission','tds','aeps_transactions.transaction_amount','users.name', 'users.email', 'users.mobile', 'transactions.txn_id', 'transactions.txn_ref_id')
                        ->leftJoin('users', 'aeps_transactions.user_id', 'users.id')
                        ->leftJoin('transactions', 'aeps_transactions.rrn', 'transactions.order_id')
                        ->where('aeps_transactions.is_trn_disputed', '1')
                        ->where('transactions.tr_identifiers', 'aeps_inward_dispute');

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('aeps_transactions.user_id', $request->user_id);
                    }

                    if (!empty($request->order[0]['column'])) {
                        $filterColumn = $request->columns[$request->order[0]['column']]['data'];
                        $orderBy = $request->order[0]['dir'];
                        $sqlQuery->orderBy($filterColumn, $orderBy);
                    } else {
                        $sqlQuery->orderBy('aeps_transactions.trn_disputed_at', 'DESC');
                    }


                    break;
                case 'upi-stack':
                    $searchData = ['users.name', 'users.email', 'users.mobile', 'upi_callbacks.payee_vpa', 'upi_callbacks.customer_ref_id'];
                    $sqlQuery = DB::table('upi_callbacks')
                        ->select('upi_callbacks.*', 'users.name', 'users.email', 'users.mobile', 'transactions.txn_id', 'transactions.txn_ref_id')
                        ->leftJoin('users', 'upi_callbacks.user_id', 'users.id')
                        ->leftJoin('transactions', 'upi_callbacks.customer_ref_id', 'transactions.order_id')
                        ->where('upi_callbacks.is_trn_disputed', '1')
                        ->where('transactions.tr_identifiers', 'upi_inward_dispute');

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('upi_callbacks.user_id', $request->user_id);
                    }

                    if (!empty($request->order[0]['column'])) {
                        $filterColumn = $request->columns[$request->order[0]['column']]['data'];
                        $orderBy = $request->order[0]['dir'];
                        $sqlQuery->orderBy($filterColumn, $orderBy);
                    } else {
                        $sqlQuery->orderBy('upi_callbacks.trn_disputed_at', 'DESC');
                    }


                    break;

                case 'smart-collect':
                    $searchData = ['users.name', 'users.email', 'users.mobile', 'cf_merchants_fund_callbacks.virtual_vpa_id', 'cf_merchants_fund_callbacks.v_account_number', 'cf_merchants_fund_callbacks.utr', 'cf_merchants_fund_callbacks.v_account_id'];
                    $sqlQuery = DB::table('cf_merchants_fund_callbacks')
                        ->select('cf_merchants_fund_callbacks.*', 'users.name', 'users.email', 'users.mobile', 'transactions.txn_id', 'transactions.txn_ref_id')
                        ->leftJoin('users', 'cf_merchants_fund_callbacks.user_id', 'users.id')
                        ->leftJoin('transactions', 'cf_merchants_fund_callbacks.utr', 'transactions.order_id')
                        ->where('cf_merchants_fund_callbacks.is_trn_disputed', '1')
                        ->where('transactions.tr_identifiers', 'smart_collect_vpa_dispute');

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('cf_merchants_fund_callbacks.user_id', $request->user_id);
                    }

                    if (!empty($request->order[0]['column'])) {
                        $filterColumn = $request->columns[$request->order[0]['column']]['data'];
                        $orderBy = $request->order[0]['dir'];
                        $sqlQuery->orderBy($filterColumn, $orderBy);
                    } else {
                        $sqlQuery->orderBy('cf_merchants_fund_callbacks.trn_disputed_at', 'DESC');
                    }


                    break;

                default:
                    return abort(404);
                    break;
            }


            if (!empty($request->search['value'])) {
                $searchValue = trim($request->search['value']);
                $sqlQuery->where(function ($sql) use ($searchValue, $searchData) {
                    foreach ($searchData as $value) {
                        $sql->orWhere($value, 'like', '%' . $searchValue . '%');
                    }
                });
            }

            $sqlQueryCount = $sqlQuery;
            $sqlQueryCount = $sqlQueryCount->get();

            if ($request['length'] != -1) {
                $sqlQuery->skip($request->start)->take($request->length);
            }
            $result = $sqlQuery->get();


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
        } else {
            return abort(404);
        }
    }


    /**
     * UPI Stack
     */
    public function aepsTXN()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] =  "AEPS Transaction Dispute Resolution";
            $data['site_title'] =  "AEPS Transaction Dispute Resolution";
            $data['view']       = ADMIN . ".aeps-dispute_txn";

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * Fetch record by UTR
     */
    public function aepsTXNSubmit(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'utr' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            try {
                //fetch record from upi callback
                $utr =  $request->utr;

                $utrData = DB::table('aeps_transactions')
                    ->select('aeps_transactions.*', 'users.name', 'users.email', 'users.mobile')
                    ->leftJoin('users', 'aeps_transactions.user_id', 'users.id')
                    ->where('aeps_transactions.rrn', $utr)
                    ->first();

                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This RRN is not found.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_credited === '0' || empty($utrData->trn_credited_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is not credited yet.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_disputed === '1' || !empty($utrData->trn_disputed_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is already disputed.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                if ($utrData->transaction_type != 'cw') {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Only CW transaction is applicable.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                if (!empty($utrData->trn_ref_id)) {
                    $utrTxn = DB::table('transactions')->select('id', 'txn_id', 'service_id')
                        ->where('tr_identifiers', 'aeps_inward_credit')
                        ->where('txn_ref_id', $utrData->trn_ref_id)
                        ->first();
                } else {
                    $utrTxn = '';
                }

                if (empty($utrTxn)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Transaction not found");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                return ResponseHelper::success('AEPS: Record found successfully', $utrData);
            } catch (Exception $e) {
                return ResponseHelper::failed($e->getMessage());
            }
        } else {
            return abort(404);
        }
    }

    /**
     * Dispute transaction
     */
    public function aepsFinalSubmit(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'utr' => "required",
                ]
            );

            if ($validator->fails()) {
                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => $validator->errors()->get('utr'));
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
            }

            try {
                //fetch record from upi callback
                $utr =  $request->utr;

                $utrData = DB::table('aeps_transactions')->select('*')
                    ->where('rrn', $utr)
                    ->first();

                if (empty($utrData)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This RRN is not found.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_credited === '0' || empty($utrData->trn_credited_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is not credited yet.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if ($utrData->is_trn_disputed === '1' || !empty($utrData->trn_disputed_at)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This transaction is already disputed.");
                    $this->title = "Transaction Dispute";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $utrData->admin_id = Auth::user()->id;
              //  $data = AEPSHelper::aepsDisputedTxn($utrData);

                PrimaryFundCredit::dispatch($utrData, 'aeps_txn_dispute')->onQueue('primary_fund_queue');

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = "Job assigned Successfully";
                $this->title = "Transaction Disputed";
                $this->redirect = true;
                return $this->populateresponse();
            } catch (Exception $e) {
                return ResponseHelper::failed($e->getMessage());
            }
        } else {
            return abort(404);
        }
    }

}
