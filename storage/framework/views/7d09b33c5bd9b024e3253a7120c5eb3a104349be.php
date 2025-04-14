<?php if(Request::is('payout/*') || Request::is('payout') || Request::is('aeps/*') || Request::is('aeps') || Request::is('upi') || Request::is('upi/*') || Request::is('collect') || Request::is('collect/*') || Request::is('va') || Request::is('va/*') || Request::is('verification') || Request::is('verification/*') ): ?>
<?php $__currentLoopData = $serviceData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $myservice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
if ($myservice->service_slug == 'upi_collect') {
    $newcheckUri = 'upi';
    $checkUri = Request::is($newcheckUri . '/*');
    $newcheckUri = Request::is($newcheckUri);
} elseif ($myservice->service_slug == 'smart_collect') {
    $newcheckUri = 'collect';
    $checkUri = Request::is($newcheckUri . '/*');
    $newcheckUri = Request::is($newcheckUri);
} elseif ($myservice->service_slug == 'verification') {
    $newcheckUri = 'verification';
    $checkUri = Request::is($newcheckUri . '/*');
    $newcheckUri = Request::is($newcheckUri);
} else {
    $checkUri = Request::is($myservice->service_slug . '/*');
    $newcheckUri = Request::is($myservice->service_slug);
}

$aepsTodayBusiness =  DB::table('aeps_transactions')
->whereDate('created_at', date('Y-m-d'))
->where(['transaction_type' => 'cw', 'user_id' => Auth::user()->id, 'status' => 'success'])
->sum('transaction_amount');
$unsettledThresholdAmount = @DB::table('user_config')
                        ->select("threshold as amt")
                        ->where('user_id', Auth::user()->id)
                        ->first()->amt;
?>
<?php if($checkUri || $newcheckUri): ?>
<div class="fancy-selector-current ">
    <div class="fs-img">
       
    </div>
    <div class="fs-main-info">
        <div class="fs-name">
            <?php echo e($myservice->service_name); ?>

        </div>
        <?php if( Request::segment(1) == 'upi'): ?>
        <?php
        $data = CommonHelper::callBackUPITotalAmount(Auth::user()->id);
        $totalAmount = $data['amount'];
        ?>
        <div class="fs-sub">
            <span>Balance:</span> <strong>₹<?php echo e($totalAmount); ?></strong>
        </div>

        <?php elseif( Request::segment(1) == 'collect'): ?>
        <div class="fs-sub">
            <span>Balance:</span> <strong>₹<?php echo e((CommonHelper::callBackSmartCollectTotalAmount(Auth::user()->id))['amount']); ?></strong>
        </div>

        <?php elseif( Request::segment(1) == 'va'): ?>
        <div class="fs-sub">
            <span>Balance:</span> <strong>₹<?php echo e((CommonHelper::callBackVirtualAccountTotalAmount(Auth::user()->id))['amount']); ?></strong>
        </div>

        <?php elseif( Request::segment(1) == 'verification'): ?>
        <div class="fs-sub">
            <span></span>
        </div>

        <?php elseif(Request::segment(1) == 'aeps'): ?>
        <?php if(CommonHelper::isServiceActive(Auth::user()->id,$myservice->service_id)): ?>
        <div class="fs-sub">
            <span>Balance:</span> <strong>₹<?php echo e($aepsTodayBusiness); ?></strong>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <?php if(CommonHelper::isServiceActive(Auth::user()->id,$myservice->service_id)): ?>
        <div class="fs-sub">
            <?php
            if (Request::segment(1) == 'payout') {
                $lockedAmount = isset(\DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum) ? \DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum : 0;
            } else {
                $lockedAmount = isset(CommonHelper::getServiceAccount(Auth::user()->id, $myservice->service_id)->locked_amount) ? CommonHelper::getServiceAccount(Auth::user()->id, $myservice->service_id)->locked_amount : 0;
            }
            ?>

            <span>Balance:</span> <strong>₹<?php echo e(CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->transaction_amount + $lockedAmount); ?></strong>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="fs-extra-info">
        <?php if(CommonHelper::isServiceActive(Auth::user()->id,$myservice->service_id)): ?>
        <strong>
            <?php echo e(substr(CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->service_account_number,8,12)); ?>

        </strong>
        <span>ending</span>
        <?php endif; ?>
    </div>

    <div class="fs-selector-trigger">
        <i class="os-icon os-icon-arrow-down4"></i>
    </div>
</div>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
<div class="fancy-selector-current ">
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

                                <?php endif; ?>
            </strong>
        </div>
    </div>
    <div class="fs-extra-info">

        <strong>
            <?php echo e(substr(Auth::user()->account_number,8,12)); ?>

        </strong>
        <span>ending</span>
    </div>

    <div class="fs-selector-trigger">
        <i class="os-icon os-icon-arrow-down4"></i>
    </div>
</div>
<?php endif; ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/include/user/serviceautoselect.blade.php ENDPATH**/ ?>