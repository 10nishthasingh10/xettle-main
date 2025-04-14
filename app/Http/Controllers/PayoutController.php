<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use App\Models\Webhook;
use App\Models\UserService;
use Auth;
use CommonHelper;

class PayoutController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $data['page_title'] =  "Payout Dashboard";
        $data['site_title'] =  "Payout Dashboard";
        $data['view']       = USER . ".payout.index";
        $chart = CommonHelper::chart(Auth::user()->id, 'orders', '30Days');
        $data['lastMonth'] = $chart['days'];
        $data['lastMonthAmount'] = $chart['amount'];

        $data['serviceData'] = UserService::leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
            ->select('global_services.service_id', 'global_services.service_name', 'user_services.*', 'global_services.url')
            ->where('user_services.user_id', Auth::user()->id)
            ->where('user_services.is_active', '1')->get();
        $data['payoutServiceId'] = UserService::leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
            ->select('global_services.service_id', 'global_services.service_name', 'user_services.*', 'global_services.url')
            ->where('user_services.user_id', Auth::user()->id)
            ->where('global_services.service_slug', 'payout')
            ->where('user_services.is_active', '1')->first();
        $data['data'] = UserService::where('user_id', Auth::user()->id)->where('service_id', PAYOUT_SERVICE_ID)->first();
        return view($data['view'])->with($data);
    }

    public function developerControls()
    {
        $data['page_title'] =  "Payout Api Key";
        $data['site_title'] =  "Payout Api Key";
        $data['view']       = USER . ".payout.security.apikey";
        $data['webhook'] = Webhook::where(['user_id' => Auth::user()->id, 'service_id' => 1])->first();
        return view($data['view'])->with($data);
    }
}
