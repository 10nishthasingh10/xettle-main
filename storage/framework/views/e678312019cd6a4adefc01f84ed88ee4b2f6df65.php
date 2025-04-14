<?php $__env->startSection('title', $page_title); ?>
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
        margin-bottom: 0.5rem;
    }

    .content-box {
        padding: 0.5rem !important;
    }

    .element-box {
        padding: 0.5rem !important;
    }

    b,
    strong {
        font-weight: bolder !important;
    }
</style>
<link href="<?php echo e(url('public/css/jquerysctipttop.css')); ?>" rel="stylesheet" type="text/css">
<link href="<?php echo e(url('public/css/style.css')); ?>" rel="stylesheet" type="text/css">
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
                                    <label for="">User:</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value=""> Select User</option>
                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($row->id); ?>"><?php echo e($row->name); ?> (<?php echo e($row->email); ?>)</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">KYC Status:</label>
                                    <select class="form-control" name="status">
                                        <option value=""> Select Status</option>
                                        <option value="0">Pending</option>
                                        <option value="1">Verified</option>
                                        <option value="2">Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
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

                        </div>
                    </fieldset>
                </form>

                <div class="table-responsive" style=" overflow-x: auto;">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th></th>
                                <!-- <th>Name</th> -->
                                <th>Biz. name</th>
                                <th>Owner Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Status Updated At</th>
                                <th>Created</th>
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

<div aria-hidden="true" class="onboarding-modal modal fade animated" id="onboardingWideSlideModal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg modal-centered" role="document">
        <div class="modal-content text-center">

            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                <span class="close-label"></span>
                <span class="os-icon os-icon-close"></span>
            </button>


            <div class="onboarding-slide">
                <div class="onboarding-side-by-side">
                    <div class="onboarding-content with-gradient kkyc-content-modal-div">
                        <h4 class="onboarding-title">Video KYC Approvel</h4>
                        <div class="onboarding-text"></div>

                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <video id="kycVid" controls playsinline></video>
                            </div>

                            <div class="col-md-12 mb-2">
                                <span class="text-danger" id="kyc-text"></span>
                            </div>

                            <div class="col-md-12" id="controls">
                                <input type="hidden" id="kycid" value="">
                                <button class="btn btn-success" id="btnApp">Approve</button>
                                &nbsp;
                                <button class="btn btn-danger" id="btnRej">Reject</button>
                                <div id="rcTimer" class="text-success font-weight-bold"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="rejectKycModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Reject Video KYC
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="rejectKycForm" role="reject-kyc-form" action="<?php echo e(url('admin/users/video-kyc')); ?>" data-DataTables="datatable" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""> Remarks </label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter remarks"></textarea>
                        <input class="form-control" type="hidden" name="vid" id="vidModal" />
                        <input type="hidden" name="value" value="rej">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-callbackfn="resetRejectForm" data-target='[role="reject-kyc-form"]' value="Reject" />
                </div>
            </form>
        </div>
    </div>
</div>

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="approveKycModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Approve Video KYC
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="approveKycForm" role="approve-kyc-form" action="<?php echo e(url('admin/users/video-kyc')); ?>" data-DataTables="datatable" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""> Remarks </label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter remarks"></textarea>
                        <input class="form-control" type="hidden" name="vid" id="approveVidModal" />
                        <input type="hidden" name="value" value="app">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-callbackfn="resetApproveForm" data-target='[role="approve-kyc-form"]' value="Approve" />
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(url('public/js/handlebars.js')); ?>"></script>
<script src="<?php echo e(url('public/js/dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>

<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable">
        <tr>
            <td><b>Business Type :</b> {{business_name.business_type}}</td>
            <td><b>Business PAN :</b> {{business_name.business_pan}}</td>
        </tr>
        <tr>
            <td><b>Owner PAN :</b> {{business_name.pan_number}}</td>
            <td><b>Owner AADHAAR :</b> {{business_name.aadhar_number}}</td>
        </tr>
        <tr>
            <td colspan="2"><b>Remarks :</b> {{remarks}}</td>
        </tr>
    </table>
</script>

