<?php

namespace App\Http\Controllers;

use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\CommonHelper;
use App\Helpers\NumberFormat;
use App\Models\AutoCollectCallback;
use App\Models\State;
use App\Models\UserService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AutoCollectController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }



    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data['page_title'] =  "Smart Collect Dashboard";
        $data['site_title'] =  "Smart Collect Dashboard";
        $data['view']       = USER . ".auto_collect.dashboard";

        $data['active'] = DB::table('cf_merchants')->where('user_id', Auth::user()->id)->count();

        $callbacks = DB::table('cf_merchants_fund_callbacks')
            ->where('user_id', Auth::user()->id);

        $data['unsettledSmartCollect'] = CommonHelper::getUnsettledBalance(Auth::user()->id, 'smart_collect');

        $data['callbacks'] =  $callbacks->limit(10)->orderBy('id', 'desc')->get();

        return view($data['view'])->with($data);
    }



    /**
     * Dashboard Chart
     */
    public static function dashboardChart($type)
    {

        $userId = Auth::user()->id;

        $resp = [];
        if (!empty($userId)) {
            
            $query = CommonHelper::callBackSmartCollectTotalAmount($userId, $type);

            $resp['count'] = $query['count'];
            $resp['amount'] = NumberFormat::init()->change($query['amount'], 2);
        }
        return $resp;
    }


    /**
     * Display a listing of the resource.
     */
    public function merchants()
    {
        $data['page_title'] = "Merchants Listing";
        $data['site_title'] = "Smart Collect Merchants";
        $data['view']       = USER . ".auto_collect.merchant";
        $data['states']     = State::where('is_active', '1')->get();

        return view($data['view'])->with($data);
    }


    /**
     * Display a listing of the resource.
     */
    public function callbacks()
    {
        $data['page_title'] = "Payments Listing";
        $data['site_title'] = "Smart Collect Payments";
        $data['view']       = USER . ".auto_collect.callback";
        return view($data['view'])->with($data);
    }


    /**
     * Fn for update VAN accounts user email and phone number
     */
    public function editVanEmailPhone()
    {
        $businessInfos = DB::table('business_infos')->select('van_acc_id')
            ->whereNotNull('van_acc_id')
            ->get();

        $count = 0;
        $response = [];

        foreach ($businessInfos as $row) {
            //making params
            $params = [
                "vAccountId" => $row->van_acc_id,
                "phone" => "9999999999",
                "email" => "payments.cf@example.com",
            ];

            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();

            $result = $vanHelper->vanManager($params, '/cac/v1/editVA', Auth::user()->id, 'POST', 'editVA');

            $response[$count]['id'] = $row->van_acc_id;
            $response[$count]['res'] = json_decode($result['response']);
            $count++;
        }

        return print_r($response);
    }


    /**
     * Fn for update VAN accounts user email and phone number
     */
    public function inactiveVanCfMerchants($vanStatus, $offset = 0, $limit = 10)
    {
        if ($vanStatus === '1') {
            $checkStatus = '0';
            $status = 'ACTIVE';
        } else {
            $checkStatus = '1';
            $status = 'INACTIVE';
        }

        $cf_merchants = DB::table('cf_merchants')->select('id', 'v_account_id')
            ->where('service_type', 'van')
            ->where('van_status', $checkStatus)
            ->whereNotNull('van_1')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        $response = [];


        //creating object
        $vanHelper = new CashfreeAutoCollectHelper();

        foreach ($cf_merchants as $row) {
            //making params
            $params = [
                "vAccountId" => $row->v_account_id,
                "status" => $status,
            ];

            $result = $vanHelper->vanManager($params, '/cac/v1/changeVAStatus', Auth::user()->id, 'POST', 'inactiveVAN');

            if ($result['code'] == 200) {
                $cashfreeResponse = json_decode($result['response']);

                if ($cashfreeResponse->subCode === "200") {
                    //update table
                    DB::table('cf_merchants')->where('id', $row->id)->update([
                        'van_status' => $vanStatus
                    ]);
                }
            }

            $response[$count]['id'] = $row->v_account_id;
            $response[$count]['res'] = json_decode($result['response']);
            $count++;
        }

        echo "<pre>";
        print_r($response);
        echo "</pre>";
        exit();
    }


    /**
     * Fn for update VAN accounts user email and phone number
     */
    public function inactivePartnerVanMerchants($vanStatus, $offset = 0, $limit = 10)
    {
        if ($vanStatus === '1') {
            $checkStatus = '0';
            $status = 'ACTIVE';
        } else {
            $checkStatus = '1';
            $status = 'INACTIVE';
        }

        $cf_merchants = DB::table('business_infos')->select('id', 'van_acc_id')
            ->whereNotNull('van_acc_id')
            ->where('van_status', $checkStatus)
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        $response = [];


        //creating object
        $vanHelper = new CashfreeAutoCollectHelper();

        foreach ($cf_merchants as $row) {
            //making params
            $params = [
                "vAccountId" => $row->van_acc_id,
                "status" => $status,
            ];

            $result = $vanHelper->vanManager($params, '/cac/v1/changeVAStatus', Auth::user()->id, 'POST', 'inactiveVAN');

            if ($result['code'] == 200) {
                $cashfreeResponse = json_decode($result['response']);

                if ($cashfreeResponse->subCode === "200") {
                    //update table
                    DB::table('business_infos')->where('id', $row->id)->update([
                        'van_status' => $vanStatus
                    ]);
                }
            }

            $response[$count]['id'] = $row->van_acc_id;
            $response[$count]['res'] = json_decode($result['response']);
            $count++;
        }

        echo "<pre>";
        print_r($response);
        echo "</pre>";
        exit();
    }


    /**
     * Fn for update CF Smart Collect accounts status
     */
    public function updateAccountStatusMerchants($userId, $vanStatus, $offset = 0, $limit = 10)
    {

        if (empty($userId)) {
            return response()->json('User ID is required.');
        }

        $cf_merchants = DB::table('cf_merchants')
            ->select('id', 'v_account_id')
            ->where('service_type', 'upi')
            ->where('user_id', $userId)
            ->whereNotNull('vpa_1');


        if ($vanStatus === '1') {
            $cf_merchants->where('van_status', '0');
            $status = 'ACTIVE';
        } else {
            $cf_merchants->where(function ($sql) {
                $sql->where('van_status', '1')
                    ->orWhereNull('van_status');
            });
            $status = 'INACTIVE';
        }

        $cf_merchants = $cf_merchants->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get();

        $count = 0;
        $response = [];


        //creating object
        $vanHelper = new CashfreeAutoCollectHelper();

        foreach ($cf_merchants as $row) {
            //making params
            $params = [
                "vAccountId" => $row->v_account_id,
                "status" => $status,
            ];

            $result = $vanHelper->vanManager($params, '/cac/v1/changeVAStatus', 1, 'POST', 'inactiveUPI');

            if ($result['code'] == 200) {
                $cashfreeResponse = json_decode($result['response']);

                if ($cashfreeResponse->subCode === "200") {
                    //update table
                    DB::table('cf_merchants')->where('id', $row->id)->update([
                        'van_status' => $vanStatus
                    ]);
                }
            }

            $response[$count]['id'] = $row->v_account_id;
            $response[$count]['res'] = json_decode($result['response']);
            $count++;
        }

        echo "<pre>";
        print_r($response);
        echo "</pre>";
        exit();
    }
}
