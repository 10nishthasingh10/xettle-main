@extends('layouts.user.app')
@section('title','My Profile')

@section('style')
<style type="text/css">
    .lettersProfile:before {
        height: 80px;
        width: 80px;
        line-height: 80px;
        font-size: 2.7rem;
        line-height: 1.8em;

    }

    .border-danger {
        border-color: #dc3545 !important;
    }

    .element-box {
        padding: 1.5rem 0.8rem;
    }

    .lettersProfile::before {
        margin-right: 0 !important;
    }
</style>
<meta name="user-token" content="{{encrypt($userData->id)}}">
@endsection

@section('content')
<div class="content-i">
    <div class="content-box">
        <div class="row">
            <div class="col-md-4">
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
                        <!--<div class="logged-user-role">-->
                        <!--    @if($userData->is_admin)-->
                        <!--    Admin-->
                        <!--    @else-->
                        <!--    User-->
                        <!--    @endif-->
                        <!--</div>-->
                    </div>
                    <div class="ecommerce-customer-sub-info">
                        <div class="row ecc-sub-info-row">
                            <div class="col-6 sub-info-label">
                                Transactional Amount
                            </div>
                            <div class="col-6 sub-info-value">
                                @if($userData->transaction_amount > 0)
                                ₹{{number_format($userData->transaction_amount,2)}}
                                @else
                                ₹{{number_format(0,2)}}
                                @endif

                            </div>
                        </div>
                        <div class="row ecc-sub-info-row">
                            <div class="col-6 sub-info-label">
                                Locked Amount
                            </div>
                            <div class="col-6 sub-info-value">
                                @if($userData->locked_amount > 0)
                                ₹{{number_format($userData->locked_amount,2)}}
                                @else
                                ₹{{number_format(0,2)}}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="os-tabs-controls">
                        <ul class="nav nav-tabs" id="myProfileTab">
                            @if(!empty($obVan))
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#vanDetails"> VAN</a>
                            </li>
                            @else
                            @php
                            $isActiveTab = 'active';
                            @endphp
                            @endif
                            <li class="nav-item">
                                <a class="nav-link {{$isActiveTab}}" data-toggle="tab" href="#profile">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#business_profile_show">Business Profile</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <!-- VAN Details -->
                        @if(!empty($obVan))
                        <div class="tab-pane active" id="vanDetails">

                            <?php
                            /*/
                            ?>
                            @if(!empty($data['van_info']))

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Biz. Name
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['business_info']->business_name}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    VAN
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['van_info']['van']}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    IFSC
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['van_info']['ifsc']}}
                                </div>
                            </div>
                            @if(!empty($data['van_info']['van_2']))
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    VAN (2nd)
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['van_info']['van_2']}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    IFSC (2nd)
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$data['van_info']['ifsc_2']}}
                                </div>
                            </div>
                            @endif
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Status
                                </div>
                                <div class="col-7 sub-info-value">
                                    @if($data['van_info']['status'] === '1')
                                    <div class="status-pill green" data-title="Active" data-toggle="tooltip" data-original-title="" title="">
                                    </div>
                                    @else
                                    <div class="status-pill red" data-title="InActive" data-toggle="tooltip" data-original-title="" title="">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Min Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 100
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Max Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 1000000
                                </div>
                            </div>
                            @endif

                            @if(!empty($ebVan))
                            <hr class="hr">

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Biz. Name
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$ebVan->account_holder_name}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    VAN
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$ebVan->account_number}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    IFSC
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$ebVan->ifsc}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Status
                                </div>
                                <div class="col-7 sub-info-value">
                                    @if($ebVan->status === '1')
                                    <div class="status-pill green" data-title="Active" data-toggle="tooltip" data-original-title="" title="">
                                    </div>
                                    @else
                                    <div class="status-pill red" data-title="InActive" data-toggle="tooltip" data-original-title="" title="">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Min Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 100
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Max Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 1000000
                                </div>
                            </div>
                            @endif

                            <?php
                            //*/
                            ?>

                            @if(!empty($obVan))
                            <hr class="hr">

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Biz. Name
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$obVan->account_holder_name}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    VAN
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$obVan->account_number}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    IFSC
                                </div>
                                <div class="col-7 sub-info-value">
                                    {{$obVan->ifsc}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Status
                                </div>
                                <div class="col-7 sub-info-value">
                                    @if($obVan->status === '1')
                                    <div class="status-pill green" data-title="Active" data-toggle="tooltip" data-original-title="" title=""></div>
                                    @else
                                    <div class="status-pill red" data-title="InActive" data-toggle="tooltip" data-original-title="" title=""></div>
                                    @endif
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Min Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 100
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-5 sub-info-label">
                                    Max Amount
                                </div>
                                <div class="col-7 sub-info-value">
                                    Rs. 1000000
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <div class="tab-pane {{$isActiveTab}}" id="profile">
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Full Name
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$userData->name}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    E-Mail Address
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$userData->email}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Contact
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$userData->mobile}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Number
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$userData->account_number}}
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane " id="business_profile_show">
                            @if(isset($business_info) && !empty($business_info))
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Contact Name
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->name}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    E-Mail Address
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->email}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Contact
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->mobile}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Business Type
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->business_type}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Business Category
                                </div>
                                <div class="col-6 sub-info-value">
                                    @if(isset($business_info->business_category_id) && !empty($business_info->business_category_id))
                                    @php
                                    $business_category = DB::table('business_categories')
                                    ->where('id',$business_info->business_category_id)->first();

                                    @endphp
                                    @if(isset($business_category) && !empty($business_category))
                                    {{$business_category->name}}
                                    @endif
                                    @else
                                    NA
                                    @endif
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Business Sub Category
                                </div>
                                <div class="col-6 sub-info-value">
                                    @if(isset($business_info->business_subcategory_id) && !empty($business_info->business_subcategory_id))
                                    @php
                                    $business_category = DB::table('business_categories')
                                    ->where('id',$business_info->business_subcategory_id)->first();

                                    @endphp
                                    @if(isset($business_category) && !empty($business_category))
                                    {{$business_category->name}}
                                    @endif
                                    @else
                                    NA
                                    @endif
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Pan Number
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->pan_number}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Pan Owner Name
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->pan_owner_name}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Billing Label
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->billing_label}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Address
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->address}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Pin Code
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->pincode}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    City
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->city}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    State
                                </div>
                                <div class="col-6 sub-info-value">
                                    @foreach($data['state_list'] as $state_list)
                                    @if($business_info->state == $state_list->id)
                                    {{$state_list->state_name}}
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Gstin
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->gstin}}
                                </div>
                            </div>

                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Manager
                                </div>
                                <div class="col-6 sub-info-value">
                                    @if(isset($account_manager_data->name))

                                    {{$account_manager_data->name}} ({{$account_manager_data->mobile}})
                                    @endif
                                </div>
                            </div>

                            @if(isset($account_coordinator_data->name))
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Coordinator
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$account_coordinator_data->name}} ({{$account_coordinator_data->mobile}})
                                </div>
                            </div>
                            @endif

                            @else
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-value">
                                    <span style="color:red">Please update business profile.</span>
                                </div>
                            </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="element-box">

                    <div class="os-tabs-w">
                        <div class="os-tabs-controls">
                            <ul class="nav nav-tabs bigger">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tab_overview">Personal info </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tab_sales">Business details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tab_bank">Bank details</a>
                                </li>
                                <!--<li class="nav-item">-->
                                <!--    <a class="nav-link" data-toggle="tab" href="#tab_api">Integrations</a>-->
                                <!--</li>-->
                            </ul>

                        </div>
                        <div class="tab-content">
                            <!-- personal form -->
                            <div class="tab-pane active" id="tab_overview">
                                <form role="update-profile" action="{{custom_secure_url('admin/accounts/profile-update')}}" method="post">
                                    @csrf
                                    <div class="row">
                                        <input type="hidden" name="user_id" value="{{encrypt($userData->id)}}" />
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for=""> Name</label><input class="form-control" placeholder="Enter Name" name="name" value="{{$userData->name}}"  type="text">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">Mobile *</label><input class="form-control" placeholder="Phone number" value="{{$userData->mobile}}" name="mobile" type="number">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-buttons-w text-right">
                                        <button type="submit" name="update-profile" class="btn btn-primary" data-request="ajax-submit" data-target='[role="update-profile"]'>Update Profile</button>
                                    </div>
                                </form>

                                <form role="change-password" action="{{custom_secure_url('admin/accounts/profile-change-password')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{encrypt($userData->id)}}" />
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for=""> Old Password </label><input class="form-control" placeholder="old Password" name="old_password" type="password">
                                            </div>

                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">New Password</label><input class="form-control" placeholder="New Password" name="password" type="password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">Confirm Password</label><input class="form-control" placeholder="Password" name="confirm_password" type="password">
                                            </div>

                                        </div>
                                    </div>

                                    <div class="form-buttons-w text-right">
                                        <button type="submit" name="change-password" class="btn btn-primary" data-request="ajax-submit" data-callbackfn="changePasswordCallback" data-target='[role="change-password"]'>Change Password</button>
                                    </div>
                                </form>
                            </div>
                            <!-- personal form  end-->

                            <!-- from business  -->
                            <div class="tab-pane" id="tab_sales">
                                <form role="update-business-profile" action="{{url('admin/accounts/business-profile-update')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{encrypt($userData->id)}}" />
                                    <fieldset class="form-group">
                                        <legend><span>CONTACT INFO</span></legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Contact Name <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Contact Name" name="name" 
                                                    @if(isset($data['business_info']))

                                                    value="{{$data['business_info']->name}}"
                                                    @else
                                                    value="{{$userData->name}}" @endif type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Contact Number <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Contact Number" 

                                                    @if(isset($data['business_info']))
                                                    value="{{$data['business_info']->mobile}}"
                                                    @else
                                                    value="{{$userData->mobile}}"
                                                    @endif
                                                    name="contact_number" type="text">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Contact Email <span class="requiredstar">*</span></label>
                                                    <input type="email" name="contact_email" 
                                                    

                                                    @if(isset($data['business_info']))
                                                    value="{{$data['business_info']->email}}"
                                                    @else
                                                    value="{{$userData->email}}"
                                                    @endif
                                                    class="form-control" placeholder="Contact Number">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Reseller <span class="requiredstar">*</span></label>
                                                    <select name="reseller" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Reseller..." class="form-select form-select-solid" >
                                                        <option value="">Select a Reseller...</option>
                                                        @foreach($data['resellers'] as $resellerdata)
                                                        <option value="{{$resellerdata->id}}" @if(isset($data['userData']) &&  $data['userData']->reseller==$resellerdata->id) selected @endif
                                                            >{{$resellerdata->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <fieldset class="form-group">
                                        <legend><span>BUSINESS OVERVIEW</span></legend>
                                        <div class="row">

                                            <div class="col-sm-6">
                                                <input type="hidden" name="update_by_user_id" value="{{encrypt($userData->id)}}" />
                                                <div class="form-group">
                                                    <label for="">Business/Company Name <span class="requiredstar">*</span></label>
                                                    <input type="text" name="business_name"

                                                    @if(isset($data['business_info']->business_name))
                                                    value="{{$data['business_info']->business_name}}"
                                                    @endif
                                                    class="form-control" placeholder="Business/Company Name" required="required">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Business/Company Pan <span class="requiredstar"></span></label>
                                                    <input type="text" name="business_pan" 

                                                    @if(isset($data['business_info']->business_pan))
                                                    value="{{$data['business_info']->business_pan}}"
                                                    @endif
                                                    class="form-control" placeholder="Business/Company Pan" required="required">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Business Type <span class="requiredstar">*</span></label>
                                                    <select name="business_type" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Business Type..." class="form-select form-select-solid">
                                                        <option value="">Select a Business Type...</option>
                                                        <option value="Proprietorship" @if(isset($data['business_info'])) @if($data['business_info']->business_type=="Proprietorship")
                                                            selected
                                                            @endif
                                                            @endif>Proprietorship</option>
                                                        <option value="Partnership" @if(isset($data['business_info'])) @if($data['business_info']->business_type=="Partnership")
                                                            selected
                                                            @endif
                                                            @endif
                                                            >Partnership</option>
                                                        <option value="Public Limited" @if(isset($data['business_info'])) @if($data['business_info']->business_type=="Public Limited")
                                                            selected
                                                            @endif
                                                            @endif
                                                            >Public Limited</option>
                                                        <option value="Private Limited" @if(isset($data['business_info'])) @if($data['business_info']->business_type == "Private Limited")
                                                            selected
                                                            @endif
                                                            @endif
                                                            >Private Limited</option>

                                                        <option value="LLP" @if(isset($data['business_info'])) @if($data['business_info']->business_type=="LLP")
                                                            selected
                                                            @endif
                                                            @endif
                                                            >LLP</option>
                                                        <option value="Trust" @if(isset($data['business_info'])) @if($data['business_info']->business_type=="Trust")
                                                            selected
                                                            @endif
                                                            @endif
                                                            >Trust</option>
                                                        <option value="Society" @if(isset($data['business_info'])) @if($data['business_info']->business_type=="Society")
                                                            selected
                                                            @endif
                                                            @endif
                                                            >Society</option>
                                                        <option value="NGO" @if(isset($data['business_info'])) @if($data['business_info']->business_type=="NGO")
                                                            selected
                                                            @endif
                                                            @endif
                                                            >NGO</option>
                                                        <option value="Not Registered" @if(isset($data['business_info'])) @if($data['business_info']->business_type=="Not Registered")
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
                                                    <select name="business_category" class="form-control" id="business_category_id" onchange="businessCategory(this);" data-control="select2" data-hide-search="true" data-placeholder="Select a Category..." class="form-select form-select-solid">
                                                        <option value="">Select a Business Category...</option>
                                                        @foreach($data['business_category'] as $business_category)
                                                        <option @if(isset($data['business_info'])) @if($data['business_info']->business_category_id==$business_category->id)
                                                            selected
                                                            @endif
                                                            @endif
                                                            value="{{$business_category->id}}">{{$business_category->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6" style="display:none" id="b_sub_cate_id">
                                                <div class="form-group">
                                                    <label for="">Business Sub Category</label>
                                                    <select class="form-control" name="business_subcategory" id="businesssubcategory" data-control="select2" data-hide-search="true" data-placeholder="Select a Sub Category..." class="form-select form-select-solid" >
                                                        <option value="">Select a Business Sub Category...</option>
                                                    </select>
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Website URL</label>
                                                    
                                                    <input type="text" name="web_url" @if(isset($data['business_info'])) value="{{$data['business_info']->web_url}}" @endif class="form-control" placeholder="Website URL">
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                    
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">App URL</label>
                                                    
                                                    <input type="text" name="app_url"  @if(isset($data['business_info'])) value="{{$data['business_info']->app_url}}" @endif class="form-control" placeholder="App URL">
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                    
                                                </div>
                                            </div>
                                        </div>


                                    </fieldset>

                                    <fieldset class="form-group">
                                        <legend><span>BUSINESS DETAILS</span></legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Authorised Signatory PAN <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Authorised Signatory PAN"
                                                    @if(isset($data['business_info']))
                                                    value="{{$data['business_info']->pan_number}}"
                                                    @else
                                                    value="{{$userData->pan_number}}"
                                                    @endif name="pan_number" type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for=""> PAN Owner’s Name <span class="requiredstar">*</span></label>
                                                        <input class="form-control" placeholder="PAN Owner’s Name"

                                                        @if(isset($data['business_info']))
                                                        value="{{$data['business_info']->pan_owner_name}}"
                                                        @else
                                                        value="{{$userData->pan_owner_name}}"
                                                        @endif
                                                        name="pan_owner_name"
                                                        type="text">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Billing Label <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Billing Label " name="billing_label" 

                                                    @if(isset($data['business_info']))
                                                    value="{{$data['business_info']->billing_label}}"
                                                    @else
                                                    value="{{$userData->billing_label}}"
                                                    @endif type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="">Address <span class="requiredstar">*</span></label>
                                                        <textarea class="form-control" name="address" >@if(isset($data['business_info'])){{$data['business_info']->address}}@endif</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Pincode <span class="requiredstar">*</span></label>
                                                    <input class="form-control" name="pincode" placeholder="Pin code" 
                                                    @if(isset($data['business_info']))
                                                    value="{{$data['business_info']->pincode}}"
                                                    @else
                                                    value="{{$userData->pincode}}"
                                                    @endif type="number">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="">City <span class="requiredstar">*</span></label>
                                                        <input class="form-control" 
                                                        @if(isset($data['business_info']))
                                                        value="{{$data['business_info']->city}}"
                                                        @else
                                                        value="{{$userData->city}}"
                                                        @endif placeholder="City" name="city" type="text">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> State <span class="requiredstar">*</span></label>
                                                    <select name="state" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a State..." class="form-select form-select-solid" >
                                                        <option value="">Select a State...</option>
                                                        @foreach($data['state_list'] as $state_list)
                                                        <option value="{{$state_list->id}}" @if(isset($data['business_info'])) @if($data['business_info']->state==$state_list->id)
                                                            selected
                                                            @endif
                                                            @endif
                                                            >{{$state_list->state_name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="">GSTIN <span class="requiredstar">*</span></label>
                                                        <input class="form-control" name="gstin"
                                                        @if(isset($data['business_info']))
                                                        value="{{$data['business_info']->gstin}}"
                                                        @else
                                                        value="{{$userData->gstin}}"
                                                        @endif placeholder="GSTIN" type="text">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="">Category Code (MCC) <span class="requiredstar">*</span></label>
                                                        <input class="form-control" name="business_mcc" 
                                                        @if(isset($data['business_info']))
                                                        value="{{$data['business_info']->mcc}}"
                                                        @else
                                                        value="{{$userData->mcc}}"
                                                        @endif placeholder="Category Code (MCC)" type="text">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!--<fieldset class="form-group">-->
                                    <!--    <legend><span>Account Manager</span></legend>-->
                                    <!--    <div class="row">-->
                                    <!--        <div class="col-sm-6">-->
                                    <!--            <div class="form-group">-->
                                    <!--                <label for=""> Account Manager <span class="requiredstar">*</span></label>-->
                                    <!--                <select name="acc_manager_id" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Account Manager..." class="form-select form-select-solid" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1') disabled @endif>-->
                                    <!--                    <option value="">Select a Account Manager...</option>-->
                                    <!--                    @foreach($data['account_manager'] as $account_manager)-->
                                    <!--                    @if($account_manager->type == 'account_manager')-->
                                    <!--                    <option value="{{$account_manager->id}}"-->
                                                            
                                    <!--                        >{{$account_manager->name}} ({{$account_manager->mobile}})</option>-->
                                    <!--                        @endif-->
                                    <!--                    @endforeach-->
                                    <!--                </select>-->
                                    <!--            </div>-->
                                    <!--        </div>-->
                                    <!--        <div class="col-sm-6">-->
                                    <!--            <div class="form-group">-->
                                    <!--                <label for=""> Account Coordinator <span class="requiredstar">*</span></label>-->
                                    <!--                <select name="acc_coordinator_id" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Account Manager..." class="form-select form-select-solid" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1') disabled @endif>-->
                                    <!--                    <option value="">Select a Account Coordinator...</option>-->
                                    <!--                    @foreach($data['account_manager'] as $account_manager)-->
                                    <!--                    @if($account_manager->type == 'account_coordinator')-->
                                    <!--                    <option value="{{$account_manager->id}}" -->
                                    <!--                        >{{$account_manager->name}} ({{$account_manager->mobile}})</option>-->
                                    <!--                        @endif-->
                                    <!--                    @endforeach-->
                                    <!--                </select>-->
                                    <!--            </div>-->
                                    <!--        </div>-->
                                    <!--    </div>-->

                                    <!--</fieldset>-->

                                    <!-- <fieldset class="form-group">
                                        <legend><span>DOCUMENTS VERIFICATION</span></legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Aadhar Number <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Aadhar Number" name="aadhar_number" @if(isset($data['business_info']))
                                                            value="{{$data['business_info']->aadhar_number}}"
                                                            @endif type="number">
                                                </div>
                                            </div>
                                           <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for=""> Business Registration Proof</label>
                                                        <input type="file" class="form-control" name="business_proof">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Personal PAN</label>
                                                    <input type="file" class="form-control" placeholder="Personal PAN" name="pan_id" @if(isset($data['business_info']))
                                                            value="{{$data['business_info']->pan_id}}"
                                                            @endif >
                                                </div>
                                            </div>

                                        </div>

                                    </fieldset> -->

                                    <div class="form-buttons-w text-right">

                                        <button type="submit" name="update-business-profile" class="btn btn-primary" data-request="ajax-submit" data-target='[role="update-business-profile"]'>Update Business Profile</button> 
                                    </div>
                                </form>
                            </div>

                            <!-- from business end -->

                            <!-- bank form -->
                            <div class="tab-pane " id="tab_bank">

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
                                                        <?php
                                                        /*//
                                                        ?>
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
                                                        <?php
                                                        //*/
                                                        ?>
                                                    </tbody>
                                                </table>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    @endforeach

                                </div>

                                @else
                                <form role="update-bankDetails" action="{{url('admin/accounts/update-bank-details')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{encrypt($userData->id)}}" />
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for=""> Beneficiary Name <span class="requiredstar">*</span></label>
                                                <input class="form-control" placeholder="Beneficiary Name" 

                                                type="text" name="beneficiary_name" @if(isset($data['business_info']))
                                                value="{{$data['business_info']->beneficiary_name}}"
                                                @endif>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="form-group">
                                                    <label for=""> Branch IFSC Code <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Branch IFSC Code" 
                                                    type="text" name="ifsc" @if(isset($data['business_info']))
                                                    value="{{$data['business_info']->ifsc}}"
                                                    @endif>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">Account Number <span class="requiredstar">*</span></label>
                                                <input class="form-control" placeholder="Account Number"
                                                type="number" name="account_number"
                                                @if(isset($data['business_info']))
                                                value="{{$data['business_info']->account_number}}"
                                                @endif>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="form-group">
                                                    <label for="">Re-Enter Account Number <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Re-Enter Account Number"
                                                    type="text" name="re_eneter_account_number"
                                                    @if(isset($data['business_info']))
                                                    value="{{$data['business_info']->account_number}}"
                                                    @endif>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-buttons-w text-right">
                                        <button type="submit" name="update-business-profile" class="btn btn-primary" data-request="ajax-submit" data-target='[role="update-bankDetails"]'>Update Bank details</button>
                                    </div>
                                </form>
                                @endif


                            </div>
                            <!-- bank form  end-->



                            <!-- IP White List start -->
                            <!--<div class="tab-pane " id="tab_api">-->
                                <!-- API Overview -->
                            <!--    <div class="element-box">-->
                            <!--        <h5 class="form-header d-flex justify-content-between">-->
                            <!--            API Overview-->
                                        <!--@if(CommonHelper::isServiceActive($userData->id,PAYOUT_SERVICE_ID) || CommonHelper::isServiceActive($userData->id,AEPS_SERVICE_ID) || CommonHelper::isServiceActive($userData->id,UPI_SERVICE_ID))-->
                                        <!--<button class="btn btn-sm btn-primary" id="regenerateApiKeyBtn">{{GENERATE_NEW_API_KEY}}</button>-->
                                        <!--@endif-->
                            <!--        </h5>-->

                            <!--        <div class="element-actions">-->
                            <!--        </div>-->


                            <!--        <div class="table-responsive">-->

                            <!--            <table class="table table-lightborder" style="width:100% !important" id="kt_api_keys_table">-->
                                            <!--begin::Thead-->
                            <!--                <thead>-->
                            <!--                    <tr>-->
                            <!--                        <th>API Keys</th>-->
                            <!--                        <th>Service</th>-->
                            <!--                        <th>Created At</th>-->
                            <!--                        <th>Status</th>-->
                            <!--                    </tr>-->
                            <!--                </thead>-->
                            <!--                <tbody></tbody>-->
                            <!--            </table>-->
                            <!--        </div>-->
                            <!--    </div>-->


                                <!-- over end -->
                                <!-- IP White List start  -->

                            <!--    <div class="element-box">-->
                            <!--        <h5 class="form-header d-flex justify-content-between">-->
                                        <!--IP White List-->

                                        <!--<a href="#" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#kt_modal_create_ipwhite" id="kt_toolbar_primary_button">-->
                                        <!--    Add IP-->
                                        <!--</a>-->
                            <!--        </h5>-->
                            <!--        <div class="table-responsive">-->
                            <!--            <table class="table table-lightborder" style="width: 100% !important;" id="ip_list_table">-->
                                            <!--begin::Thead-->
                            <!--                <thead class=" ">-->
                            <!--                    <tr>-->
                            <!--                        <th class="">IP</th>-->
                            <!--                        <th class="">Service</th>-->
                            <!--                        <th class="">Created At</th>-->
                            <!--                        <th class="">Action</th>-->
                            <!--                    </tr>-->
                            <!--                </thead>-->
                                            <!--end::Thead-->
                                            <!--begin::Tbody-->
                            <!--                <tbody class="">-->
                            <!--                </tbody>-->
                                            <!--end::Tbody-->
                            <!--            </table>-->

                            <!--        </div>-->
                            <!--    </div>-->
                                <!-- IP White List end -->

                                <!-- Webhook Url start -->

                            <!--    <div class="element-box">-->
                            <!--        <h5 class="form-header d-flex justify-content-between">-->
                            <!--            Webhook Url-->
                            <!--            <button class="btn btn-primary" data-target="#exampleModal1" data-toggle="modal" type="button"> @if(isset($data['webhook']) && !empty($data['webhook']))-->
                            <!--                Update-->
                            <!--                @else-->
                            <!--                Add-->
                            <!--                @endif webhook</button>-->
                            <!--        </h5>-->

                            <!--        <div class="table-responsive">-->
                            <!--            <table class="table table-lightborder" style="width: 100% !important;" id="webhook_list_table">-->
                                            <!--begin::Thead-->
                            <!--                <thead class=" ">-->
                            <!--                    <tr>-->
                            <!--                        <th class="">URL</th>-->
                            <!--                        <th class="">Header Key</th>-->
                            <!--                        <th class="">Header Value</th>-->
                            <!--                        <th class="">Created At</th>-->
                            <!--                    </tr>-->
                            <!--                </thead>-->
                                            <!--end::Thead-->
                                            <!--begin::Tbody-->
                            <!--                <tbody class="">-->
                            <!--                </tbody>-->
                                            <!--end::Tbody-->
                            <!--            </table>-->

                            <!--        </div>-->
                            <!--    </div>-->
                            <!--    @if((isset($user_config->is_sdk_enable) && $user_config->is_sdk_enable == '1') || (isset($user_config->is_matm_enable) && $user_config->is_matm_enable == '1'))-->
                            <!--    <div class="element-box">-->
                            <!--        <h5 class="form-header d-flex justify-content-between">-->
                            <!--            SETUP SDK-->
                            <!--            @if((isset($user_config->is_sdk_enable) && $user_config->is_sdk_enable == '1'))-->
                            <!--            <button class="btn btn-sm btn-primary" id="regenerateSDKApiKeyBtn">-->
                            <!--                AEPS Generate Key-->
                            <!--            </button>-->
                            <!--            @endif-->
                            <!--            @if((isset($user_config->is_sdk_enable) && $user_config->is_sdk_enable == '1') && CommonHelper::isServiceActive($userData->id, MATM_SERVICE_ID))-->
                            <!--            <button class="btn btn-sm btn-primary" id="matmRegenerateSDKApiKeyBtn">-->
                            <!--                MATM Generate Key-->
                            <!--            </button>-->
                            <!--            @endif-->
                            <!--        </h5>-->
                            <!--        <div class="element-actions">-->



                            <!--        </div>-->
                            <!--        <div class="table-responsive">-->
                            <!--            <table class="table table-lightborder" style="width: 100% !important;" id="sdk_list_table">-->
                                            <!--begin::Thead-->
                            <!--                <thead class=" ">-->
                            <!--                    <tr>-->
                            <!--                        <th class="">Type</th>-->
                            <!--                        <th class="">Key</th>-->
                            <!--                        <th class="">Created At</th>-->
                            <!--                    </tr>-->
                            <!--                </thead>-->
                                            <!--end::Thead-->
                                            <!--begin::Tbody-->
                            <!--                <tbody class="">-->
                            <!--                @if((isset($user_config->is_sdk_enable) && $user_config->is_sdk_enable == '1') && !empty($user_config->app_id))-->
                            <!--                    <tr>-->
                            <!--                        <td class=""><span>AEPS SDK</span></td>-->
                            <!--                        <td class=""><span id="sdkKey">{{$user_config->app_id}}</span></td>-->
                            <!--                        <td class=""> <span id="sdkKeyUpdatedAt">{{$user_config->app_cred_created_at}}</span></td>-->
                            <!--                    </tr>-->
                            <!--                @endif-->

                            <!--                @if(isset($user_config->is_matm_enable) && $user_config->is_matm_enable == '1' && !empty($user_config->matm_app_id))-->
                            <!--                    <tr>-->
                            <!--                        <td class=""><span>MATM SDK</span></td>-->
                            <!--                        <td class=""><span id="matmKey">{{$user_config->matm_app_id}}</span></td>-->
                            <!--                        <td class=""> <span id="matmKeyUpdatedAt">{{$user_config->matm_app_cred_created_at}}</span></td>-->
                            <!--                    </tr>-->
                            <!--                @endif-->
                            <!--                </tbody>-->
                                            <!--end::Tbody-->
                            <!--            </table>-->

                            <!--        </div>-->
                            <!--    </div>-->
                            <!--    @endif-->

                            <!--</div>-->

                        </div>

                    </div>
                    <!--begin::Modals-->
                    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="exampleModal1" role="dialog" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">
                                        @if(isset($data['webhook']) && !empty($data['webhook']))
                                        Update
                                        @else
                                        Create
                                        @endif
                                        Web hook
                                    </h5>
                                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> &times;</span></button>
                                </div>

                                <form role="update-webhook" action="{{url('user/accounts/webhook-update')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{encrypt($userData->id)}}" />

                                    <div class="modal-body">

                                        <div class="d-flex flex-column mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required fs-5 fw-bold mb-2">URL <span class="requiredstar">*</span></label>

                                            <input class="form-control" placeholder="" name="webhook_url" @if(isset($data['webhook']) && !empty($data['webhook'])) value="{{$data['webhook']->webhook_url}}" @endif type="text">
                                            <!--end::Select-->
                                        </div>
                                        <div class="d-flex flex-column mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required fs-5 fw-bold mb-2">Secret <span class="requiredstar">*</span></label>

                                            <input class="form-control" placeholder="" name="secret" @if(isset($data['webhook']) && !empty($data['webhook'])) value="{{$data['webhook']->secret}}" @endif type="text">
                                            <!--end::Select-->
                                        </div>

                                        <div class="d-flex flex-column mt-2 mb-10 fv-row" style="margin-left: 20px">
                                            <!--begin::Label-->
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="add_header_key_value" id="agreeCheckBox" onClick="agreeFunction()" type="checkbox" @if(isset($data['webhook']) && !empty($data['webhook'])) @if($data['webhook']->header_key || $data['webhook']->header_value)
                                                checked
                                                @endif
                                                @endif />
                                                Add Header</label>
                                            <!--end::Select-->
                                        </div>

                                        <div class="d-flex flex-column mb-10 fv-row headerKey" id="headerKey" @if(isset($data['webhook']) && !empty($data['webhook'])) @if($data['webhook']->header_key != '' || $data['webhook']->header_value != '')
                                            style="display:block !important"
                                            @else
                                            style="display:none !important"
                                            @endif
                                            @else
                                            style="display:none !important"
                                            @endif>
                                            <label class="col-form-label headerKey" for=""> Header Key</label>
                                            <input class="form-control headerKey" placeholder="" @if(isset($data['webhook']) && !empty($data['webhook'])) value="{{$data['webhook']->header_key}}" @endif type="text" name="headerKey">
                                        </div>

                                        <div class="d-flex flex-column mb-10 fv-row headerValue" id="headerValue" @if(isset($data['webhook']) && !empty($data['webhook'])) @if($data['webhook']->header_key != '' || $data['webhook']->header_value != '')
                                            style="display:block"

                                            @else
                                            style="display:none !important"
                                            @endif
                                            @else
                                            style="display:none !important"
                                            @endif>
                                            <label class="col-form-label headerValue" for=""> Header value <span class="requiredstar">*</span></label>

                                            <input class="form-control headerValue" placeholder="" type="text" @if(isset($data['webhook']) && !empty($data['webhook'])) value="{{$data['webhook']->header_value}}" @endif name="headerValue">

                                        </div>


                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Cancel</button>
                                        <button class="btn btn-primary" id="webhookId" type="submit" data-request="ajax-submit" data-target='[role="update-webhook"]' data-callbackfn="setWebhookCallback" data-targetform='kt_modal_create_webhook_form'>@if(!empty($data['webhook'])) Update @else Add @endif</button>
                                    </div>
                            </div>
                            </form>
                        </div>
                    </div>

                    @include(USER.'.payout.modals.ip')
                    <!--begin::Modal - Create Api Key-->

                    @include(USER.'.payout.modals.apikey')

                </div>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{asset('user-js/user-profile.js?v=1.0.2')}}"></script>
<script type="text/javascript">
    function businessCategory(id) {
        $('#b_sub_cate_id').css('display', 'none');
        $id = id.value;
        $('#businesssubcategory').html('');
        $.get("{{custom_secure_url('getbusiness_sub_category')}}/" + $id, function(data, status) {
            if (data.status) {
                $('#b_sub_cate_id').css('display', 'block');
                $('#businesssubcategory').html(data.option);
            }
        });
    }

    $(document).ready(function() {
        $('#b_sub_cate_id').css('display', 'none');
        $id = $('#business_category_id').val();
        $('#businesssubcategory').html('');
        if (isEmpty($id)) {
            $.get("{{custom_secure_url('getbusiness_sub_category')}}/" + $id, function(data, status) {
                if (data.status) {
                    $('#b_sub_cate_id').css('display', 'block');
                    $('#businesssubcategory').html(data.option);
                }
            });
        }
    });

    function isEmpty(val) {
        return (val === undefined || val == null || val.length <= 0) ? false : true;
    }

    function agreeFunction() {
        if ($('#agreeCheckBox').prop('checked')) {
            $('.headerKey').show();
            $('.headerValue').show();
        } else {
            $('.headerKey').hide();
            $('.headerValue').hide();
        }
    }

    // Api Key for
    $(document).ready(function() {
        var url = "{{custom_secure_url('user/fetch')}}/ipWhiteLists/0";
        var onDraw = function() {};
        var options = [{
                data: 'ip',
            },
            {
                data: 'service_id',
                render: function(data, type, full, meta) {
                    if (full.service != undefined && full.service != null) {
                        return '<span class="badge badge-primary">' + full.service.service_name + '</span>';
                    } else {
                        return '<span class="badge badge-danger">NA</span>';
                    }
                }
            },
            {
                data: 'new_created_at'
            },
            {
                "data": "action",
                render: function(data, type, full, meta) {
                    var $id = full.id;
                    var $btn = '<a href="javascript:void(0)" onclick="deleteIp(' + $id + ');" class="edit btn btn-danger btn-sm"><i class="os-icon os-icon-trash-2"></i></a>';
                    return $btn;
                }
            }
        ];

        datatableSetup(url, options, onDraw, "#ip_list_table");

        var url = "{{custom_secure_url('user/fetch')}}/webHookLists/0";
        var options = [{
                data: 'webhook_url'
            },
            {
                data: 'header_key'
            },
            {
                data: 'header_value'
            },
            {
                data: 'new_created_at'
            },
        ];

        datatableSetup(url, options, onDraw, "#webhook_list_table");
    });


    var hash = window.location.hash;
    $('ul a[href="' + hash + '"]').trigger('click');

    function tabMenu(id) {
        $('ul a[href="#' + id + '"]').trigger('click');
        //$('ul').find('href="#'+id+'"').addClass('active');
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
</script>
@endsection