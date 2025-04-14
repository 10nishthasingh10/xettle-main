<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Validations\ReconcileValidation as Validations;
use Storage;
/**
 * ReconcileController
 */
class ReconcileController extends Controller
{
	/**
	 * index
	 *
	 * @return void
	 */
	public function index()
	{
		$data['page_title'] =  "Reconcile";
        $data['site_title'] =  "Reconcile List";
        $data['view']       = ADMIN . '/' . ".reports.reconcile";
        $data['userData']   = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        return view($data['view'])->with($data);
	}
	/**
	 * reconcileReport
	 *
	 * @return void
	 */
	public function reconcileReport(Request $request)
	{
		$validation = new Validations($request);
        $validator = $validation->reconcileReport();
        if ($validator->fails()) {
			$this->message = $validator->errors();
        } else {
			$user = DB::table('users')
				->where('id', $request->user_id)
				->first();
			$userService = DB::table('user_services')
				->where('user_id', $request->user_id)
				->get();
			$cashInHtml = '<tr><td colspan="6" class="center">Cash IN</td></tr>';
			$cashOutHtml = '<tr><td colspan="6" class="center">Cash OUT</td></tr>';
			$internalTransferHtml = '';
			$cashInAmount= 0;
			$cashInFee = 0;
			$cashInTax = 0;
			$cashOutTax = 0;
			$cashOutFee = 0;
			$cashOutAmount = 0;
			$cashInCount = 0;
			$cashOutCount = 0;
			$payoutRefundAmount = 0;
			foreach ($userService as $userServices) {
				$serviceName = DB::table('global_services')
					->where('service_id', $userServices->service_id)->first();
				if (!in_array($userServices->service_id, [PAYOUT_SERVICE_ID, VALIDATE_SERVICE_ID])) {
					$result = $this->getRecord($request->user_id, $userServices->service_id, $request->from, $request->to);
					if (isset($result['total']->amount) && !empty($result['total']->amount)) {
						$cashInHtml .='<tr><td rowspan="'.$this->getNonEmptyArrayCount($result).'">'.$serviceName->service_name.'</td>
						<td>'.@$result['total']->amount.'</td><td>'.@$result['total']->count.'</td><td>'.@$result['total']->fee.'</td><td>'.@$result['total']->tax.'</td><td>Over all</td></tr>';
						if (isset($result['credit']->amount) && !empty($result['credit']->amount)) {
							
							$cashInHtml .='<tr><td>'.@$result['credit']->amount.'</td><td>'.@$result['credit']->count.'</td><td>'.@$result['credit']->fee.'</td><td>'.@$result['credit']->tax.'</td><td>Credit</td></tr>';
						}
						
						if (isset($result['nonCredit']->amount) && !empty($result['nonCredit']->amount)) {
							$cashInHtml .='<tr><td>'.@$result['nonCredit']->amount.'</td><td>'.@$result['nonCredit']->count.'</td><td>'.@$result['nonCredit']->fee.'</td><td>'.@$result['nonCredit']->tax.'</td><td>Non Credit</td></tr>';
						}
						
						if (isset($result['refunded']->amount) && !empty($result['refunded']->amount)) {
							$cashInHtml .='<tr><td>'.@$result['refunded']->amount.'</td><td>'.@$result['refunded']->count.'</td><td>'.@$result['refunded']->fee.'</td><td>'.@$result['refunded']->tax.'</td><td>Refunded</td></tr>';
						}
						if (isset($result['failed']->amount) && !empty($result['failed']->amount)) {
							$cashInHtml .='<tr><td>'.@$result['failed']->amount.'</td><td>'.@$result['failed']->count.'</td><td>'.@$result['failed']->fee.'</td><td>'.@$result['failed']->tax.'</td><td>Failed</td></tr>';
						}
						
						if ((isset($result['txn']->amount) && !empty($result['txn']->amount))|| (isset($result['txn']->fee) && !empty($result['txn']->fee)) || (isset($result['txn']->tax) && !empty($result['txn']->tax))) {
							// $cashInAmount = $cashInAmount + $result['txn']->amount;
							// $cashInFee = $cashInFee + @$result['txn']->fee;
							// $cashInTax = $cashInTax + @$result['txn']->tax;
							// $cashInCount = $cashInCount + @$result['txn']->count;
							$cashInHtml .='<tr><td>'.@$result['txn']->amount.'</td><td>'.@$result['txn']->count.'</td><td>'.@$result['txn']->fee.'</td><td>'.@$result['txn']->tax.'</td><td>Transactions</td></tr>';
						}
							$cashInAmount = $cashInAmount + $result['credit']->amount;
							$cashInFee = $cashInFee + @$result['credit']->fee + @$result['txn']->fee;
							$cashInTax = $cashInTax + @$result['credit']->tax + @$result['txn']->tax;
							$cashInCount = $cashInCount + @$result['credit']->count + @$result['txn']->count;

						if (isset($result['disputed']->amount) && !empty($result['disputed']->amount)) {
							$cashInHtml .='<tr><td>'.@$result['disputed']->amount.'</td><td>'.@$result['disputed']->count.'</td><td>'.@$result['disputed']->fee.'</td><td>'.@$result['disputed']->tax.'</td><td>Disputed</td></tr>';
						}
						
					}
					else if ((isset($result['txn']->amount) && !empty($result['txn']->amount))|| (isset($result['txn']->fee) && !empty($result['txn']->fee)) || (isset($result['txn']->tax) && !empty($result['txn']->tax))) {
						$cashOutFee = $cashOutFee  + @$result['txn']->fee;
							$cashOutTax = $cashOutTax  + @$result['txn']->tax;
							$cashOutCount = $cashOutCount  + @$result['txn']->count;
					}
				}
				if (in_array($userServices->service_id, [PAYOUT_SERVICE_ID, VALIDATE_SERVICE_ID])) {
					$result = $this->getRecord($request->user_id, $userServices->service_id, $request->from, $request->to);
					if (isset($result['total']->amount) && !empty($result['total']->amount)) {
						$cashOutHtml .='<tr><td rowspan="'.$this->getNonEmptyArrayCount($result).'">'.$serviceName->service_name.'</td>
						<td>'.@$result['total']->amount.'</td><td>'.@$result['total']->count.'</td><td>'.@$result['total']->fee.'</td><td>'.@$result['total']->tax.'</td><td>Over all</td></tr>';
						if (isset($result['credit']->amount) && !empty($result['credit']->amount)) {
							$cashOutAmount = $cashOutAmount + $result['credit']->amount;
							$cashOutFee = $cashOutFee + @$result['credit']->fee;
							$cashOutTax = $cashOutTax + @$result['credit']->tax;
							$cashOutCount = $cashOutCount + @$result['credit']->count;
							$cashOutHtml .='<tr><td>'.@$result['credit']->amount.'</td><td>'.@$result['credit']->count.'</td><td>'.@$result['credit']->fee.'</td><td>'.@$result['credit']->tax.'</td><td>Credit</td></tr>';
						}
						
						if (isset($result['nonCredit']->amount) && !empty($result['nonCredit']->amount)) {
							$cashOutHtml .='<tr><td>'.@$result['nonCredit']->amount.'</td><td>'.@$result['nonCredit']->count.'</td><td>'.@$result['nonCredit']->fee.'</td><td>'.@$result['nonCredit']->tax.'</td><td>Non Credit</td></tr>';
						}
						
						if (isset($result['refunded']->amount) && !empty($result['refunded']->amount)) {
							$payoutRefundAmount = $result['refunded']->amount + @$result['refunded']->fee + @$result['refunded']->tax;
							//$cashInFee = $cashInFee + @$result['refunded']->fee;
							//$cashInTax = $cashInTax + @$result['refunded']->tax;
							//$cashInCount = $cashInCount + @$result['refunded']->count;
							$cashOutHtml .='<tr><td>'.@$result['refunded']->amount.'</td><td>'.@$result['refunded']->count.'</td><td>'.@$result['refunded']->fee.'</td><td>'.@$result['refunded']->tax.'</td><td>Refunded</td></tr>';
						}
						if (isset($result['cancelled']->amount) && !empty($result['cancelled']->amount)) {
							$cashOutHtml .='<tr><td>'.@$result['cancelled']->amount.'</td><td>'.@$result['cancelled']->count.'</td><td>'.@$result['cancelled']->fee.'</td><td>'.@$result['cancelled']->tax.'</td><td>Cancelled</td></tr>';
						}
						if (isset($result['txn']->amount) && !empty($result['txn']->amount)) {
					
							$cashOutHtml .='<tr><td>'.@$result['txn']->amount.'</td><td>'.@$result['txn']->count.'</td><td>'.@$result['txn']->fee.'</td><td>'.@$result['txn']->tax.'</td><td>Transactions</td></tr>';
						}
						if (isset($result['refund_txn']->amount) && !empty($result['refund_txn']->amount)) {
					
							$cashOutHtml .='<tr><td>'.@$result['refund_txn']->amount.'</td><td>'.@$result['refund_txn']->count.'</td><td>'.@$result['refund_txn']->fee.'</td><td>'.@$result['refund_txn']->tax.'</td><td>RefundTransactions</td></tr>';
						}
						
					}




				}
				// if ($userServices->service_id == VALIDATE_SERVICE_ID) {
				// 	$cashOutHtml .='<tr><td rowspan="4">'.$serviceName->service_name.'</td><td>2000</td><td>20</td><td>5</td><td>2</td><td>Over all</td></tr>
				// 	<tr><td>1500</td><td>14</td><td>3</td><td>1</td><td>Credit</td></tr><tr><td>500</td><td>6</td><td>1</td><td>.5</td><td>Non Credit</td>
				// 	</tr><tr><td>500</td><td>6</td><td>1</td><td>.5</td> <td>Refunded</td></tr>';
				// }

			}
			$result = $this->getRecord($request->user_id, 'srv_1656398681', $request->from, $request->to);
			if (isset($result['total']->amount) && !empty($result['total']->amount)) {
				$cashOutHtml .='<tr><td rowspan="'.$this->getNonEmptyArrayCount($result).'">Auto Settlements</td>
				<td>'.@$result['total']->amount.'</td><td>'.@$result['total']->count.'</td><td>'.@$result['total']->fee.'</td><td>'.@$result['total']->tax.'</td><td>Over all</td></tr>';
				if (isset($result['credit']->amount) && !empty($result['credit']->amount)) {
					$cashOutAmount = $cashOutAmount + $result['credit']->amount;
					$cashOutFee = $cashOutFee + @$result['credit']->fee;
					$cashOutTax = $cashOutTax + @$result['credit']->tax;
					$cashOutCount = $cashOutCount + @$result['credit']->count;
					$cashOutHtml .='<tr><td>'.@$result['credit']->amount.'</td><td>'.@$result['credit']->count.'</td><td>'.@$result['credit']->fee.'</td><td>'.@$result['credit']->tax.'</td><td>Credit</td></tr>';
				}
				
				if (isset($result['nonCredit']->amount) && !empty($result['nonCredit']->amount)) {
					$cashOutHtml .='<tr><td>'.@$result['nonCredit']->amount.'</td><td>'.@$result['nonCredit']->count.'</td><td>'.@$result['nonCredit']->fee.'</td><td>'.@$result['nonCredit']->tax.'</td><td>Non Credit</td></tr>';
				}
				
				if (isset($result['failed']->amount) && !empty($result['failed']->amount)) {
					$payoutRefundAmount = $result['failed']->amount + @$result['failed']->fee + @$result['failed']->tax;
					//$cashInFee = $cashInFee + @$result['refunded']->fee;
					//$cashInTax = $cashInTax + @$result['refunded']->tax;
					//$cashInCount = $cashInCount + @$result['refunded']->count;
					$cashOutHtml .='<tr><td>'.@$result['failed']->amount.'</td><td>'.@$result['failed']->count.'</td><td>'.@$result['failed']->fee.'</td><td>'.@$result['failed']->tax.'</td><td>Failed</td></tr>';
				}
				
				if (isset($result['txn']->amount) && !empty($result['txn']->amount)) {
			
					$cashOutHtml .='<tr><td>'.@$result['txn']->amount.'</td><td>'.@$result['txn']->count.'</td><td>'.@$result['txn']->fee.'</td><td>'.@$result['txn']->tax.'</td><td>Transactions</td></tr>';
				}
				if (isset($result['refund_txn']->amount) && !empty($result['refund_txn']->amount)) {
			
					$cashOutHtml .='<tr><td>'.@$result['refund_txn']->amount.'</td><td>'.@$result['refund_txn']->count.'</td><td>'.@$result['refund_txn']->fee.'</td><td>'.@$result['refund_txn']->tax.'</td><td>RefundTransactions</td></tr>';
				}
				
			}


			$result = $this->getRecord($request->user_id, 'srv_1635429299', $request->from, $request->to);
			if (isset($result['total']->amount) && !empty($result['total']->amount)) {
				$cashInHtml .='<tr><td rowspan="'.$this->getNonEmptyArrayCount($result).'">Patner VAN</td>
				<td>'.@$result['total']->amount.'</td><td>'.@$result['total']->count.'</td><td>'.@$result['total']->fee.'</td><td>'.@$result['total']->tax.'</td><td>Over all</td></tr>';
				if (isset($result['credit']->amount) && !empty($result['credit']->amount)) {
					
					$cashInHtml .='<tr><td>'.@$result['credit']->amount.'</td><td>'.@$result['credit']->count.'</td><td>'.@$result['credit']->fee.'</td><td>'.@$result['credit']->tax.'</td><td>Credit</td></tr>';
				}
				
				if (isset($result['nonCredit']->amount) && !empty($result['nonCredit']->amount)) {
					$cashInHtml .='<tr><td>'.@$result['nonCredit']->amount.'</td><td>'.@$result['nonCredit']->count.'</td><td>'.@$result['nonCredit']->fee.'</td><td>'.@$result['nonCredit']->tax.'</td><td>Non Credit</td></tr>';
				}
				
				if (isset($result['txn']->amount) && !empty($result['txn']->amount)) {
					$cashInAmount = $cashInAmount + $result['txn']->amount;
					$cashInFee = $cashInFee + @$result['txn']->fee;
					$cashInTax = $cashInTax + @$result['txn']->tax;
					$cashInCount = $cashInCount + @$result['txn']->count;
					$cashInHtml .='<tr><td>'.@$result['txn']->amount.'</td><td>'.@$result['txn']->count.'</td><td>'.@$result['txn']->fee.'</td><td>'.@$result['txn']->tax.'</td><td>Transactions</td></tr>';
				}
				
			}
			$result = $this->getRecord($request->user_id, 'srv_1640687279', $request->from, $request->to);
			if (isset($result['total']->amount) && !empty($result['total']->amount)) {
				$cashInHtml .='<tr><td rowspan="'.$this->getNonEmptyArrayCount($result).'">Load Money</td>
				<td>'.@$result['total']->amount.'</td><td>'.@$result['total']->count.'</td><td>'.@$result['total']->fee.'</td><td>'.@$result['total']->tax.'</td><td>Over all</td></tr>';
				if (isset($result['credit']->amount) && !empty($result['credit']->amount)) {
					
					$cashInHtml .='<tr><td>'.@$result['credit']->amount.'</td><td>'.@$result['credit']->count.'</td><td>'.@$result['credit']->fee.'</td><td>'.@$result['credit']->tax.'</td><td>Credit</td></tr>';
				}
				
				if (isset($result['nonCredit']->amount) && !empty($result['nonCredit']->amount)) {
					$cashInHtml .='<tr><td>'.@$result['nonCredit']->amount.'</td><td>'.@$result['nonCredit']->count.'</td><td>'.@$result['nonCredit']->fee.'</td><td>'.@$result['nonCredit']->tax.'</td><td>Non Credit</td></tr>';
				}
				
				if (isset($result['txn']->amount) && !empty($result['txn']->amount)) {
					$cashInAmount = $cashInAmount + $result['txn']->amount;
					$cashInFee = $cashInFee + @$result['txn']->fee;
					$cashInTax = $cashInTax + @$result['txn']->tax;
					$cashInCount = $cashInCount + $result['txn']->count;
					$cashInHtml .='<tr><td>'.@$result['txn']->amount.'</td><td>'.@$result['txn']->count.'</td><td>'.@$result['txn']->fee.'</td><td>'.@$result['txn']->tax.'</td><td>Transactions</td></tr>';
				}
				
			}
			$result = $this->getRecord($request->user_id,'internal_transfer',$request->from,$request->to);
			if (isset($result['credit']->amount) && !empty($result['credit']->amount)) {
					
					$internalTransferHtml .='<tr><td>Internal Transfer</td><td>'.@$result['credit']->amount.'</td><td>'.@$result['credit']->count.'</td><td>'.@$result['credit']->fee.'</td><td>'.@$result['credit']->tax.'</td><td>Credit</td></tr>';
				}

		/*	$cashInHtml .='<tr><td rowspan="4">Load Money</td><td>2000</td><td>20</td><td>5</td><td>2</td><td>Over all</td></tr>
			<tr><td>1500</td><td>14</td><td>3</td><td>1</td><td>Credit</td></tr>
			<tr><td>500</td><td>6</td><td>1</td><td>.5</td><td>Non Credit</td></tr>
			<tr><td>500</td><td>6</td><td>1</td><td>.5</td><td>Refunded</td></tr>'; */

		$table = '<table class="table table-bordered table-striped table-hover" id="datatable">
			<thead>
			<tr><th colspan="6" class="center"></th></tr>
			</thead>
			<tbody>
			<tr>
			<td>Services</td>
		   <td>Total</td>
		   <td>Count</td>
		   <td>Fee</td>
		   <td>Tax</td>
		   <td>Type</td>
		</tr>
			'.$cashInHtml.
		
			$cashOutHtml.'
			</tbody>
			</table>
			<table class="table table-bordered table-striped table-hover" id="datatable1">
			<thead>
			<tr><th colspan="6" class="center">Internal Transfer</th></tr>
			</thead>
			<tbody>
			<tr><td>Services</td>
			<td>Total</td>
		   <td>Count</td>
		   <td>Fee</td>
		   <td>Tax</td>
		   <td>Type</td>
		   </tr>
			'.$internalTransferHtml.'
			</tbody>
			</table>';
			$primaryOpeningBalance = $this->getDayBook($request->user_id,$request->from);
			$primaryClosingBalance = $this->getDayBook($request->user_id,$request->to);
			$payoutOpeningBalance = $this->getDayBook($request->user_id,$request->from);
			$payoutClosingBalance = $this->getDayBook($request->user_id,$request->to);
			
			$payoutAbleAmount = round($cashInAmount,2) + (isset($primaryOpeningBalance->primary_opening_balance)?$primaryOpeningBalance->primary_opening_balance:0) + (isset($payoutOpeningBalance->payout_opening_balance)?$payoutOpeningBalance->payout_opening_balance:0) + $payoutRefundAmount;
			$tottalCashOut = round($cashOutAmount,2) + round($cashInFee,2) + round($cashInTax,2) + round($cashOutFee,2) + round($cashOutTax,2);
			$diff = $payoutAbleAmount - $tottalCashOut;
			$this->data['cashIn'] = $cashInHtml;
			$this->data['cashInAmount'] = '₹'.round($cashInAmount,2).' | '.$cashInCount;
			$this->data['cashOutAmount'] = '₹'.round($cashOutAmount,2).' | '.$cashOutCount;
			$this->data['cashInFeeTax'] = round($cashInFee,2) + round($cashInTax,2);
			$this->data['cashOutFeeTax'] = round($cashOutFee,2) + round($cashOutTax,2);
			$this->data['totalFeeTax'] = '₹'.round(($cashInFee + $cashInTax + $cashOutFee + $cashOutTax),2).' | '.($cashInCount + $cashOutCount);
			$this->data['primaryOpeningBalance'] = (isset($primaryOpeningBalance->primary_opening_balance)?$primaryOpeningBalance->primary_opening_balance:0);
			$this->data['primaryClosingBalance'] = isset($primaryClosingBalance->primary_closing_balance)?$primaryClosingBalance->primary_closing_balance:0;
			$this->data['payoutOpeningBalance'] = (isset($payoutOpeningBalance->payout_opening_balance)?$payoutOpeningBalance->payout_opening_balance:0);
			$this->data['payoutClosingBalance'] = isset($payoutClosingBalance->payout_closing_balance)?$payoutClosingBalance->payout_closing_balance:0;
			$this->data['payoutAbleAmount'] = round($payoutAbleAmount,2);
			$this->data['tottalCashOut'] = round($tottalCashOut,2);
			$this->data['diff'] =  round((($this->data['primaryClosingBalance'] + $this->data['payoutClosingBalance']) - $diff),2);
			$this->data['availablebalance'] = $this->data['primaryClosingBalance'] + $this->data['payoutClosingBalance'];
			$this->data['table'] = $table;
			$this->message = "Record fetched successfully.";
			return response()->json(
				$this->populate([
					'message'   => $this->message,
					'status'    => true,
					'data'      => $this->data
				])
			);
		}
		return response()->json(
			$this->populate([
				'message'   => $this->message,
				'status'    => false,
				'data'      => $this->message
			])
		);
	}

