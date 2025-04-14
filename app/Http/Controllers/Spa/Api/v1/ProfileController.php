<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ResponseHelper;
use App\Models\User;
use Validations\UserValidation as Validations;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CommonHelper;
use App\Jobs\SendTransactionEmailJob;
use App\Models\IpWhitelist;
use App\Models\BusinessInfo;
use App\Models\UserConfig;
use App\Models\UserService;
use Exception;
use Illuminate\Support\Facades\DB;


class ProfileController extends Controller
{
	public function userProfile(Request $request)
	{
		$userId = $request->user()->id;
		$businessInfos = DB::table('business_infos')
			->select(
				'business_infos.business_name',
				'business_infos.business_name_from_pan',
				'business_infos.business_pan',
				'business_infos.business_type',
				'business_infos.business_category_id',
				'business_infos.business_subcategory_id',
				'business_infos.pan_number',
				'business_infos.pan_owner_name',
				'business_infos.address',
				'business_infos.pincode',
				'business_infos.city',
				'business_infos.state',
				'business_infos.business_description',
				'business_infos.web_url',
				'business_infos.app_url',
				'business_infos.gstin',
				DB::raw('concat("XXXXXX", right(business_infos.aadhar_number, 4)) as aadhar_number'),
				'business_infos.aadhaar_name',
				'business_infos.acc_manager_id',
				'business_infos.is_active',
				'business_infos.is_kyc_updated',
				'business_infos.is_bank_updated',
				'business_infos.is_kyc_documents_uploaded',
				'b1.name as business_category_name',
				'b2.name as business_subcategory_name',
				'states.state_name as stateName',
				'account_managers.name as account_manager_name'
			)
			->leftjoin('business_categories as b1', 'b1.id', '=', 'business_infos.business_category_id')
			->leftjoin('business_categories as b2', 'b2.id', '=', 'business_infos.business_subcategory_id')
			->leftjoin('states', 'business_infos.state', '=', 'states.id')
			->leftjoin('account_managers', 'business_infos.acc_manager_id', '=', 'account_managers.id')
			// ->where('business_infos.is_active', '1')
			->where('business_infos.user_id', $userId)
			->first();

		$userData = $request->user();
		if ($businessInfos) {

			if (strtolower($businessInfos->business_type) === 'proprietorship') {
				$businessInfos->biz_name = $businessInfos->business_name;
			} else {
				$businessInfos->biz_name = $businessInfos->business_name_from_pan;
			}

			unset($businessInfos->business_name);
			unset($businessInfos->business_name_from_pan);

			$data['businessInfos'] = $businessInfos;
		} else {
			$data['businessInfos'] = '';
		}
		if ($userData) {
			$data['userInfos']['name'] = $userData->name;
			$data['userInfos']['email'] = $userData->email;
			$data['userInfos']['mobile'] = $userData->mobile;
			$data['userInfos']['is_profile_updated'] = $userData->is_profile_updated;
			$data['userInfos']['email_verified_at'] = $userData->email_verified_at;
			$data['userInfos']['is_active'] = $userData->is_active;
		}

		return ResponseHelper::success('Record fetched successfully.', $data);
	}

	public function bankDetails(Request $request)
	{
		$userId = $request->user()->id;
		$bankInfos = DB::table('user_bank_infos')
			->select('beneficiary_name', 'ifsc', 'account_number', 'is_active', 'is_verified', 'is_primary', 'created_at')
			->where('user_id', $userId)
			->get();

		if ($bankInfos->isNotEmpty()) {
			return ResponseHelper::success('Record fetched successfully.', $bankInfos);
		}

		return ResponseHelper::failed('Record not found.');
	}

	public function IpList(Request $request)
	{
		$userId = $request->user()->id;
		$ipList = DB::table('ip_whitelists')
			->select('ip_whitelists.id', 'ip_whitelists.ip', 'ip_whitelists.is_active', 'ip_whitelists.created_at', 'global_services.service_name')
			->join('global_services', 'global_services.service_id', '=', 'ip_whitelists.service_id')
			->where('ip_whitelists.user_id', $userId)
			->where('ip_whitelists.is_active', '1')
			->orderBy('created_at', 'desc')
			->get();
		if ($ipList->isNotEmpty()) {
			return ResponseHelper::success('Record fetched successfully.', $ipList);
		}
		return ResponseHelper::failed('Record not found.');
	}



