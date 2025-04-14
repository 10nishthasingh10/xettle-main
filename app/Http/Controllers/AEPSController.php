<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Models\AepsTransaction;
use Illuminate\Http\Request;
use App\Models\Agent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AEPSController extends Controller
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
            $data['page_title'] =  "AEPS Dashboard";
            $data['site_title'] =  "AEPS Dashboard";
            $data['view']       = USER.".aeps.dashboard";
            /*
            $data['serviceData'] = UserService::leftJoin('global_services','global_services.service_id','user_services.service_id')
                ->select('global_services.service_id','global_services.service_name','user_services.*','global_services.url')
                ->where('user_services.user_id', Auth::user()->id)
                ->where('user_services.is_active','1')->get();
            $data['payoutServiceId'] = UserService::leftJoin('global_services','global_services.service_id','user_services.service_id')
                ->select('global_services.service_id','global_services.service_name','user_services.*','global_services.url')
                ->where('user_services.user_id',Auth::user()->id)
                ->where('global_services.service_slug','payout')
                ->where('user_services.is_active','1')->first();
            $data['data'] = UserService::where('user_id',Auth::user()->id)->where('service_id',PAYOUT_SERVICE_ID)->first();
            //*/
            $data['agents'] = Agent::where('user_id', Auth::user()->id)->limit(5)->orderBy('id', 'desc')->get();
            $merchant = Agent::join('aeps_transactions' , 'aeps_transactions.merchant_code', 'agents.merchant_code')
                ->where('agents.user_id', Auth::user()->id);
            $data['activeMerchants']  = $merchant->distinct('agents.merchant_code')
                ->count();

            $data['sattleAmount']  =  DB::table('aeps_transactions')
                ->where(['transaction_type' => 'cw', 'is_trn_credited' => '1', 'user_id' => Auth::user()->id])
                ->sum('transaction_amount');
            $data['commission']  = AepsTransaction::select(DB::raw('sum(commission) as totalAmount,count(id) as totalCount'))
                ->where(['status' =>  'success'])
                ->whereDate('created_at', date('Y-m-d'))
                ->where('user_id', Auth::user()->id)
                ->whereIn('transaction_type' ,['cw', 'ms'])
                ->first();
            // $data['transactionCount']  =  DB::table('aeps_transactions')
            //     ->whereDate('created_at', date('Y-m-d'))
            //     ->where(['transaction_type' => 'cw', 'user_id' => Auth::user()->id, 'status' => 'success'])
            //     ->count();
            $data['unsettleAmount']  =  DB::table('aeps_transactions')
                ->where(['transaction_type' => 'cw', 'is_trn_credited' => '0', 'status' => 'success', 'user_id' => Auth::user()->id])
                ->sum('transaction_amount');
            $data['txnDatas']  =  DB::table('transactions')
                ->where(['tr_type' => 'cr', 'tr_identifiers' => 'aeps_inward_credit', 'user_id' => Auth::user()->id])
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
}
