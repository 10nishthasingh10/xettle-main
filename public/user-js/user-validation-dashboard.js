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
    showChartOverlay('#validationChartOverlay');

    $('#select-date-range span').html(type);


    let jsonData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        startDate: start.format('YYYY-MM-DD'),
        endDate: end.format('YYYY-MM-DD'),
    };

    $.post($('meta[name="base-url"]').attr('content') + '/graphs/validation', jsonData, function (response) {
        if (response.code === '0x0200') {

            drawValidationGraph(response);
            hideChartOverlay('#validationChartOverlay');

        } else {
            console.log(response);
        }
    });
}


function drawValidationGraph(response) {
    let labels = Array();
    let labelStamp = Array();

    let bankData = Array();
    let vpaData = Array();
    let ifscData = Array();

    let bankDataTot = 0;
    let vpaDataTot = 0;
    let ifscDataTot = 0;

    let showGraph = true;

    response.data.lables.forEach((obj, idx) => {
        labels.push(obj.x);
        labelStamp.push(obj.z);
    });


    if (response.data.requestCount.length > 0) {
        response.data.requestCount.forEach((obj, idx) => {

            switch (obj.type) {
                case 'bank':
                    bankData.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    bankDataTot += parseInt(obj.totCount);
                    break;
                case 'vpa':
                    vpaData.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    vpaDataTot += parseInt(obj.totCount);
                    break;
                case 'ifsc':
                    ifscData.push({
                        z: obj.stamp,
                        x: obj.mDate,
                        y: obj.totCount
                    });

                    ifscDataTot += parseInt(obj.totCount);
                    break;
            }

        });
    }

    if (bankData.length > 0) {

        labels.forEach((obj, idx) => {
            if (bankData.filter(e => e.x === obj).length == 0) {
                bankData.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });
    } else {
        labels.forEach((obj, idx) => {
            bankData.push({
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


    if (ifscData.length > 0) {

        labels.forEach((obj, idx) => {
            if (ifscData.filter(e => e.x === obj).length == 0) {
                ifscData.push({
                    z: labelStamp[idx],
                    x: obj,
                    y: 0
                });
            }
        });
    } else {
        labels.forEach((obj, idx) => {
            ifscData.push({
                z: labelStamp[idx],
                x: obj,
                y: null
            });
        });
    }


    $('#validationChartTotal').html(`<i class="fas fa-square text-success bg-success"></i> ${changeNumberFormat(bankDataTot)}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ${changeNumberFormat(vpaDataTot)}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ${changeNumberFormat(ifscDataTot)}`);


    $('#validateBank').text(changeNumberFormat(bankDataTot));
    $('#validateVpa').text(changeNumberFormat(vpaDataTot));
    $('#validateIfsc').text(changeNumberFormat(ifscDataTot));



    if (showGraph) {
        bankData.sort(dynamicSort("z"));
        vpaData.sort(dynamicSort("z"));
        ifscData.sort(dynamicSort("z"));

        $('#validationChart').html(`<div class="position-relative mb-4">
            <canvas id="validationCanvas" height="220"></canvas>
        </div>`);

        var validationCanvas = $('#validationCanvas');
        new Chart(validationCanvas, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'BANK',
                        data: bankData,
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
                        label: 'VPA',
                        data: vpaData,
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
                        label: 'IFSC',
                        data: ifscData,
                        backgroundColor: 'transparent',
                        borderColor: colorList.primary,
                        pointBorderColor: '#3E4B5B',
                        pointBackgroundColor: '#3E4B5B',
                        fill: true,
                        pointHoverBackgroundColor: '#007bff',
                        pointHoverBorderColor: '#007bff'
                    },
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