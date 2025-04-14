<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class VerifyEmailController extends Controller
{

    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id')); //takes user ID from verification link. Even if somebody would hijack the URL, signature will be fail the request
        if ($user->hasVerifiedEmail()) {
            // return redirect('/home');
            return redirect(env('APP_USERAPP_URL') . '/login');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $message = 'email_verify_success';
        } else {
            $message = 'email_verify_failed';
        }

        // $message = __('Your email has been verified.');

        // return redirect('login')->with('status', $message); //if user is already logged in it will redirect to the dashboard page
        return redirect(env('APP_USERAPP_URL') . "/email/verify?message=$message");
    }

    // public function resendEmail(Request $request)
    // {
    //     $user = User::where('email', $request->email)->first();

    //     event(new Registered($user));
    //     $message = __('Verification link has been sent on your email. Please click on link to verify email.');

    //     return redirect('login')->with('status', $message);
    //     exit;
    // }
}
