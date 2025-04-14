@extends('layouts.user.app')
@section('title',ucfirst($page_title))

@section('style')
<style>
    .daterangepicker {
        min-width: auto !important;
    }

    .xttl-chart-container {
        min-height: 250px;
    }
</style>
@endsection

@section('content')
<div class="element-wrapper compact pt-4">

    @if(Auth::user()->email_verified_at != null)

    @else
    <div aria-live="polite" aria-atomic="true" style="position: relative;/* min-height: 200px; */">
        <div class="toast" data-animation="true" style="position: absolute;top: 0;right: 0;background-color: rgba(255,255,255,.85);border: 1px solid rgba(0,0,0,.1);border-radius: .25rem;box-shadow: 0 0.25rem 0.75rem rgb(0 0 0 / 10%);backdrop-filter: blur(10px);z-index: 999;">
            <div class="toast-header" style="
    display: flex;
    -ms-flex-align: center;
    align-items: center;
    padding: .25rem .75rem;
    color: #6c757d;
    background-color: rgba(255,255,255,.85);
    background-clip: padding-box;
    border-bottom: 1px solid rgba(0,0,0,.05);
    ">
                <svg class="bd-placeholder-img rounded mr-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img">
                    <rect fill="#007aff" width="100%" height="100%"></rect>
                </svg>
                <strong class="mr-auto">Xettel</strong>

                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="toast-body" style="padding: .75rem;">
                Your email is not verified yet. Please check your email and click on verify link.
            </div>
        </div>
    </div>

    @endif
    <div class="element-actions">
        <a class="btn btn-secondary btn-sm" href="{{url('/payout/contacts')}}">
            <i class="os-icon os-icon-eye"></i><span>View Contacts</span>
        </a>
        <a class="btn btn-success btn-sm" href="{{url('/payout/bulk')}}">
            <i class="os-icon os-icon-grid-10"></i><span>Bulk Payouts</span>
        </a>
    </div>
    <h6 class="element-header">
        Account Overview
    </h6>
    <div class="element-box-tp">
        <div class="row">
            <div class="col-lg-7 col-xxl-6">
                <!--START - BALANCES-->
                <div class="element-balances" style="padding-left: 0px; justify-content: flex-start;">
                    <div class="balance ">
                        <div class="balance-title">
                            Primary Balance
                        </div>
                        <div class="balance-value text-success">
                            <span>
                                @if((Auth::user()->transaction_amount + Auth::user()->locked_amount - $unsettledThresholdAmount) > 0)
                                ₹{{number_format((Auth::user()->transaction_amount+Auth::user()->locked_amount) - $unsettledThresholdAmount,2)}}
                                @else
                                ₹{{number_format(0,2)}}
                                @endif
                            </span>
                        </div>
                        <div class="balance-link">
                            <a class="btn btn-link btn-underlined" href="{{url('user/transactions')}}"><span>View Transactions</span><i class="os-icon os-icon-arrow-right4"></i></a>
                        </div>
                    </div>

                    <div class="balance">
                        <div class="balance-title">
                            Unrealised
                        </div>
                        <div class="balance-value">
                            @if (Auth::user()->transaction_amount >= $unsettledThresholdAmount)
                            &#8377;{{number_format($unsettledThresholdAmount, 2)}}
                            @else
                            @if (Auth::user()->transaction_amount > 0)
                            &#8377;{{number_format(Auth::user()->transaction_amount, 2)}}
                            @else
                            &#8377;0
                            @endif

                            @endif
                        </div>
                        <div class="balance-link">
                            @if(!empty($unsettledSmartCollect))
                            <!--<a class="btn btn-link btn-underlined" href="{{url('collect/payments')}}">
                                <span data-placement="top" data-toggle="tooltip" type="button" data-original-title="Smart Collect">&#8377;{{number_format($unsettledSmartCollect,2)}}</span>
                            </a> -->
                            @endif

                            @if(!empty($unsettledUpiStack))
                            @if(!empty($unsettledSmartCollect))

                            @endif
                            <!-- <a class="btn btn-link btn-underlined ml-0" href="{{url('upi/upicallbacks')}}">
                                <span data-placement="top" data-toggle="tooltip" type="button" data-original-title="UPI Stack">&#8377;{{number_format($unsettledUpiStack,2)}}</span>
                            </a> -->
                            @endif

                            @if(!empty($unsettledVirtualAccount))
                            @if(!empty($unsettledSmartCollect) || !empty($unsettledUpiStack))

                            @endif
                            <!--<a class="btn btn-link btn-underlined ml-0" href="{{url('va/payments')}}">
                                <span data-placement="top" data-toggle="tooltip" type="button" data-original-title="Virtual Account">&#8377;{{number_format($unsettledVirtualAccount,2)}}</span>
                            </a> -->
                            @endif

                            <span data-placement="top" class="text-primary" data-toggle="tooltip" type="button" data-original-title="Limit Amount">Limit : &#8377;{{number_format($unsettledThresholdAmount, 2)}}</span>

                        </div>
                    </div>

                    {{-- <div class="balance">
                        <div class="balance-title">
                            Blocked
                        </div>
                        <div class="balance-value danger">
                            ₹{{number_format(Auth::user()->locked_amount,2)}}
                </div>
                <div class="balance-link">
                    <a class="btn btn-link btn-underlined btn-gold" href="#"><span>Check Queue</span><i class="os-icon os-icon-arrow-right4"></i></a>
                </div>
            </div> --}}

        </div>
        <!--END - BALANCES-->

    </div>
    <div class="col-lg-5 col-xxl-6">
        <!--START - MESSAGE ALERT-->

        <div class="alert alert-warning borderless">
            <h5 class="alert-heading">
                @if(CommonHelper::isKycUpdated(Auth::user()->id))
                Make payouts directly from bank account
                @else
                Update KYC
                @endif
            </h5>
            <p>
                @if(CommonHelper::isKycUpdated(Auth::user()->id))
                You can earn: 15,000 Membership Rewards points for each approved referral – up to 55,000 Membership Rewards points per calendar year.
                @else
                Your KYC is pending . Please update your KYC form.
                @endif

            </p>
            <div class="alert-btn">
                @if(CommonHelper::isKycUpdated(Auth::user()->id))
                <a class="btn btn-white-gold" href="#"><i class="os-icon os-icon-ui-92"></i><span>Connect Bank Account</span></a>
                @else
                <a class="btn btn-white-gold" href="{{url('user/profile#tab_sales')}}"><i class="os-icon os-icon-ui-92"></i><span>Profile Update</span></a>
                @endif
            </div>
        </div>
        <!--END - MESSAGE ALERT-->
    </div>
