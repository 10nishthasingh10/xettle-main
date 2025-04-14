<!DOCTYPE html>
<html>

<head>

    <title>Xettle Technologies @yield('title')</title>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{custom_secure_url('')}}">
    <link href="{{url('img/favicon.ico')}}" rel="shortcut icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://kit-pro.fontawesome.com/releases/v5.15.2/css/pro.min.css">


    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link href="{{asset('bower_components/select2/dist/css/select2.min.css')}}" rel="stylesheet">
    <!-- <link href="{{asset('bower_components/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet"> -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="{{asset('bower_components/dropzone/dist/dropzone.css')}}" rel="stylesheet">
    <link href="{{asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">

    <link href="{{asset('bower_components/fullcalendar/dist/fullcalendar.min.css')}}" rel="stylesheet">
    <link href="{{asset('bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css')}}" rel="stylesheet">
    <link href="{{asset('bower_components/slick-carousel/slick/slick.css')}}" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css" rel="stylesheet">

    <link href="{{asset('css/main.css?version=4.5.1')}}" rel="stylesheet">
    <link href="{{asset('bower_components/common.css')}}" rel="stylesheet">

    <style type="text/css">
        .requiredstar {
            color: red;
        }

        .help-block {
            color: red;
        }

        td.details-control {
            background: url("{{custom_secure_url('')}}/public/images/details_open.png") no-repeat center center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            background: url("{{custom_secure_url('')}}/public/images/details_close.png") no-repeat center center;
        }
    </style>
    <style>
    </style>
    @yield('style')
</head>

