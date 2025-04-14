"use strict";

var colorList = {
    green: '#4ba512',
    pink: '#e955dd',
    cyan: '#41868e',
    brown: '#856733',
    orange: '#e86238',
    blue: '#2a50b6',
    red: '#b72322',
    purple: '#652ad4',
    purple2: '#5f1b9f',
    darkGreen: '#355537',
    yellow: '#c28621',
    success: '#24b314',
    primary: '#047bf8',
    danger: '#b71b1b',
    dark: '#343a40',
};

var chartColors = {
    aepsRoot: {
        PAYTM: colorList.green,
        AIRTEL: colorList.orange,
        SBM: colorList.purple2,
        ICICI: colorList.blue,
    },
    payoutMode: {
        NEFT: colorList.brown,
        IMPS: colorList.orange,
        RTGS: colorList.blue,
        UPI: colorList.darkGreen,
    },
    payoutArea: {
        API: colorList.brown,
        WEB: colorList.yellow,
    }
};

function hideChartOverlay(ele) {
    $(ele).addClass('d-none');
}

function showChartOverlay(ele) {
    $(ele).removeClass('d-none');
}

var tabTopUsersClicked = false;

$('#tabTopUsers').on('click', function () {
    if (!tabTopUsersClicked) {
        tabTopUsersClicked = true;

        var url = $('#dashboardUsersAmount').val();
        var onDraw = function () { };
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
            "data": null,
            "orderable": false,
            render: function (data, type, full, meta) {
                return `${full.name} <br>[${full.email}]`;
            }

        },
        {
            "data": "business_name",
            "orderable": false
        },
        {
            "data": null,
            render: function (data, type, full, meta) {
                var totalAmount = 0;

                if (full.tot_amount) {
                    totalAmount += full.tot_amount;
                }
                if (full.payout_amt) {
                    totalAmount += full.payout_amt;
                }
                if (full.dmt_amt) {
                    totalAmount += full.dmt_amt;
                }
                if (full.validate_amt) {
                    totalAmount += full.validate_amt;
                }
                if (full.recharge_amt) {
                    totalAmount += full.recharge_amt;
                }

                return `<span class="d-block text-right">${numberWithCommas(totalAmount.toFixed(2))}</span>`;
            }
        },
        {
            "data": "tot_amount", //primary ammount
            render: function (data, type, full, meta) {
                return `<span class="d-block text-right">${(data !== null && data !== '') ? numberWithCommas(data.toFixed(2)) : 0}</span>`;
            }
        },
        {
            "data": "payout_amt",
            render: function (data, type, full, meta) {
                return `<span class="d-block text-right">${(data !== null && data !== '') ? numberWithCommas(data.toFixed(2)) : 0}</span>`;
            }
        },
        {
            "data": "dmt_amt",
            render: function (data, type, full, meta) {
                return `<span class="d-block text-right">${(data !== null && data !== '') ? numberWithCommas(data.toFixed(2)) : 0}</span>`;
            }
        },
        {
            "data": "recharge_amt",
            render: function (data, type, full, meta) {
                return `<span class="d-block text-right">${(data !== null && data !== '') ? numberWithCommas(data.toFixed(2)) : 0}</span>`;
            }
        },
        {
            "data": "validate_amt",
            render: function (data, type, full, meta) {
                return `<span class="d-block text-right">${(data !== null && data !== '') ? numberWithCommas(data.toFixed(2)) : 0}</span>`;
            }
        }
        ];
        datatableSetup(url, options, onDraw);

    }
});

var tabLastTxnClicked = false;

