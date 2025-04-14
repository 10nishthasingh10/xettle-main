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
                                    <input type="hidden" name="tr_type" value="dr" />
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
                                <div class="form-group">
                                    <label for="">Filter by Service:</label>
                                    <select name="service_id" class="form-control">
                                        <option value="">-- Service --</option>
                                        @foreach($serviceList as $row)
                                        <option value="{{$row->service_id}}">{{$row->title}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status <span class="requiredstar"></span></label>
                                    <select name="is_active" class="form-control">
                                        <option value="">-- Status --</option>
                                        <option value="1" @if(isset($_GET['is_active']) && $_GET['is_active']=='1' ) selected @endif>Active</option>
                                        <option value="0" @if(isset($_GET['is_active']) && $_GET['is_active']=='0' ) selected @endif>In Active</option>
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
                                    <button type="submit" class="btn btn-primary" id="searching" data-loading-text="<b>
                        <i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i>
                                        </b> Search</button>
                                    <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b>
                        <i class='fa fa-spin fa-spinner'></i></b> Reset"><b><i class="icon-rotate-ccw3"></i></b> Reset</button>
                                </div> -->
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive custom-table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Service Id</th>
                                <th>Account No</th>
                                <th>WEB</th>
                                <th>API</th>
                                <th>Status</th>
                                <th>Created At </th>
                                <th>Action </th>
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
                -------------------->
    </div>
</div>

@section('scripts')

<script src="{{url('public/js/dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>

<script>
    $(document).ready(function() {
        var url = "{{custom_secure_url('admin/fetch')}}/serviceRequest/0";
        var onDraw = function() {};
        var options = [{
                "data": "user_id",
                render: function(data, type, full, meta) {
                    if (full.user != null) {
                        return "<b style='font-weight: 900;'>" + full.user.name + "</b> (" + full.user.mobile + ")<br/>" + full.user.email;
                    } else {
                        return 'NA';
                    }
                }
            },
            {
                "data": "service_id",
                render: function(data, type, full, meta) {
                    if (full.service != null) {
                        return full.service.service_name;
                    } return  "";
                }
            },
            {
                "data": "service_account_number",
                render: function(data, type, full, meta) {
                    return data;
                }
            },
            {
                "data": "is_web_enable  ",
                render: function(data, type, full, meta) {
                    var userId = full.id;
                    if (full.is_web_enable === '1') {
                        var btn = `<label class="switch" onChange="webServiceUpdate('${userId}')"><input type="checkbox" checked><span class="slider round"></span></label>`;
                    } else {
                        var btn = `<label class="switch" onChange="webServiceUpdate('${userId}')"><input type="checkbox"><span class="slider round"></span></label>`;
                    }
                    return btn;
                }
            },
            {
                "data": "is_api_enable",
                render: function(data, type, full, meta) {
                    var userId = full.id;
                    if (full.is_api_enable === '1') {
                        var btn = `<label class="switch" onChange="apiServiceUpdate('${userId}')"><input type="checkbox" checked><span class="slider round"></span></label>`;
                    } else {
                        var btn = `<label class="switch" onChange="apiServiceUpdate('${userId}')"><input type="checkbox"><span class="slider round"></span></label>`;
                    }
                    return btn;
                }
            },
            {
                "data": "is_active",
                render: function(data, type, full, meta) {
                    if (data == '0') {
                        return showSpan('InActive', 'no');
                    } else {
                        return showSpan('Active', 'no');
                    }
                }
            },
            {
                "data": "new_created_at"
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    var $id = full.id;
                    if (full.is_active == 1) {
                        var btn = '  <label class="switch" onChange="statusUpdate(' + $id + ')"><input type="checkbox" checked><span class="slider round"></span></label> ';
                    } else {
                        var btn = '  <label class="switch" onChange="statusUpdate(' + $id + ')"><input type="checkbox" ><span class="slider round"></span></label> ';
                    }
                    var id = 'userprofile/status/' + full.user_id;
                    var viewDetails = '<a href="' + id + '" title="View Profile" target="_blank" class="edit btn btn-info btn-sm"><i class="os-icon os-icon-eye"></i></a>';
                    return btn + viewDetails;
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
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.is_active = $('#searchForm').find('[name="is_active"]').val();
                    d.service_id = $('#searchForm').find('[name="service_id"]').val();
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


    function statusUpdate(id) {
        $.ajax({
            url: "{{custom_secure_url('admin/serviceActivate')}}/" + id,
            type: 'GET',
            success: function(res) {
                $("#datatable").DataTable().ajax.reload();
            }
        });
    }

    function webServiceUpdate(id) {
        $.ajax({
            url: `{{custom_secure_url('admin/web-service-activate')}}/${id}`,
            type: 'GET',
            success: function(res) {
                // $("#datatable").DataTable().ajax.reload();
            }
        });
    }

    function apiServiceUpdate(id) {
        $.ajax({
            url: `{{custom_secure_url('admin/api-service-activate')}}/${id}`,
            type: 'GET',
            success: function(res) {
                // $("#datatable").DataTable().ajax.reload();
            }
        });
    }
</script>
@endsection
@endsection