	public function serviceKeys(Request $request)
	{
		$userId = $request->user()->id;
		$keys = DB::table('oauth_clients')
			->select('oauth_clients.id', 'oauth_clients.service_id', DB::raw('CONCAT(SUBSTR(client_key, 1, 6),REPEAT("*", CHAR_LENGTH(client_key) - 6),SUBSTR(client_key, -6)) as client_key'), 'oauth_clients.created_at', 'global_services.service_name')
			->leftjoin('global_services', 'global_services.service_id', '=', 'oauth_clients.service_id')
			->where('oauth_clients.user_id', $userId)
			->where('oauth_clients.is_active', '1')
			->orderBy('created_at', 'desc')
			->get();
		if ($keys->isNotEmpty()) {
			return ResponseHelper::success('Record fetched successfully.', $keys);
		}
		return ResponseHelper::failed('Record not found.');
	}

	public function serviceList(Request $request)
	{
		$userId = $request->user()->id;
		$query = "SELECT gs.*,(case when us.is_active='1' then 'activated' when us.is_active='0' then 'pending' else 'notactivated' end) as services_status FROM `global_services` gs left join user_services us on us.service_id=gs.service_id and us.user_id=$userId where gs.is_active='1'";
		// $serviceList = DB::table('global_services')
		// 				->leftjoin('user_services as us1','us1.service_id','=','global_services.service_id')
		// 				->leftjoin('user_services as us2','us2.user_id','=',$userId)
		// 				->where('global_services.is_active','1')
		// 				->get();
		$serviceList = DB::select($query);

		$response = [];

		if (!empty($serviceList)) {

			foreach ($serviceList as $row) {
				$response[] = $row;
			}

			$isAutoSettlement = DB::table('user_config')
				->select('is_auto_settlement', 'is_internal_transfer_enable')
				// ->where('is_auto_settlement', '1')
				->where('user_id', $userId)
				->first();


			if (!empty($isAutoSettlement->is_auto_settlement)) {
				$objects = [
					'is_activation_allowed' => '0',
					'service_slug' => 'auto_settlement',
					'services_status' => 'activated',
					"service_type" => "0"
				];

				$response[] = $objects;
			}

			if (!empty($isAutoSettlement->is_internal_transfer_enable)) {
				$objects = [
					'is_activation_allowed' => '0',
					'service_slug' => 'internal_transfer',
					'services_status' => 'activated',
					"service_type" => "0"
				];

				$response[] = $objects;
			}

			return ResponseHelper::success('Record fetched successfully.', $response);
		}

		return ResponseHelper::failed('Record not found.');
	}

	public function updateProfile(Request $request)
	{
		$userId = $request->user()->id;
		//$request->merge(['user_id' => $userId]);
		$validator = Validator::make($request->all(), [
			'name' => 'required',
			'mobile' => 'required|numeric',
			'email' => 'required|email',
			'aadhar_number' => 'required|integer|digits_between:8,20',
			'business_type' => 'required',
			'business_category_id' => 'required',
			//'business_subcategory_id' => 'required',
			'business_description' => 'required',
			'business_name' => 'required',
			'business_pan' => 'required|size:10|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
			'pan_owner_name' => 'required',
			'pan_number' => 'required|size:10|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
			'gstin' => 'required',
			'mcc' => 'required|integer',
			'web_url' => 'required|url|active_url',
			'app_url' => 'required|url|active_url',
			'acc_manager_id' => 'required',
			'billing_label' => 'required',
			'address' => 'required',
			'pincode' => 'required|integer|digits:6',
			'city' => 'required',
			'state' => 'required'
		]);
		// $validation = new Validations($request);
		//        $validator = $validation->businessProfileUpdate();

		if ($validator->fails()) {
			return ResponseHelper::missing($validator->errors());
		} else {
			try {
				$BusinessInfo = BusinessInfo::where('user_id', $userId)->first();
				$data = $request->all();
				unset($data['_token']);

				if (isset($data['business_subcategory'])) {
					$data['business_subcategory_id'] = $data['business_subcategory'];
					unset($data['business_subcategory']);
				} else {
					$data['business_subcategory_id'] = null;
				}

				if (!empty($request->web_url)) {
					$url = parse_url($request->web_url);
					$data['web_url'] = $url['scheme'] . '://' . $url['host'];
				}
				$data['app_url'] = $request->app_url;

				$updatedById = 0;
				if (!empty($request->update_by_user_id)) {
					$updatedById = decrypt($request->update_by_user_id);
					unset($data['update_by_user_id']);
				}

				if (isset($BusinessInfo)) {
					// unset($data['business_proof']);
					// unset($data['pan_id']);
					// unset($data['user_id']);
					$BusinessInfo = BusinessInfo::where('user_id', $userId)->update($data);
				} else {
					$data['is_kyc_updated'] = '1';
					$data['user_id'] = $userId;
					$BusinessInfo = BusinessInfo::create($data);
				}
				if ($updatedById == $userId) {
					$message = "Business profile updated.";
				} else {
					$message = "Business profile updated.";
				}
				User::where('id', $userId)->update(['is_profile_updated' => '1']);

				ActivityLogHelper::addToLog('business_profile_update', $userId, $message, $updatedById);

				return ResponseHelper::success($message);
			} catch (Exception $e) {
				return ResponseHelper::failed($e->getMessage());
			}
		}
	}

