<div class="tab-pane" id="tab_UpiStackChart">

    <div class="row">

        <div class="col-md-12">
            <div class="element-box">

                <div class="xttl-chart-loader upiStackTxnOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <div class="element-actions">
                    <form class="form-inline justify-content-sm-end">
                        <div class="input-group input-group-sm ml-1">
                            <select class="form-control select2 xtl-user-picker" id="upistack-user-id">
                                <option value="0">All User</option>
                                @foreach($userList as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group input-group-sm ml-1">
                            <div id="upistack-date-range" class="xtl-chart-date-picker">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span>Today</span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <h6 class="element-header">UPI Stack Transactions</h6>

                <div id="upiStackTxnChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalUpiStackTxnChart"></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-success bg-success"></i> FPay
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-primary bg-primary"></i> IBL
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-12">
            <div class="element-box">

                <div class="xttl-chart-loader upiStackDisputeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6 class="element-header">UPI Stack Disputed</h6>

                <div id="upiStackDisputeChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="totalUpiStackDisputeChart"></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-success bg-success"></i> FPay
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-primary bg-primary"></i> IBL
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader upiStackTxnOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>UPI Stack Transactions</h6>
                <div id="upiStackTxnDoughnut" class="xttl-chart-container"></div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="element-box">
                <div class="xttl-chart-loader upiStackDisputeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6>UPI Stack Disputed</h6>
                <div id="upiStackDisputeDoughnut" class="xttl-chart-container"></div>
            </div>
        </div>

    </div>

</div>