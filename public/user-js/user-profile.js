/**
 * Version: 1.0.2
 * File: user-profile.js?v=1.0.2
 */

"use strict";

var limitReachedSevices = Array();

$(function () {

    $('#regenerateSDKApiKeyBtn').on('click', function () {
        var btnText = $(this).html();
        $(this).html('Generating...');
        $(this).prop('disabled', true);

        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: $('meta[name="base-url"]').attr('content') + "/user/accounts/sdk-api-key",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        user_id: $('meta[name="user-token"]').attr('content')
                    },
                    type: 'POST',
                    success: (res) => {
                        if (res.status === true) {
                            $('#sdkKeyUpdatedAt').text(res.updatedAt);
                            $('#sdkKey').text(res.sdkKey);
                            swal.fire("SDK Key Generated", res.message, "success");
                        } else {
                            swal.fire("SDK Key Generated", res.message, "error");
                        }

                        $(this).html(btnText);
                        $(this).prop('disabled', false);
                    }
                });
            } else {
                $(this).html(btnText);
                $(this).prop('disabled', false);
            }
        });
    });

    $('#matmRegenerateSDKApiKeyBtn').on('click', function () {
        var btnText = $(this).html();
        $(this).html('Generating...');
        $(this).prop('disabled', true);

        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: $('meta[name="base-url"]').attr('content') + "/user/accounts/matm-sdk-api-key",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        user_id: $('meta[name="user-token"]').attr('content')
                    },
                    type: 'POST',
                    success: (res) => {
                        if (res.status === true) {
                            $('#matmKeyUpdatedAt').text(res.updatedAt);
                            $('#matmKey').text(res.sdkKey);
                            swal.fire("SDK Key Generated", res.message, "success");
                        } else {
                            swal.fire("SDK Key Generated", res.message, "error");
                        }

                        $(this).html(btnText);
                        $(this).prop('disabled', false);
                    }
                });
            } else {
                $(this).html(btnText);
                $(this).prop('disabled', false);
            }
        });
    });

    $('#service_idd').on('change', function () {
        $(".has-error").removeClass('has-error');
        $('.help-block').remove();
        if (jQuery.inArray($(this).val(), limitReachedSevices) >= 0) {
            $('#kt_modal_create_api_key_submit').prop('disabled', true);
        } else {
            $('#kt_modal_create_api_key_submit').prop('disabled', false);
        }
    });

    $('#regenerateApiKeyBtn').on('click', function () {
        $('#kt_modal_create_api_key').modal('show');
        $('#kt_modal_create_api_key_submit').show();
    });

    $('#kt_modal_create_ipwhite').on('hidden.bs.modal', function () {
        $(".has-error").removeClass('has-error');
        $('.help-block').remove();
        $('#kt_modal_create_api_ip_form').trigger('reset');
    });

    $('#kt_modal_create_api_key').on('hidden.bs.modal', function () {
        $('#keydata').addClass('d-none');
        $('#kt_modal_create_api_key_form').removeClass('d-none');

        $(".has-error").removeClass('has-error');
        $('.help-block').remove();
        $('#kt_modal_create_api_key_form').trigger('reset');
    });

    $(document).on('click', '[data-copy-enable="true"]', function () {
        copyText2Clipboard(
            $(this).attr('id'),
            $(this).attr('data-copy-target')
        );
    });


    /**
     * DataTable setup for API Keys 
     */
    datatableSetup(
        $('meta[name="base-url"]').attr('content') + "/user/fetch/apiKeys/0",
        [
            {
                data: 'client_key',
                render: function (data, type, full) {
                    if (full.is_active === '1') {
                        return `<span>${data}</span>`;
                    } else {
                        return `<span class="text-muted">${data}</span>`;
                    }
                }
            },
            {
                data: 'service_id',
                render: function (data, type, full, meta) {
                    if (full.service != undefined && full.service != null) {

                        if (full.is_active === '1') {
                            return '<span class="badge badge-primary">' + full.service.service_name + '</span>';
                        } else {
                            return '<span class="badge badge-muted">' + full.service.service_name + '</span>';
                        }

                    } else {
                        return '<span class="badge badge-danger">NA</span>';
                    }
                }
            },
            {
                data: 'new_created_at',
                render: function (data, type, full) {
                    if (full.is_active === '1') {
                        return `<span>${data}</span>`;
                    } else {
                        return `<span class="text-muted">${data}</span>`;
                    }
                }
            },
            {
                "data": 'is_active',
                render: function (data) {
                    if (data === '1') {
                        return `<span class="text-success">Active</span>`;
                    } else {
                        return `<span class="text-danger">Inactive</span>`;
                    }
                }
            }
        ],
        () => { },
        "#kt_api_keys_table"
    );

    /**
     * Generate API Key
     */
    $('#kt_modal_create_api_key_form').on('submit', function (e) {
        e.preventDefault();
        $(".has-error").removeClass('has-error');
        $('.help-block').remove();

        if ($('#service_idd').val() === null || $('#service_idd').val() == '') {
            show_validation_error({ 'service_id': ["The service id field is required."] });
            return false;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "Your previously generated API key will be disabled.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Okay'
        }).then((result) => {
            if (result.isConfirmed) {

                var submitBtn = $('#kt_modal_create_api_key_submit');
                var clicktext = submitBtn.text();
                submitBtn.text('Pending...').prop('disabled', true);

                var url = $(this).attr('action');
                var method = $(this).attr('method');
                var dataTablesReload = $(this).attr('data-dataTables');
                var data = new FormData($(this)[0]);


                $.ajax({
                    url: url,
                    data: data,
                    cache: false,
                    type: method,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: (response) => {

                        if (response.status_code === '200') {
                            $('#keydata').removeClass('d-none');
                            $('#kt_modal_create_api_key_form').addClass('d-none');
                            $("#" + dataTablesReload).DataTable().ajax.reload();

                            $('#key_data').val(response.data.key);
                            $('#secret_data').val(response.data.secret);

                            submitBtn.text(clicktext).prop('disabled', false);
                        } else if (response.status_code === '400') {
                            show_validation_error(response.data);

                            submitBtn.text(clicktext).prop('disabled', false);
                        } else if (response.status_code === '101') {
                            show_validation_error(response.data);
                            limitReachedSevices.push(this.service_id.value);
                            // $('#kt_modal_create_api_key_submit').prop('disabled', true);
                            submitBtn.text(clicktext);
                            console.log('limit', this.service_id.value);
                        } else if (response.message_object) {
                            Swal.fire({
                                title: 'Oops...',
                                text: response.message.message,
                                icon: "error",
                                buttonsStyling: !1,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });

                            submitBtn.text(clicktext).prop('disabled', false);
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        var message = "Sorry, Internal Server Error.";
                        if (XMLHttpRequest.status != null) {
                            if (XMLHttpRequest.status == '429' || XMLHttpRequest.status == 429) {
                                message = "Too Many Requests. Please try after some time.";
                            } else if (XMLHttpRequest.status == '419' || XMLHttpRequest.status == 419) {
                                message = "CSRF token missmatch.";
                            } else if (XMLHttpRequest.status == '401' || XMLHttpRequest.status == 401) {
                                message = "The login session has been expired, please login again.";
                            }
                        }
                        Swal.fire({
                            text: message,
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                            // Reload the Page
                            location.reload();
                        });

                        submitBtn.text(clicktext).prop('disabled', false);
                    }
                });

            } else {
                $('#kt_modal_create_api_key').modal('hide');
            }
        });

    });

});

