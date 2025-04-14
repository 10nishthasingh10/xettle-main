<?php $__env->startSection('title', __('Reset Password')); ?>
<?php $__env->startSection('content'); ?>
		<!--begin::Main-->
		<div class="auth-box-w">
        <div class="logo-w">
          <a href="#"><img alt="" src="<?php echo e(asset('images/logo.png')); ?>"></a>
        </div>
        <h4 class="auth-header">
     <?php echo e(__('Update Password')); ?>

        </h4>
       

		<form id="kt_sign_in_forms"
        action="<?php echo e(route('password.request')); ?>" method="post">
        <?php if(session('status')): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo e(session('status')); ?>

                        </div>
                    <?php endif; ?>
		<?php echo csrf_field(); ?>
        <input type="hidden" name="token" value="<?php echo e($token); ?>">

        <div class="form-group <?php if($errors->has('email')): ?> has-danger <?php endif; ?>">
            <label for=""> <?php echo e(__('E-Mail Address')); ?></label></label>
			<input class="form-control" placeholder="Enter your E-Mail Address" name="email" type="email">
			<?php if($errors->has('email')): ?>
					<div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('email')); ?></div>
			<?php endif; ?>
            <div class="pre-icon os-icon os-icon-email-2-at2"></div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group <?php if($errors->has('password')): ?> has-danger <?php endif; ?>">
                <label for=""> <?php echo e(__('Password')); ?></label>
				<input class="form-control" placeholder="Password" name="password" type="password">
				<?php if($errors->has('password')): ?>
					<div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('password')); ?></div>
			    <?php endif; ?>
                <div class="pre-icon os-icon os-icon-fingerprint"></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group <?php if($errors->has('password_confirmation')): ?> has-danger <?php endif; ?>">
                <label for=""> <?php echo e(__('Confirm Password')); ?></label>
				<input class="form-control" name="password_confirmation" placeholder="Confirm Password" required type="password">
				<?php if($errors->has('password_confirmation')): ?>
					<div class="help-block form-text with-errors form-control-feedback" role="alert"><?php echo e($errors->first('password_confirmation')); ?></div>
			    <?php endif; ?>
          <div class="pre-icon os-icon os-icon-fingerprint"></div>
              </div>
            </div>
          </div>
                     
        
          <div class="buttons-w">
            <button class="mr-2 mb-2 btn btn-primary" type="submit">   <?php echo e(__('Reset Password')); ?></button>
            <a href="<?php echo e(url('login')); ?>" class="text-white mr-2 mb-2 btn btn-primary" >Back to Sign In</a>
          </div>
        
        </form>
  
		<!--end::Main-->
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.loginapp', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/auth/passwords/reset.blade.php ENDPATH**/ ?>