	public function getUserDetails(Request $request)
	{
		try {

			$userId = Auth::user()->id;

			$user = DB::table('users as u')
				->select(
					DB::raw('case when u.email_verified_at is null then 0 else 1 end as isEmailVerified'),
					'u.is_active as isProfileActive',
					'u.is_profile_updated',
					'u.signup_status as signup_step',
					DB::raw('case when bi.is_kyc_updated is null then 0 else 1 end as isKycUpdated'),
					DB::raw('case when user_video_kyc.status is null then "" else user_video_kyc.status end as video_kyc_status')
				)
				->leftjoin('business_infos as bi', 'bi.user_id', '=', 'u.id')
				->leftjoin('user_video_kyc', 'u.id', '=', 'user_video_kyc.user_id')
				->where('u.id', $userId)
				->first();

			$data['userInfos'] = $user;


			//check bank added or not
			$userBank = DB::table('user_bank_infos')
				->where('user_id', $userId)
				->first();

			if (!empty($userBank)) {
				$data['bankInfos'] = 1;
			} else {
				$data['bankInfos'] = 0;
			}


			//check apes and matm sdk is enable or not
			$userConfig = DB::table('user_config')
				->select('is_sdk_enable', 'is_matm_enable')
				->where('user_id', $userId)
				->first();

			$data['isAepsSdkEnable'] = 0;
			if (!empty($userConfig->is_sdk_enable)) {
				$data['isAepsSdkEnable'] = 1;
			}

			$data['isMatmSdkEnable'] = 0;
			if (!empty($userConfig->is_matm_enable)) {
				$data['isMatmSdkEnable'] = 1;
			}

			return ResponseHelper::success('Record found.', $data);
		} catch (Exception $e) {
			return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
		}
	}

