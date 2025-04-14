<?php
namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Models\Recharge;
use Illuminate\Http\Request;
use App\Models\Agent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RechargeController extends Controller
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
            $data['page_title'] =  "Recharge Dashboard";
            $data['site_title'] =  "Recharge Dashboard";
            $data['view']       = USER.".recharge.dashboard";
            $data['view']       = USER.".recharge.dashboard";
            
            $data['lockedAmount'] = isset(\DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum) ? \DB::table('orders')->select(DB::raw("SUM(amount + fee + tax) as paidsum"))->where(['user_id' => Auth::user()->id, 'status' => 'processing'])->first()->paidsum : 0;
            
            $data['commission']  = Recharge::select(DB::raw('sum(commission) as totalAmount,count(id) as totalCount'))
                ->where(['status' =>  'processed'])
                ->whereDate('created_at', date('Y-m-d'))
                ->where('user_id', Auth::user()->id)
                ->first();
                
            $data['txnData']  = Recharge::select(DB::raw('sum(amount) as totalAmount,count(id) as totalCount'))
                ->where(['status' =>  'processed'])
                ->whereDate('created_at', date('Y-m-d'))
                ->where('user_id', Auth::user()->id)
                ->first();           
            
            $data['txnDatas']  =  DB::table('recharges')
                ->where(['user_id' => Auth::user()->id])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();
            return view($data['view'])->with($data);
        }
          /**
     * Display a listing of the resource.
     */
    public function aepsmerchants()
    {
        $data['page_title'] =  "AEPS Merchants Listing";
        $data['site_title'] =  "AEPS Merchants";
        $data['view']       = USER.'/'."aeps.aepsmerchants";
        return view($data['view'])->with($data);
    }
          /**
     * Display a listing of the resource.
     */
    public function transaction()
    {
        $data['page_title'] =  "AEPS Transaction Listing";
        $data['site_title'] =  "AEPS Transaction";
        $data['view']       = USER.'/'."aeps.aepstransactions";
        return view($data['view'])->with($data);
    }

    public function settlement()
    {
        $data['page_title'] =  "AEPS Settlement Listing";
        $data['site_title'] =  "AEPS Settlement";
        $data['view']       = USER.'/'."aeps.aepssettlement";
        return view($data['view'])->with($data);
    }



    /**
     * dashboard card data
     */
    public function dashboardCardData(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'startDate' => 'required|date|before_or_equal:endDate',
                'endDate' => 'required|date'
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing($message);
        }

        $userId = Auth::user()->id;
        $startDate = trim($request->startDate);
        $endDate = trim($request->endDate);


        $amount = DB::connection('slave')
            ->table('aeps_transactions')
            ->select(DB::raw('sum(transaction_amount) as totAmt'), DB::raw('sum(fee+tax) as feeAndTax'))
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereIn('transaction_type', ['cw', 'ap'])
            ->first();

        $merchant = DB::connection('slave')
            ->table('agents')
            ->leftJoin('aeps_transactions', 'aeps_transactions.merchant_code', 'agents.merchant_code')
            ->where('agents.user_id', $userId)
            ->whereDate('aeps_transactions.created_at', '>=', $startDate)
            ->whereDate('aeps_transactions.created_at', '<=', $endDate)
            ->distinct('agents.merchant_code')
            ->count();

        $commission = DB::connection('slave')
            ->table('aeps_transactions')
            ->select(DB::raw('sum(commission) as totalAmount,count(id) as totalCount'))
            ->where(['status' =>  'success'])
            ->where('user_id',  $userId)
            ->where('commission', '!=', 0)
            ->whereIn('transaction_type', ['cw', 'ms'])
            ->whereDate('aeps_transactions.created_at', '>=', $startDate)
            ->whereDate('aeps_transactions.created_at', '<=', $endDate)
            ->first();


        $returnData['merchant'] = CommonHelper::numberFormat($merchant);
        $returnData['amount'] = CommonHelper::numberFormat(@$amount->totAmt - @$amount->feeAndTax);
        $returnData['commissionAmount'] = CommonHelper::numberFormat(@$commission->totalAmount);
        $returnData['commissionCount'] = CommonHelper::numberFormat(@$commission->totalCount);

        return ResponseHelper::success('success', $returnData);
    }

    public function rechargeindex()
	{
		$data['page_title'] = 'Recharge Data';
		$data['view'] = 'user.recharge.rechargeData';
		$data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
		return view($data['view'],$data);
	}

    public function getRechargeOperators() {
        $operators = [
            [
                'operator_name' => 'Airtel',
                'logo' => 'airtel_logo.png',
            ],
            [
                'operator_name' => 'Vodafone Idea',
                'logo' => 'vi_logo.png',
            ],
            [
                'operator_name' => 'Jio',
                'logo' => 'jio_logo.png',
            ],
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Recharge operators retrieved successfully',
            'data' => $operators    
        ]);
    }

    public function getRechargeCircles()
    {
        $circles = [
            [
                'name' => 'Delhi',
            ],
            [
                'name' => 'Mumbai',
            ],
            [
                'name' => 'Kolkata',
            ],
            [
                'name' => 'Chennai',
            ],
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Recharge circles retrieved successfully',
            'data' => $circles
        ]);
    }

    public function dthrecharge()
	{
		$data['page_title'] = 'DTH Recharge';
		$data['view'] = 'user.recharge.dthrecharge';
		$data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        $data['dth_recharges'] = DB::table('dth_recharges')->get();
        // return view(USER . '/dthrecharge')->with($data);
		return view($data['view'],$data);
	}

    public function licrecharge()
	{
		$data['page_title'] = 'LIC Recharge';
		$data['view'] = 'user.recharge.licrecharge';
		$data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        $data['lic_recharge'] = DB::table('lic_recharge')->get();
        return view($data['view'],$data);
	}

    public function electricityrecharge()
    {
        $data['page_title'] = 'Electricity Recharge';
        $data['view'] = 'user.recharge.electricity';
        $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        $data['electricity_recharge'] = DB::table('electricity_recharge')->get();
        return view($data['view'],$data);
    }

    public function postpaidrecharge()
    {
        $data['page_title'] = 'Postpaid Recharge';
        $data['view'] = 'user.recharge.postpaid';
        $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        $data['postpaid_recharges'] = DB::table('postpaid_recharges')->get();
        return view($data['view'],$data);
    }

    public function creditcardrecharge()
    {
        $data['page_title'] = 'Creditcard Recharge';
        $data['view'] = 'user.recharge.creditcard';
        $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        $data['creditcard_recharge'] = DB::table('creditcard_recharge')->get();
        return view($data['view'],$data);
    }

    public function datarecharge()
    {
        $data['page_title'] = 'Data Recharge';
        $data['view'] = 'user.recharge.datarecharge';
        $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        $data['recharge_datas'] = DB::table('recharge_datas')->get();
        return view($data['view'],$data);
    }
}
