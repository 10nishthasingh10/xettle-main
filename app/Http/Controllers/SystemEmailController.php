<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\UPICallback;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\AutoCollectCallback;
use App\Models\GlobalConfig;
use DB;
use App\Jobs\SendTransactionEmailJob;
use App\Helpers\CommonHelper;
use App\Helpers\CashfreeHelper;

class SystemEmailController extends Controller
{

	public function SendEmailUpiCallback(Request $request)
	{
	    $user = User::select('id','name','email')->where('is_active','1')->where('is_admin','0')->get();
	    
	    foreach($user as $val)
	    {
	        
	        $upiCallbacks = UPICallback::select('upi_callbacks.*')->where(DB::raw('date_format(upi_callbacks.created_at,"%Y-%m-%d")'),date("Y-m-d"))->where('upi_callbacks.user_id',$val->id)
	                    ->orderBy('upi_callbacks.created_at', 'desc')->get();
	        if(!$upiCallbacks->isEmpty())
	        {
	            $val->upiCallbacks = $upiCallbacks;
	        }
	    }
	    
	    foreach($user as $value)
	    {
	        
	        if(isset($value->upiCallbacks)&&!$value->upiCallbacks->isEmpty())
	        {
	            dispatch(new SendTransactionEmailJob($value,'upiCallbacks'));
	        }
	        
	    }
	  
	    
	  
	    dd('done');
	}

	public function sendOrderEmail(Request $request)
	{
		$order = Order::with('user')->select('orders.*','users.name','users.email')->join('users','users.id','=','orders.user_id')->where(DB::raw('date_format(orders.created_at,"%Y-%m-%d")'),'=',date("Y-m-d"))->get();
		
		if(!$order->isEmpty())
		{
			foreach ($order as $key => $value) {
				// code...
				if($value->status=='failed')
				{
					dispatch(new SendTransactionEmailJob($value,'failedOrder'));
				}
				else if($value->status=='processed')
				{
					dispatch(new SendTransactionEmailJob($value,'processedOrder'));
				}else if($value->status=='reversed')
				{
					dispatch(new SendTransactionEmailJob($value,'failedOrder'));
				}
			}
		}
		dd('done');
	}

	public function sendUpiTransactionEmail(Request $request)
	{
		//DB::enableQueryLog();
		$transactionType = $request->transactionType;
		if($transactionType=='daily')
		{
			$data = [];
			$totalAmount = UPICallback::select(DB::raw('sum(amount) as totalAmount'))->where(DB::raw('date_format(txn_date,"%Y-%m-%d")'),date('Y-m-d'))->first();
		
			$transactionData = UPICallback::with('user')->where(DB::raw('date_format(txn_date,"%Y-%m-%d")'),date('Y-m-d'))->orderBy('amount','desc')->take(10)->get();
			//dd(DB::getQueryLog());
			//print_r($data);
			//echo '<pre>';
			$data['totalAmount'] = $totalAmount->totalAmount;
			$data['data'] = $transactionData;
			$data['email'] = '';
			dispatch(new SendTransactionEmailJob($data,'upiTransaction'));
		}
		

	}

	public function checkAccountBalance()
	{
		$cashfree_min_amount = CommonHelper::getGlobalConfig(array('attribute_1'),'cashfree_min_amount');
		//print_r($cashfree_min_amount->attribute_1);

		$Cashfree = new CashfreeHelper;
		$balance = $Cashfree->getBalance();
		print_r($balance['data']);
		print_r($balance['data']->data->balance);
		print_r($balance['data']->status);
		if($balance['data']->status=='SUCCESS')
		{
			if($balance['data']->data->balance <= $balance)
			{

				$data['email'] = '';
				$data['balance'] = $balance['data']->data->balance;
				$data['accountName'] = 'CashFree';
				$data['msg'] = ''; 
				dispatch(new SendTransactionEmailJob($data,'cashfreeBalance'));
			}
		}
		
	}

