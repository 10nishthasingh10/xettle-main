<div class="tab-pane active" id="tab_payout">
    <div class="row">
        <div class="col-md-12">

            <div class="element-box">

                <div class="xttl-chart-loader" id="payoutChartOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <div class="element-actions">
                    <form class="form-inline justify-content-sm-end">
                        <div class="input-group input-group-sm ml-1">
                            <select class="form-control select2 xtl-user-picker" id="select-user-id">
                                <option value="0">All User</option>
                                <?php $__currentLoopData = $userList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($row->id); ?>"><?php echo e($row->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="input-group input-group-sm ml-1">
                            <div id="select-date-range" class="xtl-chart-date-picker">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span>Today</span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <h6 class="element-header">Payout Overview</h6>

                <div id="payout-chart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="total-payout-chart"></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-success bg-success"></i> Success
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-danger bg-danger"></i> Failed
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-dark bg-dark"></i> Reversed
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-primary bg-primary"></i> Processing
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader payoutModeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Payout Mode Success</h6>
                <div id="payoutModeSuccess" class="xttl-chart-container"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader payoutModeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Payout Mode Failed</h6>
                <div id="payoutModeFailed" class="xttl-chart-container"></div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader payoutAreaOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Payout Area Success</h6>
                <div id="payoutAreaSuccess" class="xttl-chart-container"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader payoutAreaOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Payout Area Failed</h6>
                <div id="payoutAreaFailed" class="xttl-chart-container"></div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/pgpaysecureco/public_html/resources/views/admin/dash_templates/tab_payout.blade.php ENDPATH**/ ?>