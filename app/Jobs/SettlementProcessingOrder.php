<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Helpers\CashfreeHelper;
use App\Helpers\CommonHelper;
use App\Helpers\EaseBuzzHelper;
use App\Helpers\InstantPayHelper;
use App\Helpers\RazorpayHelper;
use App\Helpers\SafeXPayHelper;
use App\Helpers\TransactionHelper;
use App\Models\BulkPayout;
use App\Models\BulkPayoutDetail;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettlementProcessingOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     *
     * */
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 350;

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
    private $orderId, $userId, $fileName;
    public function __construct($orderId, $userId)
    {
        $this->orderId = $orderId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = 'SettlementOrderProcessingJobs' . $this->orderId . '.txt';
        try {
         
            // Storage::disk('local')->put($fileName, 'processing order : '.$this->orderId);
            $order = DB::table('user_settlement_logs')
                ->select('*')->where('status', 'processing')
                ->where('cron_status', '1')
                ->where('user_id', $this->userId)
                ->where('settlement_txn_id', $this->orderId)
                ->first();
            if (isset($order)) {
                //  Storage::disk('local')->append($fileName, ' order data : '.$order->integration_id.' '.$order->area);

                $route = DB::table('integrations')
                    ->select('slug')
                    ->where('integration_id', $order->integration_id)
                    ->first();
                $types = isset($route->slug) ? $route->slug : "NA";

                switch ($types) {
                    case 'cashfree':
                        $successArray = array('200');
                        $failedArray = array('404');
                        $Cashfree = new CashfreeHelper;
                        $requestTransfer = $Cashfree->getDirectTransferStatus($order->settlement_txn_id);
                        if (isset($requestTransfer['data'])) {
                            if (isset($requestTransfer['data']
                                ->subCode)) {

                                $errorDesc = $requestTransfer['data']->message;
                                $bank_reference = "";
                                $status = "pending";
                                if (in_array($requestTransfer['data']->subCode, $successArray) && $requestTransfer['data']->data->transfer->status == 'SUCCESS') {
                                    $message = $requestTransfer['data']->message;
                                    $statusCode = $requestTransfer['data']->subCode;
                                    $status = "processed";
                                    $bank_reference  = isset($requestTransfer['data']->data->transfer->utr) ? $requestTransfer['data']->data->transfer->utr : "";
                                   
                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "','" . $order->settlement_txn_id . "', $order->user_id, 'processed', '" . $message . "', '" . $statusCode . "','', '" . $bank_reference . "', @json)");
                                    $results = DB::select('select @json as json');
                                } else if (in_array($requestTransfer['data']->subCode, $successArray) && $requestTransfer['data']->data->transfer->status == 'FAILED') {
                                    
                                    $errorDesc = isset($requestTransfer['data']->data->transfer->reason) ? $requestTransfer['data']->data->transfer->reason : $errorDesc;
                                    $statusCode = $requestTransfer['data']->subCode;
                                  
                                    $utr = $bank_reference = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";

                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id, 'failed', '" . $errorDesc . "', '" . $statusCode . "','" . $errorDesc . "','" . $utr . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                } elseif (in_array($requestTransfer['data']->subCode, $failedArray)) {
                         
                                    $errorDesc = isset($requestTransfer['data']->data->transfer->reason) ? $requestTransfer['data']->data->transfer->reason : $errorDesc;
                                    $statusCode = $requestTransfer['data']->subCode;
                                   
                                    $utr = $bank_reference = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";

                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id, 'failed', '" . $errorDesc . "', '" . $statusCode . "','" . $errorDesc . "', '" . $utr . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                }
                            }
                        }
                        break;
                    case 'safexpay':

                        $successArray = array('0000', 'S0026', 'S0033', 'S0035');
                        $pendingArray = array('0001', 'E0499', 'E0513');
                        $failedArray = array('0002', '00002', 'B', 'E0005', 'E0010', 'E0011', 'E0021', 'E0027', 'E0030', 'E0039', 'E0046', 'E0049', 'E0055', 'E0058', 'E0092', 'E0151', 'E0152', 'E0153', 'E0154', 'E0155', 'E0156', 'E0158', 'E0160', 'E0161', 'E0165', 'E0187', 'E0197', 'E0198', 'E0211', 'E0212', 'E0213', 'E0214', 'E0221', 'E0237', 'E0249', 'E0279', 'E0359', 'E0389', 'E0404', 'E0405', 'E0406', 'E0407', 'E0409', 'E0429', 'E0435', 'E0452', 'E0478', 'E0480', 'E0492', 'E0494', 'E0495', 'E0497', 'E0498', 'E0506', 'E0510', 'E0511', 'E0521', 'E0522', 'E0523', 'E0530', 'E0531', 'E0532', 'E0541', 'E0507', 'E0542', 'E0543', 'E0544', 'E0545', 'E0546', 'E0547', 'E0553', 'E0552', 'E0554', 'E0558', 'F', 'L0032', 'M', 'N', 'O', 'IP002');
                        $safeXPay = new SafeXPayHelper;
                        $checkStatus = $safeXPay->payoutStatusCheckByOrderRefId($order->settlement_txn_id, $order->user_id);
                        $response = json_decode($checkStatus);
                        $status = 'pending';
                        $bank_reference = '';
                        $errorDesc = $response->response->description;
                        if (in_array($response->response->code, $successArray)  && $response->payOutBean->statusCode) {

                            $statusCode = isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code;
                            if (in_array($statusCode, $successArray)) {
                                $status = 'processed';
                                $statusCode = isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code;
                                $errorDesc = $message = isset($response->payOutBean->statusDesc) ? $response->payOutBean->statusDesc : $errorDesc;
                                $bank_reference = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";
                                DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "','" . $order->settlement_txn_id . "', $order->user_id, 'processed', '" . $message . "', '" . $statusCode . "', '','" . $bank_reference . "', @json)");
                                $results = DB::select('select @json as json');
                                $response = json_decode($results[0]->json, true);
                            } else if (in_array($statusCode, $pendingArray)) {
                                DB::table('user_settlement_logs')->where('settlement_txn_id', $order->settlement_txn_id)
                                    ->update([
                                        'cron_status' => '1',
                                        'status_code' => $statusCode,
                                        'payout_id' => $response->payOutBean->payoutId
                                    ]);
                            } else if (in_array($statusCode, $failedArray)) {
                               
                                $errorDesc = isset($response->payOutBean->statusDesc) ? $response->payOutBean->statusDesc : $errorDesc;

                                $utr = $bank_reference = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";
                                DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "', '" . $errorDesc . "', '" . $utr . "', @json)");
                                $results = DB::select('select @json as json');
                                $response = json_decode($results[0]->json, true);
                            }
                        } else if (in_array($response->response->code, $pendingArray)) {
                            DB::table('user_settlement_logs')->where('settlement_txn_id', $order->settlement_txn_id)
                                ->update([
                                    'cron_status' => '1',
                                    'payout_id' => $response->payOutBean->payoutId
                                ]);
                        } else if (in_array($response->response->code, $failedArray)) {
                          
                            $statusCode = isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code;
                            $errorDesc = isset($response->payOutBean->statusDesc) ? $response->payOutBean->statusDesc : $errorDesc;

                            $utr = $bank_reference = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";

                            DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "', '" . $errorDesc . "',  '" . $utr . "', @json)");
                            $results = DB::select('select @json as json');
                            $response = json_decode($results[0]->json, true);
                        }

                        break;

                    case 'easebuzz':
                        $easeBuzz = new EaseBuzzHelper;
                        $requestTransfer = $easeBuzz->quickTransferStatus($order->settlement_txn_id, $order->user_id);
                        if (isset($requestTransfer['data'])) {
                            if (isset($requestTransfer['data']
                                ->success)) {
                                $errorDesc = isset($requestTransfer['data']->message) ? $requestTransfer['data']->message : $requestTransfer['data']
                                    ->success;

                                $bank_reference = "";
                               
                                if ($requestTransfer['data']->success == true && $requestTransfer['data']->data->transfer_request->status == 'success') {
                                    $message = $requestTransfer['data']->success;
                                    $statusCode = $requestTransfer['data']->success;
                                   
                                    $bank_reference  = isset($requestTransfer['data']->data->transfer_request->unique_transaction_reference) ? $requestTransfer['data']->data->transfer_request->unique_transaction_reference : "";

                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id, 'processed', '" . $message . "', '" . $statusCode . "','', '" . $bank_reference . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                } else if ($requestTransfer['data']->success == true && ($requestTransfer['data']->data->transfer_request->status == 'failure' ||  $requestTransfer['data']->data->transfer_request->status == 'rejected')) {
                                    
                                    $errorDesc = isset($requestTransfer['data']->data->transfer_request->failure_reason) ? $requestTransfer['data']->data->transfer_request->failure_reason : $errorDesc;
                                    $statusCode = $requestTransfer['data']->success;

                                    $utr = $bank_reference = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";

                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id,  'failed', '" . $errorDesc . "', '" . $statusCode . "',  '" . $errorDesc . "', '" . $utr . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                } elseif ($requestTransfer['data']->success == false) {
                                  
                                    $statusCode = $requestTransfer['data']->success;

                                    $utr = $bank_reference = " ";

                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "',  '" . $errorDesc . "',  '" . $utr . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                }
                            }
                        }
                        break;
                    case 'instantpay':
                        $instantPay = new InstantPayHelper;
                        if (isset($order->cron_date) && !empty($order->cron_date)) {
                            $requestTransfer = $instantPay->instantpayTransferStatus($order->settlement_txn_id, $order->cron_date, $order->user_id);
                            if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                                $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : "";
                                //   Storage::disk('local')->append($fileName, 'easebuzz status : '.$errorDesc);
                                $bank_reference = "";
                                

                                $failedArray = ['IAN', 'FAB', 'TRP'];
                                if (
                                    isset($requestTransfer['data']->data) &&
                                    ($requestTransfer['data']->data->transactionStatusCode == 'TXN' && $requestTransfer['data']->data->transactionReferenceId != '00')
                                ) {
                                    $message = @$requestTransfer['data']->data->transactionStatus;
                                    $statusCode = 200;
                                 
                                    $bank_reference  = isset($requestTransfer['data']->data->transactionReferenceId) ? $requestTransfer['data']->data->transactionReferenceId : "";
                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id, 'processed', '" . $message . "', '" . $statusCode . "', '','" . $bank_reference . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                } else if (isset($requestTransfer['data']
                                    ->data->transactionStatusCode) && in_array($requestTransfer['data']
                                    ->data->transactionStatusCode, $failedArray)) {
                                 
                                    $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : $errorDesc;
                                    $statusCode = $requestTransfer['data']->statuscode;

                                    $utr = $bank_reference = isset($requestTransfer['data']->data->transactionReferenceId) ? $requestTransfer['data']->data->transactionReferenceId : " ";

                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "','" . $errorDesc . "',  '" . $utr . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                } else if (isset($requestTransfer['data']
                                    ->data->transactionStatusCode) && in_array($requestTransfer['data']
                                    ->data->transactionStatusCode, ['ERR']) && ($requestTransfer['data']
                                    ->data->transactionReferenceId == null || $requestTransfer['data']
                                    ->data->transactionReferenceId == '00')) {
                                
                                    $errorDesc = isset($requestTransfer['data']->data->transactionStatus) ? $requestTransfer['data']->data->transactionStatus : $errorDesc;
                                    $statusCode = $requestTransfer['data']->data->transactionStatusCode;

                                    $utr = $bank_reference = isset($requestTransfer['data']->data->transactionReferenceId) ? $requestTransfer['data']->data->transactionReferenceId : " ";

                                    DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "','" . $errorDesc . "', '" . $utr . "', @json)");
                                    $results = DB::select('select @json as json');
                                    $response = json_decode($results[0]->json, true);
                                }
                            }
                        }
                        break;
                    case 'razorpay':
                        $razorpay = new RazorpayHelper;
                        if (isset($order->payout_id) && !empty($order->payout_id)) {
                            $requestTransfer = $razorpay->razorpayGetPayoutById($order->payout_id);
                            if (isset($requestTransfer['data'])) {
                                $successArray = array('processed');
                                $pendingArray = array('pending', 'processing');
                                $failedArray = array('rejected', 'cancelled', 'failed', 'failure');
                                $reversedArray = array('reversed');
                                if (isset($requestTransfer['data']
                                    ->status)) {
                                    $errorDesc = isset($requestTransfer['data']->failure_reason) ? $requestTransfer['data']->failure_reason : $requestTransfer['data']
                                        ->status;
                                    $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : "";
                                   
                                    if (in_array($requestTransfer['data']
                                        ->status, $successArray)) {
                                        $message = $requestTransfer['data']->status;
                                        $statusCode = $requestTransfer['data']->status;
                                     
                                        DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id, 'processed', '" . $message . "', '" . $statusCode . "','', '" . $bank_reference . "', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                    } else if (in_array($requestTransfer['data']
                                        ->status, $failedArray)) {
                                  
                                        $statusCode = $requestTransfer['data']->status;

                                        $utr = $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : " ";

                                        DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "', '" . $errorDesc . "', '" . $utr . "', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                    } else if (in_array($requestTransfer['data']
                                        ->status, $reversedArray)) {
                                     
                                        $statusCode = $requestTransfer['data']->status;

                                        $utr = $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : " ";

                                        DB::select("CALL SettlementStatusUpdate('" . $order->settlement_ref_id . "', '" . $order->settlement_txn_id . "', $order->user_id,  'failed',  '" . $errorDesc . "', '" . $statusCode . "','" . $errorDesc . "','" . $utr . "', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                    }
                                }
                            }
                        } else {
                            $message = "payout id not be null";
                        }

                        break;
                }
            }
            //code...
        } catch (\Exception  $e) {

            Storage::disk('local')->put('errorlog_' . $fileName, $e . time());
        }
    }

    public function middleware()
    {
        //$time = [5];
        return [(new WithoutOverlapping($this->userId))->releaseAfter(rand(1, 30))];
    }

    public function retryUntil()
    {
        return now()->addHours(8);
    }
}
