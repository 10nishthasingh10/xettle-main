<div class="onboarding-modal modal fade animated" id="kt_modal_create_api_key" tabindex="-1" aria-hidden="true">

	<div class="modal-dialog">

		<div class="modal-content">

			<div class="modal-header" id="kt_modal_create_api_key_header">

				<h5>Generate API Key</h5>

				<button aria-label="Close" class="close" data-dismiss="modal" type="button">
					<span class="close-label"></span><span class="os-icon os-icon-close"></span>
				</button>

			</div>

			<div class="modal-body">


				<form id="kt_modal_create_api_key_form" class="form" method="post" action="{{url('user/accounts/api-key-generate')}}" data-dataTables="kt_api_keys_table">
					@csrf
					<input type="hidden" name="user_id" value="{{encrypt(auth()->user()->id)}}" />

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="">Service<span class="requiredstar">*</span></label>
								<select name="service_id" class="form-control" id="service_idd" data-control="select2" data-hide-search="true" data-placeholder="Select a Service..." class="form-select form-select-solid">
									<option value="">Select a Service...</option>
									@foreach($data['services'] as $service)
									<option value="{{$service->service_id}}">{{$service->service_name}}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-md-12">
							<div class="text-right mt-3">
								<button type="button" data-dismiss="modal" id="kt_modal_create_api_key_cancel" class="btn btn-light mr-2">Close</button>
								<button type="submit" id="kt_modal_create_api_key_submit" class="btn btn-primary">Generate Key</button>
							</div>
						</div>
					</div>

				</form>


				<div class="row d-none" id="keydata">

					<div class="col-md-12 mb-3">
						<label>
							<h6>Client-Id</h6>
						</label>
						<div class="input-group">
							<input type="text" id="key_data" class="form-control" readonly="readonly" />
							<div class="input-group-append button-submit">
								<button title="Copy ID" type="button" class="btn btn-primary" id="copyBtnKey" data-copy-enable="true" data-copy-target="key_data">
									<i class="fas fa-copy"></i>
								</button>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<label>
							<h6>Client-Secret</h6>
						</label>
						<div class="input-group">
							<input type="text" id="secret_data" class="form-control" readonly="readonly" />
							<div class="input-group-append button-submit">
								<button title="Copy Secret" type="button" class="btn btn-primary" id="copyBtnSecret" data-copy-enable="true" data-copy-target="secret_data">
									<i class="fas fa-copy"></i>
								</button>
							</div>
						</div>
					</div>

				</div>
			</div>

		</div>

	</div>
</div>