<?php $__env->startSection('title','Aeps Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<style type="text/css">
.expandtable{
    width: 100% !important;
    margin-bottom: 1rem;
}
.expandtable,tbody,tr,td{

	margin-bottom: 1rem;
}

.content-box {
        padding: 10px !important;
    }

    .element-box {
        padding: 1.5rem 1rem !important;
    }
    
    .modal-dialog,
.modal-content {
    /* 100% of window height */
    height: 85%;
}

.modal-body {
    /* 100% = dialog height, 120px = header + footer */
    overflow-y: scroll;
}

#datatables  thead tr th {
    position: sticky;
    top: 0;
    background-color: #dee2e6;
    border-bottom:none;
    border-top:none;
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
                                </div>
                   
                        <form id="searchForm">

                            <fieldset class="form-group">

                                <div class="row">
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">From Date <span class="requiredstar"></span></label>
                                            <input type="date" name="from" class="form-control" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>" <?php else: ?> value="<?php echo e(date('Y-m-d')); ?>" <?php endif; ?> />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">To Date <span class="requiredstar"></span></label>
                                            <input type="date" name="to" class="form-control" <?php if(isset($_GET['to'])): ?>  value="<?php echo e($_GET['to']); ?>" <?php else: ?> value="<?php echo e(date('Y-m-d')); ?>" <?php endif; ?> />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">User <span class="requiredstar"></span></label>
                                            <select class="form-control select2" name="user_id"  >
                                                <option value="">Select user</option>
                                                <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($val->id); ?>"><?php echo e($val->userName); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

                                        <!-- <div class="form-group" style="margin-top:26px;">
                                            <button type="submit" class="btn btn-primary" data-loading-text="<b>
                                                        <i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i>
                                                </b> Search</button>
                                            <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b>
                                                        <i class='fa fa-spin fa-spinner'></i></b> Reset"><b><i class="icon-rotate-ccw3"></i></b> Reset</button>
                                        </div> -->
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                        <div class="element-content">
                            
                            <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-4">
                                
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalSuccessTxn">
                                        <?php echo e(@$totalAmount[0]->totalCount); ?> | ₹<?php echo e(empty(@$totalAmount[0]->totalAmount) ? 0 : @$totalAmount[0]->totalAmount); ?>

                                        </div>
                                        <div class="label">
                                            Counts & Amounts
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalCommissionSuccessTxn">
                                        <?php echo e(@$totalAmount[3]->totalCommissionCount); ?> | ₹<?php echo e(empty(@$totalAmount[3]->totalCommissionAmount) ? 0 : @$totalAmount[3]->totalCommissionAmount); ?>

                                        </div>
                                        <div class="label">
                                        Commission Counts & Amounts
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-4"> 
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-danger" href="#">
                                        <div class="value font-1-5" id="merchantCode">
                                        <?php echo e(empty(@$totalAmount[1]->merchantCount) ? 0 : $totalAmount[1]->merchantCount); ?>

                                        </div>
                                        <div class="label">
                                            Merchants
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3" id="trnCount">
                            <?php $trType1 = ['be', 'ms', 'cw']?>
                            <?php $trType = []?>
                                <?php $__currentLoopData = $totalAmount[4]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trnCount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $trType[] = $trnCount->transaction_type; ?>
                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalSuccessCountTxn">
                                        <?php echo e(@$trnCount->trnCount); ?>

                                        </div>
                                        <div class="label">
                                        <?php echo e(CommonHelper::case(@$trnCount->transaction_type, 'u')); ?>

                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php $result = array_diff($trType1, $trType); ?>
                                <?php $__currentLoopData = $result; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trTypes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-sm-4">
                                        <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                            <div class="value font-1-5" id="totalSuccessCountTxn">
                                            0
                                            </div>
                                            <div class="label">
                                            <?php echo e(CommonHelper::case(@$trTypes, 'u')); ?>

                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">

                            <div class="row mb-xl-2 mb-xxl-3" id="lists">
                                <?php $__currentLoopData = $totalAmount[2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lists): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5">
                                        <?php echo e(@$lists->totalCount); ?> | ₹<?php echo e(@$lists->totalAmount); ?>

                                    </div>
                                        <div class="label">
                                        <?php echo e(CommonHelper::case($lists->route_type, 'u')); ?>

                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                        </div>
                    <div class="element-box">
                    <div class="element-content">
                    <div class="row" >

                    <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable2">
                        <thead>
                            <tr>

                            <th>User</th>
                            <th>Total/Active Merchants </th>
                            <th>Total Count</th>
                            <th>Total Amount</th>
                            <th>Settled Amount</th>
                            <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th> </th>
                                <th>Total</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        </table>
                    </div>
                    </div>

                    </div>
                </div>
                </div><!--------------------
                START - Color Scheme Toggler
                -------------------->
                </div>
            </div>
            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="merchantList" role="dialog" tabindex="-1">

<div class="modal-dialog" role="document" style="max-width: 834px;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">
                Merchants List
            </h5>
            <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
        </div>
      <div class="modal-body" style="padding-left: 1rem !important;
    padding-top: 0rem !important;">
      <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatables">
                        <thead>
                            <tr>
                            <th>Merchants </th>
                            <th>Counts</th>
                                <th>Amounts</th>
                                <th>Settled Amounts</th>
                                <th>Routes</th>
                                <th>Txn Type</th>
                            </tr>
                        </thead>
                        <tbody id="merchantTableResponse">
                        </tbody>
                        </table>

                    </div>
      </div>
    </div>
</div>
</div>
            <?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>

	<script src="<?php echo e(url('public/js/handlebars.js')); ?>" ></script>
	<script id="details-template"  type="text/x-handlebars-template">

    
</script>
<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>


<script type="text/javascript">


    $(document).ready(function () {
  
        $('td > span[data-toggle="tooltip"]').tooltip({html: true});
        var url = "<?php echo e(custom_secure_url('admin/get')); ?>/aepsUsers/0";
        var onDraw = function() { 
        };
        var options = [
            {
                "data":"name",
                render:function(data, type, full, meta)
                {

                    var $user = full.name+' <br/>'+full.email;
                    return $user;
                }
            },
            {
                "data": "total_agents",
                render:function(data, type, full, meta)
                {
                 
                    return setTooltipAndvalue(data,  'text-primary')+' | '+setTooltipAndvalue(full.active_agents, 'text-success') ;
                }
            },

         
            {
                "data": "counts"
            },
            {
                "data": "totalAmount"
            },
            {
                "data": "credited_amount"
            },
            {
                "data": "counts",
                render:function(data, type, full, meta)
                {
                   
                    return  '<span class="edit btn btn-primary btn-sm"><a href="javascript:void(0);" onclick="showMerchant(\'' + full.user_id + '\')"   style="color:white;text-decoration:none"><i class="os-icon os-icon-eye"></i></a></span>';;
                }
            }
        ];
        datatableSetup2(url, options, onDraw);
    });
    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();

        $('#datatable2').dataTable().api().ajax.reload();
        getRecords(from,to);
        return false;
    });


