<?php $__env->startSection('title', $page_title); ?>

<?php $__env->startSection('style'); ?>
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

    #viewBankInfo .modal-body,
    #viewSchemeInfoModal .modal-body {
        max-height: 500px;
        overflow-y: auto;
    }
</style>
<link href="<?php echo e(url('public/css/jquerysctipttop.css')); ?>" rel="stylesheet" type="text/css">
<link href="<?php echo e(url('public/css/style.css')); ?>" rel="stylesheet" type="text/css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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
                                    <label>Pan Number <span class="requiredstar"></span></label>
                                    <input type="text" name="searchUserInfo" class="form-control" placeholder="Enter Search Key" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">User Status:</label>
                                    <select class="form-control" name="is_active">
                                        <option value=""> Select Status</option>
                                        <option value="0">Initiate</option>
                                        <option value="1">Active</option>
                                        <option value="2">Inactive</option>
                                        <option value="3">Suspended</option>
                                        <option value="4">Blocked</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Filter by Service:</label>
                                    <select class="form-control js-example-basic-multiple" multiple="" id="services" name="services[]">
                                        <?php $__currentLoopData = $serviceListObject; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceListObjects): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($serviceListObjects->id); ?>">
                                            <?php echo e($serviceListObjects->title); ?>

                                        </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Service Status:</label>
                                    <select class="form-control" name="service_is_active">
                                        <option value=""> Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Filter Area:</label><br>
                                    <select class="form-control js-example-basic-multiple" multiple="" id="filter_area" name="filter_area[]">
                                        <optgroup label="WEB">
                                            <option value="is_web_enable#1">WEB - Active</option>
                                            <option value="is_web_enable#0">WEB - Inactive</option>
                                        </optgroup>
                                        <optgroup label="API">
                                            <option value="is_api_enable#1">API - Active</option>
                                            <option value="is_api_enable#0">API - Inactive</option>
                                        </optgroup>
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

                        </div>
                    </fieldset>
                </form>

                <div class="table-responsive" style=" overflow-x: auto;">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name - Biz. name</th>
                                <th>Email - Mobile</th>
                                <th>Services</th>
                                <th>Services Value</th>
                                <th>Balances (&#8377;)</th>
                                <th>Status</th>
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
<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="userStatusChange" data-DataTablesd="datatable" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Status Change
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="orderForm" role="cancel-request-form" action="<?php echo e(url('admin/users/statusChange')); ?>" data-DataTables="datatable" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""> Remarks </label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter remarks"></textarea>
                        <input class="form-control" type="hidden" name="user_id" id="user_id" />
                        <input class="form-control" type="hidden" name="status_id" id="status_id" />
                        <input class="form-control" type="hidden" name="logged_id" id="logged_id" value="<?php echo e(encrypt(Auth::user()->id)); ?>" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="cancel-request-form"]' value="Submit" />
                </div>
            </form>
        </div>
    </div>
</div>


<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="viewBankInfo" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="userNameModal"></h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <div class="modal-body">
                <div id="bankInfo"></div>
                <div id="vanInfo"></div>
                <div id="userConfig"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="viewSchemeInfoModal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <div class="modal-body">
                <div id="viewSchemeInfoModalData"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
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
                <h5 class="modal-title"></h5>
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

<script src="<?php echo e(asset('js/handlebars.js')); ?>"></script>
<script src="<?php echo e(asset('js/dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(asset('js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/buttons.print.min.js')); ?>"></script>
<script src="<?php echo e(asset('admin-js/user-list.js')); ?>"></script>

