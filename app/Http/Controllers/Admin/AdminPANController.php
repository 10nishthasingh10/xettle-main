<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class AdminPANController extends Controller
{
    //
    public function index(){
        $data['page_title'] = 'PAN Card Transaction';
		$data['view'] = 'admin.pan.panCardTxn';
		$data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
		return view($data['view'],$data);
    }

    public function panAgent(){
        $data['page_title'] = 'PAN Card Agents';
		$data['view'] = 'admin.pan.panCardAgent';
		$data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
		return view($data['view'],$data);
    }
}
