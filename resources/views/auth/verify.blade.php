
    @extends('layouts.loginapp')
    @section('title', __('Verify Your Email Address'))
    @section('content')
            <!--begin::Main-->
            <div class="auth-box-w">
            <div class="logo-w">
            <a href="#"><img alt="" src="{{asset('images/logo.png')}}"></a>
            </div>
            <h4 class="auth-header">
            Login Form
            </h4>
            @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                {{ __('A fresh verification link has been sent to your email address.') }}
                            </div>
                        @endif
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                        {{ __('If you did not receive the email') }},
            <form id="kt_sign_in_forms"
            action="{{ route('verification.resend') }}" method="post">
            @csrf
            <div class="buttons-w">
                <button class="btn btn-primary" type="submit">     {{ __('click here to request another') }}</button>
            </div>
            </form>
            <!--end::Main-->
    @endsection
