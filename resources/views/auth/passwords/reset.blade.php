@extends('layouts.loginapp')
@section('title', __('Reset Password'))
@section('content')
		<!--begin::Main-->
		<div class="auth-box-w">
        <div class="logo-w">
          <a href="#"><img alt="" src="{{asset('images/logo.png')}}"></a>
        </div>
        <h4 class="auth-header">
     {{ __('Update Password')}}
        </h4>
       

		<form id="kt_sign_in_forms"
        action="{{ route('password.request') }}" method="post">
        @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
		@csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group @if($errors->has('email')) has-danger @endif">
            <label for=""> {{__('E-Mail Address') }}</label></label>
			<input class="form-control" placeholder="Enter your E-Mail Address" name="email" type="email">
			@if($errors->has('email'))
					<div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('email') }}</div>
			@endif
            <div class="pre-icon os-icon os-icon-email-2-at2"></div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group @if($errors->has('password')) has-danger @endif">
                <label for=""> {{__('Password') }}</label>
				<input class="form-control" placeholder="Password" name="password" type="password">
				@if($errors->has('password'))
					<div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('password') }}</div>
			    @endif
                <div class="pre-icon os-icon os-icon-fingerprint"></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group @if($errors->has('password_confirmation')) has-danger @endif">
                <label for=""> {{__('Confirm Password') }}</label>
				<input class="form-control" name="password_confirmation" placeholder="Confirm Password" required type="password">
				@if($errors->has('password_confirmation'))
					<div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('password_confirmation') }}</div>
			    @endif
          <div class="pre-icon os-icon os-icon-fingerprint"></div>
              </div>
            </div>
          </div>
                     
        
          <div class="buttons-w">
            <button class="mr-2 mb-2 btn btn-primary" type="submit">   {{ __('Reset Password') }}</button>
            <a href="{{url('login')}}" class="text-white mr-2 mb-2 btn btn-primary" >Back to Sign In</a>
          </div>
        
        </form>
  
		<!--end::Main-->
@endsection

