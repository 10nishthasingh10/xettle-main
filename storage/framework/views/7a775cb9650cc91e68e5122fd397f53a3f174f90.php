<?php
$serviceData = \App\Models\UserService::leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
    ->select('global_services.service_id', 'global_services.service_name', 'user_services.*', 'global_services.url', 'global_services.service_slug')
    ->where('user_services.user_id', Auth::user()->id)
    ->where('user_services.is_active', '1')
    ->get();
$allService = \App\Models\Service::where('is_active', '1')->get();
$segmentUri = \App\Models\Service::select('id')
    ->where('service_slug', Request::segment(3))
    ->first();

$aepsTodayBusiness =  DB::table('aeps_transactions')
->whereDate('created_at', date('Y-m-d'))
->where(['transaction_type' => 'cw', 'user_id' => Auth::user()->id, 'status' => 'success'])
->sum('transaction_amount');
$unsettledThresholdAmount = @DB::table('user_config')
                        ->select("threshold as amt")
                        ->where('user_id', Auth::user()->id)
                        ->first()->amt;
?>
<div class="top-bar color-scheme-light">
    <div class="logo-w menu-size">
        <?php if(Auth::user()->is_admin == 1): ?>
        <a class="logo" href="<?php echo e(url('/admin/dashboard')); ?>">
            <?php else: ?>
            <a class="logo" href="<?php echo e(url('/user/dashboard')); ?>">
                <?php endif; ?>
            
                <div class="logo-label">
                    <img alt=""  style="width:200px;height:45px !important" src="<?php echo e(asset('')); ?>/images/logo.png" />
                </div>
            </a>
    </div>
    <div class="fancy-selector-w wrapp">


        <?php echo $__env->make('include.user.serviceautoselect', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="fancy-selector-options">
            <?php if(Request::is('user') || Request::is('user') || Request::is('user/*')): ?>
            <a href="javascript:void(0)">
                <div class="fancy-selector-option active">
                    <?php else: ?>
                    <a href="<?php echo e(url('/')); ?>/user/dashboard">
                        <div class="fancy-selector-option ">
                            <?php endif; ?>
                            <div class="fs-img">
                                <img alt="" src="<?php echo e(asset('')); ?>/media/logos/wallet.jpg" />
                            </div>
                            <div class="fs-main-info">
                                <div class="fs-name">
                                    Primary Account
                                </div>
                                <div class="fs-sub">
                                    <span>Balance:</span><strong>

                                    <?php if((Auth::user()->transaction_amount + Auth::user()->locked_amount - $unsettledThresholdAmount) > 0): ?>
                                ₹<?php echo e(number_format((Auth::user()->transaction_amount+Auth::user()->locked_amount) - $unsettledThresholdAmount,2)); ?>

                                <?php else: ?>
                                ₹<?php echo e(number_format(0,2)); ?>

                                <?php endif; ?></strong>
                                </div>
                            </div>
                        </div>
                    </a>

                    <?php $__currentLoopData = $serviceData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $myservice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($myservice->service_slug == 'payout' || $myservice->service_slug == 'aeps' || $myservice->service_slug == 'upi_collect' || $myservice->service_slug == 'smart_collect' || $myservice->service_slug == 'va' || $myservice->service_slug == 'validate'|| $myservice->service_slug == 'recharge'|| $myservice->service_slug == 'dmt'): ?>
                    <?php
                    $url = "javascript:void(0)";
                    if ($myservice->service_slug == 'aeps') {
                        $url = "aeps";
                    } else if ($myservice->service_slug == 'upi_collect') {
                        $url = "upi";
                    } else if ($myservice->service_slug == 'smart_collect') {
                        $url = "collect";
                    } else if ($myservice->service_slug == 'validate') {
                        $url = "validation";
                    } else if ($myservice->service_slug == 'va') {
                        $url = "va";
                    }else if ($myservice->service_slug == 'recharge') {
                        $url = "recharge";
                    }else if ($myservice->service_slug == 'dmt') {
                        $url = "dmt";
                    } else {
                        $url = $myservice->service_slug;
                    }
                    ?>

                    <?php if($url == "javascript:void(0)"): ?>
                    <a href="aeps">
                        <?php else: ?>
                        <a href="<?php echo e(url($url)); ?>">
                            <?php endif; ?>
                            <?php if(Request::segment(1) == $url): ?>
                            <div class="fancy-selector-option active">
                                <?php else: ?>
                                <div class="fancy-selector-option">
                                    <?php endif; ?>
                                    <div class="fs-img">
                                        <!--<img alt="" src="<?php echo e(asset('')); ?>/media/logos/<?php echo e($myservice->url); ?>">-->
                                    </div>
                                    <div class="fs-main-info">
                                        <div class="fs-name">
                                            <?php echo e($myservice->service_name); ?>

                                        </div>
                                        <?php if(CommonHelper::isServiceActive(Auth::user()->id,$myservice->service_id)): ?>
                                        <div class="fs-sub">
                                            <?php
                                            $data = CommonHelper::callBackUPITotalAmount(Auth::user()->id);
                                            $totalAmount = $data['amount'];
                                            ?>
                                            <?php if($url == 'upi'): ?>
                                            <span>Balance:</span> <strong>₹<?php echo e($totalAmount); ?></strong>
                                            <?php elseif($url == 'va'): ?>
                                            <span>Balance:</span> <strong>&#8377;<?php echo e((CommonHelper::callBackVirtualAccountTotalAmount(Auth::user()->id))['amount']); ?></strong>
                                            <?php elseif($url == 'verification'): ?>
                                            <span></span>
                                            <?php elseif($url == 'aeps'): ?>
                                            <span>Balance:</span> <strong>₹<?php echo e($aepsTodayBusiness); ?></strong>
                                            <?php elseif($url == 'collect'): ?>
                                            <span>Balance:</span> <strong>&#8377;<?php echo e((CommonHelper::callBackSmartCollectTotalAmount(Auth::user()->id))['amount']); ?></strong>
                                            <?php elseif($url == 'payout'): ?>
                                            <?php
                                            $lockedAmount = isset(\DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum) ? \DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum : 0;
                                            ?>
                                            <span>Balance:</span> <strong>₹<?php echo e(CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->transaction_amount + $lockedAmount); ?></strong>
                                            <?php else: ?>
                                            <?php if(CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->locked_amount > 0): ?>
                                            <?php
                                            $lockedAmount = CommonHelper::getServiceAccount(Auth::user()->id, $myservice->service_id)->locked_amount;
                                            ?>
                                            <?php else: ?>
                                            <?php
                                            $lockedAmount = 0;
                                            ?>
                                            <?php endif; ?>
                                            <span>Balance:</span> <strong>₹<?php echo e(CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->transaction_amount + $lockedAmount); ?></strong>
                                            <?php endif; ?>

                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                        </a>
                        <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <div class="fancy-selector-actions text-right">
                            <a class="btn btn-primary" href="#" data-target="#onboardingFeaturesModal" data-toggle="modal"><i class="os-icon os-icon-ui-22"></i><span>Add Service</span></a>
                        </div>
                </div>
        </div>
        <!--------------------
        START - Top Menu Controls
        -------------------->
        <div class="top-menu-controls">
            <div class="element-search autosuggest-search-activator">
                <input placeholder="Search by UTR..." type="text">
            </div>
            <!--------------------
        START - Messages Link in secondary top menu
        -------------------->
            <div class="messages-notifications os-dropdown-trigger os-dropdown-position-left">
                <i class="os-icon os-icon-mail-14"></i>
                <div class="new-messages-count">
                    0
                </div>
            </div>
            <!--------------------
        END - Messages Link in secondary top menu
        -------------------->
            <!--------------------
        START - Settings Link in secondary top menu
        -------------------->
            <div class="top-icon top-settings os-dropdown-trigger os-dropdown-position-left">
                <i class="os-icon os-icon-ui-46"></i>
                <div class="os-dropdown">
                    <div class="icon-w">
                        <i class="os-icon os-icon-ui-46"></i>
                    </div>
                    <ul>
                        <li onclick="tabMenu();">
                            <a href="<?php echo e(url('user/profile')); ?>"><i class="os-icon os-icon-user"></i><span>My Profile</span></a>
                        </li>
                        <li onclick="tabMenu('tab_sales');">
                            <a href="<?php echo e(url('user/profile#tab_sales')); ?>"><i class="os-icon os-icon-home"></i><span>Business Profile</span></a>
                        </li>
                        <li onclick="tabMenu('tab_bank');">
                            <a href="<?php echo e(url('user/profile#tab_bank')); ?>"><i class="os-icon os-icon-coins-4"></i><span>Bank Details</span></a>
                        </li>
                        <li onclick="tabMenu('tab_api');">
                            <a href="<?php echo e(url('user/profile#tab_api')); ?>"><i class="os-icon os-icon-ui-09"></i><span>Integrations</span></a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('logout')); ?>"><i class="os-icon os-icon-signs-11"></i><span>Logout</span></a>
                        </li>
                        <!-- <li>
                <a href="users_profile_small.html"><i class="os-icon os-icon-ui-15"></i><span>Cancel Account</span></a>
                </li> -->
                    </ul>
                </div>
            </div>
            <!--------------------
        END - Settings Link in secondary top menu
        -------------------->
            <!--------------------
        START - User avatar and menu in secondary top menu
        -------------------->
            <div class="logged-user-w d-lg-none d-md-block">
                <div class="logged-user-i">
                    <?php if(isset(Auth::user()->avatar)): ?>
                    <div class="avatar-w">
                        <img alt="" src="<?php echo e(url('public/uploads/profile/')); ?>/<?php echo e(Auth::user()->avatar); ?>">
                    </div>
                    <?php else: ?>
                    <p data-letters="<?php echo e(CommonHelper::shortName(Auth::user()->id)); ?>"></p>
                    <?php endif; ?>
                    <div class="logged-user-menu color-style-bright">
                        <div class="logged-user-avatar-info">
                            <?php if(isset(Auth::user()->avatar)): ?>
                            <div class="avatar-w">
                                <img alt="" src="<?php echo e(url('public/uploads/profile/')); ?>/<?php echo e(Auth::user()->avatar); ?>">
                            </div>
                            <?php else: ?>
                            <p data-letters="<?php echo e(CommonHelper::shortName(Auth::user()->id)); ?>"></p>
                            <?php endif; ?>
                            <div class="logged-user-info-w">
                                <div class="logged-user-name">
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
                        </div>
                        <div class="bg-icon">
                            <i class="os-icon os-icon-wallet-loaded"></i>
                        </div>
                        <ul>
                            <li>

                                <a href="<?php echo e(url('user/profile')); ?>"><i class="os-icon os-icon-user-male-circle2"></i><span>Profile Details</span></a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('logout')); ?>"><i class="os-icon os-icon-signs-11"></i><span>Logout</span></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--------------------
        END - User avatar and menu in secondary top menu
        -------------------->
        </div>
        <!--------------------
        END - Top Menu Controls
        -------------------->
    </div>




    <!-- model -->
    <div aria-hidden="true" class="onboarding-modal modal fade animated" id="onboardingFeaturesModal" style="z-index: 22222;" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-centered" role="document">
            <div class="modal-content text-center">
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
                <div class="onboarding-media"></div>
                <div class="onboarding-content with-gradient">
                    <h4 class="onboarding-title">
                        Add Services
                    </h4>

                    <div class="table-responsive">
                        <?php if(!CommonHelper::isKycUpdated(Auth::user()->id)): ?>
                        <p style="font-size:15px; color:red;">Your KYC is pending . Please update your KYC form. <a href="<?php echo e(url('user/profile#tab_sales')); ?>" onclick="tabMenu('tab_api');" class="mr-2 mb-2 btn btn-link">Click Here </a></p>
                        <?php endif; ?>
                        <!--------------------
                        START - Basic Table
                        -------------------->
                        <table class="table ">

                            <tbody>
                                <?php $__currentLoopData = $allService; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="img_title">
                                    <td>
                                        <img alt="" src="<?php echo e(asset('')); ?>/media/logos/<?php echo e($service->url); ?>">
                                    </td>
                                    <td>
                                        <div class="fs-main-info-add">
                                            <div class="fs-name">
                                                <?php echo e($service->service_name); ?>

                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fs-extra-info-add">

                                            <?php if(CommonHelper::isServiceActive(Auth::user()->id,$service->service_id)): ?>
                                                <strong>
                                                    <span class="mr-2 mb-2 p-2 text-light badge bg-success">Activated</span>
                                                </strong>
                                            <?php else: ?>
                                                <?php if(CommonHelper::serviceStatusCheck(Auth::user()->id,$service->service_id) == 'Pending'): ?>
                                                    <span class="mr-2 mb-2 p-2 text-light badge bg-info">Pending</span>
                                                <?php else: ?>
                                                    <?php if(CommonHelper::isKycUpdated(Auth::user()->id)): ?>
                                                        <?php if($service->is_activation_allowed == '1'): ?>
                                                        <span class="mr-2 mb-2 btn btn-primary" onclick="serviceActivate('<?php echo e($service->service_id); ?>')">Activate</span>
                                                        <?php else: ?>
                                                            <span class="mr-2 mb-2 p-2 text-light badge bg-info">Comming Soon</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                        </div>
                                    </td>

                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><?php /**PATH C:\xampp\htdocs\xettle\resources\views/include/user/topbar.blade.php ENDPATH**/ ?>