$('#tabLastTxn').on('click', function () {
    if (!tabLastTxnClicked) {
        tabLastTxnClicked = true;
        // let jsonData = {
        //     _token: $('meta[name="csrf-token"]').attr('content')
        // };

        // fetchActiveTransaction(jsonData);

        var url = $('meta[name="base-url"]').attr('content') + '/admin/dashboard/active-user';
        var onDraw = function () { };
        var options = [
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
                orderable: false,
                "data": "name",
            },
            {
                orderable: false,
                "data": "email",
            },
            {
                orderable: false,
                "data": "mobile",
            },
            {
                orderable: false,
                "data": "txndate",
            },
            {
                orderable: false,
                "data": "service",
                render: function (data, type, full, meta) {
                    if (data == '' || data == null || data == undefined) {
                        return '';
                    }

                    return serviceSpan(data, full);
                }
            }
        ];
        datatableSetup(url, options, onDraw, '#dt-last_txn');
    }
});

$('#tabltstSignup').on('click', function () {
    if (!tabLastTxnClicked) {
        tabLastTxnClicked = true;
        // let jsonData = {
        //     _token: $('meta[name="csrf-token"]').attr('content')
        // };

        // fetchActiveTransaction(jsonData);

        var url = $('meta[name="base-url"]').attr('content') + '/admin/dashboard/latest-user';
        var onDraw = function () { };
        var options = [
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
                orderable: false,
                "data": "name",
            },
            {
                orderable: false,
                "data": "email",
            },
            {
                orderable: false,
                "data": "mobile",
            },
            {
                orderable: false,
                "data": "is_profile_updated",
                render: function (data, type, full) {
                    if (data == '1') {
                        return '<span class="badge badge-success">Updated</span>';
                    } else {
                        return '<span class="badge badge-warning">Not Updated</span>';
                    }
                }
            },
            {
                orderable: false,
                "data": "created_at",
            }
        ];
        datatableSetup(url, options, onDraw, '#dt-lst_signup');
    }
});

var getServiceInfoAjax = false;


var globalStartDate, globalEndDate, globalType;


$('#dt-last_txn').on('click', '.getServiceInfo', function () {

    var thisHtml = $(this).html();
    $(this).prop('disabled', true);
    $(this).html('Loading...');

    var service = $(this).attr('data-service')

    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        rowNum: $(this).attr('data-row'),
        date: $(this).attr('data-date'),
        service: $(this).attr('data-service')
    };

    if (!getServiceInfoAjax) {
        getServiceInfoAjax = true;
        $.post($('meta[name="base-url"]').attr('content') + '/admin/dashboard/active-user/txn-details',
            jsonData, (response) => {
                if (response.code === '0x0200') {

                    $(this).prop('disabled', false);
                    $(this).html(thisHtml);
                    getServiceInfoAjax = false;

                    if (response.data === null || response.data === '' || response.data === undefined) {
                        return false;
                    }

                    switch (service) {
                        case 'payout':
                            showPayoutInfo(response, 'Payout');
                            break;

                        case 'aeps':
                            showAepsInfo(response);
                            break;

                        case 'upi_stack':
                            showUpiInfo(response, 'UPI Stack');
                            break;

                        case 'smart_collect':
                            showSmartCollectInfo(response);
                            break;

                        case 'upi_tpv':
                            showUpiInfo(response, 'UPI TPV');
                            break;

                        case 'validation':
                            return showValidateInfo(response);

                        case 'recharge':
                            return showPayoutInfo(response, 'Recharge');

                        case 'dmt':
                            return showPayoutInfo(response, 'Money Transfer');

                        case 'matm':
                            return showPayoutInfo(response, 'Micro ATM');

                        case 'pan':
                            return showPayoutInfo(response, 'PAN Card');

                        default:
                            return '';
                    }

                } else {
                    console.log(response);
                }
            });
    }

});


const showPayoutInfo = function (response, title) {

    var tableData = `
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Status</th>
                    <th>Txn Counts</th>
                    <th class="text-right">Total Amount (&#8377;)</th>
                </tr>
            </thead>
            <tbody>`;

    response.data.forEach((row, idx) => {
        tableData += `
                <tr>
                    <td>${++idx}</td>
                    <td>${showTxnStatus(row.status)}</td>
                    <td>${row.txnCounts}</td>
                    <td class="text-right">${row.sumAmt}</td>
                </tr>
        `;
    });

    tableData += `</tbody>`;

    showModal(tableData, title);
}

