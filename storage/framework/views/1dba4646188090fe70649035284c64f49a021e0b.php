<div
    class="menu-w selected-menu-color-light menu-activated-on-hover menu-has-selected-link color-scheme-dark color-style-default sub-menu-color-dark menu-position-side menu-side-left menu-layout-mini sub-menu-style-over">
    <div class="logged-user-w avatar-inline">
        <div class="logged-user-i">

            <?php if(isset(Auth::user()->avatar)): ?>
                <div class="avatar-w">
                    <img alt="" src="<?php echo e(url('uploads/profile/')); ?>/<?php echo e(Auth::user()->avatar); ?>">
                </div>
            <?php else: ?>
                <p data-letters="<?php echo e(CommonHelper::shortName(Auth::user()->id)); ?>"></p>
            <?php endif; ?>

            <div class="logged-user-info-w">
                <div class="logged-user-name">
                    <?php echo e(Auth::user()->name); ?>

                </div>
                <div class="logged-user-role">
                    <?php if(Auth::user()->is_admin): ?>
                        Admin
                    <?php else: ?>
                        User
                    <?php endif; ?>
                </div>
            </div>
            <div class="logged-user-toggler-arrow">
                <div class="os-icon os-icon-chevron-down"></div>
            </div>
            <div class="logged-user-menu color-style-bright">
                <div class="logged-user-avatar-info">
                    <?php if(isset(Auth::user()->avatar)): ?>
                        <div class="avatar-w">
                            <img alt="" src="<?php echo e(url('uploads/profile/')); ?>/<?php echo e(Auth::user()->avatar); ?>">
                        </div>
                    <?php else: ?>
                        <p data-letters="<?php echo e(CommonHelper::shortName(Auth::user()->id)); ?>"></p>
                    <?php endif; ?>
                    <div class="logged-user-info-w">
                        <div class="logged-user-name">
                            <?php echo e(Auth::user()->name); ?>

                        </div>
                        <div class="logged-user-role">
                            <?php if(Auth::user()->is_admin): ?>
                                Admin
                            <?php else: ?>
                                User
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="bg-icon">
                    <i class="os-icon os-icon-wallet-loaded"></i>
                </div>
                <ul>

                    <li>
                        <a href="<?php echo e(url('admin/profile')); ?>"><i
                                class="os-icon os-icon-user-male-circle2"></i><span>Profile Details</span></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <ul class="main-menu">

        <li class="selected has-sub-menu">
            <a href="<?php echo e(url('/admin/dashboard')); ?>">
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

        <?php if(Auth::user()->hasRole('accountant') ||
                Auth::user()->hasRole('super-admin') ||
                Auth::user()->hasRole('finance') ||
                Auth::user()->hasRole('log')): ?>
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
                        <?php if(Auth::user()->hasRole('accountant') || Auth::user()->hasRole('super-admin')): ?>
                            <li>
                                <a href="<?php echo e(url('admin/allreports')); ?>">All Reports</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/reports/payout')); ?>">Payout</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/reports/aeps')); ?>">Aeps</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/reports/upi')); ?>">UPI</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/reports/van')); ?>">VAN</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/reports/recharge')); ?>">Recharge</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/reports/panCard')); ?>">PAN Card</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/reports/validation')); ?>">Validation</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/reports/dmt')); ?>">DMT</a>
                            </li>
                        <?php endif; ?>
                        <?php if(Auth::user()->hasRole('accountant') || Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('finance')): ?>
                            <li>
                                <a href="<?php echo e(url('admin/allreports/excel/download')); ?>">Download</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

            </li>
        <?php endif; ?>


        <?php if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')): ?>
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
                            <a href="<?php echo e(url('admin/contacts')); ?>">Contacts</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/orders')); ?>">Orders</a>
                        </li>
                        <?php if(Auth::user()->hasRole('super-admin')): ?>
                            <li>
                                <a href="<?php echo e(url('admin/bulk')); ?>">Bulk Payout</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <li class="has-sub-menu">
                <a href="<?php echo e(url('admin/recharges')); ?>">
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
                            <a href="<?php echo e(url('admin/pan/agents')); ?>">Agents</a>
                        </li>

                        <li>
                            <a href="<?php echo e(url('admin/pan')); ?>">Transactions</a>
                        </li>

                    </ul>
                </div>
            </li>
            <!-- <li class="has-sub-menu">
            <a href="<?php echo e(url('admin/validation')); ?>">
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
                <a href="<?php echo e(url('admin/dmt')); ?>">
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
                <a href="<?php echo e(url('admin/insurance/agents')); ?>">
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
            <?php if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')): ?>
                <li class=" has-sub-menu">
                    <a href="<?php echo e(url('/admin/auto-settlements')); ?>">
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
            <?php endif; ?>
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
                            <a href="<?php echo e(url('admin/van-callback')); ?>">VAN Callbacks</a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')): ?>
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
                                <a href="<?php echo e(url('admin/aeps/agents')); ?>">Agents</a>
                            </li>

                            <li>
                                <a href="<?php echo e(url('admin/aeps/transactions')); ?>">Transactions</a>
                            </li>

                        </ul>
                    </div>
                </li>
            <?php endif; ?>
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
                            <a href="<?php echo e(url('admin/upiMerchant')); ?>">UPI Merchants</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/upiCallback')); ?>">UPI Callbacks</a>
                        </li>
                        <?php if(Auth::user()->hasRole('super-admin')): ?>
                            <li>
                                <a href="<?php echo e(url('admin/manual-settlement/upi-stack')); ?>">Manual Settlement</a>
                            </li>
                        <?php endif; ?>
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
                            <a href="<?php echo e(url('admin/va/clients')); ?>">VA Clients</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/va/callbacks')); ?>">VA Callbacks</a>
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
                            <a href="<?php echo e(url('admin/validation-suite/transactions')); ?>">Transactions</a>
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
                            <a href="<?php echo e(url('admin/smart-collcet/merchants')); ?>">Merchants</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/smart-collect/callbacks')); ?>">Callbacks</a>
                        </li>
                        <?php if(Auth::user()->hasRole('super-admin')): ?>
                            <li>
                                <a href="<?php echo e(url('admin/manual-settlement/smart-collect')); ?>">Manual Settlement</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <?php if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')): ?>
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
                                <a href="<?php echo e(url('admin/transactions')); ?>">Transactions</a>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>
            <?php if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')): ?>
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
                                <a href="<?php echo e(url('admin/dispute-transactions/upi-stack')); ?>">UPI Stack</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/dispute-transactions/aeps-txn')); ?>">AEPS Transactions</a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('admin/dispute-transactions/smart-collect')); ?>">Smart Collect</a>
                            </li>
                            <?php if(Auth::user()->hasRole('super-admin')): ?>
                                <li>
                                    <a href="<?php echo e(url('admin/dispute-transactions/orders')); ?>">Smart Payout</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(Auth::user()->hasRole('accountant')): ?>
            <li class="has-sub-menu">
                <a href="<?php echo e(url('admin/users')); ?>">
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
        <?php endif; ?>

        <?php if(Auth::user()->hasRole('support')): ?>
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
                            <a href="<?php echo e(url('admin/users')); ?>">Users</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/users/video-kyc')); ?>">Video KYC</a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->hasRole('support')): ?>
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
                            <a href="<?php echo e(url('admin/global-billing/rules')); ?>">Global</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/custom-billing/rules')); ?>">Dynamic</a>
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
                            <a href="<?php echo e(url('admin/offer/category-list')); ?>">Category List</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/offer/offer-list')); ?>">Offer List</a>
                        </li>


                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->hasRole('super-admin')): ?>
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
                            <a href="<?php echo e(url('admin/users')); ?>">Users</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/users/video-kyc')); ?>">Video KYC</a>
                        </li>
                        <!-- <li>
                        <a href="<?php echo e(url('admin/users-bank')); ?>">Users Bank</a>
                    </li> -->
                        <li>
                            <a href="<?php echo e(url('admin/serviceRequest')); ?>">Service Requests</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/load-money-request')); ?>">Load Money Request</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/partners-van/ebuz-list')); ?>">Ebuz Partners VAN</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/partners-van/edit-info')); ?>">Update Partners VAN</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/smart-collect-van/edit-info')); ?>">Update Smart Collect VAN</a>
                        </li>
                        <!--  <?php if(Auth::user()->hasRole('super-admin')): ?>
