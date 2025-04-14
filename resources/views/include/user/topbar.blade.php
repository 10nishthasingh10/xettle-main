<?php
$serviceData = \App\Models\UserService::leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
    ->select('global_services.service_id', 'global_services.service_name', 'user_services.*', 'global_services.url', 'global_services.service_slug')
    ->where('user_services.user_id', Auth::user()->id)
    ->where('user_services.is_active', '1')
    ->get();
$allService = \App\Models\Service::where('is_active', '1')->get();
$segmentUri = \App\Models\Service::select('id')
    ->where('service_slug', Request::segment(3))
    ->first();

$aepsTodayBusiness =  DB::table('aeps_transactions')
->whereDate('created_at', date('Y-m-d'))
->where(['transaction_type' => 'cw', 'user_id' => Auth::user()->id, 'status' => 'success'])
->sum('transaction_amount');
$unsettledThresholdAmount = @DB::table('user_config')
                        ->select("threshold as amt")
                        ->where('user_id', Auth::user()->id)
                        ->first()->amt;
?>
<div class="top-bar color-scheme-light">
    <div class="logo-w menu-size">
        @if(Auth::user()->is_admin == 1)
        <a class="logo" href="{{url('/admin/dashboard')}}">
            @else
            <a class="logo" href="{{url('/user/dashboard')}}">
                @endif
            
                <div class="logo-label">
                    <img alt=""  style="width:200px;height:45px !important" src="{{asset('')}}images/logo.png" />
                </div>
            </a>
    </div>
    <div class="fancy-selector-w wrapp">


        @include('include.user.serviceautoselect')
        <div class="fancy-selector-options">
            @if(Request::is('user') || Request::is('user') || Request::is('user/*'))
            <a href="javascript:void(0)">
                <div class="fancy-selector-option active">
                    @else
                    <a href="{{url('/')}}/user/dashboard">
                        <div class="fancy-selector-option ">
                            @endif
                            <div class="fs-img">
                                <img alt="" src="{{asset('')}}/media/logos/wallet.jpg" />
                            </div>
                            <div class="fs-main-info">
                                <div class="fs-name">
                                    Primary Account
                                </div>
                                <div class="fs-sub">
                                    <span>Balance:</span><strong>

                                    @if((Auth::user()->transaction_amount + Auth::user()->locked_amount - $unsettledThresholdAmount) > 0)
                                ₹{{number_format((Auth::user()->transaction_amount+Auth::user()->locked_amount) - $unsettledThresholdAmount,2)}}
                                @else
                                ₹{{number_format(0,2)}}
                                @endif</strong>
                                </div>
                            </div>
                        </div>
                    </a>

                    @foreach($serviceData as $myservice)
                    @if($myservice->service_slug == 'payout' || $myservice->service_slug == 'aeps' || $myservice->service_slug == 'upi_collect' || $myservice->service_slug == 'smart_collect' || $myservice->service_slug == 'va' || $myservice->service_slug == 'validate'|| $myservice->service_slug == 'recharge'|| $myservice->service_slug == 'dmt')
                    <?php
                    $url = "javascript:void(0)";
                    if ($myservice->service_slug == 'aeps') {
                        $url = "aeps";
                    } else if ($myservice->service_slug == 'upi_collect') {
                        $url = "upi";
                    } else if ($myservice->service_slug == 'smart_collect') {
                        $url = "collect";
                    } else if ($myservice->service_slug == 'validate') {
                        $url = "validation";
                    } else if ($myservice->service_slug == 'va') {
                        $url = "va";
                    }else if ($myservice->service_slug == 'recharge') {
                        $url = "recharge";
                    }else if ($myservice->service_slug == 'dmt') {
                        $url = "dmt";
                    } else {
                        $url = $myservice->service_slug;
                    }
                    ?>

                    @if($url == "javascript:void(0)")
                    <a href="aeps">
                        @else
                        <a href="{{url($url)}}">
                            @endif
                            @if(Request::segment(1) == $url)
                            <div class="fancy-selector-option active">
                                @else
                                <div class="fancy-selector-option">
                                    @endif
                                    <div class="fs-img">
                                        <!--<img alt="" src="{{asset('')}}/media/logos/{{$myservice->url}}">-->
                                    </div>
                                    <div class="fs-main-info">
                                        <div class="fs-name">
                                            {{$myservice->service_name}}
                                        </div>
                                        @if(CommonHelper::isServiceActive(Auth::user()->id,$myservice->service_id))
                                        <div class="fs-sub">
                                            @php
                                            $data = CommonHelper::callBackUPITotalAmount(Auth::user()->id);
                                            $totalAmount = $data['amount'];
                                            @endphp
                                            @if($url == 'upi')
                                            <span>Balance:</span> <strong>₹{{$totalAmount}}</strong>
                                            @elseif($url == 'va')
                                            <span>Balance:</span> <strong>&#8377;{{(CommonHelper::callBackVirtualAccountTotalAmount(Auth::user()->id))['amount']}}</strong>
                                            @elseif($url == 'verification')
                                            <span></span>
                                            @elseif($url == 'aeps')
                                            <span>Balance:</span> <strong>₹{{$aepsTodayBusiness}}</strong>
                                            @elseif($url == 'collect')
                                            <span>Balance:</span> <strong>&#8377;{{(CommonHelper::callBackSmartCollectTotalAmount(Auth::user()->id))['amount']}}</strong>
                                            @elseif($url == 'payout')
                                            <?php
                                            $lockedAmount = isset(\DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum) ? \DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum : 0;
                                            ?>
                                            <span>Balance:</span> <strong>₹{{CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->transaction_amount + $lockedAmount}}</strong>
                                            @else
                                            @if(CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->locked_amount > 0)
                                            <?php
                                            $lockedAmount = CommonHelper::getServiceAccount(Auth::user()->id, $myservice->service_id)->locked_amount;
                                            ?>
                                            @else
                                            <?php
                                            $lockedAmount = 0;
                                            ?>
                                            @endif
                                            <span>Balance:</span> <strong>₹{{CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->transaction_amount + $lockedAmount}}</strong>
                                            @endif

                                        </div>
                                        @endif
                                    </div>
                                </div>
                        </a>
                        @endif
                        @endforeach

                        <div class="fancy-selector-actions text-right">
                            <a class="btn btn-primary" href="#" data-target="#onboardingFeaturesModal" data-toggle="modal"><i class="os-icon os-icon-ui-22"></i><span>Add Service</span></a>
                        </div>
                </div>
        </div>
        <!--------------------
        START - Top Menu Controls
        -------------------->
        <div class="top-menu-controls">
            <div class="element-search autosuggest-search-activator">
                <input placeholder="Search by UTR..." type="text">
            </div>
            <!--------------------
        START - Messages Link in secondary top menu
        -------------------->
            <div class="messages-notifications os-dropdown-trigger os-dropdown-position-left">
                <i class="os-icon os-icon-mail-14"></i>
                <div class="new-messages-count">
                    0
                </div>
            </div>
            <!--------------------
        END - Messages Link in secondary top menu
        -------------------->
            <!--------------------
        START - Settings Link in secondary top menu
        -------------------->
            <div class="top-icon top-settings os-dropdown-trigger os-dropdown-position-left">
                <i class="os-icon os-icon-ui-46"></i>
                <div class="os-dropdown">
                    <div class="icon-w">
                        <i class="os-icon os-icon-ui-46"></i>
                    </div>
                    <ul>
                        <li onclick="tabMenu();">
                            <a href="{{url('user/profile')}}"><i class="os-icon os-icon-user"></i><span>My Profile</span></a>
                        </li>
                        <li onclick="tabMenu('tab_sales');">
                            <a href="{{url('user/profile#tab_sales')}}"><i class="os-icon os-icon-home"></i><span>Business Profile</span></a>
                        </li>
                        <li onclick="tabMenu('tab_bank');">
                            <a href="{{url('user/profile#tab_bank')}}"><i class="os-icon os-icon-coins-4"></i><span>Bank Details</span></a>
                        </li>
                        <li onclick="tabMenu('tab_api');">
                            <a href="{{url('user/profile#tab_api')}}"><i class="os-icon os-icon-ui-09"></i><span>Integrations</span></a>
                        </li>
                        <li>
                            <a href="{{url('logout')}}"><i class="os-icon os-icon-signs-11"></i><span>Logout</span></a>
                        </li>
                        <!-- <li>
                <a href="users_profile_small.html"><i class="os-icon os-icon-ui-15"></i><span>Cancel Account</span></a>
                </li> -->
                    </ul>
                </div>
            </div>
            <!--------------------
        END - Settings Link in secondary top menu
        -------------------->
            <!--------------------
        START - User avatar and menu in secondary top menu
        -------------------->
            <div class="logged-user-w d-lg-none d-md-block">
                <div class="logged-user-i">
                    @if(isset(Auth::user()->avatar))
                    <div class="avatar-w">
                        <img alt="" src="{{url('public/uploads/profile/')}}/{{Auth::user()->avatar}}">
                    </div>
                    @else
                    <p data-letters="{{ CommonHelper::shortName(Auth::user()->id) }}"></p>
                    @endif
                    <div class="logged-user-menu color-style-bright">
                        <div class="logged-user-avatar-info">
                            @if(isset(Auth::user()->avatar))
                            <div class="avatar-w">
                                <img alt="" src="{{url('public/uploads/profile/')}}/{{Auth::user()->avatar}}">
                            </div>
                            @else
                            <p data-letters="{{ CommonHelper::shortName(Auth::user()->id) }}"></p>
                            @endif
                            <div class="logged-user-info-w">
                                <div class="logged-user-name">
                                    {{Auth::user()->name}}
                                </div>
                                <div class="logged-user-role">
                                    @if(Auth::user()->is_admin)
                                    Admin
                                    @else
                                    User
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="bg-icon">
                            <i class="os-icon os-icon-wallet-loaded"></i>
                        </div>
                        <ul>
                            <li>

                                <a href="{{url('user/profile')}}"><i class="os-icon os-icon-user-male-circle2"></i><span>Profile Details</span></a>
                            </li>
                            <li>
                                <a href="{{url('logout')}}"><i class="os-icon os-icon-signs-11"></i><span>Logout</span></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--------------------
        END - User avatar and menu in secondary top menu
        -------------------->
        </div>
        <!--------------------
        END - Top Menu Controls
        -------------------->
    </div>




    <!-- model -->
    <div aria-hidden="true" class="onboarding-modal modal fade animated" id="onboardingFeaturesModal" style="z-index: 22222;" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-centered" role="document">
            <div class="modal-content text-center">
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
                <div class="onboarding-media"></div>
                <div class="onboarding-content with-gradient">
                    <h4 class="onboarding-title">
                        Add Services
                    </h4>

                    <div class="table-responsive">
                        @if(!CommonHelper::isKycUpdated(Auth::user()->id))
                        <p style="font-size:15px; color:red;">Your KYC is pending . Please update your KYC form. <a href="{{url('user/profile#tab_sales')}}" onclick="tabMenu('tab_api');" class="mr-2 mb-2 btn btn-link">Click Here </a></p>
                        @endif
                        <!--------------------
                        START - Basic Table
                        -------------------->
                        <table class="table ">

                            <tbody>
                                @foreach($allService as $service)
                                <tr class="img_title">
                                    <td>
                                        <img alt="" src="{{asset('')}}/media/logos/{{$service->url}}">
                                    </td>
                                    <td>
                                        <div class="fs-main-info-add">
                                            <div class="fs-name">
                                                {{$service->service_name}}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fs-extra-info-add">

                                            @if(CommonHelper::isServiceActive(Auth::user()->id,$service->service_id))
                                                <strong>
                                                    <span class="mr-2 mb-2 p-2 text-light badge bg-success">Activated</span>
                                                </strong>
                                            @else
                                                @if(CommonHelper::serviceStatusCheck(Auth::user()->id,$service->service_id) == 'Pending')
                                                    <span class="mr-2 mb-2 p-2 text-light badge bg-info">Pending</span>
                                                @else
                                                    @if(CommonHelper::isKycUpdated(Auth::user()->id))
                                                        @if ($service->is_activation_allowed == '1')
                                                        <span class="mr-2 mb-2 btn btn-primary" onclick="serviceActivate('{{$service->service_id}}')">Activate</span>
                                                        @else
                                                            <span class="mr-2 mb-2 p-2 text-light badge bg-info">Comming Soon</span>
                                                        @endif
                                                    @else
                                                    @endif
                                                @endif
                                            @endif

                                        </div>
                                    </td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>