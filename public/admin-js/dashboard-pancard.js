"use strict";

var isPancardTabClicked = false;

var globalPanUserId = 0;
var globalPanStartDate = "";
var globalPanEndDate = "";
var globalPanFilterType = "Today";

$(function () {
  var start = moment();
  var end = moment();

  $("#tabPanCard").on("click", function () {
    if (!isPancardTabClicked) {
      isPancardTabClicked = true;
      getPanDashboardGraph(start, end, "Today");
    }
  });

  $("#pan-date-range").daterangepicker(
    {
      maxSpan: {
        days: 30,
      },
      showCustomRangeLabel: true,
      startDate: start,
      endDate: end,
      // opens: left,
      // alwaysShowCalendars: true,
      ranges: {
        Today: [moment(), moment()],
        "7 Days": [moment().subtract(6, "days"), moment()],
        "30 Days": [moment().subtract(29, "days"), moment()],
      },
    },
    getPanDashboardGraph
  );

  $("select#pan-user-id").on("change", function () {
    globalPanUserId = $(this).val();
    getPanDashboardGraph(
      globalPanStartDate,
      globalPanEndDate,
      globalPanFilterType
    );
  });
});

function getPanDashboardGraph(start, end, type) {
  showChartOverlay("#panTxnOverlay");
  showChartOverlay("#panMrcBoardOverlay");

  globalPanFilterType = type;
  $("#pan-date-range span").html(globalPanFilterType);

  globalPanStartDate = start;
  globalPanEndDate = end;

  let jsonData = {
    _token: $('meta[name="csrf-token"]').attr("content"),
    startDate: globalPanStartDate.format("YYYY-MM-DD"),
    endDate: globalPanEndDate.format("YYYY-MM-DD"),
    userId: globalPanUserId,
  };

  $.post($("#panGraphs").val() + "/transaction", jsonData, function (response) {
    if (response.code === "0x0200") {
      drawPanTransactions(response);
      hideChartOverlay("#panTxnOverlay");
    } else {
      console.log("transaction", response);
    }

    //mercahnts
    $.post($("#panGraphs").val() + "/merchant", jsonData, function (response) {
      if (response.code === "0x0200") {
        drawPanMerchants(response);
        hideChartOverlay("#panMrcBoardOverlay");
      } else {
        console.log(response);
      }
    });
  });
}

