<?php $__env->startSection('title','My Profile'); ?>

<?php $__env->startSection('style'); ?>
<style type="text/css">
    .lettersProfile:before {
        height: 80px;
        width: 80px;
        line-height: 80px;
        font-size: 2.7rem;
        line-height: 1.8em;

    }

    .lettersProfile::before {
        margin-right: 0 !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="content-i">
    <div class="content-box">
        <div class="row">
            <div class="col-md-4">
                <div class="ecommerce-customer-info">
                    <div class="ecommerce-customer-main-info">
                        <?php if(isset(Auth::user()->avatar)): ?>
                        <img alt="" src="<?php echo e(url('public/uploads/profile/')); ?>/<?php echo e(Auth::user()->avatar); ?>">
                        <?php else: ?>
                        <p data-letters="<?php echo e(CommonHelper::shortName(Auth::user()->id)); ?>" class="lettersProfile"></p>
                        <?php endif; ?>

                        <div class="ecc-name">
                            <?php echo e(Auth::user()->name); ?>

                        </div>
                        <div class="logged-user-role">
                            <?php if(Auth::user()->is_admin): ?>
                            Admin
                            <?php else: ?>
                            User
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ecommerce-customer-sub-info">
                        <div class="row ecc-sub-info-row">
                            <div class="col-6 sub-info-label">
                                Transactional Amount

                            </div>
                            <div class="col-6 sub-info-value">
                                <?php echo e(number_format(Auth::user()->transaction_amount,2)); ?>


                            </div>
                        </div>
                        <div class="row ecc-sub-info-row">
                            <div class="col-6 sub-info-label">
                                Locked Amount
                            </div>
                            <div class="col-6 sub-info-value">
                                <?php echo e(number_format(Auth::user()->locked_amount,2)); ?>

                            </div>
                        </div>
                    </div>
                    <div class="os-tabs-controls">
                        <ul class="nav nav-tabs" id="myProfileTab">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#profile">Profile</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile">
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Full Name
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e(Auth::user()->name); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    E-Mail Address
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e(Auth::user()->email); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Contact
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e(Auth::user()->mobile); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Number
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e(Auth::user()->account_number); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="element-box">

                    <div class="os-tabs-w">
                        <div class="os-tabs-controls">
                            <ul class="nav nav-tabs bigger">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tab_overview">Personal info </a>
                                </li>
                            </ul>

                        </div>
                        <div class="tab-content">
                            <!-- personal form -->
                            <div class="tab-pane active" id="tab_overview">
                                <form role="update-profile" action="<?php echo e(secure_url('admin/accounts/profile-update')); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                    <div class="row">
                                        <input type="hidden" name="user_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for=""> Name</label><input class="form-control" placeholder="Enter Name" name="name" value="<?php echo e(Auth::user()->name); ?>" type="text">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">Mobile *</label><input class="form-control" placeholder="Phone number" value="<?php echo e(Auth::user()->mobile); ?>" name="mobile" type="number">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-buttons-w text-right">
                                        <button type="submit" name="update-profile" class="btn btn-primary" data-request="ajax-submit" data-target='[role="update-profile"]'>Update Profile</button>
                                    </div>
                                </form>

                                <form role="change-password" action="<?php echo e(secure_url('admin/accounts/profile-change-password')); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for=""> Old Password </label><input class="form-control" placeholder="old Password" name="old_password" type="password">
                                            </div>

                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">New Password</label><input class="form-control" placeholder="New Password" name="password" type="password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">Confirm Password</label><input class="form-control" placeholder="Password" name="confirm_password" type="password">
                                            </div>

                                        </div>
                                    </div>



                                    <div class="form-buttons-w text-right">
                                        <button type="submit" name="change-password" class="btn btn-primary" data-request="ajax-submit" data-callbackfn="changePasswordCallback" data-target='[role="change-password"]'>Change Password</button>
                                    </div>
                                </form>
                            </div>
                            <!-- personal form  end-->


                        </div>

                    </div>
                    <!--begin::Modals-->

                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('scripts'); ?>
<script type="text/javascript">
    function changePasswordCallback(response) {
        if (response.status_code === '200') {
            $('form[role="change-password"]').trigger('reset');
        }
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/admin//profile.blade.php ENDPATH**/ ?>