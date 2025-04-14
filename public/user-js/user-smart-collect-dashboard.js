'use strict'

var isAjax = false;

var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
}

var mode = 'index';
var intersect = true;

$(function () {
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
    }, getValidationGraph);

    getValidationGraph(start, end, 'Today');
});


function getValidationGraph(start, end, type) {
    showChartOverlay('#smartCollectChartOverlay');

    $('#select-date-range span').html(type);


    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: start.format('YYYY-MM-DD'),
        endDate: end.format('YYYY-MM-DD'),
    };

    $.post($('meta[name="base-url"]').attr('content') + '/graphs/smart-collect', jsonData, function (response) {
        if (response.code === '0x0200') {

            drawValidationGraph(response);
            hideChartOverlay('#smartCollectChartOverlay');

        } else {
            console.log(response);
        }
    });
}


function drawValidationGraph(response) {
    let labels = Array();
    let labelStamp = Array();

    let vanData = Array();
    let vanDataTot = 0;

    let vpaData = Array();
    let vpaDataTot = 0;

    // let creditDataTot = 0;
    let creditDataCount = 0;

    let showGraph = true;

    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });


    if (response.data.requestCount.length > 0) {
        response.data.requestCount.forEach((obj, idx) => {

            switch (obj.type) {
                case '0':
                    vanData.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt
                    });

                    vanDataTot += parseFloat(obj.totAmt);
                    creditDataCount++;
                    break;
                case '1':
                    vpaData.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totAmt
                    });

                    vpaDataTot += parseFloat(obj.totAmt);
                    creditDataCount++;
                    break;
            }



        });
    }

    if (vanData.length > 0) {

        labels.forEach((obj, idx) => {
            if (vanData.filter(e => e.x === obj).length == 0) {
                vanData.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });
    } else {
        labels.forEach((obj, idx) => {
            vanData.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }

    if (vpaData.length > 0) {

        labels.forEach((obj, idx) => {
            if (vpaData.filter(e => e.x === obj).length == 0) {
                vpaData.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });
    } else {
        labels.forEach((obj, idx) => {
            vpaData.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }

    $('#countMerchant').text(changeNumberFormat(creditDataCount));
    $('#currentBusiness').text('₹' + changeNumberFormat(vanDataTot + vpaDataTot));

    $('#smartCollectChartTotal').html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(vanDataTot)}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ₹ ${changeNumberFormat(vpaDataTot)}`);


    if (showGraph) {
        vpaData.sort(dynamicSort("z"));
        vanData.sort(dynamicSort("z"));

        $('#smartCollectChart').html(`<div class="position-relative mb-4">
            <canvas id="smartCollectCanvas" height="220"></canvas>
        </div>`);

        var smartCollectCanvas = $('#smartCollectCanvas');
        new Chart(smartCollectCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'VAN ₹',
                        data: vanData,
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
                        label: 'VPA ₹',
                        data: vpaData,
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
                                return '₹' + value;
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