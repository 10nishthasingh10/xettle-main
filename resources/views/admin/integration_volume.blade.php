@extends('layouts.admin.app')
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

    .content-box {
        padding: 0.5rem !important;
    }

    .element-box {
        padding: 0.5rem !important;
    }

    @media screen and (min-width: 767px) {
        #datatable_length {
            margin-top: 0;
        }
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
                <div class="form-desc">
                    &nbsp;
                </div>
                <form id="searchForm">
                    <fieldset class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="user_id">Filter by User</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        @foreach($userData as $user)
                                        <option value="{{$user->id}}" @if(isset($_GET['user_id']) && $_GET['user_id'] == $user->id) selected  @endif>{{$user->userName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="integration_id">Filter by Integration</label>
                                    <select class="form-control select2" name="integration_id">
                                        <option value="">-- Select Integration --</option>
                                        @foreach($integrations as $integration)
                                        <option value="{{$integration->integration_id}}" @if(isset($_GET['integration_id']) && $_GET['integration_id'] == $integration->integration_id) selected  @endif>{{$integration->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" id="fromDate" @if(isset($_GET['from'])) value="{{$_GET['from']}}"  @endif />
                                </div>
                            </div>
                            <div class="col-md-2">
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
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th>#Id</th>
                                <th>Name</th>
                                <th>Payin</th>
                                <th>Payout</th>
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

<script>
    $(document).ready(function() {
        var url = "{{custom_secure_url('admin/integration/fetchpipetxn')}}";
        var onDraw = function() {};
        var options = [ {
            "orderable": false,
            "searchable": false,
            "defaultContent": '',
            "data": 'count',
            render: function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
            },
            {
                "data": "name",
            },
            {
                "data": function(row) {
                var totalPayin = "success - " + (row.payin.success) + 
                ", pending - " + (row.payin.pending) + 
                ", rejected - " + (row.payin.rejected   );
                return totalPayin;
            }
            },
            {
                "data": function(row) {
                var totalPayout = "hold - " + (row.payout.hold) +
                ", failed - " + (row.payout.failed) +
                ", processing - " + (row.payout.processing) +
                ", cancelled - " + (row.payout.cancelled) +
                ", reversed - " + (row.payout.reversed);
                return totalPayout;
            }
        }
        ];
        datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width",$(".table-responsive").width());
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
            scrollX: true,
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
                    $("")
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.user_id = $('#searchForm').find('[name="user_id"]').val();
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