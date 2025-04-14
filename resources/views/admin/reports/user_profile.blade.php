@extends('layouts.admin.app')
@section('title',ucfirst($page_title))

@section('style')
<style type="text/css">
    .expandtable {
        width: 100% !important;
        margin-bottom: 1rem;
    }

    .expandtable,
    tbody,
    tr,
    td {
        margin-bottom: 1rem;
    }

    .fieldset {
        margin-top: 0rem !important;
    }

    .form-group {
        margin-top: 0rem !important;
    }

    .content-box {
        padding: 1rem !important;
    }

    .element-box {
        padding: 1rem !important;
    }

    #table-user_bank_filter,
    #table-user_bank_length {
        display: none;
    }

    @media screen and (min-width: 767px) {
        #datatable_length {
            margin-top: 0;
        }
    }

    .enable_disable_settings .form-group label:first-child {
        min-width: 112px;
    }
</style>
<link href="{{url('public/css/jquerysctipttop.css')}}" rel="stylesheet" type="text/css">
<link href="{{url('public/css/style.css?v=1.0.0')}}" rel="stylesheet" type="text/css">
@endsection

@section('content')

<div class="content-i">
    <div class="content-box">
        <div class="row">

            <div class="col-sm-5">
                <div class="ecommerce-customer-info">
                    <div class="ecommerce-customer-main-info">
                        @if(isset($userData->avatar))
                        <img alt="" src="{{url('public/uploads/profile/')}}/{{$userData->avatar}}">
                        @else
                        <p data-letters="{{ CommonHelper::shortName($user_id) }}" class="lettersProfile"></p>
                        @endif

                        <div class="ecc-name">
                            @if(isset($userData))
                            {{$userData->name}}
                            @else
                            NA
                            @endif
                        </div>

                    </div>
                    <div class="up-contents">
                        <div class="m-b">
                            <div class="row m-b">
                                <div class="col-sm-6 b-r b-b">
                                    <div class="el-tablo centered padded-v">
                                        <div class="value" style="font-size: 1.3rem;">
                                            @if(isset($userData))
                                            {{ CommonHelper::numberFormat($userData->transaction_amount + $userData->locked_amount) }}
                                            @else
                                            0
                                            @endif
                                        </div>
                                        <div class="label">
                                            <span class="badge badge-primary"> Primary</span>
                                        </div>
                                        <p>
                                            @if(isset($userData))
                                            {{ $userData->account_number }}
                                            @else
                                            NA
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6 b-b">
                                    <div class="el-tablo centered padded-v">
                                        <div class="value" style="font-size: 1.3rem;">
                                            <?php $payoutAmount = 0;
                                            $payoutAccountNo = "";
                                            $payoutAccountPKId = ""; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == PAYOUT_SERVICE_ID)
                                            <?php
                                            $payoutAccountPKId = $userServices['servicePkId'];
                                            $payoutAmount = CommonHelper::numberFormat($userServices['transaction_amount']);
                                            $payoutAccountNo = $userServices['service_account_number'];
                                            ?>
                                            @endif
                                            @endforeach
                                            {{ $payoutAmount }}
                                        </div>
                                        <div class="label">
                                            <?php $payoutName = "Payout Amount"; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == PAYOUT_SERVICE_ID)
                                            <?php $payoutName = $userServices['service_name']; ?>
                                            @else
                                            @endif
                                            @endforeach
                                            <span class="badge badge-success">{{ $payoutName }}</span>
                                        </div>
                                        <p> {{ $payoutAccountNo }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-b">
                                <div class="col-sm-6 b-r b-b">
                                    <div class="el-tablo centered padded-v">
                                        <div class="value" style="font-size: 1.3rem;">
                                            <?php $validateAmount = 0;
                                            $validateAccountNo = "";
                                            $validateAccountPKId = ""; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == VALIDATE_SERVICE_ID)
                                            <?php
                                            $validateAccountPKId = $userServices['servicePkId'];
                                            $validateAmount = CommonHelper::numberFormat($userServices['transaction_amount']);
                                            $validateAccountNo = $userServices['service_account_number'];
                                            ?>
                                            @endif
                                            @endforeach
                                            {{ $validateAmount }}

                                        </div>
                                        <div class="label">
                                            <?php $validateName = "Validate Amount"; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == VALIDATE_SERVICE_ID)
                                            <?php $validateName = $userServices['service_name']; ?>
                                            @else
                                            @endif
                                            @endforeach
                                            <span class="badge badge-success">{{ $validateName }}</span>
                                        </div>
                                        <p> {{ $validateAccountNo }}</p>
                                    </div>
                                </div>
                                <div class="col-sm-6 b-b">
                                    <div class="el-tablo centered padded-v">
                                        <div class="value" style="font-size: 1.3rem;">
                                            <?php $rechargeAmount = 0;
                                            $rechargeAccountNo = "";
                                            $rechargeAccountPKId = ""; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == RECHARGE_SERVICE_ID)
                                            <?php
                                            $rechargeAccountPKId = $userServices['servicePkId'];
                                            $rechargeAmount = CommonHelper::numberFormat($userServices['transaction_amount']);
                                            $rechargeAccountNo = $userServices['service_account_number'];
                                            ?>
                                            @endif
                                            @endforeach
                                            {{ $rechargeAmount }}
                                        </div>
                                        <div class="label">
                                            <?php $rechargeName = "Recharge Amount"; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == RECHARGE_SERVICE_ID)
                                            <?php $rechargeName = $userServices['service_name']; ?>
                                            @else
                                            @endif
                                            @endforeach
                                            <span class="badge badge-success">{{ $rechargeName }}</span>
                                        </div>
                                        <p> {{ $rechargeAccountNo }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-b">
                                <div class="col-sm-6 b-r b-b">
                                    <div class="el-tablo centered padded-v">
                                        <div class="value" style="font-size: 1.3rem;">
                                            <?php $panCardAmount = 0;
                                            $panCardAccountNo = "";
                                            $panCardAccountPKId = ""; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == PAN_CARD_SERVICE_ID)
                                            <?php
                                            $panCardAccountPKId = $userServices['servicePkId'];
                                            $panCardAmount = CommonHelper::numberFormat($userServices['transaction_amount']);
                                            $panCardAccountNo = $userServices['service_account_number'];
                                            ?>
                                            @endif
                                            @endforeach
                                            {{ $panCardAmount }}
                                        </div>
                                        <div class="label">
                                            <?php $panCardName = "Pan Card Amount"; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == PAN_CARD_SERVICE_ID)
                                            <?php $panCardName = $userServices['service_name']; ?>
                                            @else
                                            @endif
                                            @endforeach
                                            <span class="badge badge-success">{{ $panCardName }}</span>
                                        </div>
                                        <p> {{ $panCardAccountNo }}</p>
                                    </div>
                                </div>
                                <div class="col-sm-6 b-b">
                                    <div class="el-tablo centered padded-v">
                                        <div class="value" style="font-size: 1.3rem;">
                                            <?php $dmtAmount = 0;
                                            $dmtAccountNo = "";
                                            $dmtAccountPKId = ""; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == DMT_SERVICE_ID)
                                            <?php
                                            $dmtAccountPKId = $userServices['servicePkId'];
                                            $dmtAmount = CommonHelper::numberFormat($userServices['transaction_amount']);
                                            $dmtAccountNo = $userServices['service_account_number'];
                                            ?>
                                            @endif
                                            @endforeach
                                            {{ $dmtAmount }}
                                        </div>
                                        <div class="label">
                                            <?php $dmtName = "DMT Amount"; ?>
                                            @foreach ($userService as $userServices)
                                            @if($userServices['service_id'] == DMT_SERVICE_ID)
                                            <?php $dmtName = $userServices['service_name']; ?>
                                            @else
                                            @endif
                                            @endforeach
                                            <span class="badge badge-success">{{ $dmtName }}</span>
                                        </div>
                                        <p> {{ $dmtAccountNo }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="padded">
                                @if(isset($userData))
                                <div class="os-progress-bar primary">

                                    @php
                                    $valuenow = 0;
                                    $bg = "danger";

                                    @endphp
                                    @if ($userData->is_profile_updated == '1')
                                    @php
                                    $valuenow += 33;
                                    $bg = "primary";

                                    @endphp
                                    @endif
                                    @if (isset($userBusinessProfile->is_kyc_updated)&&$userBusinessProfile->is_kyc_updated == '1')
                                    @php
                                    $valuenow += 33;
                                    $bg = "info";
                                    @endphp
                                    @endif
                                    @if (isset($userBusinessProfile->is_bank_updated)&&$userBusinessProfile->is_bank_updated == '1')
                                    @php
                                    $valuenow += 34;
                                    $bg = "success";
                                    @endphp
                                    @endif
                                </div>

                                <div class="bar-labels">
                                    <div class="bar-label-left">
                                        <span><b>
                                                <h6>Profile Completion</h6>
                                            </b></span><span class="positive"></span>
                                    </div>
                                </div><br />
                                <div class="progress">
                                    <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{$valuenow}}" class="progress-bar progress-bar-striped bg-{{$bg}}" role="progressbar" style="width: {{$valuenow}}%">
                                        @if($bg == 'danger')
                                        Any Profile Not Updated.
                                        @else
                                        {{ $valuenow }}%
                                        @endif
                                    </div>
                                </div> <br />

                                <div class="el-legend">
                                    <div class="legend-value-w">
                                        <div class="legend-pin" @if($userData->is_profile_updated == '1')
                                            style="background-color: #85c751;"
                                            @else
                                            style="background-color: #d97b70;"
                                            @endif
                                            >
                                        </div>
                                        <div class="legend-value">Personal Info</div>
                                    </div>
                                    <div class="legend-value-w">
                                        <div class="legend-pin" @if((isset($userBusinessProfile->is_kyc_updated))&&($userBusinessProfile->is_kyc_updated == '1' || $userBusinessProfile->is_kyc_updated == '2'))
                                            style="background-color: #85c751;"
                                            @else
                                            style="background-color: #d97b70;"
                                            @endif></div>
                                        <div class="legend-value">Business Info</div>
                                    </div>
                                    <div class="legend-value-w">
                                        <div class="legend-pin" @if(isset($userBusinessProfile->is_bank_updated)&&$userBusinessProfile->is_bank_updated == '1')
                                            style="background-color: #85c751;"
                                            @else
                                            style="background-color: #d97b70;"
                                            @endif></div>
                                        <div class="legend-value">Bank Info</div>
                                    </div>
                                    @endif
                                    <div class="legend-value-w">

                                    </div>
                                </div>
                                <br />
                                <br />
                                <div class="bar-labels">
                                    <div class="bar-label-left">
                                        <span>
                                            <b>
                                                <h6>Service List</h6>
                                            </b>
                                        </span>
                                        <span class="positive">
                                        </span>
                                    </div>
                                </div>
                                @if(isset($userService) && !empty($userService))
                                <div class="table-responsive">
                                    <table class="table table-lightborder">

                                        <tbody>
                                            @foreach($userService as $userServices)
                                            <tr>
                                                <td class="nowrap"> @if($userServices['service_active_check'] == '1')
                                                    <div class="status-pill green" data-title="Complete" data-toggle="tooltip" data-original-title="" title="">
                                                    </div>
                                                    @else
                                                    <div class="status-pill yellow" data-title="InActive" data-toggle="tooltip" data-original-title="" title="">
                                                    </div>
                                                    @endif {{ $userServices['service_name']}}
                                                </td>

                                            </tr>
                                            <tr>
                                                <td class="nowrap">
                                                    <div class="col-lg-6" style="width: 610px;">
                                                        @if($userServices['service_id'] == PAYOUT_SERVICE_ID)
                                                        <input type="text" class="justAnotherInputBoxPayout" placeholder="Type to filter" autocomplete="off" />
                                                        @elseif($userServices['service_id'] == AEPS_SERVICE_ID)
                                                        WEB : <input type="text" class="justAnotherInputBoxAEPS" placeholder="Type to filter" autocomplete="off" /></br>
                                                        API : <input type="text" class="justAnotherInputBoxAEPS1" placeholder="Type to filter" autocomplete="off" />
                                                        @elseif($userServices['service_id'] == UPI_SERVICE_ID)
                                                        <input type="text" class="justAnotherInputBoxUPI" placeholder="Type to filter" autocomplete="off" />
                                                        @elseif($userServices['service_id'] == AUTO_COLLECT_SERVICE_ID)
                                                        <input type="text" class="justAnotherInputBoxSmartCollect" placeholder="Type to filter" autocomplete="off" />
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @if (isset($userData))
                                            <input type="hidden" name="userIds" id="userIds" value="{{encrypt($userData->id)}}" />

                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="col text-center" style="padding-bottom: 20px">
                                        <button type="submit" class="btn btn-primary text-right" id="updateApiValue">Update</button>
                                    </div>
                                </div>
                                @endif
                                <div class="w-100 border"></div><br />
                                <div class="bar-labels mt-1">
                                    <div class="bar-label-left">
                                        <span>
                                            <h6>Transfer Amount (Main To Payout)</h6>
                                        </span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <form role="transferAmount" action="{{url('admin/accounts/admin-transfer-amount')}}" method="post">
                                        @csrf
                                        <div class="row">
                                            @if(isset($userData))
                                            <input type="hidden" name="user_id" value="{{ encrypt($userData->id) }}" />
                                            @endif
                                            <input type="hidden" name="transfer_by" value="{{ encrypt(Auth::user()->id) }}" />
                                            <input type="hidden" name="service_id" value="{{ $payoutAccountPKId}}" />
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="">Amount *</label>
                                                    <input class="form-control" placeholder="Amount" name="transfer_amount" type="number">
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="">Remarks *</label>
                                                    <textarea class="form-control" name="remarks"></textarea>
                                                </div>
                                                <div class="col text-center">
                                                    <button type="submit" class="btn btn-primary text-right" data-request="ajax-submit" data-target='[role="transferAmount"]'>Send</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <br>
                                <div class="w-100 border"></div><br />
                                <div class="bar-labels mt-1">
                                    <div class="bar-label-left">
                                        <span>
                                            <h6>Claim Back (Payout To Main)</h6>
                                        </span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <form role="claimback" action="{{url('admin/accounts/claimback')}}" method="post">
                                        @csrf
                                        <div class="row">
                                            @if(isset($userData))
                                            <input type="hidden" name="user_id" value="{{encrypt($userData->id)}}" />
                                            @endif
                                            <input type="hidden" name="claim_by" value="{{encrypt(Auth::user()->id)}}" />
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="">Service Account <span class="requiredstar">*</span></label>
                                                    <select name="service_account" class="form-control">
                                                        <option value="">Account Number</option>
                                                        @foreach($userService as $userServices)
                                                        @if($userServices['service_id'] == PAYOUT_SERVICE_ID)
                                                        <option value="{{encrypt($userServices['servicePkId'])}}">{{$userServices['service_account_number']}}</option>
                                                        @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="">Amount *</label>
                                                    <input class="form-control" placeholder="Amount" name="amount" type="number">
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="">Remarks *</label>
                                                    <textarea class="form-control" name="remarks"></textarea>
                                                </div>
                                                <div class="col text-center">
                                                    <button type="submit" class="btn btn-primary text-right" data-request="ajax-submit" data-target='[role="claimback"]'>Claim Back</button>
                                                </div>
                                            </div>



                                        </div>



                                    </form>
                                </div>

                                <br>
                                <div class="w-100 border"></div><br />
                                <div class="bar-labels mt-1">
                                    <div class="bar-label-left">
                                        <span>
                                            <h6>Threshold Amount Set</h6>
                                        </span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <form role="threshold" action="{{url('admin/accounts/threshold')}}" method="post">
                                        @csrf
                                        <div class="row">
                                            @if(isset($userData))
                                            <input type="hidden" name="user_id" value="{{encrypt($userData->id)}}" />
                                            @endif
                                            <input type="hidden" name="created_by" value="{{encrypt(Auth::user()->id)}}" />

                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="">Amount *</label>
                                                    <input class="form-control" placeholder="Amount" name="threshold_amount" @if(isset($userConfig->threshold)) value="{{$userConfig->threshold}}" @endif type="number">
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="col text-center">
                                                    <button type="submit" class="btn btn-primary text-right" data-request="ajax-submit" data-target='[role="threshold"]'>Threshold Update</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <br>

                                <div class="w-100 border"></div>

                                <div class="bar-labels mt-1">
                                    <div class="bar-label-left">
                                        <span>
                                            <h6>Partner VAN Info</h6>
                                        </span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-lightborder">
                                        <tbody>
                                            @if(!empty($obVanAccount))
                                            <tr>
                                                <td class="nowrap">Account Number :</td>
                                                <td>
                                                    {{$obVanAccount->account_number}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="nowrap">IFSC :</td>
                                                <td>
                                                    {{$obVanAccount->ifsc}}
                                                </td>
                                            </tr>

                                            @else
                                            <tr>
                                                <td colspan="2">
                                                    <div>
                                                        @if(isset($userData))
                                                        <button class="btn btn-link" id="generate-van-ob" data-user="{{encrypt($userData->id)}}">Generate VAN (OpenBank)</button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>


                                <div class="w-100 border"></div>

                                <div class="bar-labels mt-1">
                                    <div class="bar-label-left">
                                        <span>
                                            <h6>Lean Mark</h6>
                                        </span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-lightborder">
                                        <tbody>

                                            <tr>
                                                <td class="nowrap">
                                                    <div>Primary Balance: &nbsp;
                                                        @if($userData)
                                                        <strong>Rs. {{number_format($userData->transaction_amount, 2)}}</strong>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="nowrap">
                                                    <div class="pb-1">Enter Amount *</div>
                                                    <input class="w-100 form-control" type="number" id="lean_amt">
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-center">
                                                    <button class="btn btn-primary" data-user="{{encrypt($userData->id)}}" id="btn_lean_amt_update">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="w-100 border"></div>

                                <div class="bar-labels mt-1">
                                    <div class="bar-label-left">
                                        <span>
                                            <h6>Reseller</h6>
                                        </span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <form id="reseller_form" role="reseller_update" method="POST" action="{{url('admin/update-reseller/'.$userData->id )}}">
                                        @csrf
                                        <table class="table table-lightborder">
                                            <tbody>
                                                <tr>
                                                    <td class="nowrap">
                                                        <div class="col-sm-12">
                                                            <div class="form-group">
                                                                <label for=""> Reseller <span class="requiredstar">*</span></label>
                                                                <select name="reseller" id="resellers_data" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Reseller...">
                                                                    <option value="">Select a Reseller...</option>
                                                                    @foreach($resellers as $reseller)
                                                                        <option value="{{ $reseller->id }}" @if(isset($userData) && $userData->reseller == $reseller->id) selected @endif>
                                                                            {{ $reseller->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">
                                                        <button class="btn btn-primary" data-request="ajax-submit" data-target='[role="reseller_update"]' type="submit" id="btn_reseller_update">Reseller Update</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-sm-7">

                <div class="element-wrapper xtl_tab_content">
                    <div class="element-box-tp">
                        <div class="os-tabs-w">

                            <div class="os-tabs-controls">
                                <ul class="nav nav-tabs smaller searchby">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#profile">Profile</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#bank_info">Bank Info</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#upi_data">UPI Data</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#settings">Settings</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#lean_mark">Lean Mark</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content">

                                <!-- PROFILE -->
                                <div class="tab-pane active" id="profile">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="element-box">
                                                <div class="element-info">
                                                    <div class="element-info-with-icon">
                                                        <div class="element-info-icon">
                                                            <div class="os-icon os-icon-user-male-circle2"></div>
                                                        </div>
                                                        <div class="element-info-text">
                                                            <h5 class="element-inner-header">
                                                                Profile
                                                            </h5>

                                                        </div>
                                                    </div>
                                                </div>
                                                <form role="update-profile" action="{{url('admin/user/'.$userData->id)}}" method="post">
                                                    <fieldset class="form-group">
                                                        <div class="row">
                                                            <input type="hidden" name="user_id" value="{{$userData->id}}" />
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for=""> Name</label>
                                                                    <input class="form-control" placeholder="Enter Name" readonly="readonly" value="{{$userData->name}}">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="">Mobile *</label>
                                                                    <input class="form-control" placeholder="Phone number" readonly="readonly" value="{{$userData->mobile}}">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for=""> Email</label>
                                                                    <input class="form-control" placeholder="Enter Email" readonly="readonly" value="{{$userData->email}}">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for=""> Email verified at</label>
                                                                    @if(!empty($userData->email_verified_at))
                                                                    <input class="form-control" placeholder="Enter Email" readonly="readonly" value="{{$userData->email_verified_at}}">
                                                                    @else
                                                                    <input class="form-control" placeholder="Enter Email" readonly="readonly" value="Not yet">
                                                                    @endif
                                                                </div>
                                                            </div>


                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="">Sign-Up Status</label>
                                                                    <input class="form-control" placeholder="Phone number" readonly="readonly" value="{{$userData->signup_status}}">
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </fieldset>

                                                    <fieldset class="form-group">

                                                        <fieldset class="form-group">
                                                            <legend><span>Business Profile</span></legend>

                                                            <div class="row">

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for="">Business/Company Name <span class="requiredstar">*</span></label>
                                                                        <input type="text" readonly="readonly" value="{{@$userBusinessProfile->business_name}}" class="form-control" placeholder="Business/Company Name" required="required">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for="">Business/Company GSTIN <span class="requiredstar">*</span></label>
                                                                        <input type="text" readonly="readonly" value="{{@$userBusinessProfile->gstin}}" class="form-control" placeholder="Business/Company Name" required="required">
                                                                    </div>
                                                                </div>


                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for="">Business PAN <span class="requiredstar">*</span></label>
                                                                        <input type="text" readonly="readonly" value="{{@$userBusinessProfile->business_pan}}" class="form-control" required="required">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for="">Business Name as PAN <span class="requiredstar">*</span></label>
                                                                        <input type="text" readonly="readonly" value="{{@$userBusinessProfile->business_name_from_pan}}" class="form-control" required="required">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for=""> Authorised Signatory PAN <span class="requiredstar">*</span></label>
                                                                        <input class="form-control" readonly="readonly" value="{{@$userBusinessProfile->pan_number}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for=""> Authorised Signatory Name <span class="requiredstar">*</span></label>
                                                                        <input class="form-control" readonly="readonly" value="{{@$userBusinessProfile->pan_owner_name}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for=""> Authorised Signatory AADHAAR <span class="requiredstar">*</span></label>
                                                                        <input class="form-control" readonly="readonly" value="{{@$userBusinessProfile->aadhar_number}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for=""> Authorised Signatory AADHAAR Name <span class="requiredstar">*</span></label>
                                                                        <input class="form-control" readonly="readonly" value="{{@$userBusinessProfile->aadhaar_name}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for="">Authorised Signatory Name Matched (%) <span class="requiredstar">*</span></label>
                                                                        <input type="text" readonly="readonly" value="{{@$userBusinessProfile->owner_match_percentage}}" class="form-control" required="required">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </fieldset>


                                                        <fieldset class="form-group">
                                                            <legend><span>Business Overview</span></legend>

                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for=""> Business Type <span class="requiredstar">*</span></label>
                                                                        <select name="business_type" disabled="disabled" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Business Type..." class="form-select form-select-solid" @if(isset($userBusinessProfile) && $userBusinessProfile->is_kyc_updated == '1')
                                                                            disabled="disabled"
                                                                            @endif
                                                                            >
                                                                            <option value="">Select a Business Type...</option>
                                                                            <option value="Proprietorship" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="Proprietorship")
                                                                                selected
                                                                                @endif
                                                                                @endif>Proprietorship</option>
                                                                            <option value="Partnership" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="Partnership")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >Partnership</option>
                                                                            <option value="Private Limited" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="Private Limited")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >Private Limited</option>
                                                                            <option value="Public Limited" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="Public Limited")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >Public Limited</option>
                                                                            <option value="LLP" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="LLP")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >LLP</option>
                                                                            <option value="Private Limited" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="Private Limited")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >Private Limited</option>
                                                                            <option value="Trust" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="Trust")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >Trust</option>
                                                                            <option value="Society" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="Society")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >Society</option>
                                                                            <option value="NGO" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="NGO")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >NGO</option>
                                                                            <option value="Not Registered" @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_type=="Not Registered")
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >Not Registered</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for="">Business Category <span class="requiredstar">*</span></label>
                                                                        <select name="business_category" disabled="disabled" class="form-control" id="business_category_id" onchange="businessCategory(this);" data-control="select2" data-hide-search="true" data-placeholder="Select a Category..." class="form-select form-select-solid" @if(isset($userBusinessProfile) && $userBusinessProfile->is_kyc_updated == '1')
                                                                            disabled="disabled"
                                                                            @endif>
                                                                            <option value="">Select a Business Category...</option>
                                                                            @foreach($business_category as $business_category)
                                                                            <option @if(isset($userBusinessProfile)) @if($userBusinessProfile->business_category_id==$business_category->id)
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                value="{{$business_category->id}}">{{$business_category->name}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <div class="form-group">
                                                                        <div class="form-group">
                                                                            <label for="">Business Description <span class="requiredstar">*</span></label>
                                                                            <textarea class="form-control" readonly="readonly">{{@$userBusinessProfile->business_description}}</textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-12">
                                                                    <div class="form-group">
                                                                        <div class="form-group">
                                                                            <label for="">Address <span class="requiredstar">*</span></label>
                                                                            <textarea class="form-control" readonly="readonly">{{@$userBusinessProfile->address}}</textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for=""> Pincode <span class="requiredstar">*</span></label>
                                                                        <input class="form-control" readonly="readonly" placeholder="Pin code" value="{{@$userBusinessProfile->pincode}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <div class="form-group">
                                                                            <label for="">City <span class="requiredstar">*</span></label>
                                                                            <input class="form-control" readonly="readonly" value="{{@$userBusinessProfile->city}}" placeholder="City">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label for=""> State <span class="requiredstar">*</span></label>
                                                                        <select name="state" class="form-control" data-control="select2" disabled data-hide-search="true" data-placeholder="Select a State..." class="form-select form-select-solid" @if(isset($userBusinessProfile) && $userBusinessProfile->is_kyc_updated == '1') disabled @endif>
                                                                            <option value="">Select a State...</option>
                                                                            @foreach($state_list as $state_list)
                                                                            <option value="{{$state_list->id}}" @if(isset($userBusinessProfile)) @if($userBusinessProfile->state==$state_list->id)
                                                                                selected
                                                                                @endif
                                                                                @endif
                                                                                >{{$state_list->state_name}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <div class="form-group">
                                                                        <label for="">Website URL</label>
                                                                        @if(!empty($userBusinessProfile->web_url))
                                                                        <div class="form-control"><a href="{{@$userBusinessProfile->web_url}}" target="_blank">{{@$userBusinessProfile->web_url}}</a></div>
                                                                        @else
                                                                        <div class="form-control">Not Provided</div>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-12">
                                                                    <div class="form-group">
                                                                        <label for="">App URL</label>
                                                                        @if(!empty($userBusinessProfile->app_url))
                                                                        <div class="form-control"><a href="{{@$userBusinessProfile->app_url}}" target="_blank">{{@$userBusinessProfile->app_url}}</a></div>
                                                                        @else
                                                                        <div class="form-control">Not Provided</div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>


                                                        </fieldset>

                                                        <fieldset class="form-group">
                                                            <legend><span>Bank Details</span></legend>

                                                            @if($userBankInfos->isNotEmpty())

                                                            <div class="row">

                                                                @foreach($userBankInfos as $row)

                                                                <div class="col-md-6 xttl-card mb-3">
                                                                    <div class="card" style="max-width:400px">
                                                                        <div class="card-body">
                                                                            <h5 class="card-title">
                                                                                @if($row->is_primary == '1')
                                                                                <span class="text-success">Primary Account</span>
                                                                                @else
                                                                                Bank Info
                                                                                @endif
                                                                            </h5>
                                                                            <p class="card-text">
                                                                            <table class="table table-striped xttl-table">
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <th colspan="2">Beneficiary Name</th>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td colspan="2">{{$row->beneficiary_name}}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th colspan="2">Account Number</th>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td colspan="2">{{$row->account_number}}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>IFSC</th>
                                                                                        <td>{{$row->ifsc}}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Status</th>
                                                                                        <td>
                                                                                            @if($row->is_active == '1')
                                                                                            <span class="badge badge-success">Active</span>
                                                                                            @else
                                                                                            <span class="badge badge-secondary">In-Active</span>
                                                                                            @endif
                                                                                        </td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <th>Verified</th>
                                                                                        <td>
                                                                                            @if($row->is_verified == '1')
                                                                                            <span class="badge badge-success">Verified</span>
                                                                                            @else
                                                                                            <span class="badge badge-secondary">Not-Verified</span>
                                                                                            @endif
                                                                                        </td>
                                                                                    </tr>

                                                                                </tbody>
                                                                            </table>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                @endforeach

                                                            </div>

                                                            @endif

                                                        </fieldset>
                                                    </fieldset>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- BANK INFO -->
                                <div class="tab-pane" id="bank_info">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            @if(auth()->user()->hasRole('super-admin'))

                                            <!-- User Banks Info -->
                                            <div class="element-box">
                                                <div class="element-info">
                                                    <div class="element-info-text">
                                                        <h5 class="element-inner-header">
                                                            User Bank Info

                                                            <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addBankAccountModal">
                                                                Add New
                                                            </button>
                                                        </h5>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-striped table-hover w-100" id="table-user_bank">
                                                                <thead>
                                                                    <tr>
                                                                        <th>S.N.</th>
                                                                        <th>Beneficiary Name</th>
                                                                        <th>Account Info</th>
                                                                        <th>Is Primary</th>
                                                                        <th>Status</th>
                                                                        <th>Verified</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- UPI DATA -->
                                <div class="tab-pane" id="upi_data">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="element-box">

                                                <div class="element-info">
                                                    <div class="element-info-with-icon">
                                                        <div class="element-info-text">
                                                            <h5 class="element-inner-header">
                                                                UPI Data
                                                            </h5>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div>
                                                    <table class="table table-bordered table-striped table-hover dataTable no-footer w-100">
                                                        <thead>
                                                            <th>UPI Callback Amount</th>
                                                            <th>UPI Collect Amount</th>
                                                            <th>Action</th>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>{{$upiCallbackData->totAmount}}</td>
                                                                <td>{{$upiCollectData->totAmount}}</td>
                                                                <td><a href="#" data-toggle="modal" data-target="#kt_modal_batch_import"><i class="os-icon os-icon-tasks-checked"></i></a></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div>
                                                    <table class="table table-bordered table-striped table-hover dataTable no-footer w-100" id="datatable">
                                                        <thead>
                                                            <th>Invoice Id</th>
                                                            <th>Service Id</th>
                                                            <th>Fee </th>
                                                            <th>Amount</th>
                                                            <th>Start Date</th>
                                                            <th>End Date</th>
                                                            <th>Created</th>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- USER SETTINGS -->
                                <div class="tab-pane" id="settings">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="element-box">

                                                <div class="element-info">
                                                    <div class="element-info-with-icon">
                                                        <div class="element-info-icon">
                                                            <div class="os-icon os-icon-ui-46"></div>
                                                        </div>
                                                        <div class="element-info-text">
                                                            <h5 class="element-inner-header">
                                                                Enable/Disable Settings
                                                            </h5>

                                                        </div>
                                                    </div>
                                                </div>
                                                <fieldset class="form-group">

                                                    <div class="row enable_disable_settings">
                                                        @if(isset($userConfig->is_sdk_enable))
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="" style="margin-right : 20px;"> AEPS SDK : </label>
                                                                @if($userConfig->is_sdk_enable === '1')
                                                                <label class="switch" id="change-is_sdk_enable-status" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox" checked><span class="slider round"></span></label>
                                                                @else
                                                                <label class="switch" id="change-is_sdk_enable-status" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox"><span class="slider round"></span></label>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if(isset($userConfig->load_money_request))
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="" style="margin-right : 20px;"> Load Money : </label>
                                                                @if($userConfig->load_money_request === '1')
                                                                <label class="switch" id="change-load_money_request-status" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox" checked><span class="slider round"></span></label>
                                                                @else
                                                                <label class="switch" id="change-load_money_request-status" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox"><span class="slider round"></span></label>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if(isset($userConfig->is_internal_transfer_enable))
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="" style="margin-right : 20px;"> Internal Transfer : </label>
                                                                @if($userConfig->is_internal_transfer_enable === '1')
                                                                <label class="switch" id="change-internal_transfer-status" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox" checked><span class="slider round"></span></label>
                                                                @else
                                                                <label class="switch" id="change-internal_transfer-status" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox"><span class="slider round"></span></label>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if(isset($userConfig->is_auto_settlement))
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="" style="margin-right : 20px;"> Auto Settlement : </label>
                                                                @if($userConfig->is_auto_settlement === '1')
                                                                <label class="switch" id="change-is_auto_settlement" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox" checked><span class="slider round"></span></label>
                                                                @else
                                                                <label class="switch" id="change-is_auto_settlement" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox"><span class="slider round"></span></label>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endif


                                                        @if(isset($userConfig->is_matm_enable))
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="" style="margin-right : 20px;"> MATM SDK : </label>
                                                                @if($userConfig->is_matm_enable === '1')
                                                                <label class="switch" id="change-is_matm_enable-status" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox" checked><span class="slider round"></span></label>
                                                                @else
                                                                <label class="switch" id="change-is_matm_enable-status" data-user="{{encrypt($userConfig->user_id)}}"><input type="checkbox"><span class="slider round"></span></label>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>


                                                    @if(isset($userService) && !empty($userService))
                                                    <div class="table-responsive">
                                                        <table class="table table-lightborder">
                                                            <thead>
                                                                <tr>
                                                                    <th>Service Name</th>
                                                                    <th>Web</th>
                                                                    <th>Api</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($userService as $userServices)
                                                                <tr>
                                                                    <td class="nowrap">
                                                                        <b>{{ $userServices['service_name']}}</b>
                                                                    </td>
                                                                    <td>
                                                                        <?php $userId = $userServices['servicePkId']; ?>
                                                                        @if ($userServices['is_web_enable'] == '1')
                                                                        <label class="switch" onChange="webServiceUpdate(<?php echo $userId; ?>)"><input type="checkbox" checked><span class="slider round"></span></label>
                                                                        @else
                                                                        <label class="switch" onChange="webServiceUpdate(<?php echo $userId; ?>)"><input type="checkbox"><span class="slider round"></span></label>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if ($userServices['is_api_enable'] == '1')
                                                                        <label class="switch" onChange="apiServiceUpdate(<?php echo $userId; ?>)"><input type="checkbox" checked><span class="slider round"></span></label>
                                                                        @else
                                                                        <label class="switch" onChange="apiServiceUpdate(<?php echo $userId; ?>)"><input type="checkbox"><span class="slider round"></span></label>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if ($userServices['service_active_check'] == '1')
                                                                        <label class="switch" onChange="statusUpdate(<?php echo $userId; ?>)"><input type="checkbox" checked><span class="slider round"></span></label>
                                                                        @else
                                                                        <label class="switch" onChange="statusUpdate(<?php echo $userId; ?>)"><input type="checkbox"><span class="slider round"></span></label>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    @endif
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="element-box">
                                                <div class="element-info">
                                                    <div class="element-info-with-icon">
                                                        <div class="element-info-icon">
                                                            <div class="os-icon os-icon-ui-46"></div>
                                                        </div>
                                                        <div class="element-info-text">
                                                            <h5 class="element-inner-header">
                                                                Service Integration Settings
                                                            </h5>

                                                        </div>
                                                    </div>
                                                </div>
                                                <fieldset class="form-group">
                                                    <form class="" method="post" role="upi_collect_integration" action="{{url('admin/user/upi_collect/update_integration')}}">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{$userData->id}}" />
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label>UPI Collect</label>
                                                                    <select class="form-control" name="integration_id">
                                                                        <option value="">Select Integration</option>
                                                                        <option value="int_1702294368" @if($userConfig->upi_collect_integration_id == 'int_1702294368') {{'selected'}}@endif>IBR PAY</option>
                                                                        <option value="int_1702294454" @if($userConfig->upi_collect_integration_id == 'int_1702294454') {{'selected'}}@endif>INDIC PAY</option>
                                                                        <option value="int_1702490356" @if($userConfig->upi_collect_integration_id == 'int_1702490356') {{'selected'}}@endif>Aadhar ATM</option>
                                                                        <option value="int_1702712555" @if($userConfig->upi_collect_integration_id == 'int_1702712555') {{'selected'}}@endif>HunTood</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col text-center">
                                                                    <button type="submit" class="btn btn-primary text-right" data-request="ajax-submit" data-target="[role=&quot;upi_collect_integration&quot;]">Update</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LEAN MARK -->
                                <div class="tab-pane" id="lean_mark">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="element-box">
                                                <div class="element-info">
                                                    <div class="element-info-text">
                                                        <h5 class="element-inner-header">
                                                            Lean Mark Transactions
                                                            <small class="text-muted">(Dr and Cr funds from Primary Wallet)</small>
                                                        </h5>
                                                    </div>
                                                </div>

                                                <div class="row">

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Lean Marked Amt (Dr): </label>
                                                            <label><span class="text-danger font-weight-bolder" id="drLeanAmt"></span></label>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Released Amt (Cr): </label>
                                                            <label><span class="text-success font-weight-bolder" id="crLeanAmt"> </span></label>
                                                        </div>
                                                    </div>

                                                </div>


                                                <div class="row">

                                                    <div class="col-12">

                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-striped table-hover w-100" id="dt-lean-mark">
                                                                <thead>
                                                                    <tr>
                                                                        <th></th>
                                                                        <th>TXN Type</th>
                                                                        <th>Amount</th>
                                                                        <th>Opening</th>
                                                                        <th>Closing</th>
                                                                        <th>Transaction Date</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>

                                                    </div>

                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="onboarding-modal modal fade animated" id="kt_modal_batch_import" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog ">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header" style="border: 1px solid #e9ecef" id="kt_modal_create_api_key_header">
                    <!--begin::Modal title-->
                    <h5>UPI Fee</h5>
                    <!--end::Modal title-->

                    <!--begin::Close-->
                    <button aria-label="Close" onclick="javascript:window.location.reload()" class="close" data-dismiss="modal" type="button">
                        <span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Form-->
                <form id="kt_modal_create_api_ip_form" class="form" method="post" role="update-ip" action="{{url('admin/deductUpiFee')}}" enctype="multipart/form-data">
                    @csrf
                    <!--begin::Modal body-->
                    <div class="modal-body py-10 px-lg-17">
                        <!--begin::Scroll-->
                        <div class="form-group">
                            <label for="">Start Date <span class="requiredstar"></span></label>
                            <input type="date" name="fromDate" id="fromDate" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}" @endif />
                        </div>
                        <div class="form-group">
                            <label for="">End Date <span class="requiredstar"></span></label>
                            <input type="date" name="toDate" id="toDate" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}" @endif />
                        </div>
                        <div class="form-group">
                            <label for=""> Fee % </label>

                            <input type="hidden" id="user_id" name="user_id" value="{{$user_id}}">
                            <input type="number" class="form-control" name="upiFee" id="upiFee">
                        </div>

                    </div>
                    <!--end::Modal body-->
                    <!--begin::Modal footer-->
                    <div class="modal-footer flex-center">
                        <!--begin::Button-->

                        <button type="button" id="cal_amount" class="btn btn-primary">
                            Calculate
                        </button>
                        <!--end::Button-->
                    </div>
                    <!--end::Modal footer-->
                </form>
                <div class="modal-body py-10 px-lg-17">
                    <div class="form-group" id="amt_data">

                    </div>
                </div>
                <!--end::Form-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <div class="onboarding-modal modal fade animated" id="kt_modal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog ">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header" style="border: 1px solid #e9ecef" id="kt_modal_header">
                    <!--begin::Modal title-->
                    <h5>UPI Fee</h5>
                    <!--end::Modal title-->

                    <!--begin::Close-->
                    <button aria-label="Close" onclick="javascript:window.location.reload()" class="close" data-dismiss="modal" type="button">
                        <span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Form-->
                <form id="kt_modal_form" class="form" method="post" role="update-fee" action="{{url('admin/deductUpiFee')}}" enctype="multipart/form-data">
                    @csrf
                    <!--begin::Modal body-->
                    <div class="modal-body py-10 px-lg-17">
                        <!--begin::Scroll-->
                        <div class="form-group">
                            <label for="">Start Date: <span class="requiredstar"></span></label>
                            <input class="form-control" type="text" readonly id="fee_from_date" value="" name="fee_from_date">
                            <label for="">End Date: <span class="requiredstar"></span></label>
                            <input class="form-control" type="text" readonly id="fee_to_date" value="" name="fee_to_date">
                            <input type="hidden" id="fee_percentage" value="" name="fee_percentage">
                            <input type="hidden" id="" name="user_id" value="{{$user_id}}">
                        </div>
                        <div class="form-group">
                            <label for=""> Fee: <span id="fee"></span></label>
                            <input class="form-control" type="text" readonly id="fee_amount" value="" name="fee_amount">

                        </div>
                        <div class="form-group">
                            <label for=""> Amount: </label>
                            <input class="form-control" type="text" readonly id="upi_amount" value="" name="upi_amount">

                        </div>

                    </div>
                    <!--end::Modal body-->
                    <!--begin::Modal footer-->
                    <div class="modal-footer flex-center">
                        <!--begin::Button-->



                        <button type="button" data-request="ajax-submit" data-target="[role=&quot;update-fee&quot;]" data-targetform="kt_modal_form" id="confirm_amount" class="btn btn-primary">
                            Confirm
                        </button>
                        <!--end::Button-->
                    </div>
                    <!--end::Modal footer-->
                </form>
                <div class="modal-body py-10 px-lg-17">
                    <div class="form-group" id="amt_data">

                    </div>
                </div>
                <!--end::Form-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
