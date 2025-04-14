<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\WebhookHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrimaryFundCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ManualSettlementController extends Controller
{

    /**
     * VIEW: Settlement for UPI Stack
     */
    public function viewUpiStackSettle()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] =  "UPI Stack Settlement";
            $data['site_title'] =  "UPI Stack Settlement";

            $data['userList'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('id', 'asc')
                ->get();

            return view('admin.manual_settlements.upi_stack')->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * Submit: Settlement for UPI Stack
     */
    public function fetchUpiStackSettle(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => "required|numeric|min:1",
                    'start_date' => "required|date|before_or_equal:end_date",
                    'end_date' => "required|date|before:" . date('Y-m-d'),
                    'webhook' => "required|in:yes,no",
                    'root_type' => "required|in:fpay,ibl,all",
                    'settlement_type' => "required|in:batch,single"
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $userId = $request->user_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $webhook = $request->webhook;
            $rootType = $request->root_type;
            $settlementType = $request->settlement_type;

            $batchSettlements = 0;
            $message = null;


            switch ($settlementType) {
                case 'single':
                    //check settlement, where batch id is not empty
                    $upiCallbacks = DB::table('upi_callbacks')
                        ->select(
                            // 'root_type',
                            // 'user_id',
                            // 'batch_id',
                            DB::raw('SUM(amount) AS total_amount'),
                            // DB::raw('SUM(fee) AS total_fee'),
                            // DB::raw('SUM(tax) AS total_tax'),
                            // DB::raw('SUM(cr_amount) AS total_cr_amount'),
                            // DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                            DB::raw('COUNT(id) AS counts')
                        );

                    if ($rootType != 'all') {
                        $upiCallbacks->where('root_type', $rootType);
                    }

                    $upiCallbacks = $upiCallbacks->whereNull('batch_id')
                        ->where('user_id', $userId)
                        ->where('is_trn_credited', '0')
                        ->whereNull('txn_id')
                        // ->where('is_trn_settle', '0')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->orderBy('id', 'ASC')
                        // ->groupBy('batch_id', 'user_id')
                        ->first();

                    if (!empty($upiCallbacks)) {
                        $data = [
                            'count' => $upiCallbacks->counts,
                            'amount' => empty($upiCallbacks->total_amount) ? 0 : $upiCallbacks->total_amount,
                            'inputs' => $request->all()
                        ];
                    }

                    break;

                case 'batch':
                    //check settlement, where batch id is not empty
                    $upiCallbacks = DB::table('upi_callbacks')
                        ->select(
                            // 'root_type',
                            // 'user_id',
                            // 'batch_id',
                            DB::raw('SUM(amount) AS total_amount'),
                            // DB::raw('SUM(fee) AS total_fee'),
                            // DB::raw('SUM(tax) AS total_tax'),
                            // DB::raw('SUM(cr_amount) AS total_cr_amount'),
                            // DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                            DB::raw('COUNT(id) AS counts')
                        );

                    if ($rootType != 'all') {
                        $upiCallbacks->where('root_type', $rootType);
                    }

                    $upiCallbacks = $upiCallbacks->whereNotNull('batch_id')
                        ->where('user_id', $userId)
                        ->where('is_trn_credited', '0')
                        ->where('is_trn_settle', '0')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->orderBy('id', 'ASC')
                        // ->groupBy('batch_id', 'user_id')
                        ->first();


                    if (!empty($upiCallbacks)) {
                        $data = [
                            'count' => $upiCallbacks->counts,
                            'amount' => empty($upiCallbacks->total_amount) ? 0 : $upiCallbacks->total_amount,
                            'inputs' => $request->all()
                        ];
                    }

                    break;
            }


            if (!empty($data)) {
                return ResponseHelper::success('data found', $data);
            }

            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => strtoupper($settlementType) . ": No transaction founds.");
            $this->title = "Manual Settlement";
            $this->redirect = false;
            return $this->populateresponse();
        } else {
            return abort(404);
        }
    }

    /**
     * Submit: Settlement for UPI Stack
     */
    public function submitUpiStackSettle(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => "required|numeric|min:1",
                    'start_date' => "required|date|before_or_equal:end_date",
                    'end_date' => "required|date|before:" . date('Y-m-d'),
                    'webhook' => "required|in:yes,no",
                    'root_type' => "required|in:fpay,ibl,all",
                    'settlement_type' => "required|in:batch,single"
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $userId = $request->user_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $webhook = $request->webhook;
            $rootType = $request->root_type;
            $settlementType = $request->settlement_type;

            $batchSettlements = 0;
            $message = null;


            //check service is enable or not
            // $isServiceActive = CommonHelper::checkIsServiceActive('upi_collect', $userId);

            // if (!$isServiceActive) {
            //     $this->status = true;
            //     $this->modal = true;
            //     $this->alert = true;
            //     $this->message_object = true;
            //     $this->message  = array('message' => "Service is not Active.");
            //     $this->title = "Manual Settlement";
            //     $this->redirect = false;
            //     return $this->populateresponse();
            // }


            switch ($settlementType) {
                case 'single':
                    //check settlement, where batch id is not empty
                    $upiCallbacks = DB::table('upi_callbacks')
                        ->select('*');

                    if ($rootType != 'all') {
                        $upiCallbacks->where('root_type', $rootType);
                    }

                    $upiCallbacks = $upiCallbacks->whereNull('batch_id')
                        ->where('user_id', $userId)
                        ->where('is_trn_credited', '0')
                        ->whereNull('txn_id')
                        // ->where('is_trn_settle', '0')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->orderBy('id', 'ASC')
                        // ->groupBy('batch_id', 'user_id')
                        ->get();


                    if ($upiCallbacks->isNotEmpty()) {

                        if ($webhook === 'yes') {
                            $getWebhooks = DB::table('webhooks')
                                ->where('user_id', $userId)
                                ->first();
                        }

                        $timestamp = date('Y-m-d H:i:s');

                        foreach ($upiCallbacks as $row) {
                            $batchSettlements++;
                            $txnId = CommonHelper::getRandomString('txn', false);
                            $row->txnId = $txnId;

                            DB::table('upi_callbacks')
                                ->where('is_trn_credited', '0')
                                // ->whereDate('created_at', $date)
                                ->where('id', $row->id)
                                ->update([
                                    'txn_id' => $txnId,
                                    // 'is_trn_settle' => '2',
                                    // 'trn_settled_at' => $timestamp
                                ]);

                            $row->frequency = 'manually';

                            PrimaryFundCredit::dispatch((object) $row, 'upi_stack_credit')->onQueue('primary_fund_queue');

                            if ($webhook === 'yes' && !empty($getWebhooks)) {
                                if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                    $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                    WebhookHelper::UPISuccess((object) $row, $getWebhooks->webhook_url, $getWebhooks->secret, $headers);
                                } else {
                                    WebhookHelper::UPISuccess((object) $row, $getWebhooks->webhook_url, $getWebhooks->secret);
                                }
                            }
                        }
                    }

                    if ($batchSettlements > 0) {
                        $message = "Single: $batchSettlements  Job assigned";
                    }
                    break;

                case 'batch':
                    //check settlement, where batch id is not empty
                    $upiCallbacks = DB::table('upi_callbacks')
                        ->select(
                            'root_type',
                            'user_id',
                            'batch_id',
                            DB::raw('SUM(amount) AS total_amount'),
                            DB::raw('SUM(fee) AS total_fee'),
                            DB::raw('SUM(tax) AS total_tax'),
                            DB::raw('SUM(cr_amount) AS total_cr_amount'),
                            DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                            DB::raw('COUNT(id) AS counts')
                        );

                    if ($rootType != 'all') {
                        $upiCallbacks->where('root_type', $rootType);
                    }

                    $upiCallbacks = $upiCallbacks->whereNotNull('batch_id')
                        ->where('user_id', $userId)
                        ->where('is_trn_credited', '0')
                        ->where('is_trn_settle', '0')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->orderBy('id', 'ASC')
                        ->groupBy('batch_id', 'user_id')
                        ->get();

                    if ($upiCallbacks->isNotEmpty()) {

                        if ($webhook === 'yes') {
                            $getWebhooks = DB::table('webhooks')
                                ->where('user_id', $userId)
                                ->first();
                        }

                        $timestamp = date('Y-m-d H:i:s');

                        foreach ($upiCallbacks as $row) {
                            $batchSettlements++;
                            $txnId = CommonHelper::getRandomString('txn', false);

                            DB::table('upi_callbacks')
                                //->whereIn('id', explode(',', $row->ids))
                                ->where('batch_id', $row->batch_id)
                                ->where('is_trn_settle', '0')
                                ->where('is_trn_credited', '0')
                                // ->whereDate('created_at', $date)
                                ->where('user_id', $userId)
                                ->update([
                                    'txn_id' => $txnId,
                                    'is_trn_settle' => '2',
                                    'trn_settled_at' => $timestamp
                                ]);

                            $row->timestamp = $timestamp;
                            $row->txn_id = $txnId;
                            $row->frequency = 'manually';

                            PrimaryFundCredit::dispatch($row, 'upi_stack_settle_to_primary')->onQueue('primary_fund_queue');

                            if ($webhook === 'yes' && !empty($getWebhooks)) {
                                $webhookRows = DB::table('upi_callbacks')
                                    ->select('*')
                                    ->whereIn('id', explode(',', $row->ids))
                                    ->get();

                                if ($webhookRows->isNotEmpty()) {
                                    foreach ($webhookRows as $webhookRow) {

                                        if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                            $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                            WebhookHelper::UPISuccess((object) $webhookRow, $getWebhooks->webhook_url, $getWebhooks->secret, $headers);
                                        } else {
                                            WebhookHelper::UPISuccess((object) $webhookRow, $getWebhooks->webhook_url, $getWebhooks->secret);
                                        }
                                    }
                                }
                            }
                        }
                    }


                    if ($batchSettlements > 0) {
                        $message = "Batch: $batchSettlements  Job assigned";
                    }
                    break;
            }


            if (!empty($message)) {
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = $message;
                $this->title = "Manual Settlement";
                $this->redirect = true;
                return $this->populateresponse();
            }

            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => strtoupper($settlementType) . ": No transaction founds.");
            $this->title = "Manual Settlement";
            $this->redirect = false;
            return $this->populateresponse();
        } else {
            return abort(404);
        }
    }


    /**
     * VIEW: Settlement for Smart Collect
     */
    public function viewSmartCollectSettle()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] =  "Smart Collect Settlement";
            $data['site_title'] =  "Smart Collect Settlement";

            $data['userList'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('id', 'asc')
                ->get();

            return view('admin.manual_settlements.smart_collect')->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * Submit: Settlement for Smart Collect
     */
    public function fetchSmartCollectSettle(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => "required|numeric|min:1",
                    'start_date' => "required|date|before_or_equal:end_date",
                    'end_date' => "required|date|before:" . date('Y-m-d'),
                    'webhook' => "required|in:yes,no",
                    'root_type' => "required|in:van,upi,all",
                    'settlement_type' => "required|in:batch,single"
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $userId = $request->user_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            // $webhook = $request->webhook;
            $rootType = $request->root_type;
            $settlementType = $request->settlement_type;

            // $batchSettlements = 0;
            $message = null;


            $isVpa = ($rootType === 'upi') ? '1' : '0';


            switch ($settlementType) {
                case 'single':
                    //check settlement, where batch id is not empty
                    $cfCallbacks = DB::table('cf_merchants_fund_callbacks')
                        ->select(
                            // 'user_id',
                            // 'batch_id',
                            DB::raw('SUM(amount) AS total_amount'),
                            // DB::raw('SUM(fee) AS total_fee'),
                            // DB::raw('SUM(tax) AS total_tax'),
                            // DB::raw('SUM(cr_amount) AS total_cr_amount'),
                            // DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                            DB::raw('COUNT(id) AS counts')
                        );

                    if ($rootType != 'all') {
                        $cfCallbacks->where('is_vpa', $isVpa);
                    }

                    $cfCallbacks = $cfCallbacks->whereNull('batch_id')
                        ->where('user_id', $userId)
                        ->where('is_trn_credited', '0')
                        ->whereNull('txn_id')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->orderBy('id', 'ASC')
                        ->first();


                    if (!empty($cfCallbacks)) {
                        $data = [
                            'count' => $cfCallbacks->counts,
                            'amount' => empty($cfCallbacks->total_amount) ? 0 : $cfCallbacks->total_amount,
                            'inputs' => $request->all()
                        ];
                    }

                    break;

                case 'batch':
                    //check settlement, where batch id is not empty
                    $cfCallbacks = DB::table('cf_merchants_fund_callbacks')
                        ->select(
                            // 'user_id',
                            // 'batch_id',
                            DB::raw('SUM(amount) AS total_amount'),
                            // DB::raw('SUM(fee) AS total_fee'),
                            // DB::raw('SUM(tax) AS total_tax'),
                            // DB::raw('SUM(cr_amount) AS total_cr_amount'),
                            // DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                            DB::raw('COUNT(id) AS counts')
                        )
                        ->whereNotNull('batch_id')
                        ->where('is_trn_credited', '0')
                        ->where('is_trn_settle', '0')
                        ->where('is_vpa', $isVpa)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->where('user_id', $userId)
                        ->orderBy('id', 'ASC')
                        // ->groupBy('batch_id', 'user_id')
                        ->first();


                    if (!empty($cfCallbacks)) {
                        $data = [
                            'count' => $cfCallbacks->counts,
                            'amount' => empty($cfCallbacks->total_amount) ? 0 : $cfCallbacks->total_amount,
                            'inputs' => $request->all()
                        ];
                    }
                    break;
            }

            if (!empty($data)) {
                return ResponseHelper::success('data found', $data);
            }

            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => strtoupper($settlementType) . ": No transaction founds.");
            $this->title = "Manual Settlement";
            $this->redirect = false;
            return $this->populateresponse();
        } else {
            return abort(404);
        }
    }


    /**
     * Submit: Settlement for Smart Collect
     */
    public function submitSmartCollectSettle(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => "required|numeric|min:1",
                    'start_date' => "required|date|before_or_equal:end_date",
                    'end_date' => "required|date|before:" . date('Y-m-d'),
                    'webhook' => "required|in:yes,no",
                    'root_type' => "required|in:van,upi,all",
                    'settlement_type' => "required|in:batch,single"
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $userId = $request->user_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $webhook = $request->webhook;
            $rootType = $request->root_type;
            $settlementType = $request->settlement_type;

            $batchSettlements = 0;
            $message = null;


            //check service is enable or not
            // $isServiceActive = CommonHelper::checkIsServiceActive('smart_collect', $userId);

            // if (!$isServiceActive) {
            //     $this->status = true;
            //     $this->modal = true;
            //     $this->alert = true;
            //     $this->message_object = true;
            //     $this->message  = array('message' => "Service is not Active.");
            //     $this->title = "Manual Settlement";
            //     $this->redirect = false;
            //     return $this->populateresponse();
            // }

            $isVpa = ($rootType === 'upi') ? '1' : '0';


            switch ($settlementType) {
                case 'single':
                    //check settlement, where batch id is not empty
                    $cfCallbacks = DB::table('cf_merchants_fund_callbacks')
                        ->select('*');

                    if ($rootType != 'all') {
                        $cfCallbacks->where('is_vpa', $isVpa);
                    }

                    $cfCallbacks = $cfCallbacks->whereNull('batch_id')
                        ->where('user_id', $userId)
                        ->where('is_trn_credited', '0')
                        ->whereNull('txn_id')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->orderBy('id', 'ASC')
                        ->get();


                    if ($cfCallbacks->isNotEmpty()) {

                        if ($webhook === 'yes') {
                            $getWebhooks = DB::table('webhooks')
                                ->where('user_id', $userId)
                                ->first();
                        }

                        $timestamp = date('Y-m-d H:i:s');

                        foreach ($cfCallbacks as $row) {
                            $batchSettlements++;
                            $txnId = CommonHelper::getRandomString('txn', false);
                            $row->txnId = $txnId;
                            $row->rowId = $row->id;

                            DB::table('cf_merchants_fund_callbacks')
                                ->where('is_trn_credited', '0')
                                // ->whereDate('created_at', $date)
                                ->where('id', $row->id)
                                ->update([
                                    'txn_id' => $txnId
                                ]);

                            $row->frequency = 'manually';

                            PrimaryFundCredit::dispatch((object) $row, 'smart_collect_credit')->onQueue('primary_fund_queue');

                            if ($webhook === 'yes' && !empty($getWebhooks)) {
                                if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                    $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                    WebhookHelper::autoCollectSuccess((object) $row, $getWebhooks->webhook_url, $getWebhooks->secret, $headers);
                                } else {
                                    WebhookHelper::autoCollectSuccess((object) $row, $getWebhooks->webhook_url, $getWebhooks->secret);
                                }
                            }
                        }
                    }

                    if ($batchSettlements > 0) {
                        $message = "Single: $batchSettlements  Job assigned";
                    }
                    break;

                case 'batch':
                    //check settlement, where batch id is not empty
                    $cfCallbacks = DB::table('cf_merchants_fund_callbacks')
                        ->select(
                            'user_id',
                            'batch_id',
                            DB::raw('SUM(amount) AS total_amount'),
                            DB::raw('SUM(fee) AS total_fee'),
                            DB::raw('SUM(tax) AS total_tax'),
                            DB::raw('SUM(cr_amount) AS total_cr_amount'),
                            DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                            DB::raw('COUNT(id) AS counts')
                        )
                        ->whereNotNull('batch_id')
                        ->where('is_trn_credited', '0')
                        ->where('is_trn_settle', '0')
                        ->where('is_vpa', '1')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->where('user_id', $userId)
                        ->orderBy('id', 'ASC')
                        ->groupBy('batch_id', 'user_id')
                        ->get();


                    if ($cfCallbacks->isNotEmpty() && $rootType === 'upi') {

                        if ($webhook === 'yes') {
                            $getWebhooks = DB::table('webhooks')
                                ->where('user_id', $userId)
                                ->first();
                        }

                        $timestamp = date('Y-m-d H:i:s');

                        foreach ($cfCallbacks as $row) {
                            $batchSettlements++;
                            $txnId = CommonHelper::getRandomString('txn', false);

                            DB::table('cf_merchants_fund_callbacks')
                                //->whereIn('id', explode(',', $row->ids))
                                ->where('batch_id', $row->batch_id)
                                ->where('is_trn_settle', '0')
                                ->where('is_trn_credited', '0')
                                ->where('is_vpa', '1')
                                // ->whereDate('created_at', $date)
                                ->where('user_id', $userId)
                                ->update([
                                    'txn_id' => $txnId,
                                    'is_trn_settle' => '2',
                                    'trn_settled_at' => $timestamp
                                ]);

                            $row->timestamp = $timestamp;
                            $row->txn_id = $txnId;
                            $row->frequency = 'manually';

                            PrimaryFundCredit::dispatch($row, 'smart_collect_upi_settle_to_primary')->onQueue('primary_fund_queue');

                            if ($webhook === 'yes' && !empty($getWebhooks)) {
                                $webhookRows = DB::table('cf_merchants_fund_callbacks')
                                    ->select('*')
                                    ->whereIn('id', explode(',', $row->ids))
                                    ->get();

                                if ($webhookRows->isNotEmpty()) {
                                    foreach ($webhookRows as $webhookRow) {

                                        if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                            $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                            WebhookHelper::autoCollectSuccess((object) $webhookRow, $getWebhooks->webhook_url, $getWebhooks->secret, $headers);
                                        } else {
                                            WebhookHelper::autoCollectSuccess((object) $webhookRow, $getWebhooks->webhook_url, $getWebhooks->secret);
                                        }
                                    }
                                }
                            }
                        }
                    }


                    if ($batchSettlements > 0) {
                        $message = "Batch: $batchSettlements  Job assigned";
                    }
                    break;
            }


            if (!empty($message)) {
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = $message;
                $this->title = "Manual Settlement";
                $this->redirect = true;
                return $this->populateresponse();
            }

            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => strtoupper($settlementType) . ": No transaction founds.");
            $this->title = "Manual Settlement";
            $this->redirect = false;
            return $this->populateresponse();
        } else {
            return abort(404);
        }
    }



    /**
     * Payout Report For All Users and Total amount filtered by date range
     */
    public function reportsAll(Request $request, $service, $returnType = 'all')
    {
        if (Auth::user()->hasRole('super-admin')) {
            $request['return'] = 'all';
            $request->orderIdArray = [];
            $request->serviceIdArray = [];
            $request->userIdArray = [];
            $request['returnType'] = $returnType;
            // $parentData = session('parentData');
            $request['where'] = 0;

            // $lastDate = date('Y-m-d', strtotime("-3 month", time()));
            $lastDate = CommonHelper::getUnsettledBalance(0, '', true);

            $toDate = $fromDate = date('Y-m-d');

            if (!empty($request->from)) {
                $fromDate = $request->from;
            }

            if (!empty($request->to)) {
                $toDate = $request->to;
            }

            switch ($service) {

                case 'smart-collect':
                    $searchData = ['users.name', 'users.email', 'users.mobile', 'cf_merchants_fund_callbacks.user_id'];
                    $sqlQuery = DB::table('cf_merchants_fund_callbacks')
                        ->select(
                            DB::raw("COUNT(cf_merchants_fund_callbacks.id) as count"),
                            DB::raw("SUM(amount) as amount"),
                            DB::raw("DATE_FORMAT(cf_merchants_fund_callbacks.created_at,'%Y-%m') as month"),
                            DB::raw('GROUP_CONCAT(DATE_FORMAT(cf_merchants_fund_callbacks.created_at,"%d") SEPARATOR ",") AS dates'),
                            'cf_merchants_fund_callbacks.user_id',
                            'users.name',
                            'users.email',
                        )
                        ->leftJoin('users', 'cf_merchants_fund_callbacks.user_id', 'users.id')
                        ->where('is_trn_credited', '0')
                        ->where('is_trn_settle', '0')
                        ->where('is_trn_disputed', '0')
                        ->whereDate('cf_merchants_fund_callbacks.created_at', '>=', $lastDate)
                        ->groupBy('month', 'user_id');

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('cf_merchants_fund_callbacks.user_id', $request->user_id);
                    }

                    break;

                case 'upi-stack':
                    $searchData = ['users.name', 'users.email', 'users.mobile', 'upi_callbacks.user_id'];
                    $sqlQuery = DB::table('upi_callbacks')
                        ->select(
                            DB::raw("COUNT(upi_callbacks.id) as count"),
                            DB::raw("SUM(amount) as amount"),
                            DB::raw("DATE_FORMAT(upi_callbacks.created_at,'%Y-%m') as month"),
                            DB::raw('GROUP_CONCAT(DATE_FORMAT(upi_callbacks.created_at,"%d") SEPARATOR ",") AS dates'),
                            'upi_callbacks.user_id',
                            'users.name',
                            'users.email',
                        )
                        ->leftJoin('users', 'upi_callbacks.user_id', 'users.id')
                        ->where('is_trn_credited', '0')
                        ->where('is_trn_settle', '0')
                        ->where('is_trn_disputed', '0')
                        ->whereDate('upi_callbacks.created_at', '>=', $lastDate)
                        ->groupBy('month', 'user_id');

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('upi_callbacks.user_id', $request->user_id);
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

            if (!empty($request->order[0]['column'])) {
                $filterColumn = $request->columns[$request->order[0]['column']]['data'];
                $orderBy = $request->order[0]['dir'];
                $sqlQuery->orderBy($filterColumn, $orderBy);
            } else {
                $sqlQuery->orderBy('month', 'ASC');
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
}
