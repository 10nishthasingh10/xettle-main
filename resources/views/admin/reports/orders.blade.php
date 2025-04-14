@extends('layouts.admin.app')
@section('title',ucfirst($page_title))
@section('content')
@section('style')
<link href="{{url('public/css/buttons.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
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

<!--begin::Table-->
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper ">
            <div class="element-box">
                <h5 class="form-header">
                    {{$page_title}}
                </h5>
                <div class="element-actions" style="margin-top: -2.2rem;">
                    <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#kt_modal_admincreate_order">
                        Add Order
                    </button> -->
               
                    <span class="btn btn-success" id="processingOrderUpdate">Processing Order </span>
                    <span class="btn btn-danger" id="queuedOrderFailed">Queued Order</span>
                </div>
                <div class="">&nbsp;</div>
                <div class="form-desc">
                </div>

                <form id="searchForm">

                    <fieldset class="form-group">

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Any Key <span class="requiredstar"></span></label>
                                    <input type="text" name="searchText" class="form-control" placeholder="Enter Search Key" />
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}" @endif />
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" @if(isset($_GET['to'])) value="{{$_GET['to']}}" @endif />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Filter Users <span class="requiredstar"></span></label>
                                    <select name="userId" id="target" class="form-control js-example-basic-multiple" multiple="multiple">
                                        <option value=""> Select</option>
                                        @foreach($user as $users)
                                        <option value="{{$users->id}}"> {{$users->name}} - {{$users->email}} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>Payout Root <span class="requiredstar"></span></label>
                                    <select name="integration_id" class="form-control">
                                        <option value="">Select</option>
                                        @foreach($roots as $root)
                                        <option value="{{$root->integration_id}}">{{$root->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>Status <span class="requiredstar"></span></label>
                                    <select name="status" class="form-control">
                                        <option value=""> Select</option>
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
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Mode</label>
                                    <select class="form-control" name="mode">
                                        <option value="">Select</option>
                                        <option value="IMPS">IMPS</option>
                                        <option value="NEFT">NEFT</option>
                                        <option value="RTGS">RTGS</option>
                                        <option value="UPI">UPI</option>
                                    </select>
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

                                <!-- <div class="form-group" style="margin-top:26px;">
                                    <button type="submit" class="btn btn-primary" data-loading-text="<b>
                                            <i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i>
                                        </b> Search</button>
                                    <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b>
                                            <i class='fa fa-spin fa-spinner'></i></b> Reset"><b><i class="icon-rotate-ccw3"></i></b> Reset</button>
                                </div> -->
                            </div>
                            <input type="hidden" name="queryString" id="queryString" value="{{$batchId}}" />
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Order Ref Id</th>
                                <th>Batch id</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Payout Mode</th>
                                <th>Status</th>
                                <th>Payout Root</th>
                                <th>Bank Reference</th>
                                <th>Created</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Total</th>
                                <th></th>
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

    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="reversedOrderModal" role="dialog" tabindex="-1">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Reversed Order
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="reversed-order-form" action="{{url('admin/order/reversed')}}" data-DataTables="datatable" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="message" />
                        <div class="form-group">
                            <label for=""> Remarks </label>
                            <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter remarks"></textarea>
                            <input class="form-control" type="hidden" name="orderRefId" id="order_ref_id" />
                            <input class="form-control" type="hidden" name="userId" id="user_id" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="reversed-order-form"]' value="Reversed Order" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include(USER.'.payout.modals.ordermodal')
    @include(USER.'.payout.modals.batchImport')
    @section('scripts')
    <script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
    <script src="{{url('public/js/pdfmake.min.js')}}"></script>
    <script src="{{url('public/js/jszip.min.js')}}"></script>
    <script src="{{url('public/js/vfs_fonts.js')}}"></script>
    <script src="{{url('public/js/buttons.html5.min.js')}}"></script>
    <script src="{{url('public/js/buttons.print.min.js')}}"></script>
    <script src="{{url('public/js/handlebars.js')}}"></script>


    <script id="details-template" type="text/x-handlebars-template">

        <table class="expandtable">
        <tr>
            <td><b>Contact Id :</b></td><td>@{{contact_id}}</td><td><b>Full name :</b></td><td>@{{contact.first_name}} @{{contact.last_name}}</td><td><b>Email :</b></td><td>@{{contact.email}}</td>
        </tr>
        <tr>
            <td><b>Account No :</b></td><td>@{{contact.account_number}}</td><td><b>IFSC :</b></td><td>@{{contact.account_ifsc}}</td><td><b>Vpa :</b></td><td>@{{contact.vpa_address}}</td>
        </tr>
		<tr><td><b>Order Id :</b></td><td> @{{order_id}}</td><td><b>Amount :</b></td><td> @{{amount}}</td><td><b>Currency :</td><td>@{{currency}}</td>
        </tr>
		<tr><td><b>Fee:</td><td>@{{fee}}</td><td><b>Tax :</b></td><td> @{{tax}}</td><td><b>Mode :</td><td>@{{mode}}</td>
        </tr>
		<tr><td><b>Fund Account Id :</b></td><td> @{{fund_account_id}}</td><td><b>Narration :</td><td>@{{narration}}</td>
        </tr>
		<tr><td><b>Remark:</td><td>@{{remark}}</td><td><b>Status:</b></td><td> @{{status}}</td><td><b>Created:</b></td><td> @{{new_created_at}}</td>
        </tr>
        <tr><td><b>Status Code:</td><td>@{{status_code}}</td><td><b>Status Response:</b></td><td> @{{status_response}}</td>
        </tr>
        <tr><td><b>Cancelled Message :</td><td>@{{cancellation_reason}}</td><td><b>Cancelled Date:</td><td>@{{cancelled_at}}</td><td><b>Failed Status Code:</td><td>@{{failed_status_code}}</td>
        </tr>
        <tr><td><b>Failed Message :</b></td><td> @{{failed_message}}</td><td><b>Bank Reference :</b></td><td> @{{bank_reference}}</td><td><b>Payout Reference:</td><td>@{{bulk_payout_detail.payout_reference}}</td>
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
        });

        $(document).ready(function() {
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
                            return data + '<br>/' + full.client_ref_id;
                        }
                    }
                },
                {
                    "data": "batch_id"
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
                    "data": "integration",
                    render: function(data, type, full, meta) {
                        if (full.integration == null) {
                            return '';
                        } else {
                            return data.name;
                        }

                    }
                },
                {
                    "data": "bank_reference"
                },
                {
                    "data": "new_created_at",
                },
                {
                    "data": "updated_at",
                    render: function(data, type, full, meta) {
                        if (full.updated_at != null) {
                            return full.new_updated_at;
                        } else {
                            return '';
                        }
                    }
                },
                {
                    "data": "status",
                    "orderable": false,
                    render: function(data, type, full, meta) {
                        if (data == 'processed' && "{{Auth::user()->hasRole('super-admin')}}") {
                            return '<span class="edit btn btn-danger btn-sm"><a href="javascript:void(0);" onclick="reversedOrder(\'' + full.order_ref_id + '\',\'' + full.user_id + '\')" data-target="#reversedOrderModal" tooltip="Reversed Order"  style="color:white;text-decoration:none" data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></a></span>';
                        } else {
                            return '';
                        }
                    }
                }
            ];

            datatableSetup(url, options, onDraw);
            $('.dataTables_wrapper').css("width", $(".table-responsive").width());
        });
        $('form#searchForm').submit(function() {
            $('#searchForm').find('button:submit').button('loading');
            var from = $(this).find('input[name="from"]').val();
            var to = $(this).find('input[name="to"]').val();
            $('#datatable').dataTable().api().ajax.reload();
            return false;
        });
        $('#queuedOrderFailed').on('click', function() {
            $.ajax({
                url: "{{url('admin/clearQueuedOrder')}}",
                type: 'GET',
                success: function(res) {
                    alert(res);
                },
                error: function(err) {
                    alert(err.responseJSON.message + ' Please try after 5 minutes.');
                }
            });
        });

        $('#processingOrderUpdate').on('click', function() {
            $.ajax({
                url: "{{url('admin/updateProcessingOrder')}}",
                type: 'GET',
                success: function(res) {
                    // console.log(res);
                    alert(res);
                },
                error: function(err) {
                    alert(err.responseJSON.message + ' Please try after 5 minutes.');
                }
            });
        });


    </script>

    <script type="text/javascript">
        function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
            var options = {
                processing: true,
                serverSide: true,
                ordering: true,
                scrollX: true,
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
                        .column(4, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(4).footer()).html(
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
                        d.mode = $('#searchForm').find('[name="mode"]').val();
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
            DT.search($('#queryString').val()).draw();
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
            $('.js-example-basic-multiple').select2();
        });

        function getContactId(id) {
            $id = id.value;
            $('#contactId').html('');
            $.get("{{custom_secure_url('admin/getContactByUserId')}}/" + $id, function(data, status) {
                if (data.status) {
                    $('#contactId').html(data.option);
                }
            });
        }
    </script>
    @endsection

    @endsection