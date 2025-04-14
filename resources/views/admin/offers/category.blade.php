@extends('layouts.admin.app')
@section('title',ucfirst($page_title))
@section('content')
@section('style')
<link href="{{url('public/css/buttons.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
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

    @media screen and (min-width: 767px) {
        #datatable_length {
            margin-top: 0;
        }
    }

    .content-box {
        padding: 8px !important;
    }

    .element-box {
        padding: 1.5rem 0.8rem !important;
    }
</style>
<!--begin::Table-->
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="form-header">
                        {{$page_title}}
                    </h5>
                </div>
                <div class="col-md-4 text-right">
                    <a class="bold-label text-primary" href="#">
                        <div class="value font-1-5" id="totalCountVanApi">
                            <button class="btn btn-success" data-target="#addMessageModal" data-toggle="modal">Add Category</button>
                        </div>
                    </a>
                </div>
            </div>
            <div class="element-box">
               <div class="table-responsive custom-table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Logo</th>
                                <th>Status</th>
                                <th>Created At</th>
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
<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addMessageModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Add Category
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="add-message-form" action="{{url('admin/offer/add-category')}}" data-DataTables="datatable" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 mb-1">
                                <label class="col-form-label" for="account-old-password">Title</label>
                                   <input class="form-control" type="text" name="title">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 mb-1">
                                <label class="col-form-label" for="account-old-password">Logo</label>
                                   <input class="form-control" type="file" name="logo" accept="image/*">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 mb-1">
                                <label class="col-form-label" for="account-old-password">Description</label>
                                   <textarea class="form-control" name="description"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 mb-1">
                                <label class="col-form-label" for="account-old-password">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 mb-1">

                                <input type="hidden" name="id">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="add-message-form"]' value="Submit" />
                    </div>
                </form>
            </div>
        </div>
    </div>
<div aria-hidden="false" aria-labelledby="exampleModalLabel" class="modal fade" id="viewMessageModal" role="dialog" >

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
               
                    <div class="modal-body">
                        <div class="view_data">
                        </div>
                        

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        
                    </div>

            </div>
        </div>
    </div>
@section('scripts')
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {

        // let currDate = new Date().toJSON().slice(0,10);
        let currDate = `{{date('Y-m-d', strtotime('-1 day'))}}`;
        $('#toDate').attr('max', currDate);
        $('#fromDate').attr('max', currDate);


        var url = "{{custom_secure_url('admin/fetch')}}/category_list/0";
        var onDraw = function() {};
        var options = [ {
                "data": "title"
            },
            
            {
                "data":"description",
                render:function(data,type,full)
                {
                    return '<button class="btn btn-primary btn-sm view-data" data-des="'+data+'">Show</button>';
                }
            },
            {
                "data":"logo",
                render:function(data,type,full)
                {
                    if(data != null && data !='')
                    {
                        return '<a href="'+data+'" target="_blank" class="btn btn-primary btn-sm">Show</a>';
                    }
                    
                    return '';
                }
            },
            {
                "data":"status",
                render:function(data,type,full)
                {
                    
                    if(data == '1')
                    {
                        return showSpan('active');
                    }else
                    {
                        return showSpan('inactive');
                    }
                }
            },
            {
                "data": "new_created_at"
            },
            {
                "data": "id",
                "orderable": false,
                render: function(data, type, full, meta) {
                    
                        return '<span class="edit btn btn-primary btn-sm"><a href="javascript:void(0)"  data-id="'+full.id+'" class="edit-offer"  tooltip="Edit Category"  style="color:white;text-decoration:none" ><i class="os-icon os-icon-edit"></i></a></span>';
                    
                }
            }
        ];
        datatableSetup(url, options, onDraw);
    });

    $('#datatable').on('click','.edit-offer',function(){

        var id = $(this).data('id');
        var csrf = $('meta[name="csrf-token"]').attr('content')
        $.ajax({
            url:"{{url('admin/offer/getOfferCategory/')}}/"+id,
            type:"get",
            data:{_token:csrf,id:id},
            success:function(resp){

                if(resp.status == 'SUCCESS')
                {
                    $('#addMessageModal input[name=title]').val(resp.data.title);
                    $('#addMessageModal textarea[name=description]').val(resp.data.description);
                    $('#addMessageModal select[name=status] option[value="'+resp.data.status+'"]').prop("selected", true);
                    $('#addMessageModal input[name=id]').val(resp.data.id);
                    $('#addMessageModal').modal('show');
                }
            }
        })
    });
</script>
<script type="text/javascript">
    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000],
                [10, 25, 50, 75, 100, 200, 500, 1000]
            ],
            dom: "Bfrltip",
            buttons: [
                
            ],
            order: [],
            columnDefs: [{
                'targets': [0],
                /* column index [0,1,2,3]*/
                'orderable': false,
                /* true or false */
            }],
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
                    d.userIds = $('#searchForm').find('[name="userId"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.reports = $('#searchForm').find('[name="reports"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                },
                beforeSend: function() {},
                complete: function(data) {
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
        $('form#searchFormData')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });
    
    $('#datatable').on('click','.view-data',function(){
        var des = $(this).attr('data-des');
        var modal=$('#viewMessageModal');
        modal.find('.modal-body').html(des);
        modal.modal('show');
    })
</script>
@endsection
@endsection