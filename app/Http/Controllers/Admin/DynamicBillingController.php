<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
// use App\Models\CustomScheme;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DynamicBillingController extends Controller
{


    /**
     * Manage Scheme and Rules
     */
    public function manageSchemesAndRules()
    {
        try {
            if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {

                $data['page_title'] = "Add New Scheme And Rules";
                $data['site_title'] = "Custom Scheme & Rules";
                $data['view']       = ADMIN . '.dynamic_billing';
                $data['role'] = Auth::user()->roles->first->slug->slug;

                $data['services'] = DB::table('global_services')
                    ->select('service_id', 'service_name')
                    ->orderBy('service_name', 'ASC')
                    ->get();

                $data['products'] = DB::table('global_products')
                    ->select('service_id', 'product_id', 'name')
                    ->orderBy('name', 'ASC')
                    ->get();

                $data['schemes'] = DB::table('schemes')
                    ->where('is_active', '1')
                    ->orderBy('scheme_name', 'ASC')
                    ->get();

                $data['users'] = DB::table('users')
                    ->select('id', 'name', 'email')
                    ->where('is_active', '1')
                    ->where('is_admin', '0')
                    ->orderBy('name', 'ASC')
                    ->get();


                return view($data['view'])->with($data);
            } else {
                $data['url'] = url('admin/dashboard');
                return view('errors.401')->with($data);
            }
        } catch (Exception $e) {
            return view('errors.500', ['message' => $e->getMessage()]);
        }
    }


    /**
     * Add New Scheme and Rules
     */
    public function addNewSchemesAndRules(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'scheme_name' => "required|unique:schemes,scheme_name|regex:/^([a-zA-Z0-9 _]+)$/",
                        'service.*' => "required",
                        'product.*' => "required",
                        'type.*' => "required|in:fixed,percent",
                        'is_active.*' => "required|in:0,1",
                        'start_value.*' => "nullable|numeric",
                        'end_value.*' => "nullable|numeric",
                        'fee.*' => "required|numeric",
                        'min_fee.*' => "nullable|numeric",
                        'max_fee.*' => "nullable|numeric",
                        'is_copy' => "nullable|in:copy_scheme"
                    ],
                    [
                        'required' => "Required"
                    ]
                );


                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }


                $timestamp = date('Y-m-d H:i:s');

                DB::beginTransaction();

                //create new scheme
                $scId = DB::table('schemes')->insertGetId([
                    'scheme_name' => ucfirst($request->scheme_name),
                    'is_active' => '1',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);


                if (!is_array($request->service)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Please add at least one rule.");
                    $this->title = "Scheme Added";
                    return $this->populateresponse();
                }


                //insert rules
                foreach ($request->service as $i => $row) {

                    //insert new scheme
                    $tempInArr[] = [
                        'scheme_id' => $scId,
                        'service_id' => $request->service[$i],
                        'product_id' => $request->product[$i],
                        'start_value' => $request->start_value[$i],
                        'end_value' => $request->end_value[$i],
                        'fee' => $request->fee[$i],
                        'min_fee' => $request->min_fee[$i],
                        'max_fee' => $request->max_fee[$i],
                        'is_active' => $request->is_active[$i],
                        'type' => $request->type[$i],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                $in = DB::table('scheme_rules')->insert($tempInArr);

                if ($in) {
                    DB::commit();

                    if (!empty($request->is_copy)) {
                        $this->status_code = '200-1';
                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = "Scheme & Rules are copied successfully";
                        $this->title = "Scheme Copied";
                        $this->redirect = true;
                    } else {
                        $this->status_code = '200-1';
                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = "New scheme and rules are added successfully";
                        $this->title = "Scheme Added";
                        $this->redirect = true;
                    }

                    return $this->populateresponse();
                }

                $this->status_code = '100';
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "Something went wrong");
                $this->title = "Scheme Added";
                return $this->populateresponse();
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            DB::rollBack();

            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            $this->title = "Rule Updated";
            return $this->populateresponse();
        }
    }


    /**
     * Fetch Scheme and Rules
     */
    public function listSchemesAndRules(Request $request)
    {
        try {
            if (empty($request->scheme_id)) {
                return ResponseHelper::failed("Scheme id can't empty");
            }

            $schemeId = trim($request->scheme_id);

            //get scheme name form DB
            $scheme = DB::table('schemes')
                ->where('id', $schemeId)
                ->first();

            if (empty($scheme)) {
                return ResponseHelper::failed("Invalid scheme id");
            }


            $data['schemeId'] = encrypt($scheme->id);
            $data['schemeName'] = $scheme->scheme_name;

            //fetch all rules for this scheme

            $data['schemeRules'] = DB::table('scheme_rules')
                ->select('global_services.service_name', 'global_products.name', 'scheme_rules.*')
                ->leftJoin('global_services', 'global_services.service_id', '=', 'scheme_rules.service_id')
                ->leftJoin('global_products', 'global_products.product_id', '=', 'scheme_rules.product_id')
                ->where('scheme_id', $scheme->id)
                ->orderBy('service_id', 'asc')
                ->orderBy('product_id', 'asc')
                ->get();

            return ResponseHelper::success('success', $data);
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }


    /**
     * Edit Scheme and Rules
     */
    public function editSchemesAndRules(Request $request)
    {
        try {

            if (Auth::user()->hasRole('super-admin')) {
                $scheme_id = decrypt($request->scheme_id);

                $validator = Validator::make(
                    $request->all(),
                    [
                        'scheme_id' => "required",
                        'scheme_name' => "required|unique:schemes,scheme_name,{$scheme_id}|regex:/^([a-zA-Z0-9 _]+)$/",
                        'rule_id.*' => "required|numeric",
                        'service.*' => "required",
                        'product.*' => "required",
                        'type.*' => "required|in:fixed,percent",
                        'is_active.*' => "required|in:0,1",
                        'start_value.*' => "nullable|numeric",
                        'end_value.*' => "nullable|numeric",
                        'fee.*' => "required|numeric",
                        'min_fee.*' => "nullable|numeric",
                        'max_fee.*' => "nullable|numeric",
                    ],
                    [
                        'required' => "Required"
                    ]
                );

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }


                $timestamp = date('Y-m-d H:i:s');

                DB::beginTransaction();

                //create new scheme
                DB::table('schemes')->where('id', $scheme_id)
                    ->update([
                        'scheme_name' => ucfirst($request->scheme_name),
                        // 'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);


                if (!is_array($request->service)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Please add at least one rule.");
                    $this->title = "Scheme Updated";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                //insert rules
                foreach ($request->service as $i => $row) {

                    if (!empty($request->rule_id[$i])) {
                        // update rules
                        $tempInArrUpdate[] = [
                            // 'scheme_id' => $scId,
                            'id' => $request->rule_id[$i],
                            'service_id' => $request->service[$i],
                            'product_id' => $request->product[$i],
                            'start_value' => $request->start_value[$i],
                            'end_value' => $request->end_value[$i],
                            'fee' => $request->fee[$i],
                            'min_fee' => $request->min_fee[$i],
                            'max_fee' => $request->max_fee[$i],
                            'is_active' => $request->is_active[$i],
                            'type' => $request->type[$i],
                            // 'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    } else {
                        //insert new scheme
                        $tempInArr[] = [
                            'scheme_id' => $scheme_id,
                            'service_id' => $request->service[$i],
                            'product_id' => $request->product[$i],
                            'start_value' => $request->start_value[$i],
                            'end_value' => $request->end_value[$i],
                            'fee' => $request->fee[$i],
                            'min_fee' => $request->min_fee[$i],
                            'max_fee' => $request->max_fee[$i],
                            'is_active' => $request->is_active[$i],
                            'type' => $request->type[$i],
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                if (!empty($tempInArr)) {
                    $in = DB::table('scheme_rules')->insert($tempInArr);
                }
                if (!empty($tempInArrUpdate)) {
                    $in = DB::table('scheme_rules')->upsert($tempInArrUpdate, ['id']);
                }

                if ($in) {
                    DB::commit();

                    $this->status_code = '200';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "Scheme rules are updated successfully";
                    $this->title = "Scheme Updated";
                    $this->redirect = true;
                    return $this->populateresponse();
                }

                $this->status_code = '100';
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "Something went wrong");
                $this->title = "Rule Updated";
                return $this->populateresponse();
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            DB::rollBack();

            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            $this->title = "Rule Updated";
            return $this->populateresponse();
        }
    }





    /**
     * Assign scheme to user
     */
    function assignScheme2User(Request $request)
    {
        try {

            if (Auth::user()->hasRole('super-admin')) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'scheme_id' => "required",
                        'user_token' => "required",
                    ]
                );

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }

                $userId = decrypt($request->user_token);
                $schemeId = decrypt($request->scheme_id);

                //check scheme already assigned or not
                $userConfig = DB::table('user_config')
                    ->select('user_config.id', 'schemes.scheme_name')
                    ->leftJoin('schemes', 'schemes.id', '=', 'user_config.scheme_id')
                    ->whereNotNull('scheme_id')
                    ->where('user_id', $userId)
                    ->first();

                if (!empty($userConfig)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This user has already assigned scheme ($userConfig->scheme_name)");
                    $this->title = "Assign to User";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                //check scheme status
                $schemeStatus = DB::table('schemes')
                    ->select('is_active', 'scheme_name')
                    ->where('id', $schemeId)
                    ->first();


                if ($schemeStatus->is_active !== '1') {
                    $this->status_code = '100';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => $schemeStatus->scheme_name . " is inactive.");
                    $this->title = "Alert";
                    return $this->populateresponse();
                }


                //insert new Rule
                $timestamp = date('Y-m-d H:i:s');

                //insert new scheme
                $ruleId = DB::table('user_config')->updateOrInsert(
                    [
                        'user_id' => $userId,
                    ],
                    [
                        'scheme_id' => $schemeId,
                        'updated_at' => $timestamp,
                    ]
                );

                if ($ruleId) {
                    $this->status_code = '200';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "Scheme assigned to user successfully";
                    $this->title = "Scheme Assigned";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
            } else {
                return abort(404);
            }
        } catch (Exception $e) {

            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: {$e->getMessage()}");
            $this->title = "Assign to User";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Delete Actions
     */
    public function deleteActions(Request $request, $action)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {
                switch ($action) {
                    case 'scheme-relation':
                        $validator = Validator::make(
                            $request->all(),
                            [
                                'relation_id' => "required|numeric"
                            ]
                        );

                        if ($validator->fails()) {
                            $this->status_code = '100';
                            $this->status = true;
                            $this->modal = true;
                            $this->alert = true;
                            $this->message  = "Invalid relation ID";
                            $this->title = "Scheme Revoked";
                            return $this->populateresponse();
                        }

                        $query = DB::table('user_config')->where('id', trim($request->relation_id))
                            ->update([
                                'scheme_id' => null
                            ]);

                        break;

                    default:
                        return response()->json([], 404);
                        break;
                }

                if ($query) {
                    $this->status_code = '200';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "User & Scheme relation removed successfully";
                    $this->title = "Scheme Revoked";
                    return $this->populateresponse();
                }
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message  = "Error: " . $e->getMessage();
            return $this->populateresponse();
        }
    }


    /**
     * 
     */
    public function statusActions(Request $request, $action)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {
                switch ($action) {
                        // case 'scheme-rule':
                        //     $status = DB::table('scheme_rules')
                        //         ->select('is_active')
                        //         ->where('id', $id)
                        //         ->first();

                        //     if ($status->is_active === '1') {
                        //         DB::table('scheme_rules')
                        //             ->where('id', $id)
                        //             ->update(['is_active' => '0']);
                        //         $message = "Rule Deactivated Successfully";
                        //         $title = 'Deactivated';
                        //     } else if ($status->is_active === '0') {
                        //         DB::table('scheme_rules')
                        //             ->where('id', $id)
                        //             ->update(['is_active' => '1']);
                        //         $message = "Rule Activated Successfully";
                        //         $title = 'Activated';
                        //     }

                        //     $this->status_code = '200';
                        //     $this->status = true;
                        //     $this->modal = true;
                        //     $this->alert = true;
                        //     $this->message = $message;
                        //     $this->title = $title;
                        //     return $this->populateresponse();

                        //     break;

                    case 'schemes':

                        if (empty($request->schemeId)) {
                            $this->status_code = '100';
                            $this->status = true;
                            $this->modal = true;
                            $this->alert = true;
                            $this->message  = "Invalid relation ID";
                            $this->title = "Scheme Revoked";
                            return $this->populateresponse();
                        }


                        $id = trim($request->schemeId);

                        //check that scheme is already assigned
                        $isAssigned = DB::table('user_config')
                            // ->select(DB::raw("count(id) as isAssign"))
                            ->where('scheme_id', $id)
                            ->count('id');

                        if ($isAssigned > 0) {
                            $this->status_code = '100';
                            $this->status = true;
                            $this->modal = true;
                            $this->alert = true;
                            $this->message  = "Failed!! This scheme is assigned to user.";
                            $this->title = "Alert";
                            return $this->populateresponse();
                        }


                        $status = DB::table('schemes')
                            ->select('is_active')
                            ->where('id', $id)
                            ->first();

                        if ($status->is_active === '1') {
                            DB::table('schemes')
                                ->where('id', $id)
                                ->update(['is_active' => '0']);
                            $message = "Scheme Deactivated Successfully";
                            $title = 'Deactivated';
                        } else if ($status->is_active === '0') {
                            DB::table('schemes')
                                ->where('id', $id)
                                ->update(['is_active' => '1']);
                            $message = "Scheme Activated Successfully";
                            $title = 'Activated';
                        }


                        $this->status_code = '200';
                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = $message;
                        $this->title = $title;
                        return $this->populateresponse();
                        break;

                    default:
                        return response()->json([], 404);
                        break;
                }
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->title = "Exception";
            $this->message  = "Error: " . $e->getMessage();
            return $this->populateresponse();
        }
    }



    /**
     * 
     */
    public function datatableReports(Request $request, $type, $id = 0, $returnType = "all")
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $request['return'] = 'all';
            $request->orderIdArray = [];
            $request->serviceIdArray = [];
            $request->userIdArray = [];
            $request->adminUserIdArray = [];
            $request->adminSchemeIdArray = [];
            $request['returnType'] = $returnType;
            // $parentData = session('parentData');
            $request['where'] = 0;
            $request['type'] = $type;

            switch ($type) {

                case 'schemes-info':

                    $request['table'] = '\App\Models\CustomScheme';

                    $request['searchData'] = [
                        'scheme_name', 'created_at'
                    ];

                    $request['select'] = 'all';
                    $request['with'] = ['isAssigned'];
                    $orderIndex = $request->get('order');
                    if (isset($orderIndex) && count($orderIndex)) {
                        $columnsIndex = $request->get('columns');
                        $columnIndex = $orderIndex[0]['column']; // Column index
                        $columnName = $columnsIndex[$columnIndex]['data']; // Column name
                        $columnSortOrder = $orderIndex[0]['dir']; // asc or desc
                        if ($columnName == 'new_created_at') {
                            $columnName = 'created_at';
                        }
                        if ($columnName == '0') {
                            $columnName = 'created_at';
                            $columnSortOrder = 'DESC'; // asc or desc
                        }
                        $request['order'] = [$columnName, $columnSortOrder];
                    } else {
                        $request['order'] = ['id', 'DESC'];
                    }
                    if (Auth::user()->is_admin == '1') {
                        $request['parentData'] = 'all';
                    } else {
                        $request['whereIn'] = 'user_id';
                        $request['parentData'] = [Auth::user()->id];
                    }

                    break;


                case 'user-and-scheme':

                    $request['table'] = '\App\Models\UserConfig';

                    $request['searchData'] = [
                        'user_id', 'scheme_id', 'created_at'
                    ];

                    $request['with'] = ['schemesName', 'userNameEmail'];
                    $request['select'] = ['id', 'user_id', 'scheme_id'];
                    $request['whereNotNull'] = 'scheme_id';

                    $orderIndex = $request->get('order');
                    if (isset($orderIndex) && count($orderIndex)) {
                        $columnsIndex = $request->get('columns');
                        $columnIndex = $orderIndex[0]['column']; // Column index
                        $columnName = $columnsIndex[$columnIndex]['data']; // Column name
                        $columnSortOrder = $orderIndex[0]['dir']; // asc or desc
                        if ($columnName == 'new_created_at') {
                            $columnName = 'created_at';
                        }
                        if ($columnName == '0') {
                            $columnName = 'created_at';
                            $columnSortOrder = 'DESC'; // asc or desc
                        }
                        $request['order'] = [$columnName, $columnSortOrder];
                    } else {
                        $request['order'] = ['id', 'DESC'];
                    }
                    if (Auth::user()->is_admin == '1') {
                        $request['parentData'] = 'all';
                    } else {
                        $request['whereIn'] = 'user_id';
                        $request['parentData'] = [Auth::user()->id];
                    }

                    break;

                    // case 'schemes-and-fee':

                    //     $request['table'] = '\App\Models\CustomSchemeRules';

                    //     $request['searchData'] = [
                    //         'scheme_id', 'service_id', 'product_id', 'start_value', 'end_value', 'type', 'fee', 'min_fee', 'max_fee', 'is_active', 'created_at', 'updated_at'
                    //     ];

                    //     $request['select'] = 'all';
                    //     $request['with'] = ['schemesName', 'serviceName', 'productName'];
                    //     $orderIndex = $request->get('order');
                    //     if (isset($orderIndex) && count($orderIndex)) {
                    //         $columnsIndex = $request->get('columns');
                    //         $columnIndex = $orderIndex[0]['column']; // Column index
                    //         $columnName = $columnsIndex[$columnIndex]['data']; // Column name
                    //         $columnSortOrder = $orderIndex[0]['dir']; // asc or desc
                    //         if ($columnName == 'new_created_at') {
                    //             $columnName = 'created_at';
                    //         }
                    //         if ($columnName == '0') {
                    //             $columnName = 'created_at';
                    //             $columnSortOrder = 'DESC'; // asc or desc
                    //         }
                    //         $request['order'] = [$columnName, $columnSortOrder];
                    //     } else {
                    //         $request['order'] = ['id', 'DESC'];
                    //     }
                    //     if (Auth::user()->is_admin == '1') {
                    //         $request['parentData'] = 'all';
                    //     } else {
                    //         $request['whereIn'] = 'user_id';
                    //         $request['parentData'] = [Auth::user()->id];
                    //     }

                    //     if (!empty($request->scheme_id)) {
                    //         $request['where_filter'] = ['scheme_id', $request->scheme_id];
                    //     }

                    //     break;

                default:
                    return response()->json([], 404);
                    break;
            }

            try {
                $totalData = $this->getData($request, 'count');
            } catch (Exception $e) {
                $totalData = 0;
            }
            if (isset($request->search['value'])) {
                $request->searchText = $request->search['value'];
            }
            if (isset($request->userId)) {
                $request->adminUserIdArray = $request->userId;
            }

            if ((isset($request->searchText) && !empty($request->searchText)) ||
                (isset($request->to) && !empty($request->to))       ||
                (isset($request->tr_type) && !empty($request->tr_type))       ||
                (isset($request->account_number) && !empty($request->account_number))       ||
                (isset($request->from) && !empty($request->from))       ||
                (isset($request->status) && $request->status != '') ||
                (isset($request->is_active) && $request->is_active != '') ||
                (isset($request->userId) && $request->userId != '') ||
                (isset($request->user_id) && !empty($request->user_id))
            ) {
                $request['where'] = 1;
            }
            try {
                $totalFiltered = $this->getData($request, 'count');
            } catch (Exception $e) {
                $totalFiltered = 0;
            }

            try {
                $data = $this->getData($request, 'data');
            } catch (Exception $e) {
                $data = [];
            }
            if ($request->return == "all" || $returnType == "all") {
                $json_data = array(
                    "draw"            => intval($request['draw']),
                    "recordsTotal"    => intval($totalData),
                    "recordsFiltered" => intval($totalFiltered),
                    "data"            => $data
                );
                echo json_encode($json_data);
            } else {
                return response()->json($data);
            }
        } else {
            return abort(404);
        }
    }



    /**
     * 
     */
    private function getData($request, $returnType)
    {
        // dd($request->all());
        $table = $request->table;
        $data = $table::query();
        $data->orderBy($request->order[0], $request->order[1]);
        if ($request->parentData != 'all') {
            if (!is_array($request->whereIn)) {
                $data->whereIn($request->whereIn, $request->parentData);
            } else {
                $data->where(function ($query) use ($request) {
                    $query->where($request->whereIn[0], $request->parentData)
                        ->orWhere($request->whereIn[1], $request->parentData);
                });
            }
        }
        if ($request['type'] == 'users' && Auth::user()->is_admin == '1') {
            $data->where('is_admin', '0');
        }

        if (!empty($request['where_filter'])) {
            $data->where($request['where_filter'][0], $request['where_filter'][1]);
        }

        if (!empty($request['whereNotNull'])) {
            $data->whereNotNull($request['whereNotNull']);
        }

        if (!empty($request['service_id'])) {
            $data->where('service_id', $request['service_id']);
        }

        if (!empty($request['product_id'])) {
            $data->where('product_id', $request['product_id']);
        }

        if (!empty($request['scheme_id_relation'])) {
            $data->where('scheme_id', $request['scheme_id_relation']);
        }

        if ($request->where) {
            if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
                if ($request->from == $request->to) {
                    $data->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                } else if ($request['type'] == 'daybook') {
                    $data->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from)
                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->format('Y-m-d')]);
                } else {
                    $data->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from)
                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')]);
                }
            }
            if (isset($request->user_id) && !empty($request->user_id)) {
                $data->where('user_id', $request->user_id);
            }

            if (isset($request->status) && $request->status != '' && $request->status != null) {
                $data->where('status', $request->status);
            }

            if (isset($request->is_active) && $request->is_active != '' && $request->is_active != null) {
                $data->where('is_active', $request->is_active);
            }



            if (isset($request->tr_type) && $request->tr_type != '' && $request->tr_type != null) {
                $data->where('tr_type', $request->tr_type);
            }
            if (isset($request->account_number) && $request->account_number != '' && $request->account_number != null) {
                $data->where('account_number', $request->account_number);
            }
            if (isset($request->adminUserIdArray) && count($request->adminUserIdArray)) {
                $data->whereIn('user_id', $request->adminUserIdArray);
            }
            if (isset($request->adminSchemeIdArray) && count($request->adminSchemeIdArray)) {
                $data->where('scheme_id', $request->adminSchemeIdArray);
            }


            if (isset($request->orderIdArray) && count($request->orderIdArray)) {
                $data->whereIn('order_ref_id', $request->orderIdArray);
            } else if (isset($request->serviceIdArray) && count($request->serviceIdArray)) {
                $data->whereIn('service_id', $request->serviceIdArray);
            } else if (isset($request->userIdArray) && count($request->userIdArray)) {
                $data->whereIn('user_id', $request->userIdArray);
            } else {
                if (!empty($request->searchText)) {
                    $data->where(function ($q) use ($request) {
                        foreach ($request->searchData as $value) {
                            $q->orWhere($value, 'like', $request->searchText . '%');
                            $q->orWhere($value, 'like', '%' . $request->searchText . '%');
                            $q->orWhere($value, 'like', '%' . $request->searchText);
                        }
                    });
                }
            }
        }
        if ($request->return == "all" || $request->returnType == "all") {
            if ($returnType == "count") {
                return $data->count();
            } else {
                if ($request['length'] != -1) {
                    $data->skip($request['start'])->take($request['length']);
                }
                if ($request->select == "all") {
                    if ($request->with) {
                        $data = $data->with($request->with)->select('*')->get();
                    } else {
                        $data = $data->get();
                    }

                    foreach ($data as $key => $value) {
                        $value->new_created_at = $value->created_at->format('Y-m-d H:i:s');
                    }
                    return $data;
                } else {
                    if ($request->with) {
                        $data = $data->with($request->with)->select($request->select)->get();
                    } else {
                        $data = $data->get();
                    }

                    return $data;
                }
            }
        } else {
            if ($request->select == "all") {
                return $data->first();
            } else {
                return $data->select($request->select)->first();
            }
        }
    }
}
