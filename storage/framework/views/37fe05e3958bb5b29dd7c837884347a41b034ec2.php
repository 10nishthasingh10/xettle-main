<?php $__env->startSection('title','Sign In'); ?>
<?php $__env->startSection('content'); ?>
<!--begin::Main-->
<div class="row">
    <div class="col-md-6">
        <img class="img-fluid" src="<?php echo e(asset('images/payment.avif')); ?>">
    </div>
    <div class="col-md-6">
        <div class="auth-box-w">
    <div class="logo-w">
        <a href=""><img alt="" src="<?php echo e(asset('images/logo.png')); ?>" ></a>
    </div>
    <h4 class="auth-header">
        Login to Xettle
    </h4>
    <form id="login-form" class="mobilehide" action="<?php echo e(url('/sendOtp')); ?>" method="POST" role="add-login">
        <?php echo csrf_field(); ?>
        <?php if($errors->has('message')): ?>
        <div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('message')); ?></div>
        <?php endif; ?>
        <?php if( Session::has( 'status' )): ?>
             <div class="alert alert-success" role="alert"><?php echo e(Session::get( 'status' )); ?></div>
        <?php endif; ?>
        <input type="hidden" name="message" />

        <div class="form-group <?php if($errors->has('email')): ?> has-danger <?php endif; ?>">
            <label for="">E-Mail Address</label>
            <input class="form-control " id="user_email" name="email" value="<?php echo e(old('email')); ?>" placeholder="Enter your E-Mail Address" type="email">
            <?php if($errors->has('email')): ?>
            <div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('email')); ?></div>
            <?php endif; ?>
            <div class="pre-icon os-icon os-icon-user-male-circle"></div>
        </div>

        <div class="form-group <?php if($errors->has('password')): ?> has-danger <?php endif; ?>">
            <label for="">Password</label>
            <input class="form-control" placeholder="Enter your Password" value="<?php echo e(old('password')); ?>" name="password" type="password">
            <?php if($errors->has('password')): ?>
            <div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('password')); ?></div>
            <?php endif; ?>
            <div class="pre-icon os-icon os-icon-fingerprint"></div>
        </div>

        <div class="form-group">
            <div class="g-recaptcha row" data-sitekey="<?php echo e(env('NOCAPTCHA_SITEKEY')); ?>"></div>
        </div>

        <div class="buttons-w">
            <button name="login-submit" id="login-submit" tabindex="4" class="btn btn-info" data-request="ajax-submit" data-target='[role="add-login"]' type="submit">Sign In</button>
            <div class="form-check-inline">
                <label class="form-check-label"><input class="form-check-input" name="remember" type="checkbox">Remember Me</label>
            </div>
            <div class="py-1">
                <a href="<?php echo e(route('password.request')); ?>"><?php echo e(__('Forgot Password')); ?></a>
            </div>
        </div>
        <div class="buttons-w">
            <div class="text-center ">
                <small class="text-muted" style="font-weight: 500;font-size: 1.2rem;">Are you a developer?</small>
                <a href="<?php echo e(url('register')); ?>"><button class="mr-2 mb-2 btn btn-warning mt-2" type="button">Create New Account</button></a>
            </div>
        </div>
    </form>
    <form id="Verify-otp" class="showotpform" action="<?php echo e(url('verifyotp')); ?>" method="POST" style="display:none" role="Verify-otp">
        <?php echo csrf_field(); ?>
        <span class="deliveryreponseOtp"></span>
        <div class="form-group">
            <label for="">Otp <span class="help-block">*</span></label>
            <input type="hidden" name="user_id" class="form-control" id="encrypt_user_id" />
            <input type="number" name="otp" tabindex="2" minlength="6" maxlength="6" class="form-control" placeholder="OTP">
            <span class="<?php if($errors->first('otp')): ?>help-block <?php endif; ?>"><?php echo e($errors->first('otp')); ?> </span>
            <div class="pre-icon os-icon os-icon-user-male-circle"></div>
        </div>
        <div class="buttons-w">
            <button type="submit" name="verifyotp-submit" id="verifyotp-submit" tabindex="4" class="btn btn-info" data-request="ajax-submit" data-target='[role="Verify-otp"]'>Verify OTP</button>
            <span class="btn btn-warning" id="resendotp">Resend OTP</span>
        </div>
        <span class="deliveryreponse"></span><br />
    </form>
    <form action="" method="POST" style="display:none" id="resendEmail">
        <?php echo csrf_field(); ?>
        <input type="text" id="resend_email" name="email" value="">
        <input type="submit" value="Click here">
    </form>
</div>
    </div>
</div>

<!--end::Main-->
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
<script src="<?php echo e(url('public/bower_components/select2/dist/js/select2.full.min.js')); ?>"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo e(asset('js/script.js')); ?>"></script>
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
            url: "<?php echo e(secure_url('resendotp/')); ?>/" + userId,
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
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.loginapp', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/auth/login.blade.php ENDPATH**/ ?>