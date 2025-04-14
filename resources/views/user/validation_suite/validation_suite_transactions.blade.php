@extends('layouts.user.app')
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

    .expandtable td b {
        color: #047bf8;
        font-weight: 600;
    }

    .row {
        margin-bottom: 16px;
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Service Type</label>
                                    <select name="service_type" class="form-control">
                                        <option value="">--Select--</option>
                                        <option value="bank">BANK</option>
                                        <option value="vpa">VPA</option>
                                        <option value="ifsc">IFSC</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}" @endif />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" @if(isset($_GET['to'])) value="{{$_GET['to']}}" @endif />
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group xtl-inline-flex">
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
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th>S.N.</th>
                                <th>Request ID</th>
                                <th>Service Type</th>
                                <th>Status</th>
                                <th>Req. Data</th>
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
@endsection

@section('scripts')

<script src="{{url('public/js/handlebars.js')}}"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var url = "{{custom_secure_url('validation/fetch')}}/validation_suite_txns/0";
        var onDraw = function() {};
        var options = [{
                // "className": 'details-control',
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": null,
                render: function(data, type, full, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                "data": "request_id"
            },
            {
                "data": "validation_type",
                render: function(data, type, full, meta) {
                    return data.toUpperCase();
                }
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    if (data == 'found' || data == 'valid')
                        return `<span class="badge badge-success">${data.toUpperCase()}</span>`;
                    else if (data == 'not found' || data == 'invalid')
                        return `<span class="badge badge-danger">${data.toUpperCase()}</span>`;
                    else
                        return data;
                }
            },
            {
                "data": 'validation_type',
                render: function(data, type, full, meta) {
                    if (full.validation_type == 'vpa')
                        return full.vpa;
                    else if (full.validation_type == 'bank')
                        return `${full.bank_number} <br> ${full.ifsc}`;
                    else if (full.validation_type == 'ifsc')
                        return full.ifsc;
                    else
                        return '';
                }
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

    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
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
                    d.service_type = $('#searchForm').find('[name="service_type"]').val();
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
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });
</script>

@endsection