	/**
	 * getRecord
	 *
	 * @param  mixed $userId
	 * @param  mixed $serviceId
	 * @param  mixed $fromDate
	 * @param  mixed $toDate
	 * @return object
	 */
	public function getRecord($userId, $serviceId, $fromDate, $toDate)
	{
		// Over All Amount
		$res['total'] = "";
		$res['credit'] = "";
		$res['nonCredit'] = "";
		$res['refunded'] = "";

		switch ($serviceId) {
			case PAYOUT_SERVICE_ID :
				$total = $this->getOrder($userId, $fromDate,$toDate, [],'order');
				$credit = $this->getOrder($userId, $fromDate,$toDate, ['processed','processing'],'order');
				$nonCredit = $this->getOrder($userId, $fromDate,$toDate, ['queued', 'hold', 'pending'],'order');
				$reversed = $this->getOrder($userId, $fromDate,$toDate, ['reversed'],'order');
				$cancelled = $this->getOrder($userId, $fromDate,$toDate, ['cancelled'],'order');
				$refunded = $this->getOrder($userId, $fromDate,$toDate, ['failed'],'order');
				$txn = $this->getOrder($userId, $fromDate,$toDate, ['failed'],'txn','payout_disbursement');
				$txn_ref = $this->getOrder($userId, $fromDate,$toDate, ['failed'],'txn','payout_refund');
				$res['total']	  = $total['order'];
				$res['credit']	  = $credit['order'];
				$res['nonCredit'] = $nonCredit['order'];
				$res['reversed']  = $reversed['order'];
				$res['cancelled']  = $cancelled['order'];
				$res['refunded']  = $refunded['order'];
				$res['txn'] = $txn['txns'];
				$res['refund_txn'] = $txn_ref['txns'];

			break;
			case AEPS_SERVICE_ID :

				$res['total']	  = $this->getAeps($userId, $fromDate,$toDate, [], 'total');
				$res['credit']	  =$this->getAeps($userId, $fromDate,$toDate, ['success'], 'credit');
				$res['nonCredit'] = $this->getAeps($userId, $fromDate,$toDate, ['success'], 'nonCredit');
				$res['failed']  = $this->getAeps($userId, $fromDate,$toDate, ['failed'], 'refunded');
			break;
			case PARTNER_VAN_SERVICE_ID :

				$total = $this->getPartnerVan($userId,$fromDate,$toDate,'','van');
				$credit = $this->getPartnerVan($userId,$fromDate,$toDate,'1','van');
				$nonCredit = $this->getPartnerVan($userId,$fromDate,$toDate,'0','van');
				$txn = $this->getPartnerVan($userId,$fromDate,$toDate,'0','txn');
				$res['total'] = $total['van'];
				$res['credit'] = $credit['van'];
				$res['nonCredit'] = $nonCredit['van'];
				$res['txn'] = $txn['txn'];

			break;
			case UPI_SERVICE_ID :

				$total = $this->getUPICallbacks($userId,$fromDate,$toDate,'','upi');
				$credit = $this->getUPICallbacks($userId,$fromDate,$toDate,'1','upi');
				$nonCredit = $this->getUPICallbacks($userId,$fromDate,$toDate,'0','upi');
				$txn = $this->getUPICallbacks($userId,$fromDate,$toDate,'0','txn');
				$disputed = $this->getUPICallbacks($userId,$fromDate,$toDate,'1','upi','1');
				$res['total'] = $total['upi'][0];
				$res['credit'] = $credit['upi'][0];
				$res['nonCredit'] = $nonCredit['upi'][0];
				$res['txn'] = $txn['txn'];
				if($disputed)
				{
					$res['disputed'] = $disputed['upi'];
				}

			break;
			case AUTO_COLLECT_SERVICE_ID :

				$total = $this->getSmartCollect($userId,$fromDate,$toDate,'','smart_collect');
				$credit = $this->getSmartCollect($userId,$fromDate,$toDate,'1','smart_collect');
				$nonCredit = $this->getSmartCollect($userId,$fromDate,$toDate,'0','smart_collect');
				$txn = $this->getSmartCollect($userId,$fromDate,$toDate,'0','txn');
				$res['total'] = $total['smart_collect'];
				$res['credit'] = $credit['smart_collect'];
				$res['nonCredit'] = $nonCredit['smart_collect'];
				$res['txn'] = $txn['txn'];

			break;
			case LOAD_MONEY_SERVICE_ID :

				$total = $this->getLoadMoney($userId,$fromDate,$toDate,'','load_money');
				$credit = $this->getLoadMoney($userId,$fromDate,$toDate,'1','load_money');
				$nonCredit = $this->getLoadMoney($userId,$fromDate,$toDate,'0','load_money');
				$txn = $this->getLoadMoney($userId,$fromDate,$toDate,'0','txn');
				$res['total'] = $total['load_money'];
				$res['credit'] = $credit['load_money'];
				$res['nonCredit'] = $nonCredit['load_money'];
				$res['txn'] = $txn['txn'];

			break;
			case VALIDATE_SERVICE_ID :
				$total = $this->getValidationSuite($userId,$fromDate,$toDate,'','validate');
				$credit = $this->getValidationSuite($userId,$fromDate,$toDate,'success','validate');
				$nonCredit = $this->getValidationSuite($userId,$fromDate,$toDate,'pending','validate');
				$txn = $this->getValidationSuite($userId,$fromDate,$toDate,'','txn');
				$res['total'] = $total['validate'];
				$res['credit'] = $credit['validate'];
				$res['nonCredit'] = $nonCredit['validate'];
				$res['txn'] = $txn['txn'];
				break;
			case 'internal_transfer' :
				$res['credit'] = $this->getInternalTransferMoney($userId,$fromDate,$toDate);
				break;
			case AUTO_SETTLEMENT_SERVICE_ID :
				$total = $this->getAutoSettlement($userId, $fromDate,$toDate, [],'order');
				$credit = $this->getAutoSettlement($userId, $fromDate,$toDate, ['processed','processing'],'order');
				$nonCredit = $this->getAutoSettlement($userId, $fromDate,$toDate, ['queued', 'hold', 'pending'],'order');
				//$reversed = $this->getAutoSettlement($userId, $fromDate,$toDate, ['reversed'],'order');
				//$cancelled = $this->getAutoSettlement($userId, $fromDate,$toDate, ['cancelled'],'order');
				$failed = $this->getAutoSettlement($userId, $fromDate,$toDate, ['failed'],'order');
				$txn = $this->getAutoSettlement($userId, $fromDate,$toDate, ['failed'],'txn','stlmnt_disbursement');
				$txn_ref = $this->getAutoSettlement($userId, $fromDate,$toDate, ['failed'],'txn','stlmnt_disbursement');
				$res['total']	  = $total['order'];
				$res['credit']	  = $credit['order'];
				$res['nonCredit'] = $nonCredit['order'];
				$res['failed']  = $failed['order'];
				//$res['cancelled']  = $cancelled['order'];
				//$res['refunded']  = $refunded['order'];
				$res['txn'] = $txn['txns'];
				$res['refund_txn'] = $txn_ref['txns'];
				break;


		}
		return $res;
	}

