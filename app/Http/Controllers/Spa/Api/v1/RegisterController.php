<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ResponseHelper;
use Illuminate\Auth\Events\Registered;
use App\Models\User;


class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:users',
                'mobile' => 'required|numeric|digits:10',
                'password' => 'required|min:6',
                'confirm_password' => 'required|min:6|same:password',
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


        $email = mb_strtolower(trim($request->email));
        $mobile = trim($request->mobile, '-');
        $mobile = trim($mobile, '+');

        $user = User::create([
            'name' => ucfirst(trim($request->first_name)) . ' ' . ucfirst(trim($request->last_name)),
            'email' =>  $email,
            'account_number' => CommonHelper::newWalletNumber(),
            'mobile' => trim($mobile),
            'is_profile_updated' => '0',
            'is_active' => '0',
            'signup_status' => '1',
            'password' => Hash::make(trim($request->password)),
        ]);

        if ($user) {
            event(new Registered($user));

            return ResponseHelper::success('User Created Successfully.');
        } else {
            return ResponseHelper::failed('Something Went Wrong.');
        }
    }
}
