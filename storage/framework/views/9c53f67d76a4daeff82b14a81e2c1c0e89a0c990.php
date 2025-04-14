<?php $__env->startSection('title','Sign Up'); ?>
<?php $__env->startSection('content'); ?>

<div class="auth-box-w wider">
  <div class="logo-w">
    <a href=""><img alt="" src="<?php echo e(asset('images/logo.png')); ?>"></a>
  </div>
  <h4 class="auth-header">
    Create new account
  </h4>
  <input type="hidden" name="message">
  <form class="form w-100" role="signUpForm" id="signUpForm" method="POST" action="<?php echo e(url('signUp')); ?>">
    <!--begin::Heading-->
    <?php echo csrf_field(); ?>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <div class="pre-icon os-icon os-icon-user-male-circle"></div>
          <label for=""> First Name</label>
          <input class="form-control" name="first_name" value="<?php echo e(old('first_name')); ?>" placeholder="Enter first name" type="text" required>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group <?php if($errors->has('last_name')): ?> has-danger <?php endif; ?>">
          <label for=""> Last Name</label>
          <input class="form-control" name="last_name" value="<?php echo e(old('last_name')); ?>" placeholder="Enter last name" type="text" required>
          <?php if($errors->has('last_name')): ?>
          <div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('last_name')); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group <?php if($errors->has('email')): ?> has-danger <?php endif; ?>">
          <label for=""> Email address</label>
          <input class="form-control" placeholder="Enter email" value="<?php echo e(old('email')); ?>" name="email" type="email">
          <?php if($errors->has('email')): ?>
          <div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('email')); ?></div>
          <?php endif; ?>
          <div class="pre-icon os-icon os-icon-email-2-at2"></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group <?php if($errors->has('mobile')): ?> has-danger <?php endif; ?>">
          <label for=""> Mobile</label>
          <input class="form-control" placeholder="Enter mobile" min="0" value="<?php echo e(old('mobile')); ?>" name="mobile" type="number">
          <?php if($errors->has('mobile')): ?>
          <div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('mobile')); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group <?php if($errors->has('password')): ?> has-danger <?php endif; ?>">
          <label for=""> Password</label>
          <input class="form-control" placeholder="Password" value="<?php echo e(old('password')); ?>" name="password" type="password">
          <?php if($errors->has('password')): ?>
          <div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('password')); ?></div>
          <?php endif; ?>
          <div class="pre-icon os-icon os-icon-fingerprint"></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group <?php if($errors->has('confirm_password')): ?> has-danger <?php endif; ?>">
          <label for="">Confirm Password</label>
          <input class="form-control" name="confirm_password" value="<?php echo e(old('confirm_password')); ?>" placeholder="Confirm Password" type="password">
          <?php if($errors->has('confirm_password')): ?>
          <div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('confirm_password')); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <div class="form-group">
          <div class="g-recaptcha" data-sitekey="<?php echo e(env('NOCAPTCHA_SITEKEY')); ?>"></div>
        </div>
      </div>
    </div>
    <div class="element-box-content" style="margin-top: 30px;">
      <button class="mr-2 mb-2 btn btn-primary" type="button" data-request="ajax-submit" data-target='[role="signUpForm"]'> Sign Up</button>
      <a href="<?php echo e(url('login')); ?>" class="text-white mr-2 mb-2 btn btn-primary">Back to Sign In</a>
    </div>
  </form>
</div>
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
<script src="<?php echo e(url('public/bower_components/select2/dist/js/select2.full.min.js')); ?>"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo e(asset('js/script.js')); ?>"></script>
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.loginapp', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/auth/register.blade.php ENDPATH**/ ?>