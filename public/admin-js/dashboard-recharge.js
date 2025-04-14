"use strict";

var isRechargeTabClicked = false;

var globalAepsUserId = 0;
var globalAepsStartDate = '';
var globalAepsEndDate = '';
var globalAepsFilterType = 'Today';


$(function () {
    var start = moment();
    var end = moment();

    $('#tabRecharge').on('click', function () {
        if (!isRechargeTabClicked) {
            isRechargeTabClicked = true;
            getRechargeDashboardGraph(start, end, 'Today');
        }
    });

    $('#recharge-date-range').daterangepicker({
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
    }, getRechargeDashboardGraph);

    $('select#recharge-user-id').on('change', function () {
        globalAepsUserId = $(this).val();
        getRechargeDashboardGraph(globalAepsStartDate, globalAepsEndDate, globalAepsFilterType);
    });
});

function getRechargeDashboardGraph(start, end, type) {
    showChartOverlay('#rechargeTxnOverlay');
    showChartOverlay('.bankVolumeOverlay');


    globalAepsFilterType = type;
    $('#recharge-date-range span').html(globalAepsFilterType);

    globalAepsStartDate = start;
    globalAepsEndDate = end;

    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: globalAepsStartDate.format('YYYY-MM-DD'),
        endDate: globalAepsEndDate.format('YYYY-MM-DD'),
        userId: globalAepsUserId,
    };


    $.post($('#rechargeGraphs').val() + '/transaction', jsonData, function (response) {
        if (response.code === '0x0200') {
            drawRechargeTransactions(response);
            hideChartOverlay('#rechargeTxnOverlay');
        } else {
            console.log(response);
        }


        //bank volume
        $.post($('#rechargeGraphs').val() + '/recharge-type', jsonData, function (response) {
            if (response.code === '0x0200') {
                drawRechargeTypeVolume(response);
                hideChartOverlay('.bankVolumeOverlay');
            } else {
                console.log(response);
            }

        });
    });

}

function drawRechargeTransactions(response) {
    let labels = Array();
    let labelStamp = Array();
    let showGraph = true;

    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });


    let cwAepsSuccess = Array();
    let cwAepsFailed = Array();
    let cwAepsSuccessTot = 0;
    let cwAepsFailedTot = 0;


    if (response.data.cwAepsData.length > 0) {
        response.data.cwAepsData.forEach((obj, idx) => {

            switch (obj.status) {
                case 'processed':
                    cwAepsSuccess.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt
                    });

                    cwAepsSuccessTot += parseFloat(obj.totAmt);
                    break;
                case 'failed':
                    cwAepsFailed.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt
                    });

                    cwAepsFailedTot += parseFloat(obj.totAmt);
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

    if (cwAepsFailed.length > 0) {

        labels.forEach((obj, idx) => {
            if (cwAepsFailed.filter(e => e.x === obj).length == 0) {
                cwAepsFailed.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

    } else {
        labels.forEach((obj, idx) => {
            cwAepsFailed.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }


    $('#totalTxnRechargeChart').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(cwAepsSuccessTot)}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ₹ ${changeNumberFormat(cwAepsFailedTot)}`);

    if (showGraph) {
        cwAepsSuccess.sort(dynamicSort("z"));
        cwAepsFailed.sort(dynamicSort("z"));


        $('#rechargeTxnChart').html(`<div class="position-relative mb-4">
            <canvas id="rechargeTxnChartCanvas" height="260"></canvas>
        </div>`);

        let rechargeTxnChartCanvas = $('#rechargeTxnChartCanvas');

        new Chart(rechargeTxnChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Success ₹ ',
                        data: cwAepsSuccess,
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
                        label: 'Failed ₹ ',
                        data: cwAepsFailed,
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


function drawRechargeTypeVolume(response) {

    /**
     * ========================
     * Doughnut Charts
     * Volume By BANK
     * ========================
     */

    let cwBankSuccessX = Array();
    let cwBankSuccessY = Array();
    let cwBankSuccessColors = Array();
    let rechargeSuccessTot = 0;

    let cwBankFailedX = Array();
    let cwBankFailedY = Array();
    let cwBankFailedColors = Array();
    let rechargeFailedTot = 0;
    let bColor = '';


    if (response.data.rechargeTypeData.length > 0) {
        response.data.rechargeTypeData.forEach((obj, idx) => {

            switch (obj.status) {
                case 'processed':
                    cwBankSuccessX.push(obj.type);
                    cwBankSuccessY.push(obj.totAmt);

                    bColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
                    while (cwBankSuccessColors.includes(bColor) || bColor.length != 7) {
                        bColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
                    }
                    cwBankSuccessColors.push(bColor);

                    rechargeSuccessTot += parseFloat(obj.totAmt);
                    break;

                case 'failed':
                    cwBankFailedX.push(obj.type);
                    cwBankFailedY.push(obj.totAmt);

                    bColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
                    while (cwBankSuccessColors.includes(bColor) || bColor.length != 7) {
                        bColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
                    }
                    cwBankFailedColors.push(bColor);

                    rechargeFailedTot += parseFloat(obj.totAmt);
                    break;
            }

        });
    }


    // console.log(cwBankFailedColors, cwBankSuccessColors);

    $('#rechargeSuccessDoughnut').html(`<canvas id="rechargeSuccessDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
        <div class="inside-donut-chart-label"><strong id="rechargeSuccessTot" style="font-size: 18px;">0</strong></div>`);

    if (cwBankSuccessX.length > 0) {

        new Chart("rechargeSuccessDoughnutCanvas", {
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
    $('#rechargeSuccessTot').text('₹' + changeNumberFormat(rechargeSuccessTot));




    $('#rechargeFailedDoughnut').html(`<canvas id="rechargeFailedDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
        <div class="inside-donut-chart-label"><strong id="rechargeFailedTot" style="font-size: 18px;">0</strong></div>`);

    if (cwBankFailedX.length > 0) {

        new Chart("rechargeFailedDoughnutCanvas", {
            type: "doughnut",
            data: {
                labels: cwBankFailedX,
                datasets: [{
                    backgroundColor: cwBankFailedColors,
                    data: cwBankFailedY
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
    $('#rechargeFailedTot').text('₹' + changeNumberFormat(rechargeFailedTot));

}