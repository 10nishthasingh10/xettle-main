<div class="onboarding-modal modal fade animated" id="verifyOtpModal" tabindex="-1" aria-hidden="true">
									<!--begin::Modal dialog-->
									<div class="modal-dialog ">
										<!--begin::Modal content-->
										<div class="modal-content">
											<!--begin::Modal header-->
											<div class="modal-header"  id="kt_modal_create_api_key_header">
												<!--begin::Modal title-->
												<h5>Verify OTP</h5>
												<!--end::Modal title-->
												<!--begin::Close-->
												<button aria-label="Close" onclick="javascript:window.location.reload()" class="close" data-dismiss="modal" type="button">
												<span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
												<!--end::Close-->
											</div>
                                            <div class="modal-body">
                                                <form id="Verify-otp" class="showotpform form"
                                                                        action="<?php echo e(url('payout/verifyotpforbulkpayout')); ?>" method="POST"  role="Verify-otp">
                                                                        <?php echo csrf_field(); ?>
                                                                        <span class="deliveryreponse"></span><br/>
                                                            <span class="deliveryreponseOtp"></span>
                                                        <div class="form-group">
                                                            <label for="">Otp <span class="help-block">*</span></label>
                                                            <input type="hidden" name="user_id"  class="form-control" id="encrypt_user_id" />
                                                            <input type="hidden" name="batch_id"  class="form-control" id="bulkbatchId" />
                                                            <input type="number" name="otp" tabindex="2" class="form-control" placeholder="OTP">
                                                            <span class="<?php if($errors->first('otp')): ?>help-block <?php endif; ?>"><?php echo e($errors->first('otp')); ?> </span>
                                                        </div>
                                                        <div class="modal-footer">

                                                        <button type="submit"  name="verifyotp-submit"
                                                        id="verifyotp-submit" tabindex="4" class="btn btn-primary" data-request="ajax-submit"  data-target='[role="Verify-otp"]' >Verify OTP</button>
                                                        <span class="btn btn-success" id="resendOtpOrder" style="display: none">Resend OTP</span>
                                                        </div>

                                                </form>
                                            </div>
                                            <!--end::Form-->
										</div>
										<!--end::Modal content-->
									</div>
									<!--end::Modal dialog-->
								</div><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/user/payout/modals/verifyotp.blade.php ENDPATH**/ ?>