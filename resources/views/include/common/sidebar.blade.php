<div
    class="menu-w selected-menu-color-light menu-activated-on-hover menu-has-selected-link color-scheme-dark color-style-default sub-menu-color-dark menu-position-side menu-side-left menu-layout-mini sub-menu-style-over">
    <div class="logged-user-w avatar-inline">
        <div class="logged-user-i">

            @if (isset(Auth::user()->avatar))
                <div class="avatar-w">
                    <img alt="" src="{{ url('uploads/profile/') }}/{{ Auth::user()->avatar }}">
                </div>
            @else
                <p data-letters="{{ CommonHelper::shortName(Auth::user()->id) }}"></p>
            @endif

            <div class="logged-user-info-w">
                <div class="logged-user-name">
                    {{ Auth::user()->name }}
                </div>
                <div class="logged-user-role">
                    @if (Auth::user()->is_admin)
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
                    @if (isset(Auth::user()->avatar))
                        <div class="avatar-w">
                            <img alt="" src="{{ url('uploads/profile/') }}/{{ Auth::user()->avatar }}">
                        </div>
                    @else
                        <p data-letters="{{ CommonHelper::shortName(Auth::user()->id) }}"></p>
                    @endif
                    <div class="logged-user-info-w">
                        <div class="logged-user-name">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="logged-user-role">
                            @if (Auth::user()->is_admin)
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
                        <a href="{{ url('admin/profile') }}"><i
                                class="os-icon os-icon-user-male-circle2"></i><span>Profile Details</span></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <ul class="main-menu">

        <li class="selected has-sub-menu">
            <a href="{{ url('/admin/dashboard') }}">
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
        @if (Auth::user()->hasRole('reseller') && request()->is('reseller/*'))
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w">
                        <div class="os-icon os-icon-align-justify"></div>
                    </div>
                    <span>Ledgers</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Ledgers
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('reseller/transactions') }}">Transactions</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w">
                    <div class="os-icon os-icon-ui-46"></div>
                    </div>
                    <span>Reseller</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Reseller
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('reseller/list') }}">List</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w">
                        <div class="os-icon os-icon-align-justify"></div>
                    </div>
                    <span>UPICollect</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        UPICollect
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('reseller/upicollect') }}">UPICollect</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w">
                    <div class="os-icon os-icon-users"></div>
                    </div>
                    <span>Payout</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Payout
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('reseller/payout') }}">Payout</a>
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
                    <div class="sub-menu-header">
                        Reports
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('reseller/reports/payout') }}">Payout</a>
                        </li>
                        <li>
                            <a href="{{ url('reseller/reports/upi') }}">UPI</a>
                        </li>
                        </li>
                    </ul>
                </div>
            </li>
         @endif

        @if (Auth::user()->hasRole('accountant') ||
                Auth::user()->hasRole('super-admin') ||
                Auth::user()->hasRole('finance') ||
                Auth::user()->hasRole('log'))
               
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
                        @if (Auth::user()->hasRole('accountant') || Auth::user()->hasRole('super-admin'))
                            <li>
                                <a href="{{ url('admin/allreports') }}">All Reports</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/reports/payout') }}">Payout</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/reports/aeps') }}">Aeps</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/reports/upi') }}">UPI</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/reports/van') }}">VAN</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/reports/recharge') }}">Recharge</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/reports/panCard') }}">PAN Card</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/reports/validation') }}">Validation</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/reports/dmt') }}">DMT</a>
                            </li>
                        @endif
                        @if (Auth::user()->hasRole('accountant') || Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('finance') || Auth::user()->hasRole('reseller'))
                            <li>
                                <a href="{{ url('admin/allreports/excel/download') }}">Download</a>
                            </li>
                        @endif
                    </ul>
                </div>

            </li>
        @endif


        @if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant'))
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w p-2">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <span>Payout</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Payout
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/contacts') }}">Contacts</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/orders') }}">Orders</a>
                        </li>
                        @if (Auth::user()->hasRole('super-admin'))
                            <li>
                                <a href="{{ url('admin/bulk') }}">Bulk Payout</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="{{ url('admin/recharges') }}">
                    <div class="icon-w p-2">
                        <i class="fas fa-duotone fa-mobile"></i>
                    </div>
                    <span>Recharge</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Recharge
                    </div>

                </div>

            </li>
            <li class="has-sub-menu">
                <a href="{{ url('admin/recharge-back') }}">
                    <div class="icon-w p-2">
                        <i class="fas fa-tablet-alt"></i>
                    </div>
                    <span>ReCharge Back</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                    ReCharge Back
                    </div>

                </div>

            </li>
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w p-2">
                        <i class="fas fa-duotone fa-credit-card-front"></i>
                    </div>
                    <span>PAN Card</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        PAN Card
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/pan/agents') }}">Agents</a>
                        </li>

                        <li>
                            <a href="{{ url('admin/pan') }}">Transactions</a>
                        </li>

                    </ul>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="{{ url('admin/integration/viewpipetxn') }}">
                    <div class="icon-w p-2">
                    <i class="fas fa-star"></i>
                    </div>
                    <span>Integration Volume</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                    Integration Volume
                    </div>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="{{ url('admin/reseller') }}">
                    <div class="icon-w p-2">
                    <i class="fas fa-users-class"></i>
                    </div>
                    <span>Reseller</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                    Reseller
                    </div>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="{{ url('admin/services') }}">
                    <div class="icon-w p-2">
                        <i class="far fa-id-card"></i>
                    </div>
                    <span>Services</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                    Services
                    </div>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="{{ url('admin/integration') }}">
                    <div class="icon-w p-2">
                        <i class="os-icon os-icon-package"></i>
                    </div>
                    <span>Integration</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                    Integration
                    </div>
                </div>
            </li>
            <!-- <li class="has-sub-menu">
            <a href="{{ url('admin/validation') }}">
                <div class="icon-w p-2">
                    <i class="fas fa-file-search"></i>
                </div>
                <span>OCR</span>
            </a>
            <div class="sub-menu-w">
                <div class="sub-menu-header">
                    OCR
                </div>
                
            </div>

        </li> -->
            <li class="has-sub-menu">
                <a href="{{ url('admin/dmt') }}">
                    <div class="icon-w p-2">
                        <i class="fas fa-usd-circle"></i>
                    </div>
                    <span>DMT</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        DMT
                    </div>

                </div>

            </li>
            <li class="has-sub-menu">
                <a href="{{ url('admin/insurance/agents') }}">
                    <div class="icon-w p-2">
                        <i class="fas fa-regular fa-user-shield"></i>
                    </div>
                    <span>Insurance</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Insurance
                    </div>

                </div>

            </li>
            @if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support'))
                <li class=" has-sub-menu">
                    <a href="{{ url('/admin/auto-settlements') }}">
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
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w p-2">
                        <i class="far fa-money-bill-alt"></i>
                    </div>
                    <span>Partner's VAN</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Partner's VAN
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/van-callback') }}">VAN Callbacks</a>
                        </li>
                    </ul>
                </div>
            </li>
            @if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant'))
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
                                <a href="{{ url('admin/aeps/agents') }}">Agents</a>
                            </li>

                            <li>
                                <a href="{{ url('admin/aeps/transactions') }}">Transactions</a>
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
                    <div class="sub-menu-header">
                        UPI Stack
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/upiMerchant') }}">UPI Merchants</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/upiCallback') }}">UPI Callbacks</a>
                        </li>
                        @if (Auth::user()->hasRole('super-admin'))
                            <li>
                                <a href="{{ url('admin/manual-settlement/upi-stack') }}">Manual Settlement</a>
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
                    <div class="sub-menu-header">
                        Virtual Accounts
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/va/clients') }}">VA Clients</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/va/callbacks') }}">VA Callbacks</a>
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
                    <div class="sub-menu-header">
                        Validation Suite
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/validation-suite/transactions') }}">Transactions</a>
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
                    <div class="sub-menu-header">
                        Smart Collect
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/smart-collcet/merchants') }}">Merchants</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/smart-collect/callbacks') }}">Callbacks</a>
                        </li>
                        @if (Auth::user()->hasRole('super-admin'))
                            <li>
                                <a href="{{ url('admin/manual-settlement/smart-collect') }}">Manual Settlement</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
            @if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support'))
                <li class="has-sub-menu">
                    <a href="#">
                        <div class="icon-w">
                            <div class="os-icon os-icon-align-justify"></div>
                        </div>
                        <span>Ledgers</span>
                    </a>
                    <div class="sub-menu-w">
                        <div class="sub-menu-header">
                            Ledgers
                        </div>
                        <ul class="sub-menu">
                            <li>
                                <a href="{{ url('admin/transactions') }}">Transactions</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
            @if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant'))
                <li class="has-sub-menu">
                    <a href="#">
                        <div class="icon-w p-2">
                            <i class="fas fa-redo-alt"></i>
                        </div>
                        <span>Dispute Resolution</span>
                    </a>
                    <div class="sub-menu-w">
                        <div class="sub-menu-header">
                            Dispute Resolution
                        </div>
                        <ul class="sub-menu">
                            <li>
                                <a href="{{ url('admin/dispute-transactions/upi-stack') }}">UPI Stack</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/dispute-transactions/aeps-txn') }}">AEPS Transactions</a>
                            </li>
                            <li>
                                <a href="{{ url('admin/dispute-transactions/smart-collect') }}">Smart Collect</a>
                            </li>
                            @if (Auth::user()->hasRole('super-admin'))
                                <li>
                                    <a href="{{ url('admin/dispute-transactions/orders') }}">Smart Payout</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
        @endif

        @if (Auth::user()->hasRole('accountant'))
            <li class="has-sub-menu">
                <a href="{{ url('admin/users') }}">
                    <div class="icon-w">
                        <div class="os-icon os-icon-users"></div>
                    </div>
                    <span>Users</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Users
                    </div>
                </div>
            </li>
        @endif

        @if (Auth::user()->hasRole('support'))
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w">
                        <div class="os-icon os-icon-users"></div>
                    </div>
                    <span>Users</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Users
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/users') }}">Users</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/users/video-kyc') }}">Video KYC</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if (Auth::user()->hasRole('support'))
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w">
                        <div class="os-icon os-icon-ui-46"></div>
                    </div>
                    <span>Fee Settings</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Fee Settings
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/global-billing/rules') }}">Global</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/custom-billing/rules') }}">Dynamic</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w p-2">
                        <i class="fas fa-truck-monster"></i>
                    </div>
                    <span>Offer</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Offers
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/offer/category-list') }}">Category List</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/offer/offer-list') }}">Offer List</a>
                        </li>


                    </ul>
                </div>
            </li>
        @endif

        @if (Auth::user()->hasRole('super-admin'))
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w">
                        <div class="os-icon os-icon-users"></div>
                    </div>
                    <span>Users</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Users
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/users') }}">Users</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/users/video-kyc') }}">Video KYC</a>
                        </li>
                        <!-- <li>
                        <a href="{{ url('admin/users-bank') }}">Users Bank</a>
                    </li> -->
                        <li>
                            <a href="{{ url('admin/serviceRequest') }}">Service Requests</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/load-money-request') }}">Load Money Request</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/partners-van/ebuz-list') }}">Ebuz Partners VAN</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/partners-van/edit-info') }}">Update Partners VAN</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/smart-collect-van/edit-info') }}">Update Smart Collect VAN</a>
                        </li>
                        <!--  @if (Auth::user()->hasRole('super-admin'))
