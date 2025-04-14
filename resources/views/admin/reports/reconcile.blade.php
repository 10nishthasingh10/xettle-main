@extends('layouts.admin.app')
@section('title','Payout Dashboard')
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
            <div class="element-box">
                <h5 class="form-header">
                    {{$page_title}}
                </h5>

                <form id="searchForm">

                    <fieldset class="form-group">

                        <div class="row">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" id="from"  class="form-control" value="" />
                                    <span id="fromSpan" style="color:red;"></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" id="to" class="form-control" value="" />
                                    <span id="toSpan" style="color:red;"></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">User <span class="requiredstar"></span></label>
                                    <select class="form-control select2" id="user_id"  name="user_id">
                                        <option value="">-- Select User --</option>
                                        @foreach($userData as $val)
                                        <option value="{{$val->id}}">{{$val->userName}}</option>
                                        @endforeach
                                    </select>
                                    <span id="useridSpan" style="color:red;"></span>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-90px"  id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
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
            <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-4">
                                
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="cashInAmount">
                                       0 | ₹0
                                        </div>
                                        <div class="label">
                                         Cash In Amounts & Counts 
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-danger" href="#">
                                        <div class="value font-1-5" id="cashOutAmount">
                                       0 | ₹0
                                        </div>
                                        <div class="label">
                                        Cash Out Amounts & Counts 
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-4">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-info" href="#">
                                        <div class="value font-1-5" id="fee_tax">
                                        0 | ₹0
                                        </div>
                                        <div class="label">
                                          Fee & Tax Amounts & Counts 
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="POAmount">
                                        ₹0
                                        </div>
                                        <div class="label">
                                         Primary Opening Amount 
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="#">
                                        <div class="value font-1-5" id="PCAmount">
                                       ₹0
                                        </div>
                                        <div class="label">
                                        Primary Closing Amount 
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-info" href="#">
                                        <div class="value font-1-5" id="PTOAmount">
                                        ₹0
                                        </div>
                                        <div class="label">
                                          Payout Opening Amount
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-info" href="#">
                                        <div class="value font-1-5" id="PTCAmount">
                                        ₹0
                                        </div>
                                        <div class="label">
                                          Payout Closing Amount
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-info" href="#">
                                        <div class="value font-1-5" id="payoutAbleAmount">
                                        ₹0
                                        </div>
                                        <div class="label">
                                          Total Payout Able Amount
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-info" href="#">
                                        <div class="value font-1-5" id="tottalCashOut">
                                        ₹0
                                        </div>
                                        <div class="label">
                                          Total Cash Out
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-info" href="#">
                                        <div class="value font-1-5" id="availableamount">
                                        ₹0
                                        </div>
                                        <div class="label">
                                          Available Amount
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-info" href="#">
                                        <div class="value font-1-5" id="diffamount">
                                        ₹0
                                        </div>
                                        <div class="label">
                                          Difference
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="element-box">
                <h5 class="form-header">
                    {{$page_title}}
                </h5>

                    <div class="table-responsive" id="responseTable">
                    </div>
                    </div>
            </div>
        </div>

    </div>
</div>

@endsection
@section('scripts')

<script src="{{url('public/js/handlebars.js')}}"></script>
<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
<script>
    $(document).ready(function() {
        let currDate = `{{date('Y-m-d', strtotime('-1 day'))}}`;
        $('#to').attr('max', currDate);
        $('#from').attr('max', currDate);
    });
    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        
        var from = $(this).find('input[name="from"]').val();
        $('#fromSpan').text('');
        if (from == '') {
            $('#fromSpan').text('This filed is required');
        }
        var to = $(this).find('input[name="to"]').val();
        $('#toSpan').text('');
        if (to == '') {
            $('#toSpan').text('This filed is required');
        }
        var userId = $('#searchForm').find('[name="user_id"]').val();
        $('#useridSpan').text('');
        if (userId == '') {
            $('#useridSpan').text('This filed is required');
        }
        getPayoutRecords(from, to, userId);
       // getAepsRecords(from, to);
        return false;
    });

   function getPayoutRecords(from, to, userId)
   {
        
        var url = "{{url('/admin/reconcileReport')}}";
        if(from && to && userId)
        {
            $('#searching').attr('disabled', 'disabled');
            $('#searching').text('Loading...');
            $.post(url, { from: from, to: to, user_id: userId, _token:"{{csrf_token()}}"}, function(response) {
            $('#responseTable').html(response.data.table);
            //console.log(response, response.data.cashIn);
            $('#cashInAmount').html(response.data.cashInAmount);
            $('#cashOutAmount').html(response.data.cashOutAmount);
            $('#fee_tax').html(response.data.totalFeeTax);
            console.log(response.data.primaryOpeningBalance);
            $('#POAmount').html('₹'+response.data.primaryOpeningBalance);
            $('#PCAmount').html('₹'+response.data.primaryClosingBalance);
            $('#PTCAmount').html('₹'+response.data.payoutClosingBalance);
            $('#PTOAmount').html('₹'+response.data.payoutOpeningBalance);
            $('#diffamount').html('₹'+response.data.diff);
            $('#payoutAbleAmount').html('₹'+response.data.payoutAbleAmount);
            $('#tottalCashOut').html('₹'+response.data.tottalCashOut);
            $('#availableamount').html('₹'+response.data.availablebalance);
            $('#searching').attr('disabled', false);
            $('#searching').text('Search');
            
        });
        }
        
        
    }
    </script>
@endsection