"use strict"

var isOpenModalGlobal = false;
var isUserConfigAjax = false;
var openUserConfigBtnHtml = '';

$(function () {
    $('#datatable').on('click', '.openUserConfigModal', function () {
        let bizId = $(this).val();
        let userName = $(this).attr('data-username');
        if (!isUserConfigAjax) {
            isUserConfigAjax = true;
            $(this).prop('disabled', true);
            openUserConfigBtnHtml = $(this).html();
            $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);
            getBankInfo(bizId, this);
            $('#userNameModal').html(userName);
        }
    });

    $("#viewBankInfo").on('hidden.bs.modal', function () {
        isOpenModalGlobal = false;
        $('#bankInfo').html('');
        $('#vanInfo').html('');
        $('#userConfig').html('');
        $('#userNameModal').html('');

        $('#viewSchemeInfoModal').modal('hide');
        $('#payloadModal').modal('hide');
    });
});

/**
 * Bank Info
 */
function getBankInfo(bizId, btnId) {
    $.ajax({
        url: $('meta[name="base-url"]').attr('content') + `/admin/users/bank-list/${bizId}`,
        success: function (result) {
            if (result.code === '0x0200') {
                isOpenModalGlobal = true;

                var resultTable = '';
                result.data.forEach(function (row, index) {
                    resultTable += `
                            <tr ${(row.is_primary === "1") ? "style='background-color: #DAE3FF;'" : ""}>
                                <td>${index + 1}</td>
                                <td>${row.account_number}</td>
                                <td>${row.ifsc}</td>
                                <td>${row.beneficiary_name}</td>
                                <td>${(row.is_primary == 1) ? '<span class="text-success">Primary</span>' : ''}</td>
                                <td>${(row.is_active == 1) ? '<span class="text-success">Active</span>' : '<span class="text-danger">Not Active</span>'}</td>
                                <td>${(row.is_verified == 1) ? '<span class="text-success">Verified</span>' : '<span class="text-danger">Not Verified</span>'}</td>
                                <td>${row.created_at}</td>
                            </tr>`;
                });

                resultTable = `
                    <h6>Bank Info</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>A/c Number</th>
                                    <th>IFSC</th>
                                    <th>A/c Holder Name</th>
                                    <th>Is Primary</th>
                                    <th>Status</th>
                                    <th>Verify</th>
                                    <th>Added At</th>
                                </tr>
                            </thead>
                            <tbody>
                            ${resultTable}
                            </tbody>
                        </table>
                    </div>
                    `;

                $('#bankInfo').html(resultTable);

            }

            getVanInfo(bizId, btnId);
        }
    });
}

/**
 * Van Info
 */
function getVanInfo(bizId, btnId) {
    $.ajax({
        url: $('meta[name="base-url"]').attr('content') + `/admin/users/van-list/${bizId}`,
        success: function (result) {
            if (result.code === '0x0200') {
                isOpenModalGlobal = true;

                var resultTable = '';
                result.data.forEach(function (row, index) {

                    let remitters = '';

                    switch (row.root_type) {
                        case 'eb_van':
                            if (row.remitters != undefined && row.remitters != null) {
                                let obj = row.remitters.replace('[', "");
                                obj = obj.replace(']', "");
                                obj = JSON.parse(obj);

                                remitters = obj.account_number + '<br>' + obj.account_ifsc;
                            }
                            break;
                        case 'raz_van':
                            if (row.remitters != undefined && row.remitters != null) {
                                let obj = row.remitters.replace('[', "");
                                obj = obj.replace(']', "");
                                obj = JSON.parse(obj);

                                remitters = obj.bank_account.account_number + '<br>' + obj.bank_account.ifsc;
                            }
                            break;
                        case 'cf_van':
                            remitters = row.remitterAccount + '<br>' + row.remitterIfsc;
                            break;

                    }

                    resultTable += `
                            <tr>
                            <td>${index + 1}</td>
                            <td>${row.account_number}</td>
                            <td>${row.ifsc}</td>
                            <td>${remitters}</td>
                            <td>${(row.status == 1) ? '<span class="text-success">Active</span>' : '<span class="text-danger">Not Active</span>'}</td>
                            <td>${row.root_type.replace('_', " ").toUpperCase()}</td>
                            <td>${row.created_at}</td>
                            </tr>`;
                });

                resultTable = `
                    <h6>Van Info</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>A/c Number</th>
                                    <th>IFSC</th>
                                    <th>Remitters</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Added At</th>
                                </tr>
                            </thead>
                            <tbody>
                            ${resultTable}
                            </tbody>
                        </table>
                    </div>
                    `;

                $('#vanInfo').html(resultTable);

            }

            getUserConfig(bizId, btnId);
        }
    });
}

/**
 * User Config
 */
