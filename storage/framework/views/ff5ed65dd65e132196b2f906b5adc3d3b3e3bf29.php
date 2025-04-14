<?php $__env->startSection('title','Payout Dashboard'); ?>
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

                <form id="searchForm">

                    <fieldset class="form-group">

                        <div class="row">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" value="<?php echo e($dateFrom); ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" value="<?php echo e($dateTo); ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">User <span class="requiredstar"></span></label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($val->id); ?>"><?php echo e($val->userName); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-90px" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
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
            </div>

            <div class="element-content">
                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalSuccessTxn">
                                            <?php echo e($totalAmount['success']->totalCount); ?> | ₹<?php echo e(empty($totalAmount['success']->totalAmount)?0:$totalAmount['success']->totalAmount); ?>

                                        </div>
                                        <div class="label">
                                            Success
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-danger" href="#">
                                        <div class="value font-1-5" id="totalFailedTxn">
                                            <?php echo e($totalAmount['failed']->totalCount); ?> | ₹<?php echo e(empty($totalAmount['failed']->totalAmount)?0:$totalAmount['failed']->totalAmount); ?>

                                        </div>
                                        <div class="label">
                                            Failed
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-5" id="totalReversedTxn">
                                            <?php echo e($totalAmount['reversed']->totalCount); ?> | ₹<?php echo e(empty($totalAmount['reversed']->totalAmount)?0:$totalAmount['reversed']->totalAmount); ?>

                                        </div>
                                        <div class="label">
                                            Reversed
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="p-2 h6">Success Transactions</div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Amount (&#8377;)</th>
                                        <th>Fee (&#8377;)</th>
                                        <th>Tax (&#8377;)</th>
                                        <th>Transactions</th>
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

    </div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(url('public/js/handlebars.js')); ?>"></script>
<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });


        var url = "<?php echo e(custom_secure_url('admin/fetch-reports/payout')); ?>";
        var onDraw = function() {};
        var options = [{
                //"className": 'details-control',
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": 'count',
                render: function(data, type, full, meta) {
                    let start = parseInt(meta.settings.json.start);
                    return meta.row + (start + 1);
                }
            },
            {
                "data": 'name',
                "orderable": false
            },
            {
                "data": "email",
                "orderable": false
            },
            {
                "data": "tot_amount",
                render: function(data, type, full, meta) {
                    return numberWithCommas(data.toFixed(2));
                }
            },
            {
                "data": "tot_fee",
                render: function(data, type, full, meta) {
                    return numberWithCommas(data.toFixed(2));
                }
            },
            {
                "data": "tot_tax",
                render: function(data, type, full, meta) {
                    return numberWithCommas(data.toFixed(2));
                }
            },
            {
                "data": "tot_txn"
            }
        ];
        datatableSetup(url, options, onDraw);
    });
    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        getRecords(from, to);
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
                "defaultContent": "-",
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
                    d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
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
        return DT;
    }
    $('#formReset').click(function() {
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        $('#datatable').dataTable().api().ajax.reload();
        getRecords('', '');
    });

    function getRecords(from, to) {
        $('#searching').html('Searching');
        $('#searching').attr('disabled', 'disabled');

        $.ajax({
            url: "<?php echo e(custom_secure_url('admin/reports/payout')); ?>",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                from: from,
                to: to,
                user_id: $('#searchForm').find('[name="user_id"]').val()
            },
            success: function(response) {
                if (response.success.totalAmount != null)
                    $('#totalSuccessTxn').html(`${response.success.totalCount} | ₹${response.success.totalAmount}`);
                else
                    $('#totalSuccessTxn').html(`${response.success.totalCount} | ₹0`);

                if (response.failed.totalAmount != null)
                    $('#totalFailedTxn').html(`${response.failed.totalCount} | ₹${response.failed.totalAmount}`);
                else
                    $('#totalFailedTxn').html(`${response.failed.totalCount} | ₹0`);

                if (response.reversed.totalAmount != null)
                    $('#totalReversedTxn').html(`${response.reversed.totalCount} | ₹${response.reversed.totalAmount}`);
                else
                    $('#totalReversedTxn').html(`${response.reversed.totalCount} | ₹0`);

                $('#searching').html('Search');
                $('#searching').removeAttr('disabled');
            }
        })
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/reports/payout_report.blade.php ENDPATH**/ ?>