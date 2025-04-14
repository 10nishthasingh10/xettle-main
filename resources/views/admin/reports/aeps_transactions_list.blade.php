@extends('layouts.admin.app')
@section('title', $site_title)
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
                <div class="element-actions">
                </div>
                <div class="form-desc">
                    &nbsp;
                </div>
                <form id="datatableFilterForm">

                    <fieldset class="form-group">

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" />
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user">Filter by User</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        @foreach($userList as $user)
                                        <option value="{{$user->id}}">{{$user->name}} - {{$user->email}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user">Tr Type</label>
                                    <select class="form-control select2" name="tr_type">
                                        <option value="">-- Select Tr Type --</option>
                                        <option value="cw">CW</option>
                                        <option value="ms">MS</option>
                                        <option value="be">BE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user">Route Type</label>
                                    <select class="form-control select2" name="route_type">
                                        <option value="">-- Select Route Type --</option>
                                        <option value="sbm">SBM</option>
                                        <option value="airtel">AIRTEL</option>
                                        <option value="icici">ICICI</option>
                                        <option value="paytm">PAYTM</option>
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

                            </div>
                            <!-- <input type="hidden" name="queryString" id="queryString" @if(isset($_GET['bank_ref'])) value="{{$_GET['bank_ref']}}" @endif /> -->
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive" style="overflow-x: auto">
                    <table class="table table-bordered table-striped table-hover" width="100%" id="datatable">
                        <thead>
                            <tr>
                              
                                <th>API Partner</th>
                                <th>Merchant details</th>
                                <th>Tr Type</th>
                                <th> Aadhaar</th>
                                <th> Aadhaar Count</th>
                                <th>Total Amount</th>
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


@endsection
@section('scripts')

<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
<script src="{{url('public/js/handlebars.js')}}"></script>
<script id="details-template" type="text/x-handlebars-template">

<table class="expandtable">
<tr><td><b>Shop Name</b> </td>  <td><b>Shop Address</b> </td> <td><b>Shop Pin</b> </td><td><b>State</b> </td><td><b>District</b></td></tr>

<tr> <td>@{{shop_name}} </td> <td>@{{shop_address}} </td> <td>@{{shop_pin}} </td><td>@{{state_name}} </td><td>@{{district_title}} </td> </tr>


</table>
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var template = Handlebars.compile($("#details-template").html());

        // Add event listener for opening and closing details
        $('#datatable tbody').on('click', 'td.details-control', function() {
            var tr = $(this).closest('tr');
            var table = $("#datatable").DataTable();
            var row = table.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(template(row.data())).show();
                tr.addClass('shown');
            }
        });
        
        var url = "{{custom_secure_url('admin/aeps-transactions')}}";
        // var onDraw = function() {};
        var options = [
            {
                "data": "name",
                render: function(data, type, full, meta) {
                    var fname = full.name;
                    if (full.email != null && full.email != '') {
                        fname += ' <br>'+full.email;
                    }
                    return fname;
                }
            },
            {
                "data": "first_name",
                render: function(data, type, full, meta) {
                    var fname = full.first_name;
                    if (full.last_name != null && full.last_name != '') {
                        fname += '  '+full.last_name;
                    }
                    return fname+'  <br/>'+full.merchant_code+'  <br/>'+full.mobile+'  <br/>'+full.district_title;
                }
            },
            {
                "data": "tr_type",
                render: function(data, type, full, meta) {
                    return data.toUpperCase();
                }
            },
            {
                "data": "aadhaar_no",
                render: function(data, type, full, meta) {
                    return data;
                }
            },
            {
                "data": "aadhaar_no_count",
                render: function(data, type, full, meta) {
                    return data;
                }
            },
            {
                "data": 'totalAmount',
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    return showSpan(data);
                }
            },
            {
                "data": "created_at"
            },{
                "data": "aadhaar_no",
                render: function(data, type, full, meta) {
                    return `<a target="_blank" href="{{url('admin/aeps-transactions-details')}}/${data}" class="btn btn-sm btn-primary" ><i class="os-icon os-icon-eye-circle"></i> View</a>`;
                    }
            }
        ];
        datatableSetup(url, options);
        $('.dataTables_wrapper').css("width",$(".table-responsive").width());
    });

    $('form#datatableFilterForm').on('submit', function() {
        $('#datatableFilterForm').find('button:submit').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
        return false;
    });



    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            searching: true,
            ordering: true,
            buttons: [
                'excel'
            ],
            order: [[4, 'desc']],
            columnDefs: [{
                'targets': [0],
                /* column index [0,1,2,3]*/
                'orderable': false,
                /* true or false */
            }],
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000, -1],
                [10, 25, 50, 75, 100, 200, 500, 1000, 1500]
            ],
            dom: "Bfrltip",
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
                    d.from = $('#datatableFilterForm').find('[name="from"]').val();
                    d.to = $('#datatableFilterForm').find('[name="to"]').val();
                    d.user_id = $('#datatableFilterForm').find('[name="user_id"]').val();
                    d.tr_type = $('#datatableFilterForm').find('[name="tr_type"]').val();
                    d.route_type = $('#datatableFilterForm').find('[name="route_type"]').val();
                    d.searchText = $('#datatableFilterForm').find('[name="searchText"]').val();
                },
                beforeSend: function() {},
                complete: function() {
                    $('#datatableFilterForm').find('button:submit').button('reset');
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
        // DT.search($('#queryString').val()).draw();
        return DT;
    }
    $('#formReset').click(function() {
        $('form#datatableFilterForm')[0].reset();
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });

</script>

@endsection