<body class="menu-position-side menu-side-left">

    <div class="xtl_loader_container d-none" id="xtl_loader_container">
        <div class="xtl_loader_content">
            <div class="image_box">
                <img src="{{asset('img/xettle_logo_new.png')}}" class="w-100" />
            </div>
            <div class="xtl_loading_text">
                Loading...
            </div>
        </div>
    </div>

    @if(Request::is('admin/userprofile/*'))

    <div class="all-wrapper with-side-panel solid-bg-all">

        @else
        <div class="all-wrapper solid-bg-all">
            @endif
            <div class="search-with-suggestions-w" style="left: 77%;top: 1%;">
                <div class="search-with-suggestions-modal">

                    <div class="element-search">
                        <input class="search-suggest-input" id="filterUTR" minlength="12" placeholder="Search by UTR..." type="text">
                        <div class="close-search-suggestions">
                            <i class="os-icon os-icon-x" id="loadingFilter"></i>
                        </div>
                    </div>
                    <div class="responseFilter">
                    </div>
                </div>
            </div>
            <!--------------------
      START - Top Bar
      -------------------->

            <!--------------------
      END - Top Bar
      -------------------->

            <div class="layout-w">
                <!--------------------
        START - Mobile Menu
        -------------------->
                <div class="menu-mobile menu-activated-on-click color-scheme-dark">
                    <div class="mm-logo-buttons-w">
                        <a class="logo text-white" href="">
                            <div class="logo-element"></div>
                            <div class="logo-label">
                                Xettle
                            </div>
                        </a>
                        <div class="mm-buttons">
                            <div class="content-panel-open">
                                <div class="os-icon os-icon-grid-circles"></div>
                            </div>
                            <div class="mobile-menu-trigger">
                                <div class="os-icon os-icon-hamburger-menu-1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="menu-and-user d-md-block ">
                        <ul class="main-menu">
                            <li class="">
                                <a href="{{url('admin/dashboard')}}">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span>Dashboard</span>
                                </a>
                                <!-- <ul class="sub-menu">
                                    <li>
                                        <a href="{{url('admin/dashboard')}}">Dashboard</a>
                                    </li>
                                </ul> -->
                            </li>

                            @if(Auth::user()->hasRole('accountant') || Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('finance'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-documents-03"></div>
                                    </div>
                                    <span>Reports</span>
                                </a>
                                <ul class="sub-menu">
                                    @if(Auth::user()->hasRole('accountant') || Auth::user()->hasRole('super-admin'))
                                    <li>
                                        <a href="{{url('admin/allreports')}}">All Reports</a>
                                    </li>
                                    <li>
                                        <a href="{{url('admin/reports/payout')}}">Payout</a>
                                    </li>
                                    <li>
                                        <a href="{{url('admin/reports/aeps')}}">Aeps</a>
                                    </li>
                                    <li>
                                        <a href="{{url('admin/reports/upi')}}">UPI</a>
                                    </li>
                                    <li>
                                        <a href="{{url('admin/reports/van')}}">VAN</a>
                                    </li>
                                    <li>
                                        <a href="{{url('admin/reports/recharge')}}">Recharge</a>
                                    </li>
                                    <li>
                                        <a href="{{url('admin/reports/validation')}}">Validation</a>
                                    </li>
                                    <li>
                                        <a href="{{url('admin/reports/dmt')}}">DMT</a>
                                    </li>
                                    @endif
                                    @if(Auth::user()->hasRole('accountant') || Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('finance'))
                                    <li>
                                        <a href="{{url('admin/allreports/excel/download')}}">Download</a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                            @endif

                            @if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="fas fa-hand-holding-usd"></div>
                                    </div>
                                    <span>Payout</span>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{url('admin/contacts')}}">Contacts</a>
                                    </li>
                                    <li>
                                        <a href="{{url('admin/orders')}}">Orders</a>
                                    </li>
                                    @if(Auth::user()->hasRole('super-admin'))
                                    <li>
                                        <a href="{{url('admin/bulk')}}">Bulk Payout</a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                            <li class="has-sub-menu">
                                <a href="{{url('admin/recharges')}}">
                                    <div class="icon-w">
                                        <i class="fas fa-duotone fa-mobile"></i>
                                    </div>
                                    <span>Recharges</span>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{url('/admin/recharges')}}">Recharges</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- <li class="has-sub-menu">
                                <a href="{{url('admin/ocr')}}">
                                    <div class="icon-w p-2">
                                        <i class="fas fa-file-search"></i>
                                    </div>
                                    <span>OCR</span>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{url('/admin/ocr')}}">OCR</a>
                                    </li>
                                </ul>

                            </li> -->
                            @if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support'))
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <i class="fas fa-hands-usd"></i>
                                    </div>
                                    <span>Auto Settlements</span>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{url('/admin/auto-settlements')}}">Auto Settlements</a>
                                    </li>
                                </ul>
                            </li>
                            @endif

                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="far fa-money-bill-alt"></div>
                                    </div>
                                    <span>Partner's VAN</span>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{url('admin/van-callback')}}">VAN Callbacks</a>
                                    </li>
                                </ul>
                            </li>

                            @if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w p-2">
                                        <i class="far fa-id-card"></i>
                                    </div>
                                    <span>AEPS</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/aeps/agents')}}">Agents</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/aeps/transactions')}}">Transaction</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w p-2">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <span>UPI Stack</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/upiMerchant')}}">UPI Merchants</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/upiCallback')}}">UPI Callbacks</a>
                                        </li>
                                        @if(Auth::user()->hasRole('super-admin'))
                                        <li>
                                            <a href="{{url('admin/manual-settlement/upi-stack')}}">Manual Settlement</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>

                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w p-2">
                                        <i class="far fa-file-invoice"></i>
                                    </div>
                                    <span>Virtual Accounts</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/va/clients')}}">VA Clients</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/va/callbacks')}}">VA Callbacks</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w p-2">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <span>Validation Suite</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/validation-suite/transactions')}}">Transactions</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w p-2">
                                        <i class="fas fa-money-check-alt"></i>
                                    </div>
                                    <span>Smart Collect</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/smart-collcet/merchants')}}">Merchants</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/smart-collect/callbacks')}}">Callbacks</a>
                                        </li>
                                        @if(Auth::user()->hasRole('super-admin'))
                                        <li>
                                            <a href="{{url('admin/manual-settlement/smart-collect')}}">Manual Settlement</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                            @if(Auth::user()->hasRole('super-admin')|| Auth::user()->hasRole('support'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-align-justify"></div>
                                    </div>
                                    <span>Ledgers</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/transactions')}}">Transactions</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Auth::user()->hasRole('super-admin')|| Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w p-2">
                                        <i class="fas fa-redo-alt"></i>
                                    </div>
                                    <span>Dispute Resolution</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/dispute-transactions/upi-stack')}}">UPI Stack</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/dispute-transactions/aeps-txn')}}">AEPS Transactions</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/dispute-transactions/smart-collect')}}">Smart Collect</a>
                                        </li>
                                        @if(Auth::user()->hasRole('super-admin'))
                                        <li>
                                            <a href="{{url('admin/dispute-transactions/orders')}}">Smart Payout</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                            @endif
                            @endif

                            @if(Auth::user()->hasRole('support'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-users"></div>
                                    </div>
                                    <span>Users</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/users')}}">Users</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-ui-46"></div>
                                    </div>
                                    <span>Fee Settings</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/global-billing/rules')}}">Global</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/custom-billing/rules')}}">Dynamic</a>

                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Auth::user()->hasRole('super-admin'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-users"></div>
                                    </div>
                                    <span>Users</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/users')}}">Users</a>
                                        </li>
                                        <!-- <li>
                                            <a href="{{url('admin/users-bank')}}">Users Bank</a>
                                        </li> -->
                                        <li>
                                            <a href="{{url('admin/serviceRequest')}}">Service Requests</a>
                                        </li>
                                        <li>
                                            <a href="{{url('/admin/load-money-request')}}">Load Money Request</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/partners-van/edit-info')}}">Update Partners VAN</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/smart-collect-van/edit-info')}}">Update Smart Collect VAN</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-ui-46"></div>
                                    </div>
                                    <span>Fee Settings</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/global-billing/rules')}}">Global</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/custom-billing/rules')}}">Dynamic</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <i class="fad fa-user-unlock"></i>
                                    </div>
                                    <span>Permissions</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/adminlist')}}">Admin List</a>
                                        </li>
                                        <li>
                                            <a href="{{url('admin/roles')}}">Role</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w p-2">
                                        <i class="fad fa-mail-bulk"></i>
                                    </div>
                                    <span>Messages</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/messages/list')}}">List</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Auth::user()->hasRole('finance'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-users"></div>
                                    </div>
                                    <span>Users</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('/admin/load-money-request')}}">Load Money Request</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                            @if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="fas fa-spider-web"></div>
                                    </div>
                                    <span>Logs</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('/admin/webhook-logs')}}">Webhook Logs</a>
                                        </li>
                                        <li>
                                            <a href="{{url('/admin/user-api-logs')}}">User Api Logs</a>
                                        </li>
                                        <li>
                                            <a href="{{url('/admin/api-logs')}}">Api Logs</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                            @if(Auth::user()->hasRole('aeps-support'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w p-2">
                                        <i class="far fa-id-card"></i>
                                    </div>
                                    <span>AEPS</span>
                                </a>
                                <div class="sub-menu-w">
                                    <div class="sub-menu-header">
                                        AEPS
                                    </div>
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('admin/aeps/agents')}}">Agents</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @include('include.common.sidebar')

                <div class="content-w">
                    @include('include.common.topbar')
                    <div class="content-i">
                        <div class="content-box">
                            @yield('content')
                        </div>

                        <?php
                        /*
                        ?>
                        @if(Request::is('admin/userprofile/*'))
                        <div class="content-panel">
                            <div class="content-panel-close">
                                <i class="os-icon os-icon-close"></i>
                            </div>
                            <!--------------------
              START - Support Agents
              -------------------->
                            <div class="element-wrapper">
                                <h6 class="element-header">Account Manager</h6>
                                <div class="element-box-tp">
                                    <div class="profile-tile">
                                        <?php
                                        $supportInfo = CommonHelper::supportInfo(Auth::user()->id);
                                        $mobileNo = "";
                                        ?>
                                        <a class="profile-tile-box" href="#">
                                            @if($supportInfo['status'] && isset($supportInfo['accountManager']))
                                            <?php $shortName = CommonHelper::shortName(Auth::user()->id, $supportInfo['accountManager']->name); ?>
                                            <div class="pt-avatar-w" style="border-radius:0">
                                                <p data-letters="{{ $shortName }}" class="lettersProfile"></p>
                                            </div>
                                            @endif
                                            @if($supportInfo['status'] && isset($supportInfo['accountManager']))
                                            <div class="pt-user-name"> {{ $supportInfo['accountManager']->name }}</div>
                                            @endif
                                        </a>
                                        <div class="profile-tile-meta">
                                            <ul>
                                                <li>Mobile:<strong><a href="#">
                                                            @if($supportInfo['status'] && isset($supportInfo['accountManager']))
                                                            <?php $mobileNo = $supportInfo['accountManager']->mobile; ?>
                                                            {{ $supportInfo['accountManager']->mobile }}
                                                            @endif
                                                        </a></strong></li>
                                                <li>Email:<strong>
                                                        @if($supportInfo['status'] && isset($supportInfo['accountManager']))
                                                        {{ $supportInfo['accountManager']->email }}
                                                        @endif
                                                    </strong></li>
                                            </ul>
                                            <div class="pt-btn">
                                                <a class="btn btn-success btn-sm" href="https://api.whatsapp.com/send?phone=+91{{ $mobileNo }}&text=Hello,%20I%20need%20help">Send WhatsApp</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="element-wrapper">
                                <h6 class="element-header">Account Coordinator</h6>
                                <div class="element-box-tp">
                                    <div class="profile-tile">
                                        <a class="profile-tile-box" href="#">
                                            @if($supportInfo['status'] && isset($supportInfo['accountCoordinator']))
                                            <?php $shortName = CommonHelper::shortName(Auth::user()->id, $supportInfo['accountCoordinator']->name); ?>
                                            <div class="pt-avatar-w" style="border-radius:0">
                                                <p data-letters="{{ $shortName }}" class="lettersProfile"></p>
                                            </div>
                                            @endif

                                            <div class="pt-user-name">
                                                @if($supportInfo['status'] && isset($supportInfo['accountCoordinator']))
                                                {{ $supportInfo['accountCoordinator']->name }}
                                                @endif
                                            </div>
                                        </a>
                                        <div class="profile-tile-meta">
                                            <ul>
                                                <li>Mobile:<strong><a href="#">
                                                            @if($supportInfo['status'] && isset($supportInfo['accountCoordinator']))
                                                            {{ $supportInfo['accountCoordinator']->mobile }}
                                                            <?php $mobileNo = $supportInfo['accountCoordinator']->mobile; ?>
                                                            @endif
                                                        </a></strong></li>
                                                <li>Email:<strong>
                                                        @if($supportInfo['status'] && isset($supportInfo['accountCoordinator']))
                                                        {{ $supportInfo['accountCoordinator']->email }}
                                                        @endif
                                                    </strong></li>
                                            </ul>
                                            <div class="pt-btn">
                                                <a class="btn btn-success btn-sm" target="_blank" href="https://api.whatsapp.com/send?phone=+91{{ $mobileNo }}&text=Hello,%20I%20need%20help">Send WhatsApp</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--------------------
              END - Support Agents
              -------------------->

                        </div>
                        @endif
                        <?php
                        //*/
                        ?>
                    </div>
                </div>
                <!--<footer class="m-auto"> <div class="copyright">
                    © Copyright 2021<strong><span>Xettle</span></strong>. All Rights Reserved
                </div></footer> -->
            </div>

            <div class="display-type"></div>
        </div>

        <script src="{{ asset('bower_components/jquery/dist/jquery.min.js')}}"></script>
        <script src="{{ asset('bower_components/popper.js/dist/umd/popper.min.js')}}"></script>
        <script src="{{ asset('bower_components/moment/moment.js')}}"></script>
        <script src="{{ asset('bower_components/chart.js/dist/Chart.min.js')}}"></script>
        <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js')}}"></script>
        <script src="{{ asset('bower_components/jquery-bar-rating/dist/jquery.barrating.min.js')}}"></script>
        <script src="{{ asset('bower_components/ckeditor/ckeditor.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap-validator/dist/validator.min.js')}}"></script>
        <!-- <script src="{{ asset('bower_components/bootstrap-daterangepicker/daterangepicker.js')}}"></script> -->
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="{{ asset('bower_components/ion.rangeSlider/js/ion.rangeSlider.min.js')}}"></script>
        <script src="{{ asset('bower_components/dropzone/dist/dropzone.js')}}"></script>
        <script src="{{ asset('bower_components/editable-table/mindmup-editabletable.js')}}"></script>
        <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
        <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
        <script src="{{ asset('bower_components/fullcalendar/dist/fullcalendar.min.js')}}"></script>
        <script src="{{ asset('bower_components/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js')}}"></script>
        <script src="{{ asset('bower_components/tether/dist/js/tether.min.js')}}"></script>
        <script src="{{ asset('bower_components/slick-carousel/slick/slick.min.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/util.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/alert.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/button.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/carousel.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/collapse.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/dropdown.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/modal.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/tab.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/tooltip.js')}}"></script>
        <script src="{{ asset('bower_components/bootstrap/js/dist/popover.js')}}"></script>
        <script src="{{ asset('js/dataTables.bootstrap4.min.js')}}"></script>
        <script src="{{ asset('js/demo_customizer.js?version=4.5.0')}}"></script>
        <script src="{{ asset('js/main.js?version=4.5.0')}}"></script>
        <script src="{{asset('js/toast/demos/js/jquery.toast.js')}}"></script>
        <script src="{{asset('js/sweetalert2@11.js')}}"></script>
        <script src="{{asset('js/script.js')}}"></script>
        @yield('scripts')
        <script>
            $('.content-i,.fancy-selector-options').on('click', function() {
                $('.fancy-selector-w').removeClass('opened');
            });
            $(document).ready(function() {

                $('#filterUTR').keydown(function(event) {
                    // enter has keyCode = 13, change it if you want to use another button
                    if (event.keyCode == 13) {
                        $('#loadingFilter').removeClass('os-icon os-icon-x');
                        $('#loadingFilter').addClass('os-icon os-icon-ui-46');

                        var filterutr = $('#filterUTR').val();
                        if (filterutr.length > 4) {
                            var url = "{{url('admin/filterutr')}}/" + filterutr;
                            $.get(url, function(response) {
                                $(".responseFilter").html('');
                                $('#loadingFilter').removeClass('os-icon os-icon-ui-46');
                                $('#loadingFilter').addClass('os-icon os-icon-x');
                                $(".responseFilter").append(response.data);
                            });
                        } else {
                            $(".responseFilter").html('');
                            $('#loadingFilter').removeClass('os-icon os-icon-ui-46');
                            $('#loadingFilter').addClass('os-icon os-icon-x');
                            $('.responseFilter').append('<div class="search-suggestions-group"><div class="ssg-header"><div class="ssg-name" style="margin-left:20px; color:red;"> The search field is  minimum 5 characters. </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div>')
                        }
                    }
                });

            });
        </script>
</body>

</html>