</div>
</div>
</div>
<div class="row">
    <div class="col-lg-7 col-xxl-6">
        <!--START - CHART-->
        <div class="element-wrapper pb-3">

            <div class="row">
                <div class="col-md-12">

                    <div class="element-box position-relative">

                        <div class="xttl-chart-loader" id="primaryFundOverlay">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>

                        <div class="element-actions">
                            <form class="form-inline justify-content-sm-end">
                                <div id="select-date-range" class="xtl-chart-date-picker">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span>Today</span> <i class="fa fa-caret-down"></i>
                                </div>
                            </form>
                        </div>

                        <h6 class="element-box-header">Primary Balance Flow</h6>

                        <div id="primaryFundChart" class="xttl-chart-container"></div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="d-flex flex-row justify-content-start">
                                    <span class="ml-2 font-weight-bolder" id="primaryFundTotal"></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex flex-row justify-content-end">
                                    <span class="mr-2">
                                        <i class="fas fa-square text-success bg-success"></i> Inward
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-square text-danger bg-danger"></i> Outward
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
        <!--END - CHART-->
    </div>

    @if((isset($isInternalTransfer->is_internal_transfer_enable) && $isInternalTransfer->is_internal_transfer_enable == '0' ) || $globalInternalTransfer == '0')
    <div class="col-lg-5 col-xxl-6">
            <div class="cta-w cta-with-media purple" style="height: 90%">
                <div class="cta-content">
                    <div class="highlight-header">
                        UPI
                    </div>
                    <h2 class="cta-header">Don't miss the UPI Game!</h2>
                    <h4 class="cta-header">Generate Static UPI QR Codes using APIs</h4>
                    <a class="store-google-btn" href="#"><img alt="" src="https://sandbox.xettle.io/public/img/button-view-docs.png"></a>
                </div>
                <div class="cta-media">
                    <img alt="" src="https://sandbox.xettle.io/public/img/side-media.png">
                </div>
            </div>
        </div>
        @else
    <div class="col-lg-5 col-xxl-6">
        <!--START - Money Withdraw Form-->
        <div class="element-wrapper pb-3">
            <div class="element-box" style="min-height: 363px;">
                <form id="transfer_amount" class="form" method="post" role="transfer-amount" action="{{url('user/accounts/transfer-amount')}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{encrypt(Auth::user()->id)}}" />
                    <h5 class="element-box-header">
                        Manage Funds
                    </h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="lighter" for=""> Amount <span class="required" style="color:red">*</span></label>
                                <input class="form-control mb-2 mr-sm-2 mb-sm-0" id="transfer_amounts" name="transfer_amount" placeholder="Enter Amount..." required type="number">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="lighter " for="">Transfer to <span class="required" style="color:red">*</span></label>
                                <select class="form-control" name="service_id" id="service_id" required>
                                    <option value="">Select Account Number</option>
                                    @foreach($serviceData as $value)
                                    @if(
                                        $value->service_account_number != null &&
                                        (
                                            $value->service_id == PAYOUT_SERVICE_ID ||
                                            $value->service_id == VALIDATE_SERVICE_ID ||
                                            $value->service_id == DMT_SERVICE_ID ||
                                            $value->service_id == PAN_CARD_SERVICE_ID ||
                                            $value->service_id == RECHARGE_SERVICE_ID
                                        )
                                    )
                                    <option value="{{encrypt($value->id)}}">
                                        {{$value->service_account_number}} ({{$value->service_name}})
                                    </option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="lighter" for="">Remarks</label>
                                <textarea class="form-control" name="remarks"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-buttons-w text-right compact">
                        <button type="submit" data-request="ajax-submit" data-target='[role="transfer-amount"]' disabled='disabled' data-targetform='transfer_amount' id="btnAmountTransfer" class="btn btn-primary">
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!--END - Money Withdraw Form-->
    </div>
    @endif
