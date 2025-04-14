@extends('layouts.admin.app')
@section('title',ucfirst($page_title))
@section('style')
<link href="{{url('public/css/buttons.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
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
        padding: 0.5rem !important;
    }

    .element-box {
        padding: 0.5rem !important;
    }

    @media screen and (min-width: 767px) {
        #datatable_length {
            margin-top: 0;
        }
    }
</style>
<!--begin::Table-->
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper">
            <div class="element-box">
                <h5 class="form-header">
                    {{$page_title}}
                </h5>
                <div class="element-actions" style="margin-top: -2.2rem;">
                    <button class="btn btn-success" data-target="#addIntegrationModal" data-toggle="modal">Add Integration</button>
                </div>
                <div class="form-desc">
                    &nbsp;
                </div>
                <form id="searchForm">
                    <fieldset class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Any Key <span class="requiredstar"></span></label>
                                    <input type="text" name="searchText" class="form-control" placeholder="Enter Search Key" />
                                    <input type="hidden" name="tr_type" value="dr" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" id="fromDate" @if(isset($_GET['from'])) value="{{$_GET['from']}}"  @endif />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" id="toDate" @if(isset($_GET['to'])) value="{{$_GET['to']}}"  @endif />
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
                <div class="table-responsive custom-table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th>#Id</th>
                                <th>Integration Id</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Date</th>
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

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addIntegrationModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Add Integration
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="add-integration-form" action="{{url('admin/integration/add-integration')}}" data-DataTables="datatable" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6 col-sm-12 mb-1">
                                <label class="col-form-label" for="account-old-password">Name</label>
                                   <input class="form-control" type="text" name="name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-sm-12 mb-1">
                                <label class="col-form-label" for="account-old-password">Slug</label>
                                   <input class="form-control" type="text" name="slug">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="add-integration-form"]' value="Submit" />
                    </div>
                </form>
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

<script>
    $(document).ready(function() {
        var url = "{{custom_secure_url('admin/fetch')}}/Integration/0";
        var onDraw = function() {};
        var options = [ {
            "orderable": false,
            "searchable": false,
            "defaultContent": '',
            "data": 'count',
            render: function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
            },
            {
                "data": "integration_id",
            },
            {
                "data": "name",
            },
            {
                "data": "slug"
            },
            // { "data" : "is_active",
            //     render:function(data, type, full, meta){
            //         if(data == '1') {
            //             var $actionBtn = showSpan("active");
            //         }else {
            //             var $actionBtn = showSpan("inActive");;
            //         }
            //         return $actionBtn;
            //     }
            // },
            {
                "data": "new_created_at"
            },
            {
            "data": "is_active",
            render: function(data, type, full) {
                const checked = data === '1' ? 'checked' : '';
                return `<label class="switch serviceActivation" data-id="${full.id}"><input type="checkbox" ${checked}><span class="slider round"></span></label>`;
            }
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
        return false;
    });
</script>
<script type="text/javascript">
    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            scrollX: true,
            "searching": true,
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000],
                [10, 25, 50, 75, 100, 200, 500, 1000]
            ],
            dom: "Bfrltip",
            buttons: [
                'excel', 'pdf'
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
                    d.userId = $('#searchForm').find('[name="userId"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                    d.tr_identifiers = $('#searchForm').find('[name="tr_identifiers"]').val();
                    d.service_id_array = $('#searchForm').find('[id="services"]').val();
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
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
        $('#services').on('change', function() {
            $('#trIdentifiers').html('');
            var service = $(this).val();
            $.post('/getIndentifiers',{"service" : service, "_token" : "{{csrf_token()}}"},function(data, status){
                $('#trIdentifiers').html(data);
            });
        });
    });


$('#datatable').on('click','#orderForm',function(){

var id = $(this).data('id');
var csrf = $('meta[name="csrf-token"]').attr('content')
$.ajax({
    url:"{{url('admin/integration/getIntegrationdata/')}}/"+id,
    type:"get",
    data:{_token:csrf,id:id},
    success:function(resp){

        if(resp.status == 'SUCCESS')
        {
            $('#addIntegrationModal input[name=name]').val(resp.data.name);
            $('#addIntegrationModal input[name=slug]').val(resp.data.slug);
            // $('#addIntegrationModal input[name=integration_id]').val(resp.data.integration_id);
            $('#addIntegrationModal input[name=id]').val(resp.data.id);
            $('#addIntegrationModal').modal('show');
        }
    }
});
});

var serviceActivationAjax = false;
$('#datatable').on('click', 'label.serviceActivation', function () {
    if (serviceActivationAjax) {
        return; 
    }
    serviceActivationAjax = true;
    var button = $(this);
    var id = button.closest('.switch').data('id');
    var isActive = button.find('input[type="checkbox"]').prop('checked') ? 1 : 0;
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: "{{ route('integration.updateServiceActivation', ['id' => ':id']) }}".replace(':id', id),
        type: 'POST',
        data: {
            isActive: isActive,
            _token: csrf
        },
        success: function(response) {
            Swal.fire({
                title: response.title,
                text: response.message,
                icon: "success",
                buttonsStyling: !1,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            }).then((result) => { });

            serviceActivationAjax = false;
        },
        error: function(xhr, status, error) {
            console.error(error);
            serviceActivationAjax = false;
        }
    });
});


</script>
@endsection
@endsection