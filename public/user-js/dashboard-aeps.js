"use strict";

var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
}

var mode = 'index';
var intersect = true;


var isTabClicked = false;

var globalAepsUserId = 0;
var globalAepsStartDate = '';
var globalAepsEndDate = '';
var globalAepsFilterType = 'Today';
var glbalDrawAepsTransactions = Array();
var globalDrawAepsCounts = Array();
var globalShowChart = 'amount';


$(function () {

    $('.btnShowHide').on('click', function () {
        let show = $(this).attr('data-show');
        if (show === 'amount') {
            drawAepsTransactions(glbalDrawAepsTransactions);
        } else if (show === 'count') {
            drawAepsCounts(globalDrawAepsCounts);
        }
        globalShowChart = show;
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
    }, getAepsDashboardGraph);

    getAepsDashboardGraph(start, end, 'Today');
});

function getAepsDashboardGraph(start, end, type) {
    showChartOverlay('#aepsTxnOverlay');
    showChartOverlay('#aepsCountOverlay');
    showChartOverlay('#aepsMrcBoardOverlay');
    showChartOverlay('.bankVolumeOverlay');

    globalAepsFilterType = type;
    $('#select-date-range span').html(globalAepsFilterType);

    globalAepsStartDate = start;
    globalAepsEndDate = end;

    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: globalAepsStartDate.format('YYYY-MM-DD'),
        endDate: globalAepsEndDate.format('YYYY-MM-DD'),
    };

    $.post($('meta[name="base-url"]').attr('content') + '/aeps/card-data', jsonData, function (response) {
        if (response.code === '0x0200') {
            $('#currentBusiness').text("₹" + response.data.amount);
            $('#activeMerchant').text((response.data.merchant));
            $('#commissionAmount').text("₹" + (response.data.commissionAmount));
        } else {
            $('#currentBusiness').text('₹0');
            $('#activeMerchant').text('0');
            $('#commissionCount').text('0');
            $('#commissionAmount').text('0');
        }

        $.post(aepsGraphsUrl + '/transaction', jsonData, function (response) {
            if (response.code === '0x0200') {

                if (globalShowChart === 'amount')
                    drawAepsTransactions(response);

                glbalDrawAepsTransactions = response;
                hideChartOverlay('#aepsTxnOverlay');
            } else {
                console.log(response);
            }

            $.post(aepsGraphsUrl + '/txn-counts', jsonData, function (response) {
                if (response.code === '0x0200') {

                    if (globalShowChart === 'count')
                        drawAepsCounts(response);

                    globalDrawAepsCounts = response;
                    hideChartOverlay('#aepsCountOverlay');
                } else {
                    console.log(response);
                }

                //bank volume
                $.post(aepsGraphsUrl + '/bank-volume', jsonData, function (response) {
                    if (response.code === '0x0200') {
                        drawAepsBankVolume(response);
                        hideChartOverlay('.bankVolumeOverlay');
                    } else {
                        console.log(response);
                    }

                    $.post(aepsGraphsUrl + '/top-merchant', jsonData, function (response) {
                        if (response.code === '0x0200') {
                            topMerchants(response);
                            // hideChartOverlay('.bankVolumeOverlay');
                        } else {
                            console.log(response);
                        }
                    });
                });
            });

        });

    });

}

