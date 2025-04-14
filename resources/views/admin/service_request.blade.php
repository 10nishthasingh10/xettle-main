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
                                </div>
                    <div class="form-desc">
                    &nbsp;
                    </div>
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                            <th></th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Service Name</th>
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
                    var $viewOrder ='/payout/orders?contact_id='+full.contact_id;
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

@endsection