<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable">
        <tr>
            <td><b>UserId :</b> {{id}}</td>
            <td><b>Account Number :</b> {{account_number}}</td>
            <td><b>Business Type :</b> {{business_name.business_type}}</td>
        </tr>
		<tr>
            <td><b>Registerd Name :</b> {{business_name.name}}</td>
            <td><b>Business Mobile :</b> {{business_name.mobile}}</td>
            <td><b>Business Email :</b> {{business_name.email}}</td>
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
        var url = "<?php echo e(secure_url('admin/fetch')); ?>/users/0";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "name",
                render: function(data, type, full, meta) {
                    let col = full.name;
                    if (full.business_name)
                        col += (full.business_name.business_name != null) ? ` <br>[${full.business_name.business_name}]` : '';
                    return col;
                }
            },
            {
                "data": "email",
                render: function(data, type, full, meta) {
                    let col = `${full.email} <br>[${full.mobile}]`;
                    if (full.business_name)
                        col += (full.business_name.pan_number != null) ? `<br>[${full.business_name.pan_number}]` : '';
                    return col;
                }
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {
                    let srv = '';
                    full.service_name.forEach((obj) => {
                        if (obj.is_active === '1') {
                            srv += `<span class="xttl-inline-flex">[ ` + serviceBadge(obj.service.service_name);
                            if (obj.is_web_enable == '1') {
                                srv += `&nbsp;` + serviceBadge(obj.service.service_name, 'active', 'WEB') + ` `;
                            }
                            if (obj.is_api_enable == '1') {
                                srv += `&nbsp;` + serviceBadge(obj.service.service_name, 'active', 'API') + ` `;
                            }

                            srv += ` ]</span><br>`;
                        } else if (obj.is_active === '0') {
                            srv += `<span class="xttl-inline-flex">[ ` + serviceBadge(obj.service.service_name, 'inactive');

                            if (obj.is_web_enable == '1') {
                                srv += `&nbsp;` + serviceBadge(obj.service.service_name, 'inactive', 'WEB') + ` `;
                            }
                            if (obj.is_api_enable == '1') {
                                srv += `&nbsp;` + serviceBadge(obj.service.service_name, 'inactive', 'API') + ` `;
                            }

                            srv += ` ]</span><br>`;
                        }
                    });
                    return srv.slice(0, -4);
                }
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {
                    let srv = '';
                    full.service_name.forEach((obj) => {
                        let webvalue = '';
                        let apivalue = '';
                        if (obj.web_value != '' && obj.web_value != null) {
                            if (isJsonString(obj.web_value)) {
                                jsonObj = JSON.parse(obj.web_value);
                                for (var index in jsonObj) {
                                    webvalue += `${index}:${jsonObj[index]}<br>`;
                                }
                                webvalue = webvalue.slice(0, -4);
                            } else {
                                webvalue += `${obj.web_value}`;
                            }

                            webvalue = `<br>[ ${webvalue} ]`;
                        }
                        if (obj.api_value != '' && obj.api_value != null) {
                            if (isJsonString(obj.api_value)) {
                                jsonObj = JSON.parse(obj.api_value);
                                for (var index in jsonObj) {
                                    apivalue += `${index}:${jsonObj[index]}<br>`;
                                }
                                apivalue = apivalue.slice(0, -4);
                            } else {
                                apivalue += `${obj.api_value}`;
                            }

                            apivalue = `<br>[ ${apivalue} ]`;
                        }

                        if (webvalue != '') {
                            srv += `${serviceBadge(obj.service.service_name, 'active', 'WEB')} ${webvalue}<br>`;
                        }
                        if (apivalue != '') {
                            srv += `${serviceBadge(obj.service.service_name, 'active', 'API')} ${apivalue}<br>`;
                        }
                    });
                    srv = srv.slice(0, -4);
                    return srv;
                }
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {
                    let bal = full.transaction_amount;

                    full.service_name.forEach((obj) => {
                        if (obj.service.service_slug === 'payout')
                            bal += obj.transaction_amount;
                    });

                    return numberWithCommas(bal.toFixed(2));
                }
            },
            {
                "data": "is_active",
                render: function(data, type, full, meta) {
                    if (data == '0') {
                        var $actionBtn = showSpan("Initiate");
                    } else if (data == '1') {
                        var $actionBtn = showSpan("Active");;
                    } else if (data == '2') {
                        var $actionBtn = showSpan("Inactive");;
                    } else if (data == '3') {
                        var $actionBtn = showSpan("Suspended");;
                    } else if (data == '4') {
                        var $actionBtn = showSpan("Blocked");;
                    }
                    return $actionBtn;
                }
            },
            {
                "data": "new_created_at",
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {

                    let usrConfig = `<button type="button" value="${full.id}" data-username="${full.name}" class="btn btn-sm btn-primary openUserConfigModal"><i class="fas fa-cogs"></i></button>`;

                    if ("<?php echo e(Auth::user()->hasRole('super-admin')); ?>") {
                        var isActive = `<select id="userStatusChangeId" onchange="userStatusChange(` + full.id + `,this)" class="userStatusChangeClass form-control" style="width: 121px;    margin-right: 13px; ">`;
                        if (full.is_active == 0) {
                            isActive += `<option value="<?php echo e(encrypt(0)); ?>" selected> Initiate </option>`;
                        } else {
                            isActive += `<option value="<?php echo e(encrypt(0)); ?>"> Initiate </option>`;
                        }
                        if (full.is_active == '1') {
                            isActive += `<option value="<?php echo e(encrypt(1)); ?>" selected> Active </option>`;
                        } else {
                            isActive += `<option value="<?php echo e(encrypt(1)); ?>"> Active </option>`;
                        }

                        if (full.is_active == 2) {
                            isActive += `<option value="<?php echo e(encrypt(2)); ?>" selected> Inactive </option>`;
                        } else {
                            isActive += `<option value="<?php echo e(encrypt(2)); ?>"> Inactive </option>`;
                        }
                        if (full.is_active == '3') {
                            isActive += `<option value="<?php echo e(encrypt(3)); ?>" selected> Suspended </option>`;
                        } else {
                            isActive += `<option value="<?php echo e(encrypt(3)); ?>" > Suspended </option>`;
                        }
                        if (full.is_active == '4') {
                            isActive += `<option value="<?php echo e(encrypt(4)); ?>" selected>  Blocked </option>`;
                        } else {
                            isActive += `<option value="<?php echo e(encrypt(4)); ?>">  Blocked </option>`;
                        }
                        isActive += `</select> </br> `;
                        var $viewOrder = 'user/' + full.id;
                        var $actionBtn = isActive + ' <a href="' + $viewOrder + '" target="_blank" title="View User" class="edit btn btn-primary btn-sm" ><i class="os-icon os-icon-eye"></i></a><a href="admin/userprofile/'+full.id+'" target="_blank" class="edit btn btn-primary btn-sm"><i class="os-icon os-icon-edit"></i></a><a target="_blank" href="user/userdetails/' + full.id + '" class="edit btn btn-primary btn-sm"><i class="os-icon os-icon-external-link"></i></a>';
                        return `<span class='xttl-inline-flex'>${$actionBtn + usrConfig}</span>`;
                    } else if (full.business_name != null && full.business_name != undefined && "<?php echo e(Auth::user()->hasRole('support')); ?>" && full.business_name.is_kyc_updated == '1') {
                        var $viewUserDetails = "<?php echo e(url('admin/profileOpenByAdmin')); ?>/" + full.id;
                        var $actionBtnTwo = ' <a href="' + $viewUserDetails + '" target="_blank" title="View User" class="edit btn btn-primary btn-sm" ><i class="os-icon os-icon-eye"></i></a>';
                        return `<span class='xttl-inline-flex'>${$actionBtnTwo + usrConfig}</span>`;
                    } else {
                        return ` `;
                    }
                }
            }
        ];
        datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width", $(".table-responsive").width());
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
                    d.searchUserInfo = $('#searchForm').find('[name="searchUserInfo"]').val();
                    d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                    d.is_active = $('#searchForm').find('[name="is_active"]').val();
                    d.service_is_active = $('#searchForm').find('[name="service_is_active"]').val();
                    d.filter_area = $('#searchForm').find('[id="filter_area"]').val();
                    d.services = $('#searchForm').find('[id="services"]').val();
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
        $(".js-example-basic-multiple").val(null).trigger('change');
        $('#datatable').dataTable().api().ajax.reload();
    });

    var servicesJsonData = JSON.parse('<?php echo $serviceList; ?>');

    function userStatusChange(userId, $status) {
        $('#remarks').val('');
        $('#user_id').val(userId);
        $('#status_id').val($status.value);
        $('#userStatusChange').modal('show');

    }
    $(".swal2-confirm").click(function() {
        $('#userStatusChange').modal('hide');
    });

    function serviceBadge($service, $status = 'active', $text = '') {

        if ($status === 'inactive') {
            return `<span class="badge badge-danger">${($text != '')?$text:$service}</span>`;
        }

        switch ($service) {
            case 'UPI Stack':
                return `<span class="badge badge-primary">${($text != '')?$text:$service}</span>`;
                break;
            case 'Smart Payouts':
                return `<span class="badge badge-success">${($text != '')?$text:$service}</span>`;
                break;
            case 'Smart Collect':
                return `<span class="badge badge-warning">${($text != '')?$text:$service}</span>`;
                break;
            case 'Aadhaar Banking':
                return `<span class="badge badge-dark">${($text != '')?$text:$service}</span>`;
                break;
            default:
                return `<span class="badge badge-danger">${($text != '')?$text:$service}</span>`;
                break;
        }
    }

    function isJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/admin/reports/users.blade.php ENDPATH**/ ?>