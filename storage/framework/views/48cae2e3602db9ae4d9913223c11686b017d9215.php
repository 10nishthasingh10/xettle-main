<?php $__env->startSection('title','Admin Dashboard'); ?>

<?php $__env->startSection('style'); ?>
<style>
	.daterangepicker {
		min-width: auto !important;
	}

	.content-box {
		padding: 25px !important;
	}

	.element-box {
		padding: 1rem !important;
		position: relative;
	}

	.element-box.p-0 {
		padding: 0rem !important;
		margin-bottom: 1rem;
	}

	.el-chart-w canvas {
		width: 300px !important;
	}

	.nav-link {
		font-size: 16px;
	}

	.nav-tabs .nav-link {
		color: rgb(49 59 71) !important;
	}

	.select2-container {
		width: 250px !important;
		margin-right: 10px !important;
	}

	.xettle-select2 {
		min-height: 32px !important;
		height: 32px !important;
		border: 1px solid #ccc !important;
		border-radius: 5px !important;
	}

	.lats_txn .select2-container {
		width: 100% !important;
	}

	.lats_txn .select2-container .xettle-select2 {
		height: 40px !important;
		border: 2px solid #dbe2ec !important;
	}

	.table-responsive {
		overflow: auto !important;
	}

	#dt-last_txn_filter label {
		visibility: hidden !important;
	}

	.modal-header span,
	.modal-header strong,
	.modal-header .avatar {
		vertical-align: inherit;
	}

	.el-tablo .value {
		font-size: 1.28rem !important;
	}

	#fo_cards {
		display: flex !important;
		align-items: center !important;
	}

	#refresh_report {
		border-color: #ccc !important;
		border-width: 1px !important;
	}

	#refresh_report:hover {
		color: #000 !important;
		border-width: 2px !important;
	}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="element-wrapper p-0">
	<div class="element-actions">
		<a class="btn btn-secondary btn-sm" href="#"><i class="os-icon os-icon-ui-22"></i><span>Add Account</span></a><a class="btn btn-success btn-sm" href="#"><i class="os-icon os-icon-grid-10"></i><span>Make Payment</span></a>
	</div>
	<h6 class="element-header m-0">
		Financial overview
	</h6>
	<div class="element-box-tp">

		<div class="row">
			<div class="col-md-12 col-lg-12 col-xxl-12">
				<!--START - BALANCES-->
				<div class="element-balances">
					<div class="balance ">
						<div class="balance-title">
							Primary Balance
						</div>
						<div class="balance-value text-success">
							<span>
								<i class="fas fa-rupee-sign"></i>
								<b id="primary-balance">0</b>
							</span>
						</div>
						<div class="balance-link">
							<i class="fas fa-rupee-sign"></i>
							<b id="primary-balance-actual">0</b>
						</div>
					</div>

					<div class="balance">
						<div class="balance-title">
							Payout Balance
						</div>
						<div class="balance-value">
							<span>
								<i class="fas fa-rupee-sign"></i>
								<b id="payout-balance">0</b>
							</span>
						</div>
						<div class="balance-link">
							<i class="fas fa-rupee-sign"></i>
							<b id="payout-balance-actual">0</b>
						</div>
					</div>

					<div class="balance">
						<div class="balance-title">
							Order on Queue
						</div>
						<div class="balance-value danger">
							<span>
								<i class="fas fa-rupee-sign"></i>
								<b id="order-queue">0</b>
							</span>
						</div>
						<div class="balance-link">
							<b id="order-queue-actual">0</b>
						</div>
					</div>

					<div class="balance">
						<div class="balance-title">
							Order in Process
						</div>
						<div class="balance-value">
							<span>
								<i class="fas fa-rupee-sign"></i>
								<b id="order-process">0</b>
							</span>
						</div>
						<div class="balance-link">
							<b id="order-process-actual">0</b>
						</div>
					</div>
				</div>
				<!--END - BALANCES-->
			</div>

		</div>

		<div class="row">
			<div class="col-12">
				<div class="element-actions" id="fo_cards">
					<div class="mr-3">
						<div id="refresh_report" title="Refresh Financial Overview Data" class="bg-white float-end btn btn-outline-secondary me-1">
							<i class="fas fa-sync"></i>&nbsp; Refresh
						</div>
					</div>

					<div class="form-inline justify-content-sm-end">
						<div id="select_daterange_fo" class="xtl-chart-date-picker">
							<i class="fa fa-calendar"></i>&nbsp;
							<span>Today</span> <i class="fa fa-caret-down"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="col-sm-12 border xtl_dash_custom_hr"></div>
		</div>


		<div class="row mb-2">

			<!-- Payout Summary -->

			<div class="col-lg-4 col-sm-6">
				<div class="element-box card xtl_dash_custom-card">
					<div class="card-body">
						<div class="product-container">
							<div class="bg-light-success">
								<span class="icon">
									<i class="fas fa-rupee-sign"></i>
								</span>
							</div>

							<div class="product-title">
								<h6>Payout</h6>
							</div>
						</div>
						<div class="seperator"></div>
						<div class="summary_amount">
							<h6 class="fw-bolder mb-75 text-success">
								<span id="payout_processed_count">0</span> | <span id="payout_processed">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-warning">
								<span id="payout_processing_count">0</span> | <span id="payout_processing">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-danger">
								<span id="payout_failed_count">0</span> | <span id="payout_failed">₹ 0</span>
							</h6>
						</div>
					</div>
				</div>
			</div>


			<!-- AEPS Summary -->

			<div class="col-lg-4 col-sm-6">
				<div class="element-box card xtl_dash_custom-card">
					<div class="card-body">
						<div class="product-container">
							<div class="bg-light-success">
								<span class="icon">
									<i class="far fa-address-card"></i>
								</span>
							</div>

							<div class="product-title">
								<h6>AEPS</h6>
							</div>
						</div>
						<div class="seperator"></div>
						<div class="summary_amount">
							<h6 class="fw-bolder mb-75 text-success">
								<span id="aeps_success_count">0</span> | <span id="aeps_success">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-warning">
								<span id="aeps_pending_count">0</span> | <span id="aeps_pending">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-danger">
								<span id="aeps_failed_count">0</span> | <span id="aeps_failed">₹ 0</span>
							</h6>
						</div>
					</div>
				</div>
			</div>


			<!-- Recharge Summary -->

			<div class="col-lg-4 col-sm-6">
				<div class="element-box card xtl_dash_custom-card">
					<div class="card-body">
						<div class="product-container">
							<div class="bg-light-success">
								<span class="icon">
									<i class="fas fa-mobile-alt"></i>
								</span>
							</div>

							<div class="product-title">
								<h6>Recharge</h6>
							</div>
						</div>
						<div class="seperator"></div>
						<div class="summary_amount">
							<h6 class="fw-bolder mb-75 text-success">
								<span id="recharge_processed_count">0</span> | <span id="recharge_processed">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-warning">
								<span id="recharge_processing_count">0</span> | <span id="recharge_processing">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-danger">
								<span id="recharge_failed_count">0</span> | <span id="recharge_failed">₹ 0</span>
							</h6>
						</div>
					</div>
				</div>
			</div>


			<!-- DMT Summary -->

			<div class="col-lg-4 col-sm-6">
				<div class="element-box card xtl_dash_custom-card">
					<div class="card-body">
						<div class="product-container">
							<div class="bg-light-success">
								<span class="icon">
									<i class="fas fa-exchange-alt"></i>
								</span>
							</div>

							<div class="product-title">
								<h6>Money Transfer</h6>
							</div>
						</div>
						<div class="seperator"></div>
						<div class="summary_amount">
							<h6 class="fw-bolder mb-75 text-success">
								<span id="dmt_processed_count">0</span> | <span id="dmt_processed">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-warning">
								<span id="dmt_processing_count">0</span> | <span id="dmt_processing">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-danger">
								<span id="dmt_failed_count">0</span> | <span id="dmt_failed">₹ 0</span>
							</h6>
						</div>
					</div>
				</div>
			</div>


			<!-- Validation Summary -->

			<div class="col-lg-4 col-sm-6">
				<div class="element-box card xtl_dash_custom-card">
					<div class="card-body">
						<div class="product-container">
							<div class="bg-light-success">
								<span class="icon">
									<i class="fas fa-user-check"></i>
								</span>
							</div>

							<div class="product-title">
								<h6>Validation</h6>
							</div>
						</div>
						<div class="seperator"></div>
						<div class="summary_amount">
							<h6 class="fw-bolder mb-75 text-success">
								<span id="validation_success_count">0</span> | <span id="validation_success">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-warning">
								<span id="validation_pending_count">0</span> | <span id="validation_pending">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-danger">
								<span id="validation_failed_count">0</span> | <span id="validation_failed">₹ 0</span>
							</h6>
						</div>
					</div>
				</div>
			</div>


			<!-- MATM Summary -->

			<div class="col-lg-4 col-sm-6">
				<div class="element-box card xtl_dash_custom-card">
					<div class="card-body">
						<div class="product-container">
							<div class="bg-light-success">
								<span class="icon">
									<i class="far fa-credit-card"></i>
								</span>
							</div>

							<div class="product-title">
								<h6>Micro ATM</h6>
							</div>
						</div>
						<div class="seperator"></div>
						<div class="summary_amount">
							<h6 class="fw-bolder mb-75 text-success">
								<span id="matm_processed_count">0</span> | <span id="matm_processed">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-warning">
								<span id="matm_pending_count">0</span> | <span id="matm_pending">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-danger">
								<span id="matm_failed_count">0</span> | <span id="matm_failed">₹ 0</span>
							</h6>
						</div>
					</div>
				</div>
			</div>


			<!-- Pan Card Summary -->

			<div class="col-lg-4 col-sm-6">
				<div class="element-box card xtl_dash_custom-card">
					<div class="card-body">
						<div class="product-container">
							<div class="bg-light-success">
								<span class="icon">
									<i class="fas fa-id-card"></i>
								</span>
							</div>

							<div class="product-title">
								<h6>Pan Card</h6>
							</div>
						</div>
						<div class="seperator"></div>
						<div class="summary_amount">
							<h6 class="fw-bolder mb-75 text-success">
								<span id="pancard_success_count">0</span> | <span id="pancard_success">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-warning">
								<span id="pancard_pending_count">0</span> | <span id="pancard_pending">₹ 0</span>
							</h6>
							<h6 class="fw-bolder mb-75 text-danger">
								<span id="pancard_failed_count">0</span> | <span id="pancard_failed">₹ 0</span>
							</h6>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
