@if(Request::is('payout/*') || Request::is('payout') || Request::is('aeps/*') || Request::is('aeps') || Request::is('upi') || Request::is('upi/*') || Request::is('collect') || Request::is('collect/*') || Request::is('va') || Request::is('va/*') || Request::is('verification') || Request::is('verification/*') )
@foreach($serviceData as $myservice)
<?php
if ($myservice->service_slug == 'upi_collect') {
    $newcheckUri = 'upi';
    $checkUri = Request::is($newcheckUri . '/*');
    $newcheckUri = Request::is($newcheckUri);
} elseif ($myservice->service_slug == 'smart_collect') {
    $newcheckUri = 'collect';
    $checkUri = Request::is($newcheckUri . '/*');
    $newcheckUri = Request::is($newcheckUri);
} elseif ($myservice->service_slug == 'verification') {
    $newcheckUri = 'verification';
    $checkUri = Request::is($newcheckUri . '/*');
    $newcheckUri = Request::is($newcheckUri);
} else {
    $checkUri = Request::is($myservice->service_slug . '/*');
    $newcheckUri = Request::is($myservice->service_slug);
}

$aepsTodayBusiness =  DB::table('aeps_transactions')
->whereDate('created_at', date('Y-m-d'))
->where(['transaction_type' => 'cw', 'user_id' => Auth::user()->id, 'status' => 'success'])
->sum('transaction_amount');
$unsettledThresholdAmount = @DB::table('user_config')
                        ->select("threshold as amt")
                        ->where('user_id', Auth::user()->id)
                        ->first()->amt;
?>
@if($checkUri || $newcheckUri)
<div class="fancy-selector-current ">
    <div class="fs-img">
       
    </div>
    <div class="fs-main-info">
        <div class="fs-name">
            {{$myservice->service_name}}
        </div>
        @if( Request::segment(1) == 'upi')
        <?php
        $data = CommonHelper::callBackUPITotalAmount(Auth::user()->id);
        $totalAmount = $data['amount'];
        ?>
        <div class="fs-sub">
            <span>Balance:</span> <strong>₹{{$totalAmount}}</strong>
        </div>

        @elseif( Request::segment(1) == 'collect')
        <div class="fs-sub">
            <span>Balance:</span> <strong>₹{{(CommonHelper::callBackSmartCollectTotalAmount(Auth::user()->id))['amount']}}</strong>
        </div>

        @elseif( Request::segment(1) == 'va')
        <div class="fs-sub">
            <span>Balance:</span> <strong>₹{{(CommonHelper::callBackVirtualAccountTotalAmount(Auth::user()->id))['amount']}}</strong>
        </div>

        @elseif( Request::segment(1) == 'verification')
        <div class="fs-sub">
            <span></span>
        </div>

        @elseif(Request::segment(1) == 'aeps')
        @if(CommonHelper::isServiceActive(Auth::user()->id,$myservice->service_id))
        <div class="fs-sub">
            <span>Balance:</span> <strong>₹{{$aepsTodayBusiness}}</strong>
        </div>
        @endif
        @else
        @if(CommonHelper::isServiceActive(Auth::user()->id,$myservice->service_id))
        <div class="fs-sub">
            <?php
            if (Request::segment(1) == 'payout') {
                $lockedAmount = isset(\DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum) ? \DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum : 0;
            } else {
                $lockedAmount = isset(CommonHelper::getServiceAccount(Auth::user()->id, $myservice->service_id)->locked_amount) ? CommonHelper::getServiceAccount(Auth::user()->id, $myservice->service_id)->locked_amount : 0;
            }
            ?>

            <span>Balance:</span> <strong>₹{{CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->transaction_amount + $lockedAmount}}</strong>
        </div>
        @endif
        @endif
    </div>
    <div class="fs-extra-info">
        @if(CommonHelper::isServiceActive(Auth::user()->id,$myservice->service_id))
        <strong>
            {{substr(CommonHelper::getServiceAccount(Auth::user()->id,$myservice->service_id)->service_account_number,8,12)}}
        </strong>
        <span>ending</span>
        @endif
    </div>

    <div class="fs-selector-trigger">
        <i class="os-icon os-icon-arrow-down4"></i>
    </div>
</div>
@endif
@endforeach
@else
<div class="fancy-selector-current ">
    <div class="fs-img">
        <img alt="" src="{{asset('')}}/media/logos/wallet.jpg" />
    </div>
    <div class="fs-main-info">
        <div class="fs-name">
            Primary Account
        </div>
        <div class="fs-sub">
            <span>Balance:</span><strong>
            @if((Auth::user()->transaction_amount + Auth::user()->locked_amount - $unsettledThresholdAmount) > 0)
                                ₹{{number_format((Auth::user()->transaction_amount+Auth::user()->locked_amount) - $unsettledThresholdAmount,2)}}
                                @else
                                ₹{{number_format(0,2)}}
                                @endif
            </strong>
        </div>
    </div>
    <div class="fs-extra-info">

        <strong>
            {{substr(Auth::user()->account_number,8,12)}}
        </strong>
        <span>ending</span>
    </div>

    <div class="fs-selector-trigger">
        <i class="os-icon os-icon-arrow-down4"></i>
    </div>
</div>
@endif