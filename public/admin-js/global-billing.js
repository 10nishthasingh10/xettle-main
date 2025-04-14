/**
 * Version: 1.0.0
 * File: global-billing.js?v=1.0.0
 */

"use strict"
var productCountFromDb = 0;
var productFeeCountFromDb = 0;
var currentClickedServiceButton = null;
var currentClickedProductButton = null;
var updateServiceValue = '';

$(function () {

    $('#service_slug').on('keyup', slugInputFn);
    $('#update_service_slug').on('keyup', slugInputFn);

    //add new product row
    $('#add_new_product_row').on('click', function () {
        $('#modal-product-table tbody').append(addNewProductRow(productCountFromDb++));

        $('.productUpdateForm').on('keyup', function () {
            productUpdateForm('.productUpdateForm');
        });
    });

    //remove new product row
    $('#modal-product-table').on('click', '.removeNewProductRow', function () {
        removeNewProductRow(this, 'tr.fromBtn', '1');
    });

    //add new product fee row
    $('#add_new_product_fee_row').on('click', function () {
        $('#modal-product-fee-table tbody').append(addNewProductFeeRow(productFeeCountFromDb++));

        $('.productFeeUpdateForm').on('keyup change', function () {
            productFeeUpdateForm('.productFeeUpdateForm');
        });
    });

    //remove new product row
    $('#modal-product-fee-table').on('click', '.removeNewProductFeeRow', function () {
        removeNewProductRow(this, 'tr.feeFromBtn', '2');
    });


    /**
     * Check that forms are not empty
     */
    $('.addServiceInput').on('keyup', function () {

        let isButtonEnable = true;
        var eleInputs = $('.addServiceInput');

        for (let i = 0; i < eleInputs.length; i++) {
            if (eleInputs.eq(i).val().length > 0) {
                isButtonEnable = true;
            } else {
                isButtonEnable = false;
            }
        }

        $('#formBtnAddNewService').prop('disabled', !isButtonEnable);
    });

    $('.updateServiceInput').on('keyup', function () {

        let isButtonEnable = true;
        var eleInputs = $('.updateServiceInput');
        for (let i = 0; i < eleInputs.length; i++) {
            if (eleInputs.eq(i).val() !== updateServiceValue) {
                isButtonEnable = true;
            } else {
                isButtonEnable = false;
            }
        }
        $('#formBtnUpdateService').prop('disabled', !isButtonEnable);
    });


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
                    return `<label class="switch serviceStatus" data-id="${full.id}"><input type="checkbox" checked><span class="slider round"></span></label>`;
                } else {
                    return `<label class="switch serviceStatus" data-id="${full.id}"><input type="checkbox"><span class="slider round"></span></label>`;
                }
            }
        },
        {
            "data": "is_activation_allowed",
            render: function (data, type, full) {
                if (data === '1') {
                    return `<label class="switch serviceActivation" data-id="${full.id}"><input type="checkbox" checked><span class="slider round"></span></label>`;
                } else {
                    return `<label class="switch serviceActivation" data-id="${full.id}"><input type="checkbox"><span class="slider round"></span></label>`;
                }
            }
        },
        {
            "data": "new_created_at"
        },
        {
            "data": "action",
            render: function (data, type, full) {
                let actionBtn = '';
                actionBtn += `<button title="Edit Service" data-id="${full.id}" data-name="${full.service_name}" class="btn btn-link border btn-sm editService"><i class="os-icon os-icon-edit"></i></button>`;
                actionBtn += `<button title="Product List" data-id="${full.service_id}" data-servicename="${full.service_name}" class="btn btn-link border btn-sm addUpdateProducts"><i class="fas fa-list"></i></button>`;
                return `<span class='inline-flex'>${actionBtn}</span>`;
            }
        }
    ];

    datatableSetup(
        $('meta[name="base-url"]').attr('content') + '/admin/global-billing/data-table/service-list',
        serviceOptionsDt, () => { },
        "#dt-service-list"
    );


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
                                            <td>${row.slug}</td>
                                            <td>${row.min_order_value}</td>
                                            <td>${row.max_order_value}</td>
                                            <td>${row.tax_value}</td>
                                            <td class="text-center">
                                                ${(row.is_active === '1') ? `<span class="badge badge-success">Active</span>` : `<span class="badge badge-danger">InActive</span>`}
                                            </td>
                                            <td class="text-center">${row.created_at}</td>
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
     * Handle Service activation status
     */
    var serviceActivationAjax = false;
    $('#dt-service-list').on('click', 'label.serviceActivation', function () {

        if (!serviceActivationAjax) {
            let rowId = $(this).attr('data-id');
            serviceActivationAjax = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/global-billing/status/service-activation/${rowId}`,
                type: 'GET',
                success: function (response) {
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
                }
            });
        }
    });



    /**
     * handle Service Status
     */
    var serviceStatusAjax = false;
    $('#dt-service-list').on('click', 'label.serviceStatus', function () {

        if (!serviceStatusAjax) {
            let rowId = $(this).attr('data-id');
            serviceStatusAjax = true;
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/global-billing/status/service-status/${rowId}`,
                type: 'GET',
                success: function (response) {
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

                    serviceStatusAjax = false;
                }
            });
        }
    });


    /**
     * handle Product Status
     */
    var productStatusAjax = false;
    $('#modal-product-table').on('click', 'label.productStatus', function () {

        if (!productStatusAjax) {
            let rowId = $(this).attr('data-id');
            productStatusAjax = true;
            $('#addUpdateProductOverlay').removeClass('d-none');
            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/global-billing/status/product-status/${rowId}`,
                type: 'GET',
                success: function (response) {
                    Swal.fire({
                        title: response.title,
                        text: response.message,
                        icon: "success",
                        buttonsStyling: !1,
                        confirmButtonText: "Done",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then((result) => { });

                    productStatusAjax = false;
                    $('#addUpdateProductOverlay').addClass('d-none');
                }
            });
        }
    });



    /**
     * Add and Update Products
     */
    var addUpdateProductsAjax = false;

    $('#dt-service-list').on('click', '.addUpdateProducts', function () {
        let serviceId = $(this).attr('data-id');
        let serviceName = $(this).attr('data-servicename');

        if (!addUpdateProductsAjax) {
            $('#addUpdateProductOverlay').removeClass('d-none');
            currentClickedServiceButton = this;
            addUpdateProductsAjax = true;
            $(this).prop('disabled', true);
            let btnText = $(this).html();
            $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);

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
                            $('#modal-service-name').html(serviceName);
                            $('#modal_service_id').val(serviceId);

                            if (response.data !== '' && response.data !== null && response.data !== undefined) {

                                let formInputRows = '';

                                response.data.forEach((row, index) => {
                                    formInputRows += `
                                        <tr class="fromdb">
                                            <td>
                                                <input type="hidden" name="product_id[${productCountFromDb}]" value="${row.id}" />
                                                <input value="${row.name}" type="text" name="product_name[${productCountFromDb}]" class="form-control productUpdateForm" />
                                            </td>
                                            <td><div class="form-control bg-light">${row.slug}</div></td>
                                            <td><input value="${row.min_order_value}" type="number" name="min_ord_value[${productCountFromDb}]" class="form-control productUpdateForm onlyNumberInput" /></td>
                                            <td><input value="${row.max_order_value}" type="number" name="max_ord_value[${productCountFromDb}]" class="form-control productUpdateForm onlyNumberInput" /></td>
                                            <td><input value="${row.tax_value}" type="number" name="tax_value[${productCountFromDb}]" class="form-control productUpdateForm onlyNumberInput" /></td>
                                            <td class="text-center">
                                                ${(row.is_active === '1') ? `<label class="switch productStatus" data-id="${row.id}"><input type="checkbox" checked><span class="slider round"></span></label>` : `<label class="switch productStatus" data-id="${row.id}"><input type="checkbox"><span class="slider round"></span></label>`}
                                            </td>
                                            <td class="text-center">
                                                <span class="inline-flex"><span title="Product Fee List" data-id="${row.product_id}" data-servicename="${serviceName}" data-productname="${row.name}" class="btn btn-link border btn-sm addUpdateProductFee"><i class="fas fa-list"></i></span></span>
                                            </td>
                                        </tr>`;
                                    productCountFromDb++;
                                });
                                $('#modal-product-table tbody').html(formInputRows);

                                $('#modal-product-table').on('keyup', '.onlyNumberInput', onlyNumberInput);
                                $('#modal-product-table').on('keyup', '.onlySlugInput', slugInputFn);
                            }


                            $('#addUpdateProductsModal').modal('show');

                            $("#addUpdateProductsModal").on('hide.bs.modal', function () {
                                $('#modal-product-table tbody').html('');
                                $('#modal-service-name').html('');
                                $('#modal_service_id').val('');
                                productCountFromDb = 0;
                                $('#formBtnUpdateProduct').prop('disabled', true);
                            });


                            $('.productUpdateForm').on('keyup', function () {
                                productUpdateForm('.productUpdateForm');
                            });
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


                    addUpdateProductsAjax = false;
                    $(this).prop('disabled', false);
                    $(this).html(btnText);
                    $('#addUpdateProductOverlay').addClass('d-none');
                }
            });
        }
    });



    /**
     * Add and Update Products Fee
     */
    var addUpdateProductFeeAjax = false;

    $('#modal-product-table').on('click', '.addUpdateProductFee', function () {
        let productId = $(this).attr('data-id');
        let serviceName = $(this).attr('data-servicename');
        let productName = $(this).attr('data-productname');

        if (!addUpdateProductFeeAjax) {
            $('#addUpdateProductFeeOverlay').removeClass('d-none');
            currentClickedProductButton = this;
            addUpdateProductFeeAjax = true;
            $(this).prop('disabled', true);
            let btnText = $(this).html();
            $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);

            $.ajax({
                url: $('meta[name="base-url"]').attr('content') + `/admin/global-billing/products/fetch-fee-list`,
                type: 'POST',
                data: {
                    productId: productId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {

                    switch (response.status) {
                        case 'SUCCESS':
                            $('#modal-product-name').html(`${serviceName} - ${productName}`);
                            $('#modal_product_id').val(productId);

                            if (response.data !== '' && response.data !== null && response.data !== undefined) {

                                let formInputRows = '';

                                response.data.forEach((row, index) => {
                                    formInputRows += `
                                    <tr class= "feeFromDb">
                                             <td>
                                                 <input type="hidden" name="fee_id[${productFeeCountFromDb}]" value="${row.id}" />
                                                 <input value="fixed" type="radio" name="fee_type[${productFeeCountFromDb}]" id="feeTypeF${productFeeCountFromDb}" class="form-control-radio productFeeUpdateForm" ${(row.type === 'fixed') ? 'checked' : ''} data-value="${row.type}" />
                                                 <label for="feeTypeF${productFeeCountFromDb}">Fixed</label>
                                                 <input value="percent" type="radio" name="fee_type[${productFeeCountFromDb}]" id="feeTypeP${productFeeCountFromDb}" class="form-control-radio productFeeUpdateForm" ${(row.type === 'percent') ? 'checked' : ''} data-value="${row.type}" />
                                                 <label for="feeTypeP${productFeeCountFromDb}">Percent</label>
                                             </td>
                                             <td><input value="${(row.start_value) ? row.start_value : ''}" type="number" name="fee_start_value[${productFeeCountFromDb}]" class="form-control productFeeUpdateForm onlyNumberInput" /></td>
                                             <td><input value="${(row.end_value) ? row.end_value : ''}" type="number" name="fee_end_value[${productFeeCountFromDb}]" class="form-control productFeeUpdateForm onlyNumberInput" /></td>
                                             <td><input value="${row.fee}" type="number" name="fee_value[${productFeeCountFromDb}]" class="form-control productFeeUpdateForm onlyNumberInput" /></td>
                                             <td><input value="${(row.min_fee) ? row.min_fee : ''}" type="number" name="fee_min[${productFeeCountFromDb}]" class="form-control productFeeUpdateForm onlyNumberInput" /></td>
                                             <td colspan="2"><input value="${(row.max_fee) ? row.max_fee : ''}" type="number" name="fee_max[${productFeeCountFromDb}]" class="form-control productFeeUpdateForm onlyNumberInput" /></td>
                                         </tr>`;
                                    productFeeCountFromDb++;
                                });
                                $('#modal-product-fee-table tbody').html(formInputRows);

                                $('#modal-product-fee-table').on('keyup', '.onlyNumberInput', onlyNumberInput);
                            }


                            $('#addUpdateProductFeeModal').modal('show');

                            $("#addUpdateProductFeeModal").on('hide.bs.modal', function () {
                                $('#modal-product-fee-table tbody').html('');
                                $('#modal-product-name').html('');
                                $('#modal_product_id').val('');
                                productFeeCountFromDb = 0;
                                $('#formBtnUpdateProductFee').prop('disabled', true);
                            });


                            $('.productFeeUpdateForm').on('keyup change', function () {
                                productFeeUpdateForm('.productFeeUpdateForm');
                            });

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


                    addUpdateProductFeeAjax = false;
                    $(this).prop('disabled', false);
                    $(this).html(btnText);
                    $('#addUpdateProductFeeOverlay').addClass('d-none');
                }
            });
        }
    });


    /**
     * Edit Service Name 
     */
    $('#dt-service-list').on('click', '.editService', function () {

        let rowId = $(this).attr('data-id');
        let serviceName = $(this).attr('data-name');

        $('#update_row_id').val(rowId);
        $('#update_service_name').val(serviceName);
        updateServiceValue = serviceName;

        $('#updateServiceModal').modal('show');

    });
});

function productUpdateForm(className) {
    let isButtonEnable = false;
    var eleInputs = $(className);
    for (let i = 0; i < eleInputs.length; i++) {
        if (eleInputs.eq(i).val() !== eleInputs.eq(i).prop('defaultValue')) {
            isButtonEnable = true;
        }
    }

    $('#formBtnUpdateProduct').prop('disabled', !isButtonEnable);
}

function productFeeUpdateForm(className) {

    let isButtonEnable = false;
    var eleInputs = $(className);
    for (let i = 0; i < eleInputs.length; i++) {

        if (eleInputs.eq(i).hasClass('form-control-radio') && eleInputs.eq(i)[0].checked) {
            if (eleInputs.eq(i).val() !== eleInputs.eq(i).attr('data-value')) {
                isButtonEnable = true;
            }
        } else {
            if (eleInputs.eq(i).val() !== eleInputs.eq(i).prop('defaultValue')) {
                isButtonEnable = true;
            }
        }
    }

    $('#formBtnUpdateProductFee').prop('disabled', !isButtonEnable);

}

function addNewServiceCb(response) {
    if (response.status_code === '200') {
        $('form[role="add-new-service-form"]').trigger('reset');
        $("#dt-service-list").DataTable().ajax.reload();
        $('#addNewServiceModal').modal('hide');
        $('#formBtnAddNewService').prop('disabled', true);
    }
}

function updateServiceCb(response) {
    if (response.status_code === '200') {
        $('form[role="update-service-form"]').trigger('reset');
        $("#dt-service-list").DataTable().ajax.reload();
        $('#updateServiceModal').modal('hide');
        $('#formBtnUpdateService').prop('disabled', true);
    }
}

function updateProductListCb(response) {
    if (response.status_code === '200') {
        if (currentClickedServiceButton !== null) {
            $(currentClickedServiceButton).trigger('click');
            $('#formBtnUpdateProduct').prop('disabled', true);
        }
    }
}

function updateProductFeeListCb(response) {
    if (response.status_code === '200') {
        if (currentClickedProductButton !== null) {
            $(currentClickedProductButton).trigger('click');
        }
    }
}

function onlyNumberInput(event) {
    // let val = event.target.value.replace(/[^0-9\.]/g, '');
    // event.target.value = val.replace(/\.+$/, '.');

    let evt = (event) ? event : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 46 || (charCode >= 48 && charCode <= 57)) {
        return true;
    }
    return false;
}

function slugInputFn(event) {
    let temp = event.target.value.replace(/\s+$/, '_');
    temp = temp.replace(/\_+$/, '_');
    temp = temp.replace(/\ +$/, '_');
    temp = temp.replace(/[^a-z0-9\_]/gi, '');
    event.target.value = temp.toLowerCase();
}

function addNewProductRow(count) {
    let row = `
    <tr class="fromBtn">
        <td>
            <input type="text" name="product_name[${count}]" class="form-control productUpdateForm" placeholder="Enter product name" />
        </td>
        <td><input type="text" name="product_slug[${count}]" class="form-control productUpdateForm onlySlugInput" placeholder="Enter product slug" /></td>
        <td><input type="number" min="0" name="min_ord_value[${count}]" class="form-control productUpdateForm onlyNumberInput" placeholder="Enter min order value" /></td>
        <td><input type="number" min="0" name="max_ord_value[${count}]" class="form-control productUpdateForm onlyNumberInput" placeholder="Enter max order value" /></td>
        <td><input type="number" min="0" name="tax_value[${count}]" class="form-control productUpdateForm onlyNumberInput" placeholder="Enter tax value" /></td>
        <td></td>
        <td class="text-center"><span class="btn removeNewProductRow"><i class="fas fa-times-square"></i></span></td>
    </tr>`;

    return row;
}


function addNewProductFeeRow(count) {
    let row = `
        <tr class= "feeFromBtn">
            <td>
                <input value="fixed" type="radio" name="fee_type[${count}]" id="feeTypeF${count}" class="form-control-radio productFeeUpdateForm" data-value="" />
                <label for="feeTypeF${count}">Fixed</label>
                <input value="percent" type="radio" name="fee_type[${count}]" id="feeTypeP${count}" class="form-control-radio productFeeUpdateForm" data-value="" />
                <label for="feeTypeP${count}">Percent</label>
            </td>
            <td><input type="number" min="0" name="fee_start_value[${count}]" class="form-control productFeeUpdateForm onlyNumberInput" placeholder="Start value" /></td>
            <td><input type="number" min="0" name="fee_end_value[${count}]" class="form-control productFeeUpdateForm onlyNumberInput" placeholder="End Value" /></td>
            <td><input type="number" min="0" name="fee_value[${count}]" class="form-control productFeeUpdateForm onlyNumberInput" placeholder="Fee amt" /></td>
            <td><input type="number" min="0" name="fee_min[${count}]" class="form-control productFeeUpdateForm onlyNumberInput" placeholder="Min fee" /></td>
            <td><input type="number" min="0" name="fee_max[${count}]" class="form-control productFeeUpdateForm onlyNumberInput" placeholder="Max fee" /></td>
            <td class="text-center"><span class="btn removeNewProductFeeRow"><i class="fas fa-times-square"></i></span></td>
        </tr>`;

    return row;
}


function removeNewProductRow(currentObject, parentDiv, type) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to remove this row?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Remove',
        confirmButtonColor: '#e13131',
    }).then((result) => {
        if (result.isConfirmed) {
            $(currentObject).parent().parent(parentDiv).remove();

            if (type === '1') {
                // productCountFromDb--;
                productUpdateForm('.productUpdateForm');
            }
            else if (type === '2') {
                // productFeeCountFromDb--;
                productFeeUpdateForm('.productFeeUpdateForm');
            }
        }
    });
}


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