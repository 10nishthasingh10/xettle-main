
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
        padding: 0.5rem !important;
    }

    .element-box {
        padding: 0.5rem !important;
    }

    @media screen and (min-width: 767px) {
        #datatable_length {
            margin-top: 0;
        }
    }
</style>
<!--begin::Table-->
<div class="content-w">
    <div class="content-box">
        <div class="element-wrapper">

            <div class="row">
                <div class="col-md-8">
                    <h5 class="form-header">
                        <?php echo e($page_title); ?>

                    </h5>
                </div>

                <div class="col-md-4 text-right">
                    <div class="bold-label text-primary">
                        <div class="value font-1-5">
                            <button class="btn btn-success" data-target="#addNewChargeModal" data-toggle="modal">Add New Charge</button>
                        </div>
                    </div>
                </div>
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
                                    <input type="hidden" name="tr_type" value="dr" />
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
                                <div class="form-group">
                                    <label for="user_id">Filter by User:</label>
                                    <select name="user_id" class="form-control">
                                    <option value="">-- Select User --</option>
                                        <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($user->id); ?>"><?php echo e($user->userName); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status <span class="requiredstar"></span></label>
                                    <select name="status" class="form-control">
                                        <option value="">-- Status --</option>
                                        <option value="1" <?php if(isset($_GET['status']) && $_GET['status']=='1' ): ?> selected <?php endif; ?>>Active</option>
                                        <option value="0" <?php if(isset($_GET['status']) && $_GET['status']=='0' ): ?> selected <?php endif; ?>>In Active</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="row">
                                <input type="hidden" name="queryString" id="queryString" value="<?php echo e(@$_GET['txn_id']); ?>" />
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
                <div class="table-responsive custom-table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th>USER Id</th>
                                <th>Customer Ref Id</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            <!-- </div> -->
        </div>
    </div>
</div>

 <!-- add new Charge -->
<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addNewChargeModal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">
            Charge Back
        </h5>
        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
    </div>
    
    <form role="add-new-charge-form" action="<?php echo e(url('admin/recharge-back/charge/add')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="row">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group">
                            <label for="">Any Key  <span class="requiredstar">*</span></label>
                            <input type="text" id="searchText1" name="searchTexts" class="form-control" placeholder="Enter Search Key" />
                            <input type="hidden" name="tr_type" value="dr" />
                            <input type="hidden" name="user_id" value="<?php echo e($user->id); ?>" />
                            <div class="message"></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="w-100">&nbsp;</label>
                            <button type="submit" class="btn btn-primary"  id="searchCharge" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                <b><i class="icon-search4"></i></b> Search
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row input_section" style="display:none">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Customer RefId <span class="requiredstar">*</span></label>
                            <input type="text"  readonly="readonly" class="form-control addChargeInput customer_refid" value="<?php echo e($user->customer_ref_id); ?>" name="txn_id" placeholder="Enter txn id" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Amount <span class="requiredstar">*</span></label>
                            <input type="text"  readonly="readonly" class="form-control addChargeInput amount" name="amount" value="<?php echo e($user->amount); ?>" placeholder="Enter Amount" required>
                        </div>
                    </div>
                </div>
                <div class="row input_section" style="display:none">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Bank Txn ID <span class="requiredstar">*</span></label>
                            <input type="text"  readonly="readonly" class="form-control addChargeInput bank_txnid" value="<?php echo e($user->bank_txn_id); ?>" name="bank_txn_id" placeholder="Enter txn id" required>
                            <!-- <input type="hidden" name="user_id" value="<?php echo e($user->id); ?>" /> -->
                        </div>
                    </div>
                </div>
           </div>
        </div>

        <div class="modal-footer footer_section" style="display:none">           
            <input type="submit" name="submit"  value="Submit" class="btn btn-primary submit" id="submit" data-request="ajax-submit" data-callbackfn="addNewChargeCb" data-target='[role="add-new-charge-form"]' />
        </div>

    </form>
    </div>
    </div>
</div>


<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(url('public/js/dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>

<script>
    $(document).ready(function() {
        var url = "<?php echo e(custom_secure_url('admin/fetch')); ?>/recharge-back/0";
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
                "data": "txn_id",
            },
            
            { 
                data:"status",
                render:function(data,type,full)
                {   
                    if(data == '1')
                    {
                        return showSpan('active');
                    }else
                    {
                        return showSpan('inactive');
                    }
                }
            },
            {
                "data": "new_created_at"
            },

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
        $(this).find('select[name="user_id"]').val(null);
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });




    $(document).ready(function() {

        $(document).on('click', '#searchCharge', function(e){
            e.preventDefault();
            var searchTexts = $("#searchText1").val();

            $.ajax({
                url: "<?php echo e(url('admin/recharge-back/charge/add')); ?>",
                type: 'POST',
                dataType: "json",
                data: {
                    searchTexts: searchTexts,                
                    _token: '<?php echo e(csrf_token()); ?>'
                },
                success: function(response) {              
                    
                    if(response.status == "success"){
                        $(".customer_refid").val(response.customer_ref_id);
                        $(".amount").val(response.amount);
                        $(".bank_txnid").val(response.bank_txn_id);
                        $(".message").html('');
                        $(".input_section").show();
                        $(".footer_section").show();

                    }else{                    
                        $(".message").html('<span style="color: red;">Data Not found</span>');
                        $(".customer_refid").val();
                        $(".amount").val();
                        $(".input_section").hide();
                        $(".footer_section").hide();
                        // $('#addNewChargeModal').modal('hide');
                    }                
                }
            });
            return false;
        });
    });

    $('#submit').click(function() {
        $('#addNewChargeModal').modal('hide');
        // $('#searchText1').val();
        // $('form[role="add-new-charge-form"]').trigger('reset');
        $(".input_section").hide();
        $(".footer_section").hide();
    });



</script>

<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/reports/recharge_back.blade.php ENDPATH**/ ?>