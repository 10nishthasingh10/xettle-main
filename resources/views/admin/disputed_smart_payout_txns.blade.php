@extends('layouts.admin.app')
@section('title', $site_title)

@section('style')
<link href="{{url('public/css/buttons.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .expandtable {
        width: 100% !important;
        margin-bottom: 1rem;
    }

    .expandtable,
    tbody,
    tr,
    td {
        margin-bottom: 1rem;
    }

    .content-box {
        padding: 10px !important;
    }

    .element-box {
        padding: 1.5rem 1rem !important;
    }
</style>
@endsection

@section('content')
<!--begin::Table-->
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper ">
            <div class="element-box">
                <div class="element-actions">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#kt_modal_create_contact">
                        Add Contact
                    </button>
                </div>
                <h5 class="element-header">
                    {{$page_title}}
                </h5>

                <form id="kt_modal_new_order" method="post" role="Add-Order" class="form fv-plugins-bootstrap5 fv-plugins-framework" action="{{url('admin/orders/add')}}" data-dataTables="datatable">

                    @csrf
                    <!--begin::Input group-->
                    <div class="row g-9 mb-8">
                        <!--begin::Col-->
                        <div class="col-md-4 fv-row">
                            <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                <span class="required"> Name <span id="showAmount" style="color: green;"></span> </span>
                            </label>
                            <div>
                                <!--begin::Datepicker-->
                                <select class="form-control form-select select2" data-control="select2" data-hide-search="true" data-placeholder="Select a Beneficiary Name" onchange="getContactId(this)" name="user_id">
                                    <option value="">Select Name</option>
                                    @foreach($user as $users)
                                    <option value="{{$users->id}}">{{$users->name}} {{$users->email}}</option>
                                    @endforeach
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

                                <select class="form-control form-select select2" data-control="select2" data-hide-search="true" id="contactId" data-placeholder="Select a Contact" name="contact_id">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                <span class="required">Purpose</span>
                            </label>

                            <div>
                                <!--begin::Datepicker-->
                                <select class="form-control form-select" data-control="select2" data-hide-search="true" data-placeholder="Select a Purpose" name="purpose">

                                    <option value="refund">Refund</option>
                                    <option value="reimbursement">Reimbursement</option>
                                    <option value="bonus">Bonus</option>
                                    <option value="incentive">Incentive</option>
                                    <option value="SALARY_DISBURSEMENT">SALARY DISBURSEMENT</option>
                                    <option value="others">Others</option>

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
                            <input class="form-control" placeholder="Enter your Amount" name="amount" type="number" required="required">
                        </div>
                        <!--end::Col-->
                        <div class="col-md-4 fv-row">
                            <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                <name class="required">Narration</span>
                            </label>
                            <!--begin::Input-->

                            <!--begin::Datepicker-->
                            <input class="form-control" placeholder="Enter your Narration" name="narration" type="text" required="required">
                            <!--end::Datepicker-->
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                <span class="">Remark</span>
                            </label>
                            <textarea class="form-control" name="remark" placeholder="Enter your Remark"></textarea>
                        </div>
                        <!--end::Input-->
                    </div>
                    <!--begin::Col-->

                    <!--end::Col-->

                    <!--begin::Actions-->
                    <div class="modal-footer flex-center">
                        <button type="reset" id="kt_modal_new_target_cancel" class="btn btn-white me-3">Reset</button>
                        <button type="submit" id="kt_modal_new_target_submit" data-request="ajax-submit" data-target='[role="Add-Order"]' data-targetform='kt_modal_new_order' class="btn btn-primary">
                            <span class="indicator-label">Submit</span>
                            </span>
                        </button>
                    </div>
                    <!--end::Actions-->
                    <div></div>
                </form>

            </div>

            <div class="element-box">
                <h5 class="element-header">
                    Smart Payout Dispute List
                </h5>

                <form id="searchForm">

                    <fieldset class="form-group">

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Any Key <span class="requiredstar"></span></label>
                                    <input type="text" name="searchText" class="form-control" placeholder="Enter Search Key" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Payment Status <span class="requiredstar"></span></label>
                                    <select name="status" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="hold" @if(isset($_GET['status']) && $_GET['status']=='hold' ) selected @endif>Hold</option>
                                        <option value="queued" @if(isset($_GET['status']) && $_GET['status']=='queued' ) selected @endif>Queued</option>
                                        <option value="processing" @if(isset($_GET['status']) && $_GET['status']=='processing' ) selected @endif>Processing</option>
                                        <option value="processed" @if(isset($_GET['status']) && $_GET['status']=='processed' ) selected @endif>Processed</option>
                                        <option value="cancelled" @if(isset($_GET['status']) && $_GET['status']=='cancelled' ) selected @endif>Cancelled</option>
                                        <option value="reversed" @if(isset($_GET['status']) && $_GET['status']=='reversed' ) selected @endif>Reversed</option>
                                        <option value="failed" @if(isset($_GET['status']) && $_GET['status']=='failed' ) selected @endif>Failed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">

                                    <label>User <span class="requiredstar"></span></label>
                                    <select name="userId" id="target" class="form-control js-example-basic-multiple" multiple="multiple">
                                        <option value="">-- Select User --</option>
                                        @foreach($userData as $users)
                                        <option value="{{$users->id}}"> {{$users->name}} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}" @endif />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" @if(isset($_GET['to'])) value="{{$_GET['to']}}" @endif />
                                </div>
                            </div>
                            <div class="col-md-2">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                <b><i class="icon-search4"></i></b> Search
                                            </button>
                                            <button type="button" class="btn btn-warning btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Reset">
                                                <b><i class="icon-rotate-ccw3"></i></b> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Order Ref Id</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Payout Mode</th>
                                <th>Status</th>
                                <th>Bank Reference</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Total</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include(USER.'.payout.modals.contactmodal')

