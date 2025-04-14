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
            <div class="row">
                <div class="col-md-8">
                    <h5 class="form-header">
                        <?php echo e($page_title); ?>

                    </h5>
                </div>
                <div class="col-md-4 text-right">
                    <a class="bold-label text-primary" href="#">
                        <div class="value font-1-5" id="totalCountVanApi">
                            <button class="btn btn-success" data-target="#addMessageModal" data-toggle="modal">Add Message</button>
                        </div>
                    </a>
                </div>
            </div>
            <div class="element-box">
               <div class="table-responsive custom-table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
 
                                <th>Subject</th>
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
    <div aria-hidden="false" aria-labelledby="exampleModalLabel" class="modal fade" id="sendMessageModal" role="dialog" >

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Send Email
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="assign-scheme-form" action="<?php echo e(url('admin/messages/sendEmail')); ?>" data-DataTables="datatable" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="message_id" id="message_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">User <span class="requiredstar">*</span></label>
                                    <select name="userId[]" class="form-control js-example-basic-multiple" multiple="multiple">
                                        <option value="">--select user--</option>
                                        <option value = "0"> All User </option>
                                        <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->userName); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                        

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="assign-scheme-form"]' value="Send" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <!-- assign schemes to user -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addMessageModal" role="dialog" tabindex="-1">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Add Email Message
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="add-message-form" action="<?php echo e(url('admin/messages/addMessage')); ?>" data-DataTables="datatable" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Subject <span class="requiredstar">*</span></label>
                                    <input class="form-control" type="text" name="subject">
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Message <span class="requiredstar">*</span></label>
                                    <textarea class="form-control" id="editor" type="text" name="message"></textarea> 
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="add-message-form"]' value="Submit" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div aria-hidden="false" aria-labelledby="exampleModalLabel" class="modal fade" id="viewMessageModal" role="dialog" >

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Send Email
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="assign-scheme-form" action="<?php echo e(url('admin/messages/sendEmail')); ?>" data-DataTables="datatable" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        
                        

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        
                    </div>
                </form>
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
<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
  $(document).ready(function() {
  $('#editor').summernote({
        placeholder: 'Type your message here',
        tabsize: 2,
        height: 300
      });
});
</script>

<script>
    $(document).ready(function() {

        // let currDate = new Date().toJSON().slice(0,10);
        let currDate = `<?php echo e(date('Y-m-d', strtotime('-1 day'))); ?>`;
        $('#toDate').attr('max', currDate);
        $('#fromDate').attr('max', currDate);


        var url = "<?php echo e(custom_secure_url('admin/fetch')); ?>/messages_list/0";
        var onDraw = function() {};
        var options = [ {
                "data": "subject"
            },
            {
                "data": "new_created_at"
            },
            {
                "data": "file_url",
                "orderable": false,
                render: function(data, type, full, meta) {
                    
                        return '<span class="edit btn btn-primary btn-sm"><a href="javascript:void(0)" id="'+full.id+'" class="send-link" data-target="#sendMessageModal" data-toggle="modal"  tooltip="Download"  style="color:white;text-decoration:none" ><i class="os-icon os-icon-email-2-at"></i></a></span><span class="edit btn btn-danger btn-sm"><a href="javascript:void(0);" onclick="deleteMessage(\'' + full.id + '\')" tooltip="Delete Message"  style="color:white;text-decoration:none" data-toggle="modal"><i class="os-icon os-icon-x-circle"></i></a></span><span class="edit btn btn-primary btn-sm"><a href="javascript:void(0)" id="'+full.id+'" class="view-message" data-toggle="modal"  tooltip="Download"  style="color:white;text-decoration:none" ><i class="os-icon os-icon-eye"></i></a></span>';
                    
                }
            }
        ];
        datatableSetup(url, options, onDraw);
    });

    $('#datatable').on('click','.send-link',function(){
        var id = $(this).attr('id');
        $('#message_id').val(id);

    })
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
            $('.js-example-basic-multiple').select2({
                width: '100%',
                dropdownParent: $("#sendMessageModal")
            });
        });
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    var frm = $('#searchFormData');
    frm.submit(function (e) {
        $('#errorsShow').text('');
        $('#searching').attr('disabled', 'disabled');
        $('#searching').text('Loading...');
        e.preventDefault(e);
        var formData = new FormData(this);
            $.ajax(
                {
                    url: "<?php echo e(url('/admin/allreports/ajaxGenerateExcelFile')); ?>",
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
                url: "<?php echo e(url('admin/allreports/getCountRecord')); ?>",
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

    function deleteMessage(id)
    {
        if(confirm('Are you sure, want to delete this message?'))
        {
            $.ajax({
                url:"<?php echo e(custom_secure_url('admin/messages/deleteMessage/')); ?>"+"/"+id,
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

    $('#datatable').on('click','.view-message',function(){
        var id = $(this).attr('id');
        var modal=$('#viewMessageModal');
        $.ajax({
            url:"<?php echo e(custom_secure_url('admin/messages/viewMessage/')); ?>"+"/"+id,
            type:'post',
            data:{id:id,"_token": "<?php echo e(csrf_token()); ?>"},
            success:function(response){
                console.log(response);
                modal.find('.modal-body').html(response.html);
                modal.modal('show');
            }
        })
    })
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin//Messages/message_list.blade.php ENDPATH**/ ?>