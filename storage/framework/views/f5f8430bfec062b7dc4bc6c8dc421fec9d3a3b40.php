
<?php $__env->startSection('title','AEPS Merchants'); ?>

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
        margin-bottom: 1rem;
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
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label>Any Key <span class="requiredstar"></span></label>
                                    <input type="text" name="searchText" class="form-control" placeholder="Enter Search Key" />
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>" <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" <?php if(isset($_GET['to'])): ?> value="<?php echo e($_GET['to']); ?>" <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group" style="margin-top:26px;">
                                    <button type="submit" class="btn btn-primary" data-loading-text="<b>
                                                <i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i>
                                        </b> Search</button>
                                    <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b>
                                                <i class='fa fa-spin fa-spinner'></i></b> Reset"><b><i class="icon-rotate-ccw3"></i></b> Reset</button>

                                        </div>
                                        </div>
                                    </div>
                                </fieldset>
                        </form>
                    <div class="table-responsive" id="table_responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%;">
                        <thead>
                            <tr>
                                <th></th>
                                <th>User</th>
                                <th>Operator</th>
                                <th>Amount</th>
                                <th>Phone</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Reference ID</th>
                                <th>Order Reference</th>
                                <th>Created</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
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

    <table class="expandtable">
        <tr>
            <td><b>Pin Code :</b></td><td>{{pin_code}}</td><td><b>DOB :</b></td><td>{{dob}}</td><td><b>Shop Name :</b></td><td>{{shop_name}}</td>
        </tr>
		<tr><td><b>Shop Address :</b></td><td> {{shop_address}}</td><td><b>Shop Pin :</td><td>{{shop_pin}}</td><td><b>Service:</td><td>{{service}}</td>
        </tr>
        <tr>
            <td>Documents Status : </td>
            <td>{{documents_status}}</td>
            <td>Documents Remarks : </td>
            <td>{{documents_remarks}}</td>
        </tr>
        </table>
</script>
<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>
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
                row.child(template(row.data())).show();
                tr.addClass('shown');
            }
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var url = "<?php echo e(custom_secure_url('user/fetch')); ?>/recharge-back/0";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "merchant_code"
            },
            {
                "data": "first_name",
                render: function(data, type, full, meta) {
                    var fname = full.first_name;
                    var mname = "";
                    var lname = "";
                    var name = "";
                    if (full.middle_name != null || full.middle_name != undefined) {
                        mname = full.middle_name;
                    }
                    if (full.last_name != null || full.last_name != undefined) {
                        lname = full.last_name;
                    }
                    return fname + " " + mname + " " + lname;
                }
            },
            {
                "data": "email_id"
            },
            {
                "data": "mobile",
                // render: function(data, type, full, meta) {
                //     var string = data.toString();
                //     return 'XXXXXX' + string.substr(string.length - 4);
                // }
            },
            {
                "data": "aadhar_number",
                render: function(data, type, full, meta) {
                    var string = data.toString();
                    return 'XXXXXXXX' + string.substr(string.length - 4) + ' / XXXXX' + full.pan_no.substr(data.length - 6);
                }
            },
            {
                "data": "ekyc",
                render: function(data, type, full, meta) {
                    var status = "";
                    if (data != null || data != undefined) {
                        if (data.length > 2) {
                            var ekyc = JSON.parse(data);
                            if (ekyc.paytm != null || ekyc.paytm != undefined) {
                                if (ekyc.paytm.is_ekyc != undefined) {
                                    if (ekyc.paytm.is_ekyc == 1) {
                                        status += "paytm : <div class='status-pill green' title='Active'></div> <br>";
                                    } else if (ekyc.paytm.is_ekyc == 0) {
                                        status += "paytm : <div class='status-pill yellow' title='Pending'></div> <br>";
                                    } else if (ekyc.paytm.is_ekyc == 2) {
                                        status += "paytm : <div class='status-pill red' title='Rejected'></div> <br>";
                                    }
                                }
                            }
                            if (ekyc.sbm != null || ekyc.sbm != undefined) {
                                if (ekyc.sbm.is_ekyc != undefined) {
                                    if (ekyc.sbm.is_ekyc == 1) {
                                        status += "sbm : <div class='status-pill green' title='Active'></div> <br>";
                                    } else if (ekyc.sbm.is_ekyc == 0) {
                                        status += "sbm : <div class='status-pill yellow' title='Pending'></div> <br>";
                                    } else if (ekyc.sbm.is_ekyc == 2) {
                                        status += "sbm : <div class='status-pill red' title='Rejected'></div> <br>";
                                    }
                                }
                            }
                            if (ekyc.icici != null || ekyc.icici != undefined) {
                                if (ekyc.icici.is_ekyc != undefined) {
                                    if (ekyc.icici.is_ekyc == 1) {
                                        status += "icici : <div class='status-pill green' title='Active'></div> <br>";
                                    } else if (ekyc.icici.is_ekyc == 0) {
                                        status += "icici : <div class='status-pill yellow' title='Pending'></div> <br>";
                                    } else if (ekyc.icici.is_ekyc == 2) {
                                        status += "icici : <div class='status-pill red' title='Rejected'></div> <br>";
                                    }
                                }
                            }

                            if (ekyc.airtel != null || ekyc.airtel != undefined) {
                                if (ekyc.airtel.is_ekyc != undefined) {
                                    if (ekyc.airtel.is_ekyc == 1) {
                                        status += "airtel : <div class='status-pill green' title='Active'></div> <br>";
                                    } else if (ekyc.airtel.is_ekyc == 0) {
                                        status += "airtel : <div class='status-pill yellow' title='Pending'></div> <br>";
                                    } else if (ekyc.airtel.is_ekyc == 2) {
                                        status += "airtel : <div class='status-pill red' title='Rejected'></div> <br>";
                                    }
                                }
                            }
                        }
                        //console.log(ekyc.paytm);

                    }
                    return status;
                }
            },
            {
                "data": "documents_status",
                render: function(data, type, full, meta) {
                    if (full.is_ekyc_documents_uploaded == '1') {
                            if (data == 'pending') {
                            return '<span class="badge badge-warning">Pending</span>';
                        } else if (data == 'accepted') {
                            return '<span class="badge badge-success">Approved</span>';
                        } else if (data == 'rejected') {
                            return '<span class="badge badge-rejected">Rejected</span>';
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                }
            },
            {
                "data": "address"
            },
            {
                "data": "new_created_at",
            }
        ];
        datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width",$("#table_responsive").width());
    });

    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        return false;
    });

function datatableSetup(urls, datas, onDraw=function () {}, ele="#datatable", element={}) {
    var options = {
        processing: true,
        serverSide: true,
        ordering: true,
        scrollX: true,
        "searching": true,
        buttons: [
            'excel'
        ],
        order: [],
            columnDefs: [ {
                'targets': [0], /* column index [0,1,2,3]*/
                'orderable': false, /* true or false */
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
        $('#datatable').dataTable().api().ajax.reload();
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/user/recharge_back.blade.php ENDPATH**/ ?>