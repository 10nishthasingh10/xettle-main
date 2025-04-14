"use strict";

$(function () {

    /**
     * Configuration for Product List
     */
    let serviceOptionsDt = [
        {
            //"className": 'details-control',
            "orderable": false,
            "searchable": false,
            "defaultContent": '',
            "data": 'count',
            render: function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        {
            "data": "service_name",
            render: function (data, type, full) {
                return `<span class="btn-link cursor-pointer viewServiceProducts" data-serviceid="${full.service_id}" data-servicename="${full.service_name}">${data}</span>`;
            }
        },
        {
            "data": "is_active",
            render: function (data, type, full) {
                if (data === '1') {
                    return `<span class="text-success">Active</span>`;
                } else {
                    return `<span class="text-danger">InActive</span>`;
                }
            }
        },
        {
            "data": "is_activation_allowed",
            render: function (data, type, full) {
                if (data === '1') {
                    return `<span class="text-success">Active</span>`;
                } else {
                    return `<span class="text-danger">InActive</span>`;
                }
            }
        },
        {
            "data": "new_created_at"
        },
    ];

    datatableSetup(
        $('meta[name="base-url"]').attr('content') + '/admin/global-billing/data-table/service-list',
        serviceOptionsDt, () => { },
        "#dt-service-list"
    );



    /**
     * Handle Service and product view modal
     */
    var viewServiceProductsAjax = false;
    $('#dt-service-list').on('click', '.viewServiceProducts', function () {
        let serviceId = $(this).attr('data-serviceid');
        let serviceName = $(this).attr('data-servicename');

        if (!viewServiceProductsAjax) {
            $('#serviceListOverlay').removeClass('d-none');
            viewServiceProductsAjax = true;

            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/global-billing/products/fetch-list`,
                type: 'POST',
                data: {
                    serviceId: serviceId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {

                    switch (response.status) {
                        case 'SUCCESS':

                            if (response.data !== '' && response.data !== null && response.data !== undefined) {

                                $('#modal-view-service-name').html(serviceName);

                                let formInputRows = '';

                                response.data.forEach((row, index) => {
                                    formInputRows += `
                                         <tr>
                                             <td>${row.name}</td>
                                             <td>${row.min_order_value}</td>
                                             <td>${row.max_order_value}</td>
                                             <td>${row.tax_value}</td>
                                             <td class="text-center">
                                                 ${(row.is_active === '1') ? `<span class="badge badge-success">Active</span>` : `<span class="badge badge-danger">InActive</span>`}
                                             </td>
                                             <td class="text-center fit">${row.created_at}</td>
                                         </tr>`;
                                });
                                $('#modal-view-product-table tbody').html(formInputRows);

                                $('#viewProductListModal').modal('show');

                                $("#viewProductListModal").on('hide.bs.modal', function () {
                                    $('#modal-view-product-table tbody').html('');
                                    $('#modal-view-service-name').html('');
                                });
                            } else {
                                Swal.fire({
                                    title: "Failed",
                                    text: "No products are added yet.",
                                    icon: "error",
                                    buttonsStyling: !1,
                                    confirmButtonText: "Close",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }

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


                    viewServiceProductsAjax = false;
                    $('#serviceListOverlay').addClass('d-none');
                }
            });
        }
    });


    /**
     * Configuration for Scheme fee Info
     */
    var isFirstReqSchemeInfo = true;
    let schemeInfoOptionsDt = [
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
            "data": "product_name.service_name.service_name",
        },
        {
            "data": "product_name.name"
        },
        {
            "data": "start_value",
            render: function (data, type, full, meta) {
                if (full.start_value === null || full.start_value === '')
                    return '';

                return full.start_value;
            }
        },
        {
            "data": "end_value",
            render: function (data, type, full, meta) {
                if (full.end_value === null || full.end_value === '')
                    return '';

                return full.end_value;
            }
        },
        {
            "data": "fee",
            render: function (data, type, full, meta) {
                if (full.fee === null || full.fee === '')
                    return '';

                return full.fee;
            }
        },
        {
            "data": "min_fee",
            render: function (data, type, full, meta) {
                if (full.min_fee == null || full.min_fee == '')
                    return '';

                return full.min_fee;
            }
        },
        {
            "data": "max_fee",
            render: function (data, type, full, meta) {
                if (full.max_fee === null || full.max_fee === '')
                    return '';

                return full.max_fee;
            }
        },
        {
            "data": "type",
            render: function (data) {
                return data.toUpperCase();
            }
        },
        {
            "data": "new_created_at"
        },
    ];

    $('form#searchForm_2').on('submit', function (e) {
        e.preventDefault();
        $('#searchForm_2').find('button:submit').button('loading');

        if (isFirstReqSchemeInfo) {
            isFirstReqSchemeInfo = false;
            $('#datatable_scheme_info').removeClass('d-none');
            datatableSetup(
                $('meta[name="base-url"]').attr('content') + '/admin/global-billing/data-table/product-and-fee',
                schemeInfoOptionsDt, () => { },
                "#datatable_scheme_info",
                '#searchForm_2'
            );
        } else {
            $('#datatable_scheme_info').dataTable().api().ajax.reload();
        }
    });
});


function datatableSetup(urls, datas, onDraw = function () { }, ele = "#datatable", formId = '#searchForm', element = {}) {
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
            },
            beforeSend: function () { },
            complete: function () {
                $(formId).find('button:submit').button('reset');
                $('#formReset').button('reset');
                $('#formReset_2').button('reset');
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