</div>
<!--START - Transactions Table-->
<div class="element-wrapper">
    <div class="element-box">

        <div class="table-responsive">
            <table id="transaction_table" width="100%" class="table table-striped table-lightfont">
                <thead>
                    <tr>
                        <th>#Id</th>
                        <th>Account Number</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Narration</th>
                        <th>Opening Balance</th>
                        <th>Closing Balance</th>
                        <th>Remarks</th>
                        <th>Created At </th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($transaction) > 0)
                    @foreach ($transaction as $transactions)
                    <tr>
                        <td>
                            @if ($transactions->trans_id)
                            {{$transactions->trans_id}}
                            @else
                            {{$transactions->txn_id}}
                            @endif
                        </td>
                        <td>{{$transactions->account_number}}</td>
                        <td>{{$transactions->tr_amount}}</td>
                        <td>{{$transactions->tr_date}}</td>
                        <td>
                            @if($transactions->tr_type == 'dr')
                            {!!CommonHelper::showSpan('dr')!!}
                            @else
                            {!!CommonHelper::showSpan('cr')!!}
                            @endif</td>
                        <td>{{$transactions->tr_narration}}</td>
                        <td>{{$transactions->opening_balance}}</td>
                        <td>{{$transactions->closing_balance}}</td>
                        <td>{{$transactions->remarks}}</td>
                        <td>{{$transactions->created_at}}</td>
                    </tr>
                    @endforeach

                    @if(count($transaction) > 9)
                    <tr>

                        <td colspan="11" style="text-align: center;
    color: white;
    font-size: 15px;
    background-color: #293145;"><a href="/user/transactions" style="color: white;text-decoration: none;">View Details</a></td>

                    </tr>
                    @endif

                    @else
                    <tr>
                        <td colspan="11" style="text-align:center; color:red; font-size:14px">No Transaction Found</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<!-- Service Active Modal -->
<div aria-hidden="true" class="onboarding-modal modal fade animated" id="kt_modal_new_card" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-centered" role="document">
        <div class="modal-content text-center">
            <button aria-label="Close" class="close" data-dismiss="modal" type="button" onclick="javascript:window.location.reload()"><span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
            <div class="onboarding-media">
            </div>
            <div class="onboarding-content with-gradient">
                <div class="onboarding-text">
                    <h4 class="text-gray-800 fw-bolder">{{SERVICE_ACCOUNT_ACTIVE_HEADING}}</h4>
                    <div class="fs-6 text-gray-600">
                        <strong>{{SERVICE_ACCOUNT_ACTIVE_DESCRIPTION}}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- End Active Modal -->
@endsection


@section('scripts')
<!-- <script src="{{asset('js/apexcharts.js')}}"></script> -->
<script type="text/javascript">
    $('#transfer_amounts').blur(function() {
        $('#btnAmountTransfer').attr('disabled', 'disabled');
        var amount = $('#transfer_amounts').val();
        var service_id = $('#service_id').val();
        console.log(amount, service_id, 'transfer_amounts');
        if (amount != '' && parseFloat(amount) > 0 && service_id != '') {
            console.log(amount, service_id, 'transfer_amounts', 'disabled');
            $('#btnAmountTransfer').removeAttr('disabled');
        }
    });
    $('#service_id').change(function() {
        $('#btnAmountTransfer').attr('disabled', 'disabled');
        var amount = $('#transfer_amounts').val();
        var service_id = $('#service_id').val();
        console.log(amount, service_id, 'disabled');
        if (amount != '' && parseFloat(amount) > 0 && service_id != '') {
            console.log(amount, service_id, 'service_id', 'disabled');
            $('#btnAmountTransfer').removeAttr('disabled');
        }
    });

    $('#kt_modal_new_card').on('hidden.bs.modal', function() {
        location.reload();
    });

    var primaryBalannceGraph = `{{custom_secure_url('graphs/primary-fund')}}`;
</script>
<script src="{{asset('common.js?v=1.0.0')}}"></script>
<script src="{{asset('user-js/user-home-dashboard.js?v=1.0.0')}}"></script>
@endsection