@extends('layouts.user.app')
@section('title','Payout Dashboard')

@section('style')
<style>
    .daterangepicker {
        min-width: auto !important;
    }

    .xttl-chart-container {
        min-height: 230px;
    }
</style>
@endsection

@section('content')
<div class="element-wrapper compact pb-3">
    <div class="element-actions">
        {{--
        <a class="btn btn-secondary btn-sm" href="{{url('user/dashboard')}}" data-target="#onboardingWideFormModal" data-toggle="modal">
        <i class="os-icon os-icon-ui-22"></i><span>Add Wallet</span>
        </a>
        --}}
    </div>
    <h6 class="element-header">
        Payout Overview
    </h6>

    <div class="row">
        <div class="col-sm-12 col-lg-8 col-xxl-7">
            <div class="element-balances justify-content-between mobile-full-width">
                <div class="balance balance-v2">
                    <div class="balance-title">
                        Service Balance
                    </div>
                    <div class="balance-value">
                        <span class="d-xxl-none">₹{{CommonHelper::payoutDashboard(Auth::user()->id)->transaction_amount}}</span>
                        <span class="d-none d-xxl-inline-block">₹{{CommonHelper::payoutDashboard(Auth::user()->id)->transaction_amount}}</span>
                        <span class="trending trending-down-basic">
                            <?php
                            $lockedAmount = isset(DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum) ? \DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum : 0;
                            ?>
                            ₹{{number_format($lockedAmount, 2)}}
                        </span>
                    </div>
                </div>
            </div>
            <div class="element-wrapper pb-4 mb-4 border-bottom">
                <div class="element-box-tp">
                    <a class="btn btn-primary" href="{{secure_url('user/dashboard')}}">
                        <i class="os-icon os-icon-refresh-ccw"></i><span>Add Balance</span>
                    </a>
                    <a class="btn btn-grey" href="{{url('payout/bulk')}}"><i class="os-icon os-icon-log-out"></i><span>Upload Bulkpayout</span></a>
                </div>
            </div>

            <div class="element-wrapper compact pb-3">

                <div class="element-box-tp">

                    <div class="row">
                        <div class="col-12 col-sm-12 mb-3">
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
                            <h6 class="element-box-header">
                                In-Out Flow
                            </h6>

                        </div>

                        <div class="col-6 col-md-6">
                            <a class="element-box el-tablo centered trend-in-corner smaller" href="#script">

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="label">In-flow</div>
                                        <div class="value text-success">&#8377; <span id="tot_in_amt">0</span></div>
                                    </div>
                                    <div class="col-sm-1 pb-3 border-right border-secondary"></div>
                                    <div class="col-sm-5">
                                        <div class="label">Requests</div>
                                        <div class="value"><span id="tot_in_req">0</span></div>
                                    </div>
                                </div>

                            </a>
                        </div>

                        <div class="col-6 col-md-6">

                            <a class="element-box el-tablo centered trend-in-corner smaller" href="#script">

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="label">Out-flow</div>
                                        <div class="value text-primary">&#8377; <span id="tot_out_amt">0</span></div>
                                    </div>
                                    <div class="col-sm-1 pb-3 border-right border-secondary"></div>
                                    <div class="col-sm-5">
                                        <div class="label">Requests</div>
                                        <div class="value"><span id="tot_out_req">0</span></div>
                                    </div>
                                </div>

                            </a>

                        </div>

                    </div>

                </div>


            </div>
        </div>

        <div class="col-sm-12 col-lg-4 col-xxl-5 d-none d-lg-block">
            <div class="cta-w cta-with-media purple" style="height: 90%">
                <div class="cta-content">
                    <div class="highlight-header">
                        UPI
                    </div>
                    <h2 class="cta-header">Don't miss the UPI Game!</h2>
                    <h4 class="cta-header">Generate Static UPI QR Codes using APIs</h4>
                    <a class="store-google-btn" href="#"><img alt="" src="{{url('public/img/button-view-docs.png')}}"></a>
                </div>
                <div class="cta-media">
                    <img alt="" src="{{url('public/img/side-media.png')}}">
                </div>
            </div>
        </div>


        <div class="col-md-12">

            <div class="element-box position-relative p-3">

                <div class="xttl-chart-loader" id="payoutChartOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <h6 class="element-header mb-3">In-Out Flow</h6>

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
                                <i class="fas fa-square text-success bg-success"></i> In-Flow
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-square text-primary bg-primary"></i> Payout
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="row">

    <div class="col-6 col-md-3 col-xxl-3">
        <a class="element-box el-tablo centered trend-in-corner smaller" id="processed-amt" href="#">
            <div class="label">
                Processed Amount
            </div>
            <div class="value font-1-1 text-success processed-amt" data-placement="top" data-toggle="tooltip" type="button" data-original-title="0">0</div>
            <div class="trending trending-up">
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-3 col-xxl-3">
        <a class="element-box el-tablo centered trend-in-corner smaller" id="failed-amt" href="#">
            <div class="label">
                Failed Amount
            </div>
            <div class="value font-1-1 text-danger failed-amt" data-placement="top" data-toggle="tooltip" type="button" data-original-title="0">0</div>
            <div class="trending trending-down">
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-3 col-xxl-3">
        <a class="element-box el-tablo centered trend-in-corner smaller" id="reversed-amt" href="#">
            <div class="label">
                Reversed Amount
            </div>
            <div class="value font-1-1 reversed-amt" data-placement="top" data-toggle="tooltip" type="button" data-original-title="0">0</div>
            <div class="trending trending-pending bg-dark">
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-3 col-xxl-3">
        <a class="element-box el-tablo centered trend-in-corner smaller" id="processing-amt" href="#">
            <div class="label">
                Processing Amount
            </div>
            <div class="value font-1-1 processing-amt" data-placement="top" data-toggle="tooltip" type="button" data-original-title="0">0</div>
            <div class="trending trending-pending bg-primary">
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </div>
        </a>
    </div>

</div>

<div class="element-wrapper compact pb-3">
    <div class="row">
        <div class="col-lg-12 col-xxl-12">

            <div class="element-box position-relative p-3">

                <h6 class="element-header mb-3">
                    Payout Overview

                    <div class="float-right">
                        <ul class="nav nav-pills smaller d-none d-lg-flex">
                            <li class="nav-item"><button class="nav-link btn btnShowHide active" data-toggle="tab" data-show="amount"> Amount</button></li>
                            <li class="nav-item"><button class="nav-link btn btnShowHide" data-toggle="tab" data-show="count"> Count</button></li>
                        </ul>
                    </div>
                </h6>

                <div class="xttl-chart-loader" id="payoutTransactionOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>

                <div id="payoutTransactionChart" class="xttl-chart-container"></div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-start">
                            <span class="ml-2 font-weight-bolder" id="payoutTransactionTotal"></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-success bg-success"></i> Processed
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
    </div>
</div>

@endsection

@section('scripts')
<script>
    $('#kt_modal_new_card').on('hidden.bs.modal', function() {
        location.reload();
    });
</script>
<script src="{{asset('common.js')}}"></script>
<script src="{{asset('user-js/user-payout-dashboard.js')}}"></script>
@endsection