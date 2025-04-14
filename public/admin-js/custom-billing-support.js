/**
 * Version: 1.0.0
 * File: custom-billing-support.js?v=1.0.0
 */
"use strict"

$(function () {

    /**
     * Hide event of Modal
     */
    $("#viewRulesModal").on('hide.bs.modal', function () {
        $('#viewRulesModalTable tbody').html('');
        $('#viewRulesName').html('');
    });


    /**
     * Datatable Options for List Schemes Info
     */
    datatableSetup(
        $('meta[name="base-url"]').attr('content') + '/admin/custom-billing/data-table/schemes-info',
        [
            {
                //"className": 'details-control',
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": 'count',
                render: function (data, type, full, meta) {
                    return meta.row + 1;
                }
            },
            {
                "data": "scheme_name",
                // render: function (data, type, full) {
                //     return `<span class="btn-link cursor-pointer viewSchemeRules" data-schemeid="${full.id}">${data}</span>`;
                // }
            },
            {
                "data": "is_active",
                render: function (data) {
                    if (data === '1') {
                        return `<span class="text-success">Active</span></label>`;
                    } else {
                        return `<span class="text-dark">Inactive</span>`;
                    }
                }
            },
            {
                "data": "new_created_at"
            },
            {
                "orderable": false,
                "data": null,
                render: function (data, type, full) {
                    let actionBtn = '';
                    actionBtn += `<button title="Edit Scheme" data-id="${full.id}" class="btn btn-link border btn-sm viewSchemeRules" data-schemeid="${full.id}" data-schemename="${full.scheme_name}"><i class="fas fa-eye"></i></button>`;
                    return `<span class='inline-flex'>${actionBtn}</span>`;
                }
            }
        ], () => { }, "#datatable_schemes_info");


    /**
     * Datatable options User and Scheme Relation
     */
    datatableSetup(
        $('meta[name="base-url"]').attr('content') + '/admin/custom-billing/data-table/user-and-scheme',
        [
            {
                //"className": 'details-control',
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": 'count',
                render: function (data, type, full, meta) {
                    return meta.row + 1;
                }
            },
            {
                "orderable": false,
                "data": "user_name_email.name",
            },
            {
                "orderable": false,
                "data": "user_name_email.email",
            },
            {
                "orderable": false,
                "data": "schemes_name.scheme_name"
            },
            // {
            //     "orderable": false,
            //     "data": null,
            //     render: function (data, type, full) {
            //         let $actionBtn = '';
            //         $actionBtn += `<button type="button" data-relation="${full.id}" title="Delete User Relation" class="btn btn-link border btn-sm deleteUserSchemeRelation"><i class="far fa-trash"></i></button>`;
            //         return `<span class='inline-flex'>${$actionBtn}</span>`;
            //     }
            // }
        ], () => { }, '#datatable_scheme_user_relation', '#searchFormUserSchemeRelation');


    /**
     * Handle Service and product view modal
     */
    var addUpdateSchemeAndRulesAjax = false;
    $('#datatable_schemes_info').on('click', '.viewSchemeRules', function () {
        let serviceId = $(this).attr('data-schemeid');
        let htmlText = $(this).attr('data-schemename');

        if (!addUpdateSchemeAndRulesAjax) {
            $('#viewProductOverlay').addClass('d-none');
            addUpdateSchemeAndRulesAjax = true;
            let btnText = $(this).html();
            $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);

            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/custom-billing/scheme-rule/fetch-list`,
                type: 'POST',
                data: {
                    scheme_id: serviceId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {

                    switch (response.status) {
                        case 'SUCCESS':

                            $('#viewRulesName').html(htmlText);

                            if (response.data.schemeRules !== '' &&
                                response.data.schemeRules !== null &&
                                response.data.schemeRules !== undefined) {

                                let formInputRows = '';

                                response.data.schemeRules.forEach((row, index) => {
                                    formInputRows += `
                                        <tr>
                                            <td class="text-center">${row.service_name}</td>
                                            <td class="text-center">${row.name}</td>
                                            <td class="text-center">${(row.type === 'fixed') ? 'Fixed' : 'Percent'}</td>
                                            <td class="text-center">${(row.is_active === '1') ? 'Active' : 'Inactive'}</td>
                                            <td class="text-center">${row.start_value || ''}</td>
                                            <td class="text-center">${row.end_value || ''}</td>
                                            <td class="text-center">${row.fee || ''}</td>
                                            <td class="text-center">${row.min_fee || ''}</td>
                                            <td class="text-center">${row.max_fee || ''}</td>
                                        </tr>`;
                                });

                                $('#viewRulesModalTable tbody').html(formInputRows);

                            }

                            $('#viewRulesModal').modal('show');
                            break;


                        case 'FAILURE':
                        default:
                            Swal.fire({
                                title: "Failed",
                                text: response.message,
                                icon: "error",
                                buttonsStyling: !1,
                                confirmButtonText: "Close",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                            break;
                    }


                    addUpdateSchemeAndRulesAjax = false;
                    $(this).prop('disabled', false);
                    $(this).html(btnText);
                    $('#addUpdateProductOverlay').addClass('d-none');
                },
                error: (xhr, status, error) => {
                    Swal.fire({
                        title: "HTTP Error",
                        text: error,
                        icon: "error",
                        buttonsStyling: !1,
                        confirmButtonText: "Close",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });

                    addUpdateSchemeAndRulesAjax = false;
                    $(this).prop('disabled', false);
                    $(this).html(btnText);
                    $('#addUpdateProductOverlay').addClass('d-none');
                }
            });
        }
    });
});


function datatableSetup(urls, datas, onDraw = function () { }, ele = "#datatable", formId = null, element = {}) {
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
                if (formId) {
                    d.from = $(formId).find('[name="from"]').val();
                    d.to = $(formId).find('[name="to"]').val();
                    d.searchText = $(formId).find('[name="searchText"]').val();
                    d.payoutReference = $(formId).find('[name="payoutReference"]').val();
                    d.status = $(formId).find('[name="status"]').val();
                    d.user_id = $(formId).find('[name="user_id"]').val();
                    d.scheme_id = $(formId).find('[name="scheme_id"]').val();
                    d.scheme_id_relation = $(formId).find('[name="scheme_id_relation"]').val();
                    d.service_id = $(formId).find('[name="service_id"]').val();
                    d.product_id = $(formId).find('[name="product_id"]').val();
                }
            },
            beforeSend: function () { },
            complete: function () {
                $(formId).find('button[type="submit"]').html('Search');
                $(formId).find('button[type="button"]').html('Reset');
            },
            error: function (response) { }
        },
        columns: datas
    };

    $.each(element, function (index, val) {
        options[index] = val;
    });

    var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
    return DT;
}

$('form#searchFormUserSchemeRelation').on('submit', function (e) {
    e.preventDefault();
    $('#searchFormUserSchemeRelation').find('button:submit').html(`<i class='fa fa-spin fa-spinner'></i>`);
    $('#datatable_scheme_user_relation').dataTable().api().ajax.reload();
    return false;
});

$('#resetFormUserSchemeRelation').on('click', function () {
    $('#userSchemeRelationId').val("").trigger('change');
    $('#scheme_id_relation').val("").trigger('change');
    $('#resetFormUserSchemeRelation').trigger('reset');
    $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);
    $('#datatable_scheme_user_relation').dataTable().api().ajax.reload();
});