	/**
	 * getOrder
	 *
	 * @param  mixed $userId
	 * @param  mixed $fromDate
	 * @param  mixed $toDate
	 * @param  mixed $status
	 * @return void
	 */
	public function getOrder($userId, $fromDate, $toDate, $status = [],$type,$identifiers='')
	{
		$res ='';
		$txns = '';
		if($type=='order')
		{
			$res = DB::table('orders');
			$res = $res->where(['user_id' => $userId]);
			if (count($status)) {
				$res = $res->whereIn('status', $status);
			}
			$res = $res->select(DB::raw('SUM(amount) as amount, round(SUM(fee),2) as fee, round(SUM(tax),2) as tax, COUNT(id) as count'));
			if(in_array('failed',$status))
			{
				$res->whereDate('orders.trn_loc_refunded_at', '>=', $fromDate)
					->whereDate('orders.trn_loc_refunded_at', '<=', $toDate)
					->where('orders.trn_loc_refunded','1')
					->where(DB::raw('date(orders.trn_loc_refunded_at)'),'!=',DB::raw('date(orders.created_at)'));
			}else if(in_array('reversed',$status))
			{
				$res->whereDate('orders.trn_reversed_at', '>=', $fromDate)
					->whereDate('orders.trn_reversed_at', '<=', $toDate)
					->where('orders.trn_reversed','1')
					->where(DB::raw('date(orders.trn_reversed_at)'),'!=', DB::raw('date(orders.created_at)'));
			}
			else
			{
				$res->whereDate('orders.created_at', '>=', $fromDate)
					->whereDate('orders.created_at', '<=', $toDate);
			}
					
			$res = $res->first();
		}
		if($type=='txn')
		{
			//DB::enableQueryLog();
			$txns = DB::table('transactions')
					->select(DB::raw('round(sum(tr_amount),2) as amount,round(sum(tr_tax),2) as tax,round(sum(tr_fee),2) as fee,count(id) as count'))
					->where(['user_id' => $userId])
					->where('tr_identifiers',$identifiers)
					->whereDate('transactions.created_at', '>=', $fromDate)
					->whereDate('transactions.created_at', '<=', $toDate)
					->first();
					//dd(\DB::getQueryLog());
		}
		//print_r($txns);
		return ['order'=>$res,'txns'=>$txns];
	}