<li>
                            <a href="{{ url('admin/reconcile') }}">Reconcile</a>
                        </li>
@endif -->
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
                    <div class="sub-menu-header">
                        Fee Settings
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/global-billing/rules') }}">Global</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/custom-billing/rules') }}">Dynamic</a>
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
                    <div class="sub-menu-header">
                        Permissions
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/adminlist') }}">Admin List</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/roles') }}">Role</a>
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
                    <div class="sub-menu-header">
                        Messages
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/messages/list') }}">List</a>
                        </li>


                    </ul>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w p-2">
                        <i class="fas fa-truck-monster"></i>
                    </div>
                    <span>Offer</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Offers
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('admin/offer/category-list') }}">Category List</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/offer/offer-list') }}">Offer List</a>
                        </li>


                    </ul>
                </div>
            </li>
        @endif

        @if (Auth::user()->hasRole('finance'))
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w">
                        <div class="os-icon os-icon-users"></div>
                    </div>
                    <span>Users</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Users
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('/admin/load-money-request') }}">Load Money Request</a>
                        </li>

                    </ul>
                </div>
            </li>
        @endif

        @if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('log'))
            <li class="has-sub-menu">
                <a href="#">
                    <div class="icon-w p-2">
                        <div class="fas fa-spider-web"></div>
                    </div>
                    <span>Logs</span>
                </a>
                <div class="sub-menu-w">
                    <div class="sub-menu-header">
                        Logs
                    </div>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ url('/admin/webhook-logs') }}">Webhook Logs</a>
                        </li>
                        <li>
                            <a href="{{ url('/admin/user-api-logs') }}">User Api Logs</a>
                        </li>
                        <li>
                            <a href="{{ url('/admin/api-logs') }}">Api Logs</a>
                        </li>
                        <li>
                            <a href="{{ url('/admin/activitylogs') }}">ActivityLog</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if (Auth::user()->hasRole('aeps-support'))
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
                            <a href="{{ url('admin/aeps/agents') }}">Agents</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

    </ul>

</div>
