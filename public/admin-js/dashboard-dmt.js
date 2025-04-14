"use strict";

var isDmtTabClicked = false;

var globalDmtUserId = 0;
var globalDmtStartDate = '';
var globalDmtEndDate = '';
var globalDmtFilterType = 'Today';


$(function () {
    var start = moment();
    var end = moment();

    $('#tabDmt').on('click', function () {
        if (!isDmtTabClicked) {
            isDmtTabClicked = true;
            getDmtDashboardGraph(start, end, 'Today');
        }
    });

    $('#dmt-date-range').daterangepicker({
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
    }, getDmtDashboardGraph);

    $('select#dmt-user-id').on('change', function () {
        globalDmtUserId = $(this).val();
        getDmtDashboardGraph(globalDmtStartDate, globalDmtEndDate, globalDmtFilterType);
    });
});

function getDmtDashboardGraph(start, end, type) {
    showChartOverlay('#dmtTxnOverlay');
    showChartOverlay('#dmtMrcBoardOverlay');

    globalDmtFilterType = type;
    $('#dmt-date-range span').html(globalDmtFilterType);

    globalDmtStartDate = start;
    globalDmtEndDate = end;

    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: globalDmtStartDate.format('YYYY-MM-DD'),
        endDate: globalDmtEndDate.format('YYYY-MM-DD'),
        userId: globalDmtUserId,
    };


    $.post($('#dmtGraphs').val() + '/transaction', jsonData, function (response) {
        if (response.code === '0x0200') {
            drawDmtTransactions(response);
            hideChartOverlay('#dmtTxnOverlay');
        } else {
            console.log('transaction', response);
        }


        //mercahnts
        $.post($('#dmtGraphs').val() + '/merchant', jsonData, function (response) {
            if (response.code === '0x0200') {
                drawDmtMerchants(response);
                hideChartOverlay('#dmtMrcBoardOverlay');
            } else {
                console.log(response);
            }

        });

    });


}

function drawDmtTransactions(response) {
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


    $('#totalTxnDmtChart').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(cwAepsSuccessTot)}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ₹ ${changeNumberFormat(cwAepsFailedTot)}`);

    if (showGraph) {
        cwAepsSuccess.sort(dynamicSort("z"));
        cwAepsFailed.sort(dynamicSort("z"));


        $('#dmtTxnChart').html(`<div class="position-relative mb-4">
            <canvas id="dmtTxnChartCanvas" height="260"></canvas>
        </div>`);

        let dmtTxnChartCanvas = $('#dmtTxnChartCanvas');

        new Chart(dmtTxnChartCanvas, {
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


function drawDmtMerchants(response) {
    let labels = Array();
    let labelStamp = Array();

    let showGraph = true;

    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });


    /**
     * ==============================
     * AEPS Merchant on board
     * ============================== 
     */

    let merchantOnBoard = Array();
    let merchantOnBoardTot = 0;

    //AEPS CW Count
    if (response.data.merchantOnBoardData.length > 0) {
        response.data.merchantOnBoardData.forEach((obj, idx) => {
            merchantOnBoard.push({
                z: obj.stamp,
                x: obj.mDate,
                y: obj.totCount
            });

            merchantOnBoardTot += parseInt(obj.totCount);
        });

        labels.forEach((obj, idx) => {
            if (merchantOnBoard.filter(e => e.x === obj).length == 0) {
                merchantOnBoard.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

    } else {
        labels.forEach((obj, idx) => {
            merchantOnBoard.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });

    }


    $('#totalDmtMrcBoardChart').html(`<i class="fas fa-square text-primary bg-primary"></i> ${changeNumberFormat(merchantOnBoardTot)}`);

    if (showGraph) {
        merchantOnBoard.sort(dynamicSort("z"));


        $('#dmtMrcBoardChart').html(`<div class="position-relative mb-4">
            <canvas id="dmtMrcBoardChartCanvas" height="260"></canvas>
        </div>`);

        let dmtMrcBoardChartCanvas = $('#dmtMrcBoardChartCanvas');

        new Chart(dmtMrcBoardChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        // label: '',
                        data: merchantOnBoard,
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


