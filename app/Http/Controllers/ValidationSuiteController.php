<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ValidationSuiteController extends Controller
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
        $data['page_title'] =  "Validation Suite Dashboard";
        $data['site_title'] =  "Validation Suite Dashboard";
        $data['view']       = USER . ".validation_suite.dashboard";

        $data['callbacks'] = DB::table('validation_suite')
            ->where('user_id', Auth::user()->id)
            ->limit(10)
            ->orderBy('id', 'desc')
            ->get();

        return view($data['view'])->with($data);
    }


    /**
     * Display a listing of the resource.
     */
    public function upicallback()
    {
        $data['page_title'] =  "Validation Suite Transactions";
        $data['site_title'] =  "Validation Suite Transactions";
        $data['view']       = USER . ".validation_suite.validation_suite_transactions";
        return view($data['view'])->with($data);
    }
}
