@extends('layouts.user.app')
@section('title',$site_title)

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

<div class="element-wrapper compact pt-4 mb-0 pb-0">
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
                        <span id="unsettle-us" data-placement="top" data-toggle="tooltip" type="button" data-original-title="&#8377;{{number_format($unsettledUpiStack,2)}}"></span>
                    </div>
                    <div class="trending trending-down-basic"></div>
                </a>
            </div>
        </div>
        <div class="row"></div>
    </div>
</div>

<div class="row">

    <div class="col-md-12">

        <div class="element-box position-relative p-3">

            <div class="xttl-chart-loader" id="virtualAccountChartOverlay">
                <i class="fas fa-spinner fa-spin"></i>
            </div>

            <h6>Verification Requests</h6>

            <div id="virtualAccountChart" class="xttl-chart-container"></div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="d-flex flex-row justify-content-start">
                        <span class="ml-2 font-weight-bolder" id="virtualAccountChartTotal"></span>
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
                <a class="btn btn-primary btn-sm" href="{{url('va/payments')}}"><i class="os-icon os-icon-eye"></i><span>View More</span></a>
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
                            @if(!empty($callbacks))
                            @foreach($callbacks as $callback)
                            <tr>
                                <td class="nowrap">{{$callback->payee_vpa}}</td>
                                <td>₹{{$callback->amount}}</td>
                                <td class="text-center">{{$callback->customer_ref_id}}</td>
                                <td class="text-right">{{$callback->original_order_id}}</td>
                                <td class="text-right">{{$callback->created_at}}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5" class="text-center">No Record Found</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let value = changeNumberFormat(<?php echo empty($unsettledUpiStack) ? 0 : $unsettledUpiStack; ?>);
        $('#unsettle-us').html('&#8377;' + value);
    });
</script>
<script src="{{asset('common.js')}}"></script>
<script src="{{asset('user-js/user-va-dashboard.js')}}"></script>
@endsection