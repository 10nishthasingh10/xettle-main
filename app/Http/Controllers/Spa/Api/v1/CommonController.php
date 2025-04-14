<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommonController extends Controller
{
	public function statesList(Request $request)
	{
		$statesList = DB::table('states')->where('country_id', '1')->where('is_active', '1')->get();
		if ($statesList->isNotEmpty()) {
			return ResponseHelper::success('Record fetched successfully.', $statesList);
		}
		return ResponseHelper::failed('Record not found.');
	}

	public function getBusinessTypeList(Request $request)
	{
		$list = CommonHelper::businessTypeList();
		return ResponseHelper::success('Record fetched successfully.', $list);
	}

	public function accountManagerList(Request $request)
	{
		$data = DB::table('account_managers')->where('is_active', '1')->where('type', 'account_manager')->get();
		if ($data->isNotEmpty()) {
			return ResponseHelper::success('Record fetched successfully.', $data);
		}
		return ResponseHelper::failed('Something Went Wrong.');
	}

	public function businessCategoryList(Request $request)
	{
		$businessCategoryList = DB::table('business_categories')
			->select('id', 'name', 'is_parent', 'is_active', 'created_at')
			->where('is_active', '1')
			->where('is_parent', '1')
			->get();
		if ($businessCategoryList->isNotEmpty()) {

			foreach ($businessCategoryList as $val) {
				$subCategory = DB::table('business_categories')
					->select('id', 'name', 'is_parent', 'is_active', 'created_at')
					->where('parent_id', $val->id)
					->where('is_active', '1')
					->get();
				$val->subCategory = $subCategory;
			}

			return ResponseHelper::success('Record fetched successfully.', $businessCategoryList);
		}

		return ResponseHelper::failed('Record not found.');
	}

	public function allWalletTransactions(Request $request)
	{
		$userId = $request->user()->id;
		$transaction = DB::table('transactions')
			->select(
				'txn_id',
				'trans_id',
				'txn_ref_id',
				'account_number',
				'tr_amount',
				'tr_fee',
				'tr_tax',
				'closing_balance',
				'tr_identifiers',
				// 'transactions.created_at',
				DB::raw("DATE_FORMAT(transactions.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
				'global_services.service_name'
			)
			->join('global_services', 'global_services.service_id', '=', 'transactions.service_id')
			->where('transactions.user_id', $userId)
			// ->whereIn('transactions.tr_identifiers', ['upi_ocr_credit', 'upi_validate_credit'])
			->orderBy('transactions.id', 'desc')
			->take(10)
			->get();

		if ($transaction->isNotEmpty()) {
			return ResponseHelper::success('Record fetched successfully.', $transaction);
		}
		return ResponseHelper::failed('Record not found.');
	}


	public function checkServiceStatus()
	{
		try {
			$validator = Validator::make(
				request()->all(),
				[
					'serviceName' => "required|in:requestMoney"
				]
			);

			if ($validator->fails()) {
				$message = json_decode(json_encode($validator->errors()), true);
				return ResponseHelper::missing($message);
			}

			$serviceName = request()->get('serviceName');
			$userId = Auth::user()->id;

			switch ($serviceName) {
				case 'requestMoney':
					$isActive = CommonHelper::isLoadMoneyRequestActive($userId);
					return ResponseHelper::success('Service status found', ['status' => $isActive]);
					break;

				default:
					return ResponseHelper::failed('Wrong Service found.');
					break;
			}
		} catch (Exception $e) {
			return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
		}
	}
}
