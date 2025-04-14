@extends('layouts.admin.app')
@section('title','Profile')
@section('content')

@section('style')
<style type="text/css">
    .lettersProfile:before {
        height: 80px;
        width: 80px;
        line-height: 80px;
        font-size: 2.7rem;
        line-height: 1.8em;

    }
</style>
@endsection
<div class="content-i">
    <div class="content-box">
        <div class="row">
            <div class="col-md-4">
                <div class="ecommerce-customer-info">
                    <div class="ecommerce-customer-main-info">
                    @if(isset($userData))
                        <p data-letters="{{ CommonHelper::shortName($userData->id) }}" class="lettersProfile"></p>
                        @else
                            NA
                            @endif

                        <div class="ecc-name">
                            @if(isset($userData))
                            {{$userData->name}}
                            @else
                            NA
                            @endif
                        </div>
                    </div>
                    <div class="ecommerce-customer-sub-info">
                        <div class="row ecc-sub-info-row">
                            <div class="col-6 sub-info-label">
                                Transactional Amount
                            </div>
                            <div class="col-6 sub-info-value">
                            @if(isset($userData))
                                            {{ CommonHelper::numberFormat($userData->transaction_amount + $userData->locked_amount) }}
                                            @else
                                            0
                                            @endif

                            </div>
                        </div>
                    </div>
                    <div class="os-tabs-controls">
                        <ul class="nav nav-tabs" id="myProfileTab">
                            <li class="nav-item">
                                <a class="nav-link {{$isActiveTab}}" data-toggle="tab" href="#profile">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#business_profile_show">Business Profile</a>
                            </li>
                            <!--<li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#bankDetails"> Bank Details</a>
                            </li> -->
                        </ul>
                    </div>
                    <div class="tab-content">
                    

                        <div class="tab-pane {{$isActiveTab}}" id="profile">
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Full Name
                                </div>
                                <div class="col-6 sub-info-value">
                                @if(isset($userData))
                                            {{ $userData->name }}
                                            @else
                                            NA
                                            @endif
                                
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    E-Mail Address
                                </div>
                                <div class="col-6 sub-info-value">
                                @if(isset($userData))
                                            {{ $userData->email }}
                                            @else
                                            NA
                                            @endif
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Contact
                                </div>
                                <div class="col-6 sub-info-value">
                                @if(isset($userData))
                                            {{ $userData->mobile }}
                                            @else
                                            NA
                                            @endif
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Number
                                </div>
                                <div class="col-6 sub-info-value">
                                @if(isset($userData))
                                            {{ $userData->account_number }}
                                            @else
                                            NA
                                            @endif
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
                                    {{$business_info->state}}
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
                                    Aadhar Number
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$business_info->aadhar_number}}
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
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Coordinator
                                </div>
                                <div class="col-6 sub-info-value">
                                    @if(isset($account_coordinator_data->name))

                                    {{$account_coordinator_data->name}} ({{$account_coordinator_data->mobile}})
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-value">
                                    <span style="color:red">Please update business profile.</span>
                                </div>
                            </div>
                            @endif
                        </div>

                <!--    <div class="tab-pane " id="bankDetails">
                            @if(isset($data['business_info']) && !empty($data['business_info']))
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Beneficiary Name
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$data['business_info']->beneficiary_name}}
                                </div>
                            </div>
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    IFSC
                                </div>
                                <div class="col-6 sub-info-value">
                                    {{$data['business_info']->ifsc}}
                                </div>
                            </div>
                            <d iv class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-label">
                                    Account Number
                                </div>
                                <div class="sub-info-value">
                                    {{$data['business_info']->account_number}}
                                </div>
                            </d>
                            @else
                            <div class="row ecc-sub-info-row">
                                <div class="col-6 sub-info-value">
                                    <span style="color:red">Please update bank details.</span>
                                </div>
                            </div>
                            @endif
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="element-box">

                    <div class="os-tabs-w">
                        <div class="os-tabs-controls">
                            <ul class="nav nav-tabs bigger">
                              <!--  <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tab_overview">Personal info </a>
                                </li> -->
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tab_sales">Business details</a>
                                </li>
                              <!-- <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tab_bank">Bank details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tab_api">Integrations</a>
                                </li> -->
                            </ul>

                        </div>
                        <div class="tab-content">
                            
                            
                            <!-- personal form  end-->
                            <!-- from business  -->
                            <?php $data['business_info']->is_kyc_updated = 0 ; ?>
                            <div class="tab-pane" id="tab_sales">
                                <form role="update-business-profile" action="{{url('admin/accounts/user-business-profile-update')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{encrypt($userData->id)}}" />
                                    <fieldset class="form-group">
                                        <legend><span>CONTACT INFO</span></legend>
                                        <div class="row">
                                            <input type="hidden" name="update_by_user_id" value="{{encrypt(Auth::user()->id)}}"/>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Contact Name <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Contact Name" name="name" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                    readonly="readonly"
                                                    @endif
                                                    @if(isset($data['business_info']))

                                                    value="{{$data['business_info']->name}}"
                                                    @else
                                                    value="{{$userData->name}}" @endif type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Contact Number <span class="requiredstar">*</span></label>
                                                    <input class="form-control" placeholder="Contact Number" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                    readonly="readonly"
                                                    @endif

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
                                                    <input type="email" name="contact_email" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                    readonly="readonly"
                                                    @endif

                                                    @if(isset($data['business_info']))
                                                    value="{{$data['business_info']->email}}"
                                                    @else
                                                    value="{{$userData->email}}"
                                                    @endif
                                                    class="form-control" placeholder="Contact Number" required="required">
                                                </div>
                                            </div>

                                        </div>

                                    </fieldset>

                                    <fieldset class="form-group">
                                        <legend><span>BUSINESS OVERVIEW</span></legend>
                                        <div class="row">

                                            <div class="col-sm-6">
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
                                                    <select name="business_category" class="form-control" id="business_category_id" onchange="businessCategory(this);" data-control="select2" data-hide-search="true" data-placeholder="Select a Category..." class="form-select form-select-solid" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                        disabled="disabled"
                                                        @endif>
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
                                                    <select class="form-control" name="business_subcategory" id="businesssubcategory" data-control="select2" data-hide-search="true" data-placeholder="Select a Sub Category..." class="form-select form-select-solid" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                        disabled="disabled"
                                                        @endif>
                                                        <option value="">Select a Business Sub Category...</option>
                                                    </select>
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Website URL</label>
                                                    @if(!empty($data['business_info']) && $data['business_info']->is_kyc_updated)
                                                    <input type="text" class="form-control" value="{{$data['business_info']->web_url}}" readonly>
                                                    @else
                                                    <input type="text" name="web_url" ) @if(isset($data['business_info'])) value="{{$data['business_info']->web_url}}" @endif class="form-control" placeholder="Website URL">
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">App URL</label>
                                                    @if(!empty($data['business_info']) && $data['business_info']->is_kyc_updated)
                                                    <input type="text" class="form-control" value="{{$data['business_info']->app_url}}" readonly>
                                                    @else
                                                    <input type="text" name="app_url" ) @if(isset($data['business_info'])) value="{{$data['business_info']->app_url}}" @endif class="form-control" placeholder="App URL">
                                                    <div class="help-block form-text with-errors form-control-feedback"></div>
                                                    @endif
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
                                                    <input class="form-control" placeholder="Authorised Signatory PAN" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                    readonly="readonly"
                                                    @endif
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
                                                        <input class="form-control" placeholder="PAN Owner’s Name" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                        readonly="readonly"
                                                        @endif

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
                                                    <input class="form-control" placeholder="Billing Label " name="billing_label" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                    readonly="readonly"
                                                    @endif

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
                                                        <textarea class="form-control" name="address" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1') readonly="readonly" @endif>@if(isset($data['business_info'])){{$data['business_info']->address}}@endif</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Pincode <span class="requiredstar">*</span></label>
                                                    <input class="form-control" name="pincode" placeholder="Pin code" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                    readonly="readonly"
                                                    @endif
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
                                                        <input class="form-control" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                        readonly="readonly"
                                                        @endif
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
                                                    <select name="state" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a State..." class="form-select form-select-solid" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1') disabled @endif>
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
                                                        <input class="form-control" name="gstin" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                        readonly="readonly"
                                                        @endif
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
                                                        <input class="form-control" name="business_mcc" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1')
                                                        readonly="readonly"
                                                        @endif
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

                                    <fieldset class="form-group">
                                        <legend><span>Account Manager</span></legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Account Manager <span class="requiredstar">*</span></label>
                                                    <select name="acc_manager_id" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Account Manager..." class="form-select form-select-solid" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1') disabled @endif>
                                                        <option value="">Select a Account Manager...</option>
                                                        @foreach($data['account_manager'] as $account_manager)
                                                        @if($account_manager->type == 'account_manager')
                                                        <option value="{{$account_manager->id}}" @if(isset($data['business_info'])) @if($data['business_info']->acc_manager_id == $account_manager->id)
                                                            selected
                                                            @endif
                                                            @endif
                                                            @endif
                                                            >{{$account_manager->name}} ({{$account_manager->mobile}})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for=""> Account Coordinator <span class="requiredstar">*</span></label>
                                                    <select name="acc_coordinator_id" class="form-control" data-control="select2" data-hide-search="true" data-placeholder="Select a Account Manager..." class="form-select form-select-solid" @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1') disabled @endif>
                                                        <option value="">Select a Account Coordinator...</option>
                                                        @foreach($data['account_manager'] as $account_manager)
                                                        @if($account_manager->type == 'account_coordinator')
                                                        <option value="{{$account_manager->id}}" @if(isset($data['business_info'])) @if($data['business_info']->acc_coordinator_id == $account_manager->id)
                                                            selected
                                                            @endif
                                                            @endif
                                                            @endif
                                                            >{{$account_manager->name}} ({{$account_manager->mobile}})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>

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

                                        @if(isset($data['business_info']) && $data['business_info']->is_kyc_updated == '1') @else <button type="submit" name="update-business-profile" class="btn btn-primary" data-request="ajax-submit" data-target='[role="update-business-profile"]'>Update Business Profile</button> @endif
                                    </div>
                                </form>
                            </div>
                            
                            
                        </div>
                    </div>

                </div>
                <div class="element-wrapper">
                    <div class="element-box">
                        <div class="element-info">
                            <div class="element-info-with-icon">
                                    <div class="element-info-icon">
                                        <div class="os-icon os-icon-ui-46"></div>
                                    </div>
                                    <div class="element-info-text">
                                        <h5 class="element-inner-header">
                                        Payout Root
                                        </h5>
                                    </div>
                            </div>
                        </div>
                        <fieldset class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>API Payout Root</label>
                                        <select class="form-control" name="api_payout_root">
                                            <option></option>
                                            @foreach($payout_routes as $route)
                                                <option value="{{$route->integration_id}}" @if(!empty($user_config['api_integration_id']) && $route->integration_id==$user_config['api_integration_id']) {{'selected'}}@endif>{{$route->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Web Payout Root</label>
                                        <select class="form-control" name="web_payout_root">
                                            <option></option>
                                            @foreach($payout_routes as $route)
                                                <option value="{{$route->integration_id}}" @if(!empty($user_config['web_integration_id']) && $route->integration_id==$user_config['web_integration_id']) {{'selected'}}@endif>{{$route->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        
                                        <button class="btn btn-primary" id="update-route">Update</button>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
            </div>
        </div>
    </div>
</div>

@section('scripts')

<script type="text/javascript">
    function deleteIp(id) {
        $.ajax({
            url: "{{custom_secure_url('api/v1/accounts/ipDelete')}}/" + id,
            type: 'GET',
            success: function(res) {
                swal.fire("Great Job", "Ip deleted Successfull", "success");
                $('#ip_list_table').DataTable().ajax.reload();
            }
        });
    }

    $('#update-route').click(function(){
        var api_root = $('select[name="api_payout_root"]').val();
        var web_root = $('select[name="web_payout_root"]').val();
        var id = "{{encrypt($userData->id)}}";
        $.ajax({
            url:"{{custom_secure_url('admin/update-root/')}}",
            type:"POST",
            data:{
                "_token": "{{ csrf_token() }}",
                "api_root":api_root,
                "web_root":web_root,
                "user_id": id
            },
            success:function(response)
            {
                console.log(response.status);
                if(response.status=='SUCCESS')
                {
                    swal.fire("Great Job", response.message, "success").then((result) => {
                            
                            location.reload();
                        });;
                }else
                {
                    swal.fire("Failed", response.message, "error");
                }
            }
        })
    })

    function deActivateApiKey(id) {
        $.ajax({
            url: "{{custom_secure_url('api/v1/accounts/deActivateKey')}}/" + id,
            type: 'GET',
            success: function(res) {
                //swal.fire("Great Job", "DeActivate Key  Successfully", "success");
                //$('#kt_api_keys_table').DataTable().ajax.reload();
            }
        });
    }

    function businessCategory(id) {
        $('#b_sub_cate_id').css('display', 'none');
        $id = id.value;
        $('#businesssubcategory').html('');
        $.get("{{custom_secure_url('getbusiness_sub_category_by_user_id')}}/" + $id+"/{{$userData->id}}", function(data, status) {
            if (data.status) {
                $('#b_sub_cate_id').css('display', 'block');
                $('#businesssubcategory').html(data.option);
            }
        });
    }

    function regenerateApiKey() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Your old API keys will be disabled",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#kt_api_keys_table').DataTable().ajax.reload();
                $('#kt_modal_create_api_key').modal('show');
                $('#keydata').hide();
                $('#kt_modal_create_api_key_submit').show();
                $('#kt_modal_create_api_key').modal('show');
            }
        })
    }


    $(".toggle-password").click(function() {

        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });

    $(document).ready(function() {
        $('#b_sub_cate_id').css('display', 'none');
        $id = $('#business_category_id').val();
        $('#businesssubcategory').html('');
        if (isEmpty($id)) {
            $.get("{{custom_secure_url('getbusiness_sub_category_by_user_id')}}/" + $id+"/{{$userData->id}}", function(data, status) {
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
</script>
<script type="text/javascript">
    // Api Key for
    $(document).ready(function() {
        var url = "{{custom_secure_url('user/fetch')}}/apiKeys/0";
        var onDraw = function() {};
        var options = [{
                data: 'client_key'
            }, {
                data: 'service_id',
                render: function(data, type, full, meta) {
                    if (full.service != undefined && full.service != null) {
                        return '<span class="badge badge-primary">' + full.service.service_name + '</span>';
                    } else {
                        return '<span class="badge badge-danger">NA</span>';
                    }
                }
            }, {
                data: 'new_created_at'
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    var $id = full.id;
                    if (full.is_active == 1) {
                        var btn = '  <label class="switch" onChange="deActivateApiKey(' + $id + ');"><input type="checkbox" checked><span class="slider round"></span></label>';
                    } else {
                        var btn = '  <label class="switch" onChange="deActivateApiKey(' + $id + ');"><input type="checkbox" ><span class="slider round"></span></label>';
                    }
                    return btn;
                }
            }
        ];

        datatableSetup(url, options, onDraw, "#kt_api_keys_table");


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
        var onDraw = function() {};
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

        datatableSetupForWebhook(url, options, onDraw, "#webhook_list_table");
    });


    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: false,
            stateSave: true,
            "info": false,
            "searching": false,
            "bLengthChange": false,
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
                },
                beforeSend: function() {},
                complete: function() {},
                error: function(response) {
                    console.log(response);
                }
            },
            columns: datas
        };

        $.each(element, function(index, val) {
            options[index] = val;
        });
        var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
        return DT;
    }

    function datatableSetupForWebhook(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: false,
            paginate: false,
            stateSave: true,
            "info": false,
            "searching": false,
            "bLengthChange": false,
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
                },
                beforeSend: function() {},
                complete: function() {},
                error: function(response) {
                    console.log(response);
                }
            },
            columns: datas
        };

        $.each(element, function(index, val) {
            options[index] = val;
        });
        var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
        return DT;
    }
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


@endsection