function deleteIp(id) {
    $.ajax({
        url: $('meta[name="base-url"]').attr('content') + "/user/accounts/ip-delete/" + id,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            swal.fire("Great Job", "IP deleted Successfull", "success");
            $('#ip_list_table').DataTable().ajax.reload();
        }
    });
}

function datatableSetup(urls, datas, onDraw = function () { }, ele = "#datatable", element = {}) {
    var options = {
        processing: true,
        serverSide: true,
        ordering: false,
        stateSave: true,
        "info": false,
        "searching": false,
        "bLengthChange": false,
        dom: "Bfrltip",
        language: {
            paginate: {
                'first': 'First',
                'last': 'Last',
                'next': '&rarr;',
                'previous': '&larr;'
            }
        },
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        preDrawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        },
        ajax: {
            url: urls,
            type: "post",
            data: function (d) {
                $("")
                d._token = $('meta[name="csrf-token"]').attr('content');
            },
            beforeSend: function () { },
            complete: function () { },
            error: function (response) {
                console.log(response);
            }
        },
        columns: datas
    };

    $.each(element, function (index, val) {
        options[index] = val;
    });
    var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
    return DT;
}


function changePasswordCallback(response) {
    if (response.status_code === '200') {
        $('form[role="change-password"]').trigger('reset');
    }
}

function setWebhookCallback(response) {
    if (response.status_code === '200') {
        $('#webhook_list_table').DataTable().ajax.reload();
    }
}