const showAepsInfo = function (response) {

    var tableData = `
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Txn Type</th>
                    <th>Status</th>
                    <th>Txn Counts</th>
                    <th class="text-right">Amount (&#8377;)</th>
                </tr>
            </thead>
            <tbody>`;

    response.data.forEach((row, idx) => {
        tableData += `
                <tr>
                    <td>${++idx}</td>
                    <td>${row.transaction_type.toUpperCase()}</td>
                    <td>${showTxnStatus(row.status)}</td>
                    <td>${row.txnCounts}</td>
                    <td class="text-right">${(row.sumAmt) ? row.sumAmt : ''}</td>
                </tr>
        `;
    });

    tableData += `</tbody>`;

    showModal(tableData, 'AEPS');
}

const showUpiInfo = function (response, title) {

    var tableData = `
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Txn Counts</th>
                    <th class="text-right">Amount (&#8377;)</th>
                </tr>
            </thead>
            <tbody>`;

    response.data.forEach((row, idx) => {
        tableData += `
            <tr>
                <td>${++idx}</td>
                <td>${row.txnCounts}</td>
                <td class="text-right">${row.sumAmt}</td>
            </tr>
                `;
    });

    tableData += `</tbody>`;

    showModal(tableData, title);
};

const showSmartCollectInfo = function (response) {

    var tableData = `
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Collection Type</th>
                    <th>Txn Counts</th>
                    <th class="text-right">Amount (&#8377;)</th>
                </tr>
            </thead>
            <tbody>`;

    response.data.forEach((row, idx) => {
        tableData += `
            <tr>
                <td>${++idx}</td>
                <td>${(row.is_vpa === '1') ? 'VPA' : 'VAN'}</td>
                <td>${row.txnCounts}</td>
                <td class="text-right">${row.sumAmt}</td>
            </tr>
                `;
    });

    tableData += `</tbody>`;

    showModal(tableData, 'Smart Collect');
};

const showValidateInfo = function (response) {

    var tableData = `
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Validation Type</th>
                    <th>Txn Counts</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>`;

    response.data.forEach((row, idx) => {
        tableData += `
            <tr>
                <td>${++idx}</td>
                <td>${row?.type.toUpperCase()}</td>
                <td>${row.txnCounts}</td>
                <td>${showTxnStatus(row.status)}</td>
            </tr>`;
    });

    tableData += `</tbody>`;

    showModal(tableData, 'Validation Suite');
};

$('#formReset').on('click', function () {
    $('form#searchForm')[0].reset();
    $('form#latestUserForm')[0].reset();
    $('#formReset').button('loading');
    // $(this).find('select[name="user-lst_txn"]').val(null);
    $('select[name="user-lst_txn"]').val('0').trigger('change');
    $('#dt-last_txn').dataTable().api().ajax.reload();
});

$('form#searchForm').on('submit', function (e) {
    e.preventDefault();
    $('#searchForm').find('button:submit').button('loading');
    $('#dt-last_txn').dataTable().api().ajax.reload();
    return false;
});
$('form#latestUserForm').on('submit', function (e) {
    e.preventDefault();
    $('#latestUserForm').find('button:submit').button('loading');
    $('#dt-lst_signup').dataTable().api().ajax.reload();
    return false;
});

function datatableSetup(urls, datas, onDraw = function () { }, ele = "#datatable", element = {}) {
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
                d.fromDate = $('#searchForm').find('[name="date-lst_txn"]').val();
                d.toDate = $('#searchForm').find('[name="to_date-lst_txn"]').val();
                d.from = $('#latestUserForm').find('[name="date-from_lst_signup"]').val();
                d.to = $('#latestUserForm').find('[name="date-to_lst_signup"]').val();
                // d.to = $('#searchForm').find('[name="to"]').val();
                // d.searchText = $('#searchForm').find('[name="searchText"]').val();
                // d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                // d.status = $('#searchForm').find('[name="status"]').val();
                d.user_id = $('#searchForm').find('[name="user-lst_txn"]').val();
                d.service_id = $('#searchForm').find('[name="service-lst_txn"]').val();
            },
            beforeSend: function () { },
            complete: function () {
                $('#searchForm').find('button:submit').button('reset');
                $('#formReset').button('reset');
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

function dynamicSort(property) {
    var sortOrder = 1;
    if (property[0] === "-") {
        sortOrder = -1;
        property = property.substr(1);
    }
    return function (a, b) {
        /* next line works with strings and numbers, 
         * and you may want to customize it to your needs
         */
        var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
        return result * sortOrder;
    }
}

var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
}

