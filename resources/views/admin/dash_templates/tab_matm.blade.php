<div class="tab-pane" id="tab_matm">

    <div class="row">

        <!-- AEPS Chart 1 -->
        <div class="col-md-12">

            <div class="element-box">
                <div class="xttl-chart-loader" id="matmTxnOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <div class="element-actions">
                    <form class="form-inline justify-content-sm-end">
                        <div class="input-group input-group-sm ml-1">
                            <select class="form-control select2 xtl-user-picker" id="matm-user-id">
                                <option value="0">All User</option>
                                @foreach($userList as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group input-group-sm ml-1">
                            <div id="matm-date-range" class="xtl-chart-date-picker">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span>Today</span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <h6 class="element-header">AEPS Transactions</h6>

                <div id="matmTxnChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalTxnMatmChart"></span>
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


        <!-- AEPS Chart 2 -->
        <div class="col-md-12">

            <div class="element-box">
                <div class="xttl-chart-loader" id="matmCountOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6 class="element-header">Count CW / BE</h6>

                <div id="matmCountChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalCountMatmChart"></span>
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
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>