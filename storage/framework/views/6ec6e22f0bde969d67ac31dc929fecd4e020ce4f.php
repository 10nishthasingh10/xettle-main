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
</style>
<!--begin::Table-->
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper">
            <div class="element-box">
                <h5 class="form-header">
                    <?php echo e($page_title); ?>

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
                                    <input type="date" name="from" class="form-control" id="fromDate" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>" <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" id="toDate" <?php if(isset($_GET['to'])): ?> value="<?php echo e($_GET['to']); ?>" <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>User <span class="requiredstar"></span></label>
                                    <select name="userId" id="target" class="form-control js-example-basic-multiple" multiple="multiple">
                                        <option value="">Select User</option>
                                        <?php $__currentLoopData = $user; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $users): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($users->id); ?>"> <?php echo e($users->name); ?> </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <div class="form-group" style="margin-top:25px;">
                                <span class="requiredstar" id="downloadExcelError"></span></label>
                                <a href="#" style="display:none;" class="btn btn-success btn-xs btn-labeled legitRipple" id="downloadExcel">Download Excel</a>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" width="100%" id="datatable">
                        <thead>
                            <tr>
                                <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                <th>Batch id</th>
                                <th>Name</th>
                                <!-- <th>File Name</th> -->
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
                <td><b>Batch Id :</b></td><td>{{batch_id}}</td><td><b>File Name :</b></td><td>{{filename}}</td><td><b>Total Count :</b></td><td>{{total_count}}</td>
            </tr>
            <tr>
                <td><b>Total Amount :</b></td><td>{{total_amount}}</td><td><b>Success Count :</b></td><td>{{success_count}}</td><td><b>Success Amount :</b></td><td>{{success_amount}}</td>
            </tr>
        <tr><td><b>Hold Count :</b></td><td> {{hold_count}}</td><td><b>Hold Amount :</td><td>{{hold_amount}}</td><td><b>Failed Count :</td><td>{{failed_count}}</td>
            </tr>
        <tr><td><b>Failed Amount :</b></td><td> {{failed_amount}}</td><td><b>Cancelled Count :</b></td><td>{{cancelled_count}}</td><td><b>Cancelled Amount :</b></td><td> {{cancelled_amount}}</td></tr><tr><td><b>Pending Count :</b></td><td>{{pending_count}}</td><td><b>Pending Amount :</b></td><td>{{pending_amount}}</td><td><b>Status :</td><td>{{status}}</td><tr><td><b>Created :</td><td>{{new_created_at}}</td>
            </tr>
        </table>
    </script>
<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
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
        var url = "<?php echo e(custom_secure_url('admin/fetch')); ?>/bulkpayouts/0";
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
                "data": "status",
                render: function(data, type, full, meta) {

                    var $name = full.user.name + '<br/>' + full.user.email;
                    return $name;
                }
            },
            // {
            //     "data": "filename"
            // },
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
                    var $viewOrder = '/admin/orders?batchId=' + full.batch_id;
                    var $actionBtn = '<a href="' + $urlExport + '" class="edit btn btn-primary btn-sm" > <i class="os-icon os-icon-download"></i></a><a href="' + $viewOrder + '" title="View Order" target="_blank" class="edit btn btn-info btn-sm" ><i class="os-icon os-icon-eye"></i></a>';
                    /*if(full.status == 'hold'){
                        $actionBtn1 ='<span onclick="bulkPayoutApprove('+full.id+')" title="Approve Bulk Payout" class="edit btn btn-success btn-sm"><i class="os-icon os-icon-check-circle"></i></span><span onclick="cancelBatch('+full.user_id+',\''+full.batch_id+'\')" class="edit btn btn-danger btn-sm" title="Cancel Bulk Payout" data-target="#cancelBatchOrderModal"  data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></span>';
                    }*/
                    return `<span class='inline-flex'>${$actionBtn}</span>`;
                }
            }
        ];
        datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width", $(".table-responsive").width());
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
            scrollX: true,
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
                    d.user_id = $('#searchForm').find('[name="userId"]').val();
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
        console.log(to);
        if (from != '' && to != '') {

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
                    $('#downloadExcel').attr('href', "<?php echo e(custom_secure_url('exportBulkPayoutByDate')); ?>/" + from + "/" + to);
                }
            } else {

                $('#downloadExcel').hide();
                $('#downloadExcelError').text('Please select a duration max 7 days for download');
            }
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin//reports/bulk.blade.php ENDPATH**/ ?>