var mode = 'index';
var intersect = true;

var globalUserId = 0;
var globalStartDate = '';
var globalEndDate = '';
var globalFilterType = 'Today';


$(function () {
    $('.select2').select2({
        containerCssClass: "xettle-select2"
    });

    $.get($('#dashboardBalances').val(), function (response) {
        // console.log(response);
        $('#primary-balance').html(response.primary);
        $('#primary-balance-actual').html(response.primaryActual);
        $('#payout-balance').html(response.payout);
        $('#payout-balance-actual').html(response.payoutActual);
        $('#order-queue').html(response.orderQueue);
        $('#order-queue-actual').html(`${response.orderQueueCount} | &#8377;${response.orderQueueActual}`);
        $('#order-process').html(response.orderProcess);
        $('#order-process-actual').html(`${response.orderProcessCount} | &#8377;${response.orderProcessActual}`);
    });

    var start = moment();
    var end = moment();

    $('#select-date-range').daterangepicker({
        maxSpan: {
            days: 30
        },
        showCustomRangeLabel: true,
        startDate: start,
        endDate: end,
        // opens: left,
        // alwaysShowCalendars: true,
        ranges: {
            'Today': [moment(), moment()],
            '7 Days': [moment().subtract(6, 'days'), moment()],
            '30 Days': [moment().subtract(29, 'days'), moment()]
        }
    }, getDashboardGraph);

    $('select#select-user-id').on('change', function () {
        globalUserId = $(this).val();
        getDashboardGraph(globalStartDate, globalEndDate, globalFilterType);
    });

    // setTimeout(function () {
    getDashboardGraph(start, end, 'Today');
    // }, 100);


    $('#select_daterange_fo').daterangepicker({
        maxSpan: {
            days: 30
        },
        showCustomRangeLabel: true,
        startDate: start,
        endDate: end,
        // opens: left,
        // alwaysShowCalendars: true,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 Days': [moment().subtract(6, 'days'), moment()],
            '30 Days': [moment().subtract(29, 'days'), moment()]
        }
    }, handleFinancialOverviewCards);

    handleFinancialOverviewCards(start, end, 'Today');

    $('#refresh_report').on('click', () => {
        handleFinancialOverviewCards(globalStartDate, globalEndDate, globalType);
    });
});


function handleFinancialOverviewCards(start, end, type) {

    $('#xtl_loader_container').removeClass('d-none');

    setTimeout(function () {
        $('#xtl_loader_container').addClass('d-none');
    }, 1000);

    globalStartDate = start;
    globalEndDate = end;
    globalType = type;

    const dateData = {
        fromDate: globalStartDate.format('YYYY-MM-DD'),
        toDate: globalEndDate.format('YYYY-MM-DD')
    };

    $('#select_daterange_fo span').html(globalType);

    getCardRecords('reseller/data/payout/processed', dateData);
    getCardRecords('reseller/data/payout/processing', dateData);
    getCardRecords('reseller/data/payout/failed', dateData);

    getCardRecords('reseller/data/upi/success', dateData);
    getCardRecords('reseller/data/upi/pending', dateData);
    getCardRecords('reseller/data/upi/failed', dateData);

    getCardRecords('admin/data/validation/success', dateData);
    getCardRecords('admin/data/validation/pending', dateData);
    getCardRecords('admin/data/validation/failed', dateData);

    getCardRecords('admin/data/recharge/processed', dateData);
    getCardRecords('admin/data/recharge/processing', dateData);
    getCardRecords('admin/data/recharge/failed', dateData);

}


