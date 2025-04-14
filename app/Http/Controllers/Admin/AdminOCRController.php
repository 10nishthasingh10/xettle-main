<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * RechargeController
 */
class AdminOCRController extends Controller
{
	public function index()
	{
		$data['page_title'] = 'OCR Transaction List';
		$data['view'] = 'admin.ocr.ocrTransactionList';
		$data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
		return view($data['view'],$data);
	}
}