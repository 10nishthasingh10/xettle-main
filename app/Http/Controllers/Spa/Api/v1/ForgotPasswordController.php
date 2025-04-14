<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ResponseHelper;
use App\Notifications\SMSNotifications;
use App\Jobs\SendEmailOtpJob;
use Exception;
use Illuminate\Support\Facades\DB;

class ForgotPasswordController extends Controller
{
	public function forgotPassword(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
		]);

		$validator->after(function ($validator) use ($request) {
			$user = DB::table('users')
				->select('is_active')
				->where('email', $request->email)
				->first();
			if (empty($user)) {
				$validator->errors()->add('email', "Email is not registered.");
			}
		});

		if ($validator->fails()) {
			return ResponseHelper::missing($validator->errors());
		} else {
			$user = DB::table('users')->where('email', $request->email)->first();
			if ($user) {
				$data['email'] = $user->email;
				$data['mobile'] = substr($user->mobile, 6);
			}

			return ResponseHelper::success('Record fetched successfully.', $data);
		}

		return ResponseHelper::failed('Something Went Wrong.');
	}

	public function sentOtp(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'email' => 'required|email',
				'mobile' => 'required|numeric|digits:10'
			]);

			$validator->after(function ($validator) use ($request) {

				$user = DB::table('users')
					->select('is_active')
					->where('email', $request->email)
					->where('mobile', $request->mobile)
					//->where('is_active','0')
					->first();

				if (empty($user)) {
					$validator->errors()->add('mobile', 'Mobile does not match with our record.');
				}
			});

			if ($validator->fails()) {
				return ResponseHelper::missing($validator->errors());
			} else {
				$userDetails = DB::table('users')->where('email', $request->email)->first();
				//$GlobalSMSConfig = GlobalConfig::select('attribute_1')->where(['slug' => 'login_otp_sms'])->first();
				//$GlobalEmailConfig = GlobalConfig::select('attribute_1')->where(['slug' => 'login_otp_email'])->first();

				$id = $userDetails->id;
				$otp = rand(100000, 999999);
				$otpresponce = SMSNotifications::sendOTP(array(
					'otp' => $otp,
					'user_id' => $id,
					'mobile' => $userDetails->mobile,
					'username' => $userDetails->name,
					'type' => 'forgotPasswordOTP'
				));
				// if ($otpresponce['status'] == "success") {
				// 	$otpRefId = $otpresponce['otpRefId'];
				// 	$userupdate['otp_sent_at'] = date('Y-m-d h:i:s');
				// 	$userupdate['otp'] = encrypt($otp);
				// 	DB::table('users')->where('id', $id)->update($userupdate);
				// }
				dispatch(new SendEmailOtpJob($request->email, $otp, $userDetails));

				return ResponseHelper::success('Otp sent successfully.', ['otpRefId' => @$otpresponce['otpRefId']]);
			}
		} catch (Exception $e) {
			return ResponseHelper::failed('Something Went Wrong.');
		}
	}

	public function resetPassword(Request $request)
	{
		try {

			$validator = Validator::make($request->all(), [
				'otpRefId' => 'required',
				'otp' => 'required',
				'password' => 'required|min:6',
				'confirm_password' => 'required|min:6|same:password'

			]);

			$validator->after(function ($validator) use ($request) {

				$otp = $request->otp;

				$otpData = DB::table('otps')->where('otp_ref_id', $request->otpRefId)->where('is_validated', '0')->where('type', 'forgotPassword')->first();

				if (empty($otpData)) {
					$validator->errors()->add('otp', 'Please input correct otp.');
				} else {
					$user = DB::table('users')->where('id', $otpData->user_id)->first();
					if (!empty($user) && $otp != decrypt($otpData->otp)) {
						$validator->errors()->add('otp', 'Please input correct otp.');
					}
				}
			});

			if ($validator->fails()) {
				return ResponseHelper::missing($validator->errors());
			} else {
				$otpData = DB::table('otps')->where('otp_ref_id', $request->otpRefId)->first();
				// $user = DB::table('users')->where('id', $otpData->user_id)->first();
				DB::table('users')->where('id', $otpData->user_id)->update(['password' => Hash::make($request->password)]);
				DB::table('otps')->where('otp_ref_id', $request->otpRefId)->update(['is_validated' => '1']);

				return ResponseHelper::success('Password reset successfully.');
			}

			return ResponseHelper::failed('Something Went Wrong.');
		} catch (Exception $e) {
			return ResponseHelper::failed('Something Went Wrong!');
		}
	}

	public function resendOtp(Request $request)
	{
		try {

			$validator = Validator::make($request->all(), [

				'otpRefId' => 'required'
			]);

			$validator->after(function ($validator) use ($request) {

				$otpRefId = $request->otpRefId;
				$otp = DB::table('otps')->where('otp_ref_id', $otpRefId)->first();
				if (empty($otp)) {
					$validator->errors()->add('otpRefId', 'Record not matched.');
				}
			});
			if ($validator->fails()) {
				return ResponseHelper::missing($validator->errors());
			} else {
				$otpRefId = $request->otpRefId;
				$otp = DB::table('otps')->select('user_id', 'otp', 'mobile')->where('otp_ref_id', $otpRefId)->first();
				$userDetails = DB::table('users')->where('id', $otp->user_id)->first();
				$id = $userDetails->id;

				//decrypt OTP
				$decOtp = decrypt($otp->otp);

				SMSNotifications::sendOTP(array(
					'otp' => $decOtp,
					'user_id' => $id,
					'mobile' => $userDetails->mobile,
					'username' => $userDetails->name,
					'type' => 'forgotPasswordOTP'
				));
				// if ($otpresponce['status'] == "success") {
				// $otpRefId = $otpresponce['otpRefId'];
				// $userupdate['otp_sent_at'] = date('Y-m-d h:i:s');
				// $userupdate['otp'] = encrypt($otp->otp);
				// DB::table('users')->where('id', $id)->update($userupdate);
				// }

				dispatch(new SendEmailOtpJob($userDetails->email, $decOtp, $userDetails));

				return ResponseHelper::success('Otp sent successfully.', ['otpRefId' => $otpRefId]);
			}
		} catch (Exception $e) {
			return ResponseHelper::failed('Something Went Wrong.' . $e->getMessage());
		}
	}
}