	public function sendDailyTransactionData()
	{
		$currDate = date('Y-m-d');
		$date =  date('Y-m-d',strtotime('-1 day'));
		$order = DB::connection('slave')->table('orders')->select(DB::raw('sum(amount) as totalAmount'),DB::raw("COUNT(id) AS totalCount"),DB::raw('sum(fee+tax) as totalFee'),DB::raw('sum(tax) as totalTax'))->where('status','processed')->where(DB::raw('date(created_at)'),$date)->first();

		$StackUpi = DB::connection('slave')->table('transactions')->select(DB::raw('sum(tr_amount) as totalAmount'),DB::raw("COUNT(id) AS totalCount"))->where(DB::raw('date(created_at)'),$date)->whereIn('tr_identifiers',['upi_inward_credit'])->first();
		$StackUpiFeeTax = DB::connection('slave')->table('transactions')->select(DB::raw("COUNT(id) AS totalCount"),DB::raw('sum(tr_fee+tr_tax) as totalFee'))->where(DB::raw('date(created_at)'),$date)->whereIn('tr_identifiers',['upi_inward_credit', 'upi_stack_vpa_fee', 'upi_stack_verify_fee'])->first();

		$smartCollect = DB::connection('slave')->table('transactions')->select(DB::raw('sum(tr_amount) as totalAmount'),DB::raw("COUNT(id) AS totalCount"))->where(DB::raw('date(created_at)'),$date)->whereIn('tr_identifiers',['smart_collect_vpa','smart_collect_van'])->first();
		$smartCollectFeeTax = DB::connection('slave')->table('transactions')->select(DB::raw("COUNT(id) AS totalCount"),DB::raw('sum(tr_fee+tr_tax) as totalFee'))->where(DB::raw('date(created_at)'),$date)->whereIn('tr_identifiers',['smart_collect_vpa','smart_collect_van','smart_collect_vpa_fee','smart_collect_van_fee'])->first();

		$userVan = DB::connection('slave')->table('transactions')->select(DB::raw('sum(tr_amount) as totalAmount'),DB::raw("COUNT(id) AS totalCount"),DB::raw('sum(tr_fee+tr_tax) as totalFee'),DB::raw('sum(tr_tax) as totalTax'))->where(DB::raw('date(created_at)'),$date)->whereIn('tr_identifiers',['van_inward_credit'])->first();
		$loadMoney = DB::connection('slave')->table('transactions')->select(DB::raw('sum(tr_amount) as totalAmount'),DB::raw("COUNT(id) AS totalCount"))->where(DB::raw('date(created_at)'),$date)->whereIn('tr_identifiers',['load_fund_credit'])->first();
		$aeps = DB::connection('slave')->table('aeps_transactions')->select(DB::raw('sum(transaction_amount) as totalAmount'),DB::raw('sum(commission) as totalCommission'),DB::raw("COUNT(id) AS totalCount"))->where(['status' =>  'success', 'transaction_type' => 'cw','is_trn_credited' => '1','is_trn_disputed' => '0'])->where(DB::raw('date(created_at)'),$date)->first();
		$autoSettlement = DB::connection('slave')->table('user_settlements')->select(DB::raw('sum(amount) as totalAmount'),DB::raw("COUNT(id) AS totalCount"),DB::raw('sum(fee+tax) as totalFee'),DB::raw('sum(tax) as totalTax'))->where(DB::raw('date(created_at)'),$date)->where('status','processed')->first();
		$recharge = DB::connection('slave')->table('recharges')->select(DB::raw('sum(amount) as totalAmount'),DB::raw('sum(commission) as totalCommission'),DB::raw("COUNT(id) AS totalCount"))->where('status','processed')->where(DB::raw('date(created_at)'),$date)->first();
		$dmt = DB::connection('slave')->table('dmt_fund_transfers')->select(DB::raw('sum(amount) as totalAmount'),DB::raw("COUNT(id) AS totalCount"),DB::raw('sum(fee+tax) as totalFee'),DB::raw('sum(tax) as totalTax'))->where(DB::raw('date(created_at)'),$date)->where('status','processed')->first();
		$globalConfig = GlobalConfig::where('slug','admin_email')->first();
		if(!empty($globalConfig['attribute_1']))
		{

			$emails = explode(',',$globalConfig['attribute_1']);
			foreach($emails as $email)
			{

				$data['email'] = trim($email);
				$data['order'] = (array)$order;
				$data['stackUpi'] = (array)$StackUpi;
				$data['StackUpiFeeTax'] = (array)$StackUpiFeeTax;
				$data['smartCollect'] = (array)$smartCollect;
				$data['smartCollectFeeTax'] = (array)$smartCollectFeeTax;
				$data['userVan'] = (array)$userVan;
				$data['loadMoney'] = (array)$loadMoney;
				$data['aeps'] = (array)$aeps;
				$data['autoSettlement'] = (array)$autoSettlement;
				$data['recharges'] = (array)$recharge;
				$data['dmt'] = (array)$dmt;
				$data['date'] = date('d-m-Y',strtotime($date));
				dispatch(new SendTransactionEmailJob($data,'dailyTransaction'));
				//print_r($data);
			}
			
		}
	}

	
}