<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalBillingController extends Controller
{

    /**
     * Manage Scheme and Rules
     */
    public function index()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] =  "Global Fee Rules";
            $data['site_title'] =  "Global Fee Rules";
            $data['view']       = ADMIN . '.global_billing';
            $data['role'] = Auth::user()->roles->first->slug->slug;


            $data['services'] = DB::table('global_services')
                ->select('service_id', 'service_name')
                ->orderBy('service_name', 'ASC')
                ->get();

            $data['products'] = DB::table('global_products')
                ->select('product_id', 'name')
                // ->where('is_active', '1')
                ->orderBy('name', 'ASC')
                ->get();


            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Add New Service
     */
    public function addNewService(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'service_name' => "required|max:255|regex:/^([a-zA-Z0-9 ]+)$/",
                        'service_slug' => "required|max:255|regex:/^([a-z0-9_]+)$/",
                    ]
                );

                $validator->after(function ($validator) use ($request) {

                    $serviceSlug = strtolower(str_replace(' ', '_', trim($request->service_slug)));

                    $check = DB::table('global_services')
                        ->select('id')
                        ->where('service_slug', $serviceSlug)
                        ->first();

                    if (!empty($check)) {
                        $validator->errors()->add('service_slug', 'This slug is already is used.');
                    } else {
                        $request->service_slug = $serviceSlug;
                    }

                    $check = DB::table('global_services')
                        ->select('service_name')
                        ->whereRaw("LOWER(service_name) = '" . trim(strtolower($request->service_name)) . "'")
                        ->first();

                    if (!empty($check)) {
                        $validator->errors()->add('service_name', 'This name is already is used.');
                    }
                });

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }


                $serviceId = 'srv_' . time() . rand(0, 9);
                $serviceName = trim($request->service_name);
                // $serviceSlug = trim(strtolower(str_replace(' ', '_', $request->service_name)));

                $insert = DB::table('global_services')
                    ->insert([
                        'service_id' => $serviceId,
                        'service_name' => $serviceName,
                        'service_slug' => $request->service_slug,
                        'is_active' => '0',
                        'is_activation_allowed' => '0',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);


                if ($insert) {
                    $this->status_code = '200';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "New service added succesfully.";
                    $this->title = "Service Added";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $this->status_code = '100';
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "New service not added.");
                // $this->title = "";
                $this->redirect = false;
                return $this->populateresponse();
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            // return response("Error: " . $e->getMessage(), 404);
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }



    /**
     * Update Service
     */
    public function updateService(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'row_id' => "required|numeric",
                        'service_name' => "required|max:255|regex:/^([a-zA-Z0-9 ]+)$/",
                        // 'service_slug' => "required|max:255|regex:/^([a-z0-9_]+)$/",
                    ]
                );

                $validator->after(function ($validator) use ($request) {


                    $serviceId = trim($request->row_id);

                    $serviceRow = DB::table('global_services')
                        ->select('id')
                        ->where('id', $serviceId)
                        ->first();

                    if (empty($serviceRow)) {
                        $validator->errors()->add('service_name', 'This service is not exist.');
                    }

                    // $serviceSlug = strtolower(str_replace(' ', '_', trim($request->service_slug)));

                    // $check = DB::table('global_services')
                    //     ->select('id')
                    //     ->where('service_slug', $serviceSlug)
                    //     ->where('id', '<>', $serviceId)
                    //     ->first();

                    // if (!empty($check)) {
                    //     $validator->errors()->add('service_slug', 'This slug is already is used.');
                    // } else {
                    //     $request->service_slug = $serviceSlug;
                    // }

                    $check = DB::table('global_services')
                        ->select('service_name')
                        ->whereRaw("LOWER(service_name) = '" . trim(strtolower($request->service_name)) . "'")
                        ->where('id', '<>', $serviceId)
                        ->first();

                    if (!empty($check)) {
                        $validator->errors()->add('service_name', 'This name is already is used.');
                    }
                });

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }


                // $serviceId = uniqid('srv_');
                $serviceName = trim($request->service_name);

                $update = DB::table('global_services')
                    ->where('id', trim($request->row_id))
                    ->update([
                        'service_name' => $serviceName,
                        // 'service_slug' => $request->service_slug,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);


                if ($update) {
                    $this->status_code = '200';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "Service updated succesfully.";
                    $this->title = "Service Updated";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $this->status_code = '100';
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "Service not updated.");
                // $this->title = "";
                $this->redirect = false;
                return $this->populateresponse();
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }



    /**
     * Fetch Product List
     */
    public function productList(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'serviceId' => "required|max:55"
                    ]
                );


                if ($validator->fails()) {
                    return ResponseHelper::failed('Service Id is required.');
                }

                $serviceId = trim($request->serviceId);

                //check that service id is valid or not
                $serviceRow = DB::table('global_services')
                    ->select('id')
                    ->where('service_id', $serviceId)
                    ->first();

                if (empty($serviceRow)) {
                    return ResponseHelper::failed('Invlaid Service Id passed.');
                }


                //fetching list of products
                $productList = DB::table('global_products')
                    ->select('*')
                    ->where('service_id', $serviceId)
                    ->orderBy('service_id')
                    ->get();

                return ResponseHelper::success('Success', $productList);
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }



    /**
     * Fetch Product List
     */
    public function productFeeList(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'productId' => "required|max:55"
                    ]
                );


                if ($validator->fails()) {
                    return ResponseHelper::failed('Service Id is required.');
                }

                $productId = trim($request->productId);

                //check that service id is valid or not
                $serviceRow = DB::table('global_products')
                    ->select('id')
                    ->where('product_id', $productId)
                    ->first();

                if (empty($serviceRow)) {
                    return ResponseHelper::failed('Invlaid Product Id passed.');
                }


                //fetching list of products
                $productFeeList = DB::table('global_product_fees')
                    ->select('*')
                    ->where('product_id', $productId)
                    ->orderBy('product_id')
                    ->get();

                return ResponseHelper::success('Success', $productFeeList);
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }




    /**
     * Add and Update Product List
     */
    public function updateProductList(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'service_id' => "required",
                        'product_id.*' => "required|numeric",
                        'product_name.*' => "required",
                        'product_slug.*' => [
                            'required',
                            function ($attr, $val, $fail) use ($request) {
                                $productSlug = strtolower(str_replace(' ', '_', trim($val)));

                                $tmpIdx = intval(explode('.', $attr)[1]);
                                $productId = empty($request->product_id[$tmpIdx]) ? 0 : $request->product_id[$tmpIdx];
                                // dd($attr, $tmpIdx, $productId);
                                $count = DB::table('global_products')
                                    ->where('slug', $productSlug)
                                    ->where('service_id', trim($request->service_id))
                                    ->where('id', '<>', $productId)
                                    ->count();

                                if ($count > 0) {
                                    $fail('This slug is already added.');
                                }
                            },
                            // function ($attr, $val, $fail) use ($request) {
                            //     if (count($request->product_slug) !== count(array_unique($request->product_slug))) {
                            //         $fail('Product slug are duplicate');
                            //     }
                            // }
                        ],
                        // 'type.*' => "required|in:fixed,percent",
                        'is_active.*' => "required|in:0,1",
                        'min_ord_value.*' => "nullable|numeric",
                        'max_ord_value.*' => "nullable|numeric",
                        'tax_value.*' => "nullable|numeric",
                    ],
                    [
                        'required' => "This is required."
                    ]
                );



                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }


                $timestamp = date('Y-m-d H:i:s');

                DB::beginTransaction();

                $serviceId = trim($request->service_id);

                //check that service id is valid or not
                $serviceRow = DB::table('global_services')
                    ->select('id', 'service_slug')
                    ->where('service_id', $serviceId)
                    ->first();

                if (empty($serviceRow)) {
                    $this->status_code = '100';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Invalid Service ID.");
                    // $this->title = "Product Updated";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if (!is_array($request->product_name)) {
                    $this->status_code = '100';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Please add at least one product.");
                    // $this->title = "Product Updated";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                //insert rules
                $loopIndex = 0;
                foreach ($request->product_name as $i => $row) {

                    if (!empty($request->product_id[$i])) {
                        // update rules
                        $tempInArrUpdate[] = [
                            // 'scheme_id' => $scId,
                            'id' => $request->product_id[$i],
                            'name' => trim($request->product_name[$i]),
                            // 'slug' => strtolower(str_replace(' ', '_', trim($request->product_slug[$i]))),
                            'min_order_value' => empty($request->min_ord_value[$i]) ? 0 : $request->min_ord_value[$i],
                            'max_order_value' => empty($request->max_ord_value[$i]) ? 0 : $request->max_ord_value[$i],
                            'tax_value' => empty($request->tax_value[$i]) ? 0 : $request->tax_value[$i],
                            'updated_at' => $timestamp,
                        ];
                    } else {
                        //insert new scheme
                        $tempInArr[] = [
                            'product_id' => 'pr_' . time() . $loopIndex++,
                            'service_id' => $serviceId,
                            'name' => trim($request->product_name[$i]),
                            'slug' => strtolower(str_replace(' ', '_', trim($request->product_slug[$i]))),
                            'min_order_value' => empty($request->min_ord_value[$i]) ? 0 : $request->min_ord_value[$i],
                            'max_order_value' => empty($request->max_ord_value[$i]) ? 0 : $request->max_ord_value[$i],
                            'type' => $serviceRow->service_slug,
                            'is_active' => '1',
                            'is_fee_enabled' => '1',
                            'is_tax_enabled' => '1',
                            'tax_value' => empty($request->tax_value[$i]) ? 0 : $request->tax_value[$i],
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                if (!empty($tempInArr)) {
                    $in = DB::table('global_products')->insert($tempInArr);
                }
                if (!empty($tempInArrUpdate)) {
                    $in = DB::table('global_products')->upsert($tempInArrUpdate, ['id']);
                }

                if ($in) {
                    DB::commit();

                    $this->status_code = '200';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "Changes are saved successfully.";
                    $this->title = "Product Update";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
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
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Add and Update Product Fee List
     */
    public function updateProductFeeList(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'product_id' => "required",
                        'fee_id.*' => "required|numeric",
                        'fee_type.*' => "required|in:fixed,percent",
                        // 'is_active.*' => "required|in:0,1",
                        'fee_start_value.*' => "nullable|numeric",
                        'fee_end_value.*' => "nullable|numeric",
                        'fee_value.*' => "required|numeric",
                        'fee_min.*' => "nullable|numeric",
                        'fee_max.*' => "nullable|numeric",
                    ],
                    [
                        'required' => "This is required",
                        'numeric' => "This must be a number"
                    ]
                );



                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing('Some params are missing.', $message);
                }


                $timestamp = date('Y-m-d H:i:s');

                DB::beginTransaction();

                $productId = trim($request->product_id);

                //check that service id is valid or not
                $productRow = DB::table('global_products')
                    ->select('id', 'product_id')
                    ->where('product_id', $productId)
                    ->first();

                if (empty($productRow)) {
                    $this->status_code = '100';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Invalid Product ID.");
                    // $this->title = "Product Updated";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                if (!is_array($request->fee_type)) {
                    $this->status_code = '100';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "Please add at least one product fee.");
                    // $this->title = "Product Updated";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                //insert rules
                // $loopIndex = 0;
                foreach ($request->fee_type as $i => $row) {

                    if (!empty($request->fee_id[$i])) {
                        // update rules
                        $tempInArrUpdate[] = [
                            'id' => $request->fee_id[$i],
                            'start_value' => $request->fee_start_value[$i],
                            'end_value' => $request->fee_end_value[$i],
                            'fee' => $request->fee_value[$i],
                            'min_fee' => $request->fee_min[$i],
                            'max_fee' => $request->fee_max[$i],
                            // 'is_active' => $request->is_active[$i],
                            'type' => $request->fee_type[$i],
                            'updated_at' => $timestamp,
                        ];
                    } else {
                        //insert new scheme
                        $tempInArr[] = [
                            'product_id' => $productRow->product_id,
                            'start_value' => $request->fee_start_value[$i],
                            'end_value' => $request->fee_end_value[$i],
                            'fee' => $request->fee_value[$i],
                            'min_fee' => $request->fee_min[$i],
                            'max_fee' => $request->fee_max[$i],
                            // 'is_active' => $request->is_active[$i],
                            'type' => $request->fee_type[$i],
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                if (!empty($tempInArr)) {
                    $in = DB::table('global_product_fees')->insert($tempInArr);
                }
                if (!empty($tempInArrUpdate)) {
                    $in = DB::table('global_product_fees')->upsert($tempInArrUpdate, ['id']);
                }

                if ($in) {
                    DB::commit();

                    $this->status_code = '200';
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = "Changes are saved successfully.";
                    $this->title = "Product Fee Update";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
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
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * 
     */
    public function statusActions($action, $id)
    {
        // dd($id);
        try {
            if (Auth::user()->hasRole('super-admin')) {
                switch ($action) {
                    case 'service-status':
                        $status = DB::table('global_services')
                            ->select('is_active')
                            ->where('id', $id)
                            ->first();

                        if ($status->is_active === '1') {
                            DB::table('global_services')
                                ->where('id', $id)
                                ->update(['is_active' => '0']);
                            $message = "Service Deactivated Successfully";
                            $title = 'Deactivated';
                        } else if ($status->is_active === '0') {
                            DB::table('global_services')
                                ->where('id', $id)
                                ->update(['is_active' => '1']);
                            $message = "Service Activated Successfully";
                            $title = 'Activated';
                        }

                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = $message;
                        $this->title = $title;
                        $this->redirect = false;
                        return $this->populateresponse();

                        break;

                    case 'service-activation':
                        $status = DB::table('global_services')
                            ->select('is_activation_allowed')
                            ->where('id', $id)
                            ->first();
// dd($status);
                        if ($status->is_activation_allowed === '1') {
                            DB::table('global_services')
                                ->where('id', $id)
                                ->update(['is_activation_allowed' => '0']);
                            $message = "Service actvation is closed.";
                            $title = 'Deactivated';
                        } else if ($status->is_activation_allowed === '0') {
                            DB::table('global_services')
                                ->where('id', $id)
                                ->update(['is_activation_allowed' => '1']);
                            $message = "Service is ready for activation.";
                            $title = 'Activated';
                        }

                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = $message;
                        $this->title = $title;
                        $this->redirect = false;
                        return $this->populateresponse();

                        break;

                    case 'product-status':
                        $status = DB::table('global_products')
                            ->select('is_active')
                            ->where('id', $id)
                            ->first();

                        if ($status->is_active === '1') {
                            DB::table('global_products')
                                ->where('id', $id)
                                ->update(['is_active' => '0']);
                            $message = "Product Deactivated Successfully";
                            $title = 'Deactivated';
                        } else if ($status->is_active === '0') {
                            DB::table('global_products')
                                ->where('id', $id)
                                ->update(['is_active' => '1']);
                            $message = "Product Activated Successfully";
                            $title = 'Activated';
                        }

                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = $message;
                        $this->title = $title;
                        $this->redirect = false;
                        return $this->populateresponse();

                        break;


                    case 'scheme-rule':
                        $status = DB::table('global_product_fees')
                            ->select('is_active')
                            ->where('id', $id)
                            ->first();

                        if ($status->is_active === '1') {
                            DB::table('global_product_fees')
                                ->where('id', $id)
                                ->update(['is_active' => '0']);
                            $message = "Rule Deactivated Successfully";
                            $title = 'Deactivated';
                        } else if ($status->is_active === '0') {
                            DB::table('global_product_fees')
                                ->where('id', $id)
                                ->update(['is_active' => '1']);
                            $message = "Rule Activated Successfully";
                            $title = 'Activated';
                        }


                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = $message;
                        $this->title = $title;
                        $this->redirect = false;
                        return $this->populateresponse();

                        break;

                    case 'schemes':
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


                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = $message;
                        $this->title = $title;
                        $this->redirect = false;
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
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    /**
     * Datatables Report
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

                case 'service-list':

                    $request['table'] = '\App\Models\Service';
                    $request['searchData'] = [
                        'service_id', 'service_name', 'service_slug', 'is_active', 'is_activation_allowed', 'created_at'
                    ];
                    $request['select'] = ['id', 'service_id', 'service_name', 'service_slug', 'is_active', 'is_activation_allowed', 'created_at'];

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
                        $request['order'] = ['service_name', 'ASC'];
                    }

                    $request['parentData'] = 'all';

                    break;

                case 'product-and-services':

                    $request['table'] = '\App\Models\Product';

                    $request['searchData'] = [
                        'product_id', 'service_id', 'name', 'slug', 'type', 'is_active', 'created_at'
                    ];

                    // $request['select'] = 'all';
                    $request['select'] = ['id', 'product_id', 'service_id', 'name', 'created_at'];
                    $request['with'] = ['serviceName'];
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
                        $request['order'] = ['service_id', 'ASC'];
                    }
                    if (Auth::user()->is_admin == '1') {
                        $request['parentData'] = 'all';
                    } else {
                        $request['whereIn'] = 'user_id';
                        $request['parentData'] = [Auth::user()->id];
                    }

                    if (!empty($request->service_id)) {
                        $request['filterServiceId'] = trim($request->service_id);
                    }

                    break;


                case 'product-and-fee':

                    $request['table'] = '\App\Models\ProductFee';

                    $request['searchData'] = [
                        'scheme_id', 'service_id', 'product_id', 'start_value', 'end_value', 'type', 'fee', 'min_fee', 'max_fee', 'is_active', 'created_at', 'updated_at'
                    ];

                    $request['select'] = 'all';
                    $request['with'] = ['productName', 'productName.serviceName'];
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

                    if (!empty($request->scheme_id)) {
                        $request['where_filter'] = ['scheme_id', $request->scheme_id];
                    }

                    if (!empty($request->service_id)) {
                        if (trim($request->service_id) !== 'all') {
                            $request['whereHas'] = ['productName', function ($query) use ($request) {
                                $query->where('service_id', '=', trim($request->service_id));
                            }];
                        }
                    }

                    break;

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

        // if (!empty($request['service_id'])) {
        //     $data->leftJoin('global_products', 'global_product_fees.product_id', 'global_products.product_id')
        //         ->leftJoin('global_services', 'global_products.service_id', 'global_services.service_id')
        //         ->where('global_services.service_id', $request['service_id'])->toSql();
        // }

        if ($request['type'] == 'users' && Auth::user()->is_admin == '1') {
            $data->where('is_admin', '0');
        }

        if (!empty($request['where_filter'])) {
            $data->where($request['where_filter'][0], $request['where_filter'][1]);
        }



        if (!empty($request['product_id'])) {
            $data->where('product_id', $request['product_id']);
        }

        if (!empty($request['scheme_id_relation'])) {
            $data->where('option_value', $request['scheme_id_relation']);
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

        if (!empty($request->filterServiceId)) {
            $data->where('service_id', $request->filterServiceId);
        }

        if (!empty($request->with)) {
            $data = $data->with($request->with);

            if (!empty($request->whereHas)) {
                $data->whereHas($request->whereHas[0], $request->whereHas[1]);
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

                    $data = $data->get();

                    foreach ($data as $key => $value) {
                        $value->new_created_at = $value->created_at->format('Y-m-d H:i:s');
                    }

                    return $data;
                } else {


                    $data = $data->select($request->select)->get();

                    foreach ($data as $key => $value) {
                        if (!empty($value->created_at))
                            $value->new_created_at = $value->created_at->format('Y-m-d H:i:s');
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
