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
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}"   @endif />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" @if(isset($_GET['to'])) value="{{$_GET['to']}}"   @endif />
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="user">Database</label>
                                    <select class="form-control" name="database">
                                        <option value="mongodb">Mongodb</option>
                                        <option value="mysql">MySql</option>
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

                            </div>
                            <input type="hidden" name="queryString" id="queryString" @if(isset($_GET['bank_ref'])) value="{{$_GET['bank_ref']}}" @endif />
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Webhook Url</th>
                                <th>Attempt</th>
                                <th>Headers</th>
                                <th>Payload</th>
                                <th>Response</th>
                                <th>Error Type</th>
                                <th>Created At</th>
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

<!-- The Modal -->
<div class="modal" id="payloadModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" id="payloadModalData">
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

@endsection
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
            <td><b>UUID :</b> @{{uuid}}</td>
            <td><b>Updated At :</b> @{{updated_at}}</td>
            <td><b>HttpVerb :</b> @{{httpVerb}}</td>
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
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var url = "{{custom_secure_url('admin/webhook-logs')}}";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "webhookUrl",
                render: function(data, type, full, meta) {
                    return data.replace(/\\/g, '').replace(/\"/g, '');
                }
            },
            {
                "data": "attempt"
            },
            {
                "data": "headers",
                render: function(data, type, full, meta) {
                    return `<button type="button" data-payload='${data}' class="btn btn-sm btn-primary open-header-modal">Headers</button>`;
                }
            },
            {
                "data": "payload",
                render: function(data, type, full, meta) {
                    return `<button type="button" data-payload='${data}' class="btn btn-sm btn-primary open-payload-modal">Payload</button>`;
                }
            },
            {
                "data": "response",
                render: function(data, type, full, meta) {
                    if (data == null || data == 'null') {
                        return 'No Response';
                    }
                    return `<button type="button" data-payload='${data}' class="btn btn-sm btn-success open-response-modal">Response</button>`;
                }
            },
            {
                "data": "errorType",
                render: function(data, type, full, meta) {
                    if (data == null || data == 'null') {
                        return 'No Error';
                    }
                    return `<button type="button" data-err='${data}' data-msg='${full.errorMessage}' class="btn btn-sm btn-danger open-error-modal">Error</button>`;
                }
            },
            {
                "data": "created_at"
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
                    d.database = $('#searchForm').find('[name="database"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
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

    $('#datatable').on('click', '.open-payload-modal', function() {
        var data = $(this).attr('data-payload');
        $("#payloadModal").on('show.bs.modal', function() {
            $('#payloadModal .modal-title').html('Payload');
            $('#payloadModalData').html('<pre>' + JSON.stringify(JSON.parse(data), null, 4) + '</pre>');
        });
        $('#payloadModal').modal('show');
        $("#payloadModal").on('hidden.bs.modal', function() {
            $('#payloadModalData').html('');
            $('#payloadModal .modal-title').html('');
        });
    });

    $('#datatable').on('click', '.open-response-modal', function() {
        var data = $(this).attr('data-payload');
        $("#payloadModal").on('show.bs.modal', function() {
            $('#payloadModal .modal-title').html('Response');
            $('#payloadModalData').html('<pre>' + JSON.stringify(JSON.parse(data), null, 4) + '</pre>');
        });
        $('#payloadModal').modal('show');
        $("#payloadModal").on('hidden.bs.modal', function() {
            $('#payloadModalData').html('');
            $('#payloadModal .modal-title').html('');
        });
    });

    $('#datatable').on('click', '.open-header-modal', function() {
        var data = $(this).attr('data-payload');
        $("#payloadModal").on('show.bs.modal', function() {
            $('#payloadModal .modal-title').html('Header');
            $('#payloadModalData').html('<pre>' + JSON.stringify(JSON.parse(data), null, 4) + '</pre>');
        });
        $('#payloadModal').modal('show');
        $("#payloadModal").on('hidden.bs.modal', function() {
            $('#payloadModalData').html('');
            $('#payloadModal .modal-title').html('');
        });
    });

    $('#datatable').on('click', '.open-error-modal', function() {
        var err = $(this).attr('data-err');
        var msg = $(this).attr('data-msg');
        $("#payloadModal").on('show.bs.modal', function() {
            $('#payloadModal .modal-title').html(err);
            $('#payloadModalData').html(msg);
        });
        $('#payloadModal').modal('show');
        $("#payloadModal").on('hidden.bs.modal', function() {
            $('#payloadModalData').html('');
            $('#payloadModal .modal-title').html('');
        });
    });
</script>

@endsection