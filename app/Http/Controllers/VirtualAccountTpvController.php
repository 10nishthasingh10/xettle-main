<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\NumberFormat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VirtualAccountTpvController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard
     */
    public function index()
    {
        $data['page_title'] =  "Virtual Account Dashboard";
        $data['site_title'] =  "Virtual Account Dashboard";
        $data['view']       = USER . ".virtual_account.dashboard";

        $callbacks = DB::table('upi_callbacks')
            ->select('upi_callbacks.payee_vpa', 'upi_callbacks.amount', 'upi_callbacks.customer_ref_id', 'upi_callbacks.original_order_id', 'upi_callbacks.created_at')
            ->where('upi_callbacks.user_id', Auth::user()->id)
            ->where('root_type', 'ibl_tpv');

        $data['active'] = DB::table('upi_merchants')
            ->select('id')
            ->where('user_id', Auth::user()->id)
            ->where('root_type', 'ibl_tpv')
            ->count();


        $data['unsettledUpiStack'] = CommonHelper::getUnsettledBalance(Auth::user()->id, 'virtual_account');


        $data['callbacks'] = $callbacks->limit(10)
            ->orderBy('upi_callbacks.id', 'desc')
            ->get();

        return view($data['view'])->with($data);
    }



    /**
     * Display a listing of the resource.
     */
    public function merchants()
    {
        $data['page_title'] =  "Clients Listing";
        $data['site_title'] =  "Clients";
        $data['view']       = USER . ".virtual_account.merchant";

        return view($data['view'])->with($data);
    }


    /**
     * Display a listing of the resource.
     */
    public function upicallback()
    {
        $data['page_title'] =  "Payments Listing";
        $data['site_title'] =  "Payments Listing";
        $data['view']       = USER . ".virtual_account.upicallback";
        return view($data['view'])->with($data);
    }
}