function getCardRecords(url, value) {

    $.ajax({
        url: $('meta[name="base-url"]').attr('content') + '/' + url,
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            fromDate: value?.fromDate || '',
            toDate: value?.toDate || '',
        },
        success: function (resp) {
            if (resp.data.status) {

                $(`#${resp.data.type}`).html('₹' + resp.data.totalAmount);

                if (resp.data?.totalCount) {
                    $(`#${resp.data.type}_count`).html(resp.data.totalCount);
                } else if (value) {
                    $(`#${resp.data.type}_count`).html(0);
                }
            }
        }
    });
}


function getDashboardGraph(start, end, type) {
    showChartOverlay('#payoutChartOverlay');
    showChartOverlay('.payoutModeOverlay');
    showChartOverlay('.payoutAreaOverlay');

    globalFilterType = type;
    $('#select-date-range span').html(globalFilterType);

    globalStartDate = start;
    globalEndDate = end;


    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: globalStartDate.format('YYYY-MM-DD'),
        endDate: globalEndDate.format('YYYY-MM-DD'),
        userId: globalUserId,
    };

    $.post($('#payoutGraphs').val() + '/transaction', jsonData, function (response) {
        if (response.code === '0x0200') {
            drawTransactionCharts(response);
            hideChartOverlay('#payoutChartOverlay');
        } else {
            console.log(response);
        }


        $.post($('#payoutGraphs').val() + '/mode', jsonData, function (response) {
            if (response.code === '0x0200') {
                drawModeCharts(response);
                hideChartOverlay('.payoutModeOverlay');
            } else {
                console.log(response);
            }


            $.post($('#payoutGraphs').val() + '/area', jsonData, function (response) {
                if (response.code === '0x0200') {
                    drawAreaCharts(response);
                    hideChartOverlay('.payoutAreaOverlay');
                } else {
                    console.log(response);
                }
            });
        });
    });

}


/**
 * Transaction
 */
