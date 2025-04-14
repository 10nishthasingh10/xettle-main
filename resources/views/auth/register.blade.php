@extends('layouts.loginapp')
@section('title','Sign Up')
@section('content')

<div class="auth-box-w wider">
  <div class="logo-w">
    <a href=""><img alt="" src="{{asset('images/logo.png')}}"></a>
  </div>
  <h4 class="auth-header">
    Create new account
  </h4>
  <input type="hidden" name="message">
  <form class="form w-100" role="signUpForm" id="signUpForm" method="POST" action="{{ url('signUp') }}">
    <!--begin::Heading-->
    @csrf
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <div class="pre-icon os-icon os-icon-user-male-circle"></div>
          <label for=""> First Name</label>
          <input class="form-control" name="first_name" value="{{old('first_name')}}" placeholder="Enter first name" type="text" required>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group @if($errors->has('last_name')) has-danger @endif">
          <label for=""> Last Name</label>
          <input class="form-control" name="last_name" value="{{old('last_name')}}" placeholder="Enter last name" type="text" required>
          @if($errors->has('last_name'))
          <div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('last_name') }}</div>
          @endif
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group @if($errors->has('email')) has-danger @endif">
          <label for=""> Email address</label>
          <input class="form-control" placeholder="Enter email" value="{{old('email')}}" name="email" type="email">
          @if($errors->has('email'))
          <div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('email') }}</div>
          @endif
          <div class="pre-icon os-icon os-icon-email-2-at2"></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group @if($errors->has('mobile')) has-danger @endif">
          <label for=""> Mobile</label>
          <input class="form-control" placeholder="Enter mobile" min="0" value="{{old('mobile')}}" name="mobile" type="number">
          @if($errors->has('mobile'))
          <div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('mobile') }}</div>
          @endif
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group @if($errors->has('password')) has-danger @endif">
          <label for=""> Password</label>
          <input class="form-control" placeholder="Password" value="{{old('password')}}" name="password" type="password">
          @if($errors->has('password'))
          <div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('password') }}</div>
          @endif
          <div class="pre-icon os-icon os-icon-fingerprint"></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group @if($errors->has('confirm_password')) has-danger @endif">
          <label for="">Confirm Password</label>
          <input class="form-control" name="confirm_password" value="{{old('confirm_password')}}" placeholder="Confirm Password" type="password">
          @if($errors->has('confirm_password'))
          <div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('confirm_password') }}</div>
          @endif
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <div class="form-group">
          <div class="g-recaptcha" data-sitekey="{{env('NOCAPTCHA_SITEKEY')}}"></div>
        </div>
      </div>
    </div>
    <div class="element-box-content" style="margin-top: 30px;">
      <button class="mr-2 mb-2 btn btn-primary" type="button" data-request="ajax-submit" data-target='[role="signUpForm"]'> Sign Up</button>
      <a href="{{url('login')}}" class="text-white mr-2 mb-2 btn btn-primary">Back to Sign In</a>
    </div>
  </form>
</div>
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
<script src="{{url('public/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('js/script.js')}}"></script>
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
</script>
@endsection