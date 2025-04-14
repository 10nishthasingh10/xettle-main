<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Integration;
use App\Models\Contact;
use App\Models\Product;
use App\Models\BulkPayoutDetail;
use App\Models\BulkPayout;
use App\Notifications\SMSNotifications;
use DataTables;
use Auth;
use Cashfree;
use CommonHelper;
use ExportExcelHelper;
use Transaction as TransactionHelper;
use App\Models\ProductCommission;
use App\Models\User;
use App\Models\UserService;
use Validations\OrderValidation as Validations;
use Yajra\DataTables\Html\Builder;
Use Carbon\Carbon;
use Mail;
use DateTime;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,Builder $builder)
    {

        $data['page_title'] =  "Orders Listing";
        $data['site_title'] =  "Orders";
        $data['view']       = USER.'/'.PAYOUT.".order.list";
        $data['integrations']       = Integration::where('is_active','1')->get();
        $data['products']       = Product::where('is_active','1')->get();
        $qrString = \Request::getRequestUri();
        $getValueBatchId = "";
        if(isset($qrString) && str_contains($qrString, '?')) {
            $getArray = explode("?",$qrString);
            if(isset($getArray[1])) {
                $getArray1 = explode("=",$getArray[1]);
                if(isset($getArray1[1])) {
                    $getValueBatchId = $getArray1[1];
                }
            }
        }
        $data['batchId']       = $getValueBatchId;
        $id = 0;
        return view($data['view'],compact('id'))->with($data);
    }

    public function exportOrder()
    {
        $filename=time().'.xlsx';

        $table_name = \App\Models\Order::query()->select('contact_first_name','contact_last_name','contact_email','contact_phone','contact_type','contacts.account_type',
        'contacts.account_number','contacts.account_ifsc','bulk_payout_details.account_vpa','payout_mode','payout_amount','fee','tax','payout_reference','bulk_payout_details.order_ref_id','bulk_payout_details.bank_reference','payout_purpose',
        'payout_narration','orders.status')
        ->leftJoin('bulk_payout_details','bulk_payout_details.order_ref_id','orders.order_ref_id')
        ->leftJoin('contacts','contacts.contact_id','orders.contact_id');

        if(isset($_GET['contact_id']) && !empty($_GET['contact_id'])){
            $table_name->where('orders.contact_id',$_GET['contact_id']);
        }
        if(isset($_GET['order_ref_id']) && !empty($_GET['order_ref_id'])){
            $table_name->where('bulk_payout_details.order_ref_id',$_GET['order_ref_id']);
        }
        if(isset($_GET['batch_id']) && !empty($_GET['batch_id'])){
            $table_name->where('orders.batch_id',$_GET['batch_id']);
        }
        if(isset($_GET['mode']) && !empty($_GET['mode'])){
            $table_name->where('bulk_payout_details.payout_mode',$_GET['mode']);
        }
        if(isset($_GET['payout_reference']) && !empty($_GET['payout_reference'])){
            $table_name->where('bulk_payout_details.payout_reference',$_GET['payout_reference']);
        }
        if(isset($_GET['status']) && !empty($_GET['status'])){
            $table_name->where('orders.status',$_GET['status']);
        }
        if(isset($_GET['from']) && !empty($_GET['from']) &&  isset($_GET['to'])  && !empty($_GET['from'])){
            $table_name->whereBetween('orders.created_at', [$_GET['from'].' 00:00:00', $_GET['to'].' 23:59:59']);
        }
        $heading = [
            'Contact First Name','Contact Last Name','Contact Email','Contact Phone','Contact Type','Account Type',
            'Account Number','Account ifsc','Account vpa','Payout Mode','Payout Amount','Fee','Tax','Payout Reference','Order Reference Id','Bank Reference',
            'Payout Purpose','Payout Narration','Status'
        ];

        return (new ExportExcelHelper($heading ,$table_name ))->download($filename);
    }

            /**
     * Undocumented function
     *
     * @param [type] $orderRefId
     * @param [type] $orderId
     * @return void
     */
    public function orderCancel(Request $request)
    {
        $validation=new Validations($request);
        $validator=$validation->orderCancel();
        $validator->after(function($validator) use ($request){
        $Order = Order::where(['user_id' => $request->userId,'order_ref_id' => $request->orderRefId])->first();
        if(empty($Order)){
            $validator->errors()->add('message','Order is not valid');
        }else{
            
            if($Order->status == 'processing' || $Order->status == 'processed' || $Order->status == 'cancelled' || $Order->status == 'failed'){
                $validator->errors()->add('message','Order is not valid for cancelled');
            }
            

        }
        });

        if($validator->fails()){
            $this->message=$validator->errors();
        }else{

            $Order = Order::where(['order_id' => $request->orderId,'order_ref_id' => $request->orderRefId])->first();
            $OrderCancelledCount = Order::where(['batch_id' => $Order->batch_id,'status' => 'cancelled'])->count();
            $OrderTotalCount = Order::where(['batch_id' => $Order->batch_id])->count();
            $Order->status = 'cancelled';
            $Order->cancellation_reason = $request->remarks;
            $Order->cancelled_at = date('Y-m-d H:i:s');
            if($Order->save()){
                BulkPayoutDetail::payStatusUpdate($Order->batch_id,'cancelled',$Order->order_ref_id ,'Order Cancelled' ,'');
                if($OrderTotalCount == ($OrderCancelledCount + 1)){
                    BulkPayout::updateStatusByBatchCancelOrder($Order->batch_id, array('status'=>'cancelled'));
                }else{
                    BulkPayout::updateStatusByBatchCancelOrder($Order->batch_id, array());
                }
            }
            $this->message = "Order Cancelled Successfully";
            $this->status   = true;
            $this->modal    = true;
            $this->alert    = true;
            $this->redirect = 'orders';
            return $this->populateresponse();
        }
        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => $this::FAILED_STATUS,
                'data'      => $this->message
            ])
        );
    }

    public function addOrder(Request $request)
    {

        $validation=new Validations($request);
		$validator=$validation->addWebOrderByAdmin();
        $validator->after(function ($validator) use ($request) {
            
            $userId = isset($request->user_id) ? $request->user_id : Auth::user()->id;
            $User = User::where('id', $userId)->first();
            if (empty($User)) {
                $validator->errors()->add('user_id', 'User account details not found.');
            } else {
               /* $Contact = Contact::where(['contact_id' => $request->contact_id, 'user_id' => $userId])->first();
                if (empty($Contact)) {
                    $validator->errors()->add('contactId', 'The contact id is invalid.');
                }*/
                $serviceDetails = UserService::where(['user_id' => $request->user_id, 'service_id' => PAYOUT_SERVICE_ID])->first();
                if (empty($serviceDetails)) {
                    $validator->errors()->add('contact_id', 'The user service not found.');
                } else {
                    if (isset($serviceDetails) && !empty($serviceDetails)) {
                        $totalAmount =  $request->amount;
                        if ($totalAmount >= $serviceDetails->transaction_amount) {
                            $validator->errors()->add('amount', 'Insufficient funds.');
                        }
                    } else {
                        $validator->errors()->add('userId', 'Your payout service is disabled.');
                    }
                }
            }

        });
		if($validator->fails()){
			$this->message = $validator->errors();
	    }else{
            $data = $request->all();
           /* $Contact = Contact::where(['contact_id' => $request->contact_id, 'user_id' =>  $data['user_id']])->first();
            if ($Contact->account_type == 'vpa') {
                $data['mode'] = 'upi';
            } else {
                $data['mode'] = 'imps';
            }*/
            $productId = '';
            $serviceId = '';
            $getProductId = CommonHelper::getProductId($data['mode'], 'payout');
            if ($getProductId) {
                $productId = $getProductId->product_id;
                $serviceId = $getProductId->service_id;
            }
            $data['accountNo'] =  $request->accountNo;
            /*dd(event(new OrderFailed(49, 'REF68690772111647DBE3', '', 'soredd', '')),event(new OrderSuccess(49, 'REF686904884838489AE5', '444', '', '','666','')),event(new OrderReversed(49, 'REF68690163025882E6CE', '', 'failed', '')));*/
            $getProductConfig = TransactionHelper::getProductConfig($data['mode'], $serviceId);
           
            if ($getProductConfig['status']) {
                if ($getProductConfig['data']['min_order_value'] <= $data['amount'] && $getProductConfig['data']['max_order_value'] >= $data['amount']) {
                    // Get Total Amount Fee and Tax Amount
                    $orderRefId = CommonHelper::getRandomString('REF', false);
                    $data['clientRefId'] =  CommonHelper::getRandomString('DREF', false);
                    $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, $data['amount'], $data['user_id']);
                    $header = $request->header();
                    $data['agent']['ip'] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
                    $data['agent']['userAgent'] = isset($header["user-agent"][0]) ? $header["user-agent"][0] : "";
                    $data['charges'] = $getFeesAndTaxes;
                  
                    $orderCreate = TransactionHelper::createTransactionAndOrder($orderRefId, $data['user_id'], $serviceId, $productId, $data);

                    if ($orderCreate['status']) {

                    //dispatch(new \App\Jobs\OrderProcessJob($orderRefId, $userId))->onQueue('payout_queue')->delay($time);
                     $dis = dispatch(new \App\Jobs\OrderProcessJob($orderRefId,$data['user_id']))
                     ->onQueue('payout_queue');
                     $loggedUserId = isset(Auth::user()->id) ? Auth::user()->id : "";
                        DB::table('orders')
                            ->where(['user_id' => $data['user_id'], 'order_ref_id' => $orderRefId])
                            ->update(['area' => '22', 'txt_1' => $loggedUserId]);
                        $orderInfo = ['clientRefId' => $data['clientRefId'], 'orderRefId' => $orderRefId, 'status' => 'queued'];
                        $this->message = "Order created successfully.";
                        $this->data = ['order' => $orderInfo];
                        $this->status = true;
                        return $this->populateresponse();
                    } else {
                        $this->message_object = true;
                        $this->message  = array('message' => "Order not process ");
                        $this->data = [];
                        $this->status = false;
                    }

                } else {
                    $this->message_object = true;
                    $this->message  = array('message' => "Order not process ");
                    $this->data = [];
                    $this->status = false;
                }
            } else {
                $this->message  = array('message' => "Order not process ");
                $this->status = false;
                $this->data = [];
                $this->message_object = true;
            }
            $this->status = false;
            $this->modal    = false;
            $this->alert    = false;
            $this->redirect = true;
            return $this->populateresponse();
        }

        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => false,
                'data'      => $this->message
            ])
        );
    }

}
