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
            <div class="element-actions">
                    <a class="btn btn-primary btn-sm" href="#" data-target="#approveBatchOrderModal"  data-toggle="modal"><i class="os-icon os-icon-ui-22"></i><span>Add Role</span></a>
            </div>
                <h5 class="form-header">
                    {{$page_title}} List
                </h5>
                <!-- <form id="searchForm">
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
                                    <input type="date" name="from" class="form-control" id="fromDate" @if(isset($_GET['from'])) value="{{$_GET['from']}}" @endif />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" id="toDate" @if(isset($_GET['to'])) value="{{$_GET['to']}}" @endif />
                                </div>
                            </div>
                            <div class="col-md-4">

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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="w-100">&nbsp;</label>
                                    <span class="requiredstar" id="downloadExcelError"></span>
                                    <a href="" style="display:none;" class="btn btn-success btn-xs btn-labeled legitRipple" id="downloadExcel">Download Excel</a>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form> -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                
                                <th>Name</th>
                                
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
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

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="approveBatchOrderModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Add Role
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="orderForm" role="approve-request-form" action="{{url('admin/roles/add')}}" data-DataTables="datatable" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""> Role Name </label>
                        
                        <input class="form-control" type="text" name="role_name" id="role_name" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="approve-request-form"]' value="Submit" />
                </div>
            </form>
        </div>
    </div>
</div>


@include(USER.'.payout.modals.batchImport')
@include(USER.'.payout.modals.verifyotp')
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
            <td><b>Remarks:</b> @{{remarks}}</td>
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
        var url = "{{custom_secure_url('admin/fetch')}}/roles/0";
        var onDraw = function() {};
        var options = [
            {
                "data": "name"
            },
            
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    if(data=='active')
                    {
                        return `<span class="badge badge-success">Active</span>`;
                    }else
                    {
                        return `<span class="badge badge-danger">Inactive</span>`;
                    }
                    
                }
            },
            {
                "data": "new_created_at",
            },
            {
                "data": "action",
                "orderable": false,
                render: function(data, type, full, meta) {
                    let $actionBtn = '';
                    if (full.status == 'inactive') {
                        $actionBtn = `<span onclick="changeStatus(${full.id},'active')" title="Approve" class="edit btn btn-success btn-sm" data-target="#approveBatchOrderModal"  data-toggle="modal"><i class="os-icon os-icon-check-circle"></i></span>`;
                        
                    }
                    $actionBtn += `<a href="roles/userList/${full.id}" class="edit btn btn-primary btn-sm"><i class="os-icon os-icon-eye"></i></a><span onclick="changeStatus(${full.id},'delete')" title="Reject" class="edit btn btn-danger btn-sm" data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></span>`;
                    return `<span class='inline-flex'>${$actionBtn}</span>`;
                }
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
            stateSave: true,
            "searching": true,
            buttons: [
                'excel'
            ],
            "order": [
                [0, "desc"]
            ],
            "aaSorting": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000],
                [10, 25, 50, 75, 100, 200, 500, 1000]
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
    $('#searching').click(function() {
        var from = $('#fromDate').val();
        var to = $('#toDate').val();
        // To set two dates to two variables
        var date1 = new Date(from);
        var date2 = new Date(to);
        // To calculate the time difference of two dates
        var Difference_In_Time = date2.getTime() - date1.getTime();
        // To calculate the no. of days between two dates
        var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);
        //To display the final no. of days (result)
        if (7 > parseInt(Difference_In_Days)) {
            $('#downloadExcel').hide();
            if (from != '' || to != '') {
                $('#downloadExcelError').hide();
                $('#downloadExcel').show();
                $('#downloadExcel').attr('href', "{{custom_secure_url('exportBulkPayoutByDate')}}/" + from + "/" + to);
            }
        } else {
            $('#downloadExcel').hide();
            $('#downloadExcelError').text('Please select a duration max 7 days for download');
        }

    });

    function changeStatus(id,action)
    {
        if(confirm('Do you really want to '+action+' this role?'))
        {
            $.ajax({
                url:"{{custom_secure_url('admin/roles/changeStatus')}}",
                type:'POST',
                data:{id:id,action:action,_token:$('meta[name="csrf-token"]').attr('content')},
                success:function($response)
                {
                    if($response.status)
                    {
                        swal.fire("Great Job", "Status changed successfully", "success");
                        $('#datatable').DataTable().ajax.reload();
                    }
                   
                }
            });
        }
    }


</script>
@endsection
@endsection