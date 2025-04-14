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
                                    <label for="user_id">Filter by User</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        @foreach($userData as $user)
                                        <option value="{{$user->id}}" @if(isset($_GET['user_id']) && $_GET['user_id'] == $user->id) selected  @endif>{{$user->userName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" id="fromDate" @if(isset($_GET['from'])) value="{{$_GET['from']}}"  @endif />
                                </div>
                            </div>
                            <div class="col-md-2">
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
                                <th>Id</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
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
        </div>
    </div>
</div>

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="resellerCommissionModal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Add Commission
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form id="commissionForm" role="reseller-commission-form" action="{{url('admin/reseller/reseller-commission')}}" data-DataTables="datatable" method="POST">
                @csrf
                <input type="hidden" name="reseller_id" id="reseller_id" value="{{ $reseller_id }}">
                <input type="hidden" name="user_id" id="user_id" value="">
                

                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 col-sm-12 mb-1">
                            <label class="col-form-label" for="account-old-password">Payin Rate</label>
                                <input class="form-control" type="number" name="payin_rate">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-sm-12 mb-1">
                            <label class="col-form-label" for="account-old-password">Payout Rate</label>
                                <input class="form-control" type="number" name="payout_rate">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-sm-12 mb-1">
                            <label class="col-form-label" for="account-old-password">Min Payin Amount</label>
                                <input class="form-control" type="number" name="minimum_payinAmount">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-sm-12 mb-1">
                            <label class="col-form-label" for="account-old-password">Min Payout Amount</label>
                            <input class="form-control" type="number" name="minimum_payoutAmount">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="reseller-commission-form"]' value="Submit" />
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
        var url = "{{ custom_secure_url('admin/reseller/user-details/' . $reseller_id) }}";
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
                "data": "name"
            },
            {
                "data": "email"
            },
            {
                "data": "mobile"
            },
            {
                "data": "is_active",
                render: function(data, type, full, meta) {
                    if(data=='1')
                    {
                        return `<span class="badge badge-success">Active</span>`;
                    }else
                    {
                        return `<span class="badge badge-danger">Inactive</span>`;
                    }
                    
                }
            },
            {
                "data": "created_date",
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {
                    return `<button title="Reseller Commission" data-id="${full.id}" data-name="${full.name}" class="edit btn btn-primary btn-sm resellerCommissionModal">Assign Commission</button>`;
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
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.user_id = $('#searchForm').find('[name="user_id"]').val();
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
        $('#datatable tbody').on('click', '.resellerCommissionModal', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#serviceId').val(id);
            $('#resellerCommissionModal').modal('show');
            
            $('#user_id').val(id);
        });
        

   
    $('#commissionForm').submit(function(event) {
        event.preventDefault(); 

        var formData = $(this).serialize();
        var url = "{{ url('admin/reseller/reseller-commission')}}";

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
});

</script>
@endsection
@endsection