@endsection

@section('scripts')

<script src="{{asset('js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('js/pdfmake.min.js')}}"></script>
<script src="{{asset('js/jszip.min.js')}}"></script>
<script src="{{asset('js/vfs_fonts.js')}}"></script>
<script src="{{asset('js/buttons.html5.min.js')}}"></script>
<script src="{{asset('js/buttons.print.min.js')}}"></script>
<script src="{{asset('js/handlebars.js')}}"></script>

<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable">
        <tr>
            <td><b>Contact Id :</b></td><td>@{{contact_id}}</td>
            <td><b>Full name :</b></td><td>@{{contact.first_name}} @{{contact.last_name}}</td>
            <td><b>Email :</b></td><td>@{{contact.email}}</td>
        </tr>
        <tr>
            <td><b>Account No :</b></td><td>@{{contact.account_number}}</td>
            <td><b>IFSC :</b></td><td>@{{contact.account_ifsc}}</td>
            <td><b>Vpa :</b></td><td>@{{contact.vpa_address}}</td>
        </tr>
		<tr>
            <td><b>Order Id :</b></td><td> @{{order_id}}</td>
            <td><b>Amount :</b></td><td> @{{amount}}</td>
            <td><b>Currency :</td><td>@{{currency}}</td>
        </tr>
		<tr>
            <td><b>Fee:</td><td>@{{fee}}</td>
            <td><b>Tax :</b></td><td> @{{tax}}</td>
            <td><b>Mode :</td><td>@{{mode}}</td>
        </tr>
		<tr>
            <td><b>Purpose:</b></td><td> @{{purpose}}</td>
            <td><b>Fund Account Id :</b></td><td> @{{fund_account_id}}</td>
            <td><b>Narration :</td><td>@{{narration}}</td>
        </tr>
		<tr>
            <td><b>Remark:</td><td>@{{remark}}</td>
            <td><b>Status:</b></td><td> @{{status}}</td>
            <td><b>Created:</b></td><td> @{{new_created_at}}</td>
        </tr>
        <tr>
            <td><b>Payout Root:</td><td>@{{integration.name}}</td>
            <td><b>Status Code:</td><td>@{{status_code}}</td>
            <td><b>Status Response:</b></td><td> @{{status_response}}</td>
        </tr>
        <tr>
            <td><b>Cancelled Message :</td><td>@{{cancellation_reason}}</td>
            <td><b>Cancelled Date:</td><td>@{{cancelled_at}}</td>
            <td><b>Failed Status Code:</td><td>@{{failed_status_code}}</td>
        </tr>
        <tr>
            <td><b>Failed Message :</b></td><td> @{{failed_message}}</td>
            <td><b>Bank Reference :</b></td><td> @{{bank_reference}}</td>
            <td><b>Payout Reference:</td><td>@{{bulk_payout_detail.payout_reference}}</td>
        </tr>
	</table>
