<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validations\OrderValidation as Validations;
use App\Models\ProductCommission;
use Illuminate\Pagination\Paginator;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Contact;
use App\Helpers\ResponseHelper;
use App\Jobs\OrderCancelEmail;
use App\Events\OrderFailed;
use App\Events\OrderReversed;
use App\Events\OrderSuccess;
use App\Helpers\TransactionHelper;
use Cashfree;
use App\Helpers\CommonHelper;
use App\Helpers\HashHelper;
use App\Helpers\Chypierpay;
use App\Models\GlobalConfig;
use App\Models\UserConfig;
use DB;
use Storage;
use Carbon\Carbon;
use DateTime;
use App\Helpers\ATMPayHelper;
use App\Helpers\IBRPayHelper;
use App\Helpers\EKOPayHelper;
use App\Models\BulkPayout;
use App\Models\BulkPayoutDetail;

class OrderController extends Controller
{


    public function initiateOrder()
    {
        
       /* $reqData = array(
          "mode"=>"IMPS", //strtoupper($post->mode),
          "remarks"=> "Vendor Payout",
          "amount"=> 10,
          "type"=> "vendor",
          "bene_name"=> 'Sanjay Yadav'??$user->name,
          "bene_mobile"=> '8726241799',//$user->mobile,
          "bene_email"=> 'sy1904711@gmail.com',//$user->email,
          "bene_acc"=> '001891800042263',//$post->account,
          "bene_ifsc"=> 'YESB0000018',//strtoupper($post->ifsc),
          "bene_acc_type"=> "Saving",
          "refid"=> '345345345',
          "bene_bank_name"=> 'Yes Bank'//$post->bank//$post->bank
            
        );
        $request = array(
            "method" => "POST",
            "url" => "pay/singlepayout",
            "parameter" => $reqData
        );
        $chypierpay = new Chypierpay();
        $res = $chypierpay->hit($request);
        $output = json_encode($res);
        $response = json_decode($output);*/
    }

    public function index(Request $request)
    {

        $header = $request->header();
        $userSaltKey = CommonHelper::getUserSalt($request["auth_data"]['user_id']);

        //making hash
        $hash = HashHelper::init()->generate(HashHelper::FETCH_ALL_ORDERS, $header['php-auth-user'][0], $userSaltKey);

        //Storage::put('orderSignature'.time().'.txt', print_r($hash, true));
        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';
        $aaray = array('user_id' => $request["auth_data"]['user_id'], 'xettle' => $hash, 'client' => $signature);
        //Storage::put('orderSignature'.$request["auth_data"]['user_id'].'_'.time().'.txt', print_r($aaray, true));

        //match signature
        if (!hash_equals($hash, $signature)) {
            return ResponseHelper::failed('Your signature is invalid.');
        }


        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];

