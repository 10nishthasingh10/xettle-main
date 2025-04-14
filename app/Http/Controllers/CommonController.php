<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Service;
use App\Models\BulkPayoutDetail;
use App\Models\RechargeBack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
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
			case 'orders':
				$request['table'] = '\App\Models\Order';
				$request['searchData'] = [
					'order_ref_id',
					'user_id',
					'client_ref_id',
					'integration_id',
					'order_id',
					'batch_id',
					'mode',
					'amount',
					'fee',
					'tax',
					'reseller_commision',
					'bank_reference',
					'status',
					'area',
					'created_at',
					'payout_id',
					'contact_id'
				];
				$request['select'] = 'all';
				$request['with'] = ['user'];
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
				if(\Auth::user()->is_admin == '1' && Auth::user()->hasRole('reseller')) {
					$userIds = CommonHelper::getUsersAssignedToReseller();
					$request['whereIn'] = 'user_id';
					$request['parentData'] = $userIds;
				}
				elseif (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;
			case 'settlementorders':
				$request['table'] = '\App\Models\UserSettlement';
				$request['searchData'] = [
					'settlement_ref_id',
					'amount',
					'status',
					'user_id',
					'fee',
					'tax',
					'mode',
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
					if ($columnName == '0' || $columnName == 'settlement_ref_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;

			case 'bulkpayouts':
				$request['table'] = '\App\Models\BulkPayout';
				$request['searchData'] = ['batch_id', 'filename', 'total_amount', 'status', 'created_at', 'total_count'];
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
					if ($columnName == '0' || $columnName == 'batch_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;

			case 'contacts':
				$request['table'] = '\App\Models\Contact';
				$request['searchData'] = ['contact_id', 'account_number', 'vpa_address', 'phone', 'first_name', 'last_name', 'email', 'account_type', 'created_at'];
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
					if ($columnName == '0' || $columnName == 'contact_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;

			case 'merchants':
				$request['table'] = '\App\Models\UPIMerchant';
				$request['searchData'] = [
					'root_type',
					'request_id',
					'merchant_business_name',
					'merchant_virtual_address',
					'mobile',
					'pan_no',
					'contact_email',
					'gstn',
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				$request['root_type_in'] = ['ibl', 'fpay'];
				break;

				case 'Integration':
					$request['table'] = '\App\Models\Integration';
					$request['searchData'] = [
						'integration_id',
						'name',
						'slug',
						'is_active',
						'created_at',
						'updated_at'
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
						if ($columnName == '0') {
							$columnName = 'created_at';
							$columnSortOrder = 'DESC'; // asc or desc
						}
						$request['order'] = [$columnName, $columnSortOrder];
					} else {
						$request['order'] = ['id', 'DESC'];
					}
					if (Auth::user()->is_admin == '1') {
						$request['parentData'] = 'all';
					} else {
						$request['whereIn'] = 'user_id';
						$request['parentData'] = [Auth::user()->id];
					}
					break;

					case 'Resellers':
						$request['table'] = '\App\Models\Reseller';
						$request['searchData'] = [
							'name',
							'email',
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
							if ($columnName == '0') {
								$columnName = 'created_at';
								$columnSortOrder = 'DESC'; // asc or desc
							}
							$request['order'] = [$columnName, $columnSortOrder];
						} else {
							$request['order'] = ['id', 'DESC'];
						}
						if (Auth::user()->is_admin == '1') {
							$request['parentData'] = 'all';
						} else {
							$request['whereIn'] = 'user_id';
							$request['parentData'] = [Auth::user()->id];
						}
						break;
	
					case 'Activitylogs':
						$request['table'] = '\App\Models\ActivityLog';
						$request['searchData'] = [
							'type',
							'url',
							'method',
							'ip',
							'agent',
							'user_id',
							'message',
							'created_at'
						];
						$request['select'] = 'all';
						$request['with'] = ['user'];
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
							$request['order'] = ['id', 'DESC'];
						}
						if (Auth::user()->is_admin == '1') {
							$request['parentData'] = 'all';
						} else {
							$request['whereIn'] = 'user_id';
							$request['parentData'] = [Auth::user()->id];
		
							$unsettleDate = CommonHelper::getUnsettledBalance(Auth::user()->id, 'virtual_account', true);
							$request['havingUnsettle'] = $unsettleDate;
						}
						break;
		
					case 'Services':
						$request['table'] = '\App\Models\Service';
						$request['searchData'] = [
							'service_id',
							'service_name',
							'is_active',
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
							if ($columnName == '0') {
								$columnName = 'created_at';
								$columnSortOrder = 'DESC'; // asc or desc
							}
							$request['order'] = [$columnName, $columnSortOrder];
						} else {
							$request['order'] = ['id', 'DESC'];
						}
						if (Auth::user()->is_admin == '1') {
							$request['parentData'] = 'all';
						} else {
							$request['whereIn'] = 'user_id';
							$request['parentData'] = [Auth::user()->id];
						}
						break;

			case 'merchants_tpv':
				$request['table'] = '\App\Models\UPIMerchant';
				$request['searchData'] = [
					'root_type',
					'request_id',
					'merchant_business_name',
					'merchant_virtual_address',
					'mobile',
					'pan_no',
					'contact_email',
					'gstn',
					'created_at',
					'allowed_vpa',
					'allowed_bank'
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				$request['root_type'] = 'ibl_tpv';
				break;

			case 'autocollect_merchants':
				$request['table'] = '\App\Models\AutoCollectMerchant';
				$request['searchData'] = ['user_id', 'service_type', 'request_id', 'bank_account_no', 'business_name', 'van_1', 'van_2', 'van_1_ifsc', 'van_2_ifsc', 'vpa_1', 'vpa_2', 'mobile', 'pan_no', 'contact_email', 'gstn', 'city', 'pin_code', 'created_at'];
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

			case 'aepsmerchants':
				$request['table'] = '\App\Models\Agent';
				$request['searchData'] = ['merchant_code', 'first_name', 'last_name', 'middle_name', 'email_id', 'mobile', 'address', 'dob', 'pan_no', 'shop_name', 'shop_address', 'shop_pin', 'aadhar_number', 'pin_code', 'created_at'];
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

			case 'upicallbacks':
				$request['table'] = '\App\Models\UPICollect';
				$request['searchData'] = [
					'user_id','txn_note','amount','resp_code','description','payee_vpa','customer_ref_id','merchant_txn_ref_id','txn_id','original_order_id','bank_txn_id','payer_vpa','upi_txn_id','status','npci_txn_id','payer_acc_name','payer_mobile','payer_acc_no','payer_ifsc','code','fee','tax','reseller_commision','txn_date','type','created_at'
				];

				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}
				if(\Auth::user()->is_admin == '1' && Auth::user()->hasRole('reseller')) {
					$userIds = CommonHelper::getUsersAssignedToReseller();
					$request['whereIn'] = 'user_id';
					$request['parentData'] = $userIds;
				}
				elseif (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];

					// $isServiceActive = CommonHelper::checkIsServiceActive('upi_collect', Auth::user()->id);

					// if (!$isServiceActive) {
					// 	$request['is_trn_credited'] = '1';
					// }

					$unsettleDate = CommonHelper::getUnsettledBalance(Auth::user()->id, 'upi_stack', true);
					$request['havingUnsettle'] = $unsettleDate;
				}

				//$request['root_type_in'] = ['ibl', 'fpay'];
				break;

			case 'upicallbacks_tpv':
				$request['table'] = '\App\Models\UPICallback';
				$request['searchData'] = [
					'batch_id',
					'root_type',
					'payee_vpa',
					'amount',
					'txn_note',
					'description',
					'type',
					'npci_txn_id',
					'bank_txn_id',
					'customer_ref_id',
					'payer_acc_name',
					'payer_mobile',
					'payer_ifsc',
					'created_at'
				];

				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];

					$unsettleDate = CommonHelper::getUnsettledBalance(Auth::user()->id, 'virtual_account', true);
					$request['havingUnsettle'] = $unsettleDate;
				}

				$request['root_type'] = 'ibl_tpv';
				break;

			case 'validation_suite_txns':
				$request['table'] = '\App\Models\Validation';
				$request['searchData'] = [
					'order_ref_id',
					'type',
					'request_id',
					'param_1',
					'param_2',
					'status',
					'created_at'
				];

				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				// if(!empty($request->service_type)){
				// 	$request['type'] = $request->service_type;
				// 	$request['where'] = 1;
				// }

				break;


			case 'autocollect_callbacks':
				$request['table'] = '\App\Models\AutoCollectCallback';

				$request['searchData'] = [
					'batch_id',
					'v_account_number',
					'amount',
					'utr',
					'reference_id',
					'remitter_account',
					'remitter_name',
					'remitter_ifsc',
					'v_account_id',
					'credit_ref_no',
					'email',
					'phone',
					'created_at'
				];

				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];

					// $isServiceActive = CommonHelper::checkIsServiceActive('smart_collect', Auth::user()->id);

					// if (!$isServiceActive) {
					// 	$request['is_trn_credited'] = '1';
					// }

					$unsettleDate = CommonHelper::getUnsettledBalance(Auth::user()->id, 'smart_collect', true);
					$request['havingUnsettle'] = $unsettleDate;
				}

				if (isset($request->service_type)) {
					$request->filterArray = ['is_vpa' => $request->service_type];
				}
				break;

			case 'van-callbacks':
				$request['table'] = '\App\Models\FundReceiveCallback';
				$request['searchData'] = [
					'v_account_number',
					'amount',
					'utr',
					'reference_id',
					'remitter_account',
					'remitter_name',
					'v_account_id',
					'credit_ref_no',
					'email',
					'phone',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

			case 'webhook-logs':
				$request['table'] = '\App\Models\WebhookLog';
				$request['searchData'] = [
					'uuid',
					'httpVerb',
					'webhookUrl',
					'payload',
					'headers',
					'meta',
					'tags',
					'attempt',
					'response',
					'errorType',
					'errorMessage',
					'transferStats',
					'created_at',
					'updated_at'
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

			case 'load-money-request':
				$request['table'] = '\App\Models\LoadMoneyRequest';
				$request['searchData'] = [
					'request_id',
					'amount',
					'utr',
					'txn_id',
					'remarks',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['userNameEmail'];
				$orderIndex = $request->get('id');

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
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;


			case 'UPICollects':
				$request['table'] = '\App\Models\UPICollect';
				$request['searchData'] = [
					'payee_vpa',
					'amount',
					'txn_note',
					'description',
					'npci_txn_id',
					'bank_txn_id',
					'customer_ref_id',
					'payer_acc_name',
					'payer_mobile',
					'payer_ifsc',
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;


			case 'serviceRequest':
				$request['table'] = '\App\Models\UserService';
				$request['searchData'] = ['service_id', 'is_active', 'created_at', 'service_account_number'];
				$request['select'] = 'all';

				if (!empty($request->service_id)) {
					$request->where = true;
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
					if ($columnName == '0' || $columnName == 'contact_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

			case 'apiKeys':
				$request['table'] = '\App\Models\OauthClient';
				$request['searchData'] = ['client_key'];
				$request['select'] = 'all';
				$request['order'] = ['id', 'DESC'];
				$request['whereIn'] = 'user_id';
				$request['parentData'] = [Auth::user()->id];
				break;

			case 'ipWhiteLists':
				$request['table'] = '\App\Models\IpWhitelist';
				$request['searchData'] = ['ip'];
				$request['select'] = 'all';
				$request['order'] = ['id', 'DESC'];
				$request['whereIn'] = 'user_id';
				$request['parentData'] = [Auth::user()->id];
				$request->where = true;
				$request['is_active'] = '1';
				break;

			case 'webHookLists':
				$request['table'] = '\App\Models\Webhook';
				$request['searchData'] = ['webhook_url', 'header_key'];
				$request['select'] = 'all';
				$request['order'] = ['id', 'DESC'];
				$request['whereIn'] = 'user_id';
				$request['parentData'] = [\Auth::user()->id];
				break;

			case 'transactions':
				$request['table'] = '\App\Models\Transaction';
				$request['searchData'] = ['account_number', 'trans_id', 'txn_id', 'user_id', 'txn_ref_id', 'opening_balance', 'closing_balance', 'tr_amount', 'tr_identifiers', 'tr_type', 'remarks', 'order_id'];
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
					if ($columnName == '0' || $columnName == 'trans_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if(\Auth::user()->is_admin == '1' && Auth::user()->hasRole('reseller')) {
					$userIds = CommonHelper::getUsersAssignedToReseller();
					$request['whereIn'] = 'user_id';
					$request['parentData'] = $userIds;
				}
				elseif (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				}  else{
					$request['whereIn'] = 'user_id';
				$request['parentData'] = [\Auth::user()->id];
				}
				break;
			case 'exceldownload':
				$request['table'] = '\App\Models\ExcelDownload';
				$request['searchData'] = ['file_name', 'file_url', 'created_at'];
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
					if ($columnName == '0' || $columnName == 'id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}

				break;

			case 'mytransactions':
				$request['table'] = '\App\Models\Transaction';
				$request['searchData'] = [
					'account_number',
					'trans_id',
					'user_id',
					'txn_ref_id',
					'txn_id',
					'opening_balance',
					'closing_balance',
					'tr_amount',
					'tr_identifiers',
					'tr_type',
					'remarks',
					'tr_narration'
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
					if ($columnName == '0' || $columnName == 'trans_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;

			case 'users':
				$request['table'] = '\App\Models\User';
				$request['searchData'] = ['id', 'name', 'email', 'mobile', 'created_at','integration_id'];
				$request['select'] = 'all';
				$request['with'] = ['businessName', 'serviceName', 'integrationName'];
				// $request['with'] = ['collectName', 'integrationName'];

				if (isset($request->searchUserInfo) && !empty($request->searchUserInfo)) {
					$request['whereHas'] = [
						'businessName',
						function ($query) use ($request) {
							//$query->orWhere('pan_number', 'like', $request->searchUserInfo . '%');
							$query->where('pan_number', 'like', '%' . $request->searchUserInfo . '%');
							//$query->orWhere('pan_number', 'like', '%' . $request->searchUserInfo);
						}
					];
				}
				if (!empty($request->services)) {

					$serviceIds = DB::table('global_services')
						->select('service_id')
						->where('is_active', '1')
						->whereIn('service_slug', $request->services)
						->get();

					$srvIds = [];
					foreach ($serviceIds as $row) {
						$srvIds[] = $row->service_id;
					}
					$request['userService'] = $srvIds;

					$request['whereHas'] = [
						'serviceName',
						function ($query) use ($srvIds, $request) {
							$query->whereIn('service_id', $srvIds);
							if (isset($request->service_is_active)) {
								$query->where('is_active', $request->service_is_active);
							}

							if (!empty($request->filter_area)) {

								$webArr = [];
								$apiArr = [];
								foreach ($request->filter_area as $row) {
									$isArr = explode('#', $row);
									if ($isArr[0] === 'is_web_enable') {
										$webArr[] = $isArr[1];
									} else if ($isArr[0] === 'is_api_enable') {
										$apiArr[] = $isArr[1];
									}
								}

								if (!empty($webArr)) {
									$query->whereIn('is_web_enable', $webArr);
								}
								if (!empty($apiArr)) {
									$query->whereIn('is_api_enable', $apiArr);
								}

							}
						}
					];

				} else if (isset($request->service_is_active)) {
					$request['whereHas'] = [
						'serviceName',
						function ($query) use ($request) {
							$query->where('is_active', $request->service_is_active);

							if (!empty($request->filter_area)) {

								$webArr = [];
								$apiArr = [];
								foreach ($request->filter_area as $row) {
									$isArr = explode('#', $row);
									if ($isArr[0] === 'is_web_enable') {
										$webArr[] = $isArr[1];
									} else if ($isArr[0] === 'is_api_enable') {
										$apiArr[] = $isArr[1];
									}
								}

								if (!empty($webArr)) {
									$query->whereIn('is_web_enable', $webArr);
								}
								if (!empty($apiArr)) {
									$query->whereIn('is_api_enable', $apiArr);
								}

							}
						}
					];


				} else if (!empty($request->filter_area)) {

					$webArr = [];
					$apiArr = [];
					foreach ($request->filter_area as $row) {
						$isArr = explode('#', $row);
						if ($isArr[0] === 'is_web_enable') {
							$webArr[] = $isArr[1];
						} else if ($isArr[0] === 'is_api_enable') {
							$apiArr[] = $isArr[1];
						}
					}

					$request['whereHas'] = [
						'serviceName',
						function ($query) use ($request, $webArr, $apiArr) {
							if (!empty($webArr) && !empty($apiArr)) {
								$query->whereIn('is_web_enable', $webArr)
									->orWhereIn('is_api_enable', $apiArr);
							} else if (!empty($webArr)) {
								$query->whereIn('is_web_enable', $webArr);
							} else if (!empty($apiArr)) {
								$query->whereIn('is_api_enable', $apiArr);
							}
						}
					];
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
					if ($columnName == '0' || $columnName == 'contact_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if(\Auth::user()->is_admin == '1' && Auth::user()->hasRole('reseller')) {
					$userIds = CommonHelper::getUsersAssignedToReseller();
					$request['whereIn'] = 'id';
					$request['parentData'] = $userIds;
				}
				elseif (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				}  else{
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				// if (Auth::user()->is_admin == '1') {
				// 	$request['parentData'] = 'all';
				// } else {
				// 	$request['whereIn'] = 'user_id';
				// 	$request['parentData'] = [Auth::user()->id];
				// }
				break;

			case 'user-video-kyc':
				$request['table'] = '\App\Models\UserVideoKyc';
				$request['searchData'] = ['user_video_kyc.user_id', 'user_video_kyc.status', 'user_video_kyc.created_at', 'business_infos.email', 'business_infos.mobile', 'business_infos.name', 'business_infos.business_name'];
				$request['select'] = 'all';
				$request['with'] = ['businessName'];

				$orderIndex = $request->get('order');

				if (isset($orderIndex) && count($orderIndex)) {
					$columnsIndex = $request->get('columns');
					$columnIndex = $orderIndex[0]['column']; // Column index
					$columnName = $columnsIndex[$columnIndex]['data']; // Column name
					$columnSortOrder = $orderIndex[0]['dir']; // asc or desc
					if ($columnName == 'new_created_at') {
						$columnName = 'created_at';
					}
					if ($columnName == '0' || $columnName == 'contact_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;

			case 'upiCallbackTransaction':

				$request['table'] = '\App\Models\UPICallback';
				$request['searchData'] = [
					'payee_vpa',
					'amount',
					'txn_note',
					'description',
					'type',
					'npci_txn_id',
					'bank_txn_id',
					'customer_ref_id',
					'payer_acc_name',
					'payer_mobile',
					'payer_ifsc',
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}

				$request['whereIn'] = 'user_id';
				$request['parentData'] = [$request->id];

				break;

			case 'vanTransaction':
				$request['table'] = '\App\Models\FundReceiveCallback';
				$request['searchData'] = [
					'v_account_number',
					'amount',
					'utr',
					'reference_id',
					'remitter_account',
					'remitter_name',
					'v_account_id',
					'credit_ref_no',
					'email',
					'phone',
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}

				$request['whereIn'] = 'user_id';
				$request['parentData'] = [$request->id];

				break;
			case 'aepsTransaction':
				$request['table'] = '\App\Models\AepsTransaction';
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
					'trn_ref_id'
					,
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
			case 'aepsSettlement':
				$request['table'] = '\App\Models\Transaction';
				$request['searchData'] = [
					'txn_id',
					'created_at'
				];
				$request['select'] = 'all';
				//$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;

			case 'daybook':
				$request['table'] = '\App\Models\DayBook';
				$request['searchData'] = [
					'name',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;

			case 'upiInvoice':
				$request['table'] = '\App\Models\Invoice';
				$request['searchData'] = [
					'invoice_id',
					'service_id',
					'fee_amount',
					'fee_able_amount',
					'record_date',
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}

				$request['whereIn'] = 'user_id';
				$request['parentData'] = [$request->id];

				break;
			case 'roles':
				$request['table'] = '\App\Models\Role';
				$request['searchData'] = [
					'name',
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					//$request['parentData'] = 'all';
					$request['whereIn'] = 'status';
					$request['parentData'] = ['active', 'inactive'];
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
			case 'roles_users':
				$request['table'] = '\App\Models\UsersRole';
				$request['searchData'] = [
					'name',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user', 'role'];
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
					$request['order'] = ['user_id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					//$request['parentData'] = 'all';
					$request['whereIn'] = 'role_id';
					$request['parentData'] = [$id];
				}

				break;
			case 'admin_user':
				$request['table'] = '\App\Models\User';
				$request['searchData'] = ['id', 'name', 'email', 'mobile', 'created_at'];
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
					if ($columnName == '0' || $columnName == 'contact_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;
			case 'messages_list':
				$request['table'] = '\App\Models\Message';
				$request['searchData'] = [
					'name',
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
					//$request['whereIn'] = 'status';
					//$request['parentData'] = ['active','inactive'];
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;
			case 'offer_list':
				$request['table'] = '\App\Models\Offer';
				$request['searchData'] = [
					'title',
					'offer_id',
					'created_at'
				];
				$request['with'] = ['category'];
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
					//$request['whereIn'] = 'status';
					//$request['parentData'] = ['active','inactive'];
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;
			case 'category_list':
				$request['table'] = '\App\Models\OfferCategory';
				$request['searchData'] = [
					'title',
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
					if ($columnName == '0') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
					//$request['whereIn'] = 'status';
					//$request['parentData'] = ['active','inactive'];
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
				break;
			case 'aeps_agents':
				$request['table'] = '\App\Models\Agent';
				$request['searchData'] = ['id', 'mobile', 'first_name', 'last_name', 'email_id', 'merchant_code', 'created_at', 'ekyc_documents_uploaded_at', 'aadhar_number', 'pan_no'];
				$request['select'] = 'all';
				$request['with'] = ['businessName'];
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
					$request['order'] = ['id', 'DESC'];
				}
				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				}
				break;
			case 'rechargeTxn':
				$request['table'] = '\App\Models\Recharge';
				$request['searchData'] = [
					'stan_no',
					'order_ref_id',
					'merchant_code',
					'phone',
					'status',
					'bank_reference',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user', 'operator'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
			case 'recharge-back':
				$request['table'] = '\App\Models\RechargeBack';
				$request['searchData'] = [
					'user_id',
					'txn_id',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user', 'operator'];
				
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;

				case 'rechargeDTH':
					$request['table'] = '\App\Models\DthRecharge';
					$request['searchData'] = [
						'user_id',
						'customer_ref_id',
						'total_amount',
						'amount',
						'bank_txn_id',
						'original_order_id',
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
						if ($columnName == '0' || $columnName == 'trans_id') {
							$columnName = 'created_at';
							$columnSortOrder = 'DESC'; // asc or desc
						}
						$request['order'] = [$columnName, $columnSortOrder];
					} else {
						$request['order'] = ['id', 'DESC'];
					}
					if (\Auth::user()->is_admin == '1') {
						$request['parentData'] = 'all';
					} else {
						$request['whereIn'] = 'user_id';
						$request['parentData'] = [\Auth::user()->id];
					}
					break;
			case 'rechargeLIC':
				$request['table'] = '\App\Models\LicRecharge';
				$request['searchData'] = [
					'user_id',
					'customer_ref_id',
					'amount',
					'bank_txn_id',
					'total_amount',
					'original_order_id',
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
					if ($columnName == '0' || $columnName == 'trans_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;
			case 'rechargeElectricity':
				$request['table'] = '\App\Models\ElectricityRecharge';
				$request['searchData'] = [
					'user_id',
					'customer_ref_id',
					'amount',
					'bank_txn_id',
					'total_amount',
					'original_order_id',
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
					if ($columnName == '0' || $columnName == 'trans_id') {
						$columnName = 'created_at';
						$columnSortOrder = 'DESC'; // asc or desc
					}
					$request['order'] = [$columnName, $columnSortOrder];
				} else {
					$request['order'] = ['id', 'DESC'];
				}
				if (\Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [\Auth::user()->id];
				}
				break;
			case 'rechargePostPaid':
				$request['table'] = '\App\Models\PostPaidRecharge';
				$request['searchData'] = [
					'user_id',
					'customer_ref_id',
					'amount',
					'bank_txn_id',
					'total_amount',
					'original_order_id',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user', 'operator'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
			case 'rechargeCreditCard':
				$request['table'] = '\App\Models\CreditcardRecharge';
				$request['searchData'] = [
					'user_id',
					'customer_ref_id',
					'amount',
					'bank_txn_id',
					'total_amount',
					'original_order_id',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user', 'operator'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
			case 'rechargeData':
				$request['table'] = '\App\Models\RechargeData';
				$request['searchData'] = [
					'user_id',
					'customer_ref_id',
					'amount',
					'bank_txn_id',
					'total_amount',
					'original_order_id',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user', 'operator'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
				case 'IntegrationValue':
					$request['table'] = '\App\Models\Integration';
					$request['searchData'] = [
						'name',
						'slug',
						'is_active',
						'created_at',
						'updated_at'
					];
					$request['select'] = 'all';
				$request['with'] = ['user', 'operator'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}
					break;
			case 'ocrTxn':
				$request['table'] = '\App\Models\Ocr';
				$request['searchData'] = [
					'client_ref_id',
					'order_ref_id',
					'request_id',
					'type',
					'status',
					'created_at'
				];
				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
			case 'dmtTxn':
				$request['table'] = '\App\Models\DMTFundTransfer';
				$request['searchData'] = ['client_ref_id', 'outlet_id', 'mobile', 'bene_id', 'utr', 'status', 'created_at'];
				$request['select'] = 'all';
				$request['with'] = ['user', 'outlet'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;

			case 'panTxn':
				$request['table'] = '\App\Models\PanCardTransaction';
				$request['searchData'] = ['psa_code', 'txn_id', 'order_ref_id', 'app_no', 'ope_txn_id', 'coupon_type', 'name_on_pan', 'email', 'mobile', 'txn_type', 'fee', 'txn', 'status', 'failed_message', 'created_at'];
				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
			case 'panAgents':
				$request['table'] = '\App\Models\PanCard';
				$request['searchData'] = ['psa_id', 'client_ref_id', 'first_name', 'middle_name', 'last_name', 'mobile', 'email', 'pin', 'dob', 'state', 'district', 'address', 'pan','status','created_at'];
				$request['select'] = 'all';
				$request['with'] = ['user', 'states', 'district'];
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
					$request['order'] = ['id', 'DESC'];
				}

				if (Auth::user()->is_admin == '1') {
					$request['parentData'] = 'all';
				} else {
					$request['whereIn'] = 'user_id';
					$request['parentData'] = [Auth::user()->id];
				}

				break;
			case 'insuranceAgent':
				$request['table'] = '\App\Models\Insurance';
				$request['searchData'] = ['name','email','mobile','pan','agentId','status','created_at'];
				$request['select'] = 'all';
				$request['with'] = ['user'];
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
					$request['order'] = ['id', 'DESC'];
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
		if (isset($request->searchText) && !empty($request->searchText) && $type == 'orders') {
			$getOrderRefId = self::getOrderRefId($request->searchText);
			$request->orderIdArray = $getOrderRefId;
		}
		if (isset($request->searchText) && !empty($request->searchText) && $type == 'serviceRequest') {
			$getServiceId = self::getServiceId($request->searchText);
			$request->serviceIdArray = $getServiceId;
		}
		if (isset($request->searchText) && !empty($request->searchText) && in_array($type, array('bulkpayouts', 'serviceRequest')) && \Auth::user()->is_admin == '1') {
			$getUserId = self::getUserId($request->searchText);
			$request->userIdArray = $getUserId;
		}

		if (
			(isset($request->searchText) && !empty($request->searchText)) ||
			(isset($request->to) && !empty($request->to)) ||
			(isset($request->tr_type) && !empty($request->tr_type)) ||
			(isset($request->account_number) && !empty($request->account_number)) ||
			(isset($request->from) && !empty($request->from)) ||
			(isset($request->status) && $request->status != '') ||
			(isset($request->apes_status_array) && $request->apes_status_array != '') ||
			(isset($request->area) && $request->area != '') ||
			(isset($request->account_type) && $request->account_type != '') ||
			(isset($request->tr_identifiers) && $request->tr_identifiers != '') ||
			(isset($request->service_id_array) && $request->service_id_array != '') ||
			(isset($request->integration_id) && $request->integration_id != '') ||
			(isset($request->transaction_type_array) && $request->transaction_type_array != '') ||
			(isset($request->route_type_array) && $request->route_type_array != '') ||
			(isset($request->is_active) && $request->is_active != '') ||
			(isset($request->userId) && $request->userId != '') ||
			(isset($request->user_id) && !empty($request->user_id)) ||
			(isset($request->service_type) && !empty($request->service_type))
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
			print_r($e->getMessage());
			$data = [];
		}
		if ($request->return == "all" || $returnType == "all") {
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
		\DB::enableQueryLog();
		$table = $request->table;
		$data = $table::on('slave');
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
		if ($request['type'] == 'users' && Auth::user()->is_admin == '1') {
			$data->where('is_admin', '0');
		}
		if ($request['type'] == 'admin_user' && Auth::user()->is_admin == '1') {
			$data->where('is_admin', '1');
		}
		if ($request['type'] == 'aepsSettlement') {
			$data->where('tr_identifiers', 'aeps_inward_credit');
		}

		if (!empty($request['is_trn_credited'])) {
			$data->where('is_trn_credited', $request['is_trn_credited']);
		}

		if (!empty($request['havingUnsettle'])) {
			$data->havingRaw("(DATE(`created_at`) >= '" . $request['havingUnsettle'] . "' AND `is_trn_credited` = '0') OR (`is_trn_credited` = '1')");
		}

		if (!empty($request['root_type'])) {
			$data->where('root_type', $request['root_type']);
		}

		if (!empty($request['root_type_in'])) {
			$data->whereIn('root_type', $request['root_type_in']);
		}

		if (!empty($request->filterArray)) {
			foreach ($request->filterArray as $key => $val) {
				$data->where($key, $val);
			}
		}
		if (isset($request->account_type) && $request->account_type != '' && $request->account_type != null) {
			$data->where('account_type', $request->account_type);
		}
		if (isset($request->contact_type) && $request->contact_type != '' && $request->contact_type != null) {
			$data->where('type', $request->contact_type);
		}
		if (isset($request->mode) && $request->mode != '' && $request->mode != null) {
			$data->where('mode', $request->mode);
		}


		if ($request->where) {
			if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to)) && ($request['type'] != 'aeps_agents')) {
				if ($request->from == $request->to) {
					$data->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
				} else if ($request['type'] == 'daybook') {
					$data->whereBetween('created_at', [
						Carbon::createFromFormat('Y-m-d', $request->from)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->format('Y-m-d')
					]);
				} else {
					$data->whereBetween('created_at', [
						Carbon::createFromFormat('Y-m-d', $request->from)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
					]);
				}
			}
			if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to)) && (($request['type'] == 'aeps_agents'))) {
				if ($request->date_type == 'document_uploaded_at') {
					$date_column = 'ekyc_documents_uploaded_at';
				} else {
					$date_column = 'created_at';
				}
				if ($request->from == $request->to) {
					$data->whereDate($date_column, '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
				} else {
					$data->whereBetween($date_column, [
						Carbon::createFromFormat('Y-m-d', $request->from)
							->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
					]);
				}
			}
			if (isset($request->user_id) && !empty($request->user_id)) {
				$data->where('user_id', $request->user_id);
			}
			if (!empty($request->service_type)) {
				$data->where('type', $request->service_type);
			}
			// if (!empty($request->type))
			// {
			// 	$data->where('type', $request->type);
			// }

			if (isset($request->status) && $request->status != '' && $request->status != null) {
				$data->where('status', $request->status);
			}
			if (isset($request->integration_id) && $request->integration_id != '' && $request->integration_id != null) {
				$data->where('integration_id', $request->integration_id);
			}

			if (isset($request->is_active) && $request->is_active != '' && $request->is_active != null) {
				$data->where('is_active', $request->is_active);
			}
			if (isset($request->route_type_array) && count($request->route_type_array)) {
				$data->whereIn('route_type', $request->route_type_array);
			}

			if (isset($request->transaction_type_array) && count($request->transaction_type_array)) {
				$data->whereIn('transaction_type', $request->transaction_type_array);
			}
			if (!empty($request->service_id)) {
				$data->where('service_id', $request->service_id);
			}
			if (!empty($request->service_id_array) && count($request->service_id_array)) {
				$data->whereIn('service_id', $request->service_id_array);
			}
			if (!empty($request->apes_status_array) && count($request->apes_status_array)) {
				$data->whereIn('status', $request->apes_status_array);
			}
			if (!empty($request->txnType) && count($request->txnType)) {
				$data->whereIn('type', $request->txnType);
			}
			if (isset($request->tr_identifiers) && count($request->tr_identifiers)) {
				$data->whereIn('transactions.tr_identifiers', $request->tr_identifiers);
			}
			if (isset($request->area) && $request->area != '' && $request->area != null) {
				$data->where('area', $request->area);
			}
			if (isset($request->tr_type) && $request->tr_type != '' && $request->tr_type != null) {
				$data->where('tr_type', $request->tr_type);
			}
			if (isset($request->account_number) && $request->account_number != '' && $request->account_number != null) {
				$data->where('account_number', $request->account_number);
			}
			if (isset($request->adminUserIdArray) && count($request->adminUserIdArray)) {
				$data->whereIn('user_id', $request->adminUserIdArray);
			}

			if (isset($request->orderIdArray) && count($request->orderIdArray)) {
				$data->whereIn('order_ref_id', $request->orderIdArray);
			} else if (isset($request->serviceIdArray) && count($request->serviceIdArray)) {
				$data->whereIn('service_id', $request->serviceIdArray);
			} else if (isset($request->userIdArray) && count($request->userIdArray)) {
				$data->whereIn('user_id', $request->userIdArray);
			} else {
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
		}

		if ($request->with) {
			$data->with($request->with)->select('*');

			if (!empty($request->whereHas)) {
				$data->whereHas($request->whereHas[0], $request->whereHas[1]);
			}
		}

		if ($request->return == "all" || $request->returnType == "all") {
			if ($returnType == "count") {
				return $data->count();
			} else {
				if ($request['length'] != -1) {
					$data->skip($request['start'])->take($request['length']);
				}
				if ($request->select == "all" || in_array('created_at', $request->select)) {
					// if($request->with)
					// {
					// 	$data = $data->with($request->with)->select('*')->get();
					// }else
					// {
					// 	$data = $data->get();
					// }
                   // dd($data->toSql());
					$data = $data->get();
					//dd(\DB::getQueryLog());
					foreach ($data as $key => $value) {
						if (isset($value->aadhar_number) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction', 'aeps_agents'])) {
							$value->aadhar_number = CommonHelper::masking('aadhar', $value->aadhar_number);
						}
						if (isset($value->mobile) && in_array($request['type'], ['aepsTransaction', 'aeps_agents'])) {
							$value->mobile = CommonHelper::masking('mobile', $value->mobile);
						}

						if (isset($value->closing_balance)) {
							$value->closing_balance = round($value->closing_balance, 2);
						}
						if (isset($value->aadhaar_no) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->aadhaar_no = CommonHelper::masking('aadhar', $value->aadhaar_no);
						}
						if (isset($value->bank_number) && in_array($request['type'], ['validation_suite_txns'])) {
							$value->bank_number = CommonHelper::masking('aadhar', $value->bank_number);
						}
						if (isset($value->pan_no) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction', 'aeps_agents'])) {
							$value->pan_no = CommonHelper::masking('pan', $value->pan_no);
						}
						if (isset($value->created_at)) {
							$value->new_created_at = $value->created_at->format('Y-m-d H:i:s');
						}
						if (isset($value->updated_at)) {
							$value->new_updated_at = $value->updated_at->format('Y-m-d H:i:s');
						}

						if (isset($value->merchant->aadhar_number) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->merchant->aadhar_number = CommonHelper::masking('aadhar', $value->merchant->aadhar_number);
						}
						if (isset($value->merchant->pan_no) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
							$value->merchant->pan_no = CommonHelper::masking('aadhar', $value->merchant->pan_no);
						}
						if (isset($value->merchant->mobile) && in_array($request['type'], ['aepsTransaction'])) {
							$value->merchant->mobile = CommonHelper::masking('mobile', $value->merchant->mobile);
						}
						if (isset($value->payer_acc_no) && in_array($request['type'], ['upicallbacks', 'upicallbacks_tpv'])) {
							$value->payer_acc_no = CommonHelper::masking('mobile', $value->payer_acc_no);
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

	public static function getOrderRefId($payoutRefId)
	{
		$data = [];
		$BulkPayoutDetail = BulkPayoutDetail::select('order_ref_id')
			->where('payout_reference', 'like', $payoutRefId . '%')
			->orWhere('payout_reference', 'like', '%' . $payoutRefId)
			->orWhere('payout_reference', 'like', '%' . $payoutRefId . '%')
			->get();
		foreach ($BulkPayoutDetail as $BulkPayoutDetails) {
			$data[] = $BulkPayoutDetails->order_ref_id;
		}
		return $data;
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

	public static function getServiceId($serviceName)
	{
		$data = [];
		$services = Service::select('service_id')
			->where('service_name', 'like', $serviceName . '%')
			->orWhere('service_name', 'like', '%' . $serviceName)
			->orWhere('service_name', 'like', '%' . $serviceName . '%')
			->get();
		foreach ($services as $service) {
			$data[] = $service->service_id;
		}
		return $data;
	}

	public function fetchReportData(Request $request, $id = 0, $returnType = 'all')
	{
		$request['return'] = 'all';
		$request->orderIdArray = [];
		$request->serviceIdArray = [];
		$request->userIdArray = [];
		$request['returnType'] = $returnType;
		$parentData = session('parentData');
		$request['where'] = 0;
		DB::enableQueryLog();
		$where = '';
		if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
			if ($request->from == $request->to) {

				$where = ' and created_at =' . Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d');

			} else {
				$where = ' and (created_at between ' . Carbon::createFromFormat('Y-m-d', $request->from)
					->format('Y-m-d') . ' and ' . Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d') . ')';

			}
		}
		$result = DB::table('users as u')->select('u.id', 'u.name', 'u.email', 'u.transaction_amount as primaryAmount', DB::raw('(SELECT (transaction_amount+locked_amount) from user_services where user_id=u.id and service_id="srv_1626077095" and is_active="1") as payoutBalance'), DB::raw('(SELECT sum(amount) FROM fund_receive_callbacks  WHERE user_id=u.id ' . $where . ') AS VanAmount'), DB::raw("(select sum(tr_amount) from transactions where tr_type='dr' and tr_identifiers in ('van_fee_tax','van_fee') and user_id=u.id " . $where . ") as van_fee_tax"), DB::raw("(SELECT sum(amount) FROM upi_callbacks  WHERE user_id=u.id " . $where . ") AS callbackAmount"), DB::raw("(SELECT sum(amount)  FROM upi_collects  WHERE user_id=u.id  AND status='success' " . $where . ") AS upi_collect_amount"), DB::raw("(SELECT sum(amount) from orders where user_id=u.id and status='processed' " . $where . ") as orderProcessedAmount"), DB::raw("(SELECT sum(fee) from orders where user_id=u.id and status='processed' " . $where . ") as orderProcessedFeeAmount"), DB::raw("(SELECT sum(tax) from orders where user_id=u.id and status='processed' " . $where . ") as orderProcessedFeeTaxAmount"), DB::raw("(SELECT sum(amount) from orders where user_id=u.id and status='failed' " . $where . ") as orderFailedAmount"))->where('u.is_admin', '0');
		if ($request->user_id) {
			$result->where('u.id', $request->user_id);
		}

		try {
			$totalData = $result->count();

		} catch (\Exception $e) {
			$totalData = 0;
		}
		try {
			$totalFiltered = $result->count();
			//dd(DB::getQueryLog());
		} catch (\Exception $e) {
			print_r($e->getMessage());
			//$totalFiltered = 0;
		}
		if ($request['length'] != -1) {

			$result->skip($request['start'])->take($request['length']);
			//$totalFiltered = $result->count();
		}

		try {

			$data = $result->get();
			//print_r($data);

		} catch (\Exception $e) {
			print_r($e->getMessage());
			$data = [];
		}

		if ($request->return == "all" || $returnType == "all") {
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

	// public function getUsersAssignedToReseller()
    // {
    //     $resellerId = Auth::user()->reseller_id;
    //     $users = User::where('reseller', $resellerId)->get();

    //     return $users;
    // }

}