</script>
<script>
    $(document).ready(function() {

        var template = Handlebars.compile($("#details-template").html());

        // Add event listener for opening and closing details
        $('#datatable tbody').on('click', 'td.details-control', function() {
            var tr = $(this).closest('tr');
            var table = $("#datatable").DataTable();
            var row = table.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(template(row.data())).show();
                tr.addClass('shown');
            }
        });

        var url = "{{custom_secure_url('admin/fetch')}}/orders/0";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "targets": [],
                "defaultContent": ''
            },
            {
                "data": "order_ref_id",
                render: function(data, type, full, meta) {

                    if (full.client_ref_id == null || full.client_ref_id == undefined) {
                        return data;
                    } else {
                        return data + '<br>' + full.client_ref_id;
                    }
                }
            },
            {
                "data": "amount",
                render: function(data, type, full, meta) {
                    if (full.user == null || full.user == 'undefined') {
                        return 'NA';
                    } else {
                        return full.user.name + '<br>' + full.user.email + '<br>' + full.user.mobile;
                    }
                }
            },
            {
                "data": "amount"
            },
            {
                "data": "mode"
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    return showSpan(data);
                }
            },
            {
                "data": "bank_reference"
            },
            {
                "data": "new_created_at",
            },
        ];

        datatableSetup(url, options, onDraw);
    });
    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        return false;
    });

    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000],
                [10, 25, 50, 75, 100, 200, 500, 1000]
            ],
            dom: "Bfrltip",
            order: [],
            columnDefs: [{
                'targets': [0],
                /* column index [0,1,2,3]*/
                'orderable': false,
                /* true or false */
            }],
            language: {
                paginate: {
                    'first': 'First',
                    'last': 'Last',
                    'next': '&rarr;',
                    'previous': '&larr;'
                }
            },
            buttons: [
                'excel', 'pdf'
            ],
            drawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
            },
            preDrawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
            },
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;
                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // Total over this page
                pageTotal = api
                    .column(3, {
                        page: 'current'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(3).footer()).html(
                    '₹' + pageTotal
                );
            },
            ajax: {
                url: urls,
                headers: {
                    'Access-Control-Allow-Origin': '*'
                },
                type: "post",
                data: function(d) {
                    $("")
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.userId = $('#searchForm').find('[name="userId"]').val();
                    d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                    d.area = '22';
                    d.integration_id = $('#searchForm').find('[name="integration_id"]').val();
                },
                beforeSend: function() {},
                complete: function() {
                    $('#searchForm').find('button:submit').button('reset');
                    $('#formReset').button('reset');
                },
                error: function(response) {}
            },
            columns: datas
        };

        $.each(element, function(index, val) {
            options[index] = val;
        });

        var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
        // DT.search($('#queryString').val()).draw();
        return DT;
    }
    $('#formReset').click(function() {
        console.log(1);
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });

    function reversedOrder(order_ref_id, user_id) {
        $('#order_ref_id').val(order_ref_id);
        $('#user_id').val(user_id);
    }
    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });
        $('.js-example-basic-multiple').select2();
        $('.accountDetails').html(`	<div class="col-md-4 fv-row ">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Ifsc</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your ifsc" 
                                                                name="ifsc" type="text" required="required">
														</div>
														<!--begin::Col-->
														<div class="col-md-4 fv-row ">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Account Number</span>
                                                        </label>
															<!--begin::Input-->
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your account number" 
                                                                name="accountNumber" type="number" required="required">
																<!--end::Datepicker-->
														</div>
                                                      `);
        $('#accountTypeId').on('change', function() {
            if ($(this).find(":selected").val() == 'bank_account') {
                $('.accountDetails').html(`	<div class="col-md-4 fv-row ">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <span class="required">Ifsc</span>
                                                        </label>
                                                        <input class="form-control" placeholder="Enter your ifsc" 
                                                                name="ifsc" type="text" required="required">
														</div>
														<!--begin::Col-->
														<div class="col-md-4 fv-row ">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">Account Number</span>
                                                        </label>
															<!--begin::Input-->
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your account number" 
                                                                name="accountNumber" type="number" required="required">
																<!--end::Datepicker-->
														</div>
                                                     `);
            } else {
                $('.accountDetails').html(`<div class="col-md-4 fv-row">
                                                        <label class="d-flex align-items-center fs-4 fw-bold mb-2">
                                                        <name class="required">VPA Address</span>
                                                        </label>
															<!--begin::Input-->
																<!--begin::Datepicker-->
																<input class="form-control" placeholder="Enter your vpa address" 
                                                                name="vpaAddress" type="text" required="required">
																<!--end::Datepicker-->
														</div>

                                                     `);
            }
        })

    });

    function getContactId(id) {
        $id = id.value;

        $('select#userDetails').find('option').each(function(i, option) {
            if (option.value == $id) {
                $(this).attr('selected', true);
                $(this).attr('readonly', true);
                // option.selected = "selected";
            }
        });
        $('#contactId').html('');
        $.get("{{custom_secure_url('admin/getContactByUserId')}}/" + $id, function(data, status) {
            if (data.status) {
                $('#contactId').html(data.option);
                $('#showAmount').html(data.accountBalance);
            } else {
                $('#showAmount').html('( User service not found. )');
            }
        });
    }
</script>

@endsection