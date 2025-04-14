<?php $__env->startSection('title','OCR Transactions'); ?>
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
            <div class="element-content">
                <div class="tablo-with-chart">

                </div>
            </div>
            <div class="element-box">
                <h5 class="form-header">
                    <?php echo e($page_title); ?>

                </h5>
                <div class="element-actions">
                <span class="btn btn-success" id="processingOrderUpdate">Processing Order </span>
                </div>

                <form id="searchForm">

                    <fieldset class="form-group">

                        <div class="row">
                        <div class="col-md-2">
                                <div class="form-group">
                                    <label>Any Key <span class="requiredstar"></span></label>
                                    <input type="text" name="searchText" class="form-control" placeholder="Enter Search Key" />
                                    <input type="hidden" name="tr_type" value="dr" />
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
                                    <label for="user_id">Filter by User</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->userName); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Status:</label>
                                    <select class="form-control js-example-basic-multiple" multiple="multiple"   name="status">
                                      <option value="processed">Processed</option>
                                      <option value="processing">Processing</option>
                                      <option value="failed">Failed</option>
                                      
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="row">
                                <input type="hidden" name="queryString" id="queryString" value="<?php echo e(@$_GET['bank_ref']); ?>" />
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
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Client Ref Id/ Order Ref Id </th>
                                        <th>Outlet Id</th>
                                        <th>Bene Id</th>
                                        <th>Mobile</th>
                                        <th>Amount|Fee|Tax</th>
                                        <th>UTR</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!--------------------
                START - Color Scheme Toggler
                -------------------->
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(url('public/js/handlebars.js')); ?>"></script>
<script id="details-template" type="text/x-handlebars-template">


</script>
<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>


<script type="text/javascript">
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
        var url = "<?php echo e(custom_secure_url('admin/fetch')); ?>/dmtTxn/0";
        var onDraw = function() {};
        var options = [{
                "data": "user",
                render: function(data, type, full, meta) {

                    if (data == null) {
                        var $user = '-';
                    } else {
                        var $user = data.name + ' <br/>' + data.email;
                    }
                    return $user;
                }
            },
            {
                "data":"client_ref_id",
                render:function(data,type,full)
                {
                    return '<br/>'+data+'<br/>'+full.order_ref_id;
                }
            },
            {
                "data": "outlet_id",
                render:function(data,type,full)
                {
                    let amt = data;
                    if(full.outlet != null)
                    {
                        amt += '<br/>'+full.name+'<br/>'+full.mobile;
                    }
                    return amt;
                }
                
            },
            {
                "data": "beni_id"
            },
            {
                "data":"mobile"
            },
            {
                "data": "amount",
                render:function(data,type,full)
                {
                    let amt = data;
                    if(full.fee != null)
                    {
                        amt += '|'+parseFloat(full.fee).toFixed(2);
                    }
                    if(full.tax != null)
                    {
                        amt += '|'+parseFloat(full.tax).toFixed(2);
                    }
                    return amt;
                }
            },
            {
                "data": "utr"
            },
            
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    var failedMessage = '';
                    if (data == 'failed' && full.failed_message != null && full.failed_message.length > 0 ) {
                        failedMessage = "<br/>( "+full.failed_message+" )";
                    }
                    return showSpan(data)+failedMessage;
                }
            },
            {
                "data": "new_created_at",
            }
        ];
        datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width",$(".table-responsive").width());
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
            scrollX: true,
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
                    d.user_id = $('#searchForm').find('[name="user_id"]').val();
                    d.apes_status_array= $('#searchForm').find('[name="status"]').val();
                    d.transaction_type_array = $('#searchForm').find('[name="transaction_type"]').val();
                    d.service_type = $('#searchForm').find('[name="service_type"]').val();
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
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });

    $('#processingOrderUpdate').on('click', function() {
            $.ajax({
                url: "<?php echo e(url('admin/processingDMTOrderUpdate')); ?>",
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
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/dmt/dmtTransactionList.blade.php ENDPATH**/ ?>