</div>

@if(auth()->user()->hasRole('super-admin'))

<div aria-hidden="true" aria-labelledby="verify-bank-account" class="modal fade" id="verifyBankAccountModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verify-bank-account">
                    Verify Bank Account
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 d-none" id="response-bank-verify">
                        <h6>Bank Account Details:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <tbody></tbody>
                            </table>
                        </div>

                        <form id="verify-bank-form-final" role="verify-bank-form-final" action="{{url('admin/users-bank/verify-bank-account/final')}}" data-DataTables="datatable" method="POST">
                            <fieldset class="form-group">
                                @csrf()
                                <div class="col-md-12">
                                    <div class="col-md-12 text-right">
                                        <input type="hidden" name="acc_holder_name" id="acc_holder_name">
                                        <input type="hidden" name="acc_id" id="user_bank_id_final">
                                        <input type="hidden" name="acc_token" id="acc_token_final">
                                        <button type="submit" name="action_1" value="success" class="btn btn-success" data-request="ajax-submit" data-target='[role="verify-bank-form-final"]' data-callbackfn="verifyBankAccountModal">
                                            <b><i class="icon-search4"></i></b> Verify Account
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-danger text-light d-none" id="response-bank-error"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form id="verify-bank-form" role="verify-bank-form" action="{{custom_secure_url('admin/users-bank/verify-bank-account')}}" method="POST">
                    @csrf()
                    <input class="form-control" type="hidden" name="user_bank_id" id="user_bank_id">
                    <input class="btn btn-primary" type="submit" data-callbackfn="callbackFunction" data-request="ajax-submit" data-target='[role="verify-bank-form"]' value="Click to Verify">
                </form>
            </div>

        </div>
    </div>
