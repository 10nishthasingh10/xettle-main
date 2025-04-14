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

    .border-danger {
        border-color: #dc3545 !important;
    }

    .element-box {
        padding: 1.5rem 0.8rem;
    }

    .lettersProfile::before {
        margin-right: 0 !important;
    }
</style>
<meta name="user-token" content="<?php echo e(encrypt(Auth::user()->id)); ?>">
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
                                <?php if(Auth::user()->transaction_amount > 0): ?>
                                ₹<?php echo e(number_format(Auth::user()->transaction_amount,2)); ?>

                                <?php else: ?>
                                ₹<?php echo e(number_format(0,2)); ?>

                                <?php endif; ?>

                            </div>
                        </div>
                        <div class="row ecc-sub-info-row">
                            <div class="col-6 sub-info-label">
                                Locked Amount
                            </div>
                            <div class="col-6 sub-info-value">
                                <?php if(Auth::user()->locked_amount > 0): ?>
                                ₹<?php echo e(number_format(Auth::user()->locked_amount,2)); ?>

                                <?php else: ?>
                                ₹<?php echo e(number_format(0,2)); ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="os-tabs-controls">
                        <ul class="nav nav-tabs" id="myProfileTab">
                            <?php if(!empty($obVan)): ?>
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#vanDetails"> VAN</a>
                            </li>
                            <?php else: ?>
                            <?php
                            $isActiveTab = 'active';
                            ?>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($isActiveTab); ?>" data-toggle="tab" href="#profile">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#business_profile_show">Business Profile</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <!-- VAN Details -->
                        <?php if(!empty($obVan)): ?>
                        <div class="tab-pane active" id="vanDetails">

                            <?php
                            /*/
                            ?>
                            @if(!empty($data['van_info']))

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Biz. Name
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['business_info']->business_name}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    VAN
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['van_info']['van']}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    IFSC
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['van_info']['ifsc']}}
                                </div>
                            </div>
                            @if(!empty($data['van_info']['van_2']))
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    VAN (2nd)
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['van_info']['van_2']}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    IFSC (2nd)
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['van_info']['ifsc_2']}}
                                </div>
                            </div>
                            @endif
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Status
                                </div>
                                <div class="col-7 sub-info-value">
                                    @if($data['van_info']['status'] === '1')
                                    <div class="status-pill green" data-title="Active" data-toggle="tooltip" data-original-title="" title="">
                                    </div>
                                    @else
                                    <div class="status-pill red" data-title="InActive" data-toggle="tooltip" data-original-title="" title="">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Min Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 100
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Max Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 1000000
                                </div>
                            </div>
                            @endif

                            @if(!empty($ebVan))
                            <hr class="hr">

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Biz. Name
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$ebVan->account_holder_name}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    VAN
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$ebVan->account_number}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    IFSC
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$ebVan->ifsc}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Status
                                </div>
                                <div class="col-7 sub-info-value">
                                    @if($ebVan->status === '1')
                                    <div class="status-pill green" data-title="Active" data-toggle="tooltip" data-original-title="" title="">
                                    </div>
                                    @else
                                    <div class="status-pill red" data-title="InActive" data-toggle="tooltip" data-original-title="" title="">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Min Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 100
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Max Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 1000000
                                </div>
                            </div>
                            @endif

                            <?php
                            //*/
                            ?>

                            <?php if(!empty($obVan)): ?>
                            <hr class="hr">

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Biz. Name
                                </div>
                                <div class="col-7 sub-info-value">
                                    <?php echo e($obVan->account_holder_name); ?>

                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    VAN
                                </div>
                                <div class="col-7 sub-info-value">
                                    <?php echo e($obVan->account_number); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    IFSC
                                </div>
                                <div class="col-7 sub-info-value">
                                    <?php echo e($obVan->ifsc); ?>

                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Status
                                </div>
                                <div class="col-7 sub-info-value">
                                    <?php if($obVan->status === '1'): ?>
                                    <div class="status-pill green" data-title="Active" data-toggle="tooltip" data-original-title="" title=""></div>
                                    <?php else: ?>
                                    <div class="status-pill red" data-title="InActive" data-toggle="tooltip" data-original-title="" title=""></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Min Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 100
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Max Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 1000000
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="tab-pane <?php echo e($isActiveTab); ?>" id="profile">
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

                        <div class="tab-pane " id="business_profile_show">
                            <?php if(isset($business_info) && !empty($business_info)): ?>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Contact Name
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->name); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    E-Mail Address
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->email); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Contact
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->mobile); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Business Type
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->business_type); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Business Category
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php if(isset($business_info->business_category_id) && !empty($business_info->business_category_id)): ?>
                                    <?php
                                    $business_category = DB::table('business_categories')
                                    ->where('id',$business_info->business_category_id)->first();

                                    ?>
                                    <?php if(isset($business_category) && !empty($business_category)): ?>
                                    <?php echo e($business_category->name); ?>

                                    <?php endif; ?>
                                    <?php else: ?>
                                    NA
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Business Sub Category
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php if(isset($business_info->business_subcategory_id) && !empty($business_info->business_subcategory_id)): ?>
                                    <?php
                                    $business_category = DB::table('business_categories')
                                    ->where('id',$business_info->business_subcategory_id)->first();

                                    ?>
                                    <?php if(isset($business_category) && !empty($business_category)): ?>
                                    <?php echo e($business_category->name); ?>

                                    <?php endif; ?>
                                    <?php else: ?>
                                    NA
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Pan Number
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->pan_number); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Pan Owner Name
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->pan_owner_name); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Billing Label
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->billing_label); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Address
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->address); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Pin Code
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->pincode); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    City
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->city); ?>

                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    State
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php $__currentLoopData = $data['state_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state_list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($business_info->state == $state_list->id): ?>
                                    <?php echo e($state_list->state_name); ?>

                                    <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Gstin
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($business_info->gstin); ?>

                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Manager
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php if(isset($account_manager_data->name)): ?>

                                    <?php echo e($account_manager_data->name); ?> (<?php echo e($account_manager_data->mobile); ?>)
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if(isset($account_coordinator_data->name)): ?>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Coordinator
                                </div>
                                <div class="col-6 sub-info-value">
                                    <?php echo e($account_coordinator_data->name); ?> (<?php echo e($account_coordinator_data->mobile); ?>)
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php else: ?>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-value">
                                    <span style="color:red">Please update business profile.</span>
                                </div>
                            </div>
                            <?php endif; ?>
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
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tab_sales">Business details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tab_bank">Bank details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tab_api">Integrations</a>
                                </li>
                            </ul>

                        </div>
                        <div class="tab-content">
                            <!-- personal form -->
                            <div class="tab-pane active" id="tab_overview">
                                <form role="update-profile" action="<?php echo e(custom_secure_url('user/accounts/profile-update')); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                    <div class="row">
                                        <input type="hidden" name="user_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for=""> Name</label><input class="form-control" placeholder="Enter Name" name="name" value="<?php echo e(Auth::user()->name); ?>" <?php if(Auth::user()->is_profile_updated == '1'): ?>
                                                readonly="readonly"
                                                <?php endif; ?>
                                                type="text">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">Mobile *</label><input class="form-control" <?php if(Auth::user()->is_profile_updated == '1'): ?>
                                                readonly="readonly"
                                                <?php endif; ?>
                                                placeholder="Phone number" value="<?php echo e(Auth::user()->mobile); ?>" name="mobile" type="number">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-buttons-w text-right">
                                        <?php if(Auth::user()->is_profile_updated == '0'): ?> <button type="submit" name="update-profile" class="btn btn-primary" data-request="ajax-submit" data-target='[role="update-profile"]'>Update Profile</button> <?php endif; ?>
                                    </div>
                                </form>

                                <form role="change-password" action="<?php echo e(custom_secure_url('user/accounts/profile-change-password')); ?>" method="post">
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

                            <!-- from business  -->
                            <div class="tab-pane" id="tab_sales">
                                <form role="update-business-profile" action="<?php echo e(url('user/accounts/business-profile-update')); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
                                    <fieldset class="form-group">
                                        <legend><span>CONTACT INFO</span></legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Contact Name <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Contact Name" name="name" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>
                                                    <?php if(isset($data['business_info'])): ?>

                                                    value="<?php echo e($data['business_info']->name); ?>"
                                                    <?php else: ?>
                                                    value="<?php echo e(Auth::user()->name); ?>" <?php endif; ?> type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Contact Number <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Contact Number" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>

                                                    <?php if(isset($data['business_info'])): ?>
                                                    value="<?php echo e($data['business_info']->mobile); ?>"
                                                    <?php else: ?>
                                                    value="<?php echo e(Auth::user()->mobile); ?>"
                                                    <?php endif; ?>
                                                    name="contact_number" type="text">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Contact Email <span class="requiredstar">*</span></label>
                                                    <input type="email" name="contact_email" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>

                                                    <?php if(isset($data['business_info'])): ?>
                                                    value="<?php echo e($data['business_info']->email); ?>"
                                                    <?php else: ?>
                                                    value="<?php echo e(Auth::user()->email); ?>"
                                                    <?php endif; ?>
                                                    class="form-control" placeholder="Contact Number" required="required">
                                                </div>
                                            </div>

                                        </div>

                                    </fieldset>

                                    <fieldset class="form-group">
                                        <legend><span>BUSINESS OVERVIEW</span></legend>
                                        <div class="row">

                                            <div class="col-sm-6">
                                                <input type="hidden" name="update_by_user_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
                                                <div class="form-group">
                                                    <label for="">Business/Company Name <span class="requiredstar">*</span></label>
                                                    <input type="text" name="business_name" <?php if(isset($data['business_info']->business_name)): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>

                                                    <?php if(isset($data['business_info']->business_name)): ?>
                                                    value="<?php echo e($data['business_info']->business_name); ?>"
                                                    <?php endif; ?>
                                                    class="form-control" placeholder="Business/Company Name" required="required">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Business/Company Pan <span class="requiredstar"></span></label>
                                                    <input type="text" name="business_pan" <?php if(isset($data['business_info']->business_pan)): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>

                                                    <?php if(isset($data['business_info']->business_pan)): ?>
                                                    value="<?php echo e($data['business_info']->business_pan); ?>"
                                                    <?php endif; ?>
                                                    class="form-control" placeholder="Business/Company Pan" required="required">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Business Type <span class="requiredstar">*</span></label>
                                                    <select name="business_type" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Business Type..." class="form-select form-select-solid">
                                                        <option value="">Select a Business Type...</option>
                                                        <option value="Proprietorship" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type=="Proprietorship"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>>Proprietorship</option>
                                                        <option value="Partnership" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type=="Partnership"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            >Partnership</option>
                                                        <option value="Public Limited" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type=="Public Limited"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            >Public Limited</option>
                                                        <option value="Private Limited" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type == "Private Limited"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            >Private Limited</option>

                                                        <option value="LLP" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type=="LLP"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            >LLP</option>
                                                        <option value="Trust" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type=="Trust"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            >Trust</option>
                                                        <option value="Society" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type=="Society"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            >Society</option>
                                                        <option value="NGO" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type=="NGO"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            >NGO</option>
                                                        <option value="Not Registered" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_type=="Not Registered"): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            >Not Registered</option>
                                                    </select>



                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Business Category <span class="requiredstar">*</span></label>
                                                    <select name="business_category" class="form-control" id="business_category_id" onchange="businessCategory(this);" data-control="select2" data-hide-search="true" data-placeholder="Select a Category..." class="form-select form-select-solid" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                        disabled="disabled"
                                                        <?php endif; ?>>
                                                        <option value="">Select a Business Category...</option>
                                                        <?php $__currentLoopData = $data['business_category']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $business_category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->business_category_id==$business_category->id): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            value="<?php echo e($business_category->id); ?>"><?php echo e($business_category->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6" style="display:none" id="b_sub_cate_id">
                                                <div class="form-group">
                                                    <label for="">Business Sub Category</label>
                                                    <select class="form-control" name="business_subcategory" id="businesssubcategory" data-control="select2" data-hide-search="true" data-placeholder="Select a Sub Category..." class="form-select form-select-solid" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                        disabled="disabled"
                                                        <?php endif; ?>>
                                                        <option value="">Select a Business Sub Category...</option>
                                                    </select>
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Website URL</label>
                                                    <?php if(!empty($data['business_info']) && $data['business_info']->is_kyc_updated): ?>
                                                    <input type="text" class="form-control" value="<?php echo e($data['business_info']->web_url); ?>" readonly>
                                                    <?php else: ?>
                                                    <input type="text" name="web_url" ) <?php if(isset($data['business_info'])): ?> value="<?php echo e($data['business_info']->web_url); ?>" <?php endif; ?> class="form-control" placeholder="Website URL">
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">App URL</label>
                                                    <?php if(!empty($data['business_info']) && $data['business_info']->is_kyc_updated): ?>
                                                    <input type="text" class="form-control" value="<?php echo e($data['business_info']->app_url); ?>" readonly>
                                                    <?php else: ?>
                                                    <input type="text" name="app_url" ) <?php if(isset($data['business_info'])): ?> value="<?php echo e($data['business_info']->app_url); ?>" <?php endif; ?> class="form-control" placeholder="App URL">
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>


                                    </fieldset>

                                    <fieldset class="form-group">
                                        <legend><span>BUSINESS DETAILS</span></legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Authorised Signatory PAN <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Authorised Signatory PAN" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>
                                                    <?php if(isset($data['business_info'])): ?>
                                                    value="<?php echo e($data['business_info']->pan_number); ?>"
                                                    <?php else: ?>
                                                    value="<?php echo e(Auth::user()->pan_number); ?>"
                                                    <?php endif; ?> name="pan_number" type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for=""> PAN Owner’s Name <span class="requiredstar">*</span></label>
                                                        <input class="form-control" placeholder="PAN Owner’s Name" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                        readonly="readonly"
                                                        <?php endif; ?>

                                                        <?php if(isset($data['business_info'])): ?>
                                                        value="<?php echo e($data['business_info']->pan_owner_name); ?>"
                                                        <?php else: ?>
                                                        value="<?php echo e(Auth::user()->pan_owner_name); ?>"
                                                        <?php endif; ?>
                                                        name="pan_owner_name"
                                                        type="text">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Billing Label <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Billing Label " name="billing_label" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>

                                                    <?php if(isset($data['business_info'])): ?>
                                                    value="<?php echo e($data['business_info']->billing_label); ?>"
                                                    <?php else: ?>
                                                    value="<?php echo e(Auth::user()->billing_label); ?>"
                                                    <?php endif; ?> type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="">Address <span class="requiredstar">*</span></label>
                                                        <textarea class="form-control" name="address" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?> readonly="readonly" <?php endif; ?>><?php if(isset($data['business_info'])): ?><?php echo e($data['business_info']->address); ?><?php endif; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Pincode <span class="requiredstar">*</span></label>
                                                    <input class="form-control" name="pincode" placeholder="Pin code" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>
                                                    <?php if(isset($data['business_info'])): ?>
                                                    value="<?php echo e($data['business_info']->pincode); ?>"
                                                    <?php else: ?>
                                                    value="<?php echo e(Auth::user()->pincode); ?>"
                                                    <?php endif; ?> type="number">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="">City <span class="requiredstar">*</span></label>
                                                        <input class="form-control" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                        readonly="readonly"
                                                        <?php endif; ?>
                                                        <?php if(isset($data['business_info'])): ?>
                                                        value="<?php echo e($data['business_info']->city); ?>"
                                                        <?php else: ?>
                                                        value="<?php echo e(Auth::user()->city); ?>"
                                                        <?php endif; ?> placeholder="City" name="city" type="text">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> State <span class="requiredstar">*</span></label>
                                                    <select name="state" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a State..." class="form-select form-select-solid" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?> disabled <?php endif; ?>>
                                                        <option value="">Select a State...</option>
                                                        <?php $__currentLoopData = $data['state_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state_list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($state_list->id); ?>" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->state==$state_list->id): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            ><?php echo e($state_list->state_name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="">GSTIN <span class="requiredstar">*</span></label>
                                                        <input class="form-control" name="gstin" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                        readonly="readonly"
                                                        <?php endif; ?>
                                                        <?php if(isset($data['business_info'])): ?>
                                                        value="<?php echo e($data['business_info']->gstin); ?>"
                                                        <?php else: ?>
                                                        value="<?php echo e(Auth::user()->gstin); ?>"
                                                        <?php endif; ?> placeholder="GSTIN" type="text">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="">Category Code (MCC) <span class="requiredstar">*</span></label>
                                                        <input class="form-control" name="business_mcc" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?>
                                                        readonly="readonly"
                                                        <?php endif; ?>
                                                        <?php if(isset($data['business_info'])): ?>
                                                        value="<?php echo e($data['business_info']->mcc); ?>"
                                                        <?php else: ?>
                                                        value="<?php echo e(Auth::user()->mcc); ?>"
                                                        <?php endif; ?> placeholder="Category Code (MCC)" type="text">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <fieldset class="form-group">
                                        <legend><span>Account Manager</span></legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Account Manager <span class="requiredstar">*</span></label>
                                                    <select name="acc_manager_id" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Account Manager..." class="form-select form-select-solid" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?> disabled <?php endif; ?>>
                                                        <option value="">Select a Account Manager...</option>
                                                        <?php $__currentLoopData = $data['account_manager']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account_manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($account_manager->type == 'account_manager'): ?>
                                                        <option value="<?php echo e($account_manager->id); ?>" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->acc_manager_id == $account_manager->id): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            ><?php echo e($account_manager->name); ?> (<?php echo e($account_manager->mobile); ?>)</option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Account Coordinator <span class="requiredstar">*</span></label>
                                                    <select name="acc_coordinator_id" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Account Manager..." class="form-select form-select-solid" <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?> disabled <?php endif; ?>>
                                                        <option value="">Select a Account Coordinator...</option>
                                                        <?php $__currentLoopData = $data['account_manager']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account_manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($account_manager->type == 'account_coordinator'): ?>
                                                        <option value="<?php echo e($account_manager->id); ?>" <?php if(isset($data['business_info'])): ?> <?php if($data['business_info']->acc_coordinator_id == $account_manager->id): ?>
                                                            selected
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                            ><?php echo e($account_manager->name); ?> (<?php echo e($account_manager->mobile); ?>)</option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>

                                    <!-- <fieldset class="form-group">
                                        <legend><span>DOCUMENTS VERIFICATION</span></legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Aadhar Number <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Aadhar Number" name="aadhar_number" <?php if(isset($data['business_info'])): ?>
                                                            value="<?php echo e($data['business_info']->aadhar_number); ?>"
                                                            <?php endif; ?> type="number">
                                                </div>
                                            </div>
                                           <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for=""> Business Registration Proof</label>
                                                        <input type="file" class="form-control" name="business_proof">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Personal PAN</label>
                                                    <input type="file" class="form-control" placeholder="Personal PAN" name="pan_id" <?php if(isset($data['business_info'])): ?>
                                                            value="<?php echo e($data['business_info']->pan_id); ?>"
                                                            <?php endif; ?> >
                                                </div>
                                            </div>

                                        </div>

                                    </fieldset> -->

                                    <div class="form-buttons-w text-right">

                                        <?php if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1'): ?> <?php else: ?> <button type="submit" name="update-business-profile" class="btn btn-primary" data-request="ajax-submit" data-target='[role="update-business-profile"]'>Update Business Profile</button> <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                            <!-- from business end -->

                            <!-- bank form -->
                            <div class="tab-pane " id="tab_bank">

                                <?php if($userBankInfos->isNotEmpty()): ?>

                                <div class="row">

                                    <?php $__currentLoopData = $userBankInfos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <div class="col-md-6 xttl-card mb-3">
                                        <div class="card" style="max-width:400px">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <?php if($row->is_primary == '1'): ?>
                                                    <span class="text-success">Primary Account</span>
                                                    <?php else: ?>
                                                    Bank Info
                                                    <?php endif; ?>
                                                </h5>
                                                <p class="card-text">
                                                <table class="table table-striped xttl-table">
                                                    <tbody>
                                                        <tr>
                                                            <th colspan="2">Beneficiary Name</th>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2"><?php echo e($row->beneficiary_name); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="2">Account Number</th>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2"><?php echo e($row->account_number); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>IFSC</th>
                                                            <td><?php echo e($row->ifsc); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Status</th>
                                                            <td>
                                                                <?php if($row->is_active == '1'): ?>
                                                                <span class="badge badge-success">Active</span>
                                                                <?php else: ?>
                                                                <span class="badge badge-secondary">In-Active</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        /*//
                                                        ?>
                                                        <tr>
                                                            <th>Verified</th>
                                                            <td>
                                                                @if($row->is_verified == '1')
                                                                <span class="badge badge-success">Verified</span>
                                                                @else
                                                                <span class="badge badge-secondary">Not-Verified</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        //*/
                                                        ?>
                                                    </tbody>
                                                </table>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </div>

                                <?php else: ?>
                                <form role="update-bankDetails" action="<?php echo e(url('user/accounts/update-bank-details')); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for=""> Beneficiary Name <span class="requiredstar">*</span></label>
                                                <input class="form-control" placeholder="Beneficiary Name" <?php if(isset($data['business_info']) && $data['business_info']->is_bank_updated == '1'): ?>
                                                readonly="readonly"
                                                <?php endif; ?>

                                                type="text" name="beneficiary_name" <?php if(isset($data['business_info'])): ?>
                                                value="<?php echo e($data['business_info']->beneficiary_name); ?>"
                                                <?php endif; ?>>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="form-group">
                                                    <label for=""> Branch IFSC Code <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Branch IFSC Code" <?php if(isset($data['business_info']) && $data['business_info']->is_bank_updated == '1'): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>
                                                    type="text" name="ifsc" <?php if(isset($data['business_info'])): ?>
                                                    value="<?php echo e($data['business_info']->ifsc); ?>"
                                                    <?php endif; ?>>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">Account Number <span class="requiredstar">*</span></label>
                                                <input class="form-control" placeholder="Account Number" <?php if(isset($data['business_info']) && $data['business_info']->is_bank_updated == '1'): ?>
                                                readonly="readonly"
                                                <?php endif; ?>
                                                type="number" name="account_number"
                                                <?php if(isset($data['business_info'])): ?>
                                                value="<?php echo e($data['business_info']->account_number); ?>"
                                                <?php endif; ?>>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="form-group">
                                                    <label for="">Re-Enter Account Number <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Re-Enter Account Number" <?php if(isset($data['business_info']) && $data['business_info']->is_bank_updated == '1'): ?>
                                                    readonly="readonly"
                                                    <?php endif; ?>
                                                    type="text" name="re_enter_account_number"
                                                    <?php if(isset($data['business_info'])): ?>
                                                    value="<?php echo e($data['business_info']->account_number); ?>"
                                                    <?php endif; ?>>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-buttons-w text-right">
                                        <?php if(isset($data['business_info']) && $data['business_info']->is_bank_updated == '1'): ?> <?php else: ?> <button type="submit" name="update-business-profile" class="btn btn-primary" data-request="ajax-submit" data-target='[role="update-bankDetails"]'>Update Bank details</button> <?php endif; ?>
                                    </div>
                                </form>
                                <?php endif; ?>


                            </div>
                            <!-- bank form  end-->



                            <!-- IP White List start -->
                            <div class="tab-pane " id="tab_api">
                                <!-- API Overview -->
                                <div class="element-box">
                                    <h5 class="form-header d-flex justify-content-between">
                                        API Overview
                                        <?php if(CommonHelper::isServiceActive(Auth::user()->id,PAYOUT_SERVICE_ID) || CommonHelper::isServiceActive(Auth::user()->id,AEPS_SERVICE_ID) || CommonHelper::isServiceActive(Auth::user()->id,UPI_SERVICE_ID)): ?>
                                        <button class="btn btn-sm btn-primary" id="regenerateApiKeyBtn"><?php echo e(GENERATE_NEW_API_KEY); ?></button>
                                        <?php endif; ?>
                                    </h5>

                                    <div class="element-actions">
                                    </div>


                                    <div class="table-responsive">

                                        <table class="table table-lightborder" style="width:100% !important" id="kt_api_keys_table">
                                            <!--begin::Thead-->
                                            <thead>
                                                <tr>
                                                    <th>API Keys</th>
                                                    <th>Service</th>
                                                    <th>Created At</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>


                                <!-- over end -->
                                <!-- IP White List start  -->

                                <div class="element-box">
                                    <h5 class="form-header d-flex justify-content-between">
                                        IP White List

                                        <a href="#" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#kt_modal_create_ipwhite" id="kt_toolbar_primary_button">
                                            Add IP
                                        </a>
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-lightborder" style="width: 100% !important;" id="ip_list_table">
                                            <!--begin::Thead-->
                                            <thead class=" ">
                                                <tr>
                                                    <th class="">IP</th>
                                                    <th class="">Service</th>
                                                    <th class="">Created At</th>
                                                    <th class="">Action</th>
                                                </tr>
                                            </thead>
                                            <!--end::Thead-->
                                            <!--begin::Tbody-->
                                            <tbody class="">
                                            </tbody>
                                            <!--end::Tbody-->
                                        </table>

                                    </div>
                                </div>
                                <!-- IP White List end -->

                                <!-- Webhook Url start -->

                                <div class="element-box">
                                    <h5 class="form-header d-flex justify-content-between">
                                        Webhook Url
                                        <button class="btn btn-primary" data-target="#exampleModal1" data-toggle="modal" type="button"> <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?>
                                            Update
                                            <?php else: ?>
                                            Add
                                            <?php endif; ?> webhook</button>
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-lightborder" style="width: 100% !important;" id="webhook_list_table">
                                            <!--begin::Thead-->
                                            <thead class=" ">
                                                <tr>
                                                    <th class="">URL</th>
                                                    <th class="">Header Key</th>
                                                    <th class="">Header Value</th>
                                                    <th class="">Created At</th>
                                                </tr>
                                            </thead>
                                            <!--end::Thead-->
                                            <!--begin::Tbody-->
                                            <tbody class="">
                                            </tbody>
                                            <!--end::Tbody-->
                                        </table>

                                    </div>
                                </div>
                                <?php if((isset($user_config->is_sdk_enable) && $user_config->is_sdk_enable == '1') || (isset($user_config->is_matm_enable) && $user_config->is_matm_enable == '1')): ?>
                                <div class="element-box">
                                    <h5 class="form-header d-flex justify-content-between">
                                        SETUP SDK
                                        <?php if((isset($user_config->is_sdk_enable) && $user_config->is_sdk_enable == '1')): ?>
                                        <button class="btn btn-sm btn-primary" id="regenerateSDKApiKeyBtn">
                                            AEPS Generate Key
                                        </button>
                                        <?php endif; ?>
                                        <?php if((isset($user_config->is_sdk_enable) && $user_config->is_sdk_enable == '1') && CommonHelper::isServiceActive(Auth::user()->id, MATM_SERVICE_ID)): ?>
                                        <button class="btn btn-sm btn-primary" id="matmRegenerateSDKApiKeyBtn">
                                            MATM Generate Key
                                        </button>
                                        <?php endif; ?>
                                    </h5>
                                    <div class="element-actions">



                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-lightborder" style="width: 100% !important;" id="sdk_list_table">
                                            <!--begin::Thead-->
                                            <thead class=" ">
                                                <tr>
                                                    <th class="">Type</th>
                                                    <th class="">Key</th>
                                                    <th class="">Created At</th>
                                                </tr>
                                            </thead>
                                            <!--end::Thead-->
                                            <!--begin::Tbody-->
                                            <tbody class="">
                                            <?php if((isset($user_config->is_sdk_enable) && $user_config->is_sdk_enable == '1') && !empty($user_config->app_id)): ?>
                                                <tr>
                                                    <td class=""><span>AEPS SDK</span></td>
                                                    <td class=""><span id="sdkKey"><?php echo e($user_config->app_id); ?></span></td>
                                                    <td class=""> <span id="sdkKeyUpdatedAt"><?php echo e($user_config->app_cred_created_at); ?></span></td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php if(isset($user_config->is_matm_enable) && $user_config->is_matm_enable == '1' && !empty($user_config->matm_app_id)): ?>
                                                <tr>
                                                    <td class=""><span>MATM SDK</span></td>
                                                    <td class=""><span id="matmKey"><?php echo e($user_config->matm_app_id); ?></span></td>
                                                    <td class=""> <span id="matmKeyUpdatedAt"><?php echo e($user_config->matm_app_cred_created_at); ?></span></td>
                                                </tr>
                                            <?php endif; ?>
                                            </tbody>
                                            <!--end::Tbody-->
                                        </table>

                                    </div>
                                </div>
                                <?php endif; ?>

                            </div>

                        </div>

                    </div>
                    <!--begin::Modals-->
                    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="exampleModal1" role="dialog" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">
                                        <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?>
                                        Update
                                        <?php else: ?>
                                        Create
                                        <?php endif; ?>
                                        Web hook
                                    </h5>
                                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> &times;</span></button>
                                </div>

                                <form role="update-webhook" action="<?php echo e(url('user/accounts/webhook-update')); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo e(encrypt(auth::user()->id)); ?>" />

                                    <div class="modal-body">

                                        <div class="d-flex flex-column mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required fs-5 fw-bold mb-2">URL <span class="requiredstar">*</span></label>

                                            <input class="form-control" placeholder="" name="webhook_url" <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?> value="<?php echo e($data['webhook']->webhook_url); ?>" <?php endif; ?> type="text">
                                            <!--end::Select-->
                                        </div>
                                        <div class="d-flex flex-column mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required fs-5 fw-bold mb-2">Secret <span class="requiredstar">*</span></label>

                                            <input class="form-control" placeholder="" name="secret" <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?> value="<?php echo e($data['webhook']->secret); ?>" <?php endif; ?> type="text">
                                            <!--end::Select-->
                                        </div>

                                        <div class="d-flex flex-column mt-2 mb-10 fv-row" style="margin-left: 20px">
                                            <!--begin::Label-->
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="add_header_key_value" id="agreeCheckBox" onClick="agreeFunction()" type="checkbox" <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?> <?php if($data['webhook']->header_key || $data['webhook']->header_value): ?>
                                                checked
                                                <?php endif; ?>
                                                <?php endif; ?> />
                                                Add Header</label>
                                            <!--end::Select-->
                                        </div>

                                        <div class="d-flex flex-column mb-10 fv-row headerKey" id="headerKey" <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?> <?php if($data['webhook']->header_key != '' || $data['webhook']->header_value != ''): ?>
                                            style="display:block !important"
                                            <?php else: ?>
                                            style="display:none !important"
                                            <?php endif; ?>
                                            <?php else: ?>
                                            style="display:none !important"
                                            <?php endif; ?>>
                                            <label class="col-form-label headerKey" for=""> Header Key</label>
                                            <input class="form-control headerKey" placeholder="" <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?> value="<?php echo e($data['webhook']->header_key); ?>" <?php endif; ?> type="text" name="headerKey">
                                        </div>

                                        <div class="d-flex flex-column mb-10 fv-row headerValue" id="headerValue" <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?> <?php if($data['webhook']->header_key != '' || $data['webhook']->header_value != ''): ?>
                                            style="display:block"

                                            <?php else: ?>
                                            style="display:none !important"
                                            <?php endif; ?>
                                            <?php else: ?>
                                            style="display:none !important"
                                            <?php endif; ?>>
                                            <label class="col-form-label headerValue" for=""> Header value <span class="requiredstar">*</span></label>

                                            <input class="form-control headerValue" placeholder="" type="text" <?php if(isset($data['webhook']) && !empty($data['webhook'])): ?> value="<?php echo e($data['webhook']->header_value); ?>" <?php endif; ?> name="headerValue">

                                        </div>


                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Cancel</button>
                                        <button class="btn btn-primary" id="webhookId" type="submit" data-request="ajax-submit" data-target='[role="update-webhook"]' data-callbackfn="setWebhookCallback" data-targetform='kt_modal_create_webhook_form'><?php if(!empty($data['webhook'])): ?> Update <?php else: ?> Add <?php endif; ?></button>
                                    </div>
                            </div>
                            </form>
                        </div>
                    </div>

                    <?php echo $__env->make(USER.'.payout.modals.ip', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <!--begin::Modal - Create Api Key-->

                    <?php echo $__env->make(USER.'.payout.modals.apikey', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                </div>
            </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('user-js/user-profile.js?v=1.0.2')); ?>"></script>
<script type="text/javascript">
    function businessCategory(id) {
        $('#b_sub_cate_id').css('display', 'none');
        $id = id.value;
        $('#businesssubcategory').html('');
        $.get("<?php echo e(custom_secure_url('getbusiness_sub_category')); ?>/" + $id, function(data, status) {
            if (data.status) {
                $('#b_sub_cate_id').css('display', 'block');
                $('#businesssubcategory').html(data.option);
            }
        });
    }

    $(document).ready(function() {
        $('#b_sub_cate_id').css('display', 'none');
        $id = $('#business_category_id').val();
        $('#businesssubcategory').html('');
        if (isEmpty($id)) {
            $.get("<?php echo e(custom_secure_url('getbusiness_sub_category')); ?>/" + $id, function(data, status) {
                if (data.status) {
                    $('#b_sub_cate_id').css('display', 'block');
                    $('#businesssubcategory').html(data.option);
                }
            });
        }
    });

    function isEmpty(val) {
        return (val === undefined || val == null || val.length <= 0) ? false : true;
    }

    function agreeFunction() {
        if ($('#agreeCheckBox').prop('checked')) {
            $('.headerKey').show();
            $('.headerValue').show();
        } else {
            $('.headerKey').hide();
            $('.headerValue').hide();
        }
    }

    // Api Key for
    $(document).ready(function() {
        var url = "<?php echo e(custom_secure_url('user/fetch')); ?>/ipWhiteLists/0";
        var onDraw = function() {};
        var options = [{
                data: 'ip',
            },
            {
                data: 'service_id',
                render: function(data, type, full, meta) {
                    if (full.service != undefined && full.service != null) {
                        return '<span class="badge badge-primary">' + full.service.service_name + '</span>';
                    } else {
                        return '<span class="badge badge-danger">NA</span>';
                    }
                }
            },
            {
                data: 'new_created_at'
            },
            {
                "data": "action",
                render: function(data, type, full, meta) {
                    var $id = full.id;
                    var $btn = '<a href="javascript:void(0)" onclick="deleteIp(' + $id + ');" class="edit btn btn-danger btn-sm"><i class="os-icon os-icon-trash-2"></i></a>';
                    return $btn;
                }
            }
        ];

        datatableSetup(url, options, onDraw, "#ip_list_table");

        var url = "<?php echo e(custom_secure_url('user/fetch')); ?>/webHookLists/0";
        var options = [{
                data: 'webhook_url'
            },
            {
                data: 'header_key'
            },
            {
                data: 'header_value'
            },
            {
                data: 'new_created_at'
            },
        ];

        datatableSetup(url, options, onDraw, "#webhook_list_table");
    });


    var hash = window.location.hash;
    $('ul a[href="' + hash + '"]').trigger('click');

    function tabMenu(id) {
        $('ul a[href="#' + id + '"]').trigger('click');
        //$('ul').find('href="#'+id+'"').addClass('active');
    }

    function webServiceUpdate(id) {
        $.ajax({
            url: `<?php echo e(custom_secure_url('admin/web-service-activate')); ?>/${id}`,
            type: 'GET',
            success: function(res) {
                // $("#datatable").DataTable().ajax.reload();
            }
        });
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/user/myprofile.blade.php ENDPATH**/ ?>