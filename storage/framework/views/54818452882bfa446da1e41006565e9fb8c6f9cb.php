
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

    @media screen and (min-width: 767px) {
        #datatable_length {
            margin-top: 0;
        }
    }

    .content-box {
        padding: 8px !important;
    }

    .element-box {
        padding: 1.5rem 0.8rem !important;
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
                <form id="searchFormData">
                    <fieldset class="form-group">
                        <div class="row">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="userId" value="<?php echo e(Auth::user()->id); ?>">
                            <div class="col-md-2">
                                    <div class="form-group">
                                <label>Get Report <span class="requiredstar"></span></label>
                                        <select name = "reports" class="form-control js-example-basic-multiple">
                                            <option value="">-- Select Report --</option>
                                            <!-- <option value = "Summary" <?php if(isset($_GET['reports'])
                                                && in_array($_GET['reports'], 'Summary')): ?> selected <?php endif; ?>> Summary </option> -->
                                            <option value = "Smart_Payout" <?php if(isset($_GET['reports'])
                                                && in_array($_GET['reports'], 'Smart_Payout')): ?> selected <?php endif; ?>> Smart Payout </option>
                                                <!-- <option value = "Contacts" <?php if(isset($_GET['reports'])
                                                && in_array($_GET['reports'], 'Contacts')): ?> selected <?php endif; ?>> Contacts </option> -->
                                            <option value = "UPI_Stack" <?php if(isset($_GET['reports'])
                                                && in_array($_GET['reports'], 'UPI_Stack')): ?> selected <?php endif; ?>> UPI Stack </option>
                                                <option value = "Smart_Collect" <?php if(isset($_GET['reports'])
                                                && in_array($_GET['reports'], 'Smart_Collect')): ?> selected <?php endif; ?>> Smart Collect </option>
                                            <option value = "Patner_VAN" <?php if(isset($_GET['reports'])
                                                && in_array($_GET['reports'], 'Patner_VAN')): ?> selected <?php endif; ?>> Patner VAN </option>
                                                <option value = "AEPS" <?php if(isset($_GET['reports'])
                                                && in_array($_GET['reports'], 'AEPS')): ?> selected <?php endif; ?>> AEPS </option>
                                            <option value = "AutoSettlement" <?php if(isset($_GET['reports'])
                                                && in_array($_GET['reports'], 'AutoSettlement')): ?> selected <?php endif; ?>> AutoSettlement </option>
                                            
                                            
                                                
                                        </select>
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

                            <div class="col-md-4">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                          <button type="submit" class="btn btn-primary" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                <b><i class="icon-search4"></i></b> Generate Excel File
                                            </button>
                                            <span id="errorsShow" class="error" style="color: red;"></span>
                                           <!-- <button type="button" class="btn btn-warning btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Reset">
                                                <b><i class="icon-rotate-ccw3"></i></b> Reset
                                            </button> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive custom-table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
 
                                <th>File Name</th>
                                <th>User Name</th>
                                <th>Start date</th>
                                <th>End Date</th>
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
</div>

<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>

<script>
    $(document).ready(function() {

        // let currDate = new Date().toJSON().slice(0,10);
        let currDate = `<?php echo e(date('Y-m-d')); ?>`;
        $('#toDate').attr('max', currDate);
        $('#fromDate').attr('max', currDate);


        var url = "<?php echo e(custom_secure_url('reseller/fetch')); ?>/exceldownload/<?php echo e(Auth::user()->id); ?>";
        var onDraw = function() {};
        var options = [ {
                "data": "file_name"
            },
            {
                "data": "user_id",
                    render: function(data, type, full, meta) {
                        if (full.user == null || full.user == 'undefined') {
                            return 'NA';
                        } else {
                            return full.user.name+'<br>'+full.user.email+'<br>'+full.user.mobile;
                        }
                    }
            },
            {
                "data": "start_date"
            },
            {
                "data": "end_date"
            },
            {
                "data": "new_created_at"
            },
            {
                "data": "file_url",
                "orderable": false,
                render: function(data, type, full, meta) {
                    var file = (data === undefined || data == null || data.length <= 0) ? true : false;
                    if (file == false) {
                        return '<span class="edit btn btn-primary btn-sm"><a href="/reseller/allreports/excelDownloadLink/'+full.id+'"  tooltip="Download"  style="color:white;text-decoration:none" ><i class="os-icon os-icon-download"></i></a></span><span class="edit btn btn-danger btn-sm"><a href="javascript:void(0);" onclick="deleteFile(\'' + full.id + '\')" tooltip="Delete File"  style="color:white;text-decoration:none" data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></a></span>';
                    } else {
                        return '';
                    }
                }
            }
        ];
        datatableSetup(url, options, onDraw);
    });
    /*$('form#searchForm').submit(function() {
        $('#searching').attr('disabled', 'disabled');
        $('#searching').text('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        var reports = $(this).find('input[name="reports"]').val();
        var userId = $(this).find('input[name="userId"]').val();

        setTimeout(function()
        {
            $('#searching').attr('disabled', false);
            $('#searching').text(' Generate Excel File');
            $('#datatable').dataTable().api().ajax.reload();
        },  3 * 60 * 1000);
        return false;
    }); */
</script>
<script type="text/javascript">
    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000],
                [10, 25, 50, 75, 100, 200, 500, 1000]
            ],
            dom: "Bfrltip",
            buttons: [
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
                    d.userIds = $('#searchForm').find('[name="userId"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.reports = $('#searchForm').find('[name="reports"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                },
                beforeSend: function() {},
                complete: function(data) {
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
        $('form#searchFormData')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });
    $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
        });
    var frm = $('#searchFormData');
    frm.submit(function (e) {
        $('#errorsShow').text('');
        $('#searching').attr('disabled', 'disabled');
        $('#searching').text('Loading...');
        e.preventDefault(e);
        var formData = new FormData(this);
            $.ajax(
                {
                    url: "<?php echo e(url('/reseller/allreports/ajaxGenerateExcelFile')); ?>",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function(result) {
                        if (result.status) {
                            interval = setInterval(function()
                                {
                                    getExcelDataCount(result.count, interval);
                                }, 5000);
                        } else {
                            $('#errorsShow').text(result.message);
                            $('#searching').attr('disabled', false);
                            $('#searching').text(' Generate Excel File');
                        }

                }
            });
        });

    function getExcelDataCount(count, interval) {
        $.ajax({
                type: "get",
                url: "<?php echo e(url('reseller/allreports/getCountRecord')); ?>",
                success: function (res) {
                    if (count < res) {
                        $('#searching').attr('disabled', false);
                        $('#searching').text(' Generate Excel File');
                        $('#datatable').dataTable().api().ajax.reload();
                        clearInterval(interval);
                    }
            }
        });
    }
       /* $.ajax({
            type: "post",
            url: "/admin/allreports/excelDownloadLink",
            data: {'url' : url,     "_token": "<?php echo e(csrf_token()); ?>" },
            success: function (res) {
                const data = 'stotage/app/'.url;
                console.log(data);
                const link = document.createElement('a');
                link.setAttribute('href', data);
                link.setAttribute('download', data); // Need to modify filename ...
                link.click();
            }
        }); */

    function deleteFile(id)
    {
        if(confirm('Are you sure, want to delete this file?'))
        {
            $.ajax({
                url:"<?php echo e(custom_secure_url('reseller/removeExportFile/')); ?>"+"/"+id,
                type:"POST",
                data:{id:id,"_token": "<?php echo e(csrf_token()); ?>"},
                success:function(result)
                {
                    console.log(result);
                    if (result.status) {
                        $('#datatable').dataTable().api().ajax.reload();
                    }
                }
            })
        }
    }
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/reseller/excel_download.blade.php ENDPATH**/ ?>