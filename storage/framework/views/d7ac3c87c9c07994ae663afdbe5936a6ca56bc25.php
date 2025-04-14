<?php $__env->startSection('title','UPI Dashboard'); ?>

<?php $__env->startSection('style'); ?>
<style>
    .daterangepicker {
        min-width: auto !important;
    }

    .xttl-chart-container {
        min-height: 230px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="element-wrapper compact pt-4">
    <div class="element-actions">
        <form class="form-inline justify-content-sm-end">
            <div class="input-group input-group-sm ml-1">
                <div id="select-date-range" class="xtl-chart-date-picker">
                    <i class="fa fa-calendar"></i>&nbsp;
                    <span>Today</span> <i class="fa fa-caret-down"></i>
                </div>
            </div>
        </form>
    </div>
    <h6 class="element-header">UPI Overview</h6>
    <div class="element-content">
        <div class="row">
            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">Count</div>
                    <div class="value" style="font-size: 1.43rem;" id="countMerchant">
                        0
                    </div>
                    <div class="trending trending-up-basic"></div>
                </a>
            </div>
            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">Success Amount</div>
                   
                    <div class="value" style="font-size: 1.43rem;" id="currentBusinessData">₹ <?php echo e(number_format($successAmount->amt,2)); ?></div>
                    <div class="trending trending-down-basic"></div>
                </a>
            </div>
            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">Active Merchant</div>
                    <div class="value" style="font-size: 1.43rem;"><?php echo e($active); ?></div>
                    <div class="trending trending-down-basic"></div>
                </a>
            </div>
            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">Unsettle Balance</div>
                    <div class="value" style="font-size: 1.43rem;">
                        <span id="unsettle-us" data-placement="top" data-toggle="tooltip" type="button" data-original-title="&#8377;<?php echo e(number_format($unsettledUpiStack,2)); ?>"></span>
                    </div>
                    <div class="trending trending-down-basic"></div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">

        <div class="element-box position-relative p-3">

            <div class="xttl-chart-loader" id="upiStackChartOverlay">
                <i class="fas fa-spinner fa-spin"></i>
            </div>

            <h6>UPI Stack</h6>

            <div id="upiStackChart" class="xttl-chart-container"></div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="d-flex flex-row justify-content-start">
                        <span class="ml-2 font-weight-bolder" id="upiStackChartTotal"></span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex flex-row justify-content-end">
                        <span class="mr-2">
                            <i class="fas fa-square text-success bg-success"></i> Credit
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="element-wrapper">
            <div class="element-actions">
                <a class="btn btn-primary btn-sm" href="<?php echo e(url('upi/upicallbacks')); ?>"><i class="os-icon os-icon-eye"></i><span>View More</span></a>
            </div>
            <h6 class="element-header">Latest Transactions</h6>
            <div class="element-box">
                <div class="table-responsive">
                    <table class="table table-lightborder">
                        <thead>
                            <tr>
                                <th>VPA</th>
                                <th>Amount</th>
                                <th class="text-center">Customer Ref Id</th>
                                <th class="text-right">Original Order Id</th>
                                <th class="text-right">Txn Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($callbacks)): ?>
                            <?php $__currentLoopData = $callbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $callback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="nowrap"><?php echo e($callback->payee_vpa); ?></td>
                                <td>₹<?php echo e($callback->amount); ?></td>
                                <td class="text-center"><?php echo e($callback->customer_ref_id); ?></td>
                                <td class="text-right"><?php echo e($callback->original_order_id); ?></td>
                                <td class="text-right"><?php echo e($callback->created_at); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No Record Found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Service Active Modal -->

<!-- End Active Modal -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    // function upiDashboardChart(searchText) {
    //     $.getJSON(`<?php echo e(secure_url('upi/dashboard-chart')); ?>/` + searchText, function(response) {
    //         $('#countMerchant').text(changeNumberFormat(response.count));
    //         $('#currentBusiness').text("₹" + response.amount);
    //     });
    // }
    // upiDashboardChart('today');

    $(document).ready(function() {
        let value = changeNumberFormat(<?php echo empty($unsettledUpiStack) ? 0 : $unsettledUpiStack; ?>);
        $('#unsettle-us').html('&#8377;' + value);
    });
</script>
<script src="<?php echo e(asset('common.js')); ?>"></script>
<script src="<?php echo e(asset('user-js/user-upi-stack-dashboard.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/user/upi/dashboard.blade.php ENDPATH**/ ?>