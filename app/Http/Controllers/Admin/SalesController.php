<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\Agent;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

/**
 * SalesController
 */
class SalesController extends Controller
{
    /**
     * partnerSignUp
     *
     * @param  mixed $request
     * @return void
     */
    public function partnerSignUp(Request $request)
    {
        $data['page_title'] =  "Api Parter SignUp";
        $data['site_title'] =  "VAN Transactions";
        $data['id'] =  0;

        return view(USER . '.van_callbacks')->with($data);
    }
}