</div>

<!-- delete bank info -->
<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="deleteBankInfoModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Delete Bank Info
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form role="delete-bank-info-form" action="{{url('admin/users-bank/delete/bank-info')}}" data-DataTables="table-user_bank" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label> Are you sure to delete this Bank Info?</label>
                        <input class="form-control" type="hidden" name="bank_id" id="bank_id" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-danger" id="delete-bank-info-btn" type="submit" data-request="ajax-submit" data-target='[role="delete-bank-info-form"]' value="Delete" />
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Bank Info -->
<div aria-hidden="true" class="modal fade" id="addBankAccountModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verify-bank-account">
                    Add Bank Account
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>

            <form role="new-bank-form-modal" action="{{url('admin/users-bank/add-new-banks')}}" data-DataTables="table-user_bank" method="POST">
                <div class="modal-body">

                    <div class="row">

                        @csrf()
                        <input type="hidden" name="user_id" value="{{encrypt($user_id)}}">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Beneficiary Name <span class="requiredstar">*</span></label>
                                <input type="text" name="beneficiary_name[0]" id="new_beneficiary_name" class="form-control" placeholder="Enter beneficiary name" required="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Account Number <span class="requiredstar">*</span></label>
                                <input type="text" name="account_number[0]" id="new_account_number" class="form-control" placeholder="Enter account number" required="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">IFSC <span class="requiredstar">*</span></label>
                                <input type="text" name="ifsc[0]" id="new_ifsc" class="form-control" placeholder="Enter IFSC" required="">
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" name="action_1" value="success" class="btn btn-success" data-request="ajax-submit" data-target='[role="new-bank-form-modal"]' data-callbackfn="newBankAccountCb">
                        <b><i class="icon-search4"></i></b> Add Account
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- Update Bank Info -->
<div aria-hidden="true" aria-labelledby="verify-bank-account" class="modal fade" id="updateBankAccountModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verify-bank-account">
                    Update Bank Account
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>

            <form role="update-bank-form-modal" action="{{url('admin/users-bank/update-banks-info')}}" data-DataTables="table-user_bank" method="POST">
                <div class="modal-body">

                    <div class="row">

                        @csrf()
                        <input type="hidden" name="row_id" id="row_id">
                        <input type="hidden" name="user_id" id="update_frm_user_id">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Beneficiary Name <span class="requiredstar">*</span></label>
                                <input type="text" name="beneficiary_name" id="beneficiary_name" class="form-control" placeholder="Enter beneficiary name" required="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Account Number <span class="requiredstar">*</span></label>
                                <input type="text" name="account_number" id="account_number" class="form-control" placeholder="Enter account number" required="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">IFSC <span class="requiredstar">*</span></label>
                                <input type="text" name="ifsc" id="ifsc" class="form-control" placeholder="Enter IFSC" required="">
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" name="action_1" value="success" class="btn btn-success" data-request="ajax-submit" data-target='[role="update-bank-form-modal"]' data-callbackfn="updateBankAccountCb">
                        <b><i class="icon-search4"></i></b> Update Account
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script src="{{url('public/js/comboTreePlugin.js')}}" type="text/javascript"></script>
<?php
if (auth()->user()->hasRole('super-admin')) {
?>
    <input type="hidden" id="bankUrl" value="{{custom_secure_url('admin/users-bank/report/bank-info/' . $user_id)}}">
    <script src="{{asset('admin-js/user-profile.js?v=1.0.0')}}"></script>
<?php
}
?>
<script>
    //Reseller
    let isAjaxResellerUpdate = false;
    $('#btn_reseller_update').on('click', function() {
        let resellerId = $('#resellers_data').val();
            $.ajax({
            url: $('meta[name="base-url"]').attr('content') + `admin/update-reseller/`,
            type: 'post',
            data: {
                reseller_id: resellerId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {

                if (res.code === '0x0200') {

                    Swal.fire({
                        title: res.data.status,
                        text: res.message,
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                    }).then((result) => {
                        location.reload();
                    });

                } else {
                    Swal.fire({
                        title: res.status,
                        text: res.message,
                        icon: 'warning',
                        showCancelButton: false,
                    });
                }
                isAjaxResellerUpdate = false;
            },
            error: () => {
                isAjaxResellerUpdate = false;
            }
        });
    });

    $(document).ready(function() {
        fetchUserLeanAmount();

        var url = "{{custom_secure_url('admin/fetch/upiInvoice/'.$user_id)}}";
        var onDraw = function() {};
        var options = [{
                "data": "invoice_id"
            },
            {
                "data": "service_id"
            },
            {
                "data": "fee_amount",

            },
            {
                "data": "fee_able_amount",
            },
            {
                "data": "start_date",
            },
            {
                "data": "end_date",
            },
            {
                "data": "new_created_at"
            }
        ];
        datatableSetup(url, options, onDraw);


        //lean mark table setup
        var url = "{{custom_secure_url('admin/get/lean_mark_txn/' . $user_id)}}";
        var onDraw = function() {};
        var options = [{
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": null,
                render: function(data, type, full, meta) {
                    // console.log(dt.page.info());
                    // return meta.row + 1;
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                "data": "txn_type",
                render: function(data, type, full, meta) {
                    if (data === 'dr') {
                        return `<span class="badge badge-danger">Debit</span>`;
                    } else if (data === 'cr') {
                        return `<span class="badge badge-success">Credit</span>`;
                    }
                }
            },
            {
                "data": "amount",
                render: function(data, type, full, meta) {
                    return data.toFixed(2);
                }
            },
            {
                "data": "opening_balance",
                render: function(data, type, full, meta) {
                    return data.toFixed(2);
                }
            },
            {
                "data": "closing_balance",
                render: function(data, type, full, meta) {
                    return data.toFixed(2);
                }
            },
            {
                "data": "new_created_at"
            }
        ];
        datatableSetup(url, options, onDraw, '#dt-lean-mark');
    });





    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            "scrollX": true,
            buttons: [
                'excel'
            ],
            order: [],
            columnDefs: [{
                'targets': [0],
                /* column index [0,1,2,3]*/
                'orderable': false,
                /* true or false */
            }],
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000, -1],
                [10, 25, 50, 75, 100, 200, 500, 1000, 1500]
            ],
            dom: "Bfrltip",
            language: {
                paginate: {
                    'first': 'First',
                    'last': 'Last',
                    'next': '&rarr;',
                    'previous': '&larr;'
                }
            },
            drawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
            },
            preDrawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
            },
            ajax: {
                url: urls,
                type: "post",
                data: function(d) {
                    $("")
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                },
                beforeSend: function() {},
                complete: function() {
                    $('#searchForm').find('button:submit').button('reset');
                    $('#formReset').button('reset');
                },
                error: function(response) {}
            },
            columns: datas
        };
        $.each(element, function(index, val) {
            options[index] = val;
        });
        var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
        return DT;
    }
    let isAjax = false;

    const generateVan = function(obj, event = 'click') {
        if (!isAjax) {
            isAjax = true;

            $(obj).html('Generating...');

            $.ajax({
                type: 'POST',
                url: $('meta[name="base-url"]').attr('content') + '/api/v1/van/create',
                data: {
                    user_id: $(obj).attr('data-user')
                },
                // contentType: false,
                // processData: false,
                success: (response) => {


                    if (response.code === '0x0200') {
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Okay!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                            location.replace(`profile#vanDetails`);
                            location.reload();
                        });
                    } else {
                        if (event === 'click') {
                            Swal.fire({
                                title: "Failed",
                                text: response.message,
                                icon: "error",
                                buttonsStyling: !1,
                                confirmButtonText: "Okay!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }

                    }

                    isAjax = false;
                    $(obj).html('Generate VAN');
                },
                error: (xhr, status, err) => {
                    console.log(xhr, status, err);
                    isAjax = false;
                    $(obj).html('Generate VAN (CF)');
                }
            });
        }
    }


    const generateVanEb = function(obj, event = 'click') {
        if (!isAjax) {
            isAjax = true;

            $(obj).html('Generating...');

            $.ajax({
                type: 'POST',
                url: $('meta[name="base-url"]').attr('content') + '/admin/van/eb/create',
                data: {
                    user_id: $(obj).attr('data-user'),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                // contentType: false,
                // processData: false,
                success: (response) => {


                    if (response.code === '0x0200') {
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Okay!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                            location.replace(`profile#vanDetails`);
                            location.reload();
                        });
                    } else {
                        if (event === 'click') {
                            Swal.fire({
                                title: "Failed",
                                text: response.message,
                                icon: "error",
                                buttonsStyling: !1,
                                confirmButtonText: "Okay!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }

                    }

                    isAjax = false;
                    $(obj).html('Generate VAN (EB)');
                },
                error: (xhr, status, err) => {
                    console.log(xhr, status, err);
                    isAjax = false;
                    $(obj).html('Generate VAN (EB)');
                }
            });
        }
    }

    const generateVanRp = function(obj, event = 'click') {
        if (!isAjax) {
            isAjax = true;

            $(obj).html('Generating...');

            $.ajax({
                type: 'POST',
                url: $('meta[name="base-url"]').attr('content') + '/admin/van/rp/create',
                data: {
                    user_id: $(obj).attr('data-user'),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                // contentType: false,
                // processData: false,
                success: (response) => {


                    if (response.code === '0x0200') {
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Okay!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                            location.replace(`profile#vanDetails`);
                            location.reload();
                        });
                    } else {
                        if (event === 'click') {
                            Swal.fire({
                                title: "Failed",
                                text: response.message,
                                icon: "error",
                                buttonsStyling: !1,
                                confirmButtonText: "Okay!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }

                    }

                    isAjax = false;
                    $(obj).html('Generate VAN (EB)');
                },
                error: (xhr, status, err) => {
                    console.log(xhr, status, err);
                    isAjax = false;
                    $(obj).html('Generate VAN (EB)');
                }
            });
        }
    }

    const generateVanOb = function(obj, event = 'click') {
        if (!isAjax) {
            isAjax = true;

            $(obj).html('Generating...');

            $.ajax({
                type: 'POST',
                url: $('meta[name="base-url"]').attr('content') + '/admin/van/ob/create',
                data: {
                    user_id: $(obj).attr('data-user'),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                // contentType: false,
                // processData: false,
                success: (response) => {


                    if (response.code === '0x0200') {
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Okay!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                            location.replace(`profile#vanDetails`);
                            location.reload();
                        });
                    } else {
                        if (event === 'click') {
                            Swal.fire({
                                title: "Failed",
                                text: response.message,
                                icon: "error",
                                buttonsStyling: !1,
                                confirmButtonText: "Okay!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }

                    }

                    isAjax = false;
                    $(obj).html('Generate VAN (OpenBank)');
                },
                error: (xhr, status, err) => {
                    console.log(xhr, status, err);
                    isAjax = false;
                    $(obj).html('Generate VAN (OpenBank)');
                }
            });
        }
    }


    //Bank Status Activate and Deactive
    let isAjaxChangeStatus = false;
    $('#change-van-status').on('click', function() {
        let userId = $(this).attr('data-user');
        if (!isAjaxChangeStatus) {
            isAjaxChangeStatus = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/api/v1/van/change-status`,
                type: 'post',
                data: {
                    user_id: userId
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            // location.replace(`profile#vanDetails`);
                            location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                            // confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        });

                    }

                    isAjaxChangeStatus = false;

                },
                error: () => {
                    isAjaxChangeStatus = false;
                }
            });
        }
    });

    //SDK Enable Status Activate and Deactive
    $('#change-is_sdk_enable-status').on('click', function() {
        let userId = $(this).attr('data-user');
        if (!isAjaxChangeStatus) {
            isAjaxChangeStatus = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/accounts/sdk/status`,
                type: 'post',
                data: {
                    user_id: userId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            // location.replace(`profile#vanDetails`);
                            // location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                        });

                    }

                    isAjaxChangeStatus = false;

                },
                error: () => {
                    Swal.fire({
                        title: textStatus,
                        text: errorThrown,
                        icon: 'warning',
                        showCancelButton: false,
                    });
                    isAjaxChangeStatus = false;
                }
            });
        }
    });
    $('#change-is_auto_settlement').on('click', function() {
        let userId = $(this).attr('data-user');
        if (!isAjaxChangeStatus) {
            isAjaxChangeStatus = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/accounts/autoSettlement/status`,
                type: 'post',
                data: {
                    user_id: userId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            // location.replace(`profile#vanDetails`);
                            // location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                        });

                    }

                    isAjaxChangeStatus = false;

                },
                error: () => {
                    Swal.fire({
                        title: textStatus,
                        text: errorThrown,
                        icon: 'warning',
                        showCancelButton: false,
                    });
                    isAjaxChangeStatus = false;
                }
            });
        }
    });

    //Load Money Request Enable Status Activate and Deactive
    $('#change-load_money_request-status').on('click', function() {
        let userId = $(this).attr('data-user');
        if (!isAjaxChangeStatus) {
            isAjaxChangeStatus = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/accounts/load_money_request/status`,
                type: 'post',
                data: {
                    user_id: userId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            // location.replace(`profile#vanDetails`);
                            // location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                        });

                    }

                    isAjaxChangeStatus = false;

                },
                error: (JQueryXHR, textStatus, errorThrown) => {
                    Swal.fire({
                        title: textStatus,
                        text: errorThrown,
                        icon: 'warning',
                        showCancelButton: false,
                    });
                    isAjaxChangeStatus = false;
                }
            });
        }
    });

    //Internal Transfer Status Activate and Deactive
    $('#change-internal_transfer-status').on('click', function() {
        let userId = $(this).attr('data-user');
        if (!isAjaxChangeStatus) {
            isAjaxChangeStatus = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/accounts/internal-transfer/status`,
                type: 'post',
                data: {
                    user_id: userId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                        });

                    }

                    isAjaxChangeStatus = false;

                },
                error: (JQueryXHR, textStatus, errorThrown) => {
                    Swal.fire({
                        title: textStatus,
                        text: errorThrown,
                        icon: 'warning',
                        showCancelButton: false,
                    });
                    isAjaxChangeStatus = false;
                }
            });
        }
    });

    let isAjaxChangeStatusEB = false;
    $('#change-van-status-eb').on('click', function() {
        let vId = $(this).attr('data-user');
        if (!isAjaxChangeStatusEB) {
            isAjaxChangeStatusEB = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/van/eb/change-status`,
                type: 'post',
                data: {
                    vId: vId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            // location.replace(`profile#vanDetails`);
                            // location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                            // confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        });

                    }

                    isAjaxChangeStatusEB = false;

                },
                error: () => {
                    isAjaxChangeStatusEB = false;
                }
            });
        }
    });

    let isAjaxUpdateVanEB = false;
    $('#update-van-status-eb').on('click', function() {
        let vId = $(this).attr('data-user');
        $('#update-van-status-eb').html('Updating');

        if (!isAjaxUpdateVanEB) {
            isAjaxUpdateVanEB = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/van/eb/update-van`,
                type: 'post',
                data: {
                    vId: vId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: "Success",
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            // location.replace(`profile#vanDetails`);
                            location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                            // confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        });

                    }

                    isAjaxUpdateVanEB = false;
                    $('#update-van-status-eb').html('Update');

                },
                error: () => {
                    isAjaxUpdateVanEB = false;
                    $('#update-van-status-eb').html('Update');
                }
            });
        }
    });

    $('#change-is_matm_enable-status').on('click', function() {
        let userId = $(this).attr('data-user');
        if (!isAjaxChangeStatus) {
            isAjaxChangeStatus = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/accounts/matm/status`,
                type: 'post',
                data: {
                    user_id: userId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            // location.replace(`profile#vanDetails`);
                            // location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                        });

                    }

                    isAjaxChangeStatus = false;

                },
                error: () => {
                    Swal.fire({
                        title: textStatus,
                        text: errorThrown,
                        icon: 'warning',
                        showCancelButton: false,
                    });
                    isAjaxChangeStatus = false;
                }
            });
        }
    });


    $(document).ready(function() {
        $('#generate-van').on('click', function() {
            generateVan(this);
        });

        $('#generate-van-rp').on('click', function() {
            generateVanRp(this);
        });

        $('#generate-van-eb').on('click', function() {
            generateVanEb(this);
        });

        $('#generate-van-ob').on('click', function() {
            generateVanOb(this);
        });
    });


    //Bank Status Activate and Deactive
    let isAjaxUpdateAmt = false;
    $('#btn_amt_update').on('click', function() {
        let userId = $(this).attr('data-user');
        let minAmt = $('#min_amt').val();
        let maxAmt = $('#max_amt').val();

        if (minAmt == '' || minAmt == null || maxAmt == '' || maxAmt == null) {
            alert('Min and Max Amount are required.');
            return false;
        }

        if (!isAjaxUpdateAmt) {
            isAjaxUpdateAmt = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/api/v1/van/change-limit`,
                type: 'post',
                data: {
                    user_id: userId,
                    min_amt: minAmt,
                    max_amt: maxAmt
                },
                success: function(res) {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        });


                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                            // confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        });

                    }

                    isAjaxUpdateAmt = false;

                },
                error: () => {
                    isAjaxUpdateAmt = false;
                }
            });
        }
    });

    function apiServiceUpdate(id) {
        $.ajax({
            url: `{{custom_secure_url('admin/api-service-activate')}}/${id}`,
            type: 'GET',
            success: function(res) {
                // $("#datatable").DataTable().ajax.reload();
            }
        });
    }

    function webServiceUpdate(id) {
        $.ajax({
            url: `{{custom_secure_url('admin/web-service-activate')}}/${id}`,
            type: 'GET',
            success: function(res) {
                // $("#datatable").DataTable().ajax.reload();
            }
        });
    }

    function statusUpdate(id) {
        $.ajax({
            url: "{{custom_secure_url('admin/serviceActivate')}}/" + id,
            type: 'GET',
            success: function(res) {
                $("#datatable").DataTable().ajax.reload();
            }
        });
    }
    //Lean Amount
    let isAjaxLeanAmt = false;
    $('#btn_lean_amt_update').on('click', function() {
        let userId = $(this).attr('data-user');
        let lean_amt = $('#lean_amt').val();

        if (lean_amt == '' || lean_amt == null) {
            // alert('Min and Max Amount are required.');

            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Amount is required !'
            });

            return false;
        } else {

            // if (!confirm('Are you sure to update?'))

            Swal.fire({
                title: 'Are you sure to update?',
                showDenyButton: true,
                // showCancelButton: true,
                confirmButtonText: 'Yes',
                denyButtonText: `Cancel`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {

                    if (!isAjaxLeanAmt) {
                        isAjaxLeanAmt = true;
                        $.ajax({
                            url: $('meta[name="base-url"]').attr('content') + `/admin/update-lean-amount`,
                            type: 'post',
                            data: {
                                user_id: userId,
                                lean_amt: lean_amt,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {

                                if (res.code === '0x0200') {

                                    Swal.fire({
                                        title: res.data.status,
                                        text: res.message,
                                        icon: 'success',
                                        showCancelButton: false,
                                        confirmButtonColor: '#3085d6',
                                        // cancelButtonColor: '#d33',
                                        // confirmButtonText: 'Yes'
                                    }).then((result) => {
                                        // location.replace(`profile#vanDetails`);
                                        location.reload();
                                    });

                                } else {

                                    Swal.fire({
                                        title: res.status,
                                        text: res.message,
                                        icon: 'warning',
                                        showCancelButton: false,
                                        // confirmButtonColor: '#3085d6',
                                        // cancelButtonColor: '#d33',
                                        // confirmButtonText: 'Yes'
                                    });

                                }

                                isAjaxLeanAmt = false;

                            },
                            error: () => {
                                isAjaxLeanAmt = false;
                            }
                        });
                    }

                } else if (result.isDenied) {
                    return false;
                }
            });

        }

    });

    //Transaction Reversed
    let isAjaxRevAmt = false;

    $('#cal_amount').click(function(e) {

        var Elem = e.target;

        if (Elem.nodeName == 'BUTTON') {

            var from = $('#fromDate').val();
            var to = $('#toDate').val();
            var userId = $('#user_id').val();
            var fee = $('#upiFee').val();

            if (from && (fee && fee != 0)) {

                $.ajax({
                    url: $('meta[name="base-url"]').attr('content') + '/admin/upiTotalAmount',
                    type: 'post',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        user_id: userId,
                        from: from,
                        to: to,
                        fee: fee
                    },
                    success: function(response) {
                        var data = JSON.parse(response);

                        if (data.status) {
                            var html = '<label>Amount:' + data.amount + '</label><br/><label>Fee:' + data.fee + '</label>';
                            $('#amt_data').html(html);
                            $('#kt_modal_batch_import').modal('hide');
                            $('#fee_from_date').val(from);
                            $('#fee_to_date').val(to);
                            $('#fee_amount').val(data.fee);
                            $('#fee_percentage').val(data.fee_percentage);
                            $('#upi_amount').val(data.amount);
                            $('#kt_modal').modal('show');
                        }
                    }
                });
            }
        }
    });
