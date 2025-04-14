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

    .expandtable b,
    #utr-response-data tbody td b {
        color: #047bf8;
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

                <form role="form-ebz-van-with-kyc" action="<?php echo e(url('admin/van/eb/generate-info')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <fieldset class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">User <span class="requiredstar">*</span></label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($row->id); ?>"><?php echo e($row->id); ?> - <?php echo e($row->name); ?> - <?php echo e($row->email); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" data-callbackfn="showVanInfoCb" data-request="ajax-submit" data-target='[role="form-ebz-van-with-kyc"]' class="btn btn-primary w-100px" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                Get VAN Info
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>

                <div class="row">
                    <div class="col-md-12 d-none" id="utr-response-data">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <tbody></tbody>
                            </table>
                        </div>

                        <form role="form-ebz-van-with-kyc-final" action="<?php echo e(url('admin/van/eb/create')); ?>" data-DataTables="datatable" method="POST">
                            <fieldset class="form-group">
                                <?php echo csrf_field(); ?>
                                <div class="col-md-12">
                                    <div class="col-md-12 text-right">
                                        <input type="hidden" name="user_id" value="" id="utr_final">
                                        <button type="submit" class="btn btn-danger" data-request="ajax-submit" data-target='[role="form-ebz-van-with-kyc-final"]' id="final-submit">
                                            <b><i class="icon-search4"></i></b> Generate VAN
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>

            </div>


            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="p-2 h6">User Info</div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        <th>Name / Email</th>
                                        <th>Mobile</th>
                                        <th>Virtual Account</th>
                                        <th>IFSC</th>
                                        <th>VAN Status</th>
                                        <th>KYC Status</th>
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

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="uploadkycdoc" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Upload KYC Docs
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="orderForm" enctype="multipart/form-data" role="upload-kyc-doc-form" action="<?php echo e(url('admin/partners-van/ebuz-list/upload-kyc-doc')); ?>" data-DataTables="datatable" method="POST">
                <?php echo csrf_field(); ?>
                <input class="form-control" type="hidden" name="row_id" id="row_id" />
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""> ID Proof <span class="requiredstar">*</span></label>
                        <input type="file" class="form-control" name="id_proof" placeholder="Upload ID">
                    </div>

                    <div class="form-group">
                        <label for=""> Cancelled Cheque <span class="requiredstar">*</span></label>
                        <input type="file" class="form-control" name="cancelled_cheque" placeholder="Upload cancelled cheque">
                    </div>

                    <div class="form-group">
                        <label for=""> Other Doc (GST/Office Rent Certificate) <span class="requiredstar">*</span></label>
                        <input type="file" class="form-control" name="other_doc_file" placeholder="GST/Office Rent Certificate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="upload-kyc-doc-form"]' data-callbackfn="closeFormCb" value="Upload" />
                </div>
            </form>
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
<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable">
    </table>
</script>

<script type="text/javascript">
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

        $('#datatable').on('click', '.upload-kyc-docs', function() {
            let bizId = $(this).attr('data-rowid');

            $("#uploadkycdoc").modal('show');
            $('#row_id').val(bizId);
            $("#uploadkycdoc").on('hidden.bs.modal', function() {
                $('#row_id').val('');
            });
        });
    });

    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });

        var url = "<?php echo e(custom_secure_url('admin/partners-van/report/ebuz-pvan-list')); ?>";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "name",
                "orderable": false,
                render: function(data, type, full, meta) {
                    return `${full.name} <br>[${full.email}]`;
                }
            },
            {
                "data": "mobile",
            },
            {
                "data": "account_number"
            },
            {
                "data": "ifsc"
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    if (full.kyc_status == '2') {
                        return showSpan("pending");
                    } else if (full.kyc_status == '3') {
                        return showSpan("rejected");
                    } else if (data == '1') {
                        return showSpan("active");
                    } else {
                        return showSpan("inActive");;
                    }
                }
            },
            {
                "data": "kyc_status",
                render: function(data, type, full, meta) {
                    if (data == '1') {
                        return `<span class='inline-flex'>` + showSpan("active") + `</span>`;
                    } else {
                        let act = `<button type="button" class="btn btn-sm btn-warning upload-kyc-docs" data-rowid="${full.id}"><i class="fas fa-file-alt"></i> Upload Docs</button>`;
                        return act;
                    }
                }
            },
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

    function closeFormCb(response) {
        if (response.status === true) {
            $('form#orderForm')[0].reset()
            $('#uploadkycdoc').modal('hide');
        }
    }

    function showVanInfoCb(response) {
        if (response.status === true) {
            let res = `
                    <tr>
                        <td><b>Label :</b> ${response.data.label}</td>
                        <td><b>BANK :</b> ${response.data.authorized_remitters[0].account_number}</td>
                        <td><b>IFSC :</b> ${response.data.authorized_remitters[0].account_ifsc}</td>
                    </tr>
                    <tr>
                        <th colspan="3">Profile</th>
                    </tr>
                    <tr>
                        <td><b>Business Name :</b> ${response.data.profile.business_name}</td>
                        <td><b>Type Code :</b> ${response.data.profile.business_type_code}</td>
                        <td><b>Category Code :</b> ${response.data.profile.category_code}</td>
                    </tr>
                    <tr>
                        <td><b>Name on Bank :</b> ${response.data.profile.name_on_bank}</td>
                        <td><b>GSTIN :</b> ${response.data.profile.gstin}</td>
                        <td><b>PAN :</b> ${response.data.profile.pan_number}</td>
                    </tr>
                    <tr>
                        <th colspan="5">Address</th>
                    </tr>
                    <tr>
                        <td><b>City :</b> ${response.data.profile.city}</td>
                        <td><b>State :</b> ${response.data.profile.state}</td>
                        <td><b>Pincode :</b> ${response.data.profile.pincode}</td>
                    </tr>
                    `;

            $('#utr-response-data').removeClass('d-none');
            $('#utr-response-data table tbody').html(res);
            $('#utr_final').val(response.data.user_id);
        }
    }

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
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/partners_van_ebuzz_list.blade.php ENDPATH**/ ?>