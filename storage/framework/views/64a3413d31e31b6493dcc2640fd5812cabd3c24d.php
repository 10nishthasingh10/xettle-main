<?php $__env->startSection('title', __('Unauthorized')); ?>
<?php $__env->startSection('content'); ?>
<!--begin::Main-->
<div class="big-error-w">
    <h1>
        401
    </h1>
    <h5>
        <?php echo e(__('Unauthorized User')); ?>

    </h5>
    <h4>
        Oops, Something went missing...
    </h4>
    <form>
        <div class="input-group">
            <div class="input-group-btn">
                <a href="<?php echo e(isset($url)?$url:url('')); ?>"><button class="mr-2 mb-2 btn btn-primary mt-2" type="button" style="background: #24b314;border: none;">Go To Home</button></a>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.loginapp', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/errors/401.blade.php ENDPATH**/ ?>