function drawAepsTransactions(response) {
    let labels = Array();
    let labelStamp = Array();
    let showGraph = true;

    let cwAepsSuccessTot = 0;

    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });


    let cwAepsSuccess = Array();


    if (response.data.cwAepsData.length > 0) {
        response.data.cwAepsData.forEach((obj, idx) => {

            switch (obj.status) {
                case 'success':
                    cwAepsSuccess.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt
                    });

                    cwAepsSuccessTot += parseFloat(obj.totAmt);
                    break;
            }

        });
    }


    /**
     * ==============================
     * AEPS Transactions CW
     * ============================== 
     */
    if (cwAepsSuccess.length > 0) {

        labels.forEach((obj, idx) => {
            if (cwAepsSuccess.filter(e => e.x === obj).length == 0) {
                cwAepsSuccess.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

    } else {
        labels.forEach((obj, idx) => {
            cwAepsSuccess.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }


    if (showGraph) {
        cwAepsSuccess.sort(dynamicSort("z"));


        $('#aepsTxnChart').html(`<div class="position-relative mb-4">
            <canvas id="aepsTxnChartCanvas" height="200"></canvas>
        </div>`);

        $('#aepsAmount').html(`<div class="col-sm-6">
                <div class="d-flex flex-row justify-content-start">
                    <span class="ml-2 font-weight-bolder" id="totalTxnApesChart"></span>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="d-flex flex-row justify-content-end">
                    <span class="mr-2">
                        <i class="fas fa-square text-success bg-success"></i> Withdraw
                    </span>
                </div>
            </div>`);

        $('#totalTxnApesChart').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(cwAepsSuccessTot)}`);

        let aepsTxnChartCanvas = $('#aepsTxnChartCanvas');

        new Chart(aepsTxnChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: '₹ ',
                        data: cwAepsSuccess,
                        backgroundColor: 'transparent',
                        borderColor: colorList.success,
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
                                return '₹ ' + value;
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


function drawAepsBankVolume(response) {

    /**
     * ========================
     * Doughnut Charts
     * Volume By BANK
     * ========================
     */

    let cwBankSuccessX = Array();
    let cwBankSuccessY = Array();
    let cwBankSuccessColors = Array();
    let cwBankSuccessTot = 0;
    let bColor = '';


    if (response.data.volumeBankData.length > 0) {
        response.data.volumeBankData.forEach((obj, idx) => {

            switch (obj.status) {
                case 'success':
                    cwBankSuccessX.push(obj.bank);
                    cwBankSuccessY.push(obj.totAmt);

                    bColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
                    while (cwBankSuccessColors.includes(bColor) || bColor.length != 7) {
                        bColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
                    }
                    cwBankSuccessColors.push(bColor);

                    cwBankSuccessTot += parseFloat(obj.totAmt);
                    break;

            }

        });
    }

    $('#cwAepsSuccessDoughnut').html(`<canvas id="cwAepsSuccessDoughnutCanvas" style="width:100%;"></canvas>
        <div class="inside-donut-chart-label"><strong id="cwBankSuccessTot" style="font-size: 18px;">0</strong></div>`);

    if (cwBankSuccessX.length > 0) {

        new Chart("cwAepsSuccessDoughnutCanvas", {
            type: "doughnut",
            data: {
                labels: cwBankSuccessX,
                datasets: [{
                    backgroundColor: cwBankSuccessColors,
                    data: cwBankSuccessY
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                legend: {
                    display: false
                },
                title: {
                    display: false,
                    text: ""
                }
            }
        });
    }
    $('#cwBankSuccessTot').text('₹' + changeNumberFormat(cwBankSuccessTot));

}


function topMerchants(response) {
    $('#topMerchants').html('');

    let tbldata = '';

    if (response.data.topMerchant.length > 0) {

        response.data.topMerchant.forEach((obj, idx) => {
            let fullName = obj.first_name;

            if (obj.middle_name != null && obj.middle_name != '') {
                fullName += ' ' + obj.middle_name;
            }

            if (obj.last_name != null && obj.last_name != '') {
                fullName += ' ' + obj.last_name;
            }

            let fullAmt = `<span data-placement="top" data-toggle="tooltip" type="button" data-original-title="₹ ${parseFloat(obj.totAmt).toFixed(2)}">₹${changeNumberFormat(parseFloat(obj.totAmt).toFixed(2))}</span>`;
            tbldata += `<tr><td>${(idx + 1)}</td><td>${obj.merchant}</td><td>${fullName}</td><td>${obj.mobile}</td><td>${obj.email}</td><td>${fullAmt}</td></tr>`;
        });

    } else {
        tbldata = `<tr><td colspan="6" class="text-center">No record Found</td></tr>`;
    }

    $('#topMerchants').html(tbldata);
    $('[data-toggle="tooltip"]').tooltip();

}


function drawAepsCounts(response) {
    let labels = Array();
    let labelStamp = Array();
    let msAepsCount = Array();
    let cwAepsCount = Array();
    let beAepsCount = Array();
    let msAepsCountTot = 0;
    let cwAepsCountTot = 0;
    let beAepsCountTot = 0;

    let showGraph = true;

    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });


    if (response.data.aepsCountData.length > 0) {
        response.data.aepsCountData.forEach((obj, idx) => {

            switch (obj.transaction_type) {
                case 'cw':
                    cwAepsCount.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    cwAepsCountTot += parseInt(obj.totCount);
                    break;
                case 'ms':
                    msAepsCount.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    msAepsCountTot += parseInt(obj.totCount);
                    break;

                case 'be':
                    beAepsCount.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    beAepsCountTot += parseInt(obj.totCount);
                    break;
            }

        });
    }


    /**
     * ==============================
     * AEPS Counts
     * ============================== 
     */

    //AEPS CW Count
    if (cwAepsCount.length > 0) {

        labels.forEach((obj, idx) => {
            if (cwAepsCount.filter(e => e.x === obj).length == 0) {
                cwAepsCount.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });
    } else {
        labels.forEach((obj, idx) => {
            cwAepsCount.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }


    //AEPS MS Count
    if (msAepsCount.length > 0) {

        labels.forEach((obj, idx) => {
            if (msAepsCount.filter(e => e.x === obj).length == 0) {
                msAepsCount.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

    } else {
        labels.forEach((obj, idx) => {
            msAepsCount.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });

    }


    //AEPS BE Count
    if (beAepsCount.length > 0) {

        labels.forEach((obj, idx) => {
            if (beAepsCount.filter(e => e.x === obj).length == 0) {
                beAepsCount.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

    } else {
        labels.forEach((obj, idx) => {
            beAepsCount.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }

    if (showGraph) {
        cwAepsCount.sort(dynamicSort("z"));
        msAepsCount.sort(dynamicSort("z"));
        beAepsCount.sort(dynamicSort("z"));


        $('#aepsTxnChart').html(`<div class="position-relative mb-4">
            <canvas id="aepsTxnChartCanvas" height="200"></canvas>
        </div>`);

        $('#aepsAmount').html(`<div class="col-sm-6">
            <div class="d-flex flex-row justify-content-start">
                <span class="ml-2 font-weight-bolder" id="aepsCountChartTotal"></span>
            </div>
            </div>
            <div class="col-sm-6">
                <div class="d-flex flex-row justify-content-end">
                    <span class="mr-2">
                        <i class="fas fa-square text-success bg-success"></i> Withdraw
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-square text-danger bg-danger"></i> Balance Enq.
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-square text-primary bg-primary"></i> Statement
                    </span>
                </div>
            </div>`);

        $('#aepsCountChartTotal').html(`<i class="fas fa-square text-success bg-success"></i> ${changeNumberFormat(cwAepsCountTot)}
            &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ${changeNumberFormat(beAepsCountTot)}
            &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ${changeNumberFormat(msAepsCountTot)}`);

        let aepsCountChartCanvas = $('#aepsTxnChartCanvas');

        new Chart(aepsCountChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'CW',
                        data: cwAepsCount,
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
                        label: 'BE',
                        data: beAepsCount,
                        backgroundColor: 'transparent',
                        borderColor: colorList.danger,
                        pointBorderColor: '#3E4B5B',
                        pointBackgroundColor: '#3E4B5B',
                        fill: true,
                        pointHoverBackgroundColor: '#007bff',
                        pointHoverBorderColor: '#007bff'
                    },
                    {
                        type: 'line',
                        label: 'MS',
                        data: msAepsCount,
                        backgroundColor: 'transparent',
                        borderColor: colorList.primary,
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
                                return value
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