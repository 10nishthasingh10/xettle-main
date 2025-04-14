@extends('layouts.loginapp')
@section('title',__('Reset Password'))
@section('content')
		<!--begin::Main-->
		<div class="auth-box-w">
        <div class="logo-w">
          <a href="#"><img alt="" src="{{asset('images/logo.png')}}"></a>
        </div>
        <h4 class="auth-header">
        {{__('Reset Password') }}
        </h4>
    

		<form id="kt_sign_in_forms"
        action="{{ route('password.email') }}" method="post">
		@csrf
        @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
          <div class="form-group @if($errors->has('password')) has-danger @endif">
            <label for="">{{ __('E-Mail Address') }}</label>
			<input class="form-control" placeholder="Enter your E-Mail Address" name="email" type="email">
			@if($errors->has('email'))
				<div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('email') }}</div>
			@endif
            <div class="pre-icon os-icon os-icon-email-2-at2"></div>
          </div>
        
          <div class="buttons-w">
            <button class="mr-2 mb-2 btn btn-primary" type="submit">     {{ __('Send  Link') }}</button>
            <a href="{{url('login')}}" class="text-white mr-2 mb-2 btn btn-primary" >Back to Sign In</a>
          </div>
        
        </form>
  
		<!--end::Main-->
@endsection
