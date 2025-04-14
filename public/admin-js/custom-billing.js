/**
 * Version: 1.0.0
 * File: custom-billing.js?v=1.0.0
 */
"use strict";
var addUpdateRulesCountFromDb = 0;
var currentClickedUpdateRuleButton = null;

$(function () {
  //add new product row
  $("#addNewRulesRow").on("click", function () {
    $("#addUpdateRulesModalTable tbody").append(
      addNewRulesRow(addUpdateRulesCountFromDb++)
    );
  });

  //Bind update and change handler
  $("#addUpdateRulesModalTable").on(
    "keyup change",
    ".addUpdateRulesInput",
    function () {
      formSubmitButtonHandler("addUpdateRulesInput");
    }
  );
  $("#addUpdateRulesName").on(
    "keyup change",
    ".addUpdateRulesInput",
    function () {
      formSubmitButtonHandler("addUpdateRulesInput");
    }
  );

  //remove new product row
  $("#addUpdateRulesModalTable").on("click", ".removeNewRulesRow", function () {
    removeNewRulesRow(this, "tr.fromBtn");
  });

  /**
   * Add New Scheme And Rules
   */
  $("#addNewSchemeAndRulesBtn").on("click", function () {
    $("#addUpdateProductOverlay").addClass("d-none");
    $("#addUpdateRulesName").html(
      `<input type="text" class="form-control addUpdateRulesInput" name="scheme_name" placeholder="Enter scheme name here" />`
    );
    $('form[role="add-update-product-form"]').attr(
      "action",
      $('meta[name="base-url"]').attr("content") +
        `/admin/custom-billing/scheme-rule/add-schemes`
    );
    $("#addUpdateRulesModal").modal("show");
  });

  /**
   * Hide event of Modal
   */
  $("#addUpdateRulesModal").on("hide.bs.modal", function () {
    $("#addUpdateRulesModalTable tbody").html("");
    $("#addUpdateRulesName").html("");
    $("#btn_addUpdateRulesInput").prop("disabled", true);
    $('form[role="add-update-product-form"]').attr("action", "");
    addUpdateRulesCountFromDb = 0;
  });

  /**
   * Update Scheme and Rules
   */
  var addUpdateSchemeAndRulesAjax = false;

  $("#datatable_schemes_info").on(
    "click",
    ".addUpdateSchemeAndRules",
    function () {
      let serviceId = $(this).attr("data-id");
      let clickAction = $(this).attr("data-clickaction");

      if (!addUpdateSchemeAndRulesAjax) {
        $("#addUpdateProductOverlay").addClass("d-none");
        currentClickedUpdateRuleButton = this;
        addUpdateSchemeAndRulesAjax = true;
        $(this).prop("disabled", true);
        let btnText = $(this).html();
        $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);

        $.ajax({
          url:
            $('meta[name="base-url"]').attr("content") +
            `/admin/custom-billing/scheme-rule/fetch-list`,
          type: "POST",
          data: {
            scheme_id: serviceId,
            _token: $('meta[name="csrf-token"]').attr("content"),
          },
          success: (response) => {
            switch (response.status) {
              case "SUCCESS":
                let trClass = "fromdb";
                if (clickAction === "update") {
                  $("#addUpdateRulesName")
                    .html(`<input type="hidden" name="scheme_id" value="${response.data.schemeId}" />
                                    <input type="text" class="form-control addUpdateRulesInput" name="scheme_name" value="${response.data.schemeName}" />`);
                } else if (clickAction === "copy") {
                  trClass = "fromBtn";
                  $("#addUpdateRulesName")
                    .html(`<input type="hidden" name="is_copy" value="copy_scheme" />
                                    <input type="text" class="form-control addUpdateRulesInput" name="scheme_name" placeholder="Enter scheme name here"/>`);
                } else {
                  return;
                }

                if (
                  response.data.schemeRules !== "" &&
                  response.data.schemeRules !== null &&
                  response.data.schemeRules !== undefined
                ) {
                  let formInputRows = "";

                  response.data.schemeRules.forEach((row, index) => {
                    formInputRows += `
                                        <tr class="${trClass}">
                                            <td class="form-group">
                                                <select class="form-control addUpdateRulesInput form-control-select updateRulesServiceId" data-value="${row.service_id}" name="service[${addUpdateRulesCountFromDb}]" required>
                                                        <option value="">--Select--</option>`;
                    globalServices.map((serviceRow) => {
                      if (row.service_id == serviceRow.service_id) {
                        formInputRows += `<option value="${serviceRow.service_id}" selected>${serviceRow.service_name}</option>`;
                      } else {
                        formInputRows += `<option value="${serviceRow.service_id}">${serviceRow.service_name}</option>`;
                      }
                    });

                    formInputRows += `</select>
                                        </td>
                                        <td>
                                        <select class="form-control addUpdateRulesInput form-control-select updateRulesProductId" data-value="${row.product_id}" name="product[${addUpdateRulesCountFromDb}]" required>
                                            <option value="">--</option>`;

                    globalProducts.map((productRow) => {
                      if (row.service_id == productRow.service_id) {
                        if (row.product_id == productRow.product_id) {
                          formInputRows += `<option value="${productRow.product_id}" selected>${productRow.name}</option>`;
                        } else {
                          formInputRows += `<option value="${productRow.product_id}">${productRow.name}</option>`;
                        }
                      }
                    });

                    formInputRows += `
                                                </select></td>
                                            <td>
                                                <select class="form-control addUpdateRulesInput form-control-select" data-value="${
                                                  row.type
                                                }" name="type[${addUpdateRulesCountFromDb}]" required>
                                                    <option value="fixed" ${
                                                      row.type === "fixed"
                                                        ? "selected"
                                                        : ""
                                                    }>Fixed</option>
                                                    <option value="percent" ${
                                                      row.type === "percent"
                                                        ? "selected"
                                                        : ""
                                                    }>Percent</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control addUpdateRulesInput form-control-select" data-value="${
                                                  row.is_active
                                                }" name="is_active[${addUpdateRulesCountFromDb}]" required>
                                                    <option value="1" ${
                                                      row.is_active === "1"
                                                        ? "selected"
                                                        : ""
                                                    }>Active</option>
                                                    <option value="0" ${
                                                      row.is_active === "0"
                                                        ? "selected"
                                                        : ""
                                                    }>Inactive</option>
                                                </select>
                                            </td>
                                            <td>
                                                ${
                                                  clickAction === "update"
                                                    ? `<input type="hidden" name="rule_id[${addUpdateRulesCountFromDb}]" value="${row.id}" />`
                                                    : ""
                                                }
                                                <input type="number" min="0" class="form-control addUpdateRulesInput" value="${
                                                  row.start_value || ""
                                                }" name="start_value[${addUpdateRulesCountFromDb}]" />
                                            </td>
                                            <td><input type="number" min="0" class="form-control addUpdateRulesInput" value="${
                                              row.end_value || ""
                                            }" name="end_value[${addUpdateRulesCountFromDb}]" /></td>
                                            <td><input type="number" min="0" class="form-control addUpdateRulesInput" value="${
                                              row.fee || ""
                                            }" name="fee[${addUpdateRulesCountFromDb}]" /></td>
                                            <td><input type="number" min="0" class="form-control addUpdateRulesInput" value="${
                                              row.min_fee || ""
                                            }" name="min_fee[${addUpdateRulesCountFromDb}]" /></td>
                                            ${
                                              clickAction === "update"
                                                ? `<td colspan="2"><input type="number" min="0" class="form-control addUpdateRulesInput" value="${
                                                    row.max_fee || ""
                                                  }" name="max_fee[${addUpdateRulesCountFromDb}]" /></td>`
                                                : `<td><input type="number" min="0" class="form-control addUpdateRulesInput" value="${
                                                    row.max_fee || ""
                                                  }" name="max_fee[${addUpdateRulesCountFromDb}]" /></td>
                                                <td class="text-center"><span class="btn removeNewRulesRow"><i class="fas fa-times-square"></i></span></td>`
                                            }
                                        </tr>`;
                    addUpdateRulesCountFromDb++;
                  });

                  $("#addUpdateRulesModalTable tbody").html(formInputRows);
                }

                if (clickAction === "update") {
                  $('form[role="add-update-product-form"]').attr(
                    "action",
                    $('meta[name="base-url"]').attr("content") +
                      `/admin/custom-billing/scheme-rule/edit-schemes`
                  );
                } else if (clickAction === "copy") {
                  $('form[role="add-update-product-form"]').attr(
                    "action",
                    $('meta[name="base-url"]').attr("content") +
                      `/admin/custom-billing/scheme-rule/add-schemes`
                  );
                }

                $("#addUpdateRulesModal").modal("show");
                break;

              case "FAILURE":
              default:
                Swal.fire({
                  title: "Failed",
                  text: response.message,
                  icon: "error",
                  buttonsStyling: !1,
                  confirmButtonText: "Close",
                  customClass: {
                    confirmButton: "btn btn-primary",
                  },
                });
                break;
            }

            addUpdateSchemeAndRulesAjax = false;
            $(this).prop("disabled", false);
            $(this).html(btnText);
            $("#addUpdateProductOverlay").addClass("d-none");
          },
          error: (xhr, status, error) => {
            Swal.fire({
              title: "HTTP Error",
              text: error,
              icon: "error",
              buttonsStyling: !1,
              confirmButtonText: "Close",
              customClass: {
                confirmButton: "btn btn-primary",
              },
            });

            addUpdateSchemeAndRulesAjax = false;
            $(this).prop("disabled", false);
            $(this).html(btnText);
            $("#addUpdateProductOverlay").addClass("d-none");
          },
        });
      }
    }
  );

  //bind change event service with product
  $("#addUpdateRulesModalTable").on(
    "change",
    ".updateRulesServiceId",
    function () {
      let srvId = $(this).val();
      let ele = $(this)
        .parent()
        .parent("tr")
        .find("select.updateRulesProductId");
      let eleVal = $(ele).attr("data-value");
      ele.prop("disabled", true);

      let productOptions = '<option value="">--Select--</option>';

      if (globalProducts) {
        globalProducts.map((productRow) => {
          if (srvId == productRow.service_id) {
            if (eleVal == productRow.product_id) {
              productOptions += `<option value="${productRow.product_id}" selected>${productRow.name}</option>`;
            } else {
              productOptions += `<option value="${productRow.product_id}">${productRow.name}</option>`;
            }
          }
        });
      }

      $(ele).html(productOptions);
      ele.prop("disabled", false);
      formSubmitButtonHandler("addUpdateRulesInput");
    }
  );

  /**
   * Handle delete user and scheme delete
   */
  $("#datatable_scheme_user_relation").on(
    "click",
    ".deleteUserSchemeRelation",
    function () {
      let relationToken = $(this).attr("data-relation");
      $(this).prop("disabled", true);
      let btnText = $(this).html();
      $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);

      Swal.fire({
        title: "Are you sure?",
        text: "Do you want to delete this relation?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        confirmButtonColor: "#e13131",
      }).then((result) => {
        if (result.isConfirmed) {
          //
          $.ajax({
            url:
              $('meta[name="base-url"]').attr("content") +
              "/admin/custom-billing/delete/scheme-relation",
            method: "POST",
            data: {
              relation_id: relationToken,
              _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: (response) => {
              if (response.status_code === "200") {
                $("#datatable_scheme_user_relation")
                  .DataTable()
                  .ajax.reload(null, false);
                $("#datatable_schemes_info")
                  .DataTable()
                  .ajax.reload(null, false);

                Swal.fire({
                  title: response.title,
                  text: response.message,
                  icon: "success",
                  buttonsStyling: !1,
                  confirmButtonText: "Close",
                  customClass: {
                    confirmButton: "btn btn-primary",
                  },
                });
              } else {
                Swal.fire({
                  title: response.title,
                  text: response.message,
                  icon: "error",
                  buttonsStyling: !1,
                  confirmButtonText: "Close",
                  customClass: {
                    confirmButton: "btn btn-primary",
                  },
                });

                $(this).prop("disabled", false);
                $(this).html(btnText);
              }
            },
            error: (xhr, status, error) => {
              Swal.fire({
                title: "HTTP Error",
                text: error,
                icon: "error",
                buttonsStyling: !1,
                confirmButtonText: "Close",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              });

              $(this).prop("disabled", false);
              $(this).html(btnText);
            },
          });
        } else {
          $(this).prop("disabled", false);
          $(this).html(btnText);
        }
      });
    }
  );

  /**
   * Datatable Options for List Schemes Info
   */
  datatableSetup(
    $('meta[name="base-url"]').attr("content") +
      "/admin/custom-billing/data-table/schemes-info",
    [
      {
        //"className": 'details-control',
        orderable: false,
        searchable: false,
        defaultContent: "",
        data: "count",
        render: function (data, type, full, meta) {
          return meta.row + 1;
        },
      },
      {
        data: "scheme_name",
        render: function (data, type, full) {
          return `<span class="btn-link cursor-pointer viewSchemeRules" data-schemeid="${full.id}">${data}</span>`;
        },
      },
      {
        data: "is_active",
        render: function (data, type, full) {
          if (full.is_assigned.length > 0) {
            return `<label class="badge badge-secondary">Assigned</label>`;
          } else if (data === "1") {
            return `<div class="icon-state"><label class="switch" onChange="schemesActiveInactive('${full.id}')"><input type="checkbox" checked><span class="switch-state"></span></label></div>`;
          } else {
            return `<div class="icon-state"><label class="switch" onChange="schemesActiveInactive('${full.id}')"><input type="checkbox"><span class="switch-state"></span></label></div>`;
          }
        },
      },
      {
        data: "new_created_at",
      },
      {
        orderable: false,
        data: null,
        render: function (data, type, full) {
          let actionBtn = "";
          actionBtn += `<button title="Edit Scheme" data-id="${full.id}" class="btn btn-link border btn-sm addUpdateSchemeAndRules" data-clickaction="update"><i class="fas fa-list"></i></button>`;
          actionBtn += `<button title="Copy Rules" data-id="${full.id}" class="btn btn-link border btn-sm addUpdateSchemeAndRules" data-clickaction="copy"><i class="far fa-copy"></i></i></button>`;
          return `<span class='inline-flex'>${actionBtn}</span>`;
        },
      },
    ],
    () => {},
    "#datatable_schemes_info"
  );

  /**
   * Datatable options User and Scheme Relation
   */
  datatableSetup(
    $('meta[name="base-url"]').attr("content") +
      "/admin/custom-billing/data-table/user-and-scheme",
    [
      {
        //"className": 'details-control',
        orderable: false,
        searchable: false,
        defaultContent: "",
        data: "count",
        render: function (data, type, full, meta) {
          return meta.row + 1;
        },
      },
      {
        orderable: false,
        data: "user_name_email.name",
      },
      {
        orderable: false,
        data: "user_name_email.email",
      },
      {
        orderable: false,
        data: "schemes_name.scheme_name",
      },
      {
        orderable: false,
        data: null,
        render: function (data, type, full) {
          let $actionBtn = "";
          $actionBtn += `<button type="button" data-relation="${full.id}" title="Delete User Relation" class="btn btn-link border btn-sm deleteUserSchemeRelation"><i class="far fa-trash"></i></button>`;
          return `<span class='inline-flex'>${$actionBtn}</span>`;
        },
      },
    ],
    () => {},
    "#datatable_scheme_user_relation",
    "#searchFormUserSchemeRelation"
  );

  /**
   * Handle Service and product view modal
   */
  $("#datatable_schemes_info").on("click", ".viewSchemeRules", function () {
    let serviceId = $(this).attr("data-schemeid");
    let htmlText = $(this).html();

    if (!addUpdateSchemeAndRulesAjax) {
      $("#viewProductOverlay").addClass("d-none");
      addUpdateSchemeAndRulesAjax = true;
      let btnText = $(this).html();
      $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);

      $.ajax({
        url:
          $('meta[name="base-url"]').attr("content") +
          `/admin/custom-billing/scheme-rule/fetch-list`,
        type: "POST",
        data: {
          scheme_id: serviceId,
          _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: (response) => {
          switch (response.status) {
            case "SUCCESS":
              $("#viewRulesName").html(htmlText);

              if (
                response.data.schemeRules !== "" &&
                response.data.schemeRules !== null &&
                response.data.schemeRules !== undefined
              ) {
                let formInputRows = "";

                response.data.schemeRules.forEach((row, index) => {
                  formInputRows += `
                                        <tr>
                                            <td class="text-center">${
                                              row.service_name
                                            }</td>
                                            <td class="text-center">${
                                              row.name
                                            }</td>
                                            <td class="text-center">${
                                              row.type === "fixed"
                                                ? "Fixed"
                                                : "Percent"
                                            }</td>
                                            <td class="text-center">${
                                              row.is_active === "1"
                                                ? "Active"
                                                : "Inactive"
                                            }</td>
                                            <td class="text-center">${
                                              row.start_value || ""
                                            }</td>
                                            <td class="text-center">${
                                              row.end_value || ""
                                            }</td>
                                            <td class="text-center">${
                                              row.fee || ""
                                            }</td>
                                            <td class="text-center">${
                                              row.min_fee || ""
                                            }</td>
                                            <td class="text-center">${
                                              row.max_fee || ""
                                            }</td>
                                        </tr>`;
                  addUpdateRulesCountFromDb++;
                });

                $("#viewRulesModalTable tbody").html(formInputRows);
              }

              $("#viewRulesModal").modal("show");
              break;

            case "FAILURE":
            default:
              Swal.fire({
                title: "Failed",
                text: response.message,
                icon: "error",
                buttonsStyling: !1,
                confirmButtonText: "Close",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              });
              break;
          }

          addUpdateSchemeAndRulesAjax = false;
          $(this).prop("disabled", false);
          $(this).html(btnText);
          $("#addUpdateProductOverlay").addClass("d-none");
        },
        error: (xhr, status, error) => {
          Swal.fire({
            title: "HTTP Error",
            text: error,
            icon: "error",
            buttonsStyling: !1,
            confirmButtonText: "Close",
            customClass: {
              confirmButton: "btn btn-primary",
            },
          });

          addUpdateSchemeAndRulesAjax = false;
          $(this).prop("disabled", false);
          $(this).html(btnText);
          $("#addUpdateProductOverlay").addClass("d-none");
        },
      });
    }
  });
});

/**
 * Handle Scheme list status
 */
function schemesActiveInactive(id) {
  $.ajax({
    url:
      $('meta[name="base-url"]').attr("content") +
      `/admin/custom-billing/status/schemes`,
    method: "POST",
    data: {
      schemeId: id,
      _token: $('meta[name="csrf-token"]').attr("content"),
    },
    success: function (response) {
      if (response.status_code === "200") {
        Swal.fire({
          title: response.title,
          text: response.message,
          icon: "success",
          buttonsStyling: !1,
          confirmButtonText: "Ok, got it!",
          customClass: {
            confirmButton: "btn btn-primary",
          },
        }).then((result) => {});
      } else {
        Swal.fire({
          title: response.title,
          text: response.message,
          icon: "error",
          buttonsStyling: !1,
          confirmButtonText: "Close",
          customClass: {
            confirmButton: "btn btn-primary",
          },
        });
      }
    },
    error: (xhr, status, error) => {
      Swal.fire({
        title: "HTTP Error",
        text: error,
        icon: "error",
        buttonsStyling: !1,
        confirmButtonText: "Close",
        customClass: {
          confirmButton: "btn btn-primary",
        },
      });
    },
  });
}

/**
 * Handle remove new rules row
 */
function removeNewRulesRow(currentObject, parentDiv) {
  Swal.fire({
    title: "Are you sure?",
    text: "Do you want to remove this row?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Remove",
    confirmButtonColor: "#e13131",
  }).then((result) => {
    if (result.isConfirmed) {
      $(currentObject).parent().parent(parentDiv).remove();
      // addUpdateRulesCountFromDb--;
      formSubmitButtonHandler("addUpdateRulesInput");
    }
  });
}

function addNewRulesRow(count) {
  let row = `
        <tr class="fromBtn">
            <td>
                <select class="form-control addUpdateRulesInput form-control-select updateRulesServiceId" data-value="" name="service[${count}]" required>
                        <option value="">--Select--</option>`;
  globalServices.map((serviceRow) => {
    row += `<option value="${serviceRow.service_id}">${serviceRow.service_name}</option>`;
  });

  row += `</select>
        </td>
        <td>
            <select class="form-control addUpdateRulesInput form-control-select updateRulesProductId" data-value="" name="product[${count}]" required>
                <option value="">--</option>
            </select>
        </td>
        <td>
            <select class="form-control addUpdateRulesInput form-control-select" data-value="" name="type[${count}]" required>
                <option value="fixed">Fixed</option>
                <option value="percent">Percent</option>
            </select>
        </td>
        <td>
            <select class="form-control addUpdateRulesInput form-control-select" data-value="" name="is_active[${count}]" required>
                <option value="1" selected>Active</option>
                <option value="0"}>Inactive</option>
            </select>
        </td>
        <td>
            <input type="number" min="0" class="form-control addUpdateRulesInput" placeholder="Start Value" name="start_value[${count}]" />
        </td>
        <td><input type="number" min="0" class="form-control addUpdateRulesInput" placeholder="End Value" name="end_value[${count}]" /></td>
        <td><input type="number" min="0" class="form-control addUpdateRulesInput" placeholder="Fee" name="fee[${count}]" /></td>
        <td><input type="number" min="0" class="form-control addUpdateRulesInput" placeholder="Min Fee" name="min_fee[${count}]" /></td>
        <td><input type="number" min="0" class="form-control addUpdateRulesInput" placeholder="Max Fee" name="max_fee[${count}]" /></td>
        <td class="text-center"><span class="btn removeNewRulesRow"><i class="fas fa-times-square"></i></span></td>
    </tr>`;

  return row;
}

/**
 * Assign Scheme to user Handler
 */
function assignSchemeCallback(response) {
  if (response.status_code === "200") {
    $("#user_token").val("").trigger("change");
    $("#scheme_id").val("").trigger("change");
    $('form[role="assign-scheme-form"]').trigger("reset");
    $("#assignSchemeModal").modal("hide");
    $("#datatable_scheme_user_relation").DataTable().ajax.reload();
    $("#datatable_schemes_info").DataTable().ajax.reload(null, false);
  }
}

function addUpdateRulesCallback(response) {
  // if (response.status_code === '200') {
  // if (currentClickedUpdateRuleButton !== null) {
  //     $(currentClickedUpdateRuleButton).trigger('click');
  //     $('#btn_addUpdateRulesInput').prop('disabled', true);
  // }
  // $('#datatable_schemes_info').DataTable().ajax.reload();
  // }
}

function formSubmitButtonHandler(formName) {
  var isButtonEnable = false;

  switch (formName) {
    case "addUpdateRulesInput":
      var eleInputs = $("." + formName);
      for (let i = 0; i < eleInputs.length; i++) {
        if (eleInputs.eq(i).hasClass("form-control-select")) {
          if (eleInputs.eq(i).val() !== eleInputs.eq(i).attr("data-value")) {
            isButtonEnable = true;
          }
        } else {
          if (eleInputs.eq(i).val() !== eleInputs.eq(i).prop("defaultValue")) {
            isButtonEnable = true;
          }
        }
      }

      $("#btn_" + formName).prop("disabled", !isButtonEnable);
      break;
  }
}

function datatableSetup(
  urls,
  datas,
  onDraw = function () {},
  ele = "#datatable",
  formId = null,
  element = {}
) {
  var options = {
    processing: true,
    serverSide: true,
    ordering: true,
    searching: true,
    buttons: [],
    order: [],
    columnDefs: [
      {
        defaultContent: "-",
        targets: [0],
        /* column index [0,1,2,3]*/
        orderable: false,
        /* true or false */
      },
    ],
    lengthMenu: [
      [10, 25, 50, 75, 100, 200, 500, 1000, -1],
      [10, 25, 50, 75, 100, 200, 500, 1000, 1500],
    ],
    dom: "Bfrltip",
    language: {
      paginate: {
        first: "First",
        last: "Last",
        next: "&rarr;",
        previous: "&larr;",
      },
    },
    drawCallback: function () {
      $(this)
        .find("tbody tr")
        .slice(-3)
        .find(".dropdown, .btn-group")
        .addClass("dropup");
    },
    preDrawCallback: function () {
      $(this)
        .find("tbody tr")
        .slice(-3)
        .find(".dropdown, .btn-group")
        .removeClass("dropup");
    },
    ajax: {
      url: urls,
      type: "post",
      data: function (d) {
        $("");
        d._token = $('meta[name="csrf-token"]').attr("content");
        if (formId) {
          d.from = $(formId).find('[name="from"]').val();
          d.to = $(formId).find('[name="to"]').val();
          d.searchText = $(formId).find('[name="searchText"]').val();
          d.payoutReference = $(formId).find('[name="payoutReference"]').val();
          d.status = $(formId).find('[name="status"]').val();
          d.user_id = $(formId).find('[name="user_id"]').val();
          d.scheme_id = $(formId).find('[name="scheme_id"]').val();
          d.scheme_id_relation = $(formId)
            .find('[name="scheme_id_relation"]')
            .val();
          d.service_id = $(formId).find('[name="service_id"]').val();
          d.product_id = $(formId).find('[name="product_id"]').val();
        }
      },
      beforeSend: function () {},
      complete: function () {
        $(formId).find('button[type="submit"]').html("Search");
        $(formId).find('button[type="button"]').html("Reset");
      },
      error: function (response) {},
    },
    columns: datas,
  };

  $.each(element, function (index, val) {
    options[index] = val;
  });

  var DT = $(ele).DataTable(options).on("draw.dt", onDraw);
  return DT;
}

$("form#searchFormUserSchemeRelation").on("submit", function (e) {
  e.preventDefault();
  $("#searchFormUserSchemeRelation")
    .find("button:submit")
    .html(`<i class='fa fa-spin fa-spinner'></i>`);
  $("#datatable_scheme_user_relation").dataTable().api().ajax.reload();
  return false;
});

$("#resetFormUserSchemeRelation").on("click", function () {
  $("#userSchemeRelationId").val("").trigger("change");
  $("#scheme_id_relation").val("").trigger("change");
  $("#resetFormUserSchemeRelation").trigger("reset");
  $(this).html(`<i class='fa fa-spin fa-spinner'></i>`);
  $("#datatable_scheme_user_relation").dataTable().api().ajax.reload();
});
