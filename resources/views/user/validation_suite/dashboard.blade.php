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

<div class="element-wrapper compact pt-4">

    <div class="row">
        <div class="col-sm-12 col-lg-8 col-xxl-7">

            <div class="row">
                <div class="col-sm-12">

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
                            <div class="col-sm-4 col-md-4">
                                <a class="element-box el-tablo" href="#">
                                    <div class="label text-primary">Validate BANKs</div>
                                    <div class="value text-primary" style="font-size: 1.43rem;" id="validateBank">
                                        0
                                    </div>
                                    <div class="trending trending-up-basic"></div>
                                </a>
                            </div>
                            <div class="col-sm-4 col-md-4">
                                <a class="element-box el-tablo" href="#">
                                    <div class="label text-primary">Validate VPAs</div>
                                    <div class="value text-primary" style="font-size: 1.43rem;" id="validateVpa">0</div>
                                    <div class="trending trending-down-basic"></div>
                                </a>
                            </div>
                            <div class="col-sm-4 col-md-4">
                                <a class="element-box el-tablo" href="#">
                                    <div class="label text-primary">Validate IFSC</div>
                                    <div class="value text-primary" style="font-size: 1.43rem;" id="validateIfsc">0</div>
                                    <div class="trending trending-down-basic"></div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-md-12">

                    <div class="element-box position-relative p-3">

                        <div class="xttl-chart-loader" id="validationChartOverlay">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>

                        <h6>Verification Requests</h6>

                        <div id="validationChart" class="xttl-chart-container"></div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="d-flex flex-row justify-content-start">
                                    <span class="ml-2 font-weight-bolder" id="validationChartTotal"></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex flex-row justify-content-end">
                                    <span class="mr-2">
                                        <i class="fas fa-square text-success bg-success"></i> BANK
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-square text-danger bg-danger"></i> VPA
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-square text-primary bg-primary"></i> IFSC
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>


        <div class="col-sm-12 col-lg-4 col-xxl-5 d-none d-lg-block">
            <div class="cta-w cta-with-media purple" style="height: 95%">
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
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="element-wrapper">
                <div class="element-actions">
                    <a class="btn btn-primary btn-sm" href="{{url('validation/transactions')}}"><i class="os-icon os-icon-eye"></i><span>View More</span></a>
                </div>
                <h6 class="element-header">Latest 10 Transactions</h6>
                <div class="element-box">
                    <div class="table-responsive">
                        <table class="table table-lightborder">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Validation Type</th>
                                    <th>Status</th>
                                    <th>Req. Data</th>
                                    <th>Txn Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($callbacks))
                                @foreach($callbacks as $callback)
                                <tr>
                                    <td>{{$callback->request_id}}</td>
                                    <td>{{strtoupper($callback->validation_type)}}</td>
                                    <td>
                                        @if($callback->status == 'found' || $callback->status == 'valid')
                                        <span class="badge badge-success">{{strtoupper($callback->status)}}</span>
                                        @elseif($callback->status == 'not found' || $callback->status == 'invalid')
                                        <span class="badge badge-danger">{{strtoupper($callback->status)}}</span>
                                        @else
                                        {{$callback->status}}
                                        @endif
                                    </td>
                                    <td>
                                        @if($callback->validation_type == 'vpa')
                                        {{$callback->vpa}}
                                        @elseif($callback->validation_type == 'bank')
                                        {{CommonHelper::masking('bank', $callback->bank_number)}} <br> {{$callback->ifsc}}
                                        @elseif($callback->validation_type == 'ifsc')
                                        {{$callback->ifsc}}
                                        @endif
                                    </td>
                                    <td>{{$callback->created_at}}</td>
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

</div>
@endsection

@section('scripts')
<script src="{{asset('common.js')}}"></script>
<script src="{{asset('user-js/user-validation-dashboard.js')}}"></script>
@endsection