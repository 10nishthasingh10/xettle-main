<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Models\User;
use App\Models\OauthClient;
use DataTables;
use Illuminate\Http\Request;
use App\Models\BusinessCategory;
use App\Models\State;
use App\Models\BusinessInfo;
use App\Models\IpWhitelist;
use App\Models\Transaction;
use App\Models\UserService;
use App\Models\Webhook;
use App\Models\AccountManager;
use App\Models\UserConfig;
use Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  UsersDataTable  $dataTable
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('pages.users.list.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function createuser(Request $request)
    {
        $data = $request->all();
        Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required_with:password|same:password|min:8']
        ]);

        $user = User::create([
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        return $user;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  User  $user
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $data['page_title'] = "edit";
        return view(USER . '.profile.editprofile', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  User  $user
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    public function profileUpdate($slug)
    {
        $data['site_title'] =  "Profile";
        if (!empty($slug)) {
            if ($slug == 'apikey') {
                $data['page_title'] = "Developer Controls";
            } else if ($slug == 'edit') {
                $data['page_title'] = "Profile Edit";
            } else {
                $data['page_title'] = $slug;
            }
            $data['page_url'] = $slug;
            if ($slug == 'business_profile_edit' || $slug == 'business_profile') {
                if ($slug == 'business_profile_edit') {
                    $data['page_title'] = "Business Profile Edit";
                } else {
                    $data['page_title'] = "Business Profile";
                }
                $data['business_category'] = BusinessCategory::where('is_active', '1')->where('is_parent', '1')->get();
                $data['state_list'] =  State::where('is_active', '1')->get();
                $data['business_info'] =  BusinessInfo::where('user_id', Auth::user()->id)->first();
            }
        } else {
            $data['page_title'] = "Profile";
            $data['page_url'] = $slug;
        }
        $data['view']       = USER . '/profile/' . $slug;

        return view($data['view'], compact('data'))->with($data);
    }

    public function profile()
    {

        $data['site_title'] =  "Profile";
        $data['page_title'] =  "Profile";
        $data['page_url'] = "profile";
        return view(USER . '/' . 'profile.profile', compact('data'))->with($data);
    }

    public function myProfile()
    {
        $data['site_title'] =  "Profile";
        $data['page_title'] =  "Profile";
        $data['page_url'] = "profile";
        $data['business_category'] = BusinessCategory::where('is_active', '1')->where('is_parent', '1')->get();
        $data['state_list'] =  State::where('is_active', '1')->get();
        $data['business_info'] = $businessInfo = BusinessInfo::where('user_id', Auth::user()->id)->first();
        $data['account_manager'] =  AccountManager::where('is_active', '1')->get();
        $data['user_config'] =  UserConfig::select('is_sdk_enable', 'app_id', 'app_cred_created_at', 'is_matm_enable', 'matm_app_id', 'matm_app_cred_created_at')->where(['user_id' => Auth::user()->id])->first();
        $data['account_manager_data'] = "";
        if (isset($businessInfo->acc_manager_id)) {
            $data['account_manager_data'] =  AccountManager::where('is_active', '1')->where('id', $businessInfo->acc_manager_id)->first();
        }
        $data['account_coordinator_data'] = "";
        if (isset($businessInfo->acc_coordinator_id)) {
            $data['account_coordinator_data'] =  AccountManager::where('is_active', '1')->where('id', $businessInfo->acc_coordinator_id)->first();
        }
        $data['services'] =  UserService::select('user_services.id', 'global_services.service_id', 'global_services.service_name')
            ->leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
            ->where('user_id', Auth::user()->id)->where('user_services.is_active', '1')->get();

        // $data['ebVan'] = DB::table('user_van_accounts')
        //     ->where('root_type', 'eb_van')
        //     ->where('user_id', Auth::user()->id)
        //     ->first();

        // $data['razVan'] = DB::table('user_van_accounts')
        //     ->where('root_type', 'raz_van')
        //     ->where('user_id', Auth::user()->id)
        //     ->first();

        $data['obVan'] = DB::table('user_van_accounts')
            ->where('root_type', OPEN_BANK_VAN)
            ->where('user_id', Auth::user()->id)
            ->first();


        //check van info
        // if (!empty($businessInfo->van)) {
        //     $data['van_info']['van'] = ($businessInfo->van);
        //     $data['van_info']['ifsc'] = ($businessInfo->van_ifsc);
        //     $data['van_info']['van_2'] = ($businessInfo->van_2);
        //     $data['van_info']['ifsc_2'] = ($businessInfo->van_2_ifsc);
        //     $data['van_info']['id'] = ($businessInfo->van_acc_id);
        //     $data['van_info']['status'] = ($businessInfo->van_status);
        // }

        $data['isActiveTab'] = '';

        $data['userBankInfos'] = DB::table("user_bank_infos")
            ->select('*')
            ->where('user_id', Auth::user()->id)
            ->orderBy('id', 'ASC')
            ->get();

        $data['webhook'] =  Webhook::where('user_id', Auth::user()->id)->first();
        return view(USER . '/' . 'myprofile', compact('data'))->with($data);
    }


    public function getBusinessSubCategoryByUserId($id, $userId)
    {
        $BusinessInfo = BusinessInfo::where('user_id', $userId)->first();
        $data['status'] = false;
        $select = "";
        $business_category = BusinessCategory::where('parent_id', $id)->where('is_active', '1')->get();
        $html = "<option value=''>Select a Business Sub Category...</option>";
        foreach ($business_category as $business_categorys) {
            if (isset($BusinessInfo)) {
                if ($BusinessInfo->business_subcategory_id == $business_categorys->id) {
                    $select = "Selected";
                }
                $html .= "<option value='$business_categorys->id'
                $select
                >" . $business_categorys->name . "</option>";
            } else {
                $html .= "<option value='$business_categorys->id'>" . $business_categorys->name . "</option>";
            }

            $data['status'] = true;
        }
        $data['option'] = $html;
        return $data;
    }


    public function getBusinessSubCategory($id)
    {
        if (Auth::check()) {
            $BusinessInfo = BusinessInfo::where('user_id', Auth::user()->id)->first();
        } else {
            $BusinessInfo = '';
        }

        $data['status'] = false;
        $select = "";
        $business_category = BusinessCategory::where('parent_id', $id)->where('is_active', '1')->get();
        $html = "<option value=''>Select a Business Sub Category...</option>";
        foreach ($business_category as $business_categorys) {
            if (isset($BusinessInfo) && !empty($BusinessInfo)) {
                if ($BusinessInfo->business_subcategory_id == $business_categorys->id) {
                    $select = "Selected";
                }
                $html .= "<option value='$business_categorys->id'
                $select
                >" . $business_categorys->name . "</option>";
            } else {
                $html .= "<option value='$business_categorys->id'>" . $business_categorys->name . "</option>";
            }

            $data['status'] = true;
        }
        $data['option'] = $html;
        return $data;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // do not destroy current user
        abort_if($user->id === auth()->user()->id, 404, 'Unable to delete self account');

        return response($user->delete());
    }

    public function apikeys(Request $request)
    {
        if ($request->ajax()) {
            $data = OauthClient::where('user_id', Auth::user()->id)->select('*')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('Action', function ($row) {
                    $id = $row['id'];
                    //$btn = '<a href="javascript:void(0)" onclick="deActivateApiKey('.$id.');" class="edit btn btn-danger btn-sm">DeActive</a>';
                    if ($row['is_active'] == 1) {
                        $btn = '  <label class="switch" onclick="deActivateApiKey(' . $id . ');">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>';
                    } else {
                        $btn = '  <label class="switch" onclick="deActivateApiKey(' . $id . ');">
                            <input type="checkbox" >
                            <span class="slider round"></span>
                        </label>';
                    }

                    return $btn;
                })
                ->editColumn('created_at', function ($row) {
                    return $row['created_at'];
                })
                ->rawColumns(['Action'])
                ->make(true);
        }
    }

    public function getWebhook(Request $request)
    {
        if ($request->ajax()) {
            $data = Webhook::where('user_id', Auth::user()->id)->select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return $row['created_at'];
                })
                ->rawColumns(['Action'])
                ->make(true);
        }
    }

    /**
     * get Transaction Record
     */

    public function iplist(Request $request)
    {
        if ($request->ajax()) {
            $data = IpWhitelist::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return $row['created_at'];
                })

                ->addColumn('Action', function ($row) {
                    $id = $row['id'];
                    $btn = '<a href="javascript:void(0)" onclick="deleteIp(' . $id . ');"
                        class="edit btn btn-danger btn-sm"><i class="os-icon os-icon-trash-2"></i></a>';
                    return $btn;
                })
                ->rawColumns(['Action'])
                ->make(true);
        }
    }

    public function transactionList(Request $request)
    {

        $data['page_title'] =  "Transactions Listing";
        $data['site_title'] =  "Transactions";
        $data['id'] =  0;
        return view(USER . '/transaction')->with($data);
    }

    public function mytransactions(Request $request)
    {
        // dd("djfg");
        $data['page_title'] =  "All Transactions Listing";
        $data['site_title'] =  "All Transactions";
        $data['id'] =  0;
        $data['transactions'] =  DB::table('transactions')
            ->select('tr_identifiers')
            ->where('user_id', Auth::user()->id)
            ->groupBy('tr_identifiers')->get();

        $data['serviceLists'] = DB::table('global_services')
            ->select('service_name AS title', 'service_slug AS id', 'user_services.service_id as service_id')
            ->leftJoin('user_services', 'user_services.service_id', 'global_services.service_id')
            ->orderBy('service_name', 'ASC')
            ->where('user_services.user_id', Auth::user()->id)
            ->get();

        $data['serviceList'] = json_encode($data['serviceLists']);
        $data['serviceListObject'] = $data['serviceLists'];
        return view(USER . '/alltransaction')->with($data);
    }


    public function vanCallbacks(Request $request)
    {
        $data['page_title'] =  "VAN Transactions Listing";
        $data['site_title'] =  "VAN Transactions";
        $data['id'] =  0;

        return view(USER . '.van_callbacks')->with($data);
    }


    public function loadMoney()
    {
        $data['page_title'] =  "Load Money";
        $data['site_title'] =  "Load Money";
        $data['id'] =  0;

        return view(USER . '.load_money')->with($data);
    }


    public function loadMoneyRequest(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'amount' => "required|numeric|min:1",
                'utr' => "required|alpha_num|unique:load_money_request,utr",
            ],
            [
                'utr.unique' => "The UTR has already been requested."
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing('Some params are missing.', $message);
        }

        $userId = Auth::user()->id;

        //check is load money request is active
        if (!CommonHelper::isLoadMoneyRequestActive($userId)) {
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Load Money is not active.");
            $this->title = "Load Money";
            $this->redirect = false;
            return $this->populateresponse();
        }

        $utr = $request->utr;

        //check if UTR is already in fund_receive_callbacks
        $fundCallbacks = DB::table('fund_receive_callbacks')
            ->select('user_id', 'utr', 'amount', 'trn_credited_at')
            ->where('utr', $utr)
            ->first();

        if (!empty($fundCallbacks)) {
            $message = ['utr' => ["UTR {$utr} is already credited at {$fundCallbacks->trn_credited_at}."]];
            return ResponseHelper::missing('Some params are missing.', $message);
        }


        $amount = $request->amount;

        $request_id = CommonHelper::getRandomString('FND', false);
        $timestamp = date('Y-m-d H:i:s');

        $insert = DB::table('load_money_request')->insert([
            'request_id' => $request_id,
            'user_id' => $userId,
            'amount' => $amount,
            'utr' => $utr,
            'status' => 'pending',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($insert) {
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message = "Request Raised Successfully";
            $this->title = "Load Money";
            $this->redirect = true;
            return $this->populateresponse();
        }
    }

    /**
     * getIndentifiers
     *
     * @param  mixed $req
     * @return void
     */
    public function getIndentifiers(Request $req)
    {
        $trIdentifiers = DB::table('transactions')
                ->select('tr_identifiers')
                ->whereIn('service_id', $req->service)
                ->groupBy('tr_identifiers')
                ->get();
        $option = "<option value=''>-- Select Identifires --</option>";
        foreach($trIdentifiers as $trIdentifier)
        {
            $option .= "<option value='{$trIdentifier->tr_identifiers}'>$trIdentifier->tr_identifiers</option>";
        }
        return $option;
    }

    // public function rechargeList(Request $request)
    // {
    //     // dd("hdj");
    //     $data['page_title'] =  "Recharge Back Listing";
    //     $data['site_title'] =  "RECHARGE Back";
    //     // $data['id'] =  0;
    //     // dd($data['page_title']);
    //     $data['user'] = DB::table('recharges')->select('user_id', 'service_id', 'stan_no', 'phone', 'order_ref_id')->get();
    //     // dd($data['user']);
    //     return view(USER . '/recharge_back')->with($data);
    // }
    
}
