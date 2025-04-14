@extends('layouts.admin.app')
@section('title',ucfirst($site_title))

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

    .expandtable b,
    #utr-response-data tbody td b {
        color: #047bf8;
    }

    .content-box {
        padding: 10px !important;
    }

    .element-box {
        padding: 1.5rem 1rem !important;
    }
</style>
<!--begin::Table-->
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper">
            <div class="element-box">
                <h5 class="form-header">
                    {{$page_title}}
                </h5>

                <div class="row">

                    <div class="col-md-12 border rounded pt-4 pb-4">
                        <form class="form" method="post" role="smart-collect-settlement" action="{{custom_secure_url('admin/manual-settlement/smart-collect')}}">
                            @csrf()
                            <div class="row m-1">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Select User <span class="required" style="color:red">*</span></label>
                                        <select class="form-control select2" name="user_id" required="">
                                            <option value="">Select User</option>
                                            @foreach($userList as $row)
                                            <option value="{{$row->id}}">{{$row->id}} - {{$row->name}} - {{strtoupper($row->email)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label> Start Date <span class="required" style="color:red">*</span></label>
                                        <input class="form-control" name="start_date" placeholder="Start Date" required="" type="date">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label> End Date <span class="required" style="color:red">*</span></label>
                                        <input class="form-control" name="end_date" placeholder="End Date" required="" type="date">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-check-label">
                                            Send Webhook:
                                            <span class="required" style="color:red">*</span>
                                        </label>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="webhook" id="webhook1" value="yes">
                                            <label class="form-check-label" for="webhook1">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="webhook" id="webhook2" value="no">
                                            <label class="form-check-label" for="webhook2">No</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-check-label">
                                            Settlement Type:
                                            <span class="required" style="color:red">*</span>
                                        </label>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="settlement_type" id="settlement_type1" value="batch">
                                            <label class="form-check-label" for="settlement_type1">Batch Txns</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="settlement_type" id="settlement_type2" value="single">
                                            <label class="form-check-label" for="settlement_type2">Single Txns</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-check-label">
                                            Root Type:
                                            <span class="required" style="color:red">*</span>
                                        </label>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="root_type" id="root1" value="van">
                                            <label class="form-check-label" for="root1">VAN</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="root_type" id="root2" value="upi">
                                            <label class="form-check-label" for="root2">UPI</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 text-right">
                                    <button type="submit" data-callbackfn="showVanInfoCb" data-request="ajax-submit" data-target="[role=smart-collect-settlement]" id="btnAmountTransfer" class="btn btn-primary">
                                        Fetch Settlement
                                    </button>
                                </div>
                            </div>

                        </form>

                    </div>

                </div>

                <div class="row">
                    <div class="col-md-12 border rounded pt-4 pb-4 d-none" id="utr-response-data">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <tbody></tbody>
                            </table>
                        </div>

                        <form role="form-ebz-van-with-kyc-final" action="{{url('admin/manual-settlement/smart-collect-submit')}}" data-DataTables="datatable" method="POST">
                            <fieldset class="form-group">
                                @csrf()
                                <div class="col-md-12">
                                    <div class="col-md-12 text-right">
                                        <input type="hidden" name="user_id" id="user_id">
                                        <input type="hidden" name="start_date" id="start_date">
                                        <input type="hidden" name="end_date" id="end_date">
                                        <input type="hidden" name="webhook" id="webhook">
                                        <input type="hidden" name="settlement_type" id="settlement_type">
                                        <input type="hidden" name="root_type" id="root_type">
                                        <button type="submit" class="btn btn-danger d-none" data-confirmation="yes" data-request="ajax-submit" data-target='[role="form-ebz-van-with-kyc-final"]' id="final-submit">
                                            <b><i class="icon-search4"></i></b> Settle Transactions
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>

            </div>

            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="p-2 h6">Unsettle Transactions</div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                                <thead>
                                    <tr>
                                        <th>S.N.</th>
                                        <th>Month</th>
                                        <th>User ID</th>
                                        <th>User Name</th>
                                        <th>User Email</th>
                                        <th>Amount</th>
                                        <th>Txn Counts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

<div class="modal" id="payloadModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" id="payloadModalData">
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@section('scripts')
<script src="{{url('public/js/handlebars.js')}}"></script>
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });
    });

    $(document).ready(function() {
        var url = "{{custom_secure_url('admin/manual-settlement/report/smart-collect')}}";
        var onDraw = function() {};
        var options = [{
                //"className": 'details-control',
                "orderable": false,
                "searchable": false,
                "defaultContent": '',
                "data": 'count',
                render: function(data, type, full, meta) {
                    let start = parseInt(meta.settings.json.start);
                    return meta.row + (start + 1);
                }
            },
            {
                "data": "month",
                render: function(data, type, full, meta) {
                    return `<button type="button" class="btn btn-sm btn-primary open-date-modal" data-dates="${full.dates}">${data}</button>`;
                }
            },
            {
                "data": "user_id",
            },
            {
                "data": "name",
            },
            {
                "data": "email",
            },
            {
                "data": "amount"
            },
            {
                "data": "count"
            },
        ];
        datatableSetup(url, options, onDraw);
    });

    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
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
                    d.status = $('#searchForm').find('[name="status"]').val();
                    d.user_id = $('#searchForm').find('[name="user_id"]').val();
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
        return DT;
    }

    function showVanInfoCb(response) {
        if (response.status === 'SUCCESS') {
            let res = `
                    <tr>
                        <td><b>Transactions :</b> ${response.data.count}</td>
                        <td><b>Amount :</b> ${response.data.amount} Rs.</td>
                    </tr>
                    `;

            $('#utr-response-data').removeClass('d-none');
            $('#utr-response-data table tbody').html(res);
            $('#user_id').val(response.data.inputs.user_id);
            $('#start_date').val(response.data.inputs.start_date);
            $('#end_date').val(response.data.inputs.end_date);
            $('#webhook').val(response.data.inputs.webhook);
            $('#settlement_type').val(response.data.inputs.settlement_type);
            $('#root_type').val(response.data.inputs.root_type);

            if (response.data.count > 0) {
                $('#final-submit').removeClass('d-none');
            } else {
                $('#final-submit').addClass('d-none');
            }
        }
    }

    $('#datatable').on('click', '.open-date-modal', function() {
        let data = $(this).attr('data-dates');
        let date = $(this).html();
        $("#payloadModal").on('show.bs.modal', function() {
            $('#payloadModal .modal-title').html(`Dates: ${date}`);

            data = data.split(",");
            data = data.sort();
            data = [...new Set(data)];
            data = data.join(", ");

            $('#payloadModalData').html(data);
        });
        $('#payloadModal').modal('show');
        $("#payloadModal").on('hidden.bs.modal', function() {
            $('#payloadModalData').html('');
            $('#payloadModal .modal-title').html('');
        });
    });
</script>
@endsection
@endsection