<div class="onboarding-modal modal fade animated" id="kt_modal_create_ipwhite" tabindex="-1" aria-hidden="true">
	<!--begin::Modal dialog-->
	<div class="modal-dialog ">
		<!--begin::Modal content-->
		<div class="modal-content">
			<!--begin::Modal header-->
			<div class="modal-header" id="kt_modal_create_api_key_header">
				<!--begin::Modal title-->
				<h5>Add IP</h5>
				<!--end::Modal title-->
				<!--begin::Close-->
				<button aria-label="Close" class="close" data-dismiss="modal" type="button">
					<span class="close-label"></span><span class="os-icon os-icon-close"></span>
				</button>
				<!--end::Close-->
			</div>
			<!--end::Modal header-->
			<!--begin::Form-->
			<form id="kt_modal_create_api_ip_form" class="form" method="post" data-dataTables="ip_list_table" role="update-ip" action="<?php echo e(custom_secure_url('user/accounts/add-ip')); ?>">
				<?php echo csrf_field(); ?>
				<input type="hidden" name="user_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
				<!--begin::Modal body-->
				<div class="modal-body py-10 px-lg-17">

					<!--begin::Scroll-->
					<div class="scroll-y me-n7 pe-7" id="" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_create_api_key_header" data-kt-scroll-wrappers="#kt_modal_create_api_key_scroll" data-kt-scroll-offset="300px">
						<div class="d-flex flex-column mb-10 fv-row">
							<label for="">Service <span class="requiredstar">*</span></label>
							<select name="service_id" class="form-control" id="service_id" data-control="select2" data-hide-search="true" data-placeholder="Select a Service..." class="form-select form-select-solid">
								<option value="">Select a Service...</option>
								<?php $__currentLoopData = $data['services']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($service->service_id); ?>"><?php echo e($service->service_name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<!--end::Input group-->
						<!--begin::Input group-->
						<div class="d-flex flex-column mb-10 fv-row">
							<!--begin::Label-->
							<label class="required fs-5 fw-bold mb-2">IP Address <span class="requiredstar">*</span></label>
							<input type="text" name="ip" class="form-control" minlength="7" maxlength="15" size="15" pattern="^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$" />
							<!--end::Select-->
						</div>

						<div class="d-flex flex-column mb-10 fv-row text-muted mt-3">
							* Maximum <?php echo e(LIMIT_IP_WHITELIST); ?> IPs can be whitelisted for a service.
						</div>

					</div>
					<!--end::Scroll-->

				</div>

				<!--end::Modal body-->
				<!--begin::Modal footer-->
				<div class="modal-footer flex-center">
					<!--begin::Button-->
					<button type="reset" id="kt_modal_create_api_key_cancelled" class="btn btn-white me-3">Reset</button>
					<!--end::Button-->
					<!--begin::Button-->


					<button type="submit" id="kt_modal_create_ip_submit" data-request="ajax-submit" data-target='[role="update-ip"]' data-targetform='kt_modal_create_api_ip_form' class="btn btn-primary">
						Add Ip
					</button>
					<!--end::Button-->
				</div>
				<!--end::Modal footer-->
			</form>
			<!--end::Form-->
		</div>
		<!--end::Modal content-->
	</div>
	<!--end::Modal dialog-->
</div><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/user/payout/modals/ip.blade.php ENDPATH**/ ?>