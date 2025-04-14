<?php $__env->startSection('title','User Transaction'); ?>
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
                <!--------------------
                START - Color Scheme Toggler
                -------------------->
                <div class="element-wrapper">
  
  <div class="element-box">
    <h5 class="form-header">
      UPI Transaction
    </h5>
    <div class="element-actions">
    </div>
                <div class="form-desc">
                    &nbsp;
                </div>
                <form id="searchForm">
                    <fieldset class="form-group">
                        <div class="row">
                            

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" id="fromDate" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>" <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
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
                                                <b><i class="icon-search4"></i></b> Search
                                            </button>
                                            <button type="button" class="btn btn-warning btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Reset">
                                                <b><i class="icon-rotate-ccw3"></i></b> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top:25px;">
                                <span class="requiredstar" id="downloadExcelError"></span>
                                <a href="#" style="display:none"  class="btn btn-success btn-xs btn-labeled legitRipple" id="downloadExcel">Download Excel</a>
                            </div>
                        </div>
                    </fieldset>
                </form>
    <!--------------------
    START - Controls Above Table
    -------------------->
    
    <!--------------------
    END - Controls Above Table
    -------------------->
    <div class="table-responsive">
      <!--------------------
      START - Basic Table
      -------------------->
      <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                            <th></th>
                                <th>Id</th>
                                <th>Customer Ref ID</th>
                                <th>Payer Name</th>
                                <th>Payer Mobile</th>
                                <th>Amount</th>
                                <th>Txn Note</th>
                                <th>Txn Date</th>
                                <th>Created Date</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        </table>
      <!--------------------
      END - Basic Table
      -------------------->
    </div>
  </div>

  <div class="element-box">
    <h5 class="form-header">
      VAN Transaction 
    </h5>
    <div class="element-actions">
    </div>
                <div class="form-desc">
                    &nbsp;
                </div>
                <!-- <form  id="searchForm">
                    <fieldset class="form-group">
                        <div class="row">
                            

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" id="fromDate" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>" <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
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
                                                <b><i class="icon-search4"></i></b> Search
                                            </button>
                                            <button type="button" class="btn btn-warning btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Reset">
                                                <b><i class="icon-rotate-ccw3"></i></b> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top:25px;">
                                <span class="requiredstar" id="downloadExcelError"></span>
                                <a href="#" style="display:none"  class="btn btn-success btn-xs btn-labeled legitRipple" id="downloadExcel">Download Excel</a>
                            </div>
                        </div>
                    </fieldset>
                </form> -->
    <!--------------------
    START - Controls Above Table
    -------------------->
    
    <!--------------------
    END - Controls Above Table
    -------------------->
    <div class="table-responsive">
      <!--------------------
      START - Basic Table
      -------------------->
      <table class="table table-sm table-bordered table-striped table-hover" id="vandatatable">
                        <thead>
                            <tr>
                            
                                
                                <th>VAN</th>
                                <th>Amount</th>
                                <th>UTR</th>
                                <th>Reference ID</th>
                                <th>Remitter Aaccount</th>
                                <th>Remitter Name</th>
                                <th>Created</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        </table>
      <!--------------------
      END - Basic Table
      -------------------->
    </div>
  </div>
</div>
</div>
</div>

            <?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

	
<script src="<?php echo e(url('public/js/handlebars.js')); ?>" ></script>
	<script id="details-template"  type="text/x-handlebars-template">

    <table class="expandtable">
        <tr>
            <td><b>Npci txn id :</b></td><td>{{npci_txn_id}}</td><td><b>Bank txn id :</b></td><td>{{bank_txn_id}} </td><td><b>Customer ref id :</b></td><td>{{customer_ref_id}}</td>
        </tr>
		<tr><td><b>Description :</b></td><td> {{description}}</td><td><b>Payee vpa :</b></td><td> {{payee_vpa}}</td></td>
        </tr>
		
		<tr></tr>
        </table>
</script>
<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>
<script>
$(document).ready(function(){
var template = Handlebars.compile($("#details-template").html());

 // Add event listener for opening and closing details
    $('#datatable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
		var table=$("#datatable").DataTable();
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( template(row.data()) ).show();
            tr.addClass('shown');
        }
    });
});
</script>

<script type="text/javascript">
$(document).ready(function () {
        var url = "<?php echo e(custom_secure_url('admin/fetch')); ?>/upiCallbackTransaction/<?php echo e($user['id']); ?>";
        var onDraw = function() {
        };
        var options = [
            {
                "className":      'details-control',
                "orderable":      false,
                "defaultContent": ''
            },
            { "data" : "id"},
            { "data" : "customer_ref_id",},
            { "data" : "payer_acc_name"},
            { "data" : "payer_mobile"},
            {"data"  : "amount"},
            { "data" : "txn_note"},
            {"data" : "txn_date"
                
            },
            { "data" : "created_at",
            },
            
        ];
        datatableSetup(url, options, onDraw);
    });
function vanTransaction(){
        var url1 = "<?php echo e(custom_secure_url('admin/fetch')); ?>/vanTransaction/<?php echo e($user['id']); ?>";
        var onDraw = function() {
        };
        var options = [
            {
                "data": "v_account_number"
            },
            {
                "data": "amount"
            },
            {
                "data": "utr"
            },
            {
                "data": "reference_id"
            },
            {
                "data": "remitter_account"
            },
            {
                "data": "remitter_name"
            },
            {
                "data": "new_created_at",
            }
        ];
        datatableSetup(url1, options, onDraw,'#vandatatable');

    }
$(document).ready(function () {
vanTransaction();
    });


function datatableSetup(urls, datas, onDraw=function () {}, ele="#datatable", element={}) {
	
    var options = {
        processing: true,
        serverSide: true,
        ordering: true,
        "searching": true,
        buttons: [
            'excel', 'pdf'
        ],
        order: [],
            columnDefs: [ {
                'targets': [0], /* column index [0,1,2,3]*/
                'orderable': false, /* true or false */
            }],
        "lengthMenu": [[10, 25, 50 , 75 , 100 , 200 , 500 ,1000 , -1], [10, 25, 50 , 75 , 100 , 200 , 500 ,1000 ,1500]],
        "iDisplayLength": 25,
        dom: "Bfrltip",
        buttons: ['excel'],
        language: {
            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
        },
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        preDrawCallback: function() {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        },
        ajax:{
            url : urls,
            type: "post",
            data:function( d )
                {$( "" )
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                },
            beforeSend: function(){
            },
            complete: function(){
                $('#searchForm').find('button:submit').button('reset');
                $('#formReset').button('reset');
            },
            error:function(response) {
            }
        },
        columns: datas
    };

    $.each(element, function(index, val) {
        options[index] = val;
    });

    var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
    return DT;
}
$('#formReset').click(function () {
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });

</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/reports/userDetails.blade.php ENDPATH**/ ?>