<div class="menu-w selected-menu-color-light menu-activated-on-hover menu-has-selected-link color-scheme-dark color-style-default sub-menu-color-dark menu-position-side menu-side-left menu-layout-mini sub-menu-style-over">
    <div class="logged-user-w avatar-inline">
        <div class="logged-user-i">

            @if(isset(Auth::user()->avatar))
            <div class="avatar-w">
                <img alt="" src="{{url('uploads/profile/')}}/{{Auth::user()->avatar}}">
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
            <div class="logged-user-toggler-arrow">
                <div class="os-icon os-icon-chevron-down"></div>
            </div>
            <div class="logged-user-menu color-style-bright">
                <div class="logged-user-avatar-info">
                    @if(isset(Auth::user()->avatar))
                    <div class="avatar-w">
                        <img alt="" src="{{url('uploads/profile/')}}/{{Auth::user()->avatar}}">
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
                </ul>
            </div>
        </div>
    </div>
    <ul class="main-menu">
        <li class="sub-header">
            <span>Layouts</span>
        </li>
        <li class="selected has-sub-menu">
            @if(Request::is('payout/*') || Request::is('payout') || Request::is('upi') || Request::is('upi/*') || Request::is('aeps') || Request::is('aeps/*') || Request::is('collect/*') || Request::is('collect') || Request::is('va/*') || Request::is('va') || Request::is('verification/*') || Request::is('verification') || Request::is('reseller/*') || Request::is('reseller'))
            @if(Request::is('payout/*') || Request::is('payout'))
            <a href="{{url('/payout')}}">
                @elseif(Request::is('aeps/*') || Request::is('aeps'))
                <a href="{{url('/aeps')}}">
                    @elseif(Request::is('va/*') || Request::is('va'))
                    <a href="{{url('va')}}">
                        @elseif(Request::is('verification/*') || Request::is('verification'))
                        <a href="{{url('verification')}}">
                        @elseif(Request::is('reseller/*') || Request::is('reseller'))
                            <a href="{{url('/reseller/dashboard')}}">
                        @elseif(Request::is('collect/*') || Request::is('collect'))
                        <a href="{{url('/collect')}}">
                            @else
                            <a href="{{url('/upi')}}">
                                @endif
                                @else
                                <a href="{{url('/user/dashboard')}}">
                                    @endif
                                    <div class="icon-w">
                                        <div class="os-icon os-icon-layout"></div>
                                    </div>
                                    <span>Dashboard</span>
                                </a>
                                <div class="sub-menu-w">
                                    <div class="sub-menu-header">
                                        Dashboard
                                    </div>
                                    <div class="sub-menu-icon">
                                        <i class="os-icon os-icon-layout"></i>
                                    </div>
                                </div>
        </li>
        <!-- @if(Request::is('reseller/*') || Request::is('reseller') )
        <li class=" has-sub-menu">
            <a href="{{url('/reseller/transactions')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-life-buoy"></div>
                </div>
                <span> Transactions</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Transactions
                </div>
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
                <div class="sub-menu-header">
                    Reports
                </div>
                <ul class="sub-menu">

                    <li>
                        <a href="{{url('reseller/allreports/excel/download')}}">Download</a>
                    </li>
                </ul>
            </div>

        </li>
        @endif -->
        @if(Request::is('user/*') || Request::is('user') )
        <li class=" has-sub-menu">
            <a href="{{url('/user/transactions')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-life-buoy"></div>
                </div>
                <span> Transactions</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Transactions
                </div>
            </div>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('/user/dth-recharge')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-briefcase"></div>
                </div>
                <span>DTH Recharge</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                   DTH Recharge
                </div>
            </div>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('/user/lic-recharge')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-dollar-sign"></div>
                </div>
                <span>LIC Recharge</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                   LIC Recharge
                </div>
            </div>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('user/electricity-recharge')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-documents-03"></div>
                </div>
                <span>Electricity Recharge</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                   Electricity Recharge
                </div>
            </div>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('user/postpaid-recharge')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-briefcase"></div>
                </div>
                <span>PostPaid Recharge</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                   PostPaid Recharge
                </div>
            </div>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('user/creditcard-recharge')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-package"></div>
                </div>
                <span>Creditcard Recharge</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                   Creditcard Recharge
                </div>
            </div>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('user/data-recharge')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-layers"></div>
                </div>
                <span>Recharge Data</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                   Recharge Data
                </div>
            </div>
        </li>
        @if(CommonHelper::isAutoSettlementActive(Auth::user()->id, 'auto_settlement'))
        <li class=" has-sub-menu">
            <a href="{{url('/user/auto-settlements')}}">
                <div class="icon-w">
                <i class="fas fa-hands-usd"></i>
                </div>
                <span>Auto Settlements</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                Auto Settlements
                </div>
            </div>
        </li>
        @endif
        @if(CommonHelper::isAutoSettlementActive(Auth::user()->id, 'load_money_request'))
        <li class=" has-sub-menu">
            <a href="{{url('user/load-money-request')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-dollar-sign"></div>
                </div>
                <span>Load Money</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Load Money Request
                </div>
            </div>
        </li>
        @endif
        <li class=" has-sub-menu">
            <a href="#">
                <div class="icon-w">
                    <div class="os-icon os-icon-documents-03"></div>
                </div>
                <span>Report</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Reports
                </div>
                <ul class="sub-menu">

                    <li>
                        <a href="{{url('user/allreports/excel/download')}}">Download</a>
                    </li>
                </ul>
            </div>

        </li>
        @endif
        @if(Request::is('upi/*') || Request::is('upi'))
        <li class=" has-sub-menu">
            <a href="{{url('upi/merchants')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-layers"></div>
                </div>
                <span> Merchants</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Merchants
                </div>

            </div>
            </a>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('upi/upicallbacks')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-package"></div>
                </div>
                <span>Payments</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Payments
                </div>
                <div class="sub-menu-icon">
                    <i class="os-icon os-icon-package"></i>
                </div>

            </div>
        </li>
        {{-- <li class=" has-sub-menu">
            <a href="{{url('upi/upicollects')}}">
        <div class="icon-w">
            <div class="os-icon os-icon-briefcase"></div>
        </div>
        <span>UPI Collect</span>
        </a>
        <div class="sub-menu-w">
            <div class="sub-menu-header">
                UPI Collect List
            </div>
            <div class="sub-menu-icon">
                <i class="os-icon os-icon-briefcase"></i>
            </div>

        </div>
        </li> --}}
        @endif

        @if(Request::is('validation/*') || Request::is('verification'))
        <li class=" has-sub-menu">
            <a href="{{url('validation/transactions')}}">
                <div class="icon-w p-2">
                    <i class="fas fa-check-circle"></i>
                </div>
                <span>Validation Suite</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Validation Suite Transactions
                </div>
            </div>
        </li>
        @endif

        @if(Request::is('va/*') || Request::is('va'))
        <li class=" has-sub-menu">
            <a href="{{url('va/clients')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-layers"></div>
                </div>
                <span> Clients</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Clients
                </div>

            </div>
            </a>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('va/payments')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-package"></div>
                </div>
                <span>Payments</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Payments
                </div>
                <div class="sub-menu-icon">
                    <i class="os-icon os-icon-briefcase"></i>
                    <i class="os-icon os-icon-package"></i>
                </div>

            </div>
        </li>
        @endif

        @if(Request::is('collect/*') || Request::is('collect'))
        <li class=" has-sub-menu">
            <a href="{{url('collect/merchants')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-layers"></div>
                </div>
                <span> Merchants</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Merchants
                </div>

            </div>
            </a>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('collect/payments')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-package"></div>
                </div>
                <span>Payments</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Payments
                </div>
                <div class="sub-menu-icon">
                    <i class="os-icon os-icon-package"></i>
                </div>

            </div>
        </li>
        @endif

        @if(Request::is('aeps/*') || Request::is('aeps'))
        <li class=" has-sub-menu">
            <a href="{{url('aeps/merchants')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-layers"></div>
                </div>
                <span> Merchants</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Merchants
                </div>

            </div>
            </a>
        </li>
        <li class=" has-sub-menu">
            <a href="#">
                <div class="icon-w">
                    <div class="os-icon os-icon-package"></div>
                </div>
                <span>Aeps</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Aeps
                </div>
                <ul class="sub-menu">
                    <li>
                        <a href="{{url('aeps/transactions')}}">AEPS Transactions</a>
                    </li>
                    <li>
                        <a href="{{url('aeps/settlement')}}">AEPS Settlement</a>
                    </li>
                </ul>
                <div class="sub-menu-icon">
                    <i class="os-icon os-icon-package"></i>
                </div>

            </div>
        </li>
        @endif

        @if(Request::is('payout/*') || Request::is('payout'))
        <li class=" has-sub-menu">
            <a href="{{url('payout/contacts')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-layers"></div>
                </div>
                <span> Contact</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Contact
                </div>
            </div>
            </a>
        </li>
        <li class="sub-header">
            <span>Options</span>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('payout/bulk')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-package"></div>
                </div>
                <span>Bulk Payout</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Bulk Payout
                </div>
                <div class="sub-menu-icon">
                    <i class="os-icon os-icon-package"></i>
                </div>

            </div>
        </li>
        <li class=" has-sub-menu">
            <a href="{{url('payout/orders')}}">
                <div class="icon-w">
                    <div class="os-icon os-icon-file-text"></div>
                </div>
                <span>Orders</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    Orders
                </div>
            </div>
        </li>
        @endif

    </ul>

</div>