<li>
                            <a href="<?php echo e(url('admin/reconcile')); ?>">Reconcile</a>
                        </li>
<?php endif; ?> -->
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
                            <a href="<?php echo e(url('admin/global-billing/rules')); ?>">Global</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/custom-billing/rules')); ?>">Dynamic</a>
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
                            <a href="<?php echo e(url('admin/adminlist')); ?>">Admin List</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/roles')); ?>">Role</a>
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
                            <a href="<?php echo e(url('admin/messages/list')); ?>">List</a>
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
                            <a href="<?php echo e(url('admin/offer/category-list')); ?>">Category List</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('admin/offer/offer-list')); ?>">Offer List</a>
                        </li>


                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->hasRole('finance')): ?>
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
                            <a href="<?php echo e(url('/admin/load-money-request')); ?>">Load Money Request</a>
                        </li>

                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('log')): ?>
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
                            <a href="<?php echo e(url('/admin/webhook-logs')); ?>">Webhook Logs</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('/admin/user-api-logs')); ?>">User Api Logs</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('/admin/api-logs')); ?>">Api Logs</a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->hasRole('aeps-support')): ?>
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
                            <a href="<?php echo e(url('admin/aeps/agents')); ?>">Agents</a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

    </ul>

</div>
<?php /**PATH /home/pgpaysecureco/public_html/resources/views/include/common/sidebar.blade.php ENDPATH**/ ?>