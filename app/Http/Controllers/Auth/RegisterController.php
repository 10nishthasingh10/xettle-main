<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\NishthaHelper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Validations\UserValidation;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use CommonHelper;
use Illuminate\Auth\Events\Registered;
use DB;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return  User::create([
            'name' => $data['first_name'].' '.$data['last_name'],
            'email' => $data['email'],
            'account_number' => CommonHelper::newWalletNumber(),
            'mobile' => $data['mobile'],
            'is_profile_updated' => '1',
            'password' => Hash::make($data['password']),
        ]);
    }

    public function signUp(Request $request)
    {
        $validation = new UserValidation($request);
        $validator   = $validation->signUp();
        $validator->after(function($validator) use ($request){
            if(!empty($request['g-recaptcha-response'])){
                $Return = CommonHelper::getCaptcha($request['g-recaptcha-response']);
                if($Return->success == false){
                    $validator->errors()->add('message','Your are a robot');
                }
            }
        });
        if($validator->fails()){
            $this->message=$validator->errors();

        }else{
            $email = trim($request->email);
            $mobile = trim($request->mobile, '-');
            $mobile = trim($mobile, '+');
            $user = User::create([
                'name' => $request->first_name.' '.$request->last_name,
                'email' =>  $email,
                'account_number' => CommonHelper::newWalletNumber(),
                'mobile' => $mobile,
                'is_profile_updated' => '1',
                'is_active' => '0',
                'password' => Hash::make($request->password),
            ]);
            // $user->sendEmailVerificationNotification();
            if($user){
                // \App\Helpers\NishthaHelper::logUserActivity($user->id, 'User signed up', 'signup', now());
                \ActivityLog::addToLog('signup', $user->id, "User signed up", $user->id);

                // \ActivityLog::addToLog('signup' , $user->id);
                event(new Registered($user));

                $this->status   = true;
                $this->modalStatus = true;
                $this->modal    = false;
                $this->alert    = false;
                $this->message  = "Sign Up Successfully";
                $this->redirect    = 'login';
                return $this->populateresponse();
            }else{
                $validator->errors()->add('message','Some Error');
            }

        }

        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => false,
                'data'      => $this->message
            ])
        );
    }
}
