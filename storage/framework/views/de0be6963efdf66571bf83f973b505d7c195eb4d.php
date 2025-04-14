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
    b, strong {
    font-weight: 500;
    color: #1c951a;
    font-size: 18px;
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
                                        <option value="processing" <?php if(isset($_GET['status']) && $_GET['status']=='processing' ): ?> selected <?php endif; ?>>Processing</option>
                                        <option value="processed" <?php if(isset($_GET['status']) && $_GET['status']=='processed' ): ?> selected <?php endif; ?>>Processed</option>
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
                                <option value="RTGS">RTGS</option>
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
                <div class="table-responsive" >
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Settlement Ref Id</th>
                                <th>Cr Amount</th>
                                <th>Fee</th>
                                <th>Tax</th>
                                <th>A/C No</th>
                                <th>IFSC</th>
                                <th>Mode</th>
                                <th>Status</th>
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
        <tr> <td><b>Settlement Txn Id</b> </td><td><b>Status</b> </td><td><b>Message</b> </td> </tr>
        {{#each get_settlement_log}}
        <tr> <td>{{settlement_txn_id}} </td> <td>{{status}} </td><td>{{failed_message}} </td> </tr>
        {{/each}}

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
            var url = "<?php echo e(secure_url('payout/fetch')); ?>/settlementorders/0";
            var onDraw = function() {};
            var options = [{
                    "className": 'details-control',
                    "orderable": false,
                    "targets": [],
                    "defaultContent": ''
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
                    "data": "account_number"
                },
                {
                    "data": "account_ifsc"
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
                            return btn;

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
        function settlementLogBind(obj) {
            var html = "";
           // data-target="#kt_modal_settelement_logs" 
            console.log(obj);
            $.each( obj, function( key, value ) {
                html += `<tr><td>${value.settlement_txn_id}</td><td>${value.settlement_txn_id}</td><td>Route name</td><td>${value.status}</td><td>${value.status_code}</td><td>${value.failed_message}</td></tr>`
            });
            $('#dataBind').html(html);
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

    </script>
    <?php $__env->stopSection(); ?>

    <?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/user/autosettlement/list.blade.php ENDPATH**/ ?>