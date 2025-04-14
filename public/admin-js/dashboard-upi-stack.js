"use strict";

var tabUpiStackChartClicked = false;
var globalUpiStackUserId = 0;
var globalUpiStackStartDate = '';
var globalUpiStackEndDate = '';
var globalUpiStackFilterType = 'Today';


$(function () {
    var start = moment();
    var end = moment();

    $('#tabUpiStackChart').on('click', function () {
        if (!tabUpiStackChartClicked) {
            tabUpiStackChartClicked = true;
            getUpiStackGraph(start, end, 'Today');
        }
    });

    $('#upistack-date-range').daterangepicker({
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
    }, getUpiStackGraph);

    $('select#upistack-user-id').on('change', function () {
        globalUpiStackUserId = $(this).val();
        getUpiStackGraph(globalUpiStackStartDate, globalUpiStackEndDate, globalUpiStackFilterType);
    });

});


function getUpiStackGraph(start, end, type) {
    showChartOverlay('.upiStackTxnOverlay');
    showChartOverlay('.upiStackDisputeOverlay');

    globalUpiStackFilterType = type;
    $('#upistack-date-range span').html(globalUpiStackFilterType);

    globalUpiStackStartDate = start;
    globalUpiStackEndDate = end;


    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: globalUpiStackStartDate.format('YYYY-MM-DD'),
        endDate: globalUpiStackEndDate.format('YYYY-MM-DD'),
        userId: globalUpiStackUserId,
    };


    $.post($('#upiStackGraphs').val() + '/transaction', jsonData, function (response) {
        if (response.code === '0x0200') {
            upiStackTransactionCharts(response);
            hideChartOverlay('.upiStackTxnOverlay');
        } else {
            console.log(response);
        }


        //dispute
        $.post($('#upiStackGraphs').val() + '/disputed', jsonData, function (response) {
            if (response.code === '0x0200') {
                upiStackDisputedCharts(response);
                hideChartOverlay('.upiStackDisputeOverlay');
            } else {
                console.log(response);
            }
        });
    });

}


