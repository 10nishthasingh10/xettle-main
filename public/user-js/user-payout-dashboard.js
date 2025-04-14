'use strict'

var isAjax = false;
var isAjax2 = false;

var globalDateRange;

var globalDateRangeOrd;

var globalDataModeOrd = null;
var globalDataStatusOrd = 'processed';

var glbalDrawPayoutTransactions = Array();
var globalShowChart = 'amount';

var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
}

var mode = 'index';
var intersect = true;



$(function () {

    $('.btnShowHide').on('click', function () {
        let show = $(this).attr('data-show');
        if (show === 'amount') {
            drawTransactionCharts(glbalDrawPayoutTransactions);
        } else if (show === 'count') {
            drawTransactionCountCharts(glbalDrawPayoutTransactions);
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
    }, getPayoutGraph);

    getPayoutGraph(start, end, 'Today');
});



function getPayoutGraph(start, end, type) {
    showChartOverlay('#payoutChartOverlay');
    showChartOverlay('#payoutTransactionOverlay');

    $('#select-date-range span').html(type);


    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: start.format('YYYY-MM-DD'),
        endDate: end.format('YYYY-MM-DD'),
    };

    $.post($('meta[name="base-url"]').attr('content') + '/payout/inward-outwars', jsonData, function (response) {
        if (response.code === '0x0200') {
            $('#tot_in_amt').html(response.data.tot_in);
            if (response.data.tot_in_acc != null)
                $('#tot_in_amt').attr('title', `${response.data.tot_in_acc.toFixed(2)} Rs.`);
            $('#tot_out_amt').html(response.data.tot_out);
            if (response.data.tot_out_acc != null)
                $('#tot_out_amt').attr('title', `${response.data.tot_out_acc.toFixed(2)} Rs.`);
            $('#tot_in_req').html(response.data.req_in);
            $('#tot_in_req').attr('title', response.data.req_in_acc);
            $('#tot_out_req').html(response.data.req_out);
            $('#tot_out_req').attr('title', response.data.req_out_acc);

            inFundsDrawChart(response);
            hideChartOverlay('#payoutChartOverlay');


            $.post($('meta[name="base-url"]').attr('content') + '/graphs/payout/transaction', jsonData, function (response) {
                if (response.code === '0x0200') {

                    glbalDrawPayoutTransactions = response;
                    if (globalShowChart === 'amount') {
                        drawTransactionCharts(response);
                    } else if (globalShowChart === 'count') {
                        drawTransactionCountCharts(response);
                    }


                    hideChartOverlay('#payoutTransactionOverlay');
                } else {
                    console.log(response);
                }
            });

        } else {
            console.log(response);
        }
    });

}


