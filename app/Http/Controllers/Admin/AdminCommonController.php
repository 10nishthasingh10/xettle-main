<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminCommonController extends Controller
{

	/**
	 * Fetch User Lean Amount
	 */
	public function fetchLeanAmount(Request $request)
	{
		try {
			$validator = Validator::make(
				$request->all(),
				[
					'user_id' => "required"
				]
			);

			if ($validator->fails()) {
				$message = json_encode($validator->errors()->all());
				return ResponseHelper::missing($message);
			}

			$userId = decrypt($request->user_id);

			$returnData['dr'] = 0;
			$returnData['cr'] = 0;

			$checkBal = DB::table('lean_mark_transactions')
				->select(
					DB::raw("`user_id`, `txn_type`, sum(`amount`) as amt")
				)
				->where('user_id', $userId)
				// ->where('status', '1')
				->groupBy('txn_type')
				->get();


			if ($checkBal->isEmpty()) {
				return ResponseHelper::failed('No Lean mark amount found', $returnData);
			}

			foreach ($checkBal as $row) {
				if ($row->txn_type == "dr") {
					$returnData['dr'] = round($row->amt, 2);
				} else if ($row->txn_type == "cr") {
					$returnData['cr'] = abs(round($row->amt, 2));
				}
			}

			return ResponseHelper::success('Lean mark amount found', $returnData);
		} catch (Exception $e) {
			return ResponseHelper::swwrong($e->getMessage());
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param Request $request
	 * @param [type] $type
	 * @param integer $id
	 * @param string $returnType
	 * @return void
	 */
	public function fetchData(Request $request, $type, $id = 0, $returnType = "all")
	{
		$request['return'] = 'all';
		$request->orderIdArray = [];
		$request->serviceIdArray = [];
		$request->userIdArray = [];
		$request->adminUserIdArray = [];
		$request['returnType'] = $returnType;
		$parentData = session('parentData');
		$request['where'] = 0;
		$request['type'] = $type;
		switch ($type) {

			case 'aepsTransaction':
				$request['table'] = '\App\Models\AepsTransaction';
				$request['tableName'] = 'aeps_transactions';
				$request['searchData'] = [
					'bankiin',
					'client_ref_id',
					'route_type',
					'transaction_type',
					'resp_bank_message',
					'rrn',
					'transaction_amount',
					'resp_stan_no',
					'transaction_date',
					'merchant_code',
					'mobile_no',
					'trn_ref_id',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user', 'merchant'];
				//$request->status = 'success';
				if (!isset($request['from']) && empty($request['from'])) {
					$request['from'] = date('Y-m-d');
				}
				if (!isset($request['to']) && empty($request['to'])) {
					$request['to'] = date('Y-m-d');
				}
				$orderIndex = $request->get('order');

				if (isset($orderIndex) && count($orderIndex)) {
					$columnsIndex = $request->get('columns');
					$columnIndex = $orderIndex[0]['column']; // Column index
					$columnName = $columnsIndex[$columnIndex]['data']; // Column name
					$columnSortOrder = $orderIndex[0]['dir']; // asc or desc
					if ($columnName == 'new_created_at') {
						$columnName = 'created_at';
					}
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['totalAmount', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

			case 'aepsUsers':
				$request['table'] = '\App\Models\AepsTransaction';
				$request['tableName'] = 'aeps_transactions';
				$request['searchData'] = ['name', 'email', 'mobile', 'created_at'];
				$request['select'] = 'all';
				$request['with'] = ['aeps_transactions', 'merchant'];
				//$request->status = 'success';
				if (!isset($request['from']) && empty($request['from'])) {
					$request['from'] = date('Y-m-d');
				}
				if (!isset($request['to']) && empty($request['to'])) {
					$request['to'] = date('Y-m-d');
				}
				$orderIndex = $request->get('order');

				if (isset($orderIndex) && count($orderIndex)) {
					$columnsIndex = $request->get('columns');
					$columnIndex = $orderIndex[0]['column']; // Column index
					$columnName = $columnsIndex[$columnIndex]['data']; // Column name
					$columnSortOrder = $orderIndex[0]['dir']; // asc or desc
					if ($columnName == 'new_created_at') {
						$columnName = 'created_at';
					}
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['totalAmount', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;
			case 'recharge':
				$request['table'] = '\App\Models\Recharge';
				$request['tableName'] = 'recharges';
				$request['searchData'] = ['stan_no', 'order_ref_id', 'phone', 'created_at'];
				$request['select'] = 'all';
				//$request['with'] = ['aeps_transactions', 'merchant'];
				//$request->status = 'success';
				if (!isset($request['from']) && empty($request['from'])) {
					$request['from'] = date('Y-m-d');
				}
				if (!isset($request['to']) && empty($request['to'])) {
					$request['to'] = date('Y-m-d');
				}
				$orderIndex = $request->get('order');

				if (isset($orderIndex) && count($orderIndex)) {
					$columnsIndex = $request->get('columns');
					$columnIndex = $orderIndex[0]['column']; // Column index
					$columnName = $columnsIndex[$columnIndex]['data']; // Column name
					$columnSortOrder = $orderIndex[0]['dir']; // asc or desc
					if ($columnName == 'new_created_at') {
						$columnName = 'created_at';
					}
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['totalAmount', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;
			case 'validation':
				$request['table'] = '\App\Models\Validation';
				$request['tableName'] = 'validations';
				$request['searchData'] = ['stan_no', 'order_ref_id', 'phone', 'created_at'];
				$request['select'] = 'all';
				//$request['with'] = ['aeps_transactions', 'merchant'];
				//$request->status = 'success';
				if (!isset($request['from']) && empty($request['from'])) {
					$request['from'] = date('Y-m-d');
				}
				if (!isset($request['to']) && empty($request['to'])) {
					$request['to'] = date('Y-m-d');
				}
				$orderIndex = $request->get('order');

				if (isset($orderIndex) && count($orderIndex)) {
					$columnsIndex = $request->get('columns');
					$columnIndex = $orderIndex[0]['column']; // Column index
					$columnName = $columnsIndex[$columnIndex]['data']; // Column name
					$columnSortOrder = $orderIndex[0]['dir']; // asc or desc
					if ($columnName == 'new_created_at') {
						$columnName = 'created_at';
					}
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['totalAmount', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

			case 'lean_mark_txn':
				$request['table'] = '\App\Models\LeanMarkTransaction';
				$request['searchData'] = [
					'txn_type',
					'amount',
					'opening_balance',
					'closing_balance',
					'narration',
					'udf1',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$orderIndex = $request->get('order');

				if (isset($orderIndex) && count($orderIndex)) {
					$columnsIndex = $request->get('columns');
					$columnIndex = $orderIndex[0]['column']; // Column index
					$columnName = $columnsIndex[$columnIndex]['data']; // Column name
					$columnSortOrder = $orderIndex[0]['dir']; // asc or desc
					if ($columnName == 'new_created_at') {
						$columnName = 'created_at';
					}
					if ($columnName == '0' || $columnName == 'order_ref_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}

				// $request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				$request['parentData'] = [$id];
				// dd($id);


				break;
			case 'dmtTransaction':
				$request['table'] = '\App\Models\DMTFundTransfer';
				$request['tableName'] = 'dmt_fund_transfers';
				$request['searchData'] = [
					'client_ref_id',
					'outlet_id',
					'mobile',
					'bene_id',
					'utr',
					'mode',
					'amount',
					'fee',
					'tax',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				//$request['with'] = ['user', 'merchant'];
				//$request->status = 'success';
				if (!isset($request['from']) && empty($request['from'])) {
					$request['from'] = date('Y-m-d');
				}
				if (!isset($request['to']) && empty($request['to'])) {
					$request['to'] = date('Y-m-d');
				}
				$orderIndex = $request->get('order');

				if (isset($orderIndex) && count($orderIndex)) {
					$columnsIndex = $request->get('columns');
					$columnIndex = $orderIndex[0]['column']; // Column index
					$columnName = $columnsIndex[$columnIndex]['data']; // Column name
					$columnSortOrder = $orderIndex[0]['dir']; // asc or desc
					if ($columnName == 'new_created_at') {
						$columnName = 'created_at';
					}
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['totalAmount', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;
			case 'panCard':
				$request['table'] = '\App\Models\PanCardTransaction';
				$request['tableName'] = 'pan_txns';
				$request['searchData'] = ['psa_code', 'txn_id', 'order_ref_id', 'txn_type', 'fee as totalAmount',];
				$request['select'] = 'all';
				//$request['with'] = ['user', 'merchant'];
				//$request->status = 'success';
				if (!isset($request['from']) && empty($request['from'])) {
					$request['from'] = date('Y-m-d');
				}
				if (!isset($request['to']) && empty($request['to'])) {
					$request['to'] = date('Y-m-d');
				}
				$orderIndex = $request->get('order');

				if (isset($orderIndex) && count($orderIndex)) {
					$columnsIndex = $request->get('columns');
					$columnIndex = $orderIndex[0]['column']; // Column index
					$columnName = $columnsIndex[$columnIndex]['data']; // Column name
					$columnSortOrder = $orderIndex[0]['dir']; // asc or desc
					if ($columnName == 'new_created_at') {
						$columnName = 'created_at';
					}
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['totalAmount', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

		}

		try {
			$totalData = $this->getData($request, 'count');
		} catch (\Exception $e) {
			$totalData = 0;
		}
		if (isset($request->search['value'])) {
			$request->searchText = $request->search['value'];
		}
		if (isset($request->userId)) {
			$request->adminUserIdArray = $request->userId;
		}

		if (
			(isset($request->searchText) && !empty($request->searchText)) ||
			(isset($request->to) && !empty($request->to)) ||
			(isset($request->tr_type) && !empty($request->tr_type)) ||
			(isset($request->account_number) && !empty($request->account_number)) ||
			(isset($request->from) && !empty($request->from)) ||
			(isset($request->status) && $request->status != '') ||
			(isset($request->integration_id) && $request->integration_id != '') ||
			(isset($request->is_active) && $request->is_active != '') ||
			(isset($request->userId) && $request->userId != '') ||
			(isset($request->user_id) && !empty($request->user_id))
		) {
			$request['where'] = 1;
		}
		try {
			$totalFiltered = $this->getData($request, 'count');
		} catch (\Exception $e) {
			$totalFiltered = 0;
		}

		try {
			$data = $this->getData($request, 'data');
		} catch (\Exception $e) {
			dd($e->getMessage());
			$data = [];
		}
		if ($request->return == "all" || $returnType == "all") {
			if ($request->type == 'aepsTransaction' || $request->type == 'aepsUsers') {
				$totalData = $totalFiltered;
			}

			$json_data = array(
				"draw" => intval($request['draw']),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data
			);
			echo json_encode($json_data);
		} else {
			return response()->json($data);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $request
	 * @param [type] $returnType
	 * @return void
	 */
	public function getData($request, $returnType)
	{
		$table = $request->table;
		$data = $table::query();
		$tableName = $request->tableName;
		$data->orderBy($request->order[0], $request->order[1]);
		if ($request->parentData != 'all') {
			if (!is_array($request->whereIn)) {
				$data->whereIn($request->whereIn, $request->parentData);
			} else {
				$data->where(function ($query) use ($request) {
					$query->where($request->whereIn[0], $request->parentData)
						->orWhere($request->whereIn[1], $request->parentData);
				});
			}
		}

		switch ($request->type) {

			case 'aepsTransaction':
				$data->select('*', DB::raw('SUM(aeps_transactions.transaction_amount) as totalAmount, COUNT(aeps_transactions.merchant_code) as counts'));
				$data->groupBy('merchant_code');
				$data->where('status', 'success');
				break;

			case 'aepsUsers':
				/*	$data->leftJoin('aeps_transactions', 'users.id', 'aeps_transactions.user_id');
										 $data->leftJoin('agents', 'agents.merchant_code', 'aeps_transactions.merchant_code');
										 $data->select('agents.first_name', 'agents.merchant_code', 'agents.middle_name','agents.merchant_code', 'agents.last_name', 'users.id', 'users.name', 'users.email', 'aeps_transactions.created_at', 'users.mobile', DB::raw('SUM(aeps_transactions.transaction_amount) as totalAmount, COUNT(aeps_transactions.merchant_code) as counts'));
										 $data->groupBy('users.id');
										 $data->with(array('aepsAgents' => function($query) use ( $request){
										 
											 //$query->select( DB::raw('SUM(aeps_transactions.transaction_amount) as totalAmount, COUNT(aeps_transactions.merchant_code) as counts'));
											 //$query->groupBy('aeps_transactions.merchant_code');
									 
										 $query->whereBetween('aeps_transactions.created_at', [Carbon::createFromFormat('Y-m-d', $request->from)
										 ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)]);
											 $query->leftJoin('aeps_transactions',  'aeps_transactions.merchant_code', 'agents.merchant_code');
											 $query->sum( 'aeps_transactions.transaction_amount');
										 }));
										 $data->where('aeps_transactions.status', 'success');
									 //	dd($data->get()); */
				$data->join('users', 'users.id', 'aeps_transactions.user_id');
				$data->select(DB::raw("count(aeps_transactions.id) counts, sum(aeps_transactions.transaction_amount) as totalAmount,COUNT(DISTINCT aeps_transactions.merchant_code) as active_agents,aeps_transactions.user_id,users.name,users.email,users.created_at, sum(case when
				(aeps_transactions.is_trn_credited='1')then (aeps_transactions.transaction_amount) else 0 end) as credited_amount, (select count(DISTINCT merchant_code) from agents where user_id=aeps_transactions.user_id)
				as total_agents"));
				$data->where('aeps_transactions.status', 'success');
				$data->whereIn('aeps_transactions.transaction_type', ['cw', 'ms', 'be']);
				$data->groupBy('aeps_transactions.user_id');
				break;
			case 'recharge':
				$data->join('users', 'users.id', 'recharges.user_id');
				$data->select(DB::raw("count(recharges.id) as counts,sum(recharges.amount) as totalAmount,recharges.user_id,users.name,users.email,users.created_at"));
				$data->whereIn('recharges.status', ['processed', 'pending']);
				$data->groupBy('recharges.user_id');
				break;
			case 'validation':
				$data->join('users', 'users.id', 'validations.user_id');
				$data->select(DB::raw("count(validations.id) as counts,sum(validations.fee + validations.tax) as totalAmount,validations.user_id,users.name,users.email,users.created_at"));
				$data->whereIn('validations.status', ['success', 'pending']);
				$data->groupBy('validations.user_id');
				break;
			case 'dmtTransaction':
				$data->join('users', 'users.id', 'dmt_fund_transfers.user_id');
				$data->select(DB::raw("count(dmt_fund_transfers.id) as counts,sum(dmt_fund_transfers.amount + dmt_fund_transfers.fee + dmt_fund_transfers.tax) as totalAmount,dmt_fund_transfers.user_id,users.name,users.email,users.created_at"));
				$data->whereIn('dmt_fund_transfers.status', ['processed', 'processing']);
				$data->groupBy('dmt_fund_transfers.user_id');
				break;
			case 'panCard':
				$data->join('users', 'users.id', 'pan_txns.user_id');
				$data->select(DB::raw("count(pan_txns.id) as counts,round(sum(pan_txns.fee + pan_txns.tax),2) as totalAmount,pan_txns.user_id,users.name,users.email,users.created_at"));
				$data->whereIn('pan_txns.status', ['success','pending']);
				$data->groupBy('pan_txns.user_id');
				break;
		}

		if ($request->where) {
			if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
				if ($request->from == $request->to) {
					$data->whereDate($tableName . '.created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
				} else {
					$data->whereBetween($tableName . '.created_at', [
						Carbon::createFromFormat('Y-m-d', $request->from)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
					]);
				}
			}

			if (isset($request->user_id) && !empty($request->user_id)) {
				$data->where('user_id', $request->user_id);
			}

			if (!empty($request->searchText)) {
				$data->where(function ($q) use ($request) {
					foreach ($request->searchData as $value) {
						$q->orWhere($value, 'like', $request->searchText . '%');
						$q->orWhere($value, 'like', '%' . $request->searchText . '%');
						$q->orWhere($value, 'like', '%' . $request->searchText);
					}
				});
			}
		}

		if ($request->return == "all" || $request->returnType == "all") {
			if ($returnType == "count") {
				return count($data->get());
			} else {
				if ($request['length'] != -1) {
					$data->skip($request['start'])->take($request['length']);
				}
				if ($request->select == "all") {


					$data = $data->get();

					foreach ($data as $key => $value) {
						if (isset($value->aadhar_number) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->aadhar_number = CommonHelper::masking('aadhar', $value->aadhar_number);
						}
						if (isset($value->mobile) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->mobile = CommonHelper::masking('mobile', $value->mobile);
						}

						if (isset($value->aadhaar_no) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->aadhaar_no = CommonHelper::masking('aadhar', $value->aadhaar_no);
						}
						if (isset($value->pan_no) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->pan_no = CommonHelper::masking('pan', $value->pan_no);
						}
						if (isset($value->updated_at)) {
							$value->updated_at = $value->updated_at->format('Y-m-d H:i:s');
						}

						$value->new_created_at = $value->created_at->format('Y-m-d H:i:s');
						if (isset($value->merchant->aadhar_number) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->merchant->aadhar_number = CommonHelper::masking('aadhar', $value->merchant->aadhar_number);
						}
						if (isset($value->merchant->pan_no) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->merchant->pan_no = CommonHelper::masking('aadhar', $value->merchant->pan_no);
						}
						if (isset($value->merchant->mobile) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->merchant->mobile = CommonHelper::masking('mobile', $value->merchant->mobile);
						}
					}
					return $data;
				} else {
					return $data->select($request->select)->get();
				}
			}
		} else {
			if ($request->select == "all") {
				return $data->first();
			} else {
				return $data->select($request->select)->first();
			}
		}
	}



	public static function getUserId($search)
	{
		$data = [];
		$User = User::select('id')
			->where('name', 'like', $search . '%')
			->orWhere('name', 'like', '%' . $search)
			->orWhere('name', 'like', '%' . $search . '%')
			->orWhere('email', 'like', $search . '%')
			->orWhere('email', 'like', '%' . $search)
			->orWhere('email', 'like', '%' . $search . '%')
			->orWhere('mobile', 'like', $search . '%')
			->orWhere('mobile', 'like', '%' . $search)
			->orWhere('mobile', 'like', '%' . $search . '%')
			->get();
		foreach ($User as $Users) {
			$data[] = $Users->id;
		}
		return $data;
	}


}