@extends('layouts.user.app')
@section('title', $site_title)

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
    <h6 class="element-header">{{$page_title}}</h6>
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
                    <div class="label text-success">Amount</div>
                    <div class="value" style="font-size: 1.43rem;" id="currentBusiness">₹0</div>
                    <div class="trending trending-down-basic"></div>
                </a>
            </div>
            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">Active Merchant</div>
                    <div class="value" style="font-size: 1.43rem;">{{$active}}</div>
                    <div class="trending trending-down-basic"></div>
                </a>
            </div>
            <div class="col-sm-3 col-md-3">
                <a class="element-box el-tablo" href="#">
                    <div class="label text-success">Unsettle Balance</div>
                    <div class="value" style="font-size: 1.43rem;">
                        <span id="unsettle-sc" data-placement="top" data-toggle="tooltip" type="button" data-original-title="&#8377;{{number_format($unsettledSmartCollect,2)}}"></span>
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

            <div class="xttl-chart-loader" id="smartCollectChartOverlay">
                <i class="fas fa-spinner fa-spin"></i>
            </div>

            <h6>Smart Collect</h6>

            <div id="smartCollectChart" class="xttl-chart-container"></div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="d-flex flex-row justify-content-start">
                        <span class="ml-2 font-weight-bolder" id="smartCollectChartTotal"></span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex flex-row justify-content-end">
                        <span class="mr-2">
                            <i class="fas fa-square text-success bg-success"></i> VAN
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-square text-primary bg-primary"></i> VPA
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
                <a class="btn btn-primary btn-sm" href="{{url('collect/payments')}}"><i class="os-icon os-icon-eye"></i><span>View More</span></a>
            </div>
            <h6 class="element-header">Latest Transactions</h6>
            <div class="element-box">
                <div class="table-responsive">
                    <table class="table table-lightborder">
                        <thead>
                            <tr>
                                <th>VAN/VPA</th>
                                <th>Amount</th>
                                <th class="text-center">UTR</th>
                                <th class="text-right">Txn Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($callbacks))
                            @foreach($callbacks as $callback)
                            <tr>
                                <td class="nowrap">{{!empty($callback->v_account_number)?$callback->v_account_number:(!empty($callback->virtual_vpa_id)?$callback->virtual_vpa_id:'')}}</td>
                                <td>₹{{$callback->amount}}</td>
                                <td class="text-center">{{$callback->utr}}</td>
                                <td class="text-right">{{$callback->trn_credited_at}}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="4" class="text-center">No record Found</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Service Active Modal -->

<!-- End Active Modal -->
@endsection

@section('scripts')
<script>
    // function upiDashboardChart(searchText) {
    //     $.getJSON("{{secure_url('collect/dashboard-chart')}}/" + searchText, function(response) {
    //         $('#countMerchant').text(changeNumberFormat(response.count));
    //         $('#currentBusiness').text("₹" + response.amount);
    //     });
    // }
    // upiDashboardChart('today');

    $(document).ready(function() {
        let value = changeNumberFormat(<?php echo empty($unsettledSmartCollect) ? 0 : $unsettledSmartCollect; ?>);
        $('#unsettle-sc').html('&#8377;' + value);
    });
</script>
<script src="{{asset('common.js')}}"></script>
<script src="{{asset('user-js/user-smart-collect-dashboard.js')}}"></script>
@endsection