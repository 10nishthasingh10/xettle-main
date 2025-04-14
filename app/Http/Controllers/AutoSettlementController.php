<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Auth;

class AutoSettlementController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (CommonHelper::isAutoSettlementActive(Auth::user()->id, 'auto_settlement')) {
            $data['page_title'] = "Auto Settlement Listing";
            $data['site_title'] = "Auto Settlement Payments";
            $data['view']       = USER . ".autosettlement.list";
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('user/dashboard');
            return view('errors.401')->with($data);
        }


    }

}
