<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\NumberFormat;
use Illuminate\Http\Request;
use Validations\UPIValidation as Validations;
use App\Models\UserService;
use App\Models\Service;
use App\Models\Cities;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpiController extends Controller
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
        $data['page_title'] =  "Upi Dashboard";
        $data['site_title'] =  "Upi Dashboard";
        $data['view']       = USER . ".upi.dashboard";

        $callbacks = DB::table('upi_collects')
            ->select('upi_collects.payee_vpa', 'upi_collects.amount', 'upi_collects.customer_ref_id', 'upi_collects.original_order_id', 'upi_collects.created_at')
            ->where('upi_collects.user_id', Auth::user()->id)
            ->where('status','success');
// dd($callbacks);
        $data['unsettledUpiStack'] = CommonHelper::getUnsettledBalance(Auth::user()->id, 'upi_stack');
        
        $days = empty($days) ? 90 : intval($days->attribute_1);
        $timestamp = date('Y-m-d', strtotime("-{$days} days", time()));

        $data['successAmount'] = DB::table('upi_collects')
                                    ->selectRaw("SUM(amount) as amt")
                                    ->where('status','success')
                                    ->where('user_id', Auth::user()->id)
                                    ->whereDate('created_at', '>=', $timestamp)
                                    ->first();
        //dd($data['successAmount']);
        $data['active'] = DB::table('upi_merchants')
            ->select('id')
            ->where('user_id', Auth::user()->id)
            ->count();

        $data['callbacks'] = $callbacks->limit(10)
            ->orderBy('upi_collects.id', 'desc')
            ->get();
        //dd($data['callbacks']);
        return view($data['view'])->with($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function merchants()
    {
        $data['page_title'] =  "Merchants Listing";
        $data['site_title'] =  "Merchants";
        $data['view']       = USER.'/'."upi.merchant";
        $data['cities']       = Cities::where('is_active','1')->get();
        $data['states']       = State::where('is_active','1')->get();
        return view($data['view'])->with($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function upicallback()
    {
        $data['page_title'] =  "UPI Callback Listing";
        $data['site_title'] =  "UPI Callback";
        $data['view']       = USER.'/'."upi.upicallback";
        return view($data['view'])->with($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function upicollect()
    {
        $data['page_title'] =  "UPI Collects Listing";
        $data['site_title'] =  "UPI Collects";
        $data['view']       = USER.'/'."upi.upi_collect";
        return view($data['view'])->with($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        $data['page_title'] =  "Upi Dashboard Listing";
        $data['site_title'] =  "Upi Dashboard";
        $data['view']       = USER.'/'."upi.dashboard";
        $data['merchants']       = \App\Models\UPIMerchant::where('user_id', Auth::user()->id)->limit(10)->orderBy('id', 'desc')->get();
        $data['callbacks']       = \App\Models\UPICallback::where('user_id', Auth::user()->id)->limit(10)->orderBy('id', 'desc')->get();
        return view($data['view'])->with($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function addMerchant(Request $request)
    {
        $validation = new Validations($request);
        $validator = $validation->webMerchant();
        if($validator->fails()){
            $this->data = $validator->errors();
            return response()->json(
                $this->populate([
                    'message'   => "Some field is required",
                    'status'    => false,
                    'data'      => $this->data
                ])
            );
        }else{
            $newUpi = new \App\Http\Controllers\Clients\Api\v1\UPIController;
            $userId= Auth::user()->id;
            $service = Service::select('id','service_id')->where('service_slug', 'upi_collect')->first();
            $userService = Userservice::select('id','service_id')->where(['service_id'=> $service->service_id, 'user_id' => $userId])->first();
            $request['auth_data'] = ['user_id' => Auth::user()->id, 'service_id' => $userService->service_id];
            $upiMerchant = $newUpi->merchant($request);
            $upiMerchantResp = json_decode(json_encode($upiMerchant), true);
            if(isset($upiMerchantResp['original'])) {
                if($upiMerchantResp['original']['code'] === "0x0200") {
                    $status = true;
                    $this->message = "Merchant added successfully.";
                } else {
                    $status = false;
                    $this->message = $upiMerchantResp['original']['message'];
                }
                return response()->json(
                    $this->populate([
                        'message'   => $this->message,
                        'status'    => $status,
                        'data'      => []
                    ])
                );
            } else {
                return response()->json(
                    $this->populate([
                        'message'   => "Some field is required",
                        'status'    => false,
                        'data'      => $this->message
                    ])
                );
            }
        }
    }



    /**
     * Dashboard Chart
     */
    public static function dashboardChart($type)
    {

        $userId = Auth::user()->id;

        $resp = [];
        if (!empty($userId)) {

            $query = CommonHelper::callBackUPITotalAmount($userId, $type);

            $resp['count'] = $query['count'];
            $resp['amount'] = NumberFormat::init()->change($query['amount'], 2);
        }
        return $resp;
    }
}
