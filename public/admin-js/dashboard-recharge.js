"use strict";

var isRechargeTabClicked = false;

var globalAepsUserId = 0;
var globalAepsStartDate = "";
var globalAepsEndDate = "";
var globalAepsFilterType = "Today";

$(function () {
  var start = moment();
  var end = moment();

  $("#tabRecharge").on("click", function () {
    if (!isRechargeTabClicked) {
      isRechargeTabClicked = true;
      getRechargeDashboardGraph(start, end, "Today");
    }
  });

  $("#recharge-date-range").daterangepicker(
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
    getRechargeDashboardGraph
  );

  $("select#recharge-user-id").on("change", function () {
    globalAepsUserId = $(this).val();
    getRechargeDashboardGraph(
      globalAepsStartDate,
      globalAepsEndDate,
      globalAepsFilterType
    );
  });
});

function getRechargeDashboardGraph(start, end, type) {
  showChartOverlay("#rechargeTxnOverlay");
  showChartOverlay(".bankVolumeOverlay");

  globalAepsFilterType = type;
  $("#recharge-date-range span").html(globalAepsFilterType);

  globalAepsStartDate = start;
  globalAepsEndDate = end;

  let jsonData = {
    _token: $('meta[name="csrf-token"]').attr("content"),
    startDate: globalAepsStartDate.format("YYYY-MM-DD"),
    endDate: globalAepsEndDate.format("YYYY-MM-DD"),
    userId: globalAepsUserId,
  };

  $.post(
    $("#rechargeGraphs").val() + "/transaction",
    jsonData,
    function (response) {
      if (response.code === "0x0200") {
        drawRechargeTransactions(response);
        hideChartOverlay("#rechargeTxnOverlay");
      } else {
        console.log(response);
      }

      //bank volume
      $.post(
        $("#rechargeGraphs").val() + "/recharge-type",
        jsonData,
        function (response) {
          if (response.code === "0x0200") {
            drawRechargeTypeVolume(response);
            hideChartOverlay(".bankVolumeOverlay");
          } else {
            console.log(response);
          }
        }
      );
    }
  );
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
        case "processed":
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
        y: 0,
      });
    });
  }

  $("#totalTxnRechargeChart")
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
      document.querySelector("#chart-widget6recharge"),
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
  let bColor = "";

  if (response.data.rechargeTypeData.length > 0) {
    response.data.rechargeTypeData.forEach((obj, idx) => {
      switch (obj.status) {
        case "processed":
          cwBankSuccessX.push(obj.type);
          cwBankSuccessY.push(obj.totAmt);

          bColor = "#" + ((Math.random() * 0xffffff) << 0).toString(16);
          while (cwBankSuccessColors.includes(bColor) || bColor.length != 7) {
            bColor = "#" + ((Math.random() * 0xffffff) << 0).toString(16);
          }
          cwBankSuccessColors.push(bColor);

          rechargeSuccessTot += parseFloat(obj.totAmt);
          break;

        case "failed":
          cwBankFailedX.push(obj.type);
          cwBankFailedY.push(obj.totAmt);

          bColor = "#" + ((Math.random() * 0xffffff) << 0).toString(16);
          while (cwBankSuccessColors.includes(bColor) || bColor.length != 7) {
            bColor = "#" + ((Math.random() * 0xffffff) << 0).toString(16);
          }
          cwBankFailedColors.push(bColor);

          rechargeFailedTot += parseFloat(obj.totAmt);
          break;
      }
    });
  }

  // console.log(cwBankFailedColors, cwBankSuccessColors);

  $("#rechargeSuccessDoughnut")
    .html(`<canvas id="rechargeSuccessDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
        <div class="inside-donut-chart-label"><strong id="rechargeSuccessTot" style="font-size: 18px;">0</strong></div>`);

  if (cwBankSuccessX.length > 0) {
    new Chart("rechargeSuccessDoughnutCanvas", {
      type: "doughnut",
      data: {
        labels: cwBankSuccessX,
        datasets: [
          {
            backgroundColor: cwBankSuccessColors,
            data: cwBankSuccessY,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        cutoutPercentage: 60,
        legend: {
          display: false,
        },
        title: {
          display: false,
          text: "",
        },
      },
    });
  }
  $("#rechargeSuccessTot").text("₹" + changeNumberFormat(rechargeSuccessTot));

  $("#rechargeFailedDoughnut")
    .html(`<canvas id="rechargeFailedDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
        <div class="inside-donut-chart-label"><strong id="rechargeFailedTot" style="font-size: 18px;">0</strong></div>`);

  if (cwBankFailedX.length > 0) {
    new Chart("rechargeFailedDoughnutCanvas", {
      type: "doughnut",
      data: {
        labels: cwBankFailedX,
        datasets: [
          {
            backgroundColor: cwBankFailedColors,
            data: cwBankFailedY,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        cutoutPercentage: 60,
        legend: {
          display: false,
        },
        title: {
          display: false,
          text: "",
        },
      },
    });
  }
  $("#rechargeFailedTot").text("₹" + changeNumberFormat(rechargeFailedTot));
}
