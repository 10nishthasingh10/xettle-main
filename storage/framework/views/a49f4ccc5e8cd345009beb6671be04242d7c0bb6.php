<div class="onboarding-modal modal fade animated" id="kt_modal_admincreate_order" tabindex="-1"  aria-modal="true" role="dialog">
									<!--begin::Modal dialog-->
									<div class="modal-dialog modal-xl">
										<!--begin::Modal content-->
										<div class="modal-content ">
											<!--begin::Modal header-->
											<div class="modal-header" style="border: 1px solid #e9ecef">
                                            <h5 class="mb-3">Create New Order</h5>
												<!--begin::Close-->
												<button aria-label="Close" class="close" data-dismiss="modal" type="button">
												<span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
												<!--end::Close-->
											</div>
											<!--begin::Modal header-->
											<!--begin::Modal body-->
											<div class="modal-body ">
												<!--begin:Form-->
												<form id="kt_modal_new_order" method="post" 
										role="Add-Order"
                                                class="form fv-plugins-bootstrap5 fv-plugins-framework" action="<?php echo e(route('orders.store')); ?>">
												
													<?php echo csrf_field(); ?>
													<!--begin::Input group-->
													<div class="row g-9 mb-8">
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required"> Name</span>
                                                        </label>
                                                        <div>
																<!--begin::Datepicker-->
                                                                <select class="form-control form-select"
                                                                 data-control="select2" data-hide-search="true"
                                                                 data-placeholder="Select a Beneficiary Name" onchange="getContactId(this)" name="contact_id">
																<option value="">Select  Name</option>
																<?php $__currentLoopData = $user; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																<option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> <?php echo e($user->email); ?></option>
																<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
															</select>
																<!--end::Datepicker-->
															</div>
														</div>
														<!--end::Col-->
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Contact Details</span>
                                                        </label>
                                                        <div>

														<select class="form-control form-select"
                                                                 data-control="select2" data-hide-search="true" id="contactId"
                                                                 data-placeholder="Select a Contact" name="contactId">
															</select>
															</div>
														</div>
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Purpose</span>
                                                        </label>
													
                                                                <div>
																<!--begin::Datepicker-->
                                                                <select class="form-control form-select"
                                                                 data-control="select2" data-hide-search="true" 
                                                                 data-placeholder="Select a Purpose" name="purpose">
																<option value="">Select Purpose</option>
                                                                <option value="refund">Refund</option>
                                                                <option value="SALARY_DISBURSEMENT">SALARY DISBURSEMENT</option>
																
															</select>
																<!--end::Datepicker-->
															</div>
																<!--end::Datepicker-->
															</div>
															</div>
                                                    		<!--begin::Input group-->
															<div class="row g-9 mb-8">

														<!--begin::Col-->
										
															<!--end::Input-->
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Amount</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your Amount"
                                                         name="amount"  type="number" required="required">
														</div>
														<!--end::Col-->
                                                        <!--begin::Col-->
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Mode</span>
                                                        </label>
														<div>
                                                        <select class="form-control form-select"
                                                                 data-control="select2" data-hide-search="true" 
                                                                 data-placeholder="Select a Mode" name="mode">
																<option value="">Select Mode</option>
																<option value="IMPS">IMPS</option>
																<option value="NEFT">NEFT</option>
																
															</select>
															</div>
														</div>
														<!--end::Col-->
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Narration</span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your Narration" 
                                                                name="narration" type="text" required="required">
																<!--end::Datepicker-->
															</div>
															<!--end::Input-->
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="">Remark</span>
                                                        </label>
                                                        <textarea class="form-control" 
                                                                name="remark" placeholder="Enter your Remark"></textarea>
														</div>
														<!--end::Col-->
														</div>
														<!--end::Col-->
													
												
												
                                                       
													<!--begin::Actions-->
													<div class="modal-footer flex-center">
														<button type="reset" id="kt_modal_new_target_cancel" class="btn btn-white me-3">Cancel</button>
														<button type="submit" id="kt_modal_new_target_submit"
                                                        
                                                        data-request="ajax-submit" data-target='[role="Add-Order"]'
												data-targetform='kt_modal_new_order'
                                                         class="btn btn-primary">
															<span class="indicator-label">Submit</span>
															</span>
														</button>
													</div>
													<!--end::Actions-->
												<div></div>
											</form>
												<!--end:Form-->
											</div>
											<!--end::Modal body-->
										</div>
										<!--end::Modal content-->
									</div>
									<!--end::Modal dialog-->
								</div><?php /**PATH /home/pgpaysecureco/public_html/resources/views/user/payout/modals/ordermodal.blade.php ENDPATH**/ ?>