	public function generateKeys(Request $request)
	{
		$id = $request->user()->id;
		$validator = Validator::make($request->all(), [
			'service_id' => 'required'
		]);

		$validator->after(function ($validator) use ($request, $id) {

			$user = DB::table('users')
				->select('is_active')
				->where('id', $id)
				->first();

			if (!empty($user)) {
				if ($user->is_active != '1') {
					$message = CommonHelper::getUserStatusMessage($user->is_active);
					$validator->errors()->add('user_id', $message);
				}
			} else {
				$validator->errors()->add('user_id', "Invalid user ID");
			}

			$authCount = DB::table('oauth_clients')
				->where('user_id', $id)
				->where('service_id', trim($request->service_id))
				->count('id');


			$apiKeyLimit = CommonHelper::getUserConfig('api_key_limit', $id);

			$apiKeyLimit = !empty($apiKeyLimit->api_key_limit) ? $apiKeyLimit->api_key_limit : 0;

			//check global limit is reached or not
			if ($authCount >= $apiKeyLimit) {
				$request->apiKeyLimitReached = true;
				$validator->errors()->add('service_id', 'API Key generation limit reached. Please contact your account manager.');
			}
		});

		if ($validator->fails()) {
			return ResponseHelper::missing($validator->errors());
		} else {
			$keyCode = CommonHelper::getRandomString('', false, 16);
			$keySecret = CommonHelper::getRandomString('', false, 32);
			$secretkey = 'xettle_' . $keyCode;
			$hash = hash('sha512', $keySecret);

			DB::beginTransaction();

			DB::table('oauth_clients')
				->where('user_id', $id)
				->where('service_id', trim($request->service_id))
				->where('is_active', '1')
				->update([
					'is_active' => '0',
					'updated_at' => date('Y-m-d H:i:s')
				]);

			$insert = DB::table('oauth_clients')
				->insert([
					'user_id' => $id,
					'service_id' => trim($request->service_id),
					'client_key' => $secretkey,
					'client_secret' => $hash,
					'is_active' => '1',
					'scope' => "*",
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s')
				]);

			DB::commit();

			if ($insert) {
				//get user name and email
				$user = DB::table('users')
					->select('email', 'name')
					->where('id', $id)
					->first();

				//get service name
				$service = DB::table('global_services')
					->select('service_name')
					->where('service_id', trim($request->service_id))
					->first();

				$mailParms = [
					'email' => $user->email,
					'name' => $user->name,
					'serviceName' => $service->service_name,
					'clientId' => $secretkey,
					'clientSecret' => $keySecret
				];
				dispatch(new SendTransactionEmailJob((object) $mailParms, 'apiKeyCredentials'));

				ActivityLogHelper::addToLog('api_key_generate', $id, "New API key generated for - $service->service_name", $id);
			}
			$data = ['apikey' => true, 'key' => $secretkey, 'secret' => $keySecret];
			return ResponseHelper::success('API keys generated successfully', $data);
		}

		return ResponseHelper::failed('Something Went Wrong.');
	}



	public function serviceRequest(Request $request)
	{
		$userId = $request->user()->id;
		$user = DB::table('users')
			->select('signup_status')
			->where('id', $userId)
			->first();
		if (!empty($user)) {

			if ($user->signup_status == '5' || $user->signup_status == '10') {

				$validator = Validator::make($request->all(), [
					'service_id' => 'required'
				]);
				if ($validator->fails()) {
					return ResponseHelper::missing($validator->errors());
				} else {
					$service = DB::table('user_services')
						->where('service_id', $request->service_id)
						->where('user_id', $userId)
						->first();
					if (empty($service)) {
						$UserService = new UserService;
						$UserService->user_id = $userId;
						$UserService->service_id = $request->service_id;
						$UserService->service_account_number = null;
						$UserService->locked_amount = 0;
						$UserService->transaction_amount = 0;
						$UserService->is_active = '0';
						$UserService->save();

						#check user salt

						CommonHelper::sendSlackRequestData($request->service_id, $userId);
						$salt = base64_encode(CommonHelper::getRandomString('', false, 10));
						$user_salt = UserConfig::where('user_id', $userId)->first();

						$salt = base64_encode(CommonHelper::getRandomString('', false, 10));
						if (empty($user_salt)) {
							#save salt in user_config

							$userConfig = new UserConfig;
							$userConfig->user_id = $userId;
							$userConfig->user_salt = $salt;
							$userConfig->save();
						} else if (!empty($user_salt) && $user_salt['user_salt'] == '') {
							$user_salt->user_salt = $salt;
							$user_salt->save();
						}

						return ResponseHelper::success('Service activation request accepted.');
					} else {
						return ResponseHelper::failed('You requested for this service already.');
					}
				}
			} else {
				$message = CommonHelper::getUserStatusMessage(5);
				return ResponseHelper::failed($message);
			}
		}
		return ResponseHelper::failed('No record found.');
	}




