@extends('layouts.admin.app')
@section('title','Upi Dashboard')
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
                <form id="searchForm">

                    <fieldset class="form-group">

                        <div class="row">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" value="{{$dateFrom}}" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" value="{{$dateTo}}" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">User <span class="requiredstar"></span></label>
                                    <select class="form-control select2" name="user_id">
                                        <option value=""> Select User </option>
                                        @foreach($userData as $val)
                                        <option value="{{$val->id}}">{{$val->userName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-90px" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
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
            </div>
            <div class="element-content">
                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalUpiqrTxn">
                                            {{$totalAmount['upi_qr']->totalCount}} | ₹{{empty($totalAmount['upi_qr']->totalAmount)?0:$totalAmount['upi_qr']->totalAmount}}
                                        </div>
                                        <div class="label">
                                            Total Amount
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-5" id="totalUpiStackFeeTax">
                                            {{$totalAmount['upi_fee_tax']->totalCount??'0'}} | ₹{{empty($totalAmount['upi_fee_tax']->totalAmount)?0:$totalAmount['upi_fee_tax']->totalAmount??'0'}}
                                        </div>
                                        <div class="label">
                                            Collection Fee & Tax
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-5" id="totalUpiCollectTxn">
                                            {{$totalAmount['upi_creation']->totalCount??'0'}} | ₹{{empty($totalAmount['upi_creation']->totalAmount)?0:$totalAmount['upi_creation']->totalAmount??'0'}}
                                        </div>
                                        <div class="label">
                                            Creation Fee & Tax
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-info" href="#">
                                        <div class="value font-1-5" id="totalUpiVerifyTxn">
                                            {{$totalAmount['upi_verify']->totalCount??'0'}} | ₹{{empty($totalAmount['upi_verify']->totalAmount??'0')?0:$totalAmount['upi_verify']->totalAmoun??'0'}}
                                        </div>
                                        <div class="label">
                                            Verify Fee & Tax
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Amount (&#8377;)</th>
                                        <th>Transactions</th>
                                        <th>TYPE</th>
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
<script src="{{url('public/js/dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
<script>
    $(document).ready(function() {

        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });

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
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var url = "{{custom_secure_url('admin/fetch-reports/upi')}}";
        var onDraw = function() {};
        var options = [{
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": 'count',
                render: function(data, type, full, meta) {
                    let start = parseInt(meta.settings.json.start);
                    return meta.row + (start + 1);
                }
            },
            {
                "data": "name",
                "orderable": false
            },
            {
                "data": "email",
                "orderable": false
            },
            {
                "data": "tot_amount"
            },
            {
                "data": "tot_txn"
            },
            {
                "data": "type",
                render: function(data, type, full, meta) {
                    
                    return 'Upi';
                }
                
            },
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
    $('#formReset').click(function() {
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        $('#datatable').dataTable().api().ajax.reload();
        getRecords('', '');
    });

    function getRecords(from, to) {
        $('#searching').html('Searching');
        $('#searching').attr('disabled', 'disabled');

        $.ajax({
            url: "{{custom_secure_url('admin/reports/upi')}}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                from: from,
                to: to,
                user_id: $('#searchForm').find('[name="user_id"]').val()
            },
            success: function(response) {
                if (response.upi_qr.totalAmount != null)
                    $('#totalUpiqrTxn').html(`${response.upi_qr.totalCount} | ₹${response.upi_qr.totalAmount}`);
                else
                    $('#totalUpiqrTxn').html(`${response.upi_qr.totalCount} | ₹0`);


                if (response.upi_creation.totalAmount != null)
                    $('#totalUpiCollectTxn').html(`${response.upi_creation.totalCount} | ₹${response.upi_creation.totalAmount}`);
                else
                    $('#totalUpiCollectTxn').html(`${response.upi_creation.totalCount} | ₹0`);

                if (response.upi_verify.totalAmount != null)
                    $('#totalUpiVerifyTxn').html(`${response.upi_verify.totalCount} | ₹${response.upi_verify.totalAmount}`);
                else
                    $('#totalUpiVerifyTxn').html(`${response.upi_verify.totalCount} | ₹0`);


                if (response.upi_fee_tax.totalAmount != null)
                    $('#totalUpiStackFeeTax').html(`${response.upi_fee_tax.totalCount} | ₹${response.upi_fee_tax.totalAmount}`);
                else
                    $('#totalUpiStackFeeTax').html(`${response.upi_fee_tax.totalCount} | ₹0`);


                $('#searching').html('Search');
                $('#searching').removeAttr('disabled');
            }
        })
    }
</script>

@endsection