function datatableSetup2(urls, datas, onDraw=function () {}, ele="#datatable2", element={}) {
    var options = {
        processing: true,
        serverSide: true,
        ordering: true,
        "searching": true,
        buttons: [
            'excel'
        ],
        order: [],
            columnDefs: [ {
                "defaultContent": "-",
                'targets': [0], /* column index [0,1,2,3]*/
                'orderable': false, /* true or false */
            }],
        "lengthMenu": [[10, 25, 50 , 75 , 100 , 200 , 500 ,1000 , -1], [10, 25, 50 , 75 , 100 , 200 , 500 ,1000 ,1500]],
        dom: "Bfrltip",
        language: {
            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
        },
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api();
 
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            // Total over all pages
            total = api
                .column( 3 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                  // Total over all pages
            totalFourth = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Total over this page
            pageTotal = api
                .column( 3, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                   // Total over this page
            pageTotalFourth = api
                .column( 4, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Update footer
            $( api.column( 3 ).footer() ).html(
              pageTotal 
            );
            
            $( api.column( 4 ).footer() ).html(
              pageTotalFourth 
            );
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
                    d.user_id = $('#searchForm').find('[name="user_id"]').val();
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
        $('#datatable2').dataTable().api().ajax.reload();
        getRecords('','');
    });

function getRecords(from,to)
{
    $.ajax({
        url:"<?php echo e(custom_secure_url('admin/reports/aeps')); ?>",
        type:'POST',
        data:{_token: $('meta[name="csrf-token"]').attr('content'),from:from,to:to,user_id:$('#searchForm').find('[name="user_id"]').val()},
        success:function(response)
        {
            var data = JSON.parse(response);

            if (data[0].totalAmount != null) {
                    $('#totalSuccessTxn').html(`${data[0].totalCount} | ₹${data[0].totalAmount}`);
            }
            else {
                $('#totalSuccessTxn').html(`${data[0].totalCount} | ₹0`);
            }
            if (data[3].totalCommissionAmount != null) {
               var totalAmount= parseFloat(data[3].totalCommissionAmount).toFixed(2);
                    $('#totalCommissionSuccessTxn').html(`${data[3].totalCommissionCount} | ₹${totalAmount}`);
            }
            else {
                $('#totalCommissionSuccessTxn').html(`${data[3].totalCommissionCount} | ₹0`);
            }
            var htmlTxt = '';
            if (data[4] != null) {
                const trnType1 = [];
                const trnType = ['be', 'ms', 'cw'];
                $.each(data[4], function(key,val) {
                    trnType1[key] = val.transaction_type;
                    var trType = '';
                    if (val.transaction_type != '' && val.transaction_type != null) {
                        var trType =  val.transaction_type.toUpperCase();
                    }
                    htmlTxt += ` <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalSuccessCountTxn">
                                        ${val.trnCount}
                                        </div>
                                        <div class="label">
                                        ${trType}
                                        </div>
                                    </a>
                                </div>`;
                });
                let dif = trnType.filter(x => !trnType1.includes(x));
                $.each(dif, function(key,val) {
                    var trType = '';
                    if (val.transaction_type != '' && val.transaction_type != null) {
                        var trType =  val.transaction_type.toUpperCase();
                    }
                    htmlTxt += ` <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalSuccessCountTxn">
                                        0
                                        </div>
                                        <div class="label">
                                        ${trType}
                                        </div>
                                    </a>
                                </div>`;
                });
                $('#trnCount').html(htmlTxt);
            }
            else {
                var htmlTxt = '';
                const trnType = ['be', 'ms', 'cw'];
                $.each(trnType, function(key,val) {
                    htmlTxt += ` <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalSuccessCountTxn">
                                        0
                                        </div>
                                        <div class="label">
                                        ${val.transaction_type.toUpperCase()}
                                        </div>
                                    </a>
                                </div>`;
                });
                $('#trnCount').html(htmlTxt);
            }

            if (data[1].merchantCount != null) {
                    $('#merchantCode').html(`${data[1].merchantCount}`);
            }
            else {
                $('#merchantCode').html(`${data[1].merchantCount}`);
            }
            var listsDiv = '';
            $('#lists').html('');
            data[2].forEach((element) => {
                listsDiv += `<div class="col-sm-3"><a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5">`+element.totalCount +'| ₹' + element.totalAmount +
                                        `</div><div class="label">`+element.route_type.toUpperCase()+`</div></a></div>`;
            });
            $('#lists').html(listsDiv);
        }
    })
}

function showMerchant(user_id) {
    $('#merchantList').modal('show');
    from = $('#searchForm').find('[name="from"]').val();
    to = $('#searchForm').find('[name="to"]').val();
    $.post("<?php echo e(url('admin/aeps/merchantList')); ?>",
        {
            user_id: user_id,
            from: from,
            to: to,
            "_token": "<?php echo e(csrf_token()); ?>"
        },
    function(data, status){

        $('#merchantTableResponse').html(data.data);
    });

}
function setTooltipAndvalue(number, classs) {
   var html = `<span data-placement="top" data-toggle="tooltip" type="button" data-original-title="${number}" class="${classs}">${number}</span>`;

        return html;
    }

</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/reports/aepsTransactionReport.blade.php ENDPATH**/ ?>