<script>
    var selectFilterService;
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
        $(".js-example-basic-multiple").select2();
        var url = "<?php echo e(custom_secure_url('admin/fetch')); ?>/user-video-kyc/0";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "business_name",
                render: function(data, type, full, meta) {
                    return `${full?.business_name?.business_name_from_pan}`;
                }
            },
            // {
            //     "data": "business_name.business_name",
            // },
            {
                "data": "business_name",
                render: function(data, type, full, meta) {
                    return `${data?.aadhaar_name} <br>${full?.business_name?.pan_owner_name}`;
                }
            },
            {
                "data": "latitude",
                render: function(data, type, full, meta) {
                    return `<b>Lat:</b> ${data || '-'} <br><b>Lon:</b> ${full.longitude || '-'}`;
                }
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    if (data == '0') {
                        var $actionBtn = showSpan("Pending");
                    } else if (data == '1') {
                        var $actionBtn = showSpan("Verified");
                    } else if (data == '2') {
                        var $actionBtn = showSpan("Rejected");
                    }
                    return $actionBtn;
                }
            },
            {
                "data": "status_changed_at",
            },
            {
                "data": "new_created_at",
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {

                    if ("<?php echo e(Auth::user()->hasRole('super-admin')); ?>") {

                        var viewOrder = '../user/' + full.user_id;
                        var actionBtn = '<a href="' + viewOrder + '" target="_blank" title="View User" class="edit btn btn-primary btn-sm" ><i class="os-icon os-icon-eye"></i></a>';
                        actionBtn += `<button type="button" class="btn btn-primary btn-sm show-kyc-modal" data-id="${full.id}" data-vid="${full.video_path}" data-votp="${full.kyc_text}" data-name="${full?.business_name?.pan_owner_name}">
                                <i class="os-icon os-icon-external-link"></i>
                            </button>`;
                        return `<span class='xttl-inline-flex'>${actionBtn}</span>`;
                        //My name is (YOUR NAME) and verification OTP is <strong>otp</strong>
                    } else {
                        return ``;
                    }
                }
            }
        ];
        datatableSetup(url, options, onDraw);
    });

    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
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
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.user_id = $('#searchForm').find('[name="user_id"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                    // d.is_active = $('#searchForm').find('[name="is_active"]').val();
                    // d.service_is_active = $('#searchForm').find('[name="service_is_active"]').val();
                    // d.filter_area = $('#searchForm').find('[id="filter_area"]').val();
                    // d.services = $('#searchForm').find('[id="services"]').val();
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
        $(".select2").val(null).trigger('change');
        $('#datatable').dataTable().api().ajax.reload();
    });


    function userStatusChange(userId, $status) {
        $('#remarks').val('');
        $('#user_id').val(userId);
        $('#status_id').val($status.value);
        $('#userStatusChange').modal('show');

    }
    $(".swal2-confirm").click(function() {
        $('#userStatusChange').modal('hide');
    });

    function isJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    $('#datatable').on('click', '.show-kyc-modal', function() {
        let otp = $(this).attr('data-votp');
        let id = $(this).attr('data-id');
        let name = $(this).attr('data-name');
        let kycText = `My name is (${name}) and verification OTP is <strong>${otp}</strong>`;

        let vid = $(this).attr('data-vid');
        vid = `<?php echo e(url('')); ?>/storage/app/public/videos/${vid}`;

        $('#kyc-text').html(kycText);
        $('#kycVid').prop('src', vid);
        $('#kycid').val(id);

        $("#onboardingWideSlideModal").modal("show");
    });

    $('#btnApp').on('click', function() {
        $('#approveVidModal').val($('#kycid').val());
        $('#approveKycModal').modal('show');

        // Swal.fire({
        //     title: 'Video KYC',
        //     text: 'Are you sure to approve?',
        //     showDenyButton: true,
        //     confirmButtonText: 'Confirm',
        //     denyButtonText: `Cancel`,
        //     buttonsStyling: !1,
        //     customClass: {
        //         confirmButton: "btn btn-success mr-2",
        //         denyButton: "btn btn-danger"
        //     }
        // }).then((result) => {
        //     if (result.isConfirmed) {
        //         approveReject('app', $('#kycid').val());
        //     }
        // });
    });

    $('#btnRej').on('click', function() {

        $('#vidModal').val($('#kycid').val());
        $('#rejectKycModal').modal('show');

        // Swal.fire({
        //     title: 'Video KYC',
        //     text: 'Are you sure to reject?',
        //     showDenyButton: true,
        //     confirmButtonText: 'Confirm',
        //     denyButtonText: `Cancel`,
        //     buttonsStyling: !1,
        //     customClass: {
        //         confirmButton: "btn btn-success mr-2",
        //         denyButton: "btn btn-danger"
        //     },
        // }).then((result) => {
        //     if (result.isConfirmed) {
        //         // console.log(login);
        //         // approveReject('rej', $('#kycid').val());
        //     }
        // });
    });

    function approveReject(value, vid) {

        if (vid == '' || vid == null || vid == undefined) {
            return false;
        } else {

            const formData = new FormData();
            //console.log(document.querySelector('meta[name="csrf-token"]').content);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('value', value);
            formData.append('vid', vid);

            $.ajax({
                type: 'POST',
                url: `<?php echo e(url('')); ?>/admin/users/video-kyc`,
                data: formData,
                processData: false,
                contentType: false
            }).done(function(response) {
                // print the output from the upload.php script
                if (response.status === 'SUCCESS') {
                    Swal.fire({
                        title: 'Success',
                        text: response.message,
                        customClass: {
                            confirmButton: "btn btn-success"
                        }
                    });

                    $('#onboardingWideSlideModal').modal('hide');
                    $('#kyc-text').html('');
                    $('#kycVid').prop('src', '');
                    $('#kycid').val('');
                    $("#datatable").DataTable().ajax.reload();
                } else {
                    Swal.fire({
                        title: 'Failed',
                        text: response.message,
                        icon: "error",
                        buttonsStyling: !1,
                        customClass: {
                            confirmButton: "btn btn-success"
                        }
                    });
                }
            });

        }
    }


    /**
     * Reset Reject Kyc Form 
     */
    function resetRejectForm(response) {
        if (response.status === 'SUCCESS') {
            $('#rejectKycForm').trigger('reset');
            $('#vidModal').val('');
            $('#rejectKycModal').modal('hide');

            $('#onboardingWideSlideModal').modal('hide');
            $('#kyc-text').html('');
            $('#kycVid').prop('src', '');
            $('#kycid').val('');
            // $("#datatable").DataTable().ajax.reload();
        }
    }

    /**
     * Reset Approve Kyc Form 
     */
    function resetApproveForm(response) {
        if (response.status === 'SUCCESS') {
            $('#approveKycForm').trigger('reset');
            $('#approveVidModal').val('');
            $('#approveKycModal').modal('hide');

            $('#onboardingWideSlideModal').modal('hide');
            $('#kyc-text').html('');
            $('#kycVid').prop('src', '');
            $('#kycid').val('');
            // $("#datatable").DataTable().ajax.reload();
        }
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/reports/user_video_kyc.blade.php ENDPATH**/ ?>