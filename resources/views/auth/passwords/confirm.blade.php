@extends('layouts.loginapp')
@section('title','Confirm Password')
@section('content')
		<!--begin::Main-->
		<div class="auth-box-w">
        <div class="logo-w">
          <a href="#"><img alt="" src="{{asset('images/logo.png')}}"></a>
        </div>
        <h4 class="auth-header">
          Login Form
        </h4>
        {{ __('Please confirm your password before continuing.') }}

		<form id="kt_sign_in_forms"
        action="{{ route('password.confirm') }}" method="post">
		@csrf
          
          <div class="form-group @if($errors->has('password')) has-danger @endif">
            <label for="">Password</label>
			<input class="form-control" placeholder="Enter your password" name="password" type="password">
			@if($errors->has('password'))
				<div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('password') }}</div>
			@endif
            <div class="pre-icon os-icon os-icon-fingerprint"></div>
          </div>
        
          <div class="buttons-w">
            <button class="btn btn-primary" type="submit">Submit</button>
           
          </div>
          @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
          @endif
        </form>
  
		<!--end::Main-->
@endsection
