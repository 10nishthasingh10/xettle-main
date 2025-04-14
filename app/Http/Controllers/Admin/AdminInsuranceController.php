<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminInsuranceController extends Controller
{
    //

    public function index(){
        $data['page_title'] = 'Insurance Agents';
		$data['view'] = 'admin.insurance.insuranceAgent';
		$data['userData'] = \DB::table('users')->select(\DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
		return view($data['view'],$data);
    }
}
