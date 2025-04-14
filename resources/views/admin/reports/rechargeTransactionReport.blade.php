@extends('layouts.admin.app')
@section('title','Aeps Dashboard')
@section('content')
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
                    {{$page_title}}
                    </h5>
                    <div class="element-actions">
                                </div>
                   
                        <form id="searchForm">

                            <fieldset class="form-group">

                                <div class="row">
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">From Date <span class="requiredstar"></span></label>
                                            <input type="date" name="from" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}" @else value="{{date('Y-m-d')}}" @endif />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">To Date <span class="requiredstar"></span></label>
                                            <input type="date" name="to" class="form-control" @if(isset($_GET['to']))  value="{{$_GET['to']}}" @else value="{{date('Y-m-d')}}" @endif />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">User <span class="requiredstar"></span></label>
                                            <select class="form-control select2" name="user_id"  >
                                                <option value="">Select user</option>
                                                @foreach($userData as $val)
                                                <option value="{{$val->id}}">{{$val->userName}}</option>
                                                @endforeach
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
                                <div class="col-sm-6">
                                
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalSuccessTxn">
                                        {{@$totalAmount[0]->totalCount}} | ₹{{empty(@$totalAmount[0]->totalAmount) ? 0 : @$totalAmount[0]->totalAmount}}
                                        </div>
                                        <div class="label">
                                            Counts & Amounts
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="totalCommissionSuccessTxn">
                                        {{@$totalAmount[1]->totalCommissionCount}} | ₹{{empty(@$totalAmount[3]->totalCommissionAmount) ? 0 : @$totalAmount[1]->totalCommissionAmount}}
                                        </div>
                                        <div class="label">
                                        Commission Counts & Amounts
                                        </div>
                                    </a>
                                </div>
                                
                            </div>

                        </div>
                    </div>
                </div>
                

                <!--  -->
                        </div>
                    <div class="element-box">
                    <div class="element-content">
                    <div class="row" >

                    <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable2">
                        <thead>
                            <tr>

                            <th>User</th>
                            <th>Total Count</th>
                            <th>Total Amount</th>
                            
                            
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                
                                <th>Total</th>
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
            @endsection
@section('scripts')

    <script src="{{url('public/js/handlebars.js')}}" ></script>
    <script id="details-template"  type="text/x-handlebars-template">

    
</script>
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>


<script type="text/javascript">


    $(document).ready(function () {
  
        $('td > span[data-toggle="tooltip"]').tooltip({html: true});
        var url = "{{custom_secure_url('admin/get')}}/recharge/0";
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
                "data": "counts"
            },
            {
                "data": "totalAmount"
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
                .column( 2 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                  // Total over all pages
            
 
            // Total over this page
            pageTotal = api
                .column( 2, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                   // Total over this page
            // pageTotalFourth = api
            //     .column( 4, { page: 'current'} )
            //     .data()
            //     .reduce( function (a, b) {
            //         return intVal(a) + intVal(b);
            //     }, 0 );
 
            // Update footer
            $( api.column( 2 ).footer() ).html(
              pageTotal 
            );
            
            // $( api.column( 4 ).footer() ).html(
            //   pageTotalFourth 
            // );
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
        url:"{{custom_secure_url('admin/reports/recharge')}}",
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
            if (data[1].totalCommissionAmount != null) {
               var totalAmount= parseFloat(data[1].totalCommissionAmount).toFixed(2);
                    $('#totalCommissionSuccessTxn').html(`${data[1].totalCommissionCount} | ₹${totalAmount}`);
            }
            else {
                $('#totalCommissionSuccessTxn').html(`${data[1].totalCommissionCount} | ₹0`);
            }
            var htmlTxt = '';
            

            
            
        }
    })
}

function showMerchant(user_id) {
    $('#merchantList').modal('show');
    from = $('#searchForm').find('[name="from"]').val();
    to = $('#searchForm').find('[name="to"]').val();
    $.post("{{url('admin/recharge/merchantList')}}",
        {
            user_id: user_id,
            from: from,
            to: to,
            "_token": "{{csrf_token()}}"
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

@endsection