	/**
	 * getAeps
	 *
	 * @param  mixed $userId
	 * @param  mixed $fromDate
	 * @param  mixed $toDate
	 * @param  mixed $status
	 * @param  mixed $type
	 * @return void
	 */
	public function getAeps($userId, $fromDate, $toDate, $status = [], $type)
	{
		$res = DB::table('aeps_transactions');
		$res = $res->where(['user_id' => $userId]);
		if (count($status)) {
			$res = $res->whereIn('status', $status);

		
			if(in_array('success',$status))
			{
				$res = $res->where('is_trn_credited', '1');
			}
		}
		if ($type == 'nonCredit' || $type == 'refunded') {
			$res = $res->where('is_trn_credited', '0');
		}
		$res = $res->whereIn('transaction_type', ['cw', 'ms']);
		$res = $res->select(DB::raw('SUM(transaction_amount) as amount, COUNT(id) as count'))
				->whereDate('aeps_transactions.trn_credited_at', '>=', $fromDate)
				->whereDate('aeps_transactions.trn_credited_at', '<=', $toDate)
				->first();
		return $res;
	}

	/**
	 * getPartnerVan
	 *
	 * @param  mixed $userId
	 * @param  mixed $fromDate
	 * @param  mixed $toDate
	 * @param  mixed $status
	 * @return void
	 */
	public function getPartnerVan($userId, $fromDate, $toDate, $status ='',$type)
	{
		$res ='';
		$txn = '';
		if($type=='van')
		{
			DB::enableQueryLog();
			$res = DB::table('fund_receive_callbacks')
					->where(['user_id' => $userId])
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate);
			if($status!='')
			{
				$res->where('is_trn_credited',$status);
			}
			$res = $res->select(DB::raw('sum(amount) as amount,round(SUM(fee),2) as fee, round(SUM(tax),2) as tax,COUNT(id) as count'))->first();
			//dd(\DB::getQueryLog());
			//Storage::put('reconcile'.time().'.txt', print_r(DB::getQueryLog(), true));
		}
		if($type=='txn'){
			$txn = DB::table('transactions')
					->select(DB::raw('sum(tr_amount) as amount,round(SUM(tr_fee),2) as fee, round(SUM(tr_tax),2) as tax,COUNT(id) as count'))
					->where('user_id',$userId)
					->whereIn('tr_identifiers',['van_inward_credit','eb_van_inward_credit'])
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate)->first();
		}


		return ['van'=>$res,'txn'=>$txn];

	}

	/**
	 * getUPICallbacks
	 *
	 * @param  mixed $userId
	 * @param  mixed $fromDate
	 * @param  mixed $toDate
	 * @param  mixed $status
	 * @param  mixed $type
	 * @return void
	 */

	public function getUPICallbacks($userId, $fromDate, $toDate, $status ='',$type,$disputed='')
	{
		$res = '';
		$txn = '';

		if($type=='upi')
		{
			DB::enableQueryLog();
			// $res = DB::table('upi_callbacks')
			// 		->where(['user_id' => $userId]);
			// if($disputed=='')
			// {

			// 	$res->where('is_trn_disputed','0');
			// }else
			// {
			// 	$res->where('is_trn_disputed','1');
			// }
			// if($status =='1')
			// {
			// 	$res->whereDate('trn_credited_at','>=',$fromDate)
			// 		->whereDate('trn_credited_at','<=',$toDate);
			// }
			// else
			// {
				
			// 	$res->whereDate('created_at','>=',$fromDate)
			// 		->whereDate('created_at','<=',$toDate);
			// }
			// if($status!='')
			// {
			// 	$res->where('is_trn_credited',$status);
			// }
			//$res = $res->select(DB::raw('sum(amount) as amount,round(SUM(fee),2) as fee, round(SUM(tax),2) as tax,COUNT(id) as count'))->first();
			$query = "SELECT count(*) as count, sum(amount) as amount, round(SUM(fee),2) as fee, round(SUM(tax),2) as tax, DATE(`created_at`) FROM (SELECT DISTINCT customer_ref_id, amount,fee,tax, created_at FROM upi_callbacks where user_id=$userId";
			if($disputed=='')
			{

				$query .= " and is_trn_disputed = '0'";
			}else
			{
				$query .= " and is_trn_disputed = '1'";
			}
			if($status =='1')
			{
				$query .= " and date(trn_credited_at) >='".$fromDate."' and date(trn_credited_at) <='".$toDate."'";
			}
			else
			{
				
				$query .= " and date(created_at) >='".$fromDate."' and (created_at) <='".$toDate."'";
			}
			if($status!='')
			{
				$query .= " and is_trn_credited = '".$status."'";
				
			}
			$query .=" group by customer_ref_id) t";
			$res = DB::select($query);
			//dd(\DB::getQueryLog());
		}
		if($type=='txn'){
			$txn = DB::table('transactions')
					->select(DB::raw('sum(tr_amount) as amount,round(SUM(tr_fee),2) as fee, round(SUM(tr_tax),2) as tax,COUNT(id) as count'))
					->where('user_id',$userId)
					->whereIn('tr_identifiers',['upi_inward_credit','upi_stack_vpa_fee','upi_stack_verify_fee'])
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate)->first();
		}


		return ['upi'=>$res,'txn'=>$txn];
		
	}

	/**
	 * getSmartCollect
	 *
	 * @param  mixed $userId
	 * @param  mixed $fromDate
	 * @param  mixed $toDate
	 * @param  mixed $status
	 * @param  mixed $type
	 * @return void
	 */

	public function getSmartCollect($userId, $fromDate, $toDate, $status ='',$type)
	{
		$res = '';
		$txn = '';

		if($type=='smart_collect')
		{
			DB::enableQueryLog();
			$res = DB::table('cf_merchants_fund_callbacks')
					->where(['user_id' => $userId])
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate);
			if($status!='')
			{
				$res->where('is_trn_credited',$status);
			}
			$res = $res->select(DB::raw('sum(amount) as amount,round(SUM(fee),2) as fee, round(SUM(tax),2) as tax,COUNT(id) as count'))->first();
			//dd(\DB::getQueryLog());
		}
		if($type=='txn'){
			$txn = DB::table('transactions')
					->select(DB::raw('sum(tr_amount) as amount,round(SUM(tr_fee),2) as fee, round(SUM(tr_tax),2) as tax,COUNT(id) as count'))
					->where('user_id',$userId)
					->whereIn('tr_identifiers',['smart_collect_vpa','smart_collect_van_fee','smart_collect_vpa_fee','smart_collect_van'])
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate)->first();
		}


		return ['smart_collect'=>$res,'txn'=>$txn];
		
	}

	/**
	 * getLoadMoney
	 *
	 * @param  mixed $userId
	 * @param  mixed $fromDate
	 * @param  mixed $toDate
	 * @param  mixed $status
	 * @param  mixed $type
	 * @return void
	 */
	public function getLoadMoney($userId, $fromDate, $toDate, $status ='',$type)
	{
		$res = '';
		$txn = '';

		if($type=='load_money')
		{
			DB::enableQueryLog();
			$res = DB::table('load_money_request')
					->where(['user_id' => $userId])
					->whereDate('trn_credited_at','>=',$fromDate)
					->whereDate('trn_credited_at','<=',$toDate);
			if($status!='')
			{
				$res->where('is_trn_credited',$status);
			}
			$res = $res->select(DB::raw('sum(amount) as amount,COUNT(id) as count'))->first();
			//dd(\DB::getQueryLog());
		}
		if($type=='txn'){
			$txn = DB::table('transactions')
					->select(DB::raw('sum(tr_amount) as amount,round(SUM(tr_fee),2) as fee, round(SUM(tr_tax),2) as tax,COUNT(id) as count'))
					->where('user_id',$userId)
					->where('tr_identifiers','load_fund_credit')
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate)->first();
		}


		return ['load_money'=>$res,'txn'=>$txn];
	}

	/**
	 * getInternalTransferMoney
	 *
	 * @param  mixed $userId
	 * @param  mixed $fromDate
	 * @param  mixed $toDate
	 * @return void
	 */
	public function getInternalTransferMoney($userId, $fromDate, $toDate)
	{
		$txn = DB::table('transactions')
					->select(DB::raw('sum(tr_amount) as amount,round(SUM(tr_fee),2) as fee, round(SUM(tr_tax),2) as tax,COUNT(id) as count'))
					->where('user_id',$userId)
					->where('tr_identifiers','internal_transfer')
					->where('tr_type','cr')
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate)->first();

		return $txn;
	}

	/**
	 * getDayBook
	 *
	 * @param  mixed $userId
	 * @param  mixed $date
	 * @return void
	 */
	public function getDayBook($userId,$date)
	{
		DB::enableQueryLog();
		$txn = DB::table('day_books')
					->select('primary_opening_balance','primary_closing_balance','payout_opening_balance','payout_closing_balance')
					->where('user_id',$userId)
					->whereDate('record_date','=',$date)
					->first();
					//dd(\DB::getQueryLog());
		return $txn;
	}


	public function getValidationSuite($userId, $fromDate, $toDate,$status='',$type)
	{
		$res = '';
		$txn = '';
		if($type=='validate')
		{
			$res = DB::table('validations')
					->select(DB::raw('sum(fee) as fee,sum(tax) as tax'))
					->where(['user_id' => $userId])
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate);
			if($status!='')
			{
				$res->where('status',$status);
			}
			$res = $res->first();	
		}
		else
		{
			$txn = DB::table('transactions')
					->select(DB::raw('sum(tr_fee) as fee,sum(tr_tax) as tax'))
					->whereIn('tr_identifiers',['verification_ifsc_debit','verification_bank_debit','verification_vpa_debit','verification_pan_debit','verification_aadhaar_debit'])
					->whereDate('created_at','>=',$fromDate)
					->whereDate('created_at','<=',$toDate)->first();
		}

		return ['validate'=>$res,'txn'=>$txn];
		
	}

	public function getNonEmptyArrayCount($array)
	{

		if(!empty($array))
		{
			$i=0;
			foreach($array as $key=>$val)
			{
				//echo $key;
				//print_r($array);
				if(!empty($array[$key]->amount) || !empty($array[$key]->tax) || !empty($array[$key]->fee))
				{
					$i++;
				}
			}
			return $i;
		}

	}

	public function getAutoSettlement($userId, $fromDate, $toDate,$status= [],$type,$identifiers='')
	{
		$res ='';
		$txns = '';
		// DB::enableQueryLog();
		// echo $type;
		if($type=='order')
		{
			$res = DB::table('user_settlements');
			$res = $res->where(['user_id' => $userId]);
			if (count($status)) {
				$res = $res->whereIn('status', $status);
			}
			$res = $res->select(DB::raw('SUM(amount) as amount, round(SUM(fee),2) as fee, round(SUM(tax),2) as tax, COUNT(id) as count'));
			if(in_array('failed',$status))
			{
				$res->whereDate('user_settlements.created_at', '>=', $fromDate)
					->whereDate('user_settlements.created_at', '<=', $toDate);
					
			}else if(in_array('hold',$status))
			{
				$res->whereDate('user_settlements.created_at', '>=', $fromDate)
					->whereDate('user_settlements.created_at', '<=', $toDate);
					
			}
			else
			{
				$res->whereDate('user_settlements.created_at', '>=', $fromDate)
					->whereDate('user_settlements.created_at', '<=', $toDate);
			}
					
			$res = $res->first();
			//dd(\DB::getQueryLog());
		}
		if($type=='txn')
		{
			//DB::enableQueryLog();
			$txns = DB::table('transactions')
					->select(DB::raw('round(sum(tr_amount),2) as amount,round(sum(tr_tax),2) as tax,round(sum(tr_fee),2) as fee,count(id) as count'))
					->where(['user_id' => $userId])
					->where('tr_identifiers',$identifiers)
					->whereDate('transactions.created_at', '>=', $fromDate)
					->whereDate('transactions.created_at', '<=', $toDate)
					->first();
					//dd(\DB::getQueryLog());
		}
		//print_r($txns);
		return ['order'=>$res,'txns'=>$txns];
	}

}
