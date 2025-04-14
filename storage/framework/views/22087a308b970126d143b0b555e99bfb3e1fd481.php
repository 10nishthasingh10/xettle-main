<?php $__env->startSection('title','Recharge Dashboard'); ?>
<?php $__env->startSection('style'); ?>
<style>
    .daterangepicker {
        min-width: auto !important;
    }

    #aepsTxnChart {
        min-height: 210px;
    }

    #cwAepsSuccessDoughnut {
        min-height: 240px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="element-wrapper compact pt-4 pb-0">

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

    <h6 class="element-header">
        Business Overview
    </h6>
    <div class="element-content">
        <div class="row">
            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">
                        Current Business
                    </div>
                    <div class="value" style="font-size: 1.43rem;" id="currentBusiness">
                    <?php echo e(round(@$txnData->totalAmount)); ?>

                    </div>
                    <div class="trending trending-up-basic">
                    </div>
                </a>
            </div>

            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">
                        Recharge Balance
                    </div>
                    <div class="value" style="font-size: 1.43rem;">
                        ₹ <?php echo e(CommonHelper::getServiceAccount(Auth::user()->id,'srv_1626077505')->transaction_amount + $lockedAmount); ?>

                    </div>
                    <div class="trending trending-down-basic">
                    </div>
                </a>
            </div>

            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">
                        Commission Amount
                    </div>
                    <div class="value" style="font-size: 1.43rem;" id="commissionAmount">
                        ₹ <?php echo e(round(@$commission->totalAmount)); ?>

                    </div>
                    <div class="trending trending-down-basic">
                    </div>
                </a>
            </div>

            <div class="col-sm-3 col-md-3">
                <div class="element-box-tp">
                    <a class="btn btn-primary" href="<?php echo e(custom_secure_url('user/dashboard')); ?>">
                        <i class="os-icon os-icon-refresh-ccw"></i><span>Add Balance</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="element-wrapper p-0">

    <div class="row">
        <div class="col-md-12">

            <div class="element-box position-relative p-3">
                <div class="xttl-chart-loader" id="aepsTxnOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6 class="element-header mb-3">
                    Transactions

                    <div class="float-right">
                        <ul class="nav nav-pills smaller d-none d-lg-flex">
                            <li class="nav-item"><button class="nav-link btn btnShowHide active" data-toggle="tab" data-show="amount"> Amount</button></li>
                            <li class="nav-item"><button class="nav-link btn btnShowHide" data-toggle="tab" data-show="count"> Count</button></li>
                        </ul>
                    </div>

                </h6>

                <div id="aepsTxnChart" class="xttl-chart-container"></div>

                <div class="row" id="aepsAmount"></div>

            </div>

        </div>


       
    </div>
</div>


<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="element-wrapper">
            <div class="element-box p-3">
                <h6 class="element-header">
                    10 Transactions

                    <div class="float-right">
                        <a href="<?php echo e(url('aeps/settlement')); ?>" class="btn btn-sm btn-primary">View More</a>
                    </div>
                </h6>
                <div class="table-responsive">
                    <table class="table table-lightborder">
                        <thead>
                            <tr>
                                <th>Txn Id</th>
                                <th>Txn Ref Id</th>
                                <th>Amount</th>
                                <th>Txn Commission</th>
                                <th>Txn Date</th>
                                <th>Closing Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($txnDatas)): ?>
                            <tr>
                                <?php $__currentLoopData = $txnDatas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txnData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td>
                                    <?php echo e($txnData->order_ref_id); ?>

                                </td>
                                <td class="nowrap">
                                    <?php echo e($txnData->order_ref_id); ?>

                                </td>
                                <td class="nowrap">
                                    <?php echo e($txnData->amount); ?>

                                </td>
                                <td>
                                    <?php echo e($txnData->commission); ?>

                                </td>
                                <td>
                                    <?php echo e($txnData->created_at); ?>

                                </td>
                                
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                            <tr>
                                <td cols="6">No record Found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    var aepsGraphsUrl = `<?php echo e(custom_secure_url('graphs/aeps')); ?>`;
</script>
<script src="<?php echo e(asset('common.js')); ?>"></script>
<script src="<?php echo e(asset('user-js/dashboard-aeps.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/user/recharge/dashboard.blade.php ENDPATH**/ ?>