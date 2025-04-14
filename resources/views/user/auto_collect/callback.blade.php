@extends('layouts.user.app')
@section('title',ucfirst($site_title))
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
        padding: 10px !important;
    }

    .element-box {
        padding: 1.5rem 1rem !important;
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
                                    <input type="date" name="from" class="form-control" id="fromDate" @if(isset($_GET['from'])) value="{{$_GET['from']}}"   @endif />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" id="toDate" @if(isset($_GET['to'])) value="{{$_GET['to']}}"    @endif />
                                </div>
                            </div>

                            <div class="col-md-2">

                                <div class="row">
                                <input type="hidden" name="queryString" id="queryString" value="{{@$_GET['bank_ref']}}" />
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
                                <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                <th>VAN/VPA</th>
                                <th>UTR</th>
                                <th>Batch ID</th>
                                <th>Amount</th>
                                <th>Payer Bank/VPA</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@section('scripts')
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
<script src="{{url('public/js/handlebars.js')}}"></script>
<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable table table-sm">
        <tr>
            <td><b>Account ID :</b> @{{v_account_id}}</td>
            <td><b>Ref. No. :</b> @{{ref_no}}</td>
            <td><b>Payment Time :</b> @{{payment_time}}</td>
        </tr>
        <tr>
            <td><b>Payer Name :</b> @{{remitter_name}}</td>
            <td colspan="2"><b>Remarks :</b> @{{remarks}}</td>
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
        var url = "{{custom_secure_url('collect/fetch')}}/autocollect_callbacks";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": '',
            },
            {
                "data": "id",
                render: function(data, type, full, meta) {
                    if (full.v_account_number)
                        return full.v_account_number;
                    else if (full.virtual_vpa_id)
                        return full.virtual_vpa_id;
                    else
                        return '';
                }
            },
            {
                "data": "utr"
            },
            {
                "data": "batch_id",
                render: function(data, type, full, meta) {
                    if (data == null || data == '')
                        return '';
                    else
                        return data;
                }
            },
            {
                "data": "amount"
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {
                    if (full.is_vpa === '1')
                        return full.remitter_vpa;
                    else if (full.is_vpa === '0')
                        return `Acc No: ${full.remitter_account} <br> IFSC: ${full.remitter_ifsc}`;
                    else
                        return '';
                }
            },
            {
                "data": "is_vpa",
                render: function(data, type, full, meta) {
                    if (full.is_vpa === '1')
                        return 'UPI';
                    else if (full.is_vpa === '0')
                        return 'VAN';
                    else
                        return '';
                }
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {
                    if (full.is_trn_disputed === '1') {
                        return showSpan('disputed');
                    } else if (full.is_trn_credited === '1') {
                        return showSpan('settled');
                    } else if (full.is_trn_credited === '0' && full.is_trn_settle === '0') {
                        return showSpan('unsettled');
                    } else {
                        return '';
                    }
                },
            },
            {
                "data": "new_created_at",
            }
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
                'excel'
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
                    $("")
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
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
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });
</script>
@endsection
@endsection