function upiStackTransactionCharts(response) {
    let labels = Array();
    let labelStamp = Array();

    let showGraph = true;

    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });


    let inwardIbl = Array();
    let inwardFpay = Array();
    let inwardFpayTot = 0;
    let inwardIblTot = 0;


    if (response.data.inwardData.length > 0) {
        response.data.inwardData.forEach((obj, idx) => {

            switch (obj.root_type) {
                case 'fpay':
                    inwardFpay.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt.toFixed(2)
                    });

                    inwardFpayTot += parseFloat(obj.totAmt.toFixed(2));
                    break;
                case 'ibl':
                    inwardIbl.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt.toFixed(2)
                    });

                    inwardIblTot += parseFloat(obj.totAmt.toFixed(2));
                    break;
            }

        });
    }


    /**
     * ==============================
     * UPI Stack Transactions Credits
     * ============================== 
     */



    if (inwardIbl.length > 0) {

        labels.forEach((obj, idx) => {
            if (inwardIbl.filter(e => e.x === obj).length == 0) {
                inwardIbl.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });
    } else {
        labels.forEach((obj, idx) => {
            inwardIbl.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }

    if (inwardFpay.length > 0) {

        labels.forEach((obj, idx) => {
            if (inwardFpay.filter(e => e.x === obj).length == 0) {
                inwardFpay.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });
    } else {
        labels.forEach((obj, idx) => {
            inwardFpay.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });

    }


    $('#totalUpiStackTxnChart').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(inwardFpayTot)}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ₹ ${changeNumberFormat(inwardIblTot)}`);

    if (showGraph) {
        inwardFpay.sort(dynamicSort("z"));
        inwardIbl.sort(dynamicSort("z"));


        $('#upiStackTxnChart').html(`<div class="position-relative mb-4">
            <canvas id="upiStackTxnChartCanvas" height="260"></canvas>
        </div>`);

        let upiStackTxnChartCanvas = $('#upiStackTxnChartCanvas');

        new Chart(upiStackTxnChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'FPay ₹ ',
                        data: inwardFpay,
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
                        label: 'IBL ₹ ',
                        data: inwardIbl,
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




    /**
     * =======================
     * Doughnut Charts
     * =======================
     */

    let upiStackTxnDoughnutX = Array();
    let upiStackTxnDoughnutY = Array();
    let upiStackTxnDoughnutColors = Array();
    let upiStackTxnDoughnutTot = parseFloat(inwardIblTot) + parseFloat(inwardFpayTot);

    $('#upiStackTxnDoughnut').html(`<canvas id="upiStackTxnDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
         <div class="inside-donut-chart-label"><strong id="upiStackTxnDoughnutTot" style="font-size: 18px;">0</strong></div>`);

    if (upiStackTxnDoughnutTot > 0) {
        upiStackTxnDoughnutX = Array('FPay', 'IBL');
        upiStackTxnDoughnutY = Array(inwardFpayTot, inwardIblTot);
        upiStackTxnDoughnutColors = Array(colorList.primary, colorList.pink);

        new Chart("upiStackTxnDoughnutCanvas", {
            type: "doughnut",
            data: {
                labels: upiStackTxnDoughnutX,
                datasets: [{
                    backgroundColor: upiStackTxnDoughnutColors,
                    data: upiStackTxnDoughnutY
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                legend: {
                    display: true
                },
                title: {
                    display: false,
                    text: ""
                }
            }
        });
    }
    $('#upiStackTxnDoughnutTot').text('₹' + changeNumberFormat(upiStackTxnDoughnutTot));
}


function upiStackDisputedCharts(response) {
    let labels = Array();
    let labelStamp = Array();

    let showGraph = true;

    /**
     * ===============================
     * UPI Stack Transactions Disputed
     * ===============================
     */

    let disputedIbl = Array();
    let disputedFpay = Array();
    let disputedFpayTot = 0;
    let disputedIblTot = 0;


    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });


    if (response.data.disputedData.length > 0) {
        response.data.disputedData.forEach((obj, idx) => {

            switch (obj.root_type) {
                case 'ibl':
                    disputedIbl.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt.toFixed(2)
                    });

                    disputedIblTot += parseFloat(obj.totAmt.toFixed(2));
                    break;
                case 'fpay':
                    disputedFpay.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt.toFixed(2)
                    });

                    disputedFpayTot += parseFloat(obj.totAmt.toFixed(2));
                    break;
            }

        });
    }

    if (disputedIbl.length > 0) {

        labels.forEach((obj, idx) => {
            if (disputedIbl.filter(e => e.x === obj).length == 0) {
                disputedIbl.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });

    } else {
        labels.forEach((obj, idx) => {
            disputedIbl.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }

    if (disputedFpay.length > 0) {

        labels.forEach((obj, idx) => {
            if (disputedFpay.filter(e => e.x === obj).length == 0) {
                disputedFpay.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });
    } else {
        labels.forEach((obj, idx) => {
            disputedFpay.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }


    $('#totalUpiStackDisputeChart').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(disputedFpayTot)}
     &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ₹ ${changeNumberFormat(disputedIblTot)}`);

    if (showGraph) {
        disputedFpay.sort(dynamicSort("z"));
        disputedIbl.sort(dynamicSort("z"));


        $('#upiStackDisputeChart').html(`<div class="position-relative mb-4">
             <canvas id="upiStackDisputeChartCanvas" height="260"></canvas>
         </div>`);

        let upiStackDisputeChartCanvas = $('#upiStackDisputeChartCanvas');

        new Chart(upiStackDisputeChartCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'FPay ₹ ',
                        data: disputedFpay,
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
                        label: 'IBL ₹ ',
                        data: disputedIbl,
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


    /**
     * =======================
     * Doughnut Charts
     * =======================
     */

    let upiStackDisputeDoughnutX = Array();
    let upiStackDisputeDoughnutY = Array();
    let upiStackDisputeDoughnutColors = Array();
    let upiStackDisputeDoughnutTot = parseFloat(disputedFpayTot) + parseFloat(disputedIblTot);

    $('#upiStackDisputeDoughnut').html(`<canvas id="upiStackDisputeDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
         <div class="inside-donut-chart-label"><strong id="upiStackDisputeDoughnutTot" style="font-size: 18px;">0</strong></div>`);

    if (upiStackDisputeDoughnutTot > 0) {
        upiStackDisputeDoughnutX = Array('FPay', 'IBL');
        upiStackDisputeDoughnutY = Array(disputedFpayTot, disputedIblTot);
        upiStackDisputeDoughnutColors = Array(colorList.danger, colorList.cyan);

        new Chart("upiStackDisputeDoughnutCanvas", {
            type: "doughnut",
            data: {
                labels: upiStackDisputeDoughnutX,
                datasets: [{
                    backgroundColor: upiStackDisputeDoughnutColors,
                    data: upiStackDisputeDoughnutY
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                legend: {
                    display: true
                },
                title: {
                    display: false,
                    text: ""
                }
            }
        });
    }
    $('#upiStackDisputeDoughnutTot').text('₹' + changeNumberFormat(upiStackDisputeDoughnutTot));
}