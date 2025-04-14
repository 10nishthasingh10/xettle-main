<div class="top-bar color-scheme-bright">
        <div class="logo-w menu-size">
        @if(Auth::user()->is_admin == 1)
            <a class="logo" href="{{url('/admin/dashboard')}}">
        @else
            <a class="logo" href="{{url('/user/dashboard')}}">
        @endif
          <img src="{{url('img/logo.png')}}"/>
          </a>
        </div>
        <div class="fancy-selector-w">
          <div class="fancy-selector-current">
            <div class="fs-img">
              <img alt="" src="{{asset('')}}/media/logos/payout.png">
            </div>
            <div class="fs-main-info">
              <div class="fs-name">
               Service Account Number
              </div>
              <div class="fs-sub">
                <span>Balance:</span><strong>  
                @if(CommonHelper::isServiceActive(Auth::user()->id,PAYOUT_SERVICE_ID))
                {{CommonHelper::getServiceAccount(Auth::user()->id,PAYOUT_SERVICE_ID)->transaction_amount}}
                @else
                0
              @endif
              </strong>
              </div>
            </div>
            <div class="fs-extra-info">
                @if(CommonHelper::isServiceActive(Auth::user()->id,PAYOUT_SERVICE_ID))
                <strong>
                {{substr(CommonHelper::getServiceAccount(Auth::user()->id,PAYOUT_SERVICE_ID)->service_account_number,8,12)}}
                </strong><span>ending</span>
                @endif

              </div>

            </div>
          </div>
        </div>
        <!--------------------
        END - Top Menu Controls
        -------------------->
        @include('include.common.sidebar')
      </div>