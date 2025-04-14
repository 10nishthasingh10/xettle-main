"use strict";

var tabUpiStackChartClicked = false;
var globalUpiStackUserId = 0;
var globalUpiStackStartDate = "";
var globalUpiStackEndDate = "";
var globalUpiStackFilterType = "Today";

$(function () {
  var start = moment();
  var end = moment();

  $("#tabUpiStackChart").on("click", function () {
    if (!tabUpiStackChartClicked) {
      tabUpiStackChartClicked = true;
      getUpiStackGraph(start, end, "Today");
    }
  });

  $("#upistack-date-range").daterangepicker(
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
    getUpiStackGraph
  );

  $("select#upistack-user-id").on("change", function () {
    globalUpiStackUserId = $(this).val();
    getUpiStackGraph(
      globalUpiStackStartDate,
      globalUpiStackEndDate,
      globalUpiStackFilterType
    );
  });
});

function getUpiStackGraph(start, end, type) {
  showChartOverlay(".upiStackTxnOverlay");
  showChartOverlay(".upiStackDisputeOverlay");

  globalUpiStackFilterType = type;
  $("#upistack-date-range span").html(globalUpiStackFilterType);

  globalUpiStackStartDate = start;
  globalUpiStackEndDate = end;

  let jsonData = {
    _token: $('meta[name="csrf-token"]').attr("content"),
    startDate: globalUpiStackStartDate.format("YYYY-MM-DD"),
    endDate: globalUpiStackEndDate.format("YYYY-MM-DD"),
    userId: globalUpiStackUserId,
  };

  $.post(
    $("#upiStackGraphs").val() + "/transaction",
    jsonData,
    function (response) {
      if (response.code === "0x0200") {
        upiStackTransactionCharts(response);
        hideChartOverlay(".upiStackTxnOverlay");
      } else {
        console.log(response);
      }

      //dispute
      $.post(
        $("#upiStackGraphs").val() + "/disputed",
        jsonData,
        function (response) {
          if (response.code === "0x0200") {
            upiStackDisputedCharts(response);
            hideChartOverlay(".upiStackDisputeOverlay");
          } else {
            console.log(response);
          }
        }
      );
    }
  );
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
      switch (obj.status) {
        case "success":
          inwardFpay.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totAmt.toFixed(2),
          });

          inwardFpayTot += parseFloat(obj.totAmt.toFixed(2));
          break;
        case "pending":
          inwardIbl.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totAmt.toFixed(2),
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
      if (inwardIbl.filter((e) => e.x === obj).length == 0) {
        inwardIbl.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      inwardIbl.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  if (inwardFpay.length > 0) {
    labels.forEach((obj, idx) => {
      if (inwardFpay.filter((e) => e.x === obj).length == 0) {
        inwardFpay.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      inwardFpay.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  $("#totalUpiStackTxnChart")
    .html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(
    inwardFpayTot
  )}
    &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ₹ ${changeNumberFormat(
      inwardIblTot
    )}`);

  if (showGraph) {
    inwardFpay.sort(dynamicSort("z"));
    inwardIbl.sort(dynamicSort("z"));

    let DatePointX = inwardFpay.sort(dynamicSort("z"));
    let DatePointArr = [];
    function DatePointfunc(item, index) {
      DatePointArr.push(DatePointX[index].x);
    }
    DatePointX.forEach(DatePointfunc);
    console.log(DatePointArr);

    let inwardFpayX = inwardFpay.sort(dynamicSort("z"));
    let inwardFpayArr = [];
    function inwardFpayfunc(item, index) {
      inwardFpayArr.push(inwardFpayX[index].y);
    }
    inwardFpayX.forEach(inwardFpayfunc);
    console.log(inwardFpayArr);

    let inwardIblX = inwardIbl.sort(dynamicSort("z"));
    let inwardIblArr = [];
    function inwardIblfunc(item, index) {
      inwardIblArr.push(inwardIblX[index].y);
    }
    inwardIblX.forEach(inwardIblfunc);
    console.log(inwardIblArr);

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
      document.querySelector("#chart-widget6upi"),
      optionsproductchart
    );
    console.log(document.getElementById("chart-widget6upi"));
    chartproductchart.render();
    chartproductchart.updateSeries([
      {
        name: "Success",
        type: "area",
        data: inwardFpayArr,
      },
      {
        name: "Failed",
        type: "line",
        data: inwardIblArr,
      },
    ]);
  }

  /**
   * =======================
   * Doughnut Charts
   * =======================
   */

  let upiStackTxnDoughnutX = Array();
  let upiStackTxnDoughnutY = Array();
  let upiStackTxnDoughnutColors = Array();
  let upiStackTxnDoughnutTot =
    parseFloat(inwardIblTot) + parseFloat(inwardFpayTot);

  $("#upiStackTxnDoughnut")
    .html(`<canvas id="upiStackTxnDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
         <div class="inside-donut-chart-label"><strong id="upiStackTxnDoughnutTot" style="font-size: 18px;">0</strong></div>`);

  if (upiStackTxnDoughnutTot > 0) {
    upiStackTxnDoughnutX = Array("Success", "Pending");
    upiStackTxnDoughnutY = Array(inwardFpayTot, inwardIblTot);
    upiStackTxnDoughnutColors = Array(colorList.primary, colorList.pink);

    new Chart("upiStackTxnDoughnutCanvas", {
      type: "doughnut",
      data: {
        labels: upiStackTxnDoughnutX,
        datasets: [
          {
            backgroundColor: upiStackTxnDoughnutColors,
            data: upiStackTxnDoughnutY,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        cutoutPercentage: 60,
        legend: {
          display: true,
        },
        title: {
          display: false,
          text: "",
        },
      },
    });
  }
  $("#upiStackTxnDoughnutTot").text(
    "₹" + changeNumberFormat(upiStackTxnDoughnutTot)
  );
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
        case "success":
          disputedIbl.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totAmt.toFixed(2),
          });

          disputedIblTot += parseFloat(obj.totAmt.toFixed(2));
          break;
        case "pending":
          disputedFpay.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totAmt.toFixed(2),
          });

          disputedFpayTot += parseFloat(obj.totAmt.toFixed(2));
          break;
      }
    });
  }

  if (disputedIbl.length > 0) {
    labels.forEach((obj, idx) => {
      if (disputedIbl.filter((e) => e.x === obj).length == 0) {
        disputedIbl.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      disputedIbl.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  if (disputedFpay.length > 0) {
    labels.forEach((obj, idx) => {
      if (disputedFpay.filter((e) => e.x === obj).length == 0) {
        disputedFpay.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      disputedFpay.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  $("#totalUpiStackDisputeChart")
    .html(`<i class="fas fa-square text-success bg-success"></i> ₹ ${changeNumberFormat(
    disputedFpayTot
  )}
     &nbsp;<i class="fas fa-square text-primary bg-primary"></i> ₹ ${changeNumberFormat(
       disputedIblTot
     )}`);

  if (showGraph) {
    disputedFpay.sort(dynamicSort("z"));
    disputedIbl.sort(dynamicSort("z"));

    let DatePointX = disputedFpay.sort(dynamicSort("z"));
    let DatePointArr = [];
    function DatePointfunc(item, index) {
      DatePointArr.push(DatePointX[index].x);
    }
    DatePointX.forEach(DatePointfunc);
    console.log(DatePointArr);

    let disputedFpayX = disputedFpay.sort(dynamicSort("z"));
    let disputedFpayArr = [];
    function disputedFpayfunc(item, index) {
      disputedFpayArr.push(disputedFpayX[index].y);
    }
    disputedFpayX.forEach(disputedFpayfunc);
    console.log(disputedFpayArr);

    let disputedIblX = disputedIbl.sort(dynamicSort("z"));
    let disputedIblArr = [];
    function disputedIblfunc(item, index) {
      disputedIblArr.push(disputedIblX[index].y);
    }
    disputedIblX.forEach(disputedIblfunc);
    console.log(disputedIblArr);

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
      document.querySelector("#chart-widget6upidisp"),
      optionsproductchart
    );
    chartproductchart.render();
    chartproductchart.updateSeries([
      {
        name: "Success",
        type: "area",
        data: disputedFpayArr,
      },
      {
        name: "Failed",
        type: "line",
        data: disputedIblArr,
      },
    ]);
  }

  /**
   * =======================
   * Doughnut Charts
   * =======================
   */

  let upiStackDisputeDoughnutX = Array();
  let upiStackDisputeDoughnutY = Array();
  let upiStackDisputeDoughnutColors = Array();
  let upiStackDisputeDoughnutTot =
    parseFloat(disputedFpayTot) + parseFloat(disputedIblTot);

  $("#upiStackDisputeDoughnut")
    .html(`<canvas id="upiStackDisputeDoughnutCanvas" style="width:100%;max-width:600px"></canvas>
         <div class="inside-donut-chart-label"><strong id="upiStackDisputeDoughnutTot" style="font-size: 18px;">0</strong></div>`);

  if (upiStackDisputeDoughnutTot > 0) {
    upiStackDisputeDoughnutX = Array("Success", "Pending");
    upiStackDisputeDoughnutY = Array(disputedFpayTot, disputedIblTot);
    upiStackDisputeDoughnutColors = Array(colorList.danger, colorList.cyan);

    new Chart("upiStackDisputeDoughnutCanvas", {
      type: "doughnut",
      data: {
        labels: upiStackDisputeDoughnutX,
        datasets: [
          {
            backgroundColor: upiStackDisputeDoughnutColors,
            data: upiStackDisputeDoughnutY,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        cutoutPercentage: 60,
        legend: {
          display: true,
        },
        title: {
          display: false,
          text: "",
        },
      },
    });
  }
  $("#upiStackDisputeDoughnutTot").text(
    "₹" + changeNumberFormat(upiStackDisputeDoughnutTot)
  );
}
