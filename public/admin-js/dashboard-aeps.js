"use strict";

var isTabClicked = false;

var globalAepsUserId = 0;
var globalAepsStartDate = '';
var globalAepsEndDate = '';
var globalAepsFilterType = 'Today';


$(function () {
    var start = moment();
    var end = moment();

    $('#tabAepsChart').on('click', function () {
        if (!isTabClicked) {
            isTabClicked = true;
            getAepsDashboardGraph(start, end, 'Today');
        }
    });

    $('#aeps-date-range').daterangepicker({
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

    $('select#aeps-user-id').on('change', function () {
        globalAepsUserId = $(this).val();
        getAepsDashboardGraph(globalAepsStartDate, globalAepsEndDate, globalAepsFilterType);
    });
});

function getAepsDashboardGraph(start, end, type) {
    showChartOverlay('#aepsTxnOverlay');
    showChartOverlay('#aepsCountOverlay');
    showChartOverlay('#aepsMrcBoardOverlay');
    showChartOverlay('.bankVolumeOverlay');
    showChartOverlay('.rootVolumeOverlay');

    globalAepsFilterType = type;
    $('#aeps-date-range span').html(globalAepsFilterType);

    globalAepsStartDate = start;
    globalAepsEndDate = end;

    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: globalAepsStartDate.format('YYYY-MM-DD'),
        endDate: globalAepsEndDate.format('YYYY-MM-DD'),
        userId: globalAepsUserId,
    };


    $.post($('#aepsGraphs').val() + '/transaction', jsonData, function (response) {
        if (response.code === '0x0200') {
            drawAepsTransactions(response);
            hideChartOverlay('#aepsTxnOverlay');
        } else {
            console.log(response);
        }


        //Transaction counts
        $.post($('#aepsGraphs').val() + '/txn-counts', jsonData, function (response) {
            if (response.code === '0x0200') {
                drawAepsCounts(response);
                hideChartOverlay('#aepsCountOverlay');
            } else {
                console.log(response);
            }


            //mercahnts
            $.post($('#aepsGraphs').val() + '/merchant', jsonData, function (response) {
                if (response.code === '0x0200') {
                    drawAepsMerchants(response);
                    hideChartOverlay('#aepsMrcBoardOverlay');
                } else {
                    console.log(response);
                }


                //bank volume
                $.post($('#aepsGraphs').val() + '/bank-volume', jsonData, function (response) {
                    if (response.code === '0x0200') {
                        drawAepsBankVolume(response);
                        hideChartOverlay('.bankVolumeOverlay');
                    } else {
                        console.log(response);
                    }


                    //root volume
                    $.post($('#aepsGraphs').val() + '/root-volume', jsonData, function (response) {
                        if (response.code === '0x0200') {
                            drawAepsRootVolume(response);
                            hideChartOverlay('.rootVolumeOverlay');
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
                case 'success':
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


    $('#totalTxnApesChart').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(cwAepsSuccessTot)}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ₹ ${changeNumberFormat(cwAepsFailedTot)}`);

    if (showGraph) {
        cwAepsSuccess.sort(dynamicSort("z"));
        cwAepsFailed.sort(dynamicSort("z"));


        $('#aepsTxnChart').html(`<div class="position-relative mb-4">
            <canvas id="aepsTxnChartCanvas" height="260"></canvas>
        </div>`);

        let aepsTxnChartCanvas = $('#aepsTxnChartCanvas');

        new Chart(aepsTxnChartCanvas, {
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

    $('#totalCountApesChart').html(`<i class="fas fa-square text-success bg-success"></i> ${changeNumberFormat(cwAepsCountTot)}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ${changeNumberFormat(beAepsCountTot)}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ${changeNumberFormat(msAepsCountTot)}`);

    if (showGraph) {
        cwAepsCount.sort(dynamicSort("z"));
        msAepsCount.sort(dynamicSort("z"));
        beAepsCount.sort(dynamicSort("z"));


        $('#aepsCountChart').html(`<div class="position-relative mb-4">
            <canvas id="aepsCountChartCanvas" height="260"></canvas>
        </div>`);

        let aepsCountChartCanvas = $('#aepsCountChartCanvas');

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


function drawAepsMerchants(response) {
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


    $('#totalAepsMrcBoardChart').html(`<i class="fas fa-square text-primary bg-primary"></i> ${changeNumberFormat(merchantOnBoardTot)}`);

    if (showGraph) {
        merchantOnBoard.sort(dynamicSort("z"));


        $('#aepsMrcBoardChart').html(`<div class="position-relative mb-4">
            <canvas id="aepsMrcBoardChartCanvas" height="260"></canvas>
        </div>`);

        let aepsMrcBoardChartCanvas = $('#aepsMrcBoardChartCanvas');

        new Chart(aepsMrcBoardChartCanvas, {
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

    let cwBankFailedX = Array();
    let cwBankFailedY = Array();
    let cwBankFailedColors = Array();
    let cwBankFailedTot = 0;
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

                case 'failed':
                    cwBankFailedX.push(obj.bank);
                    cwBankFailedY.push(obj.totAmt);

                    bColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
                    while (cwBankSuccessColors.includes(bColor) || bColor.length != 7) {
                        bColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
                    }
                    cwBankFailedColors.push(bColor);

                    cwBankFailedTot += parseFloat(obj.totAmt);
                    break;
            }

        });
    }


    console.log(cwBankFailedColors, cwBankSuccessColors);

    $('#cwAepsSuccessDoughnut').html(`<canvas id="cwAepsSuccessDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
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




    $('#cwAepsFailedDoughnut').html(`<canvas id="cwAepsFailedDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
        <div class="inside-donut-chart-label"><strong id="cwBankFailedTot" style="font-size: 18px;">0</strong></div>`);

    if (cwBankFailedX.length > 0) {

        new Chart("cwAepsFailedDoughnutCanvas", {
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
    $('#cwBankFailedTot').text('₹' + changeNumberFormat(cwBankFailedTot));

}



function drawAepsRootVolume(response) {


    /**
     * ========================
     * Doughnut Charts
     * Volume By ROOT TYPE
     * ========================
     */

    let cwRootSuccessX = Array();
    let cwRootSuccessY = Array();
    let cwRootSuccessColors = Array();
    let cwRootSuccessTot = 0;

    let cwRootFailedX = Array();
    let cwRootFailedY = Array();
    let cwRootFailedColors = Array();
    let cwRootFailedTot = 0;


    if (response.data.volumeRootData.length > 0) {
        response.data.volumeRootData.forEach((obj, idx) => {

            switch (obj.status) {
                case 'success':
                    cwRootSuccessX.push(obj.lable.toUpperCase());
                    cwRootSuccessY.push(obj.volume);
                    cwRootSuccessColors.push(chartColors.aepsRoot[obj.lable.toUpperCase()]);

                    cwRootSuccessTot += parseFloat(obj.volume);
                    break;

                case 'failed':
                    cwRootFailedX.push(obj.lable.toUpperCase());
                    cwRootFailedY.push(obj.volume);
                    cwRootFailedColors.push(chartColors.aepsRoot[obj.lable.toUpperCase()]);

                    cwRootFailedTot += parseFloat(obj.volume);
                    break;
            }

        });
    }

    $('#cwAepsSuccessByRoot').html(`<canvas id="cwAepsSuccessByRootCanvas" style="width:100%;max-width:600px"></canvas>
         <div class="inside-donut-chart-label"><strong id="cwRootSuccessTot" style="font-size: 18px;">0</strong></div>`);

    if (cwRootSuccessX.length > 0) {

        new Chart("cwAepsSuccessByRootCanvas", {
            type: "doughnut",
            data: {
                labels: cwRootSuccessX,
                datasets: [{
                    backgroundColor: cwRootSuccessColors,
                    data: cwRootSuccessY
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
    $('#cwRootSuccessTot').text('₹' + changeNumberFormat(cwRootSuccessTot));



    $('#cwAepsFailedByRoot').html(`<canvas id="cwAepsFailedByRootCanvas" style="width:100%;max-width:600px"></canvas>
         <div class="inside-donut-chart-label"><strong id="cwRootFailedTot" style="font-size: 18px;">0</strong></div>`);

    if (cwRootFailedX.length > 0) {

        new Chart("cwAepsFailedByRootCanvas", {
            type: "doughnut",
            data: {
                labels: cwRootFailedX,
                datasets: [{
                    backgroundColor: cwRootFailedColors,
                    data: cwRootFailedY
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
    $('#cwRootFailedTot').text('₹' + changeNumberFormat(cwRootFailedTot));
}

