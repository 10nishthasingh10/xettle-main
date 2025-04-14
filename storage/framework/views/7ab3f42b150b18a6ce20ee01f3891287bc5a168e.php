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
                <form id="datatableFilterForm">

                    <fieldset class="form-group">

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" />
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" />
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user">Filter by Service</label>
                                    <select class="form-control" name="service_type">
                                        <option value="">-- Select Service --</option>
                                        <?php $__currentLoopData = $serviceList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($idx); ?>"><?php echo e($val); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="user">Database</label>
                                    <select class="form-control" name="database">
                                        <option value="mongodb">Mongodb</option>
                                        <option value="mysql">MySql</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user">Filter by User</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        <?php $__currentLoopData = $userList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> - <?php echo e($user->email); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <!-- <input type="hidden" name="queryString" id="queryString" <?php if(isset($_GET['bank_ref'])): ?> value="<?php echo e($_GET['bank_ref']); ?>" <?php endif; ?> /> -->
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Service</th>
                                <th>Method</th>
                                <th>IP</th>
                                <th>URL</th>
                                <th>Request</th>
                                <th>Response</th>
                                <th>Created At</th>
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

<!-- The Modal -->
<div class="modal" id="payloadModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" id="payloadModalData">
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
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

<script type="text/javascript">
    $(document).ready(function() {
        var url = "<?php echo e(custom_secure_url('admin/user-api-logs')); ?>";
        // var onDraw = function() {};
        var options = [{
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": null,
                render: function(data, type, full, meta) {
                    // console.log(dt.page.info());
                    // return meta.row + 1;
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                "data": "name",
            },
            {
                "data": "email"
            },
            {
                'data': 'service',
                render: function(data, type, full, meta) {
                    return data.toUpperCase();
                }
            },
            {
                "data": 'method',
                render: function(data, type, full, meta) {
                    return data.toUpperCase();
                }
            },
            {
                "data": 'ip',
            },
            {
                "data": 'url',
                render: function(data, type, full, meta) {
                    if (data == null || data == 'null') {
                        return 'No URL';
                    }
                    return `<button type="button" data-payload='${data}' class="btn btn-sm btn-success open-url-modal">URL</button>`;
                }
            },
            {
                "data": "request",
                render: function(data, type, full, meta) {
                    return `<button type="button" data-payload='${data}' class="btn btn-sm btn-primary open-request-modal">Request</button>`;
                }
            },
            {
                "data": "response",
                render: function(data, type, full, meta) {
                    var response = isJsonString(data);
                    if (response == false) {
                        return 'No Response';
                    } else {
                        return `<button type="button" data-payload='${data}' class="btn btn-sm btn-success open-response-modal">Response</button>`;
                    }
                }
            },
            {
                "data": "created_at"
            }
        ];
        datatableSetup(url, options);
    });

    $('form#datatableFilterForm').on('submit', function() {
        $('#datatableFilterForm').find('button:submit').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
        return false;
    });

    function isJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            searching: true,
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
                    d.from = $('#datatableFilterForm').find('[name="from"]').val();
                    d.to = $('#datatableFilterForm').find('[name="to"]').val();
                    d.user_id = $('#datatableFilterForm').find('[name="user_id"]').val();
                    d.service_type = $('#datatableFilterForm').find('[name="service_type"]').val();
                    d.searchText = $('#datatableFilterForm').find('[name="searchText"]').val();
                    d.database = $('#datatableFilterForm').find('[name="database"]').val();
                },
                beforeSend: function() {},
                complete: function() {
                    $('#datatableFilterForm').find('button:submit').button('reset');
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
        // DT.search($('#queryString').val()).draw();
        return DT;
    }
    $('#formReset').click(function() {
        $('form#datatableFilterForm')[0].reset();
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });

    $('#datatable').on('click', '.open-request-modal', function() {
        var data = $(this).attr('data-payload');
        $("#payloadModal").on('show.bs.modal', function() {
            $('#payloadModal .modal-title').html('Request');
            $('#payloadModalData').html('<pre>' + JSON.stringify(JSON.parse(data), null, 4) + '</pre>');
        });
        $('#payloadModal').modal('show');
        $("#payloadModal").on('hidden.bs.modal', function() {
            $('#payloadModalData').html('');
            $('#payloadModal .modal-title').html('');
        });
    });

    $('#datatable').on('click', '.open-response-modal', function() {
        var data = $(this).attr('data-payload');
        $("#payloadModal").on('show.bs.modal', function() {
            $('#payloadModal .modal-title').html('Response');
            $('#payloadModalData').html('<pre>' + JSON.stringify(JSON.parse(data), null, 4) + '</pre>');
        });
        $('#payloadModal').modal('show');
        $("#payloadModal").on('hidden.bs.modal', function() {
            $('#payloadModalData').html('');
            $('#payloadModal .modal-title').html('');
        });
    });

    $('#datatable').on('click', '.open-url-modal', function() {
        var data = $(this).attr('data-payload');
        $("#payloadModal").on('show.bs.modal', function() {
            $('#payloadModal .modal-title').html('URL');
            $('#payloadModalData').html(data);
        });
        $('#payloadModal').modal('show');
        $("#payloadModal").on('hidden.bs.modal', function() {
            $('#payloadModalData').html('');
            $('#payloadModal .modal-title').html('');
        });
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/reports/users_api_logs.blade.php ENDPATH**/ ?>