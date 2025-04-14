<div class="onboarding-modal modal fade animated"  id="kt_modal_create_contact" tabindex="-1"  aria-modal="true" role="dialog">
									<!--begin::Modal dialog-->
									<div class="modal-dialog modal-xl">
										<!--begin::Modal content-->
										<div class="modal-content ">
											<!--begin::Modal header-->
											<div class="modal-header" style="border: 1px solid #e9ecef">
                                            <h5 class="mb-3">Create New Contact</h5>
												<!--begin::Close-->
												<button aria-label="Close" class="close" data-dismiss="modal" type="button">
												<span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
												<!--end::Close-->
												
                
											</div>
											<!--begin::Modal header-->
											<!--begin::Modal body-->
											<div class="modal-body">
												<!--begin:Form-->
												<form id="kt_modal_new_contact" method="post" 
										role="Add-Contact"
                                                class="form fv-plugins-bootstrap5 fv-plugins-framework" action="{{route('contacts.store')}}">
												
													@csrf
													<!--begin::Input group-->
													<div class="row g-9 mb-8">
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">First Name</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your first name" name="first_name" 
                                                                type="text" required="required">
														</div>
														<!--end::Col-->
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="">Last Name</span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your last name" name="last_name" 
                                                                type="text" >
																<!--end::Datepicker-->
															</div>
															<!--end::Input-->
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Email</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your E-mail Address" name="email" 
                                                                type="email" required="required">
														</div>
														<!--end::Col-->
													</div>
														<!--end::Col-->
													
													<!--end::Input group-->

                                                    		<!--begin::Input group-->
													<div class="row g-9 mb-8">
														
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Mobile</span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your mobile number" name="mobile" 
                                                                type="number" required="required">
																<!--end::Datepicker-->
															</div>
															<!--end::Input-->
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Reference id</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your reference id" name="reference_id" 
                                                                type="text" required="required">
														</div>
														<!--end::Col-->
                                                        <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Type</span>
                                                        </label>
															<!--begin::Input-->
															<div>
																<!--begin::Datepicker-->
                                                                <select class="form-select form-control"
                                                                 data-control="select2" data-hide-search="true" 
                                                                 data-placeholder="Select a Type" name="type">
																<option value="">Select Type</option>
																<option value="employee">Employee</option>
																<option value="vendor">Vendor</option>
																<option value="customer">Customer</option>
																
															</select>
																<!--end::Datepicker-->
															</div>
                                                            </div>
															<!--end::Input-->
														</div>
														<!--end::Col-->
													
												

                                                   <!--begin::Input group-->
													<div class="row g-9 mb-8">
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Account Type</span>
                                                        </label>
														<div>
                                                        <select class="form-select form-control"
                                                                 data-control="select2" data-hide-search="true" 
                                                                 data-placeholder="Select a Type" name="accountType">
																<option value="">Select Account Type</option>
																<option value="bank_account">Bank Account</option>
																
																
															</select>
															</div>
														</div>
														<!--end::Col-->
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Name</span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your Bank Account Holder Name" 
                                                                name="name" type="text" required="required">
																<!--end::Datepicker-->
															</div>
															<!--end::Input-->
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Ifsc</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your ifsc" 
                                                                name="ifsc" type="text" required="required">
														</div>
														<!--end::Col-->
														</div>
														<!--end::Col-->
													
													<!--end::Input group-->
                                                    <!--begin::Input group-->
                                                    <div class="row g-9 mb-8">
														
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Account Number</span>
                                                        </label>
															<!--begin::Input-->
														
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your account number" 
                                                                name="accountNumber" type="number" required="required">
																<!--end::Datepicker-->
														</div>
															<!--end::Input-->
                                                            	<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Address1</span>
                                                        </label>
															
                                                                <textarea class="form-control" required="required"
                                                                name="address1" placeholder="Enter your address1"></textarea>
															
														</div>
															<!--end::Input-->
                                                            	<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="">Address2</span>
                                                        </label>
															
                                                                <textarea class="form-control" required="required" name="address2" placeholder="Enter your address2"></textarea>
															
														</div>
															<!--end::Input-->
													</div>
														<!--end::Col-->
													
													<!--end::Input group-->
                                    
														<!--end::Col-->
                                                        <!--begin::Input group-->
                                                        <div class="row g-9 mb-8">
														<!--begin::Col-->
                                                        
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">State</span>
                                                        </label>
                                                        <div>
                                                        <select class="form-select form-control"
                                                                 data-control="select2" data-hide-search="true" 
                                                                 data-placeholder="Select a State" name="state">
																<option value="">Select State</option>
                                                                @foreach($states as $state)
																<option value="{{$state->state_name}}">{{$state->state_name}}</option>
																@endforeach
																
															</select>
                                                            </div>
														</div>
														<!--end::Col-->
														<!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">City</span>
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
                                                        <span class="required">Pin Code</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your pincode" 
                                                                name="pincode" type="number" required="required">
														</div>
														<!--end::Col-->
													</div>
														<!--end::Col-->

                                                        <!--begin::Input group-->
                                                        <div class="row g-9 mb-8">
                                                        <div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Note 1</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your Notes 1" 
                                                                name="randomKey1" type="text" value="Your Notes 1" required="required">
														</div>
													
                                                            <!--begin::Col-->
														<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Note 2</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your Notes 2" 
                                                                name="randomKey2" type="text" value="Your Notes 2" required="required">
														</div>
														<!--end::Col-->
													</div>
														<!--end::Col-->
													<!--begin::Actions-->
													<div class="modal-footer flex-center">
														<button type="reset" id="kt_modal_new_target_cancel" class="btn btn-white me-3">Cancel</button>
														<button type="submit" id="kt_modal_new_target_submit"
                                                        
                                                        data-request="ajax-submit" data-target='[role="Add-Contact"]'
												data-targetform='kt_modal_new_contact'
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
								</div>