//fn for Draw chart
const inFundsDrawChart = function (response) {
    let labels = Array();
    let labelStamp = Array();
    let showGraph = true;


    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });

    let inwardData = Array();
    let payoutData = Array();

    // let dataCr = Array();
    // let dataDr = Array();

    if (response.data.inward.length > 0) {
        response.data.inward.forEach((obj, idx) => {
            inwardData.push({
                z: obj.stamp,
                x: obj.mDate,
                y: obj.totAmt
            });
        });
    }

    if (response.data.payout.length > 0) {
        response.data.payout.forEach((obj, idx) => {
            payoutData.push({
                z: obj.stamp,
                x: obj.mDate,
                y: obj.totAmt
            });
        });
    }

    if (inwardData.length > 0) {

        labels.forEach((obj, idx) => {
            if (inwardData.filter(e => e.x === obj).length == 0) {
                inwardData.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

    } else {
        labels.forEach((obj, idx) => {
            inwardData.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }

    if (payoutData.length > 0) {

        labels.forEach((obj, idx) => {
            if (payoutData.filter(e => e.x === obj).length == 0) {
                payoutData.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

    } else {
        labels.forEach((obj, idx) => {
            payoutData.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }

    if (showGraph) {
        inwardData.sort(dynamicSort("z"));
        payoutData.sort(dynamicSort("z"));


        $('#payout-chart').html(`<div class="position-relative mb-4">
            <canvas id="payout-chart-canvas" height="220"></canvas>
        </div>`);

        var payoutChartCanvas = $('#payout-chart-canvas');
        new Chart(payoutChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Inward ₹',
                        data: inwardData,
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
                        label: 'Payout ₹',
                        data: payoutData,
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


    payoutTotalAmount({
        processed: totSuccessAmt,
        failed: totFailedAmt,
        reversed: totReversedAmt,
        processing: totProcessingAmt
    });


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



    $('#payoutTransactionTotal').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(totSuccessAmt)}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ₹ ${changeNumberFormat(totFailedAmt)}
    &nbsp;<i class="fas fa-square text-dark bg-dark"></i> ₹ ${changeNumberFormat(totReversedAmt)}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ₹ ${changeNumberFormat(totProcessingAmt)}`);

    if (showGraph) {
        payoutSuccess.sort(dynamicSort("z"));
        payoutFailed.sort(dynamicSort("z"));
        payoutReversed.sort(dynamicSort("z"));
        payoutProcessing.sort(dynamicSort("z"));

        // console.log(payoutSuccess, payoutFailed);

        $('#payoutTransactionChart').html(`<div class="position-relative mb-4">
            <canvas id="payoutTransactionCanvas" height="220"></canvas>
        </div>`);

        var payoutChartCanvas = $('#payoutTransactionCanvas');
        new Chart(payoutChartCanvas, {
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
 * Transaction
 */
function drawTransactionCountCharts(response) {
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

    let totSuccessCount = 0;
    let totFailedCount = 0;
    let totReversedCount = 0;
    let totProcessingCount = 0;

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
                        y: obj.totCount
                    });

                    totSuccessAmt += parseFloat(obj.totAmt.toFixed(2));
                    totSuccessCount += parseInt(obj.totCount);
                    break;
                case 'failed':
                    payoutFailed.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    totFailedAmt += parseFloat(obj.totAmt.toFixed(2));
                    totFailedCount += parseInt(obj.totCount);
                    break;
                case 'reversed':
                    payoutReversed.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    totReversedAmt += parseFloat(obj.totAmt.toFixed(2));
                    totReversedCount += parseInt(obj.totCount);
                    break;
                case 'processing':
                    payoutProcessing.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    totProcessingAmt += parseFloat(obj.totAmt.toFixed(2));
                    totProcessingCount += parseInt(obj.totCount);
                    break;
            }

        });
    }

    payoutTotalAmount({
        processed: totSuccessAmt,
        failed: totFailedAmt,
        reversed: totReversedAmt,
        processing: totProcessingAmt
    });

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



    $('#payoutTransactionTotal').html(`<i class="fas fa-square text-success bg-success"></i> ${changeNumberFormat(totSuccessCount)}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ${changeNumberFormat(totFailedCount)}
    &nbsp;<i class="fas fa-square text-dark bg-dark"></i> ${changeNumberFormat(totReversedCount)}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ${changeNumberFormat(totProcessingCount)}`);

    if (showGraph) {
        payoutSuccess.sort(dynamicSort("z"));
        payoutFailed.sort(dynamicSort("z"));
        payoutReversed.sort(dynamicSort("z"));
        payoutProcessing.sort(dynamicSort("z"));

        $('#payoutTransactionChart').html(`<div class="position-relative mb-4">
            <canvas id="payoutTransactionCanvas" height="220"></canvas>
        </div>`);

        var payoutChartCanvas = $('#payoutTransactionCanvas');
        new Chart(payoutChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Success',
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
                        label: 'Reversed',
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
                        label: 'Processing',
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
                        label: 'Failed',
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
                                return + value
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


//order history charts
function payoutTotalAmount(obj) {

    $('#processed-amt').children('div.processed-amt').attr('data-original-title', obj.processed + ' Rs.');
    $('#processed-amt').children('div.processed-amt').html('&#8377;' + changeNumberFormat(obj.processed));

    $('#failed-amt').children('div.failed-amt').attr('data-original-title', obj.failed + ' Rs.');
    $('#failed-amt').children('div.failed-amt').html('&#8377;' + changeNumberFormat(obj.failed));

    $('#reversed-amt').children('div.reversed-amt').attr('data-original-title', obj.reversed + ' Rs.');
    $('#reversed-amt').children('div.reversed-amt').html('&#8377;' + changeNumberFormat(obj.reversed));

    $('#processing-amt').children('div.processing-amt').attr('data-original-title', obj.processing + ' Rs.');
    $('#processing-amt').children('div.processing-amt').html('&#8377;' + changeNumberFormat(obj.processing));
}