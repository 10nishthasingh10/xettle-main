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

    .expandtable b,
    #utr-response-data tbody td b {
        color: #047bf8;
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

                <div id="form-container"></div>

            </div>


            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="p-2 h6">User Info</div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Business Name</th>
                                        <th>Bank Account</th>
                                        <th>Bank IFSC</th>
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

    </div>
</div>

@endsection
@section('scripts')

<script src="{{url('public/js/handlebars.js')}}"></script>
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
<script id="details-template" type="text/x-handlebars-template">
    <table class="expandtable">
        <tr>
            <td><b>VAN 1 :</b> @{{van_1}}</td>
            <td><b>VAN 1 IFSC :</b> @{{van_1_ifsc}}</td>
            <td><b>VAN 2 :</b> @{{van_2}}</td>
            <td><b>VAN 2 IFSC :</b> @{{van_2_ifsc}}</td>
        </tr>
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

        $('#datatable').on('click', '.update-info', function() {
            console.log($(this).attr('data-bizzid'));
            let bizId = $(this).attr('data-bizzid');
            $('#form-container').html('');
            $(this).attr('disabled', true);
            $(this).html('<i class="fas fa-spinner"></i>');

            $.ajax({
                url: `{{url('')}}/admin/smart-collect-van/get-info/${bizId}`,
                success: (result) => {
                    if (result.code === '0x0200') {
                        setForm(result.data);
                    } else {
                        alert('Invalid ID');
                    }

                    $(this).html('<i class="os-icon os-icon-edit"></i>');
                    $(this).removeAttr('disabled');
                }
            });

        });
    });

    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });

        var url = "{{custom_secure_url('admin/smart-collect-van/report/edit-info')}}";
        var onDraw = function() {};
        var options = [{
                "className": 'details-control',
                "orderable": false,
                "defaultContent": ''
            },
            {
                "data": "name",
            },
            {
                "data": "email",
            },
            {
                "data": "mobile",
            },
            {
                "data": "business_name"
            },
            {
                "data": "bank_account_no"
            },
            {
                "data": "bank_ifsc"
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {
                    let $actionBtn = '';
                    $actionBtn += `<button data-bizzid="${full.id}" title="Update Info" class="edit btn btn-danger btn-sm update-info"><i class="os-icon os-icon-edit"></i></button>`;
                    return `<span class='inline-flex'>${$actionBtn}</span>`;
                }
            }
        ];
        datatableSetup(url, options, onDraw);
    });

    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        getRecords(from, to);
        return false;
    });

    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            buttons: [
                'excel'
            ],
            order: [],
            columnDefs: [{
                "defaultContent": "-",
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
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
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

    function setForm(response) {
        let res = `
        <form id="update-van-info-form" role="update-van-info-us-form" action="{{url('admin/smart-collect-van/edit-info/submit')}}" data-DataTables="datatable" method="POST">

            <fieldset class="form-group">

                <div class="row">
                    @csrf()
                    <input type="hidden" name="bizz_id" value="${response.id}">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Full Name</label>
                            <div class="form-control">${response.name}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Email</label>
                            <div class="form-control">${response.email}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Mobile</label>
                            <div class="form-control">${response.mobile}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Business Name <span class="requiredstar">*</span></label>
                            <input type="text" name="business_name" class="form-control" value="${response.business_name}" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Account Number <span class="requiredstar">*</span></label>
                            <input type="text" name="account_number" class="form-control" value="${response.bank_account_no}" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">IFSC <span class="requiredstar">*</span></label>
                            <input type="text" name="ifsc" class="form-control" value="${response.bank_ifsc}" required />
                        </div>
                    </div>

                    <div class="col-md-12 text-right">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="reset" class="btn btn-warning btn-labeled legitRipple" id="reset-btn">
                                        <b><i class="icon-rotate-ccw3"></i></b> Reset
                                    </button>

                                    <button type="submit" class="btn btn-primary w-90px" data-request="ajax-submit" data-target='[role="update-van-info-us-form"]' id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Submitting">
                                        <b><i class="icon-search4"></i></b> Update
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
        `;

        $('#form-container').html(res);

    }
</script>

@endsection