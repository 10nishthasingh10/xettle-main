/**
 * Version: 1.0.0
 * File: user-profile.js?v=1.0.0
 */
"use strict"

$(function () {
    var options = [{
        //"className": 'details-control',
        "orderable": false,
        "searchable": false,
        "defaultContent": '',
        "data": 'count',
        render: function (data, type, full, meta) {
            let start = parseInt(meta.settings.json.start);
            return meta.row + (start + 1);
        }
    },
    {
        "data": "beneficiary_name"
    },
    {
        "data": "account_number",
        render: function (data, type, full, meta) {

            return `${data} <br>${full.ifsc}`;
        }
    },
    {
        "data": "is_primary",
        render: function (data, type, full, meta) {

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
        render: function (data, type, full, meta) {
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
        render: function (data, type, full, meta) {
            let actionBtn = `<button class='btn btn-sm btn-danger verify-acc-btn' data-accid="${full.id}">Verify</button>`;
            if (data == '1') {
                actionBtn = showSpan("Verified");
            }
            return `<div class="text-center">${actionBtn}</div>`;
        }
    },
    {
        "data": null,
        "orderable": false,
        render: function (data, type, full, meta) {
            let $actionBtn = '';
            if (full.is_verified !== '1') {
                $actionBtn += `<button value="${full.id}" title="Update Bank" class="edit btn btn-primary btn-sm updateBankInfo"><i class="fal fa-edit"></i></button>`;
            }

            if (full.is_verified !== '1' && full.is_primary !== '1') {
                $actionBtn += `<button data-bizzid="${full.id}" title="Delete Bank" class="edit btn btn-danger btn-sm delete-info"><i class="fal fa-trash-alt"></i></button>`;
            }
            return `<span class='inline-flex text-center w-100'>${$actionBtn}</span>`;
        }
    }
    ];
    datatableSetup($("#bankUrl").val(), options, function () { }, "#table-user_bank");

    $('#table-user_bank').on('click', '.verify-acc-btn', function () {
        let vId = $(this).attr('data-accid');
        $('#user_bank_id').val(vId);
        $('#user_bank_id_final').val(vId);
        $("#verifyBankAccountModal").modal("show");
        $("#verifyBankAccountModal").on('hidden.bs.modal', function () {
            $('#verify-bank-form').removeClass('d-none');
            $('#response-bank-verify').addClass('d-none');
        });
    });

    $('#table-user_bank').on('change', '.change-account-status', function () {
        let vId = $(this).attr('data-statusid');

        if (!isAjaxChangeStatus) {
            isAjaxChangeStatus = true;
            $.ajax({
                url: `${$('meta[name="base-url"]').attr('content')}/admin/users-bank/update-bank-status`,
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
    $('#table-user_bank').on('change', '.change-primary-status', function () {
        let vId = $(this).attr('data-primaryid');
        let uId = $(this).attr('data-id');

        if (!isAjaxchangePrimaryStatus) {
            isAjaxchangePrimaryStatus = true;
            $.ajax({
                url: `${$('meta[name="base-url"]').attr('content')}/admin/users-bank/update-primary-status`,
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
                            $("#table-user_bank").DataTable().ajax.reload(null, false);
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

    $('#table-user_bank').on('click', '.delete-info', function () {
        let bizId = $(this).attr('data-bizzid');
        $('#bank_id').val(bizId);
        $("#deleteBankInfoModal").modal();
    });

    $('#delete-bank-info-btn').on('click', function () {
        setTimeout(function () {
            $("#deleteBankInfoModal").modal('hide');
        }, 2000);
    });

    $('#table-user_bank').on('click', '.updateBankInfo', function () {
        let vId = $(this).val();

        if (!isAjaxchangePrimaryStatus) {
            isAjaxchangePrimaryStatus = true;
            $.ajax({
                url: `${$('meta[name="base-url"]').attr('content')}/admin/users-bank/get-bank`,
                type: 'post',
                data: {
                    rowId: vId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (res) => {

                    if (res.code === '0x0200') {

                        $('#row_id').val(vId);
                        $('#update_frm_user_id').val(res.data.user_id);
                        $('#beneficiary_name').val(res.data.beneficiary_name);
                        $('#account_number').val(res.data.account_number);
                        $('#ifsc').val(res.data.ifsc);
                        $('#bank').val(res.data.bank);

                        $('#updateBankAccountModal').modal('show');

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
        }).then(function () {
            $('#verifyBankAccountModal').modal('hide');
        });
    }
}

function updateBankAccountCb(response) {
    if (response.status_code === 200) {
        $('#row_id').val('');
        $('#update_frm_user_id').val('');
        $('#beneficiary_name').val('');
        $('#account_number').val('');
        $('#ifsc').val('');

        $('#updateBankAccountModal').modal('hide');
    }
}

function newBankAccountCb(response) {
    if (response.status_code === "200") {
        $('#new_beneficiary_name').val('');
        $('#new_account_number').val('');
        $('#new_ifsc').val('');

        $('#addBankAccountModal').modal('hide');
    }
}