<div class="tab-pane" id="tab_recharge">

    <div class="row">

        <!-- AEPS Chart 1 -->
        <div class="col-md-12">

            <div class="element-box">
                <div class="xttl-chart-loader" id="rechargeTxnOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <div class="element-actions">
                    <form class="form-inline justify-content-sm-end">
                        <div class="input-group input-group-sm ml-1">
                            <select class="form-control select2 xtl-user-picker" id="recharge-user-id">
                                <option value="0">All User</option>
                                @foreach($userList as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group input-group-sm ml-1">
                            <div id="recharge-date-range" class="xtl-chart-date-picker">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span>Today</span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <h6 class="element-header">Recharge Transactions</h6>

                <div id="rechargeTxnChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalTxnRechargeChart"></span>
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


        <!-- AEPS Chart 6 -->
        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader bankVolumeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Recharge Success</h6>
                <div id="rechargeSuccessDoughnut" class="xttl-chart-container"></div>
            </div>
        </div>


        <!-- AEPS Chart 6 -->
        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader bankVolumeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>Recharge Failed</h6>
                <div id="rechargeFailedDoughnut" class="xttl-chart-container"></div>
            </div>
        </div>

    </div>

</div>