<?php $__env->startSection('title','UPI Dashboard'); ?>
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
<div class="content-w">
    <div class="content-box">
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
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label>Any Key <span class="requiredstar"></span></label>
                                    <input type="text" name="searchText" class="form-control" placeholder="Enter Search Key" />
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>"   <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" <?php if(isset($_GET['to'])): ?> value="<?php echo e($_GET['to']); ?>"   <?php endif; ?> />
                                </div>
                            </div>
                            <input type="hidden" name="queryString" id="queryString" value="<?php echo e(@$_GET['bank_ref']); ?>" />
                            <div class="col-sm-4">
                                <div class="form-group" style="margin-top:26px;">
                                    <button type="submit" class="btn btn-primary" data-loading-text="<b>
                                                <i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i>
                                        </b> Search</button>
                                    <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b>
                                                <i class='fa fa-spin fa-spinner'></i></b> Reset"><b><i class="icon-rotate-ccw3"></i></b> Reset</button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Amount</th>
                                <th>Payer VPA</th>
                                <th>UTR</th>
                                <th>Order Id</th>
                                <th>Fee</th>
                                <th>Tax</th>
                                <th>TXN Status</th>
                                <th>Created</th>
                                <th>updated</th>
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
<?php $__env->stopSection(); ?>
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
            <td><b>Order Id :</b></td><td>{{original_order_id}}</td><td><b>Merchant Txn Ref:</b></td><td>{{merchant_txn_ref_id}}</td><td><b>Bank Txn Id :</b></td><td>{{bank_txn_id}}</td>
        </tr>
		<tr><td><b>Customer Ref Id :</td><td>{{customer_ref_id}}</td><td><b>Payer Acc Name:</td><td>{{payer_acc_name}}</td>
        </tr>
        <tr><td><b>Mobile :</b></td><td> {{payer_mobile}}</td><td><b>Account No :</td><td>{{payer_acc_no}}</td><td><b>IFSC:</td><td>{{payer_ifsc}}</td>
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
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var url = "<?php echo e(custom_secure_url('upi/fetch')); ?>/upicallbacks/0";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "amount"
            },
            {
                "data": "payer_vpa"
            },
            {
                "data": "customer_ref_id"
            },
            {
                "data": "original_order_id"
            },
            {
                "data": "fee"
            },
            {
                "data": "tax"
            },
            {
              "data":"status",
                render:function(data,type,full) {
                if (data === 'pending') {
                        return showSpan('pending');
                    } else if(data === 'rejected') {
                        return showSpan('rejected');
                    } else if (data === 'success') {
                        if (full.is_trn_credited === '1') {
                        return showSpan('settled');
                        } else if (full.is_trn_credited === '0') {
                            return showSpan('unsettled');
                        }
                    } else if (full.is_trn_disputed === '1') {
                        return showSpan('disputed');
                    } 
                    // else if (full.is_trn_credited === '1') {
                    //     return showSpan('settled');
                    // } else if (full.is_trn_credited === '0') {
                    //     return showSpan('unsettled');
                    // }
                    else {
                        return showSpan(data);
                    }
                }
            },
            {
                "data": "new_created_at",
            },
            {
                "data": "new_updated_at",
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

    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            buttons: [
                'excel'
            ],
            order: [],
            columnDefs: [{
                'targets': [0],
                /* column index [0,1,2,3]*/
                'orderable': false,
                /* true or false */
            }],
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000, -1],
                [10, 25, 50, 75, 100, 200, 500, 1000, 1500]
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
<?php echo $__env->make('layouts.user.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/user/upi/upicallback.blade.php ENDPATH**/ ?>