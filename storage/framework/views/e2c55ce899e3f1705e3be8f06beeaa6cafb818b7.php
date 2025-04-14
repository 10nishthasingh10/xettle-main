<?php $__env->startSection('title',ucfirst($page_title)); ?>

<?php $__env->startSection('style'); ?>
<link href="<?php echo e(url('public/css/buttons.dataTables.min.css')); ?>" rel="stylesheet" type="text/css" />

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

    b,
    strong {
        font-weight: 500;
        color: #1c951a;
        font-size: 18px;
    }

    .content-box {
        padding: 1rem !important;
    }

    .element-box {
        padding: 1rem !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!--begin::Table-->
<div class="content-w">
    <div class="content-box ">
        <div class="element-wrapper ">
            <div class="element-box">
                <h5 class="form-header">
                    <?php echo e($page_title); ?>

                </h5>
                <div class="element-actions" style="margin-top: -2.2rem;">
                    <span class="btn btn-success" id="processingAutoSettlementOrderUpdate">Auto Settlement Processing Clear </span>
                </div>
                <div class="">&nbsp;</div>
                <div class="form-desc">
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
                                    <input type="date" name="from" class="form-control" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>" <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" <?php if(isset($_GET['to'])): ?> value="<?php echo e($_GET['to']); ?>" <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>Status <span class="requiredstar"></span></label>
                                    <select name="status" class="form-control">
                                        <option value="">-- Select Payment Status --</option>
                                        <option value="hold" <?php if(isset($_GET['status']) && $_GET['status']=='hold' ): ?> selected <?php endif; ?>>Hold</option>
                                        <option value="processing" <?php if(isset($_GET['status']) && $_GET['status']=='processing' ): ?> selected <?php endif; ?>>Processing</option>
                                        <option value="processed" <?php if(isset($_GET['status']) && $_GET['status']=='processed' ): ?> selected <?php endif; ?>>Processed</option>
                                        <option value="failed" <?php if(isset($_GET['status']) && $_GET['status']=='failed' ): ?> selected <?php endif; ?>>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Mode</label>
                                    <select class="form-control" name="mode">
                                        <option value="">Select Mode</option>
                                        <option value="IMPS">IMPS</option>
                                        <option value="RTGS">RTGS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">User <span class="requiredstar"></span></label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">Select user</option>
                                        <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($val->id); ?>"><?php echo e($val->name); ?> (<?php echo e($val->email); ?>)</option>
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
                            <input type="hidden" name="queryString" id="queryString" value="" />
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Settlement Ref Id</th>
                                <th>Cr Amount</th>
                                <th>Fee</th>
                                <th>Tax</th>
                                <th>A/C No / IFSC</th>
                                <th>Mode</th>
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
    <?php echo $__env->make(USER.'.payout.modals.settlementlogs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
        <tr> <td><b>Settlement Txn Id</b> </td> <td><b>Route</b> </td><td><b>Status</b> </td><td><b>Status Code</b></td><td><b>Failed Message</b> </td> <td><b>Created At (mm/dd/yyyy)</b> </td>  </tr>
        {{#each get_settlement_log}}
   
        <tr> <td>{{settlement_txn_id}} </td> <td>{{route_name.name}} </td><td>{{status}} </td><td>{{status_code}} </td><td>{{failed_message}} </td> <td>{{formatDate  created_at}} </td> </tr>
        {{/each}}

		</table>
</script>
    <script>
        $(document).ready(function() {

            var template = Handlebars.compile($("#details-template").html());
            var DateFormats = {
                    short: "DD MMMM - YYYY",
                    long: "dddd DD.MM.YYYY HH:mm"
                };

            Handlebars.registerHelper("formatDate", function(datetime, format) {
                var date = new Date(datetime);
                    return date.toLocaleString();
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

        $(document).ready(function() {
            var url = "<?php echo e(secure_url('admin/fetch')); ?>/settlementorders/0";
            var onDraw = function() {};
            var options = [{
                    "className": 'details-control',
                    "orderable": false,
                    "targets": [],
                    "defaultContent": ''
                },
                {
                    "data": "user",
                    render: function(data, type, full, meta) {
                        return data.name + ' <br/>' + data.email;
                    }
                },
                {
                    "data": "settlement_ref_id",
                },

                {
                    "data": "amount"
                },
                {
                    "data": "fee"
                },
                {
                    "data": "tax"
                },
                {
                    "data": "account_number",
                    render: function(data, type, full) {
                        return `${data} <br>${full.account_ifsc}`;
                    }
                },
                {
                    "data": "mode"
                },
                {
                    "data": "status",
                    render: function(data, type, full, meta) {
                        return showSpan(data);
                    }
                },
                {
                    "data": "new_created_at",
                },
                {
                    "data": "status",
                    "orderable": false,
                    render: function(data, type, full, meta) {
                        var btn = '';

                        if (data == 'failed' && "<?php echo e(Auth::user()->hasRole('super-admin')); ?>") {
                            btn = `<a href="#" class="btn btn-sm btn-primary" onclick="settlementLogBind(${full.id}, ${full.user_id}, '${full.settlement_ref_id}')"
                                data-toggle="modal" data-target="#kt_modal_settelement_logs" ><i class="os-icon os-icon-arrow-up-circle"></i> Re Pay</a>`;
                        }
                        if (data == 'processing' && "<?php echo e(Auth::user()->hasRole('super-admin')); ?>") {
                            btn = `<a href="#" class="btn btn-sm btn-primary" onclick="settlementHoldOrder(${full.id},  '${full.settlement_ref_id}')"
                                 ><i class="os-icon os-icon os-icon-trash"></i>Hold</a>`;
                        }
                        return btn;

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
            var user_id = $(this).find('input[name="user_id"]').val();
            $('#datatable').dataTable().api().ajax.reload();
            return false;
        });

        function settlementLogBind(id, userId, txnRefId) {

            $('#id').val(id);
            $('#user_id').val(userId);
            $('#settlement_ref_id').val(txnRefId);
        }

        function settlementHoldOrder(id, txnRefId) {
            $.ajax({
                url: "<?php echo e(url('admin/settlementStatus')); ?>/" + id + '/' + txnRefId,
                type: 'GET',
                success: function(res) {
                    $('#datatable').DataTable().ajax.reload();
                    swal.fire("Great Job", "Settlement status change successfull", "success");
                }
            });
        }
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
                buttons: [
                    'excel', 'pdf'
                ],
                drawCallback: function() {
                    $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
                },
                preDrawCallback: function() {
                    $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
                },
                ajax: {
                    url: urls,
                    headers: {
                        'Access-Control-Allow-Origin': '*'
                    },
                    type: "post",
                    data: function(d) {
                        $("")
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.from = $('#searchForm').find('[name="from"]').val();
                        d.to = $('#searchForm').find('[name="to"]').val();
                        d.searchText = $('#searchForm').find('[name="searchText"]').val();
                        d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                        d.status = $('#searchForm').find('[name="status"]').val();
                        d.mode = $('#searchForm').find('[name="mode"]').val();
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
            DT.search($('#queryString').val()).draw();
            return DT;
        }
        $('#formReset').click(function() {
            $('form#searchForm')[0].reset();
            $('#formReset').button('loading');
            $('#datatable').dataTable().api().ajax.reload();
        });
        $('#processingAutoSettlementOrderUpdate').on('click', function() {
            $.ajax({
                url: "<?php echo e(url('admin/processingAutoSettlementOrderUpdate')); ?>",
                type: 'GET',
                success: function(res) {
                    // console.log(res);
                    alert(res);
                },
                error: function(err) {
                    alert(err.responseJSON.message + ' Please try after 5 minutes.');
                }
            });
        });
    </script>
    <?php $__env->stopSection(); ?>

    <?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/admin/autosettlement/list.blade.php ENDPATH**/ ?>