	public function addIp(Request $request)
	{
		$userId = $request->user()->id;
		$validator = Validator::make($request->all(), [
			'service_id' => 'required',
			'ip' => 'required|ip'
		]);
		$validator->after(function ($validator) use ($request, $userId) {

			$user = DB::table('users')
				->select('is_active')
				->where('id', $userId)
				->first();

			if (!empty($user)) {

				if ($user->is_active != '1') {
					$message = CommonHelper::getUserStatusMessage($user->is_active);
					$validator->errors()->add('user_id', $message);
				}
			} else {
				$validator->errors()->add('user_id', 'Invalid user id.');
			}


			$ipWhitelist = DB::table('ip_whitelists')
				->where(
					[
						'user_id' => $userId,
						'ip' => trim($request->ip),
						'service_id' => trim($request->service_id),
						'is_active' => '1'
					]
				)->first();

			if (!empty($ipWhitelist)) {
				$validator->errors()->add('ip', 'IP address already exists');
			} else {

				$ipCounts = DB::table('ip_whitelists')
					->where('user_id', $userId)
					->where('service_id', trim($request->service_id))
					->where('is_active', '1')
					->count('id');

				if ($ipCounts >= LIMIT_IP_WHITELIST) {
					$validator->errors()->add('ip', 'IP whitelist limit reached.');
				}
			}
		});
		if ($validator->fails()) {
			return ResponseHelper::missing($validator->errors());
		} else {
			$Ip = new IpWhitelist;
			$Ip->user_id = $userId;
			$Ip->service_id = $request->service_id;
			$Ip->ip = $request->ip;
			$Ip->is_active = '1';
			$Ip->save();

			return ResponseHelper::success('Ip added successfully.');
		}
	}

	public function updateBankDetails(Request $request)
	{
		$userId = $request->user()->id;
		$validation = new Validations($request);
		$validator = $validation->updateBankDetails();

		$validator->after(function ($validator) use ($userId) {

			$userInfo = User::where('id', $userId)->first();

			if (empty($userInfo)) {
				$validator->errors()->add('user_id', "User is not activated.");
			} else {

				if ($userInfo->is_active != '1' && $userInfo->is_active !== '0') {
					$message = CommonHelper::getUserStatusMessage($userInfo->is_active);
					$validator->errors()->add('user_id', $message);
				}
			}
		});

		if ($validator->fails()) {
			return ResponseHelper::missing($validator->errors());
		} else {
			$check = DB::table('user_bank_infos')
				->select('id')
				->where('user_id', $userId)
				->where('account_number', $request->account_number)
				->count();

			if ($check > 0) {
				return ResponseHelper::failed('Account number is already added.');
			}

			$check = DB::table('user_bank_infos')
				->select('id')
				->where('user_id', $userId)
				->where('is_primary', '1')
				->where('is_active', '1')
				->count();
			if ($check > 1) {
				$is_primary = '0';
			} else {
				$is_primary = '1';
			}
			//insert bank info to user_bank_infos
			$insert = DB::table('user_bank_infos')->insert([
				'user_id' => $userId,
				'beneficiary_name' => ucwords($request->beneficiary_name),
				'account_number' => $request->account_number,
				'ifsc' => strtoupper($request->ifsc),
				'is_active' => '1',
				'is_verified' => '0',
				'is_primary' => $is_primary,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			]);

			if ($insert) {
				$businessInfo = DB::table('business_infos')
					->select('id', 'user_id')
					->where('user_id', $userId)
					->first();

				if (!empty($businessInfo)) {
					DB::table('business_infos')
						->where('user_id', $userId)
						->update([
							'is_bank_updated' => '1',
							'updated_at' => date('Y-m-d H:i:s')
						]);
				} else {
					DB::table('business_infos')
						->where('user_id', $userId)
						->insert([
							'user_id' => $userId,
							'is_bank_updated' => '1',
							'created_at' => date('Y-m-d H:i:s')
						]);
				}
				return ResponseHelper::success('Bank Account added successfully.');
			}
		}
	}
	public function deleteIp(Request $request)
	{
		$userId = $request->user()->id;
		$validator = Validator::make($request->all(), [
			'id' => 'required'

		]);
		if ($validator->fails()) {
			return ResponseHelper::missing($validator->errors());
		} else {
			$id = $request->id;
			$authClientCheck = DB::table('ip_whitelists')
				->where('id', $id)
				->where('user_id', $userId)
				->count('id');

			if ($authClientCheck > 0) {
				DB::table('ip_whitelists')
					->where('id', trim($id))
					->update(
						[
							'is_active' => '0',
							'updated_at' => date('Y-m-d H:i:s')
						]
					);
				return ResponseHelper::success('IP deleted successfully');
			}
		}
		return ResponseHelper::failed('Something went wrong.');
	}

