<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Xettle Technologies | @yield('title')</title>
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

    <link href="{{asset('css/main.css?version=4.5.0')}}" rel="stylesheet">
    <link href="{{asset('bower_components/common.css')}}" rel="stylesheet">

    <style type="text/css">
        .requiredstar {
            color: red;
        }

        .help-block {
            color: red;
            /* margin-left: 20px; */
        }

        td.details-control {
            background: url("{{custom_secure_url('')}}/public/images/details_open.png") no-repeat center center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            background: url("{{custom_secure_url('')}}/public/images/details_close.png") no-repeat center center;
        }

        .lettersProfile:before {
            height: 80px;
            width: 80px;
            line-height: 80px;
            font-size: 2.7rem;
            line-height: 1.8em;

        }
    </style>
    @yield('style')
</head>

<body class="menu-position-side menu-side-left">
    @if(Request::is('upi') || Request::is('collect') || Request::is('va'))
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
                               <img src="">
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
                            @if(Request::is('user/*') || Request::is('user'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span> Dashboard</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('user/dashboard')}}">Dashboard</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-life-buoy"></div>
                                    </div>
                                    <span> Transactions</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('user/transactions')}}">All Transactions</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-dollar-sign"></div>
                                    </div>
                                    <span>Load Money</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('user/load-money-request')}}">Load Money Request</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-documents-03"></div>
                                    </div>
                                    <span>Report</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('user/allreports/excel/download')}}">Download</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Request::is('upi/*') || Request::is('upi'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span> Dashboard</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('upi')}}">Dashboard</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layers"></div>
                                    </div>
                                    <span> Merchants</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('upi/merchants')}}">Merchants</a>
                                        </li>
                                    </ul>
                                </div>
                                </a>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-package"></div>
                                    </div>
                                    <span>Payments</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('upi/upicallbacks')}}">Payments</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-briefcase"></div>
                                    </div>
                                    <span>UPI Collect</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('upi/upicollects')}}">UPI Collect</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Request::is('va/*') || Request::is('va'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span> Dashboard</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('va')}}">Dashboard</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layers"></div>
                                    </div>
                                    <span> Clients</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('va/clients')}}">Clients</a>
                                        </li>
                                    </ul>
                                </div>
                                </a>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-package"></div>
                                    </div>
                                    <span>Payments</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('va/payments')}}">Payments</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Request::is('verification/*') || Request::is('verification'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span> Dashboard</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('verification')}}">Dashboard</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span> Transactions</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('validation/transactions')}}">Transactions</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Request::is('collect/*') || Request::is('collect'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span> Dashboard</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('collect')}}">Dashboard</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layers"></div>
                                    </div>
                                    <span> Merchants</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('collect/merchants')}}">Merchants</a>
                                        </li>
                                    </ul>
                                </div>
                                </a>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-package"></div>
                                    </div>
                                    <span>Payments</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('collect/payments')}}">Payments</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Request::is('aeps/*') || Request::is('aeps'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span> Dashboard</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('aeps')}}">Dashboard</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layers"></div>
                                    </div>
                                    <span> Merchants</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('aeps/merchants')}}">Merchants</a>
                                        </li>
                                    </ul>
                                </div>
                                </a>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-package"></div>
                                    </div>
                                    <span>Aeps Transactions</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('aeps/transactions')}}">Aeps Transactions</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                            @if(Request::is('payout/*') || Request::is('payout'))
                            <li class="has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span> Dashboard</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('payout')}}">Dashboard</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layers"></div>
                                    </div>
                                    <span> Contact</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('payout/contacts')}}">Contact</a>
                                        </li>
                                    </ul>
                                </div>
                                </a>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-package"></div>
                                    </div>
                                    <span>Bulk Payout</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('payout/bulk')}}">Bulk Payout</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class=" has-sub-menu">
                                <a href="#">
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-file-text"></div>
                                    </div>
                                    <span>Orders</span>
                                </a>
                                <div class="sub-menu-w">
                                    <ul class="sub-menu">
                                        <li>
                                            <a href="{{url('payout/orders')}}">Orders</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @include('include.user.sidebar')

                <div class="content-w">
                    @include('include.user.topbar')
                    <div class="content-i">
                        <div class="content-box">
                            @yield('content')
                        </div>

                        @if(Request::is('upi') || Request::is('collect') || Request::is('va'))
                        <div class="content-panel">
                            <div class="content-panel-close">
                                <i class="os-icon os-icon-close"></i>
                            </div>
                            <div class="element-wrapper">
                                <h6 class="element-header">
                                    Quick Links
                                </h6>
                                <div class="element-box-tp">
                                    <div class="el-buttons-list full-width">
                                        @if(Request::is('upi'))
                                        <a class="btn btn-white btn-sm" href="{{url('upi/merchants')}}"><i class="os-icon os-icon-delivery-box-2"></i><span>Create New Merchant</span></a>
                                        <a class="btn btn-white btn-sm" href="{{url('upi/upicallbacks')}}"><i class="os-icon os-icon-window-content"></i><span>UPI Transactions</span></a>
                                        @elseif(Request::is('collect'))
                                        <a class="btn btn-white btn-sm" href="{{url('collect/payments')}}"><i class="os-icon os-icon-window-content"></i><span>Transactions</span></a>
                                        @elseif(Request::is('va'))
                                        <a class="btn btn-white btn-sm" href="{{url('va/payments')}}"><i class="os-icon os-icon-window-content"></i><span>Transactions</span></a>
                                        @endif
                                    </div>
                                </div>
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
        <div aria-hidden="true" class="onboarding-modal modal fade animated" id="kt_modal_new_card" style="z-index: 1000000;" role="dialog" tabindex="-1">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content text-center">
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span class="close-label">

                        </span><span class="os-icon os-icon-close"></span></button>
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
        @yield('scripts')
        <script type="text/javascript">
            function serviceActivate(service_id) {
                $.ajax({
                    url: "{{url('user/accounts/service-activate')}}",
                    type: "post",
                    data: {
                        'service_id': service_id,
                        'user_id': "{{encrypt(Auth::user()->id)}}",
                        '_token': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status_code === '200') {
                            $('.account_number').text(response.service_account_number);
                            $("#onboardingFeaturesModal").modal('hide');
                            $('#kt_modal_new_card').modal({
                                show: true,
                                backdrop: 'static',
                                keyboard: false
                            });
                        } else {
                            swal.fire("Service Activation", res.message, "error");
                        }
                    }
                });
            }

            $('.content-i,.fancy-selector-options').on('click', function() {
                $('.fancy-selector-w').removeClass('opened');
            });
            $(function() {
                $('#kt_modal_new_card .close').click(function() {
                    $('#kt_modal_new_card').modal('hide');
                    location.reload();
                });
            });
            $(document).ready(function() {
                $('#filterUTR').keydown(function(event) {
                    // enter has keyCode = 13, change it if you want to use another button
                    if (event.keyCode == 13) {
                        $('#loadingFilter').removeClass('os-icon os-icon-x');
                        $('#loadingFilter').addClass('os-icon os-icon-ui-46');

                        var filterutr = $('#filterUTR').val();
                        if (filterutr.length > 4) {
                            var url = "{{url('user/filterutr')}}/" + filterutr;
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