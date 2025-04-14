@extends('layouts.user.app')
@section('title','AEPS Merchants')
@section('content')
<style type="text/css">
.expandtable{
    width: 100% !important;
    margin-bottom: 1rem;
}
.expandtable,tbody,tr,td{

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
<div class="content-w">
                <div class="content-box">
                <div class="element-wrapper">

                    <div class="element-box">
                    <h5 class="form-header">
                    {{$page_title}}
                    </h5>
                  
                    <form  id="searchForm">

                        <fieldset class="form-group">

                                        <div class="row">
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                            <label >Any Key <span class="requiredstar"></span></label>
                                            <input type="text" name="searchText" class="form-control"
                                            placeholder="Enter Search Key"  />
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                            <label for="">From Date  <span class="requiredstar"></span></label>
                                            <input type="date" name="from" class="form-control"  @if(isset($_GET['from'])) value="{{$_GET['from']}}"  @endif />
                                        </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                            <label for="">To Date  <span class="requiredstar"></span></label>
                                            <input type="date" name="to" class="form-control"  @if(isset($_GET['to'])) value="{{$_GET['to']}}"  @endif  />
                                        </div>
                                        </div>
                                        
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Txn Type:</label>
                                    <select class="form-control js-example-basic-multiple" multiple="multiple"   name="transaction_type">
                                      <option value="CW">CW</option>
                                      <option value="MS">MS</option>
                                      <option value="BE">BE</option>
                                      <option value="AP">AP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Route Type:</label>
                                    <select class="form-control js-example-basic-multiple" multiple="multiple" name="route_type">
                                      <option value="icici">ICICI</option>
                                      <option value="airtel">AIRTEL</option>
                                      <option value="sbm">SBM</option>
                                      <option value="paytm">PAYTM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Status:</label>
                                    <select class="form-control js-example-basic-multiple" multiple="multiple"   name="status">
                                      <option value="success">Success</option>
                                      <option value="pending">Pending</option>
                                      <option value="failed">Failed</option>
                                      <option value="disputed">Disputed</option>
                                    </select>
                                </div>
                            </div>
                                        <div class="col-sm-2">
                                            <div class="form-group" style="margin-top:26px;">
                                            <button type="submit" class="btn btn-primary" data-loading-text="<b>
                                                <i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i>
                                            </b> Search</button>
                                            <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b>
                                                <i class='fa fa-spin fa-spinner'></i></b> Reset"><b><i class="icon-rotate-ccw3"></i></b> Reset</button>
                                        </div>
                                        <input type="hidden" name="queryString" id="queryString" value="{{@$_GET['bank_ref']}}" />
                                        </div>
                                    </div>
                                </fieldset>
                        </form>
                 <div class="table-responsive" id="table_responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                            <th></th>
                            <th>User Name</th>
                                <th>Merchant Code</th>
                                <th>Bank Name</th>
                                <th>Client Ref Id</th>
                                <th> Route | Transaction Type</th>
                                <th>Bank Message</th>
                              
                                <th>RRN</th>
                                <th>Txn Amount</th>
                                <th>Commission</th>
                                <th>Comm Ref Id</th>
                                <th>Status</th>
                                <th>Created</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        </table>
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
            <td><b>Aadhaar No :</b></td><td>@{{mask aadhaar_no}}</td><td><b>Mobile No :</b></td><td>@{{mobile_no}}</td><td><b>Transaction Date :</b></td><td>@{{transaction_date}}</td>
            <td><b> Txn Settlement RefId :</b></td><td>@{{trn_ref_id}}</td>
        </tr>
        <tr><td><b>Failed Message :</b></td><td>@{{failed_message}}</td></tr>
        </table>
</script>
<script>
$(document).ready(function(){
    var template = Handlebars.compile($("#details-template").html());

    Handlebars.registerHelper('mask', function (aString) {
        var string  = aString.toString();
        return 'XXXXXXXX'+string.substr(string.length - 4);
    });
    Handlebars.registerHelper('mobileMask', function (aString) {
        var string  = aString.toString();
        return 'XXXXXX'+string.substr(string.length - 4);
    });
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
    $('.js-example-basic-multiple').select2();
        var url = "{{custom_secure_url('aeps/fetch')}}/aepsTransaction/0";
        var onDraw = function() {
        };
        var options = [
            {
                "className":      'details-control',
                "orderable":      false,
                "defaultContent": ''
            },
            { "data" : "first_name" , render: function(data, type, full, meta) {
                    if (full.merchant != null) {
                        var fname = full.merchant.first_name;
                        var lname = '';
                            if (full.merchant.last_name != null) {
                                lname = full.merchant.last_name;
                            }
                            return fname+' '+lname;
                    }
                    return '';
                }
            },
            { "data" : "merchant_code"},
            { "data" : "bankiin" , render: function(data, type, full, meta) {
                    if (full.bank != null) {
                        var name = full.bank.bank;
                        return name;
                    } else {
                        return '';
                    }
                }
            },
            { "data" : "client_ref_id"},
            {
                "data": "transaction_type",
                render: function(data, type, full, meta) {
                    if (data != null) {
                        return full.route_type.toUpperCase() + ' | ' + data.toUpperCase();
                    } else {
                        return '';
                    }
                }
            },
            { "data" : "resp_bank_message"},
            { "data" : "rrn"},
            { "data" : "transaction_amount"},
            { "data" : "status" , render: function(data, type, full, meta) {
                    let comm = 0;
                    if (data == 'success' || data == 'disputed') {
                        comm = full.commission;
                    }
                    return parseFloat(comm).toFixed(2);
                }
            },
            { "data" : "commission_ref_id"},
            { "data" : "status", render: function(data, type, full, meta)
            {
                return showSpan(data);
            }},
            { "data" : "new_created_at",
            }
        ];
        datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width",$("#table_responsive").width());
    });

    $('form#searchForm').submit(function(){
                $('#searchForm').find('button:submit').button('loading');
                var from =  $(this).find('input[name="from"]').val();
                var to =  $(this).find('input[name="to"]').val();
                
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
            'excel', 'pdf'
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
                    d.apes_status_array= $('#searchForm').find('[name="status"]').val();
                    d.transaction_type_array = $('#searchForm').find('[name="transaction_type"]').val();
                    d.route_type_array = $('#searchForm').find('[name="route_type"]').val();
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
    DT.search($('#queryString').val()).draw();
    return DT;
}
$('#formReset').click(function () {
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });

</script>

@endsection
