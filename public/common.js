/**
 * Version: 1.0.0
 */

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