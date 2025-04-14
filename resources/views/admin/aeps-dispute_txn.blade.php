@extends('layouts.admin.app')
@section('title', $site_title)
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

    .expandtable b,
    #utr-response-data tbody td b {
        color: #047bf8;
    }

    .content-box {
        padding: 10px !important;
    }

    .element-box {
        padding: 1.5rem 1rem !important;
    }
</style>
<div class="content-w">
    <div class="content-box">
        <div class="element-wrapper">
            <div class="element-box">
                <h5 class="form-header">
                    {{$page_title}}
                </h5>

                <form id="rv-txn-form" role="rv-txn-us-form" action="{{url('admin/dispute-transactions/aeps-fetch/submit')}}" data-DataTables="datatable" method="POST">

                    <fieldset class="form-group">

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    @csrf()
                                    <label for="">Enter RRN <span class="requiredstar">*</span></label>
                                    <input type="text" name="utr" class="form-control" required />
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-90px" data-callbackfn="callbackDispute" data-request="ajax-submit" data-target='[role="rv-txn-us-form"]' id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Submitting">
                                                <b><i class="icon-search4"></i></b> Fetch
                                            </button>
                                            <button type="reset" class="btn btn-warning btn-labeled legitRipple" id="reset-btn">
                                                <b><i class="icon-rotate-ccw3"></i></b> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>

                <div class="row">
                    <div class="col-md-12 d-none" id="utr-response-data">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <tbody></tbody>
                            </table>
                        </div>

                        <form id="rv-txn-form-final" role="rv-txn-us-form-final" action="{{url('admin/dispute-transactions/aeps-fetch/final-submit')}}" data-DataTables="datatable" method="POST">
                            <fieldset class="form-group">
                                @csrf()
                                <div class="col-md-12">
                                    <div class="col-md-12 text-right">
                                        <input type="hidden" name="utr" value="" id="utr_final">
                                        <button type="submit" class="btn btn-danger" data-request="ajax-submit" data-target='[role="rv-txn-us-form-final"]' id="final-submit">
                                            <b><i class="icon-search4"></i></b> Raise Dispute
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>


            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="p-2 h6">Disputed Transactions</div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        <th>Client Ref Id</th>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Amount (&#8377;)</th>
                                        <th>RRN</th>
                                        <th>Credited At</th>
                                        <th>Disputed At</th>
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

    </div>
</div>

@endsection
@section('scripts')

<script src="{{url('public/js/handlebars.js')}}"></script>
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>

<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable">
        <tr>
            <td><b>Merchant Code :</b> @{{merchant_code}}</td>
            <td><b>Route Type :</b> @{{route_type}}</td>
            @{{#if commission_ref_id}}
                <td><b>Commission :</b>₹ @{{commission}}</td>
                <td><b>TDS :</b>₹ @{{tds}}</td>
            @{{/if}}
        </tr>
    </table>
</script>

<script type="text/javascript">
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
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });

        var url = "{{custom_secure_url('admin/dispute-transactions/report/aeps-transactions')}}";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "client_ref_id"
            },
            {
                "data": "name",
            },
            {
                "data": "email",
            },
            {
                "data": "transaction_amount",
                render: function(data, type, full, meta) {
                    if (full.is_commission_credited == '1' && full.commission_ref_id != null) {
                        var total = Number(full.transaction_amount) + Number(full.commission) + Number(full.tds);
                        return '₹'+total;
                    } else {
                        var total = Number(full.transaction_amount);
                        return '₹'+total;
                    }
                }
            },
            {
                "data": "rrn"
            },
            {
                "data": "trn_credited_at"
            },
            {
                "data": "trn_disputed_at"
            }
        ];
        datatableSetup(url, options, onDraw);
    });

    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        getRecords(from, to);
        return false;
    });

    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            buttons: [
                'excel'
            ],
            order: [],
            columnDefs: [{
                "defaultContent": "-",
                'targets': [0],
                /* column index [0,1,2,3]*/
                'orderable': false,
                /* true or false */
            }],
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000, -1],
                [10, 25, 50, 75, 100, 200, 500, 1000, 1500]
            ],
            dom: "Bfrltip",
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
                    d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                    d.user_id = $('#searchForm').find('[name="user_id"]').val();
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

    $('#searching').on('click', function() {
        $('#utr-response-data table tbody').html('');
        $('#utr_final').val('');
        $('#utr-response-data').addClass('d-none');
    });

    $('#reset-btn').on('click', function() {
        $('#utr-response-data table tbody').html('');
        $('#utr_final').val('');
        $('#utr-response-data').addClass('d-none');
    });

    function callbackDispute(response) {

        if (response.code === "0x0200") {
            if (response.data.is_commission_credited == '1' && response.data.commission_ref_id != null) {
                var commi = Number(response.data.commission);
                var tds = Number(response.data.tds);
                var total = Number(response.data.transaction_amount) + Number(response.data.commission) + Number(response.data.tds);
            } else {
                var total = Number(response.data.transaction_amount) ;
                var commi = 0;
                var tds = 0;
            }
            let res = `
                    <tr>
                        <td><b>RRN :</b> ${response.data.rrn}</td>
                        <td><b>User Name :</b> ${response.data.name}</td>
                        <td><b>Email :</b> ${response.data.email}</td>
                        <td><b>Merchant Code :</b> ${response.data.merchant_code}</td>
                    </tr>
                    <tr>
                        <td><b>Client Ref id :</b> ${response.data.client_ref_id}</td>
                        <td><b>Transaction Date :</b> ${response.data.created_at}</td>
                        <td><b>Credited At :</b> ${response.data.trn_credited_at}</td>
                        <td><b>Route Type :</b> ${response.data.route_type}</td>
                    </tr>
                    <tr>
                         <td><b>Amount :</b> ₹ ${response.data.transaction_amount}</td>
                        <td><b>Commission Amount :</b> ₹ ${commi}</td>
                        <td><b>TDS :</b>₹ ${tds}</td>
                        <td><b>Dispute Amount :</b> ₹ ${total} </td>
                    </tr>
                    `;

            $('#utr-response-data').removeClass('d-none');
            $('#utr-response-data table tbody').html(res);
            $('#utr_final').val(response.data.rrn);
        }
    }
</script>

@endsection