<?php $__env->startSection('title',ucfirst($page_title)); ?>
<?php $__env->startSection('style'); ?>
<link href="<?php echo e(url('public/css/buttons.dataTables.min.css')); ?>" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
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
                    <?php echo e($page_title); ?> List
                </h5>
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
                                    <input type="date" name="from" class="form-control" id="fromDate" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>"  <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" id="toDate" <?php if(isset($_GET['to'])): ?> value="<?php echo e($_GET['to']); ?>"  <?php endif; ?> />
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
                                <th>Request ID</th>
                                <th>User Name/Email</th>
                                <th>Amount</th>
                                <th>UTR</th>
                                <th>USER ID</th>
                                <th>Status</th>
                                <th>Change By</th>
                                <th>Requested At</th>
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
                    Approve Request
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="orderForm" role="approve-request-form" action="<?php echo e(url('admin/load-money-request/approve')); ?>" data-DataTables="datatable" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""> Remarks </label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter remarks"></textarea>
                        <input class="form-control" type="hidden" name="request_id" id="app_request_id" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="approve-request-form"]' value="Approve Request" />
                </div>
            </form>
        </div>
    </div>
</div>

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="cancelBatchOrderModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Reject Request
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="orderForm" role="cancel-request-form" action="<?php echo e(url('admin/load-money-request/cancelled')); ?>" data-DataTables="datatable" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""> Remarks </label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter remarks"></textarea>
                        <input class="form-control" type="hidden" name="request_id" id="can_request_id" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="cancel-request-form"]' value="Reject Request" />
                </div>
            </form>
        </div>
    </div>
</div>
<?php echo $__env->make(USER.'.payout.modals.batchImport', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make(USER.'.payout.modals.verifyotp', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/handlebars.js')); ?>"></script>
<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable"> 
        <tr>
            <td><b>Remarks:</b> {{remarks}}</td>
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
        var url = "<?php echo e(custom_secure_url('admin/fetch')); ?>/load-money-request/<?php echo e($id); ?>";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "request_id"
            },
            {
                "data": "user_info",
                render: function(data, type, full, meta) {
                     return '';
                }
            },
            {
                "data": "amount"
            },
            {
                "data": "utr"
            },
            {
                "data": "user_id"
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    return statusSpan(data);
                }
            },
            {
                "data": "admin_id",
                render: function(data, type, full, meta) {
                    if (data == null || data == '' || data == undefined) {
                        return '';
                    } else {
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
                    if (full.status == 'pending') {
                        $actionBtn = `<span onclick="approveRequest(${full.id})" title="Approve" class="edit btn btn-success btn-sm" data-target="#approveBatchOrderModal"  data-toggle="modal"><i class="os-icon os-icon-check-circle"></i></span>`;
                        $actionBtn += `<span onclick="rejectRequest(${full.id})" title="Reject" class="edit btn btn-danger btn-sm" data-target="#cancelBatchOrderModal"  data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></span>`;
                    }
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

    function approveRequest(userId) {
        $('#app_request_id').val(userId);
    }

    function rejectRequest(userId) {
        $('#can_request_id').val(userId);
    }
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/load_money.blade.php ENDPATH**/ ?>