function getUserConfig(bizId, btnId) {
    $.ajax({
        url: $('meta[name="base-url"]').attr('content') + `/admin/users/config/${bizId}`,
        success: function (result) {
            if (result.code === '0x0200') {
                isOpenModalGlobal = true;

                let resultTable = `
                            <tr>
                                <td><button type="button" data-payload='${result.data.user_salt}' class="btn btn-sm btn-primary" id="open-salt-modal">Show</button></td>
                                <td>${(result.data.scheme_name) ? `<button type="button" value='${result.data.id}' class="btn btn-sm btn-primary" id="open-scheme-modal">${result.data.scheme_name}</button>` : ''}</td>
                                <td>${result.data.upi_stack_callbacks || ''}</td>
                                <td>${result.data.upi_stack_settlements || ''}</td>
                                <td>${result.data.smart_collect_callbacks || ''}</td>
                                <td>${result.data.smart_collect_settlements || ''}</td>
                            </tr>`;

                let resultTable2 = `
                            <tr><td colspan="6"></td></tr>
                            <tr>
                                <th>Load Money</th>
                                <th>Auto Settlement</th>
                                <th>Threshold</th>
                                <th>SDK</th>
                                <th>UPI Limit</th>
                                <th>VAN LIMIT</th>
                            </tr>
                            <tr>
                                <td>${(result.data.load_money_request === "1") ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">InActive</span>'}</td>
                                <td>${(result.data.is_auto_settlement === "1") ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">InActive</span>'}</td>
                                <td>${result.data.threshold || 0}</td>
                                <td>${(result.data.is_sdk_enable === "1") ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">InActive</span>'}</td>
                                <td>${result.data.upi_stack_vpa_limit || ''}</td>
                                <td>${result.data.smart_collect_vpa_van_limit || ''}</td>
                            </tr>`;

                resultTable = `
                    <h6>User Config</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Salt</th>
                                    <th>Scheme</th>
                                    <th>UPI Stack Callback</th>
                                    <th>UPI Stack Settle</th>
                                    <th>Smart Collect Callback</th>
                                    <th>Smart Collect Settle</th>
                                </tr>
                            </thead>
                            <tbody>
                            ${resultTable}
                            ${resultTable2}
                            </tbody>
                        </table>
                    </div>`;


                $('#userConfig').html(resultTable);

                //open salt modal
                $('#open-salt-modal').on('click', function () {
                    let msg = $(this).attr('data-payload');
                    $("#payloadModal").on('show.bs.modal', function () {
                        $('#payloadModal .modal-title').html("User Salt");
                        if (msg == null || msg == '')
                            $('#payloadModalData').html(msg);
                        else
                            $('#payloadModalData').html(msg.replace(/\=/g, ''));
                    });
                    $('#payloadModal').modal('show');
                    $("#payloadModal").on('hidden.bs.modal', function () {
                        $('#payloadModalData').html('');
                        $('#payloadModal .modal-title').html('');
                    });
                });


                //open custome scheme modal
                $('#open-scheme-modal').on('click', function () {
                    let schemeId = $(this).val();
                    let schemeName = $(this).html();

                    $(this).prop('disabled', true);
                    $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);

                    $.ajax({
                        url: $('meta[name="base-url"]').attr('content') + `/admin/users/scheme-info/${schemeId}`,
                        success: (response) => {
                            if (response.code === '0x0200') {
                                $("#viewSchemeInfoModal").on('show.bs.modal', function () {
                                    $('#viewSchemeInfoModal .modal-title').html(schemeName);

                                    let resultSchemeInfo = '';
                                    response.data.forEach(function (row, index) {
                                        resultSchemeInfo += `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${row.service_name}</td>
                                            <td>${row.name}</td>
                                            <td>${row.fee} ${(row.type === "percent") ? '%' : ''}</td>
                                            <td>${(row.is_active == 1) ? '<span class="text-success">Active</span>' : '<span class="text-danger">Not Active</span>'}</td>
                                            <td>${row.start_value || ''}</td>
                                            <td>${row.end_value || ''}</td>
                                            <td>${row.min_fee || ''}</td>
                                            <td>${row.max_fee || ''}</td>
                                        </tr>`;
                                    });


                                    resultSchemeInfo = `
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Service</th>
                                                        <th>Product</th>
                                                        <th>Fee</th>
                                                        <th>Status</th>
                                                        <th>Start Value</th>
                                                        <th>End Value</th>
                                                        <th>Min Fee</th>
                                                        <th>Max Fee</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                ${resultSchemeInfo}
                                                </tbody>
                                            </table>
                                        </div>
                                        `;

                                    $('#viewSchemeInfoModalData').html(resultSchemeInfo);

                                });

                                $('#viewSchemeInfoModal').modal('show');
                                $("#viewSchemeInfoModal").on('hidden.bs.modal', function () {
                                    $('#viewSchemeInfoModalData').html('');
                                    $('#viewSchemeInfoModal .modal-title').html('');
                                });
                            }

                            $(this).prop('disabled', false);
                            $(this).html(schemeName);
                        }
                    });


                });
            }

            showModal(btnId);
        }
    });
}

function showModal(btnId) {
    if (isOpenModalGlobal) {
        $('#viewBankInfo').modal('show');
    } else {
        alert("No data found");
    }

    $(btnId).prop('disabled', false);
    $(btnId).html(openUserConfigBtnHtml);
    isUserConfigAjax = false;
}