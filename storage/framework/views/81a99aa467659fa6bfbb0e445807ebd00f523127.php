<div class="onboarding-modal modal fade animated" id="kt_modal_batch_import" tabindex="-1" aria-hidden="true">
	<!--begin::Modal dialog-->
	<div class="modal-dialog ">
		<!--begin::Modal content-->
		<div class="modal-content">
			<!--begin::Modal header-->
			<div class="modal-header" style="border: 1px solid #e9ecef" id="kt_modal_create_api_key_header">
				<!--begin::Modal title-->
				<h5>Bulk Import</h5>
				<!--end::Modal title-->

				<!--begin::Close-->
				<button aria-label="Close" onclick="javascript:window.location.reload()" class="close" data-dismiss="modal" type="button">
					<span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
				<!--end::Close-->
			</div>
			<!--end::Modal header-->
			<!--begin::Form-->
			<form id="kt_modal_create_api_ip_form" class="form" method="post" role="update-ip" action="<?php echo e(url('payout/import-batch-file')); ?>" enctype="multipart/form-data">
				<?php echo csrf_field(); ?>
				<input type="hidden" name="user_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
				<!--begin::Modal body-->
				<div class="modal-body py-10 px-lg-17">
					<!--begin::Scroll-->
					<div class="scroll-y me-n7 pe-7" id="" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_create_api_key_header" data-kt-scroll-wrappers="#kt_modal_create_api_key_scroll" data-kt-scroll-offset="300px">
						<!--begin::Notice-->



						<!--end::Label-->


						<!--end::Input group-->
						<!--begin::Input group-->
						<div class="d-flex flex-column mb-10 fv-row">
							<!--begin::Label-->
							<input type="file" name="file" id="batchImportFile" class="form-control" onchange="checkfile(this);">
							<br>

							<!--end::Select-->
						</div>
						<!--end::Input group-->



					</div>
					<!--end::Scroll-->
				</div>
				<!--end::Modal body-->
				<!--begin::Modal footer-->
				<div class="modal-footer flex-center">
					<!--begin::Button-->
					<a href="<?php echo e(url('public/doc/xettle_sample_batch_payouts.csv')); ?>" class="btn btn-sm btn-success" id="" download>Download Sample</a>
					<!--end::Button-->
					<!--begin::Button-->


					<button type="submit" id="kt_modal_create_ip_submit" data-request="ajax-submit" data-target='[role="update-ip"]' data-targetform='kt_modal_create_api_ip_form' class="btn btn-primary">
						Submit
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
</div><?php /**PATH /home/pgpaysecureco/public_html/resources/views/user/payout/modals/batchImport.blade.php ENDPATH**/ ?>