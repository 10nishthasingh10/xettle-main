<div class="tab-pane" id="tab_aeps">

    <div class="row">

        <!-- AEPS Chart 1 -->
        <div class="col-md-12">

            <div class="element-box">
                <div class="xttl-chart-loader" id="aepsTxnOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <div class="element-actions">
                    <form class="form-inline justify-content-sm-end">
                        <div class="input-group input-group-sm ml-1">
                            <select class="form-control select2 xtl-user-picker" id="aeps-user-id">
                                <option value="0">All User</option>
                                <?php $__currentLoopData = $userList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($row->id); ?>"><?php echo e($row->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="input-group input-group-sm ml-1">
                            <div id="aeps-date-range" class="xtl-chart-date-picker">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span>Today</span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <h6 class="element-header">AEPS Transactions</h6>

                <div id="aepsTxnChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalTxnApesChart"></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-success bg-success"></i> CW Success
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-danger bg-danger"></i> CW Failed
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <!-- AEPS Chart 2 -->
        <div class="col-md-12">

            <div class="element-box">
                <div class="xttl-chart-loader" id="aepsCountOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6 class="element-header">Count CW / BE / MS</h6>

                <div id="aepsCountChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalCountApesChart"></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-success bg-success"></i> Withdraw
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-danger bg-danger"></i> Balance Enq.
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-primary bg-primary"></i> Statement
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <!-- AEPS Chart 3 -->
        <div class="col-md-12">

            <div class="element-box">
                <div class="xttl-chart-loader" id="aepsMrcBoardOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6 class="element-header">Merchants On-Board</h6>

                <div id="aepsMrcBoardChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalAepsMrcBoardChart"></span>
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


        <!-- AEPS Chart 4 -->
        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader rootVolumeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Root Volume Success</h6>
                <div id="cwAepsSuccessByRoot" class="xttl-chart-container"></div>
            </div>
        </div>


        <!-- AEPS Chart 5 -->
        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader rootVolumeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Root Volume Failed</h6>
                <div id="cwAepsFailedByRoot" class="xttl-chart-container"></div>
            </div>
        </div>


        <!-- AEPS Chart 6 -->
        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader bankVolumeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Bank Volume Success</h6>
                <div id="cwAepsSuccessDoughnut" class="xttl-chart-container"></div>
            </div>
        </div>


        <!-- AEPS Chart 6 -->
        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader bankVolumeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Bank Volume Failed</h6>
                <div id="cwAepsFailedDoughnut" class="xttl-chart-container"></div>
            </div>
        </div>

    </div>

</div><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/dash_templates/tab_aeps.blade.php ENDPATH**/ ?>