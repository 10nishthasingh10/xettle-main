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
</style>
<div class="content-w">
                <div class="content-box">
                <div class="element-wrapper">

                    <div class="element-box">
                    <h5 class="form-header">
                    {{$page_title}}
                    </h5>
                    <div class="element-actions">
                  <!--  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#kt_modal_create_contact">
                        Add Contact
                    </button> -->
                                </div>
                    <div class="form-desc">
                    &nbsp;
                    </div>
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
                                    <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Account Type:</label>
                                <select class="form-control"  name="account_type">
                                <option value="">Select Account Type</option>
                                <option value="bank_account">Bank Account</option>
                                <option value="vpa">VPA</option>
                                </select>
                            </div>
                        </div>
                           <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Contact Type:</label>
                                <select class="form-control"  name="contact_type">
                                <option value="">Select Contact Type</option>
                                <option value="vendor">Vendor</option>
                                <option value="customer">Customer</option>
                                <option value="employee">Employee</option>
                                <option value="self">Self</option>
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
                        
                            </div>
                        </div>
                    </fieldset>
                    </form>
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                            <th></th>
                                <th>Contact Id</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
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
            <td><b>Contact Id :</b></td><td>@{{contact_id}}</td><td><b>Full name :</b></td><td>@{{first_name}} @{{last_name}}</td><td><b>Email :</b></td><td>@{{email}}</td>
        </tr>
		<tr><td><b>Phone :</b></td><td> @{{phone}}</td><td><b>Type :</td><td>@{{type}}</td><td><b>Account Type:</td><td>@{{account_type}}</td>
        </tr>
		<tr><td><b>Account Number :</b></td><td> @{{account_number}}</td><td><b>Account Ifsc :</b></td><td>@{{account_ifsc}}</td><td><b>Vpa Address :</b></td><td>@{{vpa_address}}</td>
        </tr>
		<tr><td><b>Card Number :</b></td><td> @{{card_number}}</td><td><b>Address1 :</b></td><td>@{{address1}}</td><td><b>Address2 :</b></td><td>@{{address2}}</td>
        </tr>
		<tr><td><b>City :</b></td><td> @{{city}}</td><td><b>State :</td><td>@{{state}}</td><td><b>Pin Code :</b></td><td>@{{pincode}}</td>
        </tr>
		<tr><td><b>Reference :</b></td><td> @{{reference}}</td><td><b>Notes :</b></td><td>@{{notes}}</td><td><b>Created :</b></td><td>@{{new_created_at}}</td>
        </tr>
		<tr></tr>
        </table>
</script>
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
        var url = "{{custom_secure_url('admin/fetch')}}/contacts/0";
        var onDraw = function() {
        };
        var options = [
            {
                "className":      'details-control',
                "orderable":      false,
                "defaultContent": ''
            },
            { "data" : "contact_id"},
            { "data" : "first_name",
                render:function(data, type, full, meta){

                    var $actionBtn = full.first_name+' '+full.last_name;
                    return $actionBtn;
                }},
            { "data" : "email"},
            { "data" : "phone"
            },
            { "data" : "is_active",
                render:function(data, type, full, meta){
                    if(data == '1') {
                        var $actionBtn = showSpan("active");
                    }else {
                        var $actionBtn = showSpan("inActive");;
                    }
                    return $actionBtn;
                }
            },
            { "data" : "new_created_at",
            },
            { "data" : null,  "orderable":      false,
                render:function(data, type, full, meta){
                    var $viewOrder ='/admin/orders?contact_id='+full.contact_id;
                    var  $actionBtn = '<a href="'+$viewOrder+'" target="_blank" title="View Orders" class="edit btn btn-primary btn-sm" ><i class="os-icon os-icon-eye"></i></a>';
                    return $actionBtn;
                }
            }
        ];
        datatableSetup(url, options, onDraw);
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
                    d.account_type = $('#searchForm').find('[name="account_type"]').val();
                    d.contact_type = $('#searchForm').find('[name="contact_type"]').val();
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
$('form#searchForm').submit(function(){
                $('#searchForm').find('button:submit').button('loading');
                var from =  $(this).find('input[name="from"]').val();
                var to =  $(this).find('input[name="to"]').val();
                
                $('#datatable').dataTable().api().ajax.reload();
                return false;
    });
$('#formReset').click(function () {
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });

</script>

@endsection
