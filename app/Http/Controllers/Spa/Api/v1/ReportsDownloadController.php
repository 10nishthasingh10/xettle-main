<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\AepsTransaction;
use App\Models\AutoCollectCallback;
use App\Models\DMTFundTransfer;
use App\Models\FundReceiveCallback;
use App\Models\MatmTransaction;
use App\Models\Order;
use App\Models\PanCardTransaction;
use App\Models\Recharge;
use App\Models\Transaction;
use App\Models\UPICallback;
use App\Models\UserSettlement;
use App\Models\Validation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;


class ReportsDownloadController extends Controller
{

    public function ajaxGenerateExcelFile(Request $request)
    {

        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'startDate' => 'required|date|before_or_equal:endDate',
                    'endDate' => 'required|date',
                    'reportType' => "required|in:payout,auto_settlement,upi_collect,smart_collect,van_collect,transactions,aeps,matm,recharge,dmt,verification,pan"
                ]
            );


            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Fields are required', $message);
            }


            $reportName = $request->reportType;
            $userId = Auth::user()->id;
            $startDate = $request->startDate;
            $endDate = $request->endDate;

            $checkExcits = DB::table('excel_reports')
                ->select('id')
                ->where([
                    'user_id' => $userId,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'file_name' => $reportName
                ])->first();

            if (empty($checkExcits)) {

                if ($reportName == 'UserTransactions') {
                    \App\Jobs\MultipleExcelFileDownloadJob::dispatch($userId, $startDate, $endDate, $userId, $reportName, 0, '');
                } else {
                    $filename = $startDate . time() . '.xlsx';
                    $this->downloadExcel($userId, $startDate, $endDate, $reportName, $filename);


                    DB::table('excel_reports')
                        ->insert([
                            'user_id' => $userId,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'file_name' => ucfirst(str_replace('_', '', $reportName)),
                            'search_key' => $userId . ' ' . $reportName,
                            'file_url' => date('Y-m-d') . '/' . $userId . '/' . $filename
                        ]);
                }

                $response['count'] = DB::table('excel_reports')->count() - 1;

                return ResponseHelper::success('Report is generated sucessfully.', $response);
            } else {

                return ResponseHelper::failed('Requested report already created.');
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }


    public function generateDownloadLink(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'fileId' => "required|numeric"
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Fields are required', $message);
            }

            $id = trim($request->fileId);

            $data = DB::table('excel_reports')
                ->select('id')
                ->where('id', $id)
                ->first();


            if (!empty($data)) {
                $random = base64_encode(uniqid() . $id);

                $update = DB::table('excel_reports')
                    ->where('id', $id)
                    ->update(['temp_id' => $random]);

                if ($update) {
                    return ResponseHelper::success('Link generated', ['link' => $random]);
                }

                return ResponseHelper::failed('no records founds');
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }


    public function excelDownloadLink($id)
    {
        try {
            $data = DB::table('excel_reports')
                ->select('id', 'file_url')
                ->where('temp_id', $id)
                ->first();

            if (!empty($data)) {
                $pathToFile = storage_path('app/' . $data->file_url);

                DB::table('excel_reports')
                    ->where('id', $data->id)
                    ->update(['temp_id' => '']);

                return response()->download($pathToFile);
            }

            $header = request()->header();

            return response()->json([
                'code' => "0x0205",
                'status' => "FAILURE",
                'message' => "RESOURCE NOT FOUND",
                $resp['ip'] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : request()->ip(),
                $resp['userAgent'] = isset($header["user-agent"][0]) ? $header["user-agent"][0] : "",
                $resp['country'] = isset($header["cf-ipcountry"][0]) ? $header["cf-ipcountry"][0] : "",
                'ts' => date('Y-m-d h:i:s'),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong');
        }
    }



    public function removeExportFile(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'fileId' => 'required|numeric|min:1'
                ]
            );


            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Fields are required', $message);
            }


            $id = trim($request->fileId);


            $checkExcits = DB::table('excel_reports')
                ->select('id', 'file_url')
                ->where('id', $id)
                ->first();


            if (!empty($checkExcits)) {
                DB::table('excel_reports')
                    ->where('id', $id)
                    ->delete();

                unlink(storage_path('app/' . $checkExcits->file_url));

                // $resp['status'] = true;
                // $resp['message'] = 'This report file is removed sucessfully.';
                $response['count'] = DB::table('excel_reports')->count();
                // return response()->json($resp);

                return ResponseHelper::success('Report file is removed sucessfully.', $response);
            }

            return ResponseHelper::failed('File not found.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }


    /**
     * Transactions
     */
    public function dataTableTransactions(Request $request)
    {
        try {

            $userId = Auth::user()->id;

            // $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            // $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');


            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));
            $reportType = trim($request->get('reportType'));

            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc            
            $searchValue = $search_arr; // Search value

            $records = DB::table('excel_reports')
                ->select(
                    'id',
                    'user_id',
                    'start_date',
                    'end_date',
                    'file_name',
                    'file_url',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%S') as created_at")
                )
                ->where('user_id', $userId);


            if (!empty($reportType)) {
                $records->where('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('file_name', 'like', '%' . $searchValue . '%');
                });
            }

            $records = $records->orderBy('id', $columnSortOrder)
                ->paginate($per_page);

            if ($records->isNotEmpty()) {
                $responseData['records'] = $records;
            } else {
                $responseData['records'] = null;
            }

            $responseData['startDate'] = $startDate;
            $responseData['endDate'] = $endDate;

            //fetch user activated service list
            $userServiceList = DB::table('user_services')
                ->select('global_services.service_name', 'global_services.service_slug')
                ->join('global_services', 'global_services.service_id', '=', 'user_services.service_id')
                ->where('user_services.user_id', $userId)
                ->whereNotNull('user_services.activation_date')
                ->get();

            // $isAutoSettlement = DB::table('user_config')
            //     ->select('is_auto_settlement')
            //     ->where('user_id', $userId)
            //     ->first();

            $isAutoSettlement = DB::table('user_settlements')
                ->select('id')
                ->where('user_id', $userId)
                ->first();

            $responseData['reports'] = [];

            if ($userServiceList->isNotEmpty()) {
                foreach ($userServiceList as $row) {
                    $responseData['reports'][$row->service_slug] = $row->service_name;
                }
            }

            if (!empty($isAutoSettlement)) {
                $responseData['reports']['auto_settlement'] = 'Auto Settlement';
            }

            $responseData['reports']['transactions'] = 'All Transactions';

            // $responseData['reports'] = [
            //     'aeps' => 'AEPS',
            //     'auto_settlement' => 'Auto Settlement',
            //     'patner_van' => 'Patner VAN',
            //     'recharge' => 'Recharge',
            //     'smart_collect' => 'Smart Collect',
            //     'smart_payout' => 'Smart Payout',
            //     'upi_stack' => 'UPI Stack',
            // ];

            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }



    /**
     * Generate Excel File
     */
    private function downloadExcel($userId, $startDate, $endDate, $reportName, $filename)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', "5096000000");

        function usersGenerator($reportName, $startDate, $endDate, $userId)
        {
            switch ($reportName) {
                // case 'smart_payout':
                case 'payout':
                    $sqlModel = Order::on('slave')
                        ->select(
                            'contacts.first_name as contact_first_name',
                            'contacts.last_name as contact_last_name',
                            'contacts.email as contact_email',
                            'contacts.phone as contacts_phone',
                            'contacts.type as contact_type',
                            'contacts.account_number as contact_account_number',
                            'contacts.account_ifsc as contacts_account_ifsc',
                            'contacts.vpa_address as vpaAddress',
                            'contacts.reference as contacts_reference',
                            'client_ref_id',
                            'batch_id',
                            'order_ref_id',
                            'amount',
                            'fee',
                            'tax',
                            'mode',
                            'purpose',
                            'narration',
                            'remark',
                            'orders.udf1',
                            'orders.udf2',
                            'status',
                            'bank_reference',
                            'orders.created_at'
                        )->join('contacts', 'contacts.contact_id', '=', 'orders.contact_id')
                        ->where('status', 'processed')
                        ->where('orders.user_id', $userId)
                        ->whereDate('orders.created_at', '>=', $startDate)
                        ->whereDate('orders.created_at', '<=', $endDate);

                    break;


                case 'auto_settlement':

                    $sqlModel = UserSettlement::on('slave')
                        ->select(
                            'settlement_ref_id as ref_id',
                            'mode',
                            'amount',
                            'fee',
                            'tax',
                            'account_number',
                            'account_ifsc',
                            'beneficiary_name',
                            'status',
                            'created_at'
                        )->where('status', 'processed')
                        ->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);

                    break;


                // case 'upi_stack':
                case 'upi_collect':

                    $sqlModel = UPICallback::select(
                        'payee_vpa',
                        'amount',
                        'fee',
                        'tax',
                        'txn_note',
                        'npci_txn_id',
                        'original_order_id',
                        'merchant_txn_ref_id',
                        'bank_txn_id',
                        'customer_ref_id',
                        'payer_vpa',
                        'payer_acc_name',
                        'payer_mobile',
                        'created_at'
                    )->where('upi_callbacks.user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);

                    break;


                case 'smart_collect':

                    $sqlModel = AutoCollectCallback::on('slave')
                        ->select(
                            'v_account_id',
                            'virtual_vpa_id',
                            'v_account_number',
                            'amount',
                            'fee',
                            'tax',
                            'utr',
                            'credit_ref_no',
                            'remitter_account',
                            'remitter_name',
                            'remitter_vpa',
                            'created_at'
                        )
                        ->where('cf_merchants_fund_callbacks.user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);

                    break;


                // case 'patner_van':
                case 'van_collect':

                    $sqlModel = FundReceiveCallback::on('slave')
                        ->select(
                            'v_account_id',
                            'v_account_number',
                            'amount',
                            'fee',
                            'tax',
                            'utr',
                            'reference_id',
                            'remitter_account',
                            'remitter_ifsc',
                            'remitter_name',
                            'created_at'
                        )->where('fund_receive_callbacks.user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);

                    break;


                case 'transactions':

                    $sqlModel = Transaction::on('slave')
                        ->select(
                            'trans_id',
                            'txn_id',
                            'txn_ref_id',
                            'account_number',
                            'global_services.service_name',
                            // 'user_id',
                            'order_id',
                            'tr_type',
                            'tr_amount',
                            // 'tr_total_amount',
                            'tr_fee',
                            'tr_tax',
                            'closing_balance',
                            // 'tr_date',
                            // 'tr_identifiers',
                            'tr_narration',
                            'udf1',
                            'udf2',
                            'udf3',
                            'udf4',
                            'transactions.created_at'
                        )->join('global_services', 'global_services.service_id', '=', 'transactions.service_id')
                        ->where('transactions.user_id', $userId)
                        ->whereDate('transactions.created_at', '>=', $startDate)
                        ->whereDate('transactions.created_at', '<=', $endDate);

                    break;


                case 'aeps':
                    $sqlModel = AepsTransaction::on('slave')
                        ->select(
                            'merchant_code',
                            'client_ref_id',
                            'transaction_type',
                            DB::raw("CONCAT('********', SUBSTR(aadhaar_no,-4)) AS aadhaar_no"),
                            'mobile_no',
                            'transaction_amount',
                            'commission',
                            'commission_ref_id',
                            'tds',
                            'rrn',
                            'status',
                            'created_at'
                        )->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);

                    break;


                case 'matm':
                    $sqlModel = MatmTransaction::on('slave')
                        ->select(
                            'client_ref_id',
                            'order_ref_id',
                            'stanno',
                            'rrnno',
                            'tmlogid',
                            'merchant_code',
                            'serialno',
                            'status',
                            'mid',
                            'tid',
                            'transaction_amount',
                            'commission',
                            'tds',
                            'commission_credited_at',
                            'commission_ref_id',
                            'trn_ref_id',
                            'route_type',
                            'transaction_type',
                            'imei',
                            'imsi',
                            'bank_ref_no',
                            'bank_response_code',
                            'failed_message',
                            'auth_id',
                            'invoice_no',
                            'cardno',
                            'microatm_bank_response',
                            'batch_no',
                            'bank_name',
                            'card_type',
                            'latitude',
                            'longitude',
                            'reference',
                            'udf_1',
                            'udf_2',
                            'created_at'
                        )->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);

                    break;


                case 'recharge':
                    $sqlModel = Recharge::on('slave')
                        ->select(
                            'stan_no',
                            'order_ref_id',
                            'merchant_code',
                            'phone',
                            'amount',
                            'commission',
                            'tax',
                            'status',
                            'bank_reference',
                            'failed_message',
                            'created_at',
                            'updated_at'
                        )->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);

                    break;


                case 'dmt':
                    $sqlModel = DMTFundTransfer::on('slave')
                        ->select(
                            'outlet_id',
                            'merchant_code',
                            'client_ref_id',
                            'order_ref_id',
                            'amount',
                            'fee',
                            'tax',
                            'cashback',
                            DB::raw("mobile as remitter_mobile"),
                            'beni_id',
                            'beni_name',
                            DB::raw("CONCAT('xxxxxxxx', SUBSTR(bank_account,-4)) AS bank_account"),
                            'bank_ifsc',
                            'utr',
                            // 'mode',
                            'status',
                            // 'cashback_credited_at',
                            'failed_message',
                            'created_at'
                        )
                        ->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                    break;


                case 'verification':
                    $sqlModel = Validation::on('slave')
                        ->select(
                            'client_ref_id',
                            'order_ref_id',
                            'fee',
                            'tax',
                            'type',
                            'status',
                            'failed_message',
                            'created_at'
                        )
                        ->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                    break;

                case 'pan':
                    $sqlModel = PanCardTransaction::on('slave')
                        ->select(
                            'psa_code',
                            'txn_id',
                            'order_ref_id',
                            'app_no',
                            'ope_txn_id',
                            'coupon_type',
                            'email',
                            'mobile',
                            'fee as amount',
                            'status',
                            'failed_message',
                            'created_at'
                        )->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                    break;

            }


            foreach ($sqlModel->cursor() as $user) {
                $user->createdAt = $user->created_at->format('Y-m-d H:i:s');
                unset($user->created_at);

                yield $user;
            }
        }

        $path = storage_path('app/' . date('Y-m-d') . '/' . $userId . '/');

        $filename = $path . $filename;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return (new FastExcel(usersGenerator($reportName, $startDate, $endDate, $userId)))->export($filename);
    }
}