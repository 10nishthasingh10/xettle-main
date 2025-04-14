/**
 * Version: 1.0.0
 */

"user strict";

var ticksStyle = {
  fontColor: "#495057",
  fontStyle: "bold",
};

var mode = "index";
var intersect = true;

$(function () {
  var start = moment();
  var end = moment();

  $("#select-date-range").daterangepicker(
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
    getDashboardGraph
  );

  getDashboardGraph(start, end, "Today");

  $("#select_daterange_fo").daterangepicker(
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
        Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "7 Days": [moment().subtract(6, "days"), moment()],
        "30 Days": [moment().subtract(29, "days"), moment()],
      },
    },
    handleFinancialOverviewCards
  );

  handleFinancialOverviewCards(start, end, "Today");

  $("#refresh_report").on("click", () => {
    handleFinancialOverviewCards(globalStartDate, globalEndDate, globalType);
  });
});

function handleFinancialOverviewCards(start, end, type) {
  $("#xtl_loader_container").removeClass("d-none");

  setTimeout(function () {
    $("#xtl_loader_container").addClass("d-none");
  }, 1000);

  globalStartDate = start;
  globalEndDate = end;
  globalType = type;

  const dateData = {
    fromDate: globalStartDate.format("YYYY-MM-DD"),
    toDate: globalEndDate.format("YYYY-MM-DD"),
  };

  $("#select_daterange_fo span").html(globalType);

  getCardRecords("user/data/payout/processed", dateData);
  getCardRecords("user/data/payout/processing", dateData);
  getCardRecords("user/data/payout/failed", dateData);

  getCardRecords("user/data/aeps/success", dateData);
  getCardRecords("user/data/aeps/pending", dateData);
  getCardRecords("user/data/aeps/failed", dateData);
}

function getCardRecords(url, value) {
  $.ajax({
    url: $('meta[name="base-url"]').attr("content") + "/" + url,
    type: "POST",
    data: {
      _token: $('meta[name="csrf-token"]').attr("content"),
      fromDate: value?.fromDate || "",
      toDate: value?.toDate || "",
    },
    success: function (resp) {
      if (resp.data.status) {
        $(`#${resp.data.type}`).html("₹" + resp.data.totalAmount);

        if (resp.data?.totalCount) {
          $(`#${resp.data.type}_count`).html(resp.data.totalCount);
        } else if (value) {
          $(`#${resp.data.type}_count`).html(0);
        }
      }
    },
  });
}

function getDashboardGraph(start, end, type) {
  showChartOverlay("#primaryFundOverlay");

  globalFilterType = type;
  $("#select-date-range span").html(globalFilterType);

  globalStartDate = start;
  globalEndDate = end;

  let jsonData = {
    _token: $('meta[name="csrf-token"]').attr("content"),
    startDate: globalStartDate.format("YYYY-MM-DD"),
    endDate: globalEndDate.format("YYYY-MM-DD"),
    // userId: globalUserId,
  };

  $.post(primaryBalannceGraph, jsonData, function (response) {
    if (response.code === "0x0200") {
      drawTransactionCharts(response);
      hideChartOverlay("#primaryFundOverlay");
    } else {
      console.log(response);
    }
  });
}

function drawTransactionCharts(response) {
  let labels = Array();
  let labelStamp = Array();
  let inwardData = Array();
  let outwardData = Array();

  let inwardDataTotal = 0;
  let outwardDataTotal = 0;

  let showGraph = true;

  response.data.lables.forEach((obj, idx) => {
    labels.push(obj.x);
    labelStamp.push(obj.z);
  });

  if (response.data.fundFlowData.length > 0) {
    response.data.fundFlowData.forEach((obj, idx) => {
      switch (obj.type) {
        case "cr":
          inwardData.push({
            z: obj.stamp,
            x: obj.mDate,
            y: obj.totAmt.toFixed(2),
          });

          inwardDataTotal += parseFloat(obj.totAmt.toFixed(2));
          break;
        case "dr":
          outwardData.push({
            z: obj.stamp,
            x: obj.mDate,
            y: parseFloat(obj.totAmt).toFixed(2),
          });

          outwardDataTotal += parseFloat(obj.totAmt.toFixed(2));
          break;
      }
    });
  }

  if (inwardData.length > 0) {
    labels.forEach((obj, idx) => {
      if (inwardData.filter((e) => e.x === obj).length == 0) {
        inwardData.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      inwardData.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  if (outwardData.length > 0) {
    labels.forEach((obj, idx) => {
      if (outwardData.filter((e) => e.x === obj).length == 0) {
        outwardData.push({
          z: labelStamp[idx],
          x: obj,
          y: 0,
        });
      }
    });
  } else {
    labels.forEach((obj, idx) => {
      outwardData.push({
        z: labelStamp[idx],
        x: obj,
        y: 0,
      });
    });
  }

  $("#primaryFundTotal")
    .html(`<i style="background-color: transparent !important; color: #1276e9;" class="fas fa-circle bg-success"></i> ₹ ${changeNumberFormat(
    inwardDataTotal
  )}
    &nbsp;<i style="background-color: transparent !important" class="fas fa-circle text-danger bg-danger"></i> ₹ ${changeNumberFormat(
      outwardDataTotal
    )}`);

  if (showGraph) {
    inwardData.sort(dynamicSort("z"));
    outwardData.sort(dynamicSort("z"));
    console.log(inwardData.sort(dynamicSort("z")));
    console.log(outwardData.sort(dynamicSort("z")));

    let DatePointX = inwardData.sort(dynamicSort("z"));
    let DatePointArr = [];
    function DatePointfunc(item, index) {
      DatePointArr.push(DatePointX[index].x);
    }
    DatePointX.forEach(DatePointfunc);
    console.log(DatePointArr);

    let inwardDataX = inwardData.sort(dynamicSort("z"));
    let inwardDataArr = [];
    function inwardDatafunc(item, index) {
      inwardDataArr.push(inwardDataX[index].y);
    }
    inwardDataX.forEach(inwardDatafunc);
    console.log(inwardDataArr);

    let outwardDataX = outwardData.sort(dynamicSort("z"));
    let outwardDataArr = [];
    function outwardDatafunc(item, index) {
      outwardDataArr.push(outwardDataX[index].y);
    }
    outwardDataX.forEach(outwardDatafunc);
    console.log(outwardDataArr);

    // product chart
    let optionsproductchart = {
      chart: {
        height: 320,
        type: "line",
      },
      stroke: {
        curve: "smooth",
      },
      series: [
        {
          name: "Inward",
          type: "area",
          data: inwardDataArr,
        },
        {
          name: "Outward",
          type: "line",
          data: outwardDataArr,
        },
      ],
      fill: {
        colors: ["var(--primaryyy)", "#dc3545"],
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
      colors: ["var(--primaryyy)", "#dc3545"],
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
        colors: ["var(--primaryyy)", "#dc3545"],
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
      colors: ["var(--primaryyy)", "#dc3545"],
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

    // chartproductchart.destroy();
    var chartproductchart = new ApexCharts(
      document.querySelector("#chart-widget6"),
      optionsproductchart
    );
    chartproductchart.render();
    chartproductchart.updateSeries([
      {
        name: "Success",
        type: "area",
        data: inwardDataArr,
      },
      {
        name: "Failed",
        type: "line",
        data: outwardDataArr,
      },
    ]);
  }
}
