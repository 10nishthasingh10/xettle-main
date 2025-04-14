
    <div class="top-bar color-scheme-light">
            <div class="logo-w menu-size">
            <?php if(Auth::user()->is_admin == 1): ?>
            <a class="logo" href="<?php echo e(url('/admin/dashboard')); ?>">
        <?php else: ?>
            <a class="logo" href="<?php echo e(url('/user/dashboard')); ?>">
        <?php endif; ?>
                <div class="logo-element"></div>
                <div class="logo-label">
                Xettle
                </div>
            </a>
            </div>
            <div class="fancy-selector-w wrapp">
            <div class="fancy-selector-current ">
            <div class="fs-img">
            <i class="os-icon os-icon-wallet-loaded" style="font-size: 32px;"></i>
            </div>
                <div class="fs-main-info">
                <div class="fs-name">
                Main Account
                </div>
                                                <div class="fs-sub">
                                                <span>Balance:</span>
                                                <strong>
                                                <?php if(Auth::user()->transaction_amount+Auth::user()->locked_amount > 0): ?>
                                                    ₹<?php echo e(Auth::user()->transaction_amount+Auth::user()->locked_amount); ?>

                                                <?php else: ?>
                                                    ₹<?php echo e(number_format(0,2)); ?>

                                                <?php endif; ?>
                                                </strong>
                                                </div>
                </div>
                <div class="fs-extra-info">
                                            <strong>
                                            <?php echo e(substr(Auth::user()->account_number,8,12)); ?>

                                            </strong>
                                            <span>ending</span>
                                            </div>
                <div class="fs-selector-trigger">
                <i class="os-icon os-icon-arrow-down4"></i>
                </div>
                </div>
            <div class="fancy-selector-options">
            <a href="javascript:void(0)">
            <div class="fancy-selector-option active">
                <div class="fs-img">
                <i class="os-icon os-icon-wallet-loaded" style="font-size: 38px;"></i>
                </div>
                <div class="fs-main-info">
                    <div class="fs-name">
                Main Account
                    </div>
                                                <div class="fs-sub">
                                                <span>Balance:</span>
                                                <strong><?php echo e(Auth::user()->transaction_amount+Auth::user()->locked_amount); ?></strong> 
                                                </div>
                </div>
                <div class="fs-extra-info">

                                            <strong>
                                            <?php echo e(substr(Auth::user()->account_number,8,12)); ?>

                                            </strong>
                                            <span>ending</span>
                                            </div>
                </div>
                </a>
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
            --------------------><!--------------------
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
                    <a href="<?php echo e(url('admin/profile')); ?>"><i class="os-icon os-icon-ui-49"></i><span>My Profile</span></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('logout')); ?>"><i class="os-icon os-icon-signs-11"></i><span>Logout</span></a>
                    </li>
                    <!-- <li>
                    <a href="users_profile_small.html"><i class="os-icon os-icon-ui-15"></i><span>Cancel Account</span></a>
                    </li> -->
                </ul>
                </div>
            </div>
            <!--------------------
            END - Settings Link in secondary top menu
            --------------------><!--------------------
            START - User avatar and menu in secondary top menu
            -------------------->
            <div class="logged-user-w d-lg-none d-md-block">
                <div class="logged-user-i">

                <?php if(isset(Auth::user()->avatar)): ?>
                <div class="avatar-w">
                <img alt="" src="<?php echo e(url('uploads/profile/')); ?>/<?php echo e(Auth::user()->avatar); ?>">
                </div>
                                <?php else: ?>
                                <p data-letters="<?php echo e(CommonHelper::shortName(Auth::user()->id)); ?>"></p>
                                <?php endif; ?>
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
                        <a href="<?php echo e(url('admin/profile')); ?>"><i class="os-icon os-icon-user-male-circle2"></i><span>Profile Details</span></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('logout')); ?>"><i class="os-icon os-icon-signs-11"></i><span>Logout</span></a>
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
<?php /**PATH /home/pgpaysecureco/public_html/resources/views/include/common/topbar.blade.php ENDPATH**/ ?>