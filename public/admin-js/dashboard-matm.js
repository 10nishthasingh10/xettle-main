"use strict";

var isMatmTabClicked = false;

var globalMatmUserId = 0;
var globalMatmStartDate = "";
var globalMatmEndDate = "";
var globalMatmFilterType = "Today";

$(function () {
  var start = moment();
  var end = moment();

  $("#tabMatm").on("click", function () {
    if (!isMatmTabClicked) {
      isMatmTabClicked = true;
      getMatmDashboardGraph(start, end, "Today");
    }
  });

  $("#matm-date-range").daterangepicker(
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
    getMatmDashboardGraph
  );

  $("select#matm-user-id").on("change", function () {
    globalMatmUserId = $(this).val();
    getMatmDashboardGraph(
      globalMatmStartDate,
      globalMatmEndDate,
      globalMatmFilterType
    );
  });
});

function getMatmDashboardGraph(start, end, type) {
  showChartOverlay("#matmTxnOverlay");
  showChartOverlay("#matmCountOverlay");
  // showChartOverlay('#matmMrcBoardOverlay');
  // showChartOverlay('.bankVolumeOverlay');
  // showChartOverlay('.rootVolumeOverlay');

  globalMatmFilterType = type;
  $("#matm-date-range span").html(globalMatmFilterType);

  globalMatmStartDate = start;
  globalMatmEndDate = end;

  let jsonData = {
    _token: $('meta[name="csrf-token"]').attr("content"),
    startDate: globalMatmStartDate.format("YYYY-MM-DD"),
    endDate: globalMatmEndDate.format("YYYY-MM-DD"),
    userId: globalMatmUserId,
  };

  $.post(
    $("#matmGraphs").val() + "/transaction",
    jsonData,
    function (response) {
      if (response.code === "0x0200") {
        drawMatmTransactions(response);
        hideChartOverlay("#matmTxnOverlay");
      } else {
        console.log(response);
      }

      //Transaction counts
      $.post(
        $("#matmGraphs").val() + "/txn-counts",
        jsonData,
        function (response) {
          if (response.code === "0x0200") {
            drawMatnCounts(response);
            hideChartOverlay("#matmCountOverlay");
          } else {
            console.log(response);
          }
        }
      );
    }
  );
}

function drawMatmTransactions(response) {
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

  $("#totalTxnMatmChart")
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
      document.querySelector("#chart-widget6matm"),
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

function drawMatnCounts(response) {
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
        case "cw":
          cwAepsCount.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totCount,
          });

          cwAepsCountTot += parseInt(obj.totCount);
          break;

        case "be":
          beAepsCount.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totCount,
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
      if (cwAepsCount.filter((e) => e.x === obj).length == 0) {
        cwAepsCount.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      cwAepsCount.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  //AEPS MS Count
  if (msAepsCount.length > 0) {
    labels.forEach((obj, idx) => {
      if (msAepsCount.filter((e) => e.x === obj).length == 0) {
        msAepsCount.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      msAepsCount.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  //AEPS BE Count
  if (beAepsCount.length > 0) {
    labels.forEach((obj, idx) => {
      if (beAepsCount.filter((e) => e.x === obj).length == 0) {
        beAepsCount.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      beAepsCount.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  $("#totalCountApesChart")
    .html(`<i class="fas fa-square text-success bg-success"></i> ${changeNumberFormat(
    cwAepsCountTot
  )}
    &nbsp;<i class="fas fa-square text-danger bg-danger"></i> ${changeNumberFormat(
      beAepsCountTot
    )}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ${changeNumberFormat(
      msAepsCountTot
    )}`);

  if (showGraph) {
    cwAepsCount.sort(dynamicSort("z"));
    msAepsCount.sort(dynamicSort("z"));
    beAepsCount.sort(dynamicSort("z"));
    console.log(cwAepsCount.sort(dynamicSort("z")));

    let DatePointX = cwAepsCount.sort(dynamicSort("z"));
    let DatePointArr = [];
    function DatePointfunc(item, index) {
      DatePointArr.push(DatePointX[index].x);
    }
    DatePointX.forEach(DatePointfunc);
    console.log(DatePointArr);

    let cwAepsCountX = cwAepsCount.sort(dynamicSort("z"));
    let cwAepsCountArr = [];
    function cwAepsCounfunc(item, index) {
      cwAepsCountArr.push(cwAepsCountX[index].y);
    }
    cwAepsCountX.forEach(cwAepsCounfunc);
    console.log(cwAepsCountArr);

    let beAepsCountX = beAepsCount.sort(dynamicSort("z"));
    let beAepsCountArr = [];
    function beAepsCountfunc(item, index) {
      beAepsCountArr.push(beAepsCountX[index].y);
    }
    beAepsCountX.forEach(beAepsCountfunc);
    console.log(beAepsCountArr);

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
      document.querySelector("#chart-widget6matm2"),
      optionsproductchart
    );
    chartproductchart.render();
    chartproductchart.updateSeries([
      {
        name: "CW",
        type: "area",
        data: cwAepsCountArr,
      },
      {
        name: "BE",
        type: "line",
        data: beAepsCountArr,
      },
    ]);
  }
}
