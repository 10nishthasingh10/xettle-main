<?php $__env->startSection('title',ucfirst($page_title)); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startSection('style'); ?>
<link href="<?php echo e(url('public/css/buttons.dataTables.min.css')); ?>" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>
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
</style>

<!--begin::Table-->
<div class="content-w">
    <div class="content-box ">
        <div class="element-wrapper ">
            <div class="element-box">
                <h5 class="form-header">
                    <?php echo e($page_title); ?>

                </h5>
                <div class="element-actions">
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
                                    <input type="date" name="from" class="form-control" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>"  <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" <?php if(isset($_GET['to'])): ?> value="<?php echo e($_GET['to']); ?>"  <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status <span class="requiredstar"></span></label>
                                    <select name="status" class="form-control">
                                        <option value="">-- Select Payment Status --</option>
                                        <option value="hold" <?php if(isset($_GET['status']) && $_GET['status']=='hold' ): ?> selected <?php endif; ?>>Hold</option>
                                        <option value="queued" <?php if(isset($_GET['status']) && $_GET['status']=='queued' ): ?> selected <?php endif; ?>>Queued</option>
                                        <option value="processing" <?php if(isset($_GET['status']) && $_GET['status']=='processing' ): ?> selected <?php endif; ?>>Processing</option>
                                        <option value="processed" <?php if(isset($_GET['status']) && $_GET['status']=='processed' ): ?> selected <?php endif; ?>>Processed</option>
                                        <option value="cancelled" <?php if(isset($_GET['status']) && $_GET['status']=='cancelled' ): ?> selected <?php endif; ?>>Cancelled</option>
                                        <option value="reversed" <?php if(isset($_GET['status']) && $_GET['status']=='reversed' ): ?> selected <?php endif; ?>>Reversed</option>
                                        <option value="failed" <?php if(isset($_GET['status']) && $_GET['status']=='failed' ): ?> selected <?php endif; ?>>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Mode</label>
                                <select class="form-control"  name="mode">
                                <option value="">Select Mode</option>
                                <option value="IMPS">IMPS</option>
                                <option value="NEFT">NEFT</option>
                                <option value="RTGS">RTGS</option>
                                <option value="UPI">UPI</option>
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
                                    <button type="submit" class="btn btn-primary" data-loading-text="<b>
                                            <i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i>
                                        </b> Search</button>
                                    <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b>
                                            <i class='fa fa-spin fa-spinner'></i></b> Reset"><b><i class="icon-rotate-ccw3"></i></b> Reset</button>
                                </div> -->
                            </div>
                            <input type="hidden" name="queryString" id="queryString" value="<?php echo e($batchId); ?>" />
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive" id="table_responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Order Ref Id</th>
                                <th>Client Ref Id</th>
                                <th>Payout Reference/ Bank Reference</th>
                                <th>Amount</th>
                                <th>Payout Mode</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Updated At</th>
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
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="cancelOrderModal" role="dialog" tabindex="-1">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Cancel Order
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="cancel-order-form" action="<?php echo e(url('payout/order/cancelled')); ?>" data-DataTables="datatable" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="message" />
                        <div class="form-group">
                            <label for=""> Remarks </label>
                            <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter remarks"></textarea>
                            <input class="form-control" type="hidden" name="orderRefId" id="order_ref_id" />
                            <input class="form-control" type="hidden" name="userId" id="user_id" />
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
    <?php echo $__env->make(USER.'.payout.modals.batchImport', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
            <td><b>Contact Id :</b></td><td>{{contact_id}}</td><td><b>First Name :</b></td><td>{{contact.first_name}} </td><td><b>Last Name :</b></td><td>{{contact.last_name}}</td>
            <td><b>Email :</b></td><td>{{contact.email}}</td>
        </tr>
        <tr>
            {{#if contact.vpa_address}}
                <td><b>Vpa Address :</b></td><td>{{contact.vpa_address}}</td>
                {{else}}
                <td><b>Account No :</b></td><td>{{contact.account_number}}</td><td><b>IFSC :</b></td><td>{{contact.account_ifsc}}</td>
            {{/if}}
            <td><b>Order Id :</b></td><td> {{order_id}}</td>
            <td><b>Purpose:</b></td><td> {{purpose}}</td>
        </tr>
		<tr>
        <td><b>Amount :</b></td><td> {{amount}}</td><td><b>Fee:</td><td>{{fee}}</td><td><b>Tax :</b></td><td> {{tax}}</td>   <td><b>Narration :</b></td><td>{{narration}}</td>
        </tr>
        <tr>
        {{#if status_response}}
        <td><b>Message :</b></td><td> {{status_response}}</td>
                {{else}}
                    {{#if cancellation_reason}}
                    <td><b>Cancelled Message :</td><td>{{cancellation_reason}}</td> <td><b>Cancelled Date:</td><td>
                        {{cancelled_at}}</td>
                    {{else}}
                {{/if}}
            {{/if}}
            
            {{#if remark}}
                    <td><b>Remark:</td><td>{{remark}}</td>
            {{/if}}
            {{#if bank_reference}}
                    <td><b>Bank Reference :</td><td>{{bank_reference}}</td>
            {{/if}}
           <td><b>Payout Reference:</td><td>{{bulk_payout_detail.payout_reference}}</td>
        </tr>
        {{#if failed_message}}
        <tr>
        <td><b>Failed Message:</td><td>{{failed_message}}</td>
            {{/if}}
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
            var url = "<?php echo e(secure_url('payout/fetch')); ?>/orders/<?php echo e($id); ?>";
            var onDraw = function() {};
            var options = [{
                    "className": 'details-control',
                    "orderable": false,
                    "targets": [],
                    "defaultContent": ''
                },
                {
                    "data": "order_ref_id",
                },
                {
                    "data":"client_ref_id",
                },
                {
                    "data": "currency",
                    render: function(data, type, full, meta) {
                        if (full.bulk_payout_detail == null || full.bulk_payout_detail == 'undefined') {
                            var payout_reference = "";
                        } else {
                            var payout_reference = full.bulk_payout_detail.payout_reference;
                        }
                        if (full.bank_reference == null || full.bank_reference == '') {
                            var bank_reference = "";
                        } else {
                            var bank_reference = full.bank_reference;
                        }
                        if (payout_reference != "" && bank_reference != "") {
                            return payout_reference+' / '+bank_reference;
                        } else if (payout_reference != ""){
                            return payout_reference;
                        } else if (bank_reference != ""){
                            return bank_reference;
                        } else {
                            return "";
                        }
                    }
                },
                {
                    "data": "amount"
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
                    "data": "updated_at",
                    render: function(data, type, full, meta) {
                        if (full.updated_at != null) {
                            return full.new_updated_at;
                        } else {
                            return '';
                        }
                    }
                },
                {
                    "data": "status",
                    "orderable": false,
                    render: function(data, type, full, meta) {
                        if (data == 'hold') {
                            return '<span class="edit btn btn-danger btn-sm"><a href="javascript:void(0);" onclick="cancelOrder(\'' + full.order_ref_id + '\',\'' + full.user_id + '\')" data-target="#cancelOrderModal" tooltip="Cancel Order"  style="color:white;text-decoration:none" data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></a></span>';
                        } else {
                            return '';
                        }
                    }
                }
            ];

            datatableSetup(url, options, onDraw);
            $('.dataTables_wrapper').css("width",$("#table_responsive").width());
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

        function cancelOrder(order_ref_id, user_id) {
            $('#order_ref_id').val(order_ref_id);
            $('#user_id').val(user_id);
        }
    </script>
    <?php $__env->stopSection(); ?>

    <?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/user/payout/order/list.blade.php ENDPATH**/ ?>