@extends('layouts.payout.app')
@section('title','Payout Dashboard')
@section('content')

<!--begin::Content-->
<div class="content-i">
            <div class="content-box">
              <div class="element-wrapper">
              
				
			
                <div class="element-box">
					<h5>API Overview</h5>
					<div class="element-actions">
					<a href="#" class="btn btn-sm btn-primary" onclick="regenerateApiKey()"
											>{{GENERATE_NEW_API_KEY}}</a>
											
											
					</div>
				
                  <div class="form-desc">
                   &nbsp;
                  </div>

								
								<!--begin::API keys-->
								<div class="card mb-5 mb-xxl-10">
									<!--begin::Header-->
									
									<!--end::Header-->
									<!--begin::Body-->
									<div class="card-body p-10">
										<!--begin::Table wrapper-->
										<div class="table-responsive">
											<!--begin::Table-->
											<table class="table table-flush align-middle 
											 table-row-bordered table-row-solid gy-4 gs-9" id="kt_api_keys_table">
												<!--begin::Thead-->
												<thead class="border-gray-200 fs-5 fw-bold bg-lighten ">
													<tr>
														
														<th class="w-275px min-w-120px px-0">API Keys</th>
														
														
														<th class="min-w-125px min-w-90px">Created At</th>
														<th class="min-w-125px min-w-90px">Action</th>
														
													</tr>
												</thead>
												<!--end::Thead-->
												<!--begin::Tbody-->
												<tbody class="fs-6 fw-bold text-gray-600">
													
												</tbody>
												<!--end::Tbody-->
											</table>
											<!--end::Table-->
										</div>
										<!--end::Table wrapper-->
									</div>
									<!--end::Body-->
									</div>
								</div>
								<!--end::API keys-->
								<div class="element-box">
										<h5>IP White List</h5>
										<div class="element-actions">
										<a href="#" class="btn btn-sm btn-primary" data-toggle="modal"
															data-target="#kt_modal_create_ipwhite" id="kt_toolbar_primary_button">
															Add Ip</a>

										</div>
										<div class="form-desc">
									&nbsp;
									</div>
                 
								<div class="card mb-5 mb-xxl-10">
									<!--begin::Header-->

								<div class="card-body p-10">
										<!--begin::Table wrapper-->
										<div class="table-responsive">
											<!--begin::Table-->
											<table class="table table-flush align-middle 
											 table-row-bordered table-row-solid gy-4 gs-9" id="kt_ip_list_table">
												<!--begin::Thead-->
												<thead class="border-gray-200 fs-5 fw-bold bg-lighten ">
													<tr>
														<th class="w-275px min-w-250px px-0">Ip</th>
													    <th class="min-w-125px min-w-105px">Created At</th>
														<th class="min-w-125px min-w-105px">Action</th>
														</tr>
												</thead>
												<!--end::Thead-->
												<!--begin::Tbody-->
												<tbody class="fs-6 fw-bold text-gray-600">
													
												</tbody>
												<!--end::Tbody-->
											</table>
											<!--end::Table-->
										</div>
										<!--end::Table wrapper-->
									</div>
									<!--end::Body-->
								<!--begin::Modals-->
                              </div>
								<!--end::API keys-->
								</div>
										<!--end::Table wrapper-->
								@include(USER.'.payout.modals.webhook')
									<!--end::Body-->
								<!--begin::Modals-->
								@include(USER.'.payout.modals.ip')
									<!--begin::Modal - Create Api Key-->

								@include(USER.'.payout.modals.apikey')

								<!--end::Modals-->

							</div>
							<!--end::Container-->
						</div>
						<!--end::Post-->
					</div>
					<!--end::Content-->

					@section('scripts')
<script type="text/javascript">
  	$(function () {

    var table = $('#kt_api_keys_table').DataTable({
        processing: true,
        serverSide: true,
		searching: false,
        ajax: "{{ route('apikeys.index') }}",
        columns: [
            {data: 'client_key', name: 'client_key'},
            {data: 'created_at', name: 'created_at'},
			{data: 'Action', name: 'Action'},
        ]
    });

	var table = $('#kt_ip_list_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('iplist.index') }}",
        columns: [
            {data: 'ip', name: 'ip'},
            {data: 'created_at', name: 'created_at'},
            {data: 'Action', name: 'Action'},
        ]
    });
});

function deleteIp(id){
    $.ajax({
    url: "{{url('api/v1/accounts/ipDelete/')}}/"+id,
    type: 'GET',
    success: function(res) {
		swal.fire("Great Job","Ip deleted Successfull","success");
		setTimeout(function(){
            window.location.href = res.redirect;
        },2000);
    }
});
}


	function deActivateApiKey(id){
			$.ajax({
			url: "{{url('api/v1/accounts/deActivateKey/')}}/"+id,
			type: 'GET',
			success: function(res) {
				swal.fire("Great Job","DeActivate Key  Successfully","success");
				setTimeout(function(){
					window.location.href = res.redirect;
				},2000);
			}
		});
	}
	function regenerateApiKey(){
					Swal.fire({
			title: 'Are you sure?',
			text: "You won't be a new api key generate",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes'
			}).then((result) => {
			if (result.isConfirmed) {

				$('#kt_modal_create_api_key_submit').click();
				$('#kt_modal_create_api_key').modal('show');
				$('#kt_modal_create_api_key_submit').hide();
			}
			})
		}
</script>
                    @endsection
@endsection
