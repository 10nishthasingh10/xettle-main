<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\CashfreeHelper;
use App\Helpers\CommonHelper;
use App\Helpers\EaseBuzzHelper;
use App\Helpers\InstantPayHelper;
use App\Helpers\SafeXPayHelper;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettlementJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 400;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 14400;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     *
     * */

    private  $userId, $settlementTxnId;

    public function __construct($userId, $settlementTxnId)
    {

        $this->userId = $userId;
        $this->settlementTxnId = $settlementTxnId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = 'public/Settlement' . $this->userId . date('H:i:s') . '.txt';

        try {

            $Order = DB::table('user_settlements')
                ->select(
                    'user_settlements.user_id as user_id',
                    'user_settlements.user_id as user_id',
                    'user_settlements.settlement_ref_id as settlement_ref_id',
                    'user_settlements.mode as mode',
                    'user_settlements.amount',
                    'user_settlements.fee',
                    'user_settlements.tax',
                    'user_settlements.account_number',
                    'user_settlements.account_ifsc',
                    'user_settlements.beneficiary_name',
                    'user_settlement_logs.service_id',
                    'user_settlements.beneficiary_name',
                    'user_settlement_logs.settlement_txn_id',
                    'user_settlement_logs.integration_id as integration_id',
                    'user_settlement_logs.status as status'
                )
                ->join('user_settlement_logs',  'user_settlement_logs.settlement_ref_id', 'user_settlements.settlement_ref_id')
                ->where('user_settlement_logs.status', 'processing')
                ->where('user_settlement_logs.cron_status', '0')
                ->where('user_settlements.is_balance_debited', '1')
                ->where('user_settlement_logs.settlement_txn_id', $this->settlementTxnId)
                ->where('user_settlement_logs.user_id', $this->userId)
                ->first();
            $userBusinessData = DB::table('business_infos')
                ->select('name', 'email', 'mobile')
                ->where('is_active', '1')
                ->where('user_id', $this->userId)
                ->first();

            if (isset($Order) && !empty($userBusinessData)) {
                $integrations = DB::table('integrations')
                    ->select('slug')
                    ->where('integration_id', $Order->integration_id)
                    ->first();

                switch ($integrations->slug) {
                    case 'cashfree':
                        $successArray = array('200');
                        $failedArray = array('403', '412', '422', '409', '404', '520', '429', '400');
                        $Cashfree = new CashfreeHelper;
                        $requestTransfer = $Cashfree->cfAutoSettlement($Order, $userBusinessData);
                        if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                            DB::table('user_settlement_logs')
                                ->where('user_id', $this->userId)
                                ->where('settlement_txn_id', $this->settlementTxnId)
                                ->update(['cron_status' => '1']);
                            if (isset($requestTransfer['data']->subCode)) {

                                $errorDesc = $requestTransfer['data']->message;
                                if (in_array($requestTransfer['data']->subCode, $successArray)) {
                                    $message = $requestTransfer['data']->message;
                                    $statusCode = $requestTransfer['data']->subCode;
                                    $bank_reference = isset($requestTransfer['data']->data->utr) ? $requestTransfer['data']->data->utr : "";
                                    Storage::disk('local')->append($fileName, 'data Found ' . $this->userId);
                                    DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id, 'processed', '" . $message . "', '" . $statusCode . "', '','" . $bank_reference . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                } else if (in_array($requestTransfer['data']->subCode, $failedArray)) {

                                    $errorDesc = isset($requestTransfer['data']->data->transfer->reason) ? $requestTransfer['data']->data->transfer->reason : $errorDesc;
                                    $statusCode = $requestTransfer['data']->subCode;
                                    if (str_contains($errorDesc, 'Not enough available')) {
                                        $errorDesc = 'Something went wrong, please try after some time';
                                    }
                                    if (!str_contains($requestTransfer['data']->message, 'Internal server error')) {
                                        $utr = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";
                                        Storage::disk('local')->append($fileName, 'data Found ' . $this->userId);
                                        DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id, 'failed', '', '" . $statusCode . "', '" . $errorDesc . "','" . $utr . "', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                    }
                                }
                            }
                        }
                        break;
                    case 'safexpay':
                        $successArray = array('0000', 'S0026', 'S0033', 'S0035');
                        $failedArray = array('0002', '00002', 'B', 'E0005', 'E0010', 'E0011', 'E0021', 'E0027', 'E0030', 'E0039', 'E0046', 'E0049', 'E0055', 'E0058', 'E0092', 'E0151', 'E0152', 'E0153', 'E0154', 'E0155', 'E0156', 'E0158', 'E0160', 'E0161', 'E0165', 'E0187', 'E0197', 'E0198', 'E0211', 'E0212', 'E0213', 'E0214', 'E0221', 'E0237', 'E0249', 'E0279', 'E0359', 'E0389', 'E0404', 'E0405', 'E0406', 'E0407', 'E0409', 'E0429', 'E0435', 'E0452', 'E0478', 'E0480', 'E0492', 'E0494', 'E0495', 'E0497', 'E0498', 'E0506', 'E0510', 'E0511', 'E0521', 'E0522', 'E0523', 'E0530', 'E0531', 'E0532', 'E0541', 'E0507', 'E0542', 'E0543', 'E0544', 'E0545', 'E0546', 'E0547', 'E0553', 'E0552', 'E0554', 'E0558', 'F', 'L0032', 'M', 'N', 'O');
                        $SafeXPay = new SafeXPayHelper;
                        $txnType = CommonHelper::case($Order->mode, 'u');
                        $payoutWithoutOtp = $SafeXPay->sfAutoSettlement($userBusinessData->mobile, $Order->amount, $Order->account_number, $Order->account_ifsc, 'BANKNAME', $userBusinessData->name, $txnType, 'SAVING', $Order->settlement_txn_id, $Order->user_id);
                        $response = json_decode($payoutWithoutOtp);
                        if (isset($response->response)) {
                            DB::table('user_settlement_logs')
                                ->where('user_id', $this->userId)
                                ->where('settlement_txn_id', $Order->settlement_txn_id)
                                ->update(['cron_status' => '1']);
                            if (in_array($response->response->code, $successArray) && $response->payOutBean->statusCode) {
                                $statusCode = isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code;
                                $errorDesc  = $response->payOutBean->statusDesc;
                                if (in_array($statusCode, $successArray)) {
                                    $message = $response->payOutBean->statusDesc;
                                    $statusCode = $response->payOutBean->statusCode;
                                    $bank_reference = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";

                                    DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id, 'processed', '" . $message . "', '" . $statusCode . "','', '" . $bank_reference . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                } elseif (in_array($statusCode, $failedArray)) {

                                    $errorDesc = isset($response->response->description) ? $response->response->description : $errorDesc;
                                    $utr = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";

                                    DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id,  'failed', '" . $errorDesc . "', '" . $statusCode . "', '','" . $utr . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                }
                            } elseif (in_array($response->response->code, $failedArray)) {
                                $errorDesc = isset($response->response->description) ? $response->response->description : "";
                                $statusCode = isset($response->response->code) ? $response->response->code : "";
                                $utr = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";


                                DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "', '" . $errorDesc . "', '" . $utr . "', @json)");
                                $results = DB::select('select @json as json');
                                $response = json_decode($results[0]->json, true);
                            }
                        }
                        break;

                    case 'easebuzz':

                        $eazeBuzz = new EaseBuzzHelper;
                        $requestTransfer = $eazeBuzz->ebAutoSettlement($Order, $userBusinessData->name);

                        if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                            DB::table('user_settlement_logs')
                                ->where('user_id', $this->userId)
                                ->where('settlement_txn_id', $Order->settlement_txn_id)
                                ->update(['cron_status' => '1']);
                            if (
                                isset($requestTransfer['data']->success) &&
                                ($requestTransfer['data']->success == true &&
                                    $requestTransfer['data']->data->transfer_request->status == 'success')
                            ) {

                                $message = $requestTransfer['data']->message;
                                $statusCode = 200;
                                $bank_reference = isset($requestTransfer['data']->data->transfer_request->unique_transaction_reference) ? $requestTransfer['data']->data->transfer_request->unique_transaction_reference : "";
                                DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id, 'processed', '" . $message . "', '" . $statusCode . "', '', '" . $bank_reference . "', @json)");
                                $results = DB::select('select @json as json');
                                $response = json_decode($results[0]->json, true);
                            } else if (isset($requestTransfer['data']
                                ->success) && $requestTransfer['data']
                                ->success == false
                            ) {
                                $errorMsg = isset($requestTransfer['data']->message) ? $requestTransfer['data']->message : "";
                                $errorDesc = isset($requestTransfer['data']->data->transfer_request->failure_reason) ? $requestTransfer['data']->data->transfer_request->failure_reason : $errorMsg;
                                $statusCode = '';
                                if (str_contains($errorDesc, 'Insufficient account balance.')) {
                                    $errorDesc = 'Something went wrong, please try after some time';
                                }

                                $utr = isset($requestTransfer['data']->data->transfer_request->id) ? $requestTransfer['data']->data->transfer_request->id : " ";

                                DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id,  'failed', '" . $errorDesc . "', '" . $statusCode . "', '" . $errorDesc . "', '" . $utr . "', @json)");
                                $results = DB::select('select @json as json');
                                $response = json_decode($results[0]->json, true);
                            } else if (isset($requestTransfer['data']
                                ->failure)) {
                                $errorDesc = isset($requestTransfer['data']->message) ? $requestTransfer['data']->message : "NA";
                                $statusCode = '';
                                if (str_contains($errorDesc, 'Insufficient account balance.')) {
                                    $errorDesc = 'Something went wrong, please try after some time';
                                }

                                $utr = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";

                                DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id,  'failed','" . $errorDesc . "', '" . $statusCode . "','" . $errorDesc . "', '" . $utr . "', @json)");
                                $results = DB::select('select @json as json');
                                $response = json_decode($results[0]->json, true);
                            }
                        }

                        break;
                    case 'instantpay':
                        $failedArray = [
                            'RPI', 'UAD', 'IAC', 'IAT', 'AAB', 'IAB', 'ISP', 'DID', 'DTX', 'IAN', 'IRA', 'DTB', 'RBT', 'SPE', 'SPD', 'UED', 'IEC', 'IRT', 'ITI', 'TSU', 'IPE', 'ISE', 'TRP', 'OUI', 'ODI', 'TDE', 'DLS', 'RNF', 'RAR', 'IVC',
                            'IUA', 'SNA', 'ERR', 'FAB', 'UFC', 'OLR', 'EOP', 'ONV', 'RAB'
                        ];
                        $instantPay = new InstantPayHelper;
                        $requestTransfer = $instantPay->ipAutoSettlement($Order, $userBusinessData->name);

                        if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                            DB::table('user_settlement_logs')
                                ->where(['user_id' => $Order->user_id, 'settlement_txn_id' => $Order->settlement_txn_id])
                                ->update(['cron_date' => date('Y-m-d H:i:s'), 'cron_status' => '1']);

                            if (isset($requestTransfer['data']->statuscode) && $requestTransfer['data']->statuscode == 'TXN') {

                                $message = $requestTransfer['data']->status;
                                $statusCode = 200;
                                $bank_reference = isset($requestTransfer['data']->data->txnReferenceId) ? $requestTransfer['data']->data->txnReferenceId : "";
                                if ($bank_reference != '00' && $bank_reference != '') {
                                    DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id, 'processed', '" . $message . "', '" . $statusCode . "', '','" . $bank_reference . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                }
                            } else if (isset($requestTransfer['data']->statuscode) && ($requestTransfer['data']->statuscode == 'TUP')) {
                                $refId = @$requestTransfer['data']->data->externalRef;
                                DB::table('user_settlement_logs')
                                    ->where(['user_id' => $Order->user_id, 'settlement_txn_id' => $Order->settlement_txn_id])
                                    ->update(['payout_id' => $refId, 'cron_date' => date('Y-m-d H:i:s')]);
                            } else if (isset($requestTransfer['data']
                                ->statuscode) && in_array($requestTransfer['data']
                                ->statuscode, $failedArray)) {
                                $errorMsg = $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : "";
                                $statusCode = '';
                                if (in_array($requestTransfer['data']
                                    ->statuscode, ['IAB'])) {
                                    $errorDesc = 'Something went wrong, please try after some time';
                                }

                                $utr = @$requestTransfer['data']->data->txnReferenceId;

                                DB::select("CALL SettlementStatusUpdate('" . $Order->settlement_ref_id . "','" . $Order->settlement_txn_id . "', $Order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "', '" . $errorDesc . "', '" . $utr . "', @json)");
                                $results = DB::select('select @json as json');
                                $response = json_decode($results[0]->json, true);
                            }
                        }
                        break;
                }
            } else {
                //Storage::disk('local')->append($fileName, 'No record founds'.' '.$this->userId);
            }
        } catch (\Exception  $e) {
            Storage::disk('local')->append($fileName, $e);
        }
    }

    public function middleware()
    {
        return [(new WithoutOverlapping($this->userId))->releaseAfter(rand(1, 30))];
    }

    public function retryUntil()
    {
        return now()->addHours(10);
    }
}
