<div class="onboarding-modal modal fade animated" id="kt_modal_settelement_logs" tabindex="-1" aria-hidden="true">
									<!--begin::Modal dialog-->
									<div class="modal-dialog ">
										<!--begin::Modal content-->
										<div class="modal-content">
											<!--begin::Modal header-->
											<div class="modal-header" >
												<!--begin::Modal title-->
												<h5>Add Settlement </h5>
												<!--end::Modal title-->
                                                <button aria-label="Close" class="close" data-dismiss="modal"  type="button">
												<span class="close-label"></span><span class="os-icon os-icon-close"></span></button>
											</div>
											<!--end::Modal header-->
											<!--begin::Form-->
                                            <form id="kt_modal_create_api_new_settlement_form" class="form" method="post" data-dataTables="datatable"
										role="add-settlement" action="{{url('admin/createSettlement')}}">
											@csrf
											<input type="hidden" name="user_id" id="user_id" />
                                            <input type="hidden" name="id" id="id" />
                                            <input type="hidden" name="settlement_ref_id"  id="settlement_ref_id" />
												<!--begin::Modal body-->
												<div class="modal-body py-10 px-lg-17">
													<!--begin::Scroll-->
													<div class="scroll-y me-n7 pe-7" id="" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_create_api_key_header" data-kt-scroll-wrappers="#kt_modal_create_api_key_scroll" data-kt-scroll-offset="300px">
													<div class="d-flex flex-column mb-10 fv-row">
                                                    <label for="">Route<span class="requiredstar">*</span></label>
                                                    <select name="integration_id" class="form-control"  id="integration_id"
                                                            data-control="select2" data-hide-search="true"
                                                            data-placeholder="Select a Service..." class="form-select form-select-solid">
																<option value="">Select a Route...</option>
																@foreach($integration as $integration)
                                                                <option
                                                                value="{{$integration->integration_id}}">{{$integration->name}}</option>
                                                                @endforeach
															</select>
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

													<button type="submit" id="kt_modal_create_settlement_submit"
													data-request="ajax-submit" data-target='[role="add-settlement"]'
												data-targetform='kt_modal_create_api_new_settlement_form' class="btn btn-primary">
													Add Settlement
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
								</div>