        $orders = DB::table('orders')->select('orders.order_ref_id as orderRefId', 'orders.client_ref_id as clientRefId', 'contacts.contact_id as contactId', 'orders.bank_reference as bankReference', 'contacts.first_name as firstName', 'contacts.last_name as lastName', 'contacts.email as email', 'contacts.phone as phone', 'contacts.account_type as accountType', 'contacts.account_number as accountNumber', 'contacts.account_ifsc as ifsc', 'vpa_address as vpaAddress', 'card_number as cardNumber', 'orders.amount as amount', 'orders.purpose as purpose', 'orders.currency as currency', 'orders.mode as mode', 'orders.narration as narration', 'orders.remark as remark', 'orders.udf1 as udf1', 'orders.udf2 as udf2', 'orders.status as status', 'orders.created_at as created_at')
            ->leftJoin('contacts', 'orders.contact_id', 'contacts.contact_id')
            ->where('orders.client_ref_id', '!=', null)
            ->where('orders.user_id', $userId)
            ->orderBy('orders.id', 'DESC');
        if (isset($request->offset) && isset($request->limit)) {
            $orders->offset($request->offset);
            $orders->limit($request->limit);
        }
        $orders = $orders->get();
        foreach ($orders as $key => &$value) {
            unset($value->bulk_payout_detail, $value->contact, $value->user);
            if ($value->accountType == 'card') {
                unset($value->accountNumber, $value->ifsc, $value->vpaAddress);
            }
            if ($value->accountType == 'vpa') {
                unset($value->accountNumber, $value->ifsc, $value->cardNumber);
            }
            if ($value->accountType == 'bank_account') {
                unset($value->vpaAddress, $value->cardNumber);
            }
        }
        if (!$orders->isEmpty()) {
            return ResponseHelper::success('Record fetched successfully.', $orders);
        } else {
            return ResponseHelper::failed('No orders found. Its never to late to start adding one.', []);
        }
    }

    public function fetchById(Request $request, $orderId)
    {

        $userId = $request["auth_data"]['user_id'];
       
        $orders = DB::table('orders')
            ->where('orders.order_ref_id', $orderId)
            ->orWhere('orders.client_ref_id', $orderId)
            ->where('orders.user_id', $userId)->first();
            
        if (isset($orders)) {
            $order = DB::table('orders')
                ->where('orders.order_ref_id', $orderId)
                ->orWhere('orders.client_ref_id', $orderId)
                ->where('orders.user_id', $userId)->first();
            $data = [
                "order_id"=>$order->client_ref_id,
                "status"=>$order->status,
            ];  
            
            if ($orders->status == '3') {
                return ResponseHelper::success('Record fetched successfully.', $data);
            } elseif ($orders->status == '6') {
                return ResponseHelper::failed('Record is failed.', $data);
            } elseif ($orders->status == '2') {
                return ResponseHelper::pending('Record is processing successfully.', $data);
            } else {
                return ResponseHelper::success('Record fetched successfully.', $data);
            }
        } else {
            return ResponseHelper::failed('No orders found.', []);
        }
    }

    public function store(Request $request)
    {
        $header = $request->header();
        $userSaltKey = CommonHelper::getUserSalt($request["auth_data"]['user_id']);
        //making hash
        $hash = HashHelper::init()->generate(HashHelper::CREATE_ORDER, $header['php-auth-user'][0], $userSaltKey, $request->all());
        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';
        $aaray = array('user_id' => $request["auth_data"]['user_id'], 'xettle' => $hash, 'client' => $signature);


        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];

        $validation = new Validations($request);
        $validator = $validation->addOrder();
        $validator->after(function ($validator) use ($request, $userId, $hash, $signature) {

            //match signature
            if (strpos($request->amount, ".") !== false) {
                if (strlen(strrchr($request->amount, '.')) - 1 > 2) {
                    $validator->errors()->add('amount', 'Only 2 decimal except in amount');
                }
            }
            if (hash_equals($hash, $signature)) {
                $validator->errors()->add('signature', 'Your signature is invalid.');
            } else {
                $User = User::where('id', $userId)->where('is_active', '1')->first();
                if (empty($User)) {
                    $validator->errors()->add('userId', 'User Account disabled');
                } else {
                    $isAvailable = DB::table('user_services')
                        ->where(['user_id' => $userId, 'service_id' => PAYOUT_SERVICE_ID])
                        ->select('is_active', 'transaction_amount')->first();
                    if (isset($isAvailable) && $isAvailable->is_active == '1') {

                        $totalAmount =  $request->amount;
                        if ($totalAmount >= $isAvailable->transaction_amount) {
                            $validator->errors()->add('amount', 'Insufficient funds.');
                        }
                    } else {
                        $validator->errors()->add('userId', 'Your payout service is disabled.');
                    }
                }
                $mode = CommonHelper::case($request->mode, 'l');    
                $Order = Order::where('client_ref_id', $request->clientRefId)->count();
                if ($Order) {
                    $validator->errors()->add('clientRefId', 'Client Ref Id all ready exists.');
                }
            }
        });
        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return response()->json([
                'statuscode' => 400,
                'success' => false,
                'data' => null,
                'message' => "Validation failed",
                'errors' => $message,
                'exception' => null,
            ]);
            // return ResponseHelper::missing($message);
        } else {

            $UConfig = UserConfig::where(['user_id' => $userId, 'api_integration_id' => 'int_1654155017'])
                ->count();
            if ($UConfig == 1) {
                $GlobalConfig = GlobalConfig::select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4')
                    ->where(['slug' => 'partner_account_balance', 'attribute_4' => '1'])
                    ->first();
                if (isset($GlobalConfig) && !empty($GlobalConfig)) {
                    if ($GlobalConfig->attribute_3 < $request->amount) {
                        return ResponseHelper::failed('E40321:Something went wrong, please try after some time.');
                    }
                }
            }

            $data = $request->all();
            $getProductId = CommonHelper::getProductId($data['mode'], 'payout');
            $productId = '';
            $serviceId = '';
            if ($getProductId) {
                $productId = $getProductId->product_id;
                $serviceId = $getProductId->service_id;
            }
            $getProductConfig = TransactionHelper::getProductConfig($data['mode'], $serviceId);
            
            if ($getProductConfig['status']) {
                if ($getProductConfig['data']['min_order_value'] <= $data['amount'] && $getProductConfig['data']['max_order_value'] >= $data['amount']) {
                    // Get Total Amount Fee and Tax Amount
                    $orderLastWitoutProcess = DB::table('orders')->select('created_at')
                        ->where('user_id', $userId)
                        ->orderBy('id', 'desc')->first();
                    $orderRefId = CommonHelper::getRandomString('REF', false,1);
                    $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, $data['amount'], $userId);
                    $header = $request->header();

                    $data['agent']['ip'] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
                    $data['agent']['userAgent'] = isset($header["user-agent"][0]) ? $header["user-agent"][0] : "";
                    $data['charges'] = $getFeesAndTaxes;

                    $orderCreate = TransactionHelper::createTransactionAndOrder($orderRefId, $userId, $serviceId, $productId, $data);
               
                    if ($orderCreate['status']) {
                        dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($orderRefId, $userId, 'balance_debit', '', '', '', '', ''))->onQueue('payout_debit_queue');
                       
                        $orderInfo = ['clientrefid' => $orderRefId, 'status' => '1'];
                        return ResponseHelper::success('Order accepted successfully', $orderInfo, '200');
                    } else {
                        return ResponseHelper::failed($orderCreate['message'], []);
                    }
                } else {
                    $checkAndLock['message'] = $data['amount'] . ' Provided amount is not in range for ' . $data['mode'] . ' Transaction';
                    return ResponseHelper::failed($checkAndLock['message'], []);
                }
            } else {
                $checkAndLock['message'] = $getProductConfig['message'];
                return ResponseHelper::failed($checkAndLock['message'], []);
            }
        }
    }


    /**
     * Create Order
     *
     * @param array $orderArray
     * @param [type] $userId
     * @param [type] $contactId
     * @param [type] $productId
     * @param [type] $integrationId
     * @param [type] $payout_reference_id
     * @return void
     */
    public static function createOrder($orderArray = [], $userId = '', $contactId = '', $productId = '', $integrationId = '', $payout_reference_id = '', $serviceId = '')
    {
        $getCharges = TransactionHelper::getFeesAndTaxes($productId, $orderArray['amount'], $userId);

        $order = new Order;
        $order->order_id = CommonHelper::getRandomString('ord');
        $order->contact_id = $contactId;
        $order->product_id = $productId;
        $order->service_id = $serviceId;
        $order->client_ref_id = $orderArray['clientRefId'];
        $order->integration_id = null;
        $order->user_id = $userId;
        $order->batch_id = 'NA';
        $order->order_ref_id = $payout_reference_id;
        $order->currency = 'INR';
        $order->amount = $orderArray['amount'];
        $order->fee = $getCharges['fee'];
        $order->tax = $getCharges['tax'];
        $order->mode = CommonHelper::case($orderArray['mode'], 'u');
        $order->purpose = CommonHelper::case($orderArray['purpose'], 'u');
        $order->narration = null;
        $order->remark =  isset($orderArray['remark']) ? $orderArray['remark'] : 'NA';
        $order->txt_3 = $getCharges['margin'];
        $order->ip = $orderArray['ip'];
        $order->area =  $orderArray['area'];
        $order->user_agent =  $orderArray['userAgent'];
        $order->status = 'queued';
        $data = $order->save();
        if ($data) {
            $response['status'] = true;
            $response['message'] = 'Order created';
            $response['data'] = $order;
        } else {
            $response['status'] = false;
            $response['message'] = 'DB action failed';
        }
        return $response;
    }

    public static function columnSelectResponse($ordersStatus, $accountType)
    {
        $selectingColumn = array('orders.order_ref_id as orderRefId', 'orders.client_ref_id as clientRefId', 'contacts.contact_id as contactId', 'contacts.first_name as firstName', 'contacts.last_name as lastName', 'contacts.email as email', 'contacts.phone as phone', 'contacts.account_type as accountType', 'orders.amount as amount', 'orders.purpose as purpose', 'orders.currency as currency', 'orders.mode as mode', 'orders.narration as narration', 'orders.remark as remark', 'orders.udf1', 'orders.udf2', 'orders.status as status', 'orders.created_at as createdAt');
        if ($ordersStatus == 'processed') {
            array_push($selectingColumn, 'orders.bank_reference as bankReference');
        } elseif ($ordersStatus == 'failed') {
            array_push($selectingColumn, 'orders.failed_message as failedMessage');
        } elseif ($ordersStatus == 'cancelled') {
            array_push($selectingColumn, 'orders.cancellation_reason as cancellationReason', 'orders.cancelled_at as cancelledAt');
        } elseif ($ordersStatus == 'reversed') {
            array_push($selectingColumn, 'orders.bank_reference as bankReference');
        }

        if ($accountType == 'vpa') {
            array_push($selectingColumn, 'vpa_address as vpa');
            return $selectingColumn;
        } elseif ($accountType == 'card') {
            array_push($selectingColumn, 'card_number as cardNumber');
            return $selectingColumn;
        } else {
            array_push($selectingColumn, 'account_number as accountNumber', 'account_ifsc as ifsc');
            return $selectingColumn;
        }
    }
    
    
    public static function orderUpdate(){
        $GlobalConfig = DB::table('global_config')
            ->select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4')
            ->where(['slug' => 'processing_order_count'])
            ->first();
            $offset = 0;
            $limit = 50;
            $time = 30;
            $hourseOrMinutes = 1;
            if (isset($GlobalConfig)) {
                $offset = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
                $limit = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 50;
                $time = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 30;
                $hourseOrMinutes = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : 1;
            }
            if ($hourseOrMinutes == 2) {
                $times =  Carbon::now()->subHours($time);
            } else {
                $times =  Carbon::now()->subMinutes($time);
            }
            $orders = Order::where('orders.status', 'processing')
            ->where('orders.cron_status', '1')
            //->where('orders.area', '11')
            // ->where('created_at', '<',  $times)
            ->where('orders.user_id', 573)
            ->whereIn('orders.id', [2466])
            ->where('orders.order_id', '=', NULL)
            ->orderBy('orders.id', 'asc')
            ->take($limit)
            ->get();
            $i = 0;
            $fileName = 'easeOrderProcessingJobs'.$times.'.txt';
            print_r($orders);exit;
        foreach ($orders as $order)
        {
            $i++;
            DB::table('orders')->where(['order_ref_id' => $order->order_ref_id, 'user_id' => $order->user_id])
            ->update([
                'txt_2' => 1
            ]);
            Storage::disk('local')->put($fileName, 'processing order : '.$order->order_ref_id);
            $fileName = 'easeOrderProcessingJobs'.$order->order_ref_id.'.txt';
           // Storage::disk('local')->put($fileName, 'processing order : '.$this->orderRefId);
            $order = Order::select('*')->where('status', 'processing')
                ->where('orders.cron_status', '1')
                //->where('orders.area', '11')
                ->where('orders.order_id', '=', NULL)
                ->where('orders.user_id', $order->user_id)
                ->where('orders.order_ref_id', $order->order_ref_id)
                ->first();
                if(isset($order)) {
                    Storage::disk('local')->append($fileName, ' order data : '.$order->integration_id.' '.$order->area);

                    $route = DB::table('integrations')
                        ->select('slug')
                        ->where('integration_id', $order->integration_id)
                        ->first();
                    $types = isset($route->slug) ? $route->slug : "NA";

                    Storage::disk('local')->append($fileName, 'type : '.$types);
                    switch ($types)
                    {
                        case 'atmaadhaar':
                            $atmPay = new ATMPayHelper;
                            if (isset($order->cron_date) && !empty($order->cron_date)) {
                                $requestTransfer = $atmPay->atmpayTransferStatus($order->order_ref_id, $order->cron_date, $order->user_id);
                                if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                                    $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : "";
                                //   Storage::disk('local')->append($fileName, 'easebuzz status : '.$errorDesc);
                                    $bank_reference = "";
                                    $status = "pending";

                                    $failedArray =['TXF','TNF', 'ERR', 'TRP'];
                                    if (isset($requestTransfer['data']) &&
                                                ($requestTransfer['data']->statuscode == 'TXN' && $requestTransfer['data']->status == 'success' && $requestTransfer['data']->payid != '00'))
                                            {
                                            $message = @$requestTransfer['data']->status;
                                            $statusCode = 200;
                                            $status = "processed";
                                            $bank_reference  = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : "";
                                            DB::select("CALL OrderStatusProcessedUpdate('".$order->order_ref_id."', $order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                            }
                                        }else if(isset($requestTransfer['data']
                                        ->statuscode) && in_array($requestTransfer['data']
                                        ->statuscode, $failedArray)) {
                                            $status = "failed";
                                            $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : $errorDesc;
                                            $statusCode = $requestTransfer['data']->statuscode;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : " ";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                            }
                                        } else if(isset($requestTransfer['data']
                                        ->status) && in_array($requestTransfer['data']
                                        ->status, ['reversed'])) {
                                            $status = "failed";
                                            $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : $errorDesc;
                                            $statusCode = $requestTransfer['data']->statuscode;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : " ";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                            }
                                        } else if(isset($requestTransfer['data']
                                                ->statuscode) && in_array($requestTransfer['data']
                                                ->statuscode, ['ERR']) && ($requestTransfer['data']
                                                ->utr == null || $requestTransfer['data']
                                                ->utr == '00')) {
                                                    $status = "failed";
                                                    $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : $errorDesc;
                                                    $statusCode = $requestTransfer['data']->statuscode;
                                                    $txn = CommonHelper::getRandomString('txn', false);
                                                    $utr = $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : " ";
                                                    $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                                    DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                                    $results = DB::select('select @json as json');
                                                    $response = json_decode($results[0]->json, true);
                                                    if($response['status'] == '1' && $order->area == '11') {
                                                        TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                                    }
                                                }
                                        if ($order->area == '00') {
                                            BulkPayoutDetail::payStatusUpdate($order->batch_id, $status, $order->order_ref_id, $errorDesc, $bank_reference);
                                            BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
                                        }
                                }
                            }
                        break;
                    case 'ibrpaypayout':
                            $ibrPay = new IBRPayHelper;
                            if (isset($order->cron_date) && !empty($order->cron_date)) {
                                $requestTransfer = $ibrPay->ibrTransferStatus($order->order_ref_id, $order->cron_date, $order->user_id);
                                if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                                    $errorDesc = $requestTransfer['data']->mess;
                                //   Storage::disk('local')->append($fileName, 'easebuzz status : '.$errorDesc);
                                    $bank_reference = "";
                                    $status = "pending";
                                    $successArray = array('TXN');
                                    $failedArray =['`ERR`'];
                                    if (in_array($requestTransfer['data']->code, $successArray) && isset($requestTransfer['data']->data) && !empty($requestTransfer['data']->data) && isset($requestTransfer['data']->data->Status) && $requestTransfer['data']->data->Status == "Success") {
                                        $message = $requestTransfer['data']->data->Message;
                                            $statusCode = 200;
                                            $status = "processed";
                                            $bank_reference = isset($requestTransfer['data']->data->RRN) ? $requestTransfer['data']->data->RRN : "";
                                            DB::select("CALL OrderStatusProcessedUpdate('".$order->order_ref_id."', $order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                            }
                                        }else if(in_array($requestTransfer['data']->code, $failedArray) && $requestTransfer['data']->status == "failed") {
                                            $status = "failed";
                                            $errorDesc = ($errorDesc) ? $errorDesc : $requestTransfer['data']->status;
                                            $statusCode = $requestTransfer['data']->code;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = isset($requestTransfer['data']->data->RRN) ? $requestTransfer['data']->data->RRN : " ";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                            }
                                        } else if(in_array($requestTransfer['data']->code, $successArray) && (strtolower($requestTransfer['data']->data->Status) == "failed" || strtolower($requestTransfer['data']->data->Status) == "refund")) {
                                            $status = "failed";
                                            $errorDesc = ($errorDesc) ? $errorDesc : $requestTransfer['data']->status;
                                            $statusCode = $requestTransfer['data']->code;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = isset($requestTransfer['data']->data->RRN) ? $requestTransfer['data']->data->RRN : " ";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                            }
                                        } 
                                        if ($order->area == '00') {
                                            BulkPayoutDetail::payStatusUpdate($order->batch_id, $status, $order->order_ref_id, $errorDesc, $bank_reference);
                                            BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
                                        }
                                }
                            }
                        break;
                    case 'ekopay':
                            $ekoPay = new EKOPayHelper;
                            if (isset($order->cron_date) && !empty($order->cron_date)) {
                                $requestTransfer = $ekoPay->ekopayTransferStatus($order->order_ref_id, $order->cron_date, $order->user_id);
                                if ($requestTransfer['data'] && isset($requestTransfer['data']->status) && $requestTransfer['data']->status == 0 && $requestTransfer['data']->response_status_id == 0){
                                    $errorDesc = $requestTransfer['data']->data->txstatus_desc;
                                //   Storage::disk('local')->append($fileName, 'easebuzz status : '.$errorDesc);
                                    $bank_reference = "";
                                    $status = "pending";
                                    $successArray = array('TXN');
                                    $failedArray =['ERR'];
                                    $statusArray = ['SUCCESS','FAILED','INITIATED','REFUNDPENDING','REFUNDED','HOLD'];
                                    $eventStatus = isset($statusArray[$requestTransfer['data']->data->tx_status]) ? $statusArray[$requestTransfer['data']->data->tx_status] : "";
                                    if (isset($requestTransfer['data']->data->tx_status) && $requestTransfer['data']->data->tx_status == 0 ) {
                                        $message = $requestTransfer['data']->message;
                                        $statusCode = 200;
                                        $status = "processed";
                                        $bank_reference = isset($requestTransfer['data']->data->bank_ref_num) ? $requestTransfer['data']->data->bank_ref_num : "";
                                        DB::select("CALL OrderStatusProcessedUpdate('".$order->order_ref_id."', $order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                        }
                                    }else if($order->status == 'processing' && $eventStatus == 'REFUNDED' || $eventStatus == 'FAILED') {
                                        $status = "failed";
                                        $errorDesc = ($errorDesc) ? $errorDesc : $requestTransfer['data']->data->txstatus_desc;
                                        $statusCode = 200;
                                        $txn = CommonHelper::getRandomString('txn', false);
                                        $utr = isset($requestTransfer['data']->data->bank_ref_num) ? $requestTransfer['data']->data->bank_ref_num : " ";
                                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                        DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                        }
                                    }else if($order->status == 'processed' && $eventStatus == 'REFUNDED') {
                                        $status = "failed";
                                        $errorDesc = ($errorDesc) ? $errorDesc : $requestTransfer['data']->data->txstatus_desc;
                                        $statusCode = 200;
                                        $txn = CommonHelper::getRandomString('txn', false);
                                        $utr = isset($requestTransfer['data']->data->bank_ref_num) ? $requestTransfer['data']->data->bank_ref_num : " ";
                                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                        DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'reversed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                        }
                                    }
                                    if ($order->area == '00') {
                                        BulkPayoutDetail::payStatusUpdate($order->batch_id, $status, $order->order_ref_id, $errorDesc, $bank_reference);
                                        BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
                                    }
                                }
                            }
                        break;
                }
            }
        }

        if ($i == 0) {
            $message = "No record found";
        } else {
            $message =  $i." Records updated successfully.";
        }
        echo $message;
    }
}