	public function userServiceList(Request $request)
	{
		$userId = $request->user()->id;
		$UserService = DB::table('user_services')
			->select('user_services.service_id', 'user_services.service_account_number', 'user_services.transaction_amount', 'global_services.service_name', 'global_services.service_type')
			->join('global_services', 'global_services.service_id', '=', 'user_services.service_id')
			->where('user_services.user_id', $userId)
			->where('user_services.is_active', '1');
		if (isset($request->service_id) && $request->service_id) {
			$UserService->where('user_services.service_id', $request->service_id);
		}
		$resp['totalAmount'] = DB::table('user_services')
			->join('global_services', 'global_services.service_id', '=', 'user_services.service_id')
			->where('user_services.user_id', $userId)
			->where('user_services.is_active', '1')
			->where('global_services.service_type', '1')
			->sum('transaction_amount');

		$resp['serviceList'] = $UserService->get();

		if ($resp['serviceList']->isNotEmpty()) {
			return ResponseHelper::success('Record fetched successfully.', $resp);
		}

		return ResponseHelper::failed('Record not found.');
	}

	public function changePassword(Request $request)
	{
		$userId = $request->user()->id;
		$validator = Validator::make($request->all(), [
			'currentPassword' => 'required',
			'newPassword' => 'required|string|min:6',
			'confirmPassword' => 'required|min:6|same:newPassword',
		]);
		$validator->after(function ($validator) use ($request, $userId) {
			if (!(Hash::check($request->get('currentPassword'), Auth::user()->password))) {
				// The passwords matches
				$validator->errors()->add('currentPassword', "Your current password does not matches with the password.");
			} else if (strcmp($request->get('currentPassword'), $request->get('newPassword')) == 0) {
				// Current password and new password same
				$validator->errors()->add('newPassword', "New Password cannot be same as your current password.");
			}
		});
		if ($validator->fails()) {
			return ResponseHelper::missing($validator->errors());
		} else {
			//Change Password
			$user = Auth::user();
			$user->password = bcrypt($request->get('newPassword'));
			$user->save();

			return ResponseHelper::success("Password successfully changed!");
		}

		return ResponseHelper::failed('Something Went Wrong.');
	}

	public function loginActivityLog(Request $request)
	{
		$userId = $request->user()->id;
		$data = DB::table('activity_logs')->select('ip', 'agent', 'created_at')->where('type', 'verify_login_otp')->where('user_id', $userId)->orderBy('id', 'desc')->limit(10)->get();

		if ($data->isNotEmpty()) {
			return ResponseHelper::success('Record fetched successfully.', $data);
		}
		return ResponseHelper::failed('Record not found.');
	}

	public function getWebhook(Request $request)
	{
		try {
			$userId = $request->user()->id;
			$data = DB::table('webhooks')->select('webhook_url as url', 'secret as webHookSecret', 'header_key as headerKey', 'header_value as headerValue', 'updated_at as updatedAt')->where('user_id', $userId)->first();

			if (!empty($data)) {
				return ResponseHelper::success('Record fetched successfully.', $data);
			}
			return ResponseHelper::failed('Record not found.');
		} catch (Exception $e) {
			return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
		}
	}

	public function updateWebhook(Request $request)
	{
		try {
			$userId = $request->user()->id;
			$validator = Validator::make($request->all(), [
				'url' => 'required|url',
				"webhookSecret" => 'required|string|max:255',
				"headerKey" => 'nullable|string|max:255',
				"headerValue" => 'nullable|string|max:255'
			]);

			if ($validator->fails()) {
				return ResponseHelper::missing($validator->errors());
			}



			$webdata = [
				"webhook_url" => trim($request->url),
				"secret" => trim($request->webhookSecret),
				"header_key" => !empty(trim($request->headerKey)) ? trim($request->headerKey) : NULL,
				"header_value" => !empty(trim($request->headerValue)) ? trim($request->headerValue) : NULL
			];

			$data = DB::table('webhooks')->select('webhook_url', 'secret')->where('user_id', $userId)->first();
			if (!empty($data)) {
				$webdata['updated_at'] = date('Y-m-d H:i:s');
				$result = DB::table('webhooks')->where('user_id', $userId)->update($webdata);
			} else {
				$webdata['user_id'] = $userId;
				$result = DB::table('webhooks')->insert($webdata);
			}

			return ResponseHelper::success('Webhook updated successfully.', $result);
		} catch (Exception $e) {
			return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
		}
	}
}
