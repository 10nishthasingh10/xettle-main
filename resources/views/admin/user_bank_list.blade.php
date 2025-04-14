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
    #response-bank-verify tbody td b {
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

                @if(!empty($userBanks))
                <div class="element-content">
                    <div class="row">
                        <div class="col-md-12">
                            <form role="add-bank-info-form" action="{{url('admin/users-bank/update-banks-info')}}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Select User <span class="requiredstar">*</span></label>
                                            <select class="form-control service_id select2" name="user_id" required>
                                                <option value="">--Select--</option>
                                                @foreach($userList as $row)
                                                <option value="{{encrypt($row->id)}}" {{($userId === $row->id)?'selected':''}}>{{$row->id}} - {{$row->name}} - {{$row->email}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div id="rule_row_contailer">

                                    <!-- foreach($userBanks as $i => $row) -->

                                    <input type="hidden" name="row_id" value="{{encrypt($userBanks->id)}}">
                                    <div class="row first">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Beneficiary Name <span class="requiredstar">*</span></label>
                                                <input type="text" name="beneficiary_name" class="form-control" value="{{$userBanks->beneficiary_name}}" placeholder="Enter beneficiary name" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Account Number <span class="requiredstar">*</span></label>
                                                <input type="text" name="account_number" class="form-control" value="{{$userBanks->account_number}}" placeholder="Enter account number" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">IFSC <span class="requiredstar">*</span></label>
                                                <input type="text" name="ifsc" class="form-control" value="{{$userBanks->ifsc}}" placeholder="Enter IFSC" required>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- endforeach -->

                                </div>

                                <!-- <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button type="button" class="btn btn-sm btn-dark" id="add_new_row"><i class="os-icon os-icon-plus"></i> Add Row</button>
                                    </div>
                                </div> -->

                                <div class="row">
                                    <div class="col-md-12 text-right">
                                        <a href="{{url('admin/users-bank')}}" class="btn btn-warning">Cancel</a>
                                        &nbsp;
                                        <button class="btn btn-primary" type="submit" data-request="ajax-submit" data-callbackfn="userBankUpdateCallback" data-target='[role="add-bank-info-form"]'>Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @else

                <div class="element-content">
                    <div class="row">
                        <div class="col-md-12">
                            <form role="add-bank-info-form" action="{{url('admin/users-bank/add-new-banks')}}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Select User <span class="requiredstar">*</span></label>
                                            <select class="form-control service_id select2" name="user_id" id="addBankUserId" required>
                                                <option value="">--Select--</option>
                                                @foreach($userList as $row)
                                                <option value="{{encrypt($row->id)}}">{{$row->name}} - {{$row->email}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div id="rule_row_contailer">

                                    <div class="row first">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Beneficiary Name <span class="requiredstar">*</span></label>
                                                <input type="text" name="beneficiary_name[0]" class="form-control" placeholder="Enter beneficiary name" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Account Number <span class="requiredstar">*</span></label>
                                                <input type="text" name="account_number[0]" class="form-control" placeholder="Enter account number" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">IFSC <span class="requiredstar">*</span></label>
                                                <input type="text" name="ifsc[0]" class="form-control" placeholder="Enter IFSC" required>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button type="button" class="btn btn-sm btn-dark" id="add_new_row"><i class="os-icon os-icon-plus"></i> Add Row</button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 text-right">
                                        <button class="btn btn-primary" type="submit" data-callbackfn="callbackAddBank" data-request="ajax-submit" data-target='[role="add-bank-info-form"]'>Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @endif
            </div>


            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="p-2 h6">Users Bank List</div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Beneficiary Name</th>
                                        <th>Bank Account</th>
                                        <th>Bank IFSC</th>
                                        <th>Is Primary</th>
                                        <th>Status</th>
                                        <th>Verified</th>
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

<!-- delete Scheme and User Relation -->
<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="deleteBankInfoModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Delete Bank Info
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <form role="delete-bank-info-form" action="{{url('admin/users-bank/delete/bank-info')}}" data-DataTables="datatable" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label> Are you sure to delete this Bank Info?</label>
                        <input class="form-control" type="hidden" name="bank_id" id="bank_id" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                    <button class="btn btn-danger" id="delete-bank-info-btn" type="submit" data-request="ajax-submit" data-target='[role="delete-bank-info-form"]'>Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div aria-hidden="true" aria-labelledby="verify-bank-account" class="modal fade" id="verifyBankAccountModal" role="dialog" tabindex="-1">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verify-bank-account">
                    Verify Bank Account
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 d-none" id="response-bank-verify">
                        <h6>Bank Account Details:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <tbody></tbody>
                            </table>
                        </div>

                        <form id="verify-bank-form-final" role="verify-bank-form-final" action="{{url('admin/users-bank/verify-bank-account/final')}}" data-DataTables="datatable" method="POST">
                            <fieldset class="form-group">
                                @csrf()
                                <div class="col-md-12">
                                    <div class="col-md-12 text-right">
                                        <input type="hidden" name="acc_holder_name" id="acc_holder_name">
                                        <input type="hidden" name="acc_id" id="user_bank_id_final">
                                        <input type="hidden" name="acc_token" id="acc_token_final">
                                        <button type="submit" name="action_1" value="success" class="btn btn-success" data-request="ajax-submit" data-target='[role="verify-bank-form-final"]' data-callbackfn="verifyBankAccountModal">
                                            <b><i class="icon-search4"></i></b> Verify Account
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-danger text-light d-none" id="response-bank-error"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form id="verify-bank-form" role="verify-bank-form" action="{{custom_secure_url('admin/users-bank/verify-bank-account')}}" method="POST">
                    @csrf()
                    <input class="form-control" type="hidden" name="user_bank_id" id="user_bank_id">
                    <button class="btn btn-primary" type="submit" data-callbackfn="callbackFunction" data-request="ajax-submit" data-target='[role="verify-bank-form"]'>Click to Verify</button>
                </form>
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

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });

        $('#datatable').on('click', '.update-info', function() {
            // console.log($(this).attr('data-bizzid'));
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

        $('#delete-bank-info-btn').on('click', function() {
            setTimeout(function() {
                $("#deleteBankInfoModal").modal('hide');
            }, 2000);
        });

        $('#datatable').on('click', '.delete-info', function() {
            let bizId = $(this).attr('data-bizzid');
            $('#bank_id').val(bizId);
            $("#deleteBankInfoModal").modal();
        });

        $('#add-new-bank').on('click', function() {
            showNewForm();
        });
    });

    $(document).ready(function() {
        var url = "{{custom_secure_url('admin/users-bank/report/bank-info')}}";
        var onDraw = function() {};
        var options = [{
                //"className": 'details-control',
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": 'count',
                render: function(data, type, full, meta) {
                    let start = parseInt(meta.settings.json.start);
                    return meta.row + (start + 1);
                }
            },
            {
                "data": "name",
            },
            {
                "data": "email",
            },
            {
                "data": "beneficiary_name"
            },
            {
                "data": "account_number"
            },
            {
                "data": "ifsc"
            },
            {
                "data": "is_primary",
                render: function(data, type, full, meta) {

                    if (full.is_verified === '1') {
                        let btn = `<div class="w-100 text-center"><label class="switch"><input type="checkbox" data-primaryid="${full.id}" data-id="${full.user_id}" class="change-primary-status"><span class="slider round"></span></label></div>`;
                        if (data == '1') {
                            btn = `<div class="w-100 text-center"><label class="switch"><input type="checkbox" checked disabled><span class="slider round"></span></label></div>`;
                        }
                        return btn;
                    }

                    return '';
                }
            },
            {
                "data": "is_active",
                render: function(data, type, full, meta) {
                    if (full.is_verified === '1') {
                        let btn = `<div class="w-100 text-center"><label class="switch"><input type="checkbox" data-statusid="${full.id}" class="change-account-status"><span class="slider round"></span></label></div>`;
                        if (data == '1') {
                            btn = `<div class="w-100 text-center"><label class="switch"><input type="checkbox" data-statusid="${full.id}" class="change-account-status" checked><span class="slider round"></span></label></div>`;
                        }
                        return btn;
                    }

                    return '';
                }
            },
            {
                "data": "is_verified",
                render: function(data, type, full, meta) {
                    let actionBtn = `<button class='btn btn-sm btn-danger verify-acc-btn' data-accid="${full.id}">Verify It</button>`;
                    if (data == '1') {
                        actionBtn = showSpan("Verified");
                    }
                    return `<div class="text-center">${actionBtn}</div>`;
                }
            },
            {
                "data": null,
                "orderable": false,
                render: function(data, type, full, meta) {
                    let $actionBtn = '';
                    if (full.is_verified !== '1') {
                        $actionBtn += `<a href="{{url('')}}/admin/users-bank/${full.id}" title="Update Bank" class="edit btn btn-primary btn-sm"><i class="fal fa-edit"></i></a>`;
                    }

                    if (full.is_verified !== '1' && full.is_primary !== '1') {
                        $actionBtn += `<button data-bizzid="${full.id}" title="Delete Bank" class="edit btn btn-danger btn-sm delete-info"><i class="fal fa-trash-alt"></i></button>`;
                    }
                    return `<span class='inline-flex text-center w-100'>${$actionBtn}</span>`;
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


    var rulesInputCount = 1;

    var rulesInput = function(count) {

        return `<div class="row second">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Beneficiary Name <span class="requiredstar">*</span></label>
                            <input type="text" name="beneficiary_name[${count}]" class="form-control" placeholder="Enter beneficiary name" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Account Number <span class="requiredstar">*</span></label>
                            <input type="text" name="account_number[${count}]" class="form-control" placeholder="Enter account number" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">IFSC <span class="requiredstar">*</span></label>
                            <input type="text" name="ifsc[${count}]" class="form-control" placeholder="Enter IFSC" required>
                        </div>
                    </div>

                    <div class="col-md-1 text-center">
                        <label class="w-100">&nbsp;</label>
                        <button type="button" class="btn btn-warning remove_new_row"><i class="os-icon os-icon-minus"></i></button>
                    </div>
                </div>`;
    }

    $('#add_new_row').on('click', function() {
        $('#rule_row_contailer').append(rulesInput(rulesInputCount));
        rulesInputCount++;
    });

    $('#rule_row_contailer').on('click', '.remove_new_row', function() {
        $(this).parent().parent('div.second').remove();
        rulesInputCount--;
    });

    let isAjaxChangeStatus = false;
    $('#datatable').on('change', '.change-account-status', function() {
        let vId = $(this).attr('data-statusid');

        if (!isAjaxChangeStatus) {
            isAjaxChangeStatus = true;
            $.ajax({
                url: `{{custom_secure_url('admin/users-bank/update-bank-status')}}`,
                type: 'post',
                data: {
                    vId: vId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (res) => {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            // location.replace(`profile#vanDetails`);
                            // location.reload();
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                            // confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        });


                    }

                    isAjaxChangeStatus = false;

                },
                error: () => {
                    isAjaxChangeStatus = false;
                }
            });
        }
    });


    let isAjaxchangePrimaryStatus = false;
    $('#datatable').on('change', '.change-primary-status', function() {
        let vId = $(this).attr('data-primaryid');
        let uId = $(this).attr('data-id');

        if (!isAjaxchangePrimaryStatus) {
            isAjaxchangePrimaryStatus = true;
            $.ajax({
                url: `{{custom_secure_url('admin/users-bank/update-primary-status')}}`,
                type: 'post',
                data: {
                    vId: vId,
                    uId: uId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (res) => {

                    if (res.code === '0x0200') {

                        Swal.fire({
                            title: res.data.status,
                            text: res.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                        }).then((result) => {
                            $("#datatable").DataTable().ajax.reload(null, false);
                        });

                    } else {

                        Swal.fire({
                            title: res.status,
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: false,
                        });


                    }

                    isAjaxchangePrimaryStatus = false;

                },
                error: () => {
                    isAjaxchangePrimaryStatus = false;
                }
            });
        }
    });

    $('#datatable').on('click', '.verify-acc-btn', function() {
        let vId = $(this).attr('data-accid');
        $('#user_bank_id').val(vId);
        $('#user_bank_id_final').val(vId);
        $("#verifyBankAccountModal").modal("show");
        $("#verifyBankAccountModal").on('hidden.bs.modal', function() {
            $('#verify-bank-form').removeClass('d-none');
            $('#response-bank-verify').addClass('d-none');
        });
    });

    function callbackFunction(response) {
        if (response.code === "0x0200") {

            let res = ``;
            if (response.data.isValid) {
                $('#acc_token_final').val(response.data.token);
                $('#acc_holder_name').val(response.data.accountName);
                res += `
                <tr>
                    <td><b>A/c Holder Name :</b></td>
                    <td> ${response.data.accountName}</td>
                </tr>
                <tr>
                    <td><b>Amount Number:</b></td>
                    <td> ${response.data.accountNo}</td>
                </tr>
                <tr>
                    <td><b>IFSC :</b></td>
                    <td> ${response.data.ifsc}</td>
                </tr>`;

                $('#response-bank-verify').removeClass('d-none');
                $('#response-bank-verify table tbody').html(res);
                $('#verify-bank-form').addClass('d-none');
            } else {
                Swal.fire({
                    title: response.status,
                    text: 'Invalid Bank Account Details.',
                    icon: 'warning',
                    showCancelButton: false,
                });
            }
        } else {
            Swal.fire({
                title: response.status,
                text: response.message,
                icon: 'warning',
                showCancelButton: false,
            }).then(function() {
                $('#verifyBankAccountModal').modal('hide');
            });
        }
    }

    function verifyBankAccountModal() {
        $('#verifyBankAccountModal').modal('hide');
        $('#user_bank_id').val('');
        $('#user_bank_id_final').val('');
    }

    function callbackAddBank(response) {
        let statusCode = response.status_code || '';
        if (statusCode === '200') {
            $('#addBankUserId').val("").trigger('change');
            $(`form[role="add-bank-info-form"]`).trigger('reset');
            $('#datatable').DataTable().ajax.reload();
        }
    }

    function userBankUpdateCallback(response) {
        let statusCode = response.status_code || '';
        if (statusCode === '200') {
            // $('#addBankUserId').val("").trigger('change');
            // $(`form[role="add-bank-info-form"]`).trigger('reset');
            $('#datatable').DataTable().ajax.reload();
        }
    }
</script>

@endsection