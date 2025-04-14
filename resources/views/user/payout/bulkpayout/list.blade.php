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
                    @if(CommonHelper::isServiceActive(Auth::user()->id,PAYOUT_SERVICE_ID))

                    @if(CommonHelper::isServiceEnabled(Auth::user()->id,PAYOUT_SERVICE_ID))
                    <a href="#" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#kt_modal_batch_import" id="kt_toolbar_primary_button"><i class="os-icon os-icon-arrow-up-circle"></i> Bulk Import</a>
                    @endif

                    @else
                    <a href="#" class="btn btn-sm btn-primary" data-target="#onboardingFeaturesModal" data-toggle="modal" id="kt_toolbar_primary_button">Add Service</a>
                    @endif
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
                                    <input type="date" name="from" class="form-control" id="fromDate" @if(isset($_GET['from'])) value="{{$_GET['from']}}"   @endif />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" id="toDate" @if(isset($_GET['to'])) value="{{$_GET['to']}}"   @endif />
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="w-100">&nbsp;</label>
                                    <span class="requiredstar" id="downloadExcelError"></span>
                                    <a href="" style="display:none;" class="btn btn-success btn-xs btn-labeled legitRipple" id="downloadExcel">Download Excel</a>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                <th>Batch id</th>
                                <th>File Name</th>
                                <th>Total Count</th>
                                <th>Total Amount</th>
                                <th>File Status</th>
                                <th>Created</th>
                                <th>Action</th>
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
<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="cancelBatchOrderModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Cancel Batch
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="orderForm" role="cancel-order-form" action="{{url('payout/batch/cancelled')}}" data-DataTables="datatable" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="message" />
                    <div class="form-group">
                        <label for=""> Remarks </label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter remarks"></textarea>
                        <input class="form-control" type="hidden" name="userId" id="userId" />
                        <input class="form-control" type="hidden" name="batchId" id="batchId" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="cancel-order-form"]' value="Cancel Order" />
                </div>
            </form>
        </div>
    </div>
</div>
@include(USER.'.payout.modals.batchImport')
@include(USER.'.payout.modals.verifyotp')
@section('scripts')
<script>
    function bulkPayoutDownload(id) {
        $.ajax({
            url: "{{custom_secure_url('payout/bulkExport/')}}/" + id,
            type: "get",
            success: function(data) {}
        });
    }
</script>
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
               <td><b>Success Count :</b></td><td>@{{success_count}}</td><td><b>Success Amount :</b></td><td>@{{success_amount}}</td><td><b>Hold Count :</b></td><td> @{{hold_count}}</td>
            </tr>
        <tr><td><b>Hold Amount :</td><td>@{{hold_amount}}</td><td><b>Failed Count :</td><td>@{{failed_count}}</td><td><b>Failed Amount :</b></td><td> @{{failed_amount}}</td>
            </tr>
        <tr><td><b>Cancelled Count :</b></td><td>@{{cancelled_count}}</td><td><b>Cancelled Amount :</b></td><td> @{{cancelled_amount}}</td><td><b>Pending Count :</b></td><td>@{{pending_count}}</td></tr><tr><td><b>Pending Amount :</b></td><td>@{{pending_amount}}</td>
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
        var url = "{{custom_secure_url('payout/fetch')}}/bulkpayouts/{{$id}}";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "batch_id"
            },
            {
                "data": "filename"
            },
            {
                "data": "total_count"
            },
            {
                "data": "total_amount"
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {

                    var $actionBtn = showSpan(data);
                    return $actionBtn;
                }
            },
            {
                "data": "new_created_at",
            },
            {
                "data": "new_created_at",
                "orderable": false,
                render: function(data, type, full, meta) {
                    var $actionBtn1 = '';
                    var $urlExport = 'bulkExport/' + full.id;
                    var $viewOrder = '/payout/orders?batchId=' + full.batch_id;
                    var $actionBtn = '<a href="' + $urlExport + '" class="edit btn btn-primary btn-sm" > <i class="os-icon os-icon-download"></i></a><a href="' + $viewOrder + '" title="View Order" target="_blank" class="edit btn btn-info btn-sm" ><i class="os-icon os-icon-eye"></i></a>';
                    if (full.status == 'hold') {
                        $actionBtn1 = '<span onclick="bulkPayoutApprove(' + full.id + ')" title="Approve Bulk Payout" class="edit btn btn-success btn-sm"><i class="os-icon os-icon-check-circle"></i></span><span onclick="cancelBatch(' + full.user_id + ',\'' + full.batch_id + '\')" class="edit btn btn-danger btn-sm" title="Cancel Bulk Payout" data-target="#cancelBatchOrderModal"  data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></span>';
                    }
                    return `<span class='inline-flex'>${$actionBtn} ${$actionBtn1}</span>`;
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
                'excel', 'pdf'
            ],
            "order": [
                [6, "desc"]
            ],
            "aaSorting": [
                [6, "desc"]
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

    function bulkPayoutApprove(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be batch approve",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: "{{custom_secure_url('payout/bulkPayout/approveOtp/')}}/" + id,
                    method: 'GET',
                    success: function(response) {
                        if (response.status == true) {
                            $(".deliveryreponse").show();
                            $("#verifyOtpModal").modal('show');
                            $("#encrypt_user_id").val(response.userId);
                            $("#bulkbatchId").val(response.batchId);
                            $(".deliveryreponseOtp").html(response.message);
                        }
                        setTimeout(function() {
                            $(".deliveryreponse").hide();
                        }, 1000 * 10);
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {}
                });

                setTimeout(function() {
                    $("#resendOtpOrder").css('display', 'block');
                }, 30000);
            }
        });
    }

    $(document).on('click', '#resendOtpOrder', function() {
        var userId = $('#encrypt_user_id').val();
        $.ajax({
            url: "{{custom_secure_url('payout/resendOtpOrderApprove/')}}/" + userId,
            method: 'POST',
            async: false,
            data: {
                '_token': $token
            },
            success: function(response) {
                if (response.trim() == "true") {
                    $(".deliveryreponse").show();
                    $(".deliveryreponse").html("OTP Re-sended Successfully");
                }
                setTimeout(function() {
                    $(".deliveryreponse").hide();
                }, 1000 * 10);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {}
        });
    });

    function cancelBatch(userId, batchId) {
        $('#userId').val(userId);
        $('#batchId').val(batchId);
    }


    function checkfile(sender) {
        var validExts = new Array(".csv");
        var fileExt = sender.value;
        fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
        if (validExts.indexOf(fileExt) < 0) {
            document.getElementById("batchImportFile").value = null;
            alert("Invalid file selected, Please upload only csv file.");
            return false;
        }
        else return true;
    }
</script>
@endsection
@endsection