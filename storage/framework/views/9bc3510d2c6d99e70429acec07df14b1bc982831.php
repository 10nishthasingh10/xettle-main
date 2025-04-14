<div class="onboarding-modal modal fade animated"  id="addMerchants" tabindex="-1"  aria-modal="true" role="dialog">
									<!--begin::Modal dialog-->
									<div class="modal-dialog modal-xl">
										<!--begin::Modal content-->
										<div class="modal-content ">
											<!--begin::Modal header-->
											<div class="modal-header" >
                                            <h5 class="mb-3">Create New Merchants</h5>
												<!--begin::Close-->
												<button aria-label="Close" class="close" data-dismiss="modal" type="button">
												<span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
												<!--end::Close-->
												
                
											</div>
											<!--begin::Modal header-->
											<!--begin::Modal body-->
											<div class="modal-body">
                                            <div class="element-box-content">
                                                <center><div class="messages" style="color:red;" >
                                            </div> </center>
												<!--begin:Form-->
												<form id="kt_modal_new_merchants" method="post" 
										role="Add-Merchants"
                                                class="form fv-plugins-bootstrap5 fv-plugins-framework" action="<?php echo e(url('upi/addMerchant')); ?>">
												
													<?php echo csrf_field(); ?>
													<!--begin::Input group-->
													<div class="row g-9 mb-8">
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Business Name <span class="requiredstar"> *</span></span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your business name" name="merchantBusinessName" 
                                                                type="text" required="required">
														</div>
														<!--end::Col-->
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="">VPA <span class="requiredstar"> *</span></span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter Your VPA" name="merchantVirtualAddress" 
                                                                type="text" >
																<!--end::Datepicker-->
															</div>
															<!--end::Input-->
                                                        	<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Pan No <span class="requiredstar"> *</span></span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your pan number" name="panNo" 
                                                                type="text" required="required">
																<!--end::Datepicker-->
															</div>
															<!--end::Input-->
													</div>
														<!--end::Col-->
													
													<!--end::Input group-->

                                                    		<!--begin::Input group-->
													<div class="row g-9 mb-8">
														
													
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Email <span class="requiredstar"> *</span></span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your email" name="contactEmail" 
                                                                type="email" required="required">
														</div>
														<!--end::Col-->
                                                        <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">GSTIN <span class="requiredstar"> *</span></span>
                                                        </label>
															<!--begin::Input-->
															<div>
																<!--begin::Datepicker-->
                                                                <input class="form-control" placeholder="Enter your gstin" name="gstn" 
                                                                type="text" required="required">
																<!--end::Datepicker-->
															</div>
                                                            </div>
															<!--end::Input-->
															<!--begin::Col-->
													<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Business Type <span class="requiredstar"> *</span></span>
                                                        </label>
														<div>
                                                        <select class="form-control"
                                                                 data-control="select2" data-hide-search="true" 
                                                                 data-placeholder="Select a Type" name="merchantBusinessType">
																<option value="">Select Business Type</option>
																<option value="1">Individual- HUF</option>
																<option value="2">Partnership</option>
                                                                <option value="3">Companies registered under AcT</option>
                                                                <option value="4">Govt/ Govt Undertakings</option>
                                                                <option value="41">Proprietor</option>
                                                                <option value="42">Individuals / Professionals</option>
                                                                <option value="44">Regd Trusts</option>
                                                                <option value="45">LLPs</option>
																
															</select>
															</div>
														</div>
														<!--end::Col-->
														</div>
														<!--end::Col-->
													
													

                                                   <!--begin::Input group-->
													<div class="row g-9 mb-8">
													
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Per Day Transaction Count <span class="requiredstar"> *</span></span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your per day transaction count" 
                                                                name="perDayTxnCount" type="number" required="required">
																<!--end::Datepicker-->
															</div>
															<!--end::Input-->
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Per Day Transaction Limit <span class="requiredstar"> *</span></span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your per day transaction limit" 
                                                                name="perDayTxnLmt" type="number" required="required">
														</div>
														<!--end::Col-->
															<!--begin::Col-->
															<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Per Day Transaction Amount <span class="requiredstar"> *</span></span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your per day transaction amount" 
                                                                name="perDayTxnAmt" type="number" required="required">
																<!--end::Datepicker-->
														</div>
															<!--end::Input-->
														</div>
														<!--end::Col-->
													
													<!--end::Input group-->
                                                    <!--begin::Input group-->
                                                    <div class="row g-9 mb-8">
														
													
                                                            	<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Mobile <span class="requiredstar"> *</span></span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your mobile" 
                                                                name="mobile" type="number" required="required">
															
														</div>
															<!--end::Input-->
                                                            	<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="">Address <span class="requiredstar"> *</span></span>
                                                        </label>
															
                                                                <textarea class="form-control" required="required" name="address" placeholder="Enter your address"></textarea>
															
														</div>
														
														
                                                        
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">State <span class="requiredstar"> *</span></span>
                                                        </label>
                                                        <div>
                                                        <select class="form-control"
                                                                 data-control="select2" data-hide-search="true" 
                                                                 data-placeholder="Select a State" name="state">
																<option value="1">Andhra Pradesh</option>
																<option value="2">Arunachal Pradesh</option>
                                                                <option value="3">Assam</option>
                                                                <option value="4">Bihar</option>
                                                                <option value="5">Chhattisgarh</option>
                                                                <option value="6">Goa</option>
                                                                <option value="7">Gujarat</option>
                                                                <option value="8">Haryana</option>
                                                                <option value="9">Himachal Pradesh</option>
                                                                <option value="10">Jammu and Kashmir </option>
                                                                <option value="11">Jharkhand</option>
                                                                <option value="12">Karnataka</option>
                                                                <option value="13">Kerala</option>
                                                                <option value="14">Madhya Pradesh</option>
                                                                <option value="15">Maharashtra</option>
                                                                <option value="16">Manipur</option>
                                                                <option value="17">Meghalaya</option>
                                                                <option value="18">Mizoram</option>
                                                                <option value="19">Nagaland</option>
                                                                <option value="20">Odishay</option>
                                                                <option value="21">Punjab</option>
                                                                <option value="22">Rajasthan</option>
                                                                <option value="23">Sikkim</option>
                                                                <option value="24">Tamil Nadu</option>
                                                                <option value="25">Telangana</option>
                                                                <option value="26">Tripura</option>
                                                                <option value="27">Uttarakhand</option>
                                                                <option value="28">Uttar Pradesh</option>
                                                                <option value="29">West Bengal</option>
                                                                <option value="30">Andaman and Nicobar Islands</option>
                                                                <option value="31">Chandigarh</option>
                                                                <option value="32">Dadra and Nagar Haveli</option>
                                                                <option value="33">Daman and Diu</option>
                                                                <option value="34">Delhi </option>
                                                                <option value="35">Lakshadweep</option>
                                                                <option value="36">Puducherry</option>
															</select>
                                                            </div>
														</div>
														<!--end::Col-->
													</div>
														<!--end::Col-->
													
													<!--end::Input group-->
                                    
														<!--end::Col-->
                                                     	<!--end::Input-->
															   <!--begin::Input group-->
															   <div class="row g-9 mb-8">
														<!--begin::Col-->
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">City <span class="requiredstar"> *</span></span>
                                                        </label>
															<!--begin::Input-->
                                                            <div>
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your City" 
                                                                name="city" type="text" required="required">
                                                            </div>
																<!--end::Datepicker-->
															</div>
															<!--end::Input-->
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Pin Code <span class="requiredstar"> *</span></span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your pincode" 
                                                                name="pinCode" type="number" required="required">
														</div>
														<!--end::Col-->
													</div>
														<!--end::Col-->
													</div>
														<!--end::Col-->
													<!--begin::Actions-->
													<div class="modal-footer flex-center">
														<button type="reset" id="kt_modal_new_target_cancel" class="btn btn-white me-3">Cancel</button>
														<button type="submit" id="kt_modal_new_target_submit"
                                                        
                                                        data-request="ajax-submit" data-target='[role="Add-Merchants"]'
												data-targetform='kt_modal_new_merchant'
                                                         class="btn btn-primary">
															<span class="indicator-label">Submit</span>
															</span>
														</button>
													</div>
													<!--end::Actions-->
												<div></div></form>
												<!--end:Form-->
											</div>
											<!--end::Modal body-->
										</div>
										<!--end::Modal content-->
									</div>
									<!--end::Modal dialog-->
								</div><?php /**PATH /home/pgpaysecureco/public_html/resources/views/user/upi/add_merchants.blade.php ENDPATH**/ ?>