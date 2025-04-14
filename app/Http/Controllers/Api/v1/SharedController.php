<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Models\UPICallback;
use App\Models\FundReceiveCallback;
use App\Models\AutoCollectCallback;
use App\Models\Order;

class SharedController extends Controller
{

	public function getUserList(Request $request)
	{
		$key = 'asdfghjkl1234567890';
		$inputKey = $request->key;
		$from = $request->fromdate;
		$to = $request->todate;
		$validator = Validator::make($request->all(), [
			'key' 		=> 'required',
		    'fromdate'  => 'required|date_format:Y-m-d',
		    'todate'    => 'required|date_format:Y-m-d|after_or_equal:fromdate',
		]);
		if($validator->fails()) {   
            return response()->json([
                "success" => false,
                "message" => $validator->errors(),
                "error" => $validator->errors()
            ]);                       
        }
		if($key==$inputKey)
		{
			$limit = $request->limit ? $request->limit : 20;
			$page = $request->page && $request->page > 0 ? $request->page : 1;
			$skip = ($page - 1) * $limit;
			$user = User::select('users.id as usercode',DB::raw('"Xettle" as parentcode'),'users.name as username','users.email as emailid',DB::raw('case when users.mobile is null then "" else users.mobile end as mobile'),'business_infos.business_name as companyname',DB::raw('case when business_infos.business_type is null then "" else business_infos.business_type end as usertype'),DB::raw('case when business_infos.pan_number is null then "" else business_infos.pan_number end as pancard'),DB::raw('case when business_infos.address is null then "" else business_infos.address end as address'),DB::raw('case when business_infos.pincode is null then "" else business_infos.pincode end as pincode'),DB::raw('case when business_infos.city is null then "" else business_infos.city end as districtname'),DB::raw('case when states.state_name is null then "" else states.state_name end as statename'),DB::raw('case when business_infos.web_url is null then "" else business_infos.web_url end as domainurl'),DB::raw('"India" as Country'),DB::raw('case when business_infos.gstin is null then "" else business_infos.gstin end as GSTN'),DB::raw('case when account_managers.name is null then "" else account_managers.name end as createdby'),DB::raw('case when gstin is null then "unregistered" else "regular" end as GSTStatus'),DB::raw('case when business_infos.state!=21 then "Inter State" else "" end as FiscalPosition'))->leftjoin('business_infos','users.id','=','business_infos.user_id')->leftjoin('states','business_infos.state','=','states.id')->leftjoin('account_managers','business_infos.acc_manager_id','=','account_managers.id')->where('is_admin','0')
			->whereBetween('users.created_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)
						->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')]);
			//->offset($skip)->limit($limit);
			//$count = User::where('is_admin','0')->count();
			$users = $user->get();
			
			if($user)
			{
				$data['data'] = $users;
				//$data['records'] = count($users);
				$code = "0x0200";
				$status = $this::SUCCESS_STATUS;
				$this->message = 'Record fetched successfully.';
			}
			else
			{
				$data['user'] = '';
				$code = $this::FAILED_CODE;
				$status = $this::FAILED_STATUS;
				$this->message = 'No record found.';
			}
		}
		else
		{
			$data['user'] = '';
			$code = '0x0202';
			$status = $this::FAILED_STATUS;
			$this->message = 'Key does not match.';
		}
		//$resp['code']       = $code;
        //$resp['message']    = $this->message;
        //$resp['status']     = $status;
        //$resp['totalrecords'] = $count;
		// if(isset($data)) {
  //           $resp['data']   = $data;
  //       }

		return response()->json($data);
	}

	public function getTransactionList(Request $request)
	{
		$key = 'asdfghjkl1234567890';
		$inputKey = $request->key;
		$from = $request->fromdate;
		$to = $request->todate;
		$validator = Validator::make($request->all(), [
			'key' 		=> 'required',
		    'fromdate'  => 'required|date_format:Y-m-d',
		    'todate'    => 'required|date_format:Y-m-d|after_or_equal:fromdate',
		]);
		if($validator->fails()) {   
            return response()->json([
                "success" => false,
                "message" => $validator->errors(),
                "error" => $validator->errors()
            ]);                       
        }
		if($key==$inputKey)
		{
			DB::enableQueryLog();
			$exitrecord = DB::table('mgpl_transactions_list')->where('api_type','transaction')->where(function($query) use ($from,$to){
																	$query->whereBetween('from_date',[$from,$to])
																->orWhereBetween('end_date',[$from,$to]);})->count();
			//dd(DB::getQueryLog());
			if($exitrecord==0)
			{
				$refIdCount = DB::table('mgpl_transactions_list')->where('api_type','transaction')->orderBy('id','desc')->limit(1)->first();
				if(empty($refIdCount))
				{
					$journal_ref_Id = "XWDCN/1";
				}else
				{
					$ref_id = explode('/',$refIdCount->journal_ref_Id);
					$ref_id1 = $ref_id[1] + 1;
					$journal_ref_Id = $ref_id[0].'/'.$ref_id1;
				}
			 	$min_integer  =   1;
				$max_integer  =  time();
				$mm = ($max_integer - $min_integer + 1) + $min_integer;
				//DB::enableQueryLog();
					$upi_callback = DB::table('upi_callbacks')->select(DB::raw('"Xettle" as Transactionchannel'),DB::raw('FLOOR(RAND() * '.$mm.') as TransactionId'),DB::raw('user_id as CustomerID'),DB::raw('round(sum(amount),2) as amount'),DB::raw('"UPI Stack" as Analyticaltag'),DB::raw('"Xettle Customer Wallet" as Craccount'),DB::raw('"Money in UPI Stack" as Transactiontype'),DB::raw('"Xettle Transaction Debit/Credit Note" as JournalType'),DB::raw('case when root_type="fpay" then "102111"  else "102112" end as Debitaccountcode'),DB::raw('"112007" as Creditaccountcode'),DB::raw('case when root_type="fpay" then "Yes Bank Xettle UPI Transaction Pool Account"  else "Indusind Xettle UPI Transaction Pool Account" end as Draccount'))->where('is_trn_disputed','0')->whereBetween(DB::raw('date(upi_callbacks.created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id','root_type')->get();
					//ddd(DB::getQueryLog());
				$patnerVan = DB::table('fund_receive_callbacks')->select(DB::raw('"Xettle" as Transactionchannel'),DB::raw('FLOOR(RAND() * '.$mm.') as TransactionId'),DB::raw('user_id as CustomerID'),DB::raw('round(sum(amount),2) as amount'),DB::raw('"Partner VAN" as Analyticaltag'),DB::raw('"Xettle Customer Wallet" as Craccount'),DB::raw('"Funding partner wallet" as Transactiontype'),DB::raw('"Xettle Transaction Debit/Credit Note" as JournalType'),DB::raw('case when root_type="eb_van" then "EaseBuzz Xettle Transaction Pool Account" when root_type="raz_van" then "Razorpay Xettle Transaction Pool Account" else "Cashfree Xettle Transaction Pool Account" end as Draccount'),DB::raw('case when root_type="eb_van" then "102108" when root_type="raz_van" then "102110" else "102109" end as Debitaccountcode'),DB::raw('"112007" as Creditaccountcode'))->whereBetween(DB::raw('date(created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id','root_type')->get();
				$smartCollect = DB::table('cf_merchants_fund_callbacks')->select(DB::raw('"Xettle" as Transactionchannel'),DB::raw('FLOOR(RAND() * '.$mm.') as TransactionId'),DB::raw('user_id as CustomerID'),DB::raw('round(sum(amount),2) as amount'),DB::raw('"Smart Collect" as Analyticaltag'),DB::raw('"Xettle Customer Wallet" as Craccount'),DB::raw('"Money in Smart Collect" as Transactiontype'),DB::raw('"Xettle Transaction Debit/Credit Note" as JournalType'),DB::raw('"Cashfree Xettle Transaction Pool Account" as Draccount'),DB::raw('"102113" as Debitaccountcode'),DB::raw('"112007" as Creditaccountcode'))->whereBetween(DB::raw('date(created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id')->get();
				$payout = DB::table('orders')->select(DB::raw('"Xettle" as Transactionchannel'),DB::raw('FLOOR(RAND() * '.$mm.') as TransactionId'),DB::raw('user_id as CustomerID'),DB::raw('round(sum(amount),2) as amount'),DB::raw('"Smart Payout" as Analyticaltag'),DB::raw('"112008" as Debitaccountcode'),DB::raw('"Xettle Customer Wallet" as Draccount'),DB::raw('"Payout" as Transactiontype'),DB::raw('"Xettle Transaction Debit/Credit Note" as JournalType'),DB::raw('case when integration_id="int_1632375646" then "Cashfree Xettle Redeem Pool Account" when integration_id="int_1632375690" then "EaseBuzz Xettle Redeem Pool Account" else "Safex Xettle Redeem Pool Account" end as Craccount'),DB::raw('case when integration_id="int_1632375646" then "112505" when integration_id="int_1632375690" then "112506" else "112508" end as Creditaccountcode'))->where('status','processed')->whereBetween(DB::raw('date(created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id','integration_id')->get();
				//DB::enableQueryLog();
				$reversedPayout = DB::table('transactions as t')->select(DB::raw('"Xettle" as Transactionchannel'),DB::raw('FLOOR(RAND() * '.$mm.') as TransactionId'),DB::raw('t.user_id as CustomerID'),DB::raw('round(sum(tr_amount),2) as amount'),DB::raw('"Smart Payout" as Analyticaltag'),DB::raw('"112008" as Creditaccountcode'),DB::raw('"Xettle Customer Wallet" as Craccount'),DB::raw('"Payout Reversal" as Transactiontype'),DB::raw('"Xettle Transaction Debit/Credit Note" as JournalType'),DB::raw('case when integration_id="int_1632375646" then "Cashfree Xettle Redeem Pool Account" when integration_id="int_1632375690" then "EaseBuzz Xettle Redeem Pool Account" else "Safex Xettle Redeem Pool Account" end as Draccount'),DB::raw('case when integration_id="int_1632375646" then "112505" when integration_id="int_1632375690" then "112506" else "112508" end as Debitaccountcode'))->leftjoin('orders as o','o.order_ref_id','=','t.txn_ref_id')->where('t.tr_identifiers','payout_reversed')->whereBetween(DB::raw('date(t.created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('t.user_id','o.integration_id')->get();
				//dd(DB::getQueryLog());
				$aeps = DB::table('aeps_transactions')->select(DB::raw('"Xettle" as Transactionchannel'),DB::raw('FLOOR(RAND() * '.$mm.') as TransactionId'),DB::raw('user_id as CustomerID'),DB::raw('round(sum(transaction_amount),2) as amount'),DB::raw('"AEPS" as Analyticaltag'),DB::raw('"Xettle Customer Wallet" as Craccount'),DB::raw('"AEPS" as Transactiontype'),DB::raw('"Xettle Transaction Debit/Credit Note" as JournalType'),DB::raw('case when route_type="icici" then "ICICI AEPS Xettle Transaction Pool Account" when route_type="sbm" then "SBM AEPS Xettle Transaction Pool Account" when route_type="airtel" then "Airtel AEPS Xettle Transaction Pool Account" when route_type="paytm" then "Paytm AEPS Xettle Transaction Pool Account" end as Draccount'),DB::raw('case when route_type="icici" then "102115" when route_type="sbm" then "102117" when route_type="airtel" then "102116" when route_type="paytm" then "102118" end as Debitaccountcode'),DB::raw('"112007" as Creditaccountcode'))->where('status','success')->whereBetween(DB::raw('date(created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id','route_type')->get();
				$internal_transafer = DB::table('transactions')->select(DB::raw('"Xettle" as Transactionchannel'),DB::raw('FLOOR(RAND() * '.$mm.') as TransactionId'),DB::raw('user_id as CustomerID'),DB::raw('round(sum(tr_amount),2) as amount'),DB::raw('"Smart Payout" as Analyticaltag'),DB::raw('"Xettle Customer Wallet" as Draccount'),DB::raw('"Xettle Customer Smart Payout Wallet" as Craccount'),DB::raw('"Transfer from main wallet to payout wallet" as Transactiontype'),DB::raw('"Xettle Transaction Debit/Credit Note" as JournalType'),DB::raw('"112007" as Debitaccountcode'),DB::raw('"112008" as Creditaccountcode'))->where('tr_identifiers','internal_transfer')->where('tr_type','cr')->whereBetween(DB::raw('date(created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id')->get();

				$data=array();
				if(!empty($upi_callback))
				{
					$data = json_decode(json_encode($upi_callback), true);
				}
				if(!empty($patnerVan))
				{
					$patnerVan = json_decode(json_encode($patnerVan), true);
					$data = array_merge($data,$patnerVan);
				}
				if(!empty($smartCollect))
				{
					$smartCollect = json_decode(json_encode($smartCollect), true);
					$data = array_merge($data,$smartCollect);
				}
				if(!empty($payout))
				{
					$payout = json_decode(json_encode($payout), true);
					$data = array_merge($data,$payout);
				}
				if(!empty($reversedPayout))
				{
					$reversedPayout = json_decode(json_encode($reversedPayout), true);
					$data = array_merge($data,$reversedPayout);
				}
				if(!empty($aeps))
				{
					$aeps = json_decode(json_encode($aeps), true);
					$data = array_merge($data,$aeps);
				}
				if(!empty($internal_transafer))
				{
					$internal_transafer = json_decode(json_encode($internal_transafer), true);
					$data = array_merge($data,$internal_transafer);
				}

				foreach($data as &$val)
				{
					//$ref_id[$val->user_id] = $journal_ref_Id;
					if(isset($ref_id[$val['CustomerID']]))
					{
						$jrId = $ref_id[$val['CustomerID']];
					}else
					{
						$ref_id[$val['CustomerID']] = $journal_ref_Id;
						$jr_Id = explode('/',$journal_ref_Id);
						$jr_id1 = $jr_Id[1] + 1;
						$jrId = $jr_Id[0].'/'.$jr_id1;
						$journal_ref_Id = $jrId;
						
					}
					$val['Date'] = $to;
					$val['JournalRefID'] = $ref_id[$val['CustomerID']];
					DB::table('mgpl_transactions_list')->insert([
						'transaction_channel'=> $val['Transactionchannel'],
						'journal_ref_Id' => $ref_id[$val['CustomerID']],
						'transactionId' => $val['TransactionId'],
						'customerId' => $val['CustomerID'],
						'amount' => $val['amount'],
						'analytical_tag' => $val['Analyticaltag'],
						'dr_account' => $val['Draccount'],
						'cr_account' => $val['Craccount'],
						'transaction_type' => $val['Transactiontype'],
						'journal_type' => $val['JournalType'],
						'debit_account_code' => $val['Debitaccountcode'],
						'credit_account_code' => $val['Creditaccountcode'],
						'from_date' => $from,
						'end_date' => $to,
						'api_type' => 'transaction',
						'created_at' => date('Y-m-d H:i:s')
					]);
				}
				return response()->json($data);
			}
			else
			{
				$exitrecord = DB::table('mgpl_transactions_list')->where('api_type','transaction')->where(function($query) use ($from,$to){
																	$query->whereBetween('from_date',[$from,$to])
																->orWhereBetween('end_date',[$from,$to]);})->get();
				foreach($exitrecord as $val)
				{
					$data['Transactionchannel'] = $val->transaction_channel;
					$data['JournalRefID'] = $val->journal_ref_Id;
					$data['TransactionId'] = $val->transactionId;
					$data['CustomerID'] = $val->customerId;
					$data['amount'] = $val->amount;
					$data['Analyticaltag'] = $val->analytical_tag;
					$data['Draccount'] = $val->dr_account;
					$data['Craccount'] = $val->cr_account;
					$data['Transactiontype'] = $val->transaction_type;
					$data['JournalType'] = $val->journal_type;
					$data['Debitaccountcode'] = $val->debit_account_code;
					$data['Creditaccountcode'] = $val->credit_account_code;
					$data['from_date'] = $val->from_date;
					$data['end_date'] = $val->end_date;
					$data['created_at'] = $val->created_at;
					$aa[]= $data;
				}
				
				$data['code'] = '0x0202';
				$data['status'] = $this::FAILED_STATUS;
				$data['message'] = 'Record already sent.';
				return response()->json($aa);
			}
			
			
		}
		else
		{
			$data['code'] = '0x0202';
			$data['status'] = $this::FAILED_STATUS;
			$data['message'] = 'Key does not match.';
			return response()->json($data);
		}

	}

	public function getBill(Request $request)
	{
		$key = 'asdfghjkl1234567890';
		$inputKey = $request->key;
		$from = $request->fromdate;
		$to = $request->todate;
		$validator = Validator::make($request->all(), [
			'key' 		=> 'required',
		    'fromdate'  => 'required|date_format:Y-m-d',
		    'todate'    => 'required|date_format:Y-m-d|after_or_equal:fromdate',
		]);
		if($validator->fails()) {   
            return response()->json([
                "success" => false,
                "message" => $validator->errors(),
                "error" => $validator->errors()
            ]);                       
        }
		if($key==$inputKey)
		{
			$exitrecord = DB::table('mgpl_transactions_list')->where('api_type','bill')->where(function($query) use ($from,$to){
																	$query->whereBetween('from_date',[$from,$to])
																->orWhereBetween('end_date',[$from,$to]);})->count();
			if($exitrecord==0)
			{
				$min_integer  =   1;
				$max_integer  =  time();
				$mm = ($max_integer - $min_integer + 1) + $min_integer;
				$billData = DB::table('aeps_transactions')->select(DB::raw('user_id as PartnerID'),DB::raw('FLOOR(RAND() * '.$mm.') as "invoiceno"'),DB::raw('"Xettle AEPS Commission" as Product'),DB::raw('"Xettle" as AnalyticalAccount'),DB::raw('case when route_type="icici" then "ICICI" when route_type="sbm" then "SBM" when route_type="paytm" then "PAYTM" when route_type="airtel" then "Airtel" end as Analyticaltag'),DB::raw('round(sum(commission),2) as amount'),DB::raw('round(sum(tds),2) as tds'),DB::raw('"AEPS Commission" as Transactiontype'))->where('is_commission_credited','1')->where('status','success')->whereBetween(DB::raw('date(commission_credited_at)'),[Carbon::createFromFormat('Y-m-d', $request->fromdate)
								->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id','route_type')->get();
				$data = array();
				if(!empty($billData))
				{
					$data = json_decode(json_encode($billData),true);
				}
				$result = array();
				foreach($data as $val)
				{
					$aa['date'] = $to;
					$aa['customer id'] = $val['PartnerID'];
					$aa['bill no'] = $val['invoiceno'];
					$aa['product name'] = $val['Product'];
					$aa['amount'] = $val['amount'];
					$aa['transaction channel'] = $val['AnalyticalAccount'];
					$aa['Analytcal tag'] = $val['Analyticaltag'];
					$aa['Transaction type'] = $val['Transactiontype'];
					$aa['journal type'] = 'Xettle Wallet Bills';
					$result[] = $aa;
					DB::table('mgpl_transactions_list')->insert([
								'transaction_channel'=> $val['AnalyticalAccount'],
								'journal_ref_Id' => '',
								'transactionId' => $val['invoiceno'],
								'customerId' => $val['PartnerID'],
								'amount' => $val['amount'],
								'analytical_tag' => $val['Analyticaltag'],
								'dr_account' => '',
								'cr_account' => '',
								'transaction_type' => $val['Transactiontype'],
								'journal_type' => '',
								'debit_account_code' => '',
								'credit_account_code' => '',
								'from_date' => $from,
								'end_date' => $to,
								'api_type' => 'bill',
								'created_at' => date('Y-m-d H:i:s'),
								'gst' => '',
								'total_amount' => '',
								'product' => $val['Product']
							]);
				}
				return response()->json($result);
			}
			else
			{
				$exitrecord = DB::table('mgpl_transactions_list')->where('api_type','bill')->where(function($query) use ($from,$to){
																	$query->whereBetween('from_date',[$from,$to])
																->orWhereBetween('end_date',[$from,$to]);})->get();
				foreach($exitrecord as $val)
				{
					$data['customer id'] = $val->customerId;
					$data['amount'] = $val->amount;
					$data['bill no'] = $val->transactionId;
					$data['transaction channel'] = 'Xettle';
					$data['Analytcal tag'] = $val->analytical_tag;
					$data['Transaction type'] = $val->transaction_type;
					$data['journal type'] = 'Xettle Wallet Bills';
					$data['date'] = $val->end_date;
					$data['product name'] = $val->product?$val->product:'';
					$aa[] = $data;
				}
				$data['code'] = '0x0202';
				$data['status'] = $this::FAILED_STATUS;
				$data['message'] = 'Record already sent.';
				return response()->json($aa);
			}
		}
		else
		{
			$data['code'] = '0x0202';
			$data['status'] = $this::FAILED_STATUS;
			$data['message'] = 'Key does not match.';
			return response()->json($data);
		}
	}

	public function getInvoice(Request $request)
	{
		$key = 'asdfghjkl1234567890';
		$inputKey = $request->key;
		$from = $request->fromdate;
		$to = $request->todate;
		$validator = Validator::make($request->all(), [
			'key' 		=> 'required',
		    'fromdate'  => 'required|date_format:Y-m-d',
		    'todate'    => 'required|date_format:Y-m-d|after_or_equal:fromdate',
		]);
		if($validator->fails()) {   
            return response()->json([
                "success" => false,
                "message" => $validator->errors(),
                "error" => $validator->errors()
            ]);                       
        }
		if($key==$inputKey)
		{
			$exitrecord = DB::table('mgpl_transactions_list')->where('api_type','invoice')->where(function($query) use ($from,$to){
																	$query->whereBetween('from_date',[$from,$to])
																->orWhereBetween('end_date',[$from,$to]);})->count();
			if($exitrecord==0)
			{

				$min_integer  =   1;
				$max_integer  =  time();
				$mm = ($max_integer - $min_integer + 1) + $min_integer;
				$payout = DB::table('orders')->select(DB::raw('user_id as PartnerID'),DB::raw('FLOOR(RAND() * '.$mm.') as "invoiceno"'),DB::raw('"Smart Payout Fee" as Product'),DB::raw('round(sum(fee),2) as amount'),DB::raw('"Xettle" as AnalyticalAccount'),DB::raw('"Smart Payout Fee" as Transactiontype'),DB::raw('case when integration_id="int_1632375646" then "Cashfree" when integration_id="int_1632375690" then "EaseBuzz" else "Safex " end as Analyticaltag'),DB::raw('round(sum(tax),2) as GST'),DB::raw('round(sum(fee+tax),2) as Total'))->where('status','processed')->whereBetween(DB::raw('date(created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
								->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id','integration_id')->get();
				//DB::enableQueryLog();
				$smartCollectVPA = DB::table('cf_merchants')->select(DB::raw('cf_merchants.user_id as PartnerID'),DB::raw('FLOOR(RAND() * '.$mm.') as "invoiceno"'),DB::raw('"Smart Collect VPA/VAN Creation Fee" as Product'),DB::raw('round(sum(tr_fee),2) as amount'),DB::raw('"Xettle" as AnalyticalAccount'),DB::raw('"Cashfree" as Analyticaltag'),DB::raw('round(sum(tr_tax),2) as GST'),DB::raw('round(sum(tr_fee+tr_tax),2) as Total'),DB::raw('"Smart Collect VPA/VAN Creation" as Transactiontype'))->join('transactions','transactions.txn_ref_id','=','cf_merchants.request_id')->where('cf_merchants.is_fee_charged','1')->whereBetween(DB::raw('date(cf_merchants.created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
								->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('cf_merchants.user_id','cf_merchants.service_type')->get();
				//dd(DB::getQueryLog());
				$partnerVan = DB::table('fund_receive_callbacks')->select(DB::raw('user_id as PartnerID'),DB::raw('FLOOR(RAND() * '.$mm.') as "invoiceno"'),DB::raw('"Partner VAN Fee" as Product'),DB::raw('round(sum(fee),2) as amount'),DB::raw('"Xettle" as AnalyticalAccount'),DB::raw('case when root_type="eb_van" then "EaseBuzz" when root_type="cf_van" then "Cashfree" when root_type="raz_van" then "Razorpay" end as Analyticaltag'),DB::raw('round(sum(tax),2) as GST'),DB::raw('round(sum(fee+tax),2) as Total'),DB::raw('"Partner VAN Fee" as Transactiontype'))->where('is_trn_credited','1')->whereBetween(DB::raw('date(fund_receive_callbacks.created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
								->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id','root_type')->get();
				$UPICallback = DB::table('upi_callbacks')->select(DB::raw('user_id as PartnerID'),DB::raw('FLOOR(RAND() * '.$mm.') as "invoiceno"'),DB::raw('"UPI Collection Fee" as Product'),DB::raw('round(sum(fee),2) as amount'),DB::raw('"Xettle" as AnalyticalAccount'),DB::raw('case when root_type="ibl" then "Indusind Bank" when root_type="fpay" then "Yes Bank" end as Analyticaltag'),DB::raw('round(sum(tax),2) as GST'),DB::raw('round(sum(fee+tax),2) as Total'),DB::raw('"UPI Collection" as Transactiontype'))->where('is_trn_disputed','0')->whereBetween(DB::raw('date(created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
								->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('user_id','root_type')->get();
				$upi_vpa_creation = DB::table('upi_merchants')->select(DB::raw('upi_merchants.user_id as PartnerID'),DB::raw('FLOOR(RAND() * '.$mm.') as "invoiceno"'),DB::raw('"UPI VPA Creation Fee" as Product'),DB::raw('round(sum(tr_fee),2) as amount'),DB::raw('"Xettle" as AnalyticalAccount'),DB::raw('case when upi_merchants.root_type="ibl" then "Indusind Bank" else "Yes Bank" end as Analyticaltag'),DB::raw('round(sum(transactions.tr_tax),2) as GST'),DB::raw('round(sum(tr_tax+tr_fee),2) as Total'),DB::raw('"UPI VPA Creation" as Transactiontype'))->join('transactions','transactions.txn_ref_id','=','upi_merchants.merchant_txn_ref_id')->where('upi_merchants.is_fee_charged','1')->whereBetween(DB::raw('date(upi_merchants.created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
								->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('upi_merchants.user_id','upi_merchants.root_type')->get();

				$upi_verify = DB::table('upi_verify_requests')->select(DB::raw('upi_verify_requests.user_id as PartnerID'),DB::raw('FLOOR(RAND() * '.$mm.') as "invoiceno"'),DB::raw('"UPI VPA Verification Fee" as Product'),DB::raw('round(sum(tr_fee),2) as amount'),DB::raw('"Xettle" as AnalyticalAccount'),DB::raw('"Indusind Bank" as Analyticaltag'),DB::raw('round(sum(tr_tax),2) as GST'),DB::raw('round(sum(tr_tax+tr_fee),2) as Total'),DB::raw('"UPI VPA Verification" as Transactiontype'))->join('transactions','transactions.txn_ref_id','=','upi_verify_requests.request_id')->where('is_fee_charged','1')->whereBetween(DB::raw('date(upi_verify_requests.created_at)'), [Carbon::createFromFormat('Y-m-d', $request->fromdate)
								->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->format('Y-m-d')])->groupBy('upi_verify_requests.user_id')->get();
				$data=array();
				if(!empty($payout))
				{
					$payout = json_decode(json_encode($payout), true);
					$data = array_merge($data,$payout);
				}
				if(!empty($smartCollectVPA))
				{
					$smartCollectVPA = json_decode(json_encode($smartCollectVPA), true);
					$data = array_merge($data,$smartCollectVPA);
				}
				if(!empty($UPICallback))
				{
					$UPICallback = json_decode(json_encode($UPICallback), true);
					$data = array_merge($data,$UPICallback);
				}
				if(!empty($upi_vpa_creation))
				{
					$upi_vpa_creation = json_decode(json_encode($upi_vpa_creation), true);
					$data = array_merge($data,$upi_vpa_creation);
				}
				if(!empty($upi_verify))
				{
					$upi_verify = json_decode(json_encode($upi_verify), true);
					$data = array_merge($data,$upi_verify);
				}
				$result = array();
				foreach($data as &$val)
				{
					$aa['date'] = $to;
					$aa['customer id'] = $val['PartnerID'];
					$aa['invoice no'] = $val['invoiceno'];
					$aa['product name'] = $val['Product'];
					$aa['transaction channel'] = $val['AnalyticalAccount'];
					$aa['analytcal tag'] = $val['Analyticaltag'];
					$aa['GST'] = $val['GST']?$val['GST']:'';
					$aa['Total'] = $val['Total']?$val['Total']:'';
					$aa['Transaction type'] = $val['Transactiontype'];
					$aa['journal type'] = 'Xettle Wallet Invoices';
					$aa['amount'] = $val['amount']?$val['amount']:'';
					DB::table('mgpl_transactions_list')->insert([
							'transaction_channel'=> '',
							'journal_ref_Id' => '',
							'transactionId' => $val['invoiceno'],
							'customerId' => $val['PartnerID'],
							'amount' => $val['amount'],
							'analytical_tag' => $val['Analyticaltag'],
							'dr_account' => '',
							'cr_account' => '',
							'transaction_type' => $val['Transactiontype'],
							'journal_type' => '',
							'debit_account_code' => '',
							'credit_account_code' => '',
							'from_date' => $from,
							'end_date' => $to,
							'api_type' => 'invoice',
							'created_at' => date('Y-m-d H:i:s'),
							'gst' => $val['GST'],
							'total_amount' => $val['Total'],
							'product' => $val['Product']
						]);
					$result[] = $aa;
				}
				return response()->json($result);
			}
			else
			{
				$exitrecord = DB::table('mgpl_transactions_list')->where('api_type','invoice')->where(function($query) use ($from,$to){
																	$query->whereBetween('from_date',[$from,$to])
																->orWhereBetween('end_date',[$from,$to]);})->get();
				foreach($exitrecord as $val)
				{
					$data['customer id'] = $val->customerId;
					$data['amount'] = $val->amount?$val->amount:'';
					$data['invoice no'] = $val->transactionId;
					$data['transaction channel'] = 'Xettle';
					$data['analytcal tag'] = $val->analytical_tag;
					$data['Transaction type'] = $val->transaction_type;
					$data['journal type'] = 'Xettle Wallet Invoices';
					$data['GST'] = $val->gst?$val->gst:'';
					$data['Total'] = $val->total_amount?$val->total_amount:'';
					$data['date'] = $val->end_date;
					$data['product name'] = $val->product?$val->product:'';
					$aa[] = $data;
				}
				$data['code'] = '0x0202';
				$data['status'] = $this::FAILED_STATUS;
				$data['message'] = 'Record already sent.';
				return response()->json($aa);
			}
		}
		else
		{
			$data['code'] = '0x0202';
			$data['status'] = $this::FAILED_STATUS;
			$data['message'] = 'Key does not match.';
			return response()->json($data);
		}
	}

}