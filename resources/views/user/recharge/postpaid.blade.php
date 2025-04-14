@extends('layouts.user.app')
@section('title',ucfirst($page_title))
@section('style')
<link href="{{url('public/css/buttons.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
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

    @media screen and (min-width: 767px) {
        #datatable_length {
            margin-top: 0;
        }
    }

    .content-box {
        padding: 8px !important;
    }

    .element-box {
        padding: 1.5rem 0.8rem !important;
    }
</style>
<!--begin::Table-->
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper">
            <div class="element-box">
                <h5 class="form-header">
                    {{$page_title}}
                </h5>
                <div class="element-actions">
                </div>
                <div class="form-desc">
                    &nbsp;
                </div>
                <form id="searchForm">
                    <fieldset class="form-group">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Any Key <span class="requiredstar"></span></label>
                                    <input type="text" name="searchText" class="form-control" placeholder="Enter Search Key" />
                                    <input type="hidden" name="tr_type" value="dr" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" id="fromDate" @if(isset($_GET['from'])) value="{{$_GET['from']}}"  @endif />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" id="toDate" @if(isset($_GET['to'])) value="{{$_GET['to']}}"  @endif />
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

                <div class="table-responsive custom-table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th>  </th>
                                <th>Order Id</th>
                                <th>Customer Ref Id</th>
                                <th>Amount</th>
                                <th>Bank Txn Id</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--------------------
            START - Color Scheme Toggler
        ------------------ -->
    </div>
</div>

@section('scripts')
<script src="{{asset('js/handlebars.js')}}"></script>
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable">
        <tr>
            <td><b>UserId :</b> @{{user_id}}</td>
            <td><b>User Name :</b> @{{user.name}}</td>
            <td><b>Account Number :</b> @{{user.account_number}}</td>
        </tr>
		<tr>
            <td><b>User Mobile :</b> @{{user.mobile}}</td>
            <td><b>User Email :</b> @{{user.email}}</td>
            <td><b>Total Amount :</b> @{{total_amount}}</td>
        </tr>
        <tr>
            <td><b>Fee :</b> @{{fee}}</td>
            <td><b>Tax :</b> @{{tax}}</td>
            <td><b>Txn Id :</b> @{{txn_id}}</td>
        </tr>
        <tr>
            <td><b>Description :</b> @{{description}}</td>
            <td><b>Operators :</b> @{{op}}</td>
            <td><b>Circle Id :</b> @{{cir}}</td>
        </tr>
        <tr>
            <td><b>CellNumber :</b> @{{cn}}</td>
        </tr>
    </table>
</script>

<script>
    var selectFilterService;
    $(document).ready(function() {

        var template = Handlebars.compile($("#details-template").html());
        $('#datatable tbody').on('click', 'td.details-control', function() {
            var tr = $(this).closest('tr');
            var table = $("#datatable").DataTable();
            var row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(template(row.data())).show();
                tr.addClass('shown');
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
        var url = "{{custom_secure_url('user/fetch')}}/rechargePostPaid/0";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": true,
                "defaultContent": '',
            },
            {
                "data": "original_order_id",
                    render: function(data, type, full, meta) {
                        if (full.trans_id != null && full.trans_id != undefined) {
                            return full.trans_id;
                        } else {
                            if (full.original_order_id != null && full.original_order_id != undefined) {
                                return full.original_order_id;
                            }
                        }
                    }
            },
            {
                "data": "customer_ref_id",
            },
            {
                "data": "amount",
            },
            {
                "data": "bank_txn_id",
            },
            {
                "data": "status",
            },
            {
                "data": "new_created_at"
            },
        ];
        datatableSetup(url, options, onDraw);
        // $('.dataTables_wrapper').css("width",$(".table-responsive").width());
    });
    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        return false;
    });
</script>
<script type="text/javascript">
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
            buttons: [
                'excel', 'pdf'
            ],
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
            drawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
            },
            preDrawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
            },
            ajax: {
                url: urls,
                type: "post",
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                    d.tr_identifiers = '';
                    d.service_id_array = $('#searchForm').find('[id="services"]').val();
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
        return DT;
    }
    $('#formReset').click(function() {
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });
    $('#services').on('change', function() {
            $('#trIdentifiers').html('');
            var service = $(this).val();
            $.post('/getIndentifiers',{"service" : service, "_token" : "{{csrf_token()}}"},function(data, status){
                $('#trIdentifiers').html(data);
            });
        });
</script>
@endsection
@endsection