</script>
<script type="text/javascript">
    var SampleJSONDataAEPS = [

        {
            id: "web-icici",
            title: 'web-icici',
            subs: [{
                    id: "web-icici-be",
                    title: 'web-icici-be',
                }, {
                    id: "web-icici-ms",
                    title: 'web-icici-ms'
                }, {
                    id: "web-icici-cw",
                    title: 'web-icici-cw'
                }
                /*, {
                                        id: "web-icici-ap",
                                        title: 'web-icici-ap'
                                    }*/
            ]
        }, {
            id: "web-sbm",
            title: 'web-sbm',
            subs: [{
                    id: "web-sbm-be",
                    title: 'web-sbm-be',
                }, {
                    id: "web-sbm-ms",
                    title: 'web-sbm-ms'
                }, {
                    id: "web-sbm-cw",
                    title: 'web-sbm-cw'
                }
                /*, {
                    id: "web-sbm-ap",
                    title: 'web-sbm-ap'
                }*/
            ]
        }, {
            id: "web-airtel",
            title: 'web-airtel',
            subs: [{
                    id: "web-airtel-be",
                    title: 'web-airtel-be',
                }, {
                    id: "web-airtel-ms",
                    title: 'web-airtel-ms'
                }, {
                    id: "web-airtel-cw",
                    title: 'web-airtel-cw'
                }
                /*, {
                    id: "web-airtel-ap",
                    title: 'web-airtel-ap'
                }*/
            ]
        }, {
            id: "web-paytm",
            title: 'web-paytm',
            subs: [{
                    id: "web-paytm-be",
                    title: 'web-paytm-be',
                }, {
                    id: "web-paytm-ms",
                    title: 'web-paytm-ms'
                }, {
                    id: "web-paytm-cw",
                    title: 'web-paytm-cw'
                }
                /*, {
                    id: "web-paytm-ap",
                    title: 'web-paytm-ap'
                }*/
            ]
        }
    ];
    var SampleJSONDataAEPS1 = [

        {
            id: "api-icici",
            title: 'api-icici',
            subs: [{
                    id: "api-icici-be",
                    title: 'api-icici-be',
                }, {
                    id: "api-icici-ms",
                    title: 'api-icici-ms'
                }, {
                    id: "api-icici-cw",
                    title: 'api-icici-cw'
                }
                /*, {
                    id: "api-icici-ap",
                    title: 'api-icici-ap'
                }*/
            ]
        }, {
            id: "api-sbm",
            title: 'api-sbm',
            subs: [{
                    id: "api-sbm-be",
                    title: 'api-sbm-be',
                }, {
                    id: "api-sbm-ms",
                    title: 'api-sbm-ms'
                }, {
                    id: "api-sbm-cw",
                    title: 'api-sbm-cw'
                }
                /*, {
                    id: "api-sbm-ap",
                    title: 'api-sbm-ap'
                }*/
            ]
        }, {
            id: "api-airtel",
            title: 'api-airtel',
            subs: [{
                    id: "api-airtel-be",
                    title: 'api-airtel-be',
                }, {
                    id: "api-airtel-ms",
                    title: 'api-airtel-ms'
                }, {
                    id: "api-airtel-cw",
                    title: 'api-airtel-cw'
                }
                /*, {
                    id: "api-airtel-ap",
                    title: 'api-airtel-ap'
                } */
            ]
        }, {
            id: "api-paytm",
            title: 'api-paytm',
            subs: [{
                    id: "api-paytm-be",
                    title: 'api-paytm-be',
                }, {
                    id: "api-paytm-ms",
                    title: 'api-paytm-ms'
                }, {
                    id: "api-paytm-cw",
                    title: 'api-paytm-cw'
                }
                /*, {
                    id: "api-paytm-ap",
                    title: 'api-paytm-ap'
                }*/
            ]
        }
    ];

    var SampleJSONDataPayout = [{
        id: "web",
        title: 'web',
        subs: [{
            id: "web-neft",
            title: 'web-neft'
        }, {
            id: "web-imps",
            title: 'web-imps'
        }, {
            id: "web-rtgs",
            title: 'web-rtgs'
        }, {
            id: "web-upi",
            title: 'web-upi'
        }]
    }, {
        id: "api",
        title: 'api',
        subs: [{
            id: "api-neft",
            title: 'api-neft'
        }, {
            id: "api-imps",
            title: 'api-imps'
        }, {
            id: "api-rtgs",
            title: 'api-rtgs'
        }, {
            id: "api-upi",
            title: 'api-upi'
        }]
    }];

    var SampleJSONDataUPI = [{
        id: "web",
        title: 'web',
        subs: [{
            id: "web-upi_receive",
            title: 'web-upi_receive'
        }, {
            id: "web-upi_collect",
            title: 'web-upi_collect'
        }]
    }, {
        id: "api",
        title: 'api',
        subs: [{
            id: "api-upi_receive",
            title: 'api-upi_receive'
        }, {
            id: "api-upi_collect",
            title: 'api-upi_collect'
        }]
    }];

    var SampleJSONDataSmartCollect = [{
        id: "web",
        title: 'web',
        subs: [{
            id: "web-upi",
            title: 'web-upi'
        }, {
            id: "web-van",
            title: 'web-van'
        }]
    }, {
        id: "api",
        title: 'api',
        subs: [{
            id: "api-upi",
            title: 'api-upi'
        }, {
            id: "api-van",
            title: 'api-van'
        }]
    }];
    var comboTree1, comboTree2, comboTreeSmartCollect;

    jQuery(document).ready(function($) {
        comboTree1 = $('.justAnotherInputBoxPayout').comboTree({
            source: SampleJSONDataPayout,
            isMultiple: true,
            cascadeSelect: true,
            collapse: false,
            selected: [<?php echo $payoutSelectedValue; ?>]
        });
        comboTreeSmartCollect = $('.justAnotherInputBoxSmartCollect').comboTree({
            source: SampleJSONDataSmartCollect,
            isMultiple: true,
            cascadeSelect: true,
            collapse: false,
            selected: [<?php echo $smartCollectSelectedValue; ?>]
        });
        comboTree2 = $('.justAnotherInputBoxAEPS').comboTree({
            source: SampleJSONDataAEPS,
            isMultiple: true,
            cascadeSelect: true,
            collapse: false,
            selected: [<?php echo $aepsSelectedValue; ?>]
        });
        comboTreeAeps = $('.justAnotherInputBoxAEPS1').comboTree({
            source: SampleJSONDataAEPS1,
            isMultiple: true,
            cascadeSelect: true,
            collapse: false,
            selected: [<?php echo $aepsSelectedValue; ?>]
        });
        comboTree3 = $('.justAnotherInputBoxUPI').comboTree({
            source: SampleJSONDataUPI,
            isMultiple: true,
            cascadeSelect: true,
            collapse: false,
            selected: [<?php echo $upiSelectedValue; ?>]
        });
        $('#updateApiValue').on('click', function() {
            upi = $('.justAnotherInputBoxUPI').val();
            payout = $('.justAnotherInputBoxPayout').val();
            smartCollect = $('.justAnotherInputBoxSmartCollect').val();
            aepsweb = $('.justAnotherInputBoxAEPS').val();
            aepsapi = $('.justAnotherInputBoxAEPS1').val();
            var userId = $('#userIds').val();
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + '/admin/serviceValueUpdate',
                type: 'post',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    user_id: userId,
                    payout_service: payout,
                    smart_collect: smartCollect,
                    aeps_service: aepsweb,
                    upi_service: upi,
                    aeps_service_api: aepsapi
                },
                success: function(response) {
                    //console.log(response.status);
                    if (response.status) {

                        Swal.fire({
                            title: 'Update Services Values',
                            text: response.message,
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                            location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: 'Update Services Values',
                            text: response.message,
                            icon: 'warning',
                            showCancelButton: false,
                        });

                    }
                }
            });
        });
    });

    function fetchUserLeanAmount() {
        $.ajax({
            url: $('meta[name="base-url"]').attr('content') + `/admin/fetch-lean-amount`,
            type: 'post',
            data: {
                user_id: `{{encrypt($user_id)}}`,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {

                if (res.status === 'SUCCESS') {
                    $('#crLeanAmt').html('&#8377;' + res.data.cr.toFixed(2));
                    $('#drLeanAmt').html('&#8377;' + res.data.dr.toFixed(2));
                } else {
                    $('#crLeanAmt').html(0);
                    $('#drLeanAmt').html(0);
                }

            },
            error: (err) => {
                console.log(err);
                $('#crLeanAmt').html(0);
                $('#drLeanAmt').html(0);
            }
        });
    }
</script>
@endsection