</div>

<div class="element-wrapper">

	<div class="element-box-tp">
		<div class="os-tabs-w">
			<div class="p-0 os-tabs-controls element-box">
				<ul class="nav nav-tabs smaller searchby">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#tab_payout">Payout</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tabAepsChart" data-toggle="tab" href="#tab_aeps">AEPS</a>
					</li>
					<!-- <li class="nav-item">
						<a class="nav-link" id="tabUpiStackChart" data-toggle="tab" href="#tab_UpiStackChart">UPI Stack</a>
					</li> -->
					<li class="nav-item">
						<a class="nav-link" id="tabDmt" data-toggle="tab" href="#tab_dmt">DMT</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tabMatm" data-toggle="tab" href="#tab_matm">M-ATM</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tabRecharge" data-toggle="tab" href="#tab_recharge">Recharge</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tabPanCard" data-toggle="tab" href="#tab_pancard">Pan Card</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tabTopUsers" data-toggle="tab" href="#tab_TopUsers">Top Users</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tabLastTxn" data-toggle="tab" href="#tab_LastTxn">Active Users</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tabltstSignup" data-toggle="tab" href="#tab_ltstSignup">Latest SignUp</a>
					</li>
				</ul>
			</div>

			<div class="tab-content">
				<!-- Tab Payout -->
				<?php echo $__env->make('admin.dash_templates.tab_payout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- Tab AEPS -->
				<?php echo $__env->make('admin.dash_templates.tab_aeps', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- TAB DMT -->
				<?php echo $__env->make('admin.dash_templates.tab_dmt', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- TAB MATM -->
				<?php echo $__env->make('admin.dash_templates.tab_matm', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- TAB Recharge -->
				<?php echo $__env->make('admin.dash_templates.tab_recharge', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- TAB PAN Card -->
				<?php echo $__env->make('admin.dash_templates.tab_pancard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- TAB Top Users -->
				<?php echo $__env->make('admin.dash_templates.tab_topusers', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- Last Transaction by User -->
				<?php echo $__env->make('admin.dash_templates.tab_activeusers', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- Last Transaction by User -->
				<?php echo $__env->make('admin.dash_templates.tab_latestsignup', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

			</div>
		</div>
	</div>

	<div class="modal fade animated" id="modalShowTxnInfo" tabindex="-1" aria-modal="true" role="dialog">
		<!--begin::Modal dialog-->
		<div class="modal-dialog modal-lg">
			<!--begin::Modal content-->
			<div class="modal-content">
				<!--begin::Modal header-->
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">
						<strong id="modalTitleSpan"></strong> Transaction Info
					</h5>
					<button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
				</div>
				<!--begin::Modal header-->
				<!--begin::Modal body-->
				<div class="modal-body">

					<div class="table-responsive">
						<table id="modalTable" class="table table-bordered table-striped table-hover no-footer w-100">
						</table>
					</div>

				</div>
				<!--end::Col-->
				<!--begin::Actions-->
				<div class="modal-footer flex-center">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
				<!--end::Actions-->

				<!--end:Form-->
			</div>
			<!--end::Modal body-->
		</div>
		<!--end::Modal content-->
	</div>

</div>

<input type="hidden" id="dashboardBalances" value="<?php echo e(custom_secure_url('admin/dashboard/balances')); ?>">
<input type="hidden" id="dashboardUsersAmount" value="<?php echo e(custom_secure_url('admin/fetch-reports/dashboard-users-amount')); ?>">

<input type="hidden" id="payoutGraphs" value="<?php echo e(custom_secure_url('graphs/payout')); ?>">
<input type="hidden" id="aepsGraphs" value="<?php echo e(custom_secure_url('graphs/aeps')); ?>">
<input type="hidden" id="dmtGraphs" value="<?php echo e(custom_secure_url('graphs/dmt')); ?>">
<input type="hidden" id="matmGraphs" value="<?php echo e(custom_secure_url('graphs/matm')); ?>">
<input type="hidden" id="rechargeGraphs" value="<?php echo e(custom_secure_url('graphs/recharge')); ?>">
<input type="hidden" id="panGraphs" value="<?php echo e(custom_secure_url('graphs/pan-card')); ?>">


<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(url('public/js/handlebars.js')); ?>"></script>
<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>

<script src="<?php echo e(asset('admin-js/dashboard.js?v=1.0.3')); ?>"></script>
<script src="<?php echo e(asset('admin-js/dashboard-aeps.js?v=1.0.0')); ?>"></script>
<script src="<?php echo e(asset('admin-js/dashboard-dmt.js?v=1.0.0')); ?>"></script>
<script src="<?php echo e(asset('admin-js/dashboard-matm.js?v=1.0.1')); ?>"></script>
<script src="<?php echo e(asset('admin-js/dashboard-recharge.js?v=1.0.0')); ?>"></script>
<script src="<?php echo e(asset('admin-js/dashboard-pancard.js?v=1.0.0')); ?>"></script>
<script>
	$(function() {
		$('.select2').select2({
			containerCssClass: "xettle-select2"
		});
	});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/home.blade.php ENDPATH**/ ?>