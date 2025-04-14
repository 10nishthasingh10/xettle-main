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
            
                <h5 class="form-header">
                    {{$page_title}} List
                </h5>
                <form id="searchForm">
                    <fieldset class="form-group">
                        <div class="row">
                            @csrf
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
                                    <input type="date" id="fromDate" name="from" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}"  @endif />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" id="toDate" name="to" class="form-control"  @if(isset($_GET['to'])) value="{{$_GET['to']}}"  @endif/>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>User <span class="requiredstar"></span></label>
                                    <select name="userId" class="form-control select2">
                                        <option value=""> Select User</option>
                                        @foreach($user as $users)
                                        <option value="{{$users->id}}"> {{$users->name}} ({{$users->email}}) </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Date Type <span class="requiredstar"></span></label>
                                    <select class="form-control" name="date_type">
                                        <option value=""></option>
                                        <option value="created_at">Created At</option>
                                        <option value="document_uploaded_at">Documents Uploaded At</option>
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
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                
                                <th>Agent Name</th>
                                <th>Company Name</th>
                                <th>Merchant Code</th>
                                <th>Status</th>
                                <th>KYC Status</th>
                                <th>KYC Doc</th>
                                <th>Email</th>
                                <th>Document Uploaded At</th>
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
        var url = "{{custom_secure_url('admin/fetch')}}/aeps_agents/0";
        var is_view_action = "{{$is_view_action}}";
        var is_delete_action = "{{$is_delete_action}}";
        var onDraw = function() {};
        var options = [
            {
                "data": "first_name",
                render: function(data, type, full, meta) {
                    var fname = full.first_name;
                    var mname = "";
                    var lname = "";
                    var name = "";
                    var email="";
                    if(full.middle_name != null || full.middle_name != undefined) {
                        mname = full.middle_name;
                    }
                    if(full.last_name != null || full.last_name != undefined) {
                        lname = full.last_name;
                    }
                    if(full.email_id != null || full.email_id != undefined) {
                        email = full.email_id;
                    }
                    return fname+" "+mname+" "+lname+" <br>["+email+"]";
                }
                
            },
            {
                "data":"business_name",
                render: function(data,type,full,meta) {
                    if(full.business_name){
                        return full.business_name.business_name
                    }
                }
            }, 
            
            {
                "data":"merchant_code"
            },
            
            {
                "data": "is_active",
                render: function(data, type, full, meta) {
                    if(data=='1')
                    {
                        return `<span class="badge badge-success">Active</span>`;
                    }else
                    {
                        return `<span class="badge badge-danger">Inactive</span>`;
                    }
                    
                }
            },
            { "data" : "ekyc", render: function(data, type, full, meta) {
                var status = "";
                    if(data != null || data != undefined) {
                        if(data.length > 2) {
                            var ekyc = JSON.parse(data);
                            if(ekyc.paytm != null ||  ekyc.paytm != undefined) {
                                if (ekyc.paytm.is_ekyc != undefined) {
                                    if (ekyc.paytm.is_ekyc == 1) {
                                        status += "paytm : <div class='status-pill green' title='Active'></div> <br>";
                                    } else if (ekyc.paytm.is_ekyc == 0) {
                                        status += "paytm : <div class='status-pill yellow' title='Pending'></div> <br>";
                                    } else if (ekyc.paytm.is_ekyc == 2) {
                                        status += "paytm : <div class='status-pill red' title='Rejected'></div> <br>";
                                    }
                                }
                            }
                            if(ekyc.sbm != null ||  ekyc.sbm != undefined) {
                                if (ekyc.sbm.is_ekyc != undefined) {
                                    if (ekyc.sbm.is_ekyc == 1) {
                                        status += "sbm : <div class='status-pill green' title='Active'></div> <br>";
                                    } else if (ekyc.sbm.is_ekyc == 0) {
                                        status += "sbm : <div class='status-pill yellow' title='Pending'></div> <br>";
                                    } else if (ekyc.sbm.is_ekyc == 2) {
                                        status += "sbm : <div class='status-pill red' title='Rejected'></div> <br>";
                                    }
                                }
                            }
                            if(ekyc.icici != null || ekyc.icici != undefined) {
                                if (ekyc.icici.is_ekyc != undefined) {
                                    if (ekyc.icici.is_ekyc == 1) {
                                        status += "icici : <div class='status-pill green' title='Active'></div> <br>";
                                    } else if (ekyc.icici.is_ekyc == 0) {
                                        status += "icici : <div class='status-pill yellow' title='Pending'></div> <br>";
                                    } else if (ekyc.icici.is_ekyc == 2) {
                                        status += "icici : <div class='status-pill red' title='Rejected'></div> <br>";
                                    }
                                }
                            }

                            if(ekyc.airtel != null || ekyc.airtel != undefined) {
                                if (ekyc.airtel.is_ekyc != undefined) {
                                    if (ekyc.airtel.is_ekyc == 1) {
                                        status += "airtel : <div class='status-pill green' title='Active'></div> <br>";
                                    } else if (ekyc.airtel.is_ekyc == 0) {
                                        status += "airtel : <div class='status-pill yellow' title='Pending'></div> <br>";
                                    } else if (ekyc.airtel.is_ekyc == 2) {
                                        status += "airtel : <div class='status-pill red' title='Rejected'></div> <br>";
                                    }
                                }
                            }
                        }
                        //console.log(ekyc.paytm);
                      
                    }
                    return  status;
                }
            },
              
            {
                "data": "ekyc",
                render: function(data, type, full, meta) {
                  
                        if (data != null || data != undefined) {
                            if (data.length > 2) {
                                var ekycData = JSON.parse(data);
                                if (ekycData.sbm != null ||  ekycData.sbm != undefined) {
                                    if (ekycData.sbm.is_ekyc != undefined) {
                                        if (ekycData.sbm.is_ekyc == 1) {
                                            return `<span class="badge badge-info">Approved</span>`;
                                        } else if(full.documents_status=='rejected') {
                                            return `<span class="badge badge-danger">Rejected (`+full.documents_remarks+`)</span>`;
                                        }
                                        else {
                                            return ``;
                                        }
                                    } else {
                                        return ``;
                                    }
                                } else {
                                    if (full.is_attachment_send == '0' && ekycData.sbm != null)
                                    {
                                        return `<span class="badge badge-success">Send</span>`;
                                    } else
                                    {
                                        return ``;
                                    }
                                }
                            } else {
                                return ``;
                            }
                        } else {
                            return ``;
                        }
                }
            },
            {
                "data":"is_attachment_send",
                render: function(data,type,full,meta) {
                    if(data==0 && full.documents_status=='pending')
                    {
                        return '<span class="badge badge-danger">Not Sent</span>';
                    }
                    else if(data==1)
                    {
                        return `<span class="badge badge-success">Sent</span>`;
                    }
                    else
                    {
                        return ``;
                    }
                }
            },
            {
                "data":"ekyc_documents_uploaded_at",
                render:function(data,type,full,meta) {
                    console.log(data)
                    if(data != null)
                    {
                        return data;
                    }else
                    {
                        return '';
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
                    if(is_delete_action=="1"){
                           if (full.is_active == 0) {
                            $actionBtn = `<span onclick="changeStatus(${full.id},'active')" title="Status Active" class="edit btn btn-success btn-sm" ><i class="os-icon os-icon-check-circle"></i></span>`;
                            
                        }else{
                            $actionBtn = `<span onclick="changeStatus(${full.id},'inactive')" title="Status InActive" class="edit btn btn-danger btn-sm" data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></span>`;
                        }
                    }
                    if(is_view_action=="1")
                    {
                       $actionBtn +=`<span title="View" class="edit btn btn-primary btn-sm" data-toggle="modal"><a target="_blank" href="viewAgents/${full.id}" style="color:white;text-decoration:none"><i class="os-icon os-icon-eye"></i></a></span>`; 
                    }
                        
                    return `<span class='inline-flex'>${$actionBtn}</span>`;
                    
                    // else
                    // {
                    //     return '';
                    // }
                }
            }
        ];
        datatableSetup(url, options, onDraw);
    });
    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var userId = $(this).find('input[name="userId"]').val();
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        var date_type = $(this).find('select[name="date_type"]').val();
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
            // stateSave: true,
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
                    d.user_id = $('#searchForm').find('[name="userId"]').val();
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.date_type = $('#searchForm').find('[name="date_type"]').val();
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
    $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
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
        if(confirm('Do you really want to '+action+' this agent?'))
        {
            $.ajax({
                url:"{{custom_secure_url('admin/aeps/changeAgentStatus')}}",
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