@extends('layouts.loginapp')
@section('title','Sign In')
@section('content')
<!--begin::Main-->
<div class="row">
    <div class="col-md-6">
        <img class="img-fluid" src="{{asset('images/payment.avif')}}">
    </div>
    <div class="col-md-6">
        <div class="auth-box-w">
    <div class="logo-w">
        <a href=""><img alt="" src="{{asset('images/logo.png')}}" ></a>
    </div>
    <h4 class="auth-header">
        Login to Xettle
    </h4>
    <form id="login-form" class="mobilehide" action="{{url('/sendOtp')}}" method="POST" role="add-login">
        @csrf
        @if($errors->has('message'))
        <div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('message') }}</div>
        @endif
        @if( Session::has( 'status' ))
             <div class="alert alert-success" role="alert">{{ Session::get( 'status' ) }}</div>
        @endif
        <input type="hidden" name="message" />

        <div class="form-group @if($errors->has('email')) has-danger @endif">
            <label for="">E-Mail Address</label>
            <input class="form-control " id="user_email" name="email" value="{{old('email')}}" placeholder="Enter your E-Mail Address" type="email">
            @if($errors->has('email'))
            <div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('email') }}</div>
            @endif
            <div class="pre-icon os-icon os-icon-user-male-circle"></div>
        </div>

        <div class="form-group @if($errors->has('password')) has-danger @endif">
            <label for="">Password</label>
            <input class="form-control" placeholder="Enter your Password" value="{{old('password')}}" name="password" type="password">
            
            @if($errors->has('password'))
            <div class="help-block form-text with-errors form-control-feedback" role="alert">{{ $errors->first('password') }}</div>
            @endif
            <div class="pre-icon os-icon os-icon-fingerprint"></div>
        </div>

        <div class="form-group">
            <div class="g-recaptcha row" data-sitekey="{{env('NOCAPTCHA_SITEKEY')}}"></div>
        </div>

        <div class="buttons-w">
            <button name="login-submit" id="login-submit" tabindex="4" class="btn btn-info" data-request="ajax-submit" data-target='[role="add-login"]' type="submit">Sign In</button>
            <div class="form-check-inline">
                <label class="form-check-label"><input class="form-check-input" name="remember" type="checkbox">Remember Me</label>
            </div>
            <div class="py-1">
                <a href="{{ route('password.request') }}">{{ __('Forgot Password') }}</a>
            </div>
        </div>
        <div class="buttons-w">
            <div class="text-center ">
                <small class="text-muted" style="font-weight: 500;font-size: 1.2rem;">Are you a developer?</small>
                <a href="{{url('register')}}"><button class="mr-2 mb-2 btn btn-warning mt-2" type="button">Create New Account</button></a>
            </div>
        </div>
    </form>
    <form id="Verify-otp" class="showotpform" action="{{url('verifyotp')}}" method="POST" style="display:none" role="Verify-otp">
        @csrf   
    <input type="hidden" name="latitude" class="form-control" id="latitude" value="" />
    <input type="hidden" name="longitude" class="form-control" id="longitude" value=""/>
        <span class="deliveryreponseOtp"></span>
        <div class="form-group">
            <label for="">Otp <span class="help-block">*</span></label>
            <input type="hidden" name="user_id" class="form-control" id="encrypt_user_id" />
            <input type="number" name="otp" tabindex="2" minlength="6" maxlength="6" class="form-control" placeholder="OTP">
            <span class="@if($errors->first('otp'))help-block @endif">{{$errors->first('otp')}} </span>
            <div class="pre-icon os-icon os-icon-user-male-circle"></div>
        </div>
        <div class="buttons-w">
            <button type="button" name="verifyotp-submits" id="verifyotp-submits" class="btn btn-info location_section" onclick="getLocation()">Verify OTP</button>
            <button type="submit" name="verifyotp-submit" id="verifyotp-submit" style="display:none" tabindex="4" class="btn btn-info input_section" data-request="ajax-submit" data-target='[role="Verify-otp"]'>Verify OTP</button>
            <span class="btn btn-warning" id="resendotp">Resend OTP</span>
        </div>
        <span class="deliveryreponse"></span><br />
    </form>
    <form action="" method="POST" style="display:none" id="resendEmail">
        @csrf
        <input type="text" id="resend_email" name="email" value="">
        <input type="submit" value="Click here">
    </form>
</div>
    </div>
</div>

<!--end::Main-->
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
<script src="{{url('public/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('js/script.js')}}"></script>
<script>

    function resendEmail() {
        var email = $('#user_email').val();

        $('#resend_email').val(email);
        $('#resendEmail').css('display', 'block');
        alert($('#resend_email').val());
        $('#resendEmail').submit();
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '#resendotp', function() {
        var userId = $('#encrypt_user_id').val();
        $.ajax({
            url: "{{custom_secure_url('resendotp/')}}/" + userId,
            method: 'POST',
            async: false,
            data: {
                '_token': $token
            },
            success: function(response) {
                if (response.trim() == "true") {
                    $(".deliveryreponse").show();
                    $(".deliveryreponse").html("OTP Sent Successfully");
                }
                setTimeout(function() {
                    $(".deliveryreponse").hide();
                }, 1000 * 10);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {}
        });
    });

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var latitude = position.coords.latitude;
                var longitude = position.coords.longitude;

                if (latitude !== null && longitude !== null) {
                    document.getElementById("latitude").value = latitude;
                    document.getElementById("longitude").value = longitude;

                    $(".input_section").prop('disabled', false).show();
                    $(".location_section").prop('disabled', true).hide();

                } else {
                    alert('Please enable location services to proceed.');
                }
            }, function(error) {
                alert("Error getting your location: " + error.message);
                (".location_section").prop('disabled', true).hide();
            });
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }


</script>
@endsection