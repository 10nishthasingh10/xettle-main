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

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" value="<?php echo e($dateFrom); ?>" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" value="<?php echo e($dateTo); ?>" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">User <span class="requiredstar"></span></label>
                                    <select class="form-control select2" name="user_id">
                                        <option value=""> Select User</option>
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
                                <div class="col-sm-5">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label" href="#">
                                        <div class="value font-1-5" id="totalCount">
                                            <?php echo e($totalAmount[0]->totalCount + $totalAmount[1]->totalCount + $totalAmount[2]->totalCount); ?>

                                        </div>
                                        <div class="label">
                                            Total Counts
                                        </div>

                                    </a>
                                </div>

                                <div class="col-sm-7">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label" href="#">
                                        <div class="value font-1-5" id="totalAmount">
                                            <?php
                                            $totAmount = 0;
                                            if(!empty($totalAmount[0]->totalAmount)){
                                            $totAmount += $totalAmount[0]->totalAmount;
                                            }
                                            if(!empty($totalAmount[1]->totalAmount)){
                                            $totAmount += $totalAmount[1]->totalAmount;
                                            }
                                            if(!empty($totalAmount[2]->totalAmount)){
                                            $totAmount += $totalAmount[2]->totalAmount;
                                            }
                                            ?>
                                            ₹<?php echo e(number_format($totAmount,2)); ?>

                                        </div>
                                        <div class="label">
                                            Total Amount
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="element-content">
                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalCountVan">
                                            <?php echo e($totalAmount[0]->totalCount); ?> | ₹<?php echo e(empty($totalAmount[0]->totalAmount)?0:number_format($totalAmount[0]->totalAmount,2)); ?>

                                        </div>
                                        <div class="label">
                                            Partner's VAN
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-5" id="totalCountUpiApi">
                                            <?php echo e($totalAmount[1]->totalCount); ?> | ₹<?php echo e(empty($totalAmount[1]->totalAmount)?0:number_format($totalAmount[1]->totalAmount,2)); ?>

                                        </div>
                                        <div class="label">
                                            Smart Collect UPI
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-5" id="totalCountVanApi">
                                            <?php echo e($totalAmount[2]->totalCount); ?> | ₹<?php echo e(empty($totalAmount[2]->totalAmount)?0:number_format($totalAmount[2]->totalAmount,2)); ?>

                                        </div>
                                        <div class="label">
                                            Smart Collect VAN
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

                        <div class="col-md-12">
                            <h6>Partner's VAN Users</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover" id="datatable">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>User Name</th>
                                            <th>Email</th>
                                            <th>Amount (&#8377;)</th>
                                            <th>Transactions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <hr class="hr">
                            <h6>Smart Collect Users</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover" id="van_api_table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>User Name</th>
                                            <th>Email</th>
                                            <th>Amount (&#8377;)</th>
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
<script>
    $(document).ready(function() {

        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });


        $('#van_api_table tbody').on('click', 'td.details-control', function() {
            var tr = $(this).closest('tr');
            var table = $("#van_api_table").DataTable();
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
        var url = "<?php echo e(custom_secure_url('admin/fetch-reports/van')); ?>";
        var url2 = "<?php echo e(custom_secure_url('admin/fetch-reports/van-api')); ?>";
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
                "data": "name",
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
                "data": "tot_txn"
            }
        ];
        datatableSetup(url, options, onDraw);
        datatableSetup(url2, options, onDraw, "#van_api_table");
    });

    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        $('#van_api_table').dataTable().api().ajax.reload();
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
        $('#van_api_table').dataTable().api().ajax.reload();
        getRecords('', '');
    });

    function getRecords(from, to) {
        $('#searching').html('Searching');
        $('#searching').attr('disabled', 'disabled');

        $.ajax({
            url: "<?php echo e(custom_secure_url('admin/reports/van')); ?>",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                from: from,
                to: to,
                user_id: $('#searchForm').find('[name="user_id"]').val()
            },
            success: function(response) {
                $('#totalCount').html(response[0].totalCount + response[1].totalCount + response[2].totalCount);
                var totalAmount = 0;
                //Partner's VAN
                if (response[0].totalAmount != null) {
                    totalAmount += eval(response[0].totalAmount);
                    $('#totalCountVan').html(`${response[0].totalCount} | ₹${response[0].totalAmount.toFixed(2)}`);
                } else {
                    $('#totalCountVan').html(`${response[0].totalCount} | ₹0`);
                }

                //Smart Collect UPI
                if (response[1].totalAmount != null) {
                    totalAmount += eval(response[1].totalAmount);
                    $('#totalCountUpiApi').html(`${response[1].totalCount} | ₹${response[1].totalAmount.toFixed(2)}`);
                } else {
                    $('#totalCountUpiApi').html(`${response[1].totalCount} | ₹0`);
                }


                //smart collect VAN
                if (response[2].totalAmount != null) {
                    totalAmount += eval(response[2].totalAmount);
                    $('#totalCountVanApi').html(`${response[2].totalCount} | ₹${response[2].totalAmount.toFixed(2)}`);
                } else {
                    $('#totalCountVanApi').html(`${response[2].totalCount} | ₹0`);
                }

                $('#totalAmount').html('₹' + totalAmount.toFixed(2));

                $('#searching').html('Search');
                $('#searching').removeAttr('disabled');
            }
        })
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/reports/vanTransactionReport.blade.php ENDPATH**/ ?>