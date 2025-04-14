<div class="tab-pane" id="tab_dmt">

    <div class="row">

        <!-- AEPS Chart 1 -->
        <div class="col-md-12">

            <div class="element-box">
                <div class="xttl-chart-loader" id="dmtTxnOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <div class="element-actions">
                    <form class="form-inline justify-content-sm-end">
                        <div class="input-group input-group-sm ml-1">
                            <select class="form-control select2 xtl-user-picker" id="dmt-user-id">
                                <option value="0">All User</option>
                                <?php $__currentLoopData = $userList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($row->id); ?>"><?php echo e($row->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="input-group input-group-sm ml-1">
                            <div id="dmt-date-range" class="xtl-chart-date-picker">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span>Today</span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <h6 class="element-header">DMT Transactions</h6>

                <div id="dmtTxnChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalTxnDmtChart"></span>
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
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <!-- AEPS Chart 3 -->
        <div class="col-md-12">

            <div class="element-box">
                <div class="xttl-chart-loader" id="dmtMrcBoardOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6 class="element-header">Merchants On-Board</h6>

                <div id="dmtMrcBoardChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalDmtMrcBoardChart"></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-primary bg-primary"></i> Merchants
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/dash_templates/tab_dmt.blade.php ENDPATH**/ ?>