function drawPanTransactions(response) {
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
        case "success":
          cwAepsSuccess.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totAmt,
          });

          cwAepsSuccessTot += parseFloat(obj.totAmt);
          break;
        case "failed":
          cwAepsFailed.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totAmt,
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
      if (cwAepsSuccess.filter((e) => e.x === obj).length == 0) {
        cwAepsSuccess.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      cwAepsSuccess.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  if (cwAepsFailed.length > 0) {
    labels.forEach((obj, idx) => {
      if (cwAepsFailed.filter((e) => e.x === obj).length == 0) {
        cwAepsFailed.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      cwAepsFailed.push({
        z: labelStamp[idx],
        x: obj,
        y: null,
      });
    });
  }

  $("#totalTxnPanChart")
    .html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(
    cwAepsSuccessTot
  )}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ₹ ${changeNumberFormat(
      cwAepsFailedTot
    )}`);

  if (showGraph) {
    cwAepsSuccess.sort(dynamicSort("z"));
    cwAepsFailed.sort(dynamicSort("z"));
    console.log(cwAepsFailed.sort(dynamicSort("z")));

    let DatePointX = cwAepsSuccess.sort(dynamicSort("z"));
    let DatePointArr = [];
    function DatePointfunc(item, index) {
      DatePointArr.push(DatePointX[index].x);
    }
    DatePointX.forEach(DatePointfunc);
    console.log(DatePointArr);

    let cwAepsSuccessX = cwAepsSuccess.sort(dynamicSort("z"));
    let cwAepsSuccessArr = [];
    function cwAepsSuccessfunc(item, index) {
      cwAepsSuccessArr.push(cwAepsSuccessX[index].y);
    }
    cwAepsSuccessX.forEach(cwAepsSuccessfunc);
    console.log(cwAepsSuccessArr);
    let cwAepsFailedX = cwAepsFailed.sort(dynamicSort("z"));
    let cwAepsFailedArr = [];
    function cwAepsFailedfunc(item, index) {
      cwAepsFailedArr.push(cwAepsFailedX[index].y);
    }
    cwAepsFailedX.forEach(cwAepsFailedfunc);
    console.log(cwAepsFailedArr);

    // product chart
    let optionsproductchart = {};
    optionsproductchart = {
      chart: {
        height: 320,
        type: "line",
      },
      stroke: {
        curve: "smooth",
      },
      series: [],
      fill: {
        colors: ["var(--primaryyy)", "#ba895d"],
        type: "gradient",
        gradient: {
          shade: "light",
          type: "vertical",
          shadeIntensity: 0.4,
          inverseColors: false,
          opacityFrom: 0.9,
          opacityTo: 0.8,
          stops: [0, 100],
        },
      },
      colors: ["var(--primaryyy)", "#ba895d"],
      labels: DatePointArr,
      markers: {
        size: 0,
      },

      tooltip: {
        shared: true,
        intersect: false,
        y: {
          formatter: function (y) {
            if (typeof y !== "undefined") {
              return "₹" + y.toFixed(0);
            }
            return y;
          },
        },
      },
    };

    var chartproductchart = new ApexCharts(
      document.querySelector("#chart-widget6pan"),
      optionsproductchart
    );
    chartproductchart.render();
    chartproductchart.updateSeries([
      {
        name: "Success",
        type: "area",
        data: cwAepsSuccessArr,
      },
      {
        name: "Failed",
        type: "line",
        data: cwAepsFailedArr,
      },
    ]);
  }
}

function drawPanMerchants(response) {
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
        y: obj.totCount,
      });

      merchantOnBoardTot += parseInt(obj.totCount);
    });

    labels.forEach((obj, idx) => {
      if (merchantOnBoard.filter((e) => e.x === obj).length == 0) {
        merchantOnBoard.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      merchantOnBoard.push({
        z: labelStamp[idx],
        x: obj,
        y: null,
      });
    });
  }

  $("#totalPanMrcBoardChart").html(
    `<i class="fas fa-square text-primary bg-primary"></i> ${changeNumberFormat(
      merchantOnBoardTot
    )}`
  );

  if (showGraph) {
    merchantOnBoard.sort(dynamicSort("z"));

    $("#panMrcBoardChart").html(`<div class="position-relative mb-4">
            <canvas id="panMrcBoardChartCanvas" height="260"></canvas>
        </div>`);

    let panMrcBoardChartCanvas = $("#panMrcBoardChartCanvas");

    new Chart(panMrcBoardChartCanvas, {
      data: {
        labels: labels,
        datasets: [
          {
            type: "line",
            // label: '',
            data: merchantOnBoard,
            backgroundColor: "transparent",
            borderColor: colorList.primary,
            pointBorderColor: "#3E4B5B",
            pointBackgroundColor: "#3E4B5B",
            fill: true,
            pointHoverBackgroundColor: "#007bff",
            pointHoverBorderColor: "#007bff",
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        tooltips: {
          mode: mode,
          intersect: intersect,
        },
        hover: {
          mode: mode,
          intersect: intersect,
        },
        legend: {
          display: false,
        },
        scales: {
          yAxes: [
            {
              // display: false,
              gridLines: {
                display: true,
                // lineWidth: '4px',
                color: "#ebebeb",
                zeroLineColor: "transparent",
              },
              ticks: $.extend(
                {
                  beginAtZero: true,
                  fontSize: 11,
                  // suggestedMax: 200,
                  callback: function (value) {
                    if (value >= 1000) {
                      value /= 1000;
                      value += "k";
                    }
                    return value;
                  },
                },
                ticksStyle
              ),
            },
          ],
          xAxes: [
            {
              display: true,
              gridLines: {
                display: true,
                // lineWidth: '4px',
                color: "#ebebeb",
                zeroLineColor: "transparent",
              },
              ticks: $.extend(
                {
                  beginAtZero: true,
                  fontSize: 10,
                },
                ticksStyle
              ),
            },
          ],
        },
      },
    });
  }
}