function drawTransactionCharts(response) {
    let labels = Array();
    let labelStamp = Array();
    let payoutSuccess = Array();
    let payoutReversed = Array();
    let payoutProcessing = Array();
    let payoutFailed = Array();
    let totSuccessAmt = 0;
    let totFailedAmt = 0;
    let totReversedAmt = 0;
    let totProcessingAmt = 0;

    let showGraph = false;

    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });

    if (response.data.payoutData.length > 0) {
        response.data.payoutData.forEach((obj, idx) => {

            switch (obj.status) {
                case 'processed':
                    payoutSuccess.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt.toFixed(2)
                    });

                    totSuccessAmt += parseFloat(obj.totAmt.toFixed(2));
                    break;
                case 'failed':
                    payoutFailed.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt.toFixed(2)
                    });

                    totFailedAmt += parseFloat(obj.totAmt.toFixed(2));
                    break;
                case 'reversed':
                    payoutReversed.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt.toFixed(2)
                    });

                    totReversedAmt += parseFloat(obj.totAmt.toFixed(2));
                    break;
                case 'processing':
                    payoutProcessing.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt.toFixed(2)
                    });

                    totProcessingAmt += parseFloat(obj.totAmt.toFixed(2));
                    break;
            }

        });
    }

    if (payoutSuccess.length > 0) {

        labels.forEach((obj, idx) => {
            if (payoutSuccess.filter(e => e.x === obj).length == 0) {
                payoutSuccess.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

        showGraph = true;
    } else {
        labels.forEach((obj, idx) => {
            payoutSuccess.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });

        showGraph = true;
    }


    if (payoutFailed.length > 0) {

        labels.forEach((obj, idx) => {
            if (payoutFailed.filter(e => e.x === obj).length == 0) {
                payoutFailed.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

        showGraph = true;
    } else {
        labels.forEach((obj, idx) => {
            payoutFailed.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });

        showGraph = true;
    }


    if (payoutReversed.length > 0) {

        labels.forEach((obj, idx) => {
            if (payoutReversed.filter(e => e.x === obj).length == 0) {
                payoutReversed.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

        showGraph = true;
    } else {
        labels.forEach((obj, idx) => {
            payoutReversed.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });

        showGraph = true;
    }


    if (payoutProcessing.length > 0) {

        labels.forEach((obj, idx) => {
            if (payoutProcessing.filter(e => e.x === obj).length == 0) {
                payoutProcessing.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

        showGraph = true;
    } else {
        labels.forEach((obj, idx) => {
            payoutProcessing.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });

        showGraph = true;
    }



    $('#total-payout-chart').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(totSuccessAmt)}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ₹ ${changeNumberFormat(totFailedAmt)}
    &nbsp;<i class="fas fa-square text-dark bg-dark"></i> ₹ ${changeNumberFormat(totReversedAmt)}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ₹ ${changeNumberFormat(totProcessingAmt)}`);

    if (showGraph) {
        payoutSuccess.sort(dynamicSort("z"));
        payoutFailed.sort(dynamicSort("z"));
        payoutReversed.sort(dynamicSort("z"));
        payoutProcessing.sort(dynamicSort("z"));

        // console.log(payoutSuccess, payoutFailed);

        $('#payout-chart').html(`<div class="position-relative mb-4">
            <canvas id="payout-chart-canvas" height="260"></canvas>
        </div>`);

        var payoutChartCanvas = $('#payout-chart-canvas');
        var chart1 = new Chart(payoutChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Success ₹',
                        data: payoutSuccess,
                        backgroundColor: 'transparent',
                        borderColor: colorList.success,
                        pointBorderColor: '#3E4B5B',
                        pointBackgroundColor: '#3E4B5B',
                        fill: true,
                        pointHoverBackgroundColor: '#007bff',
                        pointHoverBorderColor: '#007bff'
                    },
                    {
                        type: 'line',
                        label: 'Reversed ₹',
                        data: payoutReversed,
                        backgroundColor: 'transparent',
                        borderColor: colorList.dark,
                        pointBorderColor: '#3E4B5B',
                        pointBackgroundColor: '#3E4B5B',
                        fill: true,
                        pointHoverBackgroundColor: '#007bff',
                        pointHoverBorderColor: '#007bff'
                    },
                    {
                        type: 'line',
                        label: 'Processing ₹',
                        data: payoutProcessing,
                        backgroundColor: 'transparent',
                        borderColor: colorList.primary,
                        pointBorderColor: '#3E4B5B',
                        pointBackgroundColor: '#3E4B5B',
                        fill: true,
                        pointHoverBackgroundColor: '#007bff',
                        pointHoverBorderColor: '#007bff'
                    },
                    {
                        type: 'line',
                        label: 'Failed ₹',
                        data: payoutFailed,
                        backgroundColor: 'transparent',
                        borderColor: colorList.danger,
                        pointBorderColor: '#3E4B5B',
                        pointBackgroundColor: '#3E4B5B',
                        fill: true,
                        pointHoverBackgroundColor: '#007bff',
                        pointHoverBorderColor: '#007bff'
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    mode: mode,
                    intersect: intersect
                },
                hover: {
                    mode: mode,
                    intersect: intersect
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        // display: false,
                        gridLines: {
                            display: true,
                            // lineWidth: '4px',
                            color: '#ebebeb',
                            zeroLineColor: 'transparent'
                        },
                        ticks: $.extend({
                            beginAtZero: true,
                            fontSize: 11,
                            // suggestedMax: 200,
                            callback: function (value) {
                                if (value >= 1000) {
                                    value /= 1000
                                    value += 'k'
                                }
                                return '₹' + value
                            }
                        }, ticksStyle)
                    }],
                    xAxes: [{
                        display: true,
                        gridLines: {
                            display: true,
                            // lineWidth: '4px',
                            color: '#ebebeb',
                            zeroLineColor: 'transparent'
                        },
                        ticks: $.extend({
                            beginAtZero: true,
                            fontSize: 10,
                        }, ticksStyle)
                    }]
                }
            }
        });
    }

}



/**
 * Mode
 */
function drawModeCharts(response) {

    let xValuesSuccess = Array();
    let yValuesSuccess = Array();
    let barColorsSuccess = Array();
    let totPayoutModeSuccess = 0;

    let xValuesFailed = Array();
    let yValuesFailed = Array();
    let barColorsFailed = Array();
    let totPayoutModeFailed = 0;

    $('#payoutModeSuccess').html(`<canvas id="payoutModeSuccessCanvas" style="width:100%;max-width:600px"></canvas><div class="inside-donut-chart-label"><strong id="totPayoutModeSuccess" style="font-size: 18px;">0</strong></div>`);

    if (response.data.payoutModeData.length > 0) {
        response.data.payoutModeData.forEach((obj, idx) => {

            switch (obj.status) {
                case 'processed':
                    xValuesSuccess.push(obj.mode);
                    yValuesSuccess.push(obj.totAmt.toFixed(2));
                    barColorsSuccess.push(chartColors.payoutMode[obj.mode.toUpperCase()]);
                    totPayoutModeSuccess += parseFloat(obj.totAmt.toFixed(2));
                    break;
                case 'failed':
                    xValuesFailed.push(obj.mode);
                    yValuesFailed.push(obj.totAmt.toFixed(2));
                    barColorsFailed.push(chartColors.payoutMode[obj.mode.toUpperCase()]);
                    totPayoutModeFailed += parseFloat(obj.totAmt.toFixed(2));
                    break;
            }

        });
    }

    if (xValuesSuccess.length > 0) {

        new Chart("payoutModeSuccessCanvas", {
            type: "doughnut",
            data: {
                labels: xValuesSuccess,
                datasets: [{
                    backgroundColor: barColorsSuccess,
                    data: yValuesSuccess
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                title: {
                    display: false,
                    text: ""
                }
            }
        });
    }
    $('#totPayoutModeSuccess').text('₹' + changeNumberFormat(totPayoutModeSuccess));


    /**
     * Mode Failed
     */
    $('#payoutModeFailed').html(`<canvas id="payoutModeFailedCanvas" style="width:100%;max-width:600px"></canvas><div class="inside-donut-chart-label"><strong id="totPayoutModeFailed" style="font-size: 18px;">0</strong></div>`);

    if (xValuesFailed.length > 0) {

        new Chart("payoutModeFailedCanvas", {
            type: "doughnut",
            data: {
                labels: xValuesFailed,
                datasets: [{
                    backgroundColor: barColorsFailed,
                    data: yValuesFailed
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                title: {
                    display: false,
                    text: ""
                }
            }
        });
    }
    $('#totPayoutModeFailed').text('₹' + changeNumberFormat(totPayoutModeFailed));

}


/**
 * Area
 */
function drawAreaCharts(response) {

    let xValuesSuccess = Array();
    let yValuesSuccess = Array();
    let barColorsSuccess = Array();
    let totPayoutSuccess = 0;

    let xValuesFailed = Array();
    let yValuesFailed = Array();
    let barColorsFailed = Array();
    let totPayoutFailed = 0;

    if (response.data.payoutAreaData.length > 0) {
        response.data.payoutAreaData.forEach((obj, idx) => {

            switch (obj.status) {
                case 'processed':
                    if (obj.area == '00' || obj.area == "") {
                        xValuesSuccess.push("WEB");
                        barColorsSuccess.push(chartColors.payoutArea['WEB']);
                    }
                    else {
                        xValuesSuccess.push("API");
                        barColorsSuccess.push(chartColors.payoutArea['API']);
                    }
                    yValuesSuccess.push(obj.totAmt.toFixed(2));
                    totPayoutSuccess += parseFloat(obj.totAmt.toFixed(2));
                    break;
                case 'failed':
                    if (obj.area == '00' || obj.area == "") {
                        xValuesFailed.push("WEB");
                        barColorsFailed.push(chartColors.payoutArea['WEB']);
                    }
                    else {
                        xValuesFailed.push("API");
                        barColorsFailed.push(chartColors.payoutArea['API']);
                    }
                    yValuesFailed.push(obj.totAmt.toFixed(2));
                    totPayoutFailed += parseFloat(obj.totAmt.toFixed(2));
                    break;
            }

        });
    }

    $('#payoutAreaSuccess').html(`<canvas id="payoutAreaSuccessCanvas" style="width:100%;max-width:600px"></canvas><div class="inside-donut-chart-label"><strong id="totPayoutAreaSuccess" style="font-size: 18px;">0</strong></div>`);

    if (xValuesSuccess.length > 0) {

        new Chart("payoutAreaSuccessCanvas", {
            type: "doughnut",
            data: {
                labels: xValuesSuccess,
                datasets: [{
                    backgroundColor: barColorsSuccess,
                    data: yValuesSuccess
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                title: {
                    display: false,
                    text: ""
                }
            }
        });
    }
    $('#totPayoutAreaSuccess').text('₹' + changeNumberFormat(totPayoutSuccess));


    /**
     * Area Failed
     */
    $('#payoutAreaFailed').html(`<canvas id="payoutAreaFailedCanvas" style="width:100%;max-width:600px"></canvas><div class="inside-donut-chart-label"><strong id="totPayoutAreaFailed" style="font-size: 18px;">0</strong></div>`);

    if (xValuesFailed.length > 0) {

        new Chart("payoutAreaFailedCanvas", {
            type: "doughnut",
            data: {
                labels: xValuesFailed,
                datasets: [{
                    backgroundColor: barColorsFailed,
                    data: yValuesFailed
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                title: {
                    display: false,
                    text: ""
                }
            }
        });
    }
    $('#totPayoutAreaFailed').text('₹' + changeNumberFormat(totPayoutFailed));

}


function serviceSpan(type, obj) {
    switch (type) {
        case 'payout':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-success showPayoutInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">Payout</span>`;

        case 'aeps':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-dark showAepsInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">AEPS</span>`;

        case 'upi_stack':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-primary showUpiInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">UPI Stack</span>`;

        case 'smart_collect':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-warning showUpiInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">Smart Collect</span>`;

        case 'upi_tpv':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-info showUpiInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">UPI TPV</span>`;

        case 'validation':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-secondary showValidateInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">Validation Suite</span>`;

        case 'recharge':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-warning showRechargeInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">Recharge</span>`;

        case 'dmt':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-info showDmtInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">DMT</span>`;

        case 'matm':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-primary showMatmInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">MATM</span>`;

        case 'pan':
            return `<span class="btn btn-sm mt-1 getServiceInfo btn-secondary showPanInfo" data-row="${obj.user_id}" data-date="${obj.txndate}" data-service="${obj.service}">PAN</span>`;

        default:
            return '';
    }
}

function showModal(tableData, title = "") {
    $('#modalShowTxnInfo').on('show.bs.modal', () => {
        $('#modalTitleSpan').html(title)
        $('#modalTable').html(tableData);
    });
    $("#modalShowTxnInfo").modal("show");

    $("#modalShowTxnInfo").on('hidden.bs.modal', function () {
        $('#modalTable').html('');
    });
}


function showTxnStatus(status) {

    let badge = '';

    switch (status) {
        case 'processed':
        case 'success':
        case 'approved':
        case 'valid':
        case 'found':
            badge = 'success';
            break;

        case 'queued':
        case 'hold':
            badge = 'primary';
            break;

        case 'processing':
        case 'pending':
            badge = 'warning';
            break;

        case 'cancelled':
        case 'failed':
        case 'disputed':
        case 'rejected':
        case 'invalid':
        case 'not found':
            badge = 'danger';
            break;

        case 'reversed':
            badge = 'dark';
            break;
    }

    return `<span class="badge badge-${badge}">${status.capitalize()}</span>`;

}