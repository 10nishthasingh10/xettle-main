@extends('layouts.admin.app')
@section('title','Payout Dashboard')
@section('content')
<style type="text/css">
.expandtable{
    width: 100% !important;
    margin-bottom: 1rem;
}
.expandtable,tbody,tr,td{

	margin-bottom: 1rem;
}
/*div.dataTables_wrapper {
        width: 1300px;
        margin: 0 auto;
    }*/
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
                    <div class="form-desc">
                    &nbsp;
                    </div>
                        <form id="searchForm">

                            <fieldset class="form-group">

                                <div class="row">
                                    
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="">From Date <span class="requiredstar"></span></label>
                                            <input type="date" name="from" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}"  @endif />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="">To Date <span class="requiredstar"></span></label>
                                            <input type="date" name="to" class="form-control" @if(isset($_GET['to'])) value="{{$_GET['to']}}"  @endif />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="">User <span class="requiredstar"></span></label>
                                            <select class="form-control" name="user_id"  >
                                                <option value="">Select user</option>
                                                @foreach($userData as $val)
                                                <option value="{{$val->id}}">{{$val->userName}}</option>
                                                @endforeach
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
                        <!-- <div class="element-content">
                            <div class="row">
                                <div class="col-sm-12 col-xxl-12">
                                    <div class="tablos">
                                      <div class="row mb-xl-2 mb-xxl-3">
                                        <div class="col-sm-3">
                                          <a class="element-box el-tablo centered trend-in-corner padded bold-label" href="apps_support_index.html">
                                            <div class="value" id="totalCount">
                                              
                                            </div>
                                            <div class="label">
                                              Total Transaction
                                            </div>
                                            
                                          </a>
                                        </div>
                                        <div class="col-sm-3">
                                          <a class="element-box el-tablo centered trend-in-corner padded bold-label" href="apps_support_index.html">
                                            <div class="value">
                                              12
                                            </div>
                                            <div class="label">
                                              Closed Today
                                            </div>
                                            <div class="trending trending-down-basic">
                                              <span>12%</span><i class="os-icon os-icon-arrow-down"></i>
                                            </div>
                                          </a>
                                        </div>
                                        <div class="col-sm-3">
                                          <a class="element-box el-tablo centered trend-in-corner padded bold-label" href="apps_support_index.html">
                                            <div class="value" id="totalAmount">
                                              ₹
                                            </div>
                                            <div class="label">
                                              Total Amount
                                            </div>
                                            
                                          </a>
                                        </div>
                                        
                                      </div>
                                      
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    <div class="element-box">
                    <div class="element-content">
                    <div class="row">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-responsive display nowrap" style="width:100%" cellspacing="0" id="datatable">
                        <thead>
                            <tr>
                                
                                <th>User Name</th>
                                <th>User Email</th>
                                <th>Primary Opening Balance</th>
                                <th>Primary Closing Balance</th>
                                <th>Payout Opening Balance</th>
                                <th>Payout Closing Balance</th>
                                <th>Van In</th>
                                <th>Van Out</th>
                                <th>Upi In</th>
                                <th>Upi Collect In</th>
                                <th>Order Processed Count</th>
                                <th>Order Processed Amount</th>
                                <th>Total Tax</th>
                                <th>Total Fee</th>
                                <th>Order Failed Count</th>
                                <th>Order Failed Amount</th>
                                <th>Order Processing Count</th>
                                <th>Order Processing Amount</th>
                                <th>Record Date</th>
                                <th>Created</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
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

            @endsection
@section('scripts')

	<script src="{{url('public/js/handlebars.js')}}" ></script>
	<script id="details-template"  type="text/x-handlebars-template">

    <table class="expandtable">
        <tr>
            <td><b>van in :</b></td><td>@{{van_in}}</td><td><b>van_out:</b></td><td>@{{van_out}}</td><td><b>upi_in :</b></td><td>@{{upi_in}}</td>
        </tr>
        <tr><td><b>upi_collect_in :</b></td><td> @{{upi_collect_in}}</td><td><b>order_processed_count :</td><td>@{{order_processed_count}}</td><td><b>order_processed_amount:</td><td>@{{order_processed_amount}}</td>
        </tr>
        <tr><td><b>total_tax :</b></td><td> @{{total_tax}}</td><td><b>total_fee :</td><td>@{{total_fee}}</td><td><b>payout_RTGS:</td><td>@{{payout_RTGS}}</td>
        </tr>

        </table>
</script>
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
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
        var url = "{{custom_secure_url('admin/fetch')}}/daybook/0";
        var onDraw = function() {
        };
        var options = [
            {
                "data":"user",
                render:function(data, type, full, meta)
                {
                    if(data==null)
                    {
                        var $user = '-';
                    }
                    else
                    {
                        var $user = data.name;
                    }
                    return $user;
                }
            },
            {
                "data":"user",
                render:function(data, type, full, meta)
                {
                    if(data==null)
                    {
                        var $user = '-';
                    }
                    else
                    {
                        var $user = data.email;
                    }
                    return $user;
                }
            },
            {
                "data": "primary_opening_balance"
            },
            {
                "data": "primary_closing_balance"
            },
            {
                "data": "payout_opening_balance"
            },
            {
                "data": "payout_closing_balance"
            },
            {
                "data": "van_in"
            },
            {
                "data": "van_out"
            }, 
            {
                "data": "upi_in"
            },
            {
                "data": "upi_collect_in"
            },
            {
                "data": "order_processed_count"
            },
            {
                "data": "order_processed_amount"
            },
            {
                "data": "total_tax"
            },
            {
                "data": "total_fee"
            },
            {
                "data": "order_failed_count"
            },
            {
                "data": "order_failed_amount"
            },
            {
                "data": "order_processing_count"
            },
            {
                "data": "order_processing_amount"
            },
            {
                "data": "record_date"
            },
            {
                "data": "new_created_at",
            }
        ];
        datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width",$(".table-responsive").width());
    });
    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        //getRecords(from,to);
        return false;
    });

function datatableSetup(urls, datas, onDraw=function () {}, ele="#datatable", element={}) {
    var options = {
        processing: true,
        serverSide: true,
        ordering: true,
        "searching": true,
        "scrollX": true,
        
        buttons: [
            'excel'
        ],
        order: [],
            columnDefs: [ {
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
        $('#datatable').dataTable().api().ajax.reload();
        //getRecords('','');
    });

function getRecords(from,to)
{
    $.ajax({
        url:"{{custom_secure_url('admin/reports/daybook')}}",
        type:'POST',
        data:{_token: $('meta[name="csrf-token"]').attr('content'),from:from,to:to,user_id:$('#searchForm').find('[name="user_id"]').val()},
        success:function(response)
        {
            var data = JSON.parse(response);
            console.log(data);
            console.log(data.totalAmount);
            $('#totalCount').html(data.totalCount);
            if(data.totalAmount!=null)
                $('#totalAmount').html('₹'+data.totalAmount);
            else
                $('#totalAmount').html('₹0');
        }
    })
}
</script>

@endsection
