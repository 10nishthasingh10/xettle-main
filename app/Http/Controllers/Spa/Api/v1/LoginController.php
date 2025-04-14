<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\ActivityLogHelper;
use App\Helpers\CommonHelper;
use App\Helpers\RequestKeeper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;
use App\Notifications\SMSNotifications;
use App\Jobs\SendEmailOtpJob;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{

    public function authenticate(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required|string',
                    'recaptcha' => 'required'
                ],
                [
                    'recaptcha.required' => "Google Re-captcha is required"
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }

            if (!empty($request['recaptcha'])) {
                $return = CommonHelper::getCaptcha($request['recaptcha']);
                if ($return->success == false) {
                    return ResponseHelper::failed('Your are a robot.');
                }
            }

            $userData = DB::table('users')->where('email', $request->email)->first();
            if (empty($userData)) {
                return ResponseHelper::failed('Invalid login credentials.');
            } else if ($userData->is_active == '2') {
                return ResponseHelper::failed('Your account is not activated.');
            } else if (empty($userData->email_verified_at)) {
                return ResponseHelper::pending('Please verify your email.');
            }


            // if (Auth::attempt($credentials)) {
            if (Hash::check($request->password, $userData->password)) {
                // $userDetails = DB::table('users')->where('email', $request->email)->first();
                $id = $userData->id;
                $otp = rand(100000, 999999);

                $otpresponce = SMSNotifications::sendOTP(array(
                    'otp' => $otp,
                    'user_id' => $userData->id,
                    'mobile' => $userData->mobile,
                    'username' => $userData->name,
                    'type' => 'loginOTP'
                ));

                // if ($otpresponce['status'] == "success") {
                // $otpRefId = $otpresponce['otpRefId'];
                // $userupdate['otp_sent_at'] = date('Y-m-d h:i:s');
                // $userupdate['otp'] = encrypt($otp);
                // DB::table('users')->where('id', $id)->update($userupdate);
                // }

                dispatch(new SendEmailOtpJob($request->email, $otp, $userData));
                ActivityLogHelper::addToLog('send_login_otp', $userData->id);

                return ResponseHelper::success('Otp sent successfully.', ['otpRefId' => @$otpresponce['otpRefId']]);
            }

            return ResponseHelper::failed('Invalid login credentials!');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Error: ' . $e->getMessage());
        }
    }

    public function verifyOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'otpRefId' => 'required',
                'otp' => 'required|digits:6',
                'password' => 'required|min:6',
                'email' => 'required|email'
            ]);

            $validator->after(function ($validator) use ($request) {

                $otp = $request->otp;

                $otpData = DB::table('otps')->where('otp_ref_id', $request->otpRefId)->where('is_validated', '0')->where('type', 'login')->first();
                if (empty($otpData)) {
                    $validator->errors()->add('otp', 'Please input correct otp.');
                } else {
                    if ($otp != decrypt($otpData->otp)) {
                        $validator->errors()->add('otp', 'Please input correct otp.');
                    }
                }
            });

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            } else {
                $credentials = $request->only('email', 'password');
                if (Auth::attempt($credentials)) {

                    // $request->session()->regenerate();
                    $user = Auth::user();


                    DB::table('otps')->where('otp_ref_id', $request->otpRefId)->update(['is_validated' => '1']);
                    ActivityLogHelper::addToLog('verify_login_otp', $user->id);
                    DB::table('users')->where('id', $user->id)->update(['otp' => null]);

                    $userToken = $user->createToken($user->email . '_Token')->plainTextToken;


                    //generate identifier token
                    $identifierToken = base64_encode(RequestKeeper::generateKey());

                    $lastId = DB::table('personal_access_tokens')
                        ->select('id')
                        ->where('tokenable_id', $user->id)
                        ->orderBy('id', 'desc')
                        ->first();

                    DB::table('personal_access_tokens')
                        ->where('id', $lastId->id)
                        ->update(['identifier_token' => $identifierToken]);


                    $data['name'] = $user->name;
                    $data['mobile'] = $user->mobile;
                    $data['account_number'] = $user->account_number;
                    $data['transaction_amount'] = $user->transaction_amount;
                    $data['signup_step'] = $user->signup_status;
                    $data['is_admin'] = $user->is_admin;
                    $data['toekn'] = $userToken;
                    $data['identifierToken'] = $identifierToken;

                    return ResponseHelper::success('Logged Successfully.', $data);
                }
            }
        } catch (Exception $e) {
            return ResponseHelper::failed('Something Went Wrong! ' . $e->getMessage() . ' : ' . $e->getLine());
        }
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        Auth::guard('web')->logout();
        return ResponseHelper::success('Logged out Successfully.');
    }


    public function loginToOldPlatform(Request $request)
    {
        try {
            $userId = $request->user()->id;

            if (Auth::guard('web')->loginUsingId($userId)) {
                $length = 40;
                $token = bin2hex(random_bytes($length));
                $update['remember_token'] = $token;
                $update['otp'] = null;

                DB::table('users')->where('id', $userId)
                    ->update($update);

                return ResponseHelper::success('success');
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }


    /**
     * Resend email verification link
     */
    public function resendEmail(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if ($user->hasVerifiedEmail()) {
                return ResponseHelper::failed('Your email is already verified.');
            }

            // event(new Registered($user));
            $user->sendEmailVerificationNotification();
            $message = __('Verification link has been sent on your email. Please click on link to verify email.');

            // return redirect('login')->with('status', $message);
            return ResponseHelper::success($message);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong, Please try after some time', ['error' => $e->getMessage()]);
        }
    }
}
