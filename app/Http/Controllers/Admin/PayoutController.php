<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\Agent;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * PayoutController
 */
class PayoutController extends Controller
{
    /**
     * order
     *
     * @param  mixed $request
     * @return void
     */
    public function order(Request $request, $id = 1)
    {
        $data['page_title'] =  "Test Orders";
        $data['site_title'] =  "Order List";
        $data['id'] =  $id;
        $data['integrations'] = Integration::where('is_active','1')->get();
        $data['products'] = Product::where('is_active','1')->get();
        $data['userData']   = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        $id = 0;
        return view(ADMIN . '.payout.orders',compact('id'))->with($data);
    }
}