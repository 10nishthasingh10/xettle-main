<?php $__env->startSection('title', $site_title); ?>
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

    .expandtable td b {
        color: #047bf8;
        font-weight: 600;
    }

    .row {
        margin-bottom: 16px;
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
                                    <input type="date" name="from" class="form-control" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>"  <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" <?php if(isset($_GET['to'])): ?> value="<?php echo e($_GET['to']); ?>"  <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="user">Filter by User</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> - <?php echo e($user->email); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group xtl-inline-flex">
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
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Business Name</th>
                                <th>Virtual Address</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Pan No</th>
                                <th>MC Code</th>
                                <th>Created</th>
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

<script src="<?php echo e(url('public/js/handlebars.js')); ?>"></script>
<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable">
        <tr>
            <td><b>Sub Merchant Id :</b> {{sub_merchant_id}}</td>
            <td><b>Merchant Txn :</b> {{merchant_txn_ref_id}}</td>
            <td><b>MCC :</b> {{mcc}}</td>
            <td><b>Request ID :</b> {{request_id}}</td>
        </tr>
        <tr>
            <td><b>Whitelist VPAs :</b> {{allowed_vpa}}</td>
            <td><b>Whitelist Bank :</b> {{allowed_bankAcc}}</td>
            <td><b>IFSC :</b> {{allowed_bankIfsc}}</td>
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
                row.data().allowed_vpa = row.data().allowed_vpa.replaceAll(',', ', ');
                if(row.data().allowed_bank != '' && row.data().allowed_bank != null){
                    var banks = row.data().allowed_bank.split(',');
                    row.data().allowed_bankAcc = banks[0];
                    row.data().allowed_bankIfsc = banks[1];
                }
                row.child(template(row.data())).show();
                tr.addClass('shown');
            }
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var url = "<?php echo e(custom_secure_url('admin/fetch')); ?>/merchants_tpv/0";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "merchant_business_name"
            },
            {
                "data": "merchant_virtual_address"
            },
            {
                "data": "contact_email"
            },
            {
                "data": "mobile"
            },
            {
                "data": "pan_no"
            },
            {
                "data": "mcc"
            },
            {
                "data": "new_created_at",
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
                'excel', 'pdf'
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
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin//reports/virtual_account_clients.blade.php ENDPATH**/ ?>