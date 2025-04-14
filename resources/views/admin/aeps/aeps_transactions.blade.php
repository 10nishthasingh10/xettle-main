@extends('layouts.admin.app')
@section('title','Aeps Transactions')
@section('content')
<style type="text/css">
    .expandtable {
        width: 100% !important;
        margin-bottom: 1rem;
    }

    .expandtable,
    tbody,
    tr,
    td {

        margin-bottom: 1rem;
    }

    .content-box {
        padding: 10px !important;
    }

    .element-box {
        padding: 1.5rem 1rem !important;
    }
</style>
<div class="content-w">
    <div class="content-box">
        <div class="element-wrapper">
            <div class="element-content">
                <div class="tablo-with-chart">

                </div>
            </div>
            <div class="element-box">
                <h5 class="form-header">
                    {{$page_title}}
                </h5>
                <div class="element-actions">
                </div>

                <form id="searchForm">

                    <fieldset class="form-group">

                        <div class="row">
                        <div class="col-md-2">
                                <div class="form-group">
                                    <label>Any Key <span class="requiredstar"></span></label>
                                    <input type="text" name="searchText" class="form-control" placeholder="Enter Search Key" />
                                    <input type="hidden" name="tr_type" value="dr" />
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" @if(isset($_GET['from'])) value="{{$_GET['from']}}"  @endif />
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" @if(isset($_GET['to'])) value="{{$_GET['to']}}"  @endif />
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user_id">Filter by User</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        @foreach($userData as $user)
                                        <option value="{{$user->id}}">{{$user->userName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Status:</label>
                                    <select class="form-control js-example-basic-multiple" multiple="multiple"   name="status">
                                      <option value="success">Success</option>
                                      <option value="pending">Pending</option>
                                      <option value="failed">Failed</option>
                                      <option value="disputed">Disputed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Txn Type:</label>
                                    <select class="form-control js-example-basic-multiple" multiple="multiple"   name="transaction_type">
                                      <option value="CW">CW</option>
                                      <option value="MS">MS</option>
                                      <option value="BE">BE</option>
                                      <option value="AP">AP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Route Type:</label>
                                    <select class="form-control js-example-basic-multiple" multiple="multiple" name="route_type">
                                      <option value="icici">ICICI</option>
                                      <option value="airtel">AIRTEL</option>
                                      <option value="sbm">SBM</option>
                                      <option value="paytm">PAYTM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="row">
                                <input type="hidden" name="queryString" id="queryString" value="{{@$_GET['bank_ref']}}" />
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                <b><i class="icon-search4"></i></b> Search
                                            </button>
                                            <button type="button" class="btn btn-warning btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Reset">
                                                <b><i class="icon-rotate-ccw3"></i></b> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Merchant </th>
                                        <th>Client Ref Id </th>
                                        <th>Aadhaar/ Bank </th>
                                        <th>Mobile</th>
                                        <th> Route | Transaction Type</th>
                                        <th>Amount</th>
                                        <th>Commission | TDS</th>
                                        <th>Comm Ref Id</th>
                                        <th>Stan No</th>
                                        <th>RRN</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th>Total</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!--------------------
                START - Color Scheme Toggler
                -------------------->
    </div>
</div>

<div class="onboarding-modal modal fade animated" id="modal-order-status" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content">

            <button aria-label="Close"  class="close" data-dismiss="modal" type="button">
					<span class="close-label"></span><span class="os-icon os-icon-close">

                    </span></button>
            <form action="{{url('admin/recharge/status/update')}}" method="POST" role="edit-admin">
                <div class="modal-header">
                    <h6 class="modal-title w-100" id="exampleModalLabel">
                        Status Discrepancy
                    </h6>
                </div>


                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-sm-12 mb-1 text-center">

                           <!-- <div role="alert" id="message">
                            </div> -->
                        </div>
                    </div>
                    <input type="hidden" name="agentId" id="agentId" />
                    <div class="row">
                        <div class="col-6 col-sm-6 mb-1">
                            <label class="col-form-label"><b>Client Ref Id</b></label>
                        </div>
                        <div class="col-6 col-sm-6 mb-1">
                            <span class="col-form-label" id="orderId"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 col-sm-6 mb-1">
                            <label class="col-form-label"><b>Amount at our end</b></label>
                        </div>
                        <div class="col-6 col-sm-6 mb-1">
                            <span class="col-form-label" id="amountOur"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-sm-6 mb-1">
                            <label class="col-form-label"><b>Amount at MH end</b></label>
                        </div>
                        <div class="col-6 col-sm-6 mb-1">
                            <span class="col-form-label" id="amountMh"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-sm-6 mb-1">
                            <label class="col-form-label"><b>Status At our end</b></label>
                        </div>
                        <div class="col-6 col-sm-6 mb-1">
                            <span class="col-form-label" id="statusOur"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-sm-6 mb-1">
                            <label class="col-form-label"><b>Status At MH end</b></label>
                        </div>
                        <div class="col-6 col-sm-6 mb-1">
                            <span class="col-form-label" id="statusMh"></span>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">

                    <span class="btn btn-primary me-1 mt-1 waves-effect waves-float waves" style="cursor: pointer;" id="submitStatus">Update Status</span>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('scripts')

<script src="{{url('public/js/handlebars.js')}}"></script>
<script id="details-template" type="text/x-handlebars-template">


</script>
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>


<script type="text/javascript">
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
        var url = "{{custom_secure_url('admin/fetch')}}/aepsTransaction/0";
        var onDraw = function() {};
        var options = [{
                "data": "user",
                render: function(data, type, full, meta) {

                    if (data == null) {
                        var $user = '-';
                    } else {
                        var $user = data.name + ' <br/>' + data.email;
                    }
                    return $user;
                }
            },
            {
                "data": "merchant_code",
                render: function(data, type, full, meta) {
                    if (data == null) {
                        var $user = '-';
                    } else {
                        if (full.merchant != null) {
                            var $user = data + ' <br/>' + full.merchant.first_name;
                            if (full.merchant.last_name != null) {
                                $user = data + ' <br/>' + full.merchant.first_name + ' ' + full.merchant.last_name;
                            }
                        } else {
                            var $user = data;
                        }
                    }
                    return $user;
                }
            },
            {
                "data": "client_ref_id"
            },
            {
                "data": "aadhaar_no",
                render: function(data, type, full, meta) {
                    if (data.Length == 0) {
                        var $user = 'XXXXXXXXXXXX';
                    } else {
                        var $user = 'XXXXXXXX' + data.substr(data.length - 4);
                    }
                    if (full.bank != null) {
                        $user = $user+'/ '+full.bank.bank;
                    }

                    return $user;
                }
            },
            {
                "data": "mobile_no"
            },
            {
                "data": "transaction_type",
                render: function(data, type, full, meta) {
                    if (data != null) {
                        return full.route_type.toUpperCase() + ' | ' + data.toUpperCase();
                    } else {
                        return '';
                    }
                }
            },
            {
                "data": "transaction_amount"
            },
            { "data" : "status" , render: function(data, type, full, meta) {
                    let comm = 0;
                    let tds = 0;
                    if (data == 'success' || data == 'disputed') {
                        comm = full.commission;
                        tds = full.tds;
                    }
                    return parseFloat(comm).toFixed(2)+' | '+parseFloat(tds).toFixed(2);
                }
            },
            {
                "data": "commission_ref_id"
            },
            {
                "data": "resp_stan_no"
            },
            {
                "data": "rrn"
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    var failedMessage = '';
                    if (data == 'failed' && full.failed_message != null && full.failed_message.length > 0 ) {
                        failedMessage = "<br/>( "+full.failed_message+" )";
                    }
                    return showSpan(data)+failedMessage;
                }
            },
            {
                "data": "new_created_at",
            },
            {
                "data": "status",
                render: function(data, type, full, meta) {
                    var failedMessage = '';
                    if ("{{Auth::user()->hasRole('super-admin')}}" &&  (data == 'failed' ||  data == 'success' || data == 'pending')) {
                        return '<span onclick="aepsStatus(`' + full.merchant_code + '`, `' + full.client_ref_id + '`, ' + full.transaction_amount + ', `' + full.status + '`, `' + full.user_id + '`)" class="btn btn-primary"> Check Discrepancy</span>';
                    } else {
                        return '';
                    }
                }
            },
        ];
        datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width",$(".table-responsive").width());
    });

    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        $('#datatable').dataTable().api().ajax.reload();
        return false;
    });

    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            scrollX: true,
            buttons: [
                'excel'
            ],
            order: [],
            columnDefs: [{
                "defaultContent": "-",
                'targets': [0],
                /* column index [0,1,2,3]*/
                'orderable': false,
                /* true or false */
            }],
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000, -1],
                [10, 25, 50, 75, 100, 200, 500, 1000, 1500]
            ],
            dom: "Bfrltip",
            language: {
                paginate: {
                    'first': 'First',
                    'last': 'Last',
                    'next': '&rarr;',
                    'previous': '&larr;'
                }
            },
            drawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
            },
            preDrawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
            },
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api();

                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // Total over all pages
                total = api
                    .column(5)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                pageTotal = api
                    .column(5, {
                        page: 'current'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(5).footer()).html(
                    pageTotal
                );
            },
            ajax: {
                url: urls,
                type: "post",
                data: function(d) {
                    $("")
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.payoutReference = $('#searchForm').find('[name="payoutReference"]').val();
                    d.user_id = $('#searchForm').find('[name="user_id"]').val();
                    d.apes_status_array= $('#searchForm').find('[name="status"]').val();
                    d.transaction_type_array = $('#searchForm').find('[name="transaction_type"]').val();
                    d.route_type_array = $('#searchForm').find('[name="route_type"]').val();
                },
                beforeSend: function() {},
                complete: function() {
                    $('#searchForm').find('button:submit').button('reset');
                    $('#formReset').button('reset');
                },
                error: function(response) {}
            },
            columns: datas
        };

        $.each(element, function(index, val) {
            options[index] = val;
        });

        var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
        DT.search($('#queryString').val()).draw();
        return DT;
    }

    $('#formReset').click(function() {
        $('form#searchForm')[0].reset();
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });


    function aepsStatus(merchantCode, orderId, amount, status, agentId) {
        $('#modal-order-status').modal('show');
        var base_url = window.location.origin;
        localStorage.setItem("failedMessage", '');
        $.ajax({
            url: base_url + "/admin/aeps/discrepancy/status",
            method:"post",
            data:{'clientRefId': orderId, 'merchantCode' : merchantCode, '_token': "{{csrf_token()}}"},
            success: function(result) {
                //var result = result.response;
                localStorage.setItem("stanno", '');
                localStorage.setItem("rrn", '');
                $('#amountMh').text('');
                $('#statusMh').text('');
                $('#message').removeClass('alert alert-success  p-1');
                $('#message').removeClass('alert alert-danger  p-1');
                $('#orderId').text(orderId);
                $('#amountOur').text(amount);
                $('#statusOur').removeClass('badge-success');
                $('#statusOur').removeClass('badge-danger');
                $('#statusOur').removeClass('badge badge-danger badge-success');
                $('#statusOur').removeClass('badge-success badge-danger badge-warning');
                if (status == 'success') {
                    $('#statusOur').addClass('badge badge-success');
                } else if (status == 'pending')  {
                    $('#statusOur').addClass('badge badge-warning');
                } else {
                    $('#statusOur').addClass('badge badge-danger');
                }
                $('#statusOur').text(status);
                $('#agentId').val(agentId);

                if (result != null) {
                    $('#message').removeClass('alert-success');
                    $('#message').removeClass('alert-danger');
                    if (result.statuscode == '000') {
                        $('#message').addClass('alert alert-success  p-1');
                        localStorage.setItem("stanno", result.data.stanno);
                        localStorage.setItem("rrn", result.data.rrn);

                        $('#amountMh').text(result.data.transactionAmount);
                        $('#statusMh').removeClass('alert alert-danger');
                        $('#statusMh').removeClass('badge badge-danger');
                            $('#statusMh').addClass('badge badge-success');
                        localStorage.setItem("orderStatus", 'success');
                        $('#statusMh').text('success');

                        if (result.data != null) {
                            if (status == 'success') {
                                $('#message').addClass('alert alert-success  p-1');
                                $('#message').text('Discrepancy not found.');
                                $('#submitStatus').css('display', 'none');
                            } else {
                                $('#message').addClass('alert alert-danger  p-1');
                                $('#message').text('Discrepancy found.');
                                $('#submitStatus').css('display', 'block');
                            }
                        }

                    } else if (result.statuscode == '001') {

                        if (result.data != null) {
                            localStorage.setItem("stanno", result.data.stanno);
                            localStorage.setItem("rrn", result.data.rrn);
                            if (status == 'failed') {
                                $('#message').addClass('alert alert-success  p-1');
                                $('#message').text('Discrepancy not found.');
                                $('#submitStatus').css('display', 'none');
                            } else {
                                $('#message').addClass('alert alert-danger  p-1');
                                $('#message').text('Discrepancy found.');
                                $('#submitStatus').css('display', 'block');
                            }
                        } else {
                            if (status == 'failed') {
                                $('#submitStatus').css('display', 'none');
                            } else {
                                $('#submitStatus').css('display', 'block');
                            }
                        }

                        if (result.data != null) {
                            $('#amountMh').text(result.data.transactionAmount);
                        }

                        $('#statusMh').addClass('badge badge-danger');
                        if (result.data == null) {
                            $('#statusMh').text('failed');
                        } else {
                            $('#statusMh').text('failed');
                        }
                        localStorage.setItem("orderStatus", 'failed');
                        localStorage.setItem("failedMessage", result.message);
                    } else if (result.code == '0x0203') {
                        $('#message').addClass('alert alert-danger  p-1');
                        $('#message').text('Discrepancy not found.');

                        localStorage.setItem("orderStatus", 'FAILURE');
                        localStorage.setItem("failedMessage", result.message);
                    }
                }
            }
        });
    }

    $('#submitStatus').on('click', function() {
        $('#modal-order-status').modal('hide');
        var orderStatus = localStorage.getItem("orderStatus");
        var orderId = $('#orderId').text();
        var agentId = $('#agentId').val();
        var base_url = window.location.origin;
        var failedMessage = localStorage.getItem("failedMessage");
        var rrn = localStorage.getItem("rrn");
        var stanno = localStorage.getItem("stanno");
        $.ajax({
            type: 'post',
            url: base_url + "/admin/aeps/status/update",
            data: {
                orderId: orderId,
                status: orderStatus,
                failedMessage: failedMessage,
                agentId: agentId,
                rrn: rrn,
                stanno: stanno,
                '_token': "{{csrf_token()}}"
            },
            success: function(result) {
                if (result.code == '0x0201' || result.code == '0x0401') {
                    Swal.fire({
                        title: "OOPS",
                        text: result.message,
                        icon: "error",
                        buttonsStyling: !1
                    });
                    $('#datatable').dataTable().api().ajax.reload();
                } else if (result.code == '0x0200') {
                    Swal.fire({
                        title: "Good job!",
                        text: result.message,
                        icon: "success",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        },
                        buttonsStyling: !1
                    });
                    $('#datatable').dataTable().api().ajax.reload();
                } else if (result.code == '0x0202') {
                    Swal.fire({
                        title: "OOPS",
                        text: result.message,
                        icon: "error",
                        buttonsStyling: !1
                    });
                    $('#datatable').dataTable().api().ajax.reload();
                }


            }
        });
    });
</script>

@endsection