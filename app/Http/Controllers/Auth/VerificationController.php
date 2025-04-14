<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use DB;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */
    use VerifiesEmails;
    // use VerifiesEmails {
    //     verify as parentVerify;
    // }

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = 'user/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('auth')->except('verify');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    // public function verify(Request $request)
    // {

    //     if ($request->user() && $request->user() != $request->route('id')) {
    //         Auth::logout();
    //     }
    //     $user = User::find($request->route('id'));
        
    //     if (! $request->user()) {
    //          $update['email_verified_at'] = now();
    //          DB::table('users')->where('id', $request->route('id'))->update($update);
    //          return redirect('/login');
    //         //Auth::loginUsingId($request->route('id'), true);
    //     }

    //     return $this->parentVerify($request);
    // }
}
