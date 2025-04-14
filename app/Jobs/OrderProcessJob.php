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
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderProcessJob implements ShouldQueue
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

    private $orderRefId, $userId;

    public function __construct($orderRefId, $userId)
    {
        $this->orderRefId = $orderRefId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = 'public/CheckOrderJobs'.$this->orderRefId.'.txt';
        //Storage::disk('local')->put($fileName, 'start 1'.date('H:i:s'));
            //Scheduled S0036 //Queued E0520
        try {

          
            $orderCount = 0;
            $Order = Order::select('*')->where('status', 'queued')
                ->where('orders.cron_status', '0')
                ->join('contacts', 'contacts.contact_id', 'orders.contact_id')
                ->whereIn('orders.area', ['11', '22'])
                ->where('orders.user_id', $this->userId)
                ->where('orders.order_ref_id', $this->orderRefId)
                ->orderBy('orders.id', 'asc')
                ->first();

           // Storage::disk('local')->append($fileName, 'start 2'.$this->userId. ' '.$this->orderRefId. ' '.$route['integration_id'].date('H:i:s') );
                if(isset($Order)) {
                    $userConfigGetRoute = CommonHelper::getPayoutRouteUsingUserId($Order->user_id, 'api');
                    if ($userConfigGetRoute['status']) {
                        $types = $userConfigGetRoute['slug'];
                        $integrationId = $userConfigGetRoute['integration_id'];
                    } else {
                        $route = CommonHelper::defaultPayoutRoute('api_payout_route');
                        $types = $route['slug'];
                        $integrationId = $route['integration_id'];
                    }

                    //Storage::disk('local')->append($fileName, 'start 3'. $types.'=>'.date('H:i:s'));
                    switch ($types)
                    {
                        case 'cashfree':
                        //    Storage::disk('local')->append($fileName, 'start 3 cashfree '. date('H:i:s'));
                            $successArray = array('200');
                            $failedArray = array('403', '412', '422', '409', '404', '520', '429','400');
                                        $orderCount += 1;
                                        //$lockeOrder['status'] = false;
                                        $lockeOrder = TransactionHelper::moveOrderToProcessingByOrderId($Order->user_id, $Order->order_ref_id, $integrationId);
                                        if($lockeOrder['status'] && isset($Order)) {
                                         //   Storage::disk('local')->append($fileName, 'step 5 order_ref_id => '.$Order->order_ref_id. ' time => '.date('H:i:s'));
                                            $orderData['order_ref_id'] = $Order->order_ref_id;
                                            $orderData['name'] = $Order->first_name . ' ' . $Order->last_name;
                                            $orderData['remark'] = $Order->remark;
                                            $orderData['user_id'] = $Order->user_id;
                                            $orderData['email'] = 'example@example.com'; //$Order->email;
                                            $orderData['phone'] = '9999999999';//$Order->phone;
                                            $orderData['mode'] = $Order->mode;
                                            $orderData['address'] = $Order->address1;
                                            $orderData['remark'] = $Order->remark;
                                            $orderData['contact_id'] = $Order->contact_id;
                                            $orderData['amount'] = $Order->amount;
                                            if ($Order->account_type == 'vpa') {
                                                $paymentMode = 'UPI';
                                                $orderData['vpa_address'] = $Order->vpa_address;
                                            } else {
                                                $paymentMode = 'BANK_ACCOUNT';
                                                $orderData['account_number'] = $Order->account_number;
                                                $orderData['account_ifsc'] = $Order->account_ifsc;
                                            }
                                          //  Storage::disk('local')->append($fileName, 'step 6 order_ref_id => '.date('H:i:s'));
                                            $Cashfree = new CashfreeHelper;
                                            $requestTransfer = $Cashfree->requestDirectTransfer($orderData, $paymentMode);
                                            $orderStatus = 'pending';
                                          //  Storage::disk('local')->append($fileName, 'step 7 status => pending'.' '.date('H:i:s'));
                                            if (isset($requestTransfer['data']) && $requestTransfer['data'] != null)
                                            {
                                              //  Storage::disk('local')->append($fileName, 'step 8 code => sub_code');
                                                if (isset($requestTransfer['data']
                                                    ->subCode))
                                                {
                                                //    Storage::disk('local')->append($fileName, 'step 9 message => '.$requestTransfer['data']->message.' '.date('H:i:s'));
                                                    $errorDesc = $requestTransfer['data']->message;
                                                    if (in_array($requestTransfer['data']->subCode, $successArray)) {
                                                        $message = $requestTransfer['data']->message;
                                                        $statusCode = $requestTransfer['data']->subCode;
                                                        $bank_reference = isset($requestTransfer['data']->data->utr) ? $requestTransfer['data']->data->utr : "";
                                                        DB::select("CALL OrderStatusProcessedUpdate('".$Order->order_ref_id."', $Order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                                        $results = DB::select('select @json as json');
                                                        $response = json_decode($results[0]->json, true);
                                                        if($response['status'] == '1') {
                                                            TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'processed');
                                                        }

                                                    }
                                                    if (in_array($requestTransfer['data']->subCode, $failedArray)) {

                                                        $errorDesc = isset($requestTransfer['data']->data->transfer->reason) ? $requestTransfer['data']->data->transfer->reason : $errorDesc;
                                                        $statusCode = $requestTransfer['data']->subCode;
                                                        if (str_contains($errorDesc, 'Not enough available')) {
                                                            $errorDesc = 'Something went wrong, please try after some time';
                                                        }
                                                        if (!str_contains($requestTransfer['data']->message, 'Internal server error')) {
                                                            $txn = CommonHelper::getRandomString('txn', false);
                                                            $utr = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";
                                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                                            DB::select("CALL OrderStatusUpdate('".$Order->order_ref_id."', $Order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                                            $results = DB::select('select @json as json');
                                                            $response = json_decode($results[0]->json, true);
                                                            if($response['status'] == '1') {
                                                                TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'failed');
                                                            }
                                                        }
                                                    }
                                                }
                                             //   Storage::disk('local')->append($fileName, 'step 10 orderClose '.date('H:i:s'));
                                        }
                                    } else{
                                        $messages = 'Order already processed';
                                      //  Storage::disk('local')->append($fileName, 'balanceDebitFailure => '.$lockeOrder['message'].' '.date('H:i:s'));
                                    }
                        break;
                        case 'safexpay':
                            $successArray = array('0000', 'S0026', 'S0033', 'S0035');
                            $pendingArray = array('0001', 'E0499', 'E0513');
                            $failedArray = array('0002', '00002', 'B', 'E0005', 'E0010', 'E0011', 'E0021', 'E0027', 'E0030', 'E0039', 'E0046', 'E0049', 'E0055', 'E0058', 'E0092', 'E0151', 'E0152', 'E0153', 'E0154', 'E0155', 'E0156', 'E0158', 'E0160', 'E0161', 'E0165', 'E0187', 'E0197', 'E0198', 'E0211', 'E0212', 'E0213', 'E0214', 'E0221', 'E0237', 'E0249', 'E0279', 'E0359', 'E0389', 'E0404', 'E0405', 'E0406', 'E0407', 'E0409', 'E0429', 'E0435', 'E0452', 'E0478', 'E0480', 'E0492', 'E0494', 'E0495', 'E0497', 'E0498', 'E0506', 'E0510', 'E0511', 'E0521', 'E0522', 'E0523', 'E0530', 'E0531', 'E0532', 'E0541', 'E0507', 'E0542', 'E0543', 'E0544', 'E0545', 'E0546', 'E0547', 'E0553', 'E0552', 'E0554', 'E0558', 'F', 'L0032', 'M', 'N', 'O', 'IP002');
                            $orderCount += 1;
                                //$lockeOrder['status'] = false;
                            $lockeOrder = TransactionHelper::moveOrderToProcessingByOrderId($Order->user_id, $Order->order_ref_id, $integrationId);
                            if($lockeOrder['status'] && isset($Order)) {
                                $SafeXPay = new SafeXPayHelper;
                                $payoutWithoutOtp = $SafeXPay->payoutWithoutOtp($Order->contact->phone, $Order->amount, $Order->contact->account_number, $Order->contact->account_ifsc, 'BANKNAME', $Order->contact->first_name, $Order->mode, 'SAVING', $Order->order_ref_id, $Order->user_id);
                                $response = json_decode($payoutWithoutOtp);
                                if (isset($response->response)) {
                                    if (in_array($response->response->code, $successArray) && $response->payOutBean->statusCode) {
                                        $statusCode = isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code;
                                        $errorDesc  = $response->payOutBean->statusDesc;
                                        if (in_array($statusCode, $successArray)) {
                                            $message = $response->payOutBean->statusDesc;
                                            $statusCode = $response->payOutBean->statusCode;
                                            $bank_reference = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";
                                            DB::table('orders')
                                                ->where('user_id', $this->userId)
                                                ->where('order_ref_id', $this->orderRefId)
                                                ->update([
                                                    'payout_id' => $response->payOutBean->payoutId
                                                ]);
                                            DB::select("CALL OrderStatusProcessedUpdate('".$Order->order_ref_id."', $Order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1') {
                                                TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'processed');
                                            }
                                        } elseif (in_array($statusCode, $pendingArray)) {
                                            Order::where('order_id', $Order->order_id)->where('order_ref_id', $Order->order_ref_id)
                                                ->update([
                                                    'status' => 'processing',
                                                    'txt_1' => isset($response->payOutBean->spkRefNo) ? $response->payOutBean->spkRefNo : "",
                                                    'cron_status' => '1',
                                                    'status_code' => $statusCode,
                                                    'status_response' => $response->response->description,
                                                    'payout_id' => $response->payOutBean->payoutId
                                                ]);
                                        } elseif (in_array($statusCode, $failedArray)) {

                                            $errorDesc = isset($response->response->description) ? $response->response->description : $errorDesc;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";
                                            DB::table('orders')
                                            ->where('user_id', $this->userId)
                                            ->where('order_ref_id', $this->orderRefId)
                                                ->update([
                                                    'payout_id' => isset($response->payOutBean->payoutId) ? $response->payOutBean->payoutId : ''
                                                ]);
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$Order->order_ref_id."', $Order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1') {
                                                TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'failed');
                                            }
                                        }
                                    } elseif (in_array($response->response->code, $pendingArray)) {
                                        Order::where('user_id', $this->userId)
                                            ->where('order_ref_id', $this->orderRefId)
                                            ->update([
                                                'status' => 'processing',
                                                'txt_1' => isset($response->payOutBean->spkRefNo) ? $response->payOutBean->spkRefNo : "",
                                                'cron_status' => '1',
                                                'bank_reference' => isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "",
                                                'status_code' => $response->response->code,
                                                'status_response' => $response->response->description,
                                                'payout_id' => $response->payOutBean->payoutId
                                            ]);
                                    } elseif (in_array($response->response->code, $failedArray)) {
                                        $errorDesc = isset($response->response->description) ? $response->response->description : "";
                                        $statusCode = isset($response->response->code) ? $response->response->code : "";
                                        $txn = CommonHelper::getRandomString('txn', false);
                                        $utr = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";
                                        DB::table('orders')
                                        ->where('user_id', $this->userId)
                                        ->where('order_ref_id', $this->orderRefId)
                                            ->update([
                                                'payout_id' => isset($response->payOutBean->payoutId) ? $response->payOutBean->payoutId : ''
                                            ]);
                                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                        DB::select("CALL OrderStatusUpdate('".$Order->order_ref_id."', $Order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1') {
                                            TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'failed');
                                        }
                                    }
                                }
                            } else{
                                $messages = 'Order already processed..';
                            }
                        break;

                        case 'easebuzz':

                            $orderCount += 1;
                            //$lockeOrder['status'] = false;
                            $lockeOrder = TransactionHelper::moveOrderToProcessingByOrderId($Order->user_id, $Order->order_ref_id, $integrationId);
                            if($lockeOrder['status'] && isset($Order)) {
                               // Storage::disk('local')->append($fileName, 'step 5 order_ref_id => '.$Order->order_ref_id. ' time => '.date('H:i:s'));
                                $orderData['order_ref_id'] = $Order->order_ref_id;
                                $orderData['name'] = $Order->first_name . ' ' . $Order->last_name;
                                $orderData['remark'] = $Order->remark;
                                $orderData['user_id'] = $Order->user_id;
                                $orderData['email'] = 'example@example.com'; //$Order->email;
                                $orderData['phone'] = '9999999999';//$Order->phone;
                                $orderData['mode'] = $Order->mode;
                                $orderData['address'] = $Order->address1;
                                $orderData['remark'] = $Order->remark;
                                $orderData['contact_id'] = $Order->contact_id;
                                $orderData['amount'] = $Order->amount;
                                    if ($Order->account_type == 'vpa') {
                                        $paymentMode = 'UPI';
                                        $orderData['vpa_address'] = $Order->vpa_address;
                                    } else {
                                        $paymentMode = 'BANK_ACCOUNT';
                                        $orderData['account_number'] = $Order->account_number;
                                        $orderData['account_ifsc'] = $Order->account_ifsc;
                                    }
                                        $eazeBuzz = new EaseBuzzHelper;
                                        $requestTransfer = $eazeBuzz->quickTransfer($orderData, $paymentMode);
                                        $orderStatus = 'pending';
                                        if (isset($requestTransfer['data']) && $requestTransfer['data'] != null)
                                        {
                                            if (isset($requestTransfer['data']->success) &&
                                                ($requestTransfer['data']->success == true &&
                                                $requestTransfer['data']->data->transfer_request->status == 'success'))
                                            {

                                                $message = $requestTransfer['data']->message;
                                                $statusCode = 200;
                                                $bank_reference = isset($requestTransfer['data']->data->transfer_request->unique_transaction_reference) ? $requestTransfer['data']->data->transfer_request->unique_transaction_reference : "";
                                                DB::select("CALL OrderStatusProcessedUpdate('".$Order->order_ref_id."', $Order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                                $results = DB::select('select @json as json');
                                                $response = json_decode($results[0]->json, true);
                                                    if($response['status'] == '1') {
                                                        TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'processed');
                                                    }
                                            } else if (isset($requestTransfer['data']->success) &&
                                                ($requestTransfer['data']->success == true &&
                                                $requestTransfer['data']->data->transfer_request->status == 'accepted'))
                                            {
                                                DB::table('orders')
                                                    ->where(['user_id' => $Order->user_id, 'order_ref_id' => $Order->order_ref_id])
                                                    ->update(['payout_id' => $requestTransfer['data']->data->transfer_request->id]);
                                            }  else if (isset($requestTransfer['data']
                                                ->success) && $requestTransfer['data']
                                                ->success == false)
                                            {
                                                $errorMsg = isset($requestTransfer['data']->message) ? $requestTransfer['data']->message : "";
                                                $errorDesc = isset($requestTransfer['data']->data->transfer_request->failure_reason) ? $requestTransfer['data']->data->transfer_request->failure_reason : $errorMsg;
                                                $statusCode = '';
                                                if (str_contains($errorDesc, 'Insufficient account balance.')) {
                                                    $errorDesc = 'Something went wrong, please try after some time';
                                                }
                                                
                                                $txn = CommonHelper::getRandomString('txn', false);
                                                $utr = isset($requestTransfer['data']->data->transfer_request->id) ? $requestTransfer['data']->data->transfer_request->id : " ";
                                                $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                                DB::select("CALL OrderStatusUpdate('".$Order->order_ref_id."', $Order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                                $results = DB::select('select @json as json');
                                                $response = json_decode($results[0]->json, true);
                                                if($response['status'] == '1') {
                                                    TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'failed');
                                                }
                                            } else if (isset($requestTransfer['data']
                                                    ->failure)) {
                                                $errorDesc = isset($requestTransfer['data']->message) ? $requestTransfer['data']->message : "NA";
                                                $statusCode = '';
                                                if (str_contains($errorDesc, 'Insufficient account balance.')) {
                                                    $errorDesc = 'Something went wrong, please try after some time';
                                                }
                                                $txn = CommonHelper::getRandomString('txn', false);
                                                $utr = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";
                                                $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                                DB::select("CALL OrderStatusUpdate('".$Order->order_ref_id."', $Order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                                $results = DB::select('select @json as json');
                                                $response = json_decode($results[0]->json, true);
                                                if($response['status'] == '1') {
                                                    TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'failed');
                                                }
                                            }
                                        }
                            } else{
                                $messages = 'Order already processed';
                                Storage::disk('local')->append($fileName, 'balanceDebitFailure => '.$lockeOrder['message'].' '.date('H:i:s'));
                            }
                        break;
                        case 'instantpay':

                            $orderCount += 1;
                            //$lockeOrder['status'] = false;
                            $lockeOrder = TransactionHelper::moveOrderToProcessingByOrderId($Order->user_id, $Order->order_ref_id, $integrationId);
                            if($lockeOrder['status'] && isset($Order)) {
                          
                                $failedArray =['RPI', 'UAD', 'IAC', 'IAT', 'AAB', 'IAB', 'ISP', 'DID', 'DTX', 'IAN', 'IRA', 'DTB', 'RBT', 'SPE', 'SPD', 'UED', 'IEC', 'IRT', 'ITI', 'TSU', 'IPE', 'ISE', 'TRP', 'OUI', 'ODI', 'TDE', 'DLS', 'RNF', 'RAR', 'IVC',
                                        'IUA', 'SNA', 'ERR', 'FAB', 'UFC', 'OLR' , 'EOP', 'ONV', 'RAB'];
                                $instantPay = new InstantPayHelper;
                                $requestTransfer = $instantPay->instantpayTransfer($Order);
                                $orderStatus = 'pending';
                                if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                                    DB::table('orders')
                                    ->where(['user_id' => $Order->user_id, 'order_ref_id' => $Order->order_ref_id ])
                                    ->update(['cron_date' => date('Y-m-d H:i:s')]);

                                    if (isset($requestTransfer['data']->statuscode) && $requestTransfer['data']->statuscode == 'TXN') {

                                                $message = $requestTransfer['data']->status;
                                                $statusCode = 200;
                                                $bank_reference = isset($requestTransfer['data']->data->txnReferenceId) ? $requestTransfer['data']->data->txnReferenceId : "";
                                                if ($bank_reference != '00' && $bank_reference != '') {
                                                    DB::select("CALL OrderStatusProcessedUpdate('".$Order->order_ref_id."', $Order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                                    $results = DB::select('select @json as json');
                                                    $response = json_decode($results[0]->json, true);
                                                        if($response['status'] == '1') {
                                                            TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'processed');
                                                        }
                                                }
                                    } else if (isset($requestTransfer['data']->statuscode) && ($requestTransfer['data']->statuscode == 'TUP'))
                                            {
                                                $refId = @$requestTransfer['data']->data->externalRef;
                                                DB::table('orders')
                                                    ->where(['user_id' => $Order->user_id, 'order_ref_id' => $Order->order_ref_id ])
                                                    ->update(['payout_id' => $refId, 'cron_date' => date('Y-m-d H:i:s')]);
                                            }  else if (isset($requestTransfer['data']
                                                ->statuscode) && in_array($requestTransfer['data']
                                                ->statuscode, $failedArray))
                                            {
                                                $errorMsg = $errorDesc= isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : "";
                                                $statusCode = '';
                                                if (in_array($requestTransfer['data']
                                                ->statuscode, ['IAB'])) {
                                                    $errorDesc = 'Something went wrong, please try after some time';
                                                }

                                                $txn = CommonHelper::getRandomString('txn', false);
                                                $utr = @$requestTransfer['data']->data->txnReferenceId;
                                                $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                                DB::select("CALL OrderStatusUpdate('".$Order->order_ref_id."', $Order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                                $results = DB::select('select @json as json');
                                                $response = json_decode($results[0]->json, true);
                                                if($response['status'] == '1') {
                                                    TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'failed');
                                        }
                                    }
                                }
                            } else{
                                $messages = 'Order already processed';
                                Storage::disk('local')->append($fileName, 'balanceDebitFailure => '.$lockeOrder['message'].' '.date('H:i:s'));
                            }
                        break;

                        case 'razorpay':
                                $successArray = array('processed');
                                $pendingArray = array('pending', 'processing');
                                $failedArray = array('rejected', 'cancelled');
                                $reversedArray = array('reversed');
                                $orderCount += 1;
                                //   $lockeOrder['status'] = false;
                                $lockeOrder = TransactionHelper::moveOrderToProcessingByOrderId($Order->user_id, $Order->order_ref_id, $integrationId);
                                if ($lockeOrder['status'] && isset($Order)) {
                                    $razorpay = new RazorpayHelper;
                                    $requestTransfer = $razorpay->razorpayCompositePayout($Order);
                                    $orderStatus = 'pending';
                                    //  Storage::disk('local')->append($fileName, 'step 7 status => pending'.' '.date('H:i:s'));
                                    if (isset($requestTransfer['data']) && $requestTransfer['data'] != null)
                                    {
                                    //  Storage::disk('local')->append($fileName, 'step 8 code => sub_code');
                                        $fundAccountId = isset($requestTransfer['data']->id) ? $requestTransfer['data']->id : "";
                                        self::orderPayoutId($Order->order_ref_id, $Order->user_id, $fundAccountId);
                                        if (isset($requestTransfer['data']
                                                            ->status))
                                        {
                                        //    Storage::disk('local')->append($fileName, 'step 9 message => '.$requestTransfer['data']->message.' '.date('H:i:s'));
                                            $errorDesc = isset($requestTransfer['data']->error->reason) ? $requestTransfer['data']->error->reason : " No error message found";
                                            if (in_array($requestTransfer['data']
                                            ->status, $successArray)) {
                                                $message = 'Order processed successfully.';
                                                $statusCode = '000';
                                                $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : "";
                                                DB::select("CALL OrderStatusProcessedUpdate('".$Order->order_ref_id."', $Order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                                $results = DB::select('select @json as json');
                                                $response = json_decode($results[0]->json, true);
                                                if($response['status'] == '1') {
                                                    TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'processed');
                                                }
            
                                            }
                                            if (in_array($requestTransfer['data']
                                            ->status, $failedArray)) {
            
                                                $errorDesc =  isset($requestTransfer['data']->error->reason) ? $requestTransfer['data']->error->reason : $requestTransfer['data']->error->description;
                                                $statusCode = '003';
                                                if (str_contains($errorDesc, 'Not enough available')) {
                                                    $errorDesc = 'Something went wrong, please try after some time';
                                                }
                                                $txn = CommonHelper::getRandomString('txn', false);
                                                $utr = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : "";
                                                $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                                DB::select("CALL OrderStatusUpdate('".$Order->order_ref_id."', $Order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                                $results = DB::select('select @json as json');
                                                $response = json_decode($results[0]->json, true);
                                                if($response['status'] == '1') {
                                                    TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'failed');
                                                }

                                            }
                                        }else {
                                            if (isset($requestTransfer['data']->error->code) && in_array($requestTransfer['data']->error->code, array('BAD_REQUEST_ERROR'))) {
            
                                            $errorDesc =  isset($requestTransfer['data']->error->description) ? $requestTransfer['data']->error->description : '';
                                            $statusCode = '003';
                                            if (str_contains($errorDesc, 'Not enough available')) {
                                                $errorDesc = 'Something went wrong, please try after some time';
                                            }
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : "";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$Order->order_ref_id."', $Order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1') {
                                                TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'failed');
                                            }
                                        }
                                    }
                                                 //   Storage::disk('local')->append($fileName, 'step 10 orderClose '.date('H:i:s'));
                                }
                            } else{
                                $messages = 'Order already processed';
                                    //  Storage::disk('local')->append($fileName, 'balanceDebitFailure => '.$lockeOrder['message'].' '.date('H:i:s'));
                            }
                        break;
                    }
                }
               //code...
        } catch (\Exception  $e) {
            Storage::disk('local')->append($fileName, $e.date('H:i:s'));
        }

    }

    public static function orderPayoutId($orderRefId, $userId, $payoutId)
    {
        if (isset($orderRefId) && isset($orderRefId) && isset($payoutId)) {
            DB::table('orders')
                ->where(['order_ref_id' => $orderRefId, 'user_id' => $userId])
                ->update(['payout_id' => $payoutId]);
            return true;
        } else {
            return true;
        }
    }
    /*  public function middleware()
    {
        return [(new WithoutOverlapping($this->userId))->releaseAfter(rand(1, 30))];
    }
    */
    public function retryUntil()
    {
        return now()->addHours(10);
    }
}