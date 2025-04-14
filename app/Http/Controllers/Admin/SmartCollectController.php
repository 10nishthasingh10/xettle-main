<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SmartCollectController extends Controller
{
    /**
     * UPI Stack
     */
    public function editInfo()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] =  "Update Smart Collect VAN";
            $data['site_title'] =  "Update Smart Collect VAN";
            $data['view']       = ADMIN . ".smart_collect_van_edit_info";

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Get Products Info
     */
    public function getBizzInfo($bizzId)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $sqlQuery = DB::table('cf_merchants')
                ->select(
                    'cf_merchants.id',
                    'cf_merchants.business_name',
                    'cf_merchants.bank_ifsc',
                    'cf_merchants.bank_account_no',
                    'users.name',
                    'users.email',
                    'users.mobile'
                )
                ->leftJoin('users', 'cf_merchants.user_id', 'users.id')
                ->where('users.is_active', '1')
                ->where('cf_merchants.id', $bizzId)
                ->where('cf_merchants.service_type', 'van')
                ->first();

            if (!empty($sqlQuery)) {
                return ResponseHelper::success('success', $sqlQuery);
            }

            return ResponseHelper::failed('failed');
        } else {
            return abort(404);
        }
    }


    /**
     * Fetch record by UTR
     */
    public function editInfoSubmit(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'bizz_id' => 'required',
                    'business_name' => "required",
                    'account_number' => "required",
                    'ifsc' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            try {

                $apiStatus = 0;
                $apiMessage = 'Something went wrong.';

                $bizzId = $request->bizz_id;
                $businessName = $request->business_name;
                $accountNumber = $request->account_number;
                $ifsc = strtoupper($request->ifsc);

                $accountId = DB::table('cf_merchants')
                    ->select(
                        'v_account_id',
                    )
                    ->where('service_type', 'van')
                    ->where('id', $bizzId)
                    ->first();

                if (empty($accountId)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This Biz ID not found.");
                    $this->title = "Update VAN Info";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                $params = [
                    "vAccountId" => $accountId->v_account_id,
                    "name" => $businessName,
                    "remitterAccount" => $accountNumber,
                    "remitterIfsc" => $ifsc,
                ];

                $vanHelper = new CashfreeAutoCollectHelper();

                $result = $vanHelper->vanManager($params, '/cac/v1/editVA', Auth::user()->id, 'POST', 'updateVanInfo');

                if ($result['code'] == 200) {

                    $cashfreeResponse = json_decode($result['response']);

                    //when response is success
                    if ($cashfreeResponse->subCode === "200") {

                        DB::table('cf_merchants')
                            ->where('id', $bizzId)
                            ->update([
                                'business_name' => $businessName,
                                'bank_account_no' => $accountNumber,
                                'bank_ifsc' => $ifsc,
                            ]);

                        $apiStatus = 1;
                        $apiMessage = "Business Info Updated Successfully";
                    }

                    $apiMessage = $cashfreeResponse->message;
                }

                if ($apiStatus === 0) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => $apiMessage);
                    $this->title = "Update VAN Info";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = $apiMessage;
                $this->title = "Update VAN Info";
                $this->redirect = true;
                return $this->populateresponse();
            } catch (Exception $e) {
                return ResponseHelper::failed($e->getMessage());
            }
        } else {
            return abort(404);
        }
    }



    /**
     * Payout Report For All Users and Total amount filtered by date range
     */
    public function reportsAll(Request $request, $service, $returnType = 'all')
    {
        if (Auth::user()->hasRole('super-admin')) {
            $request['return'] = 'all';
            $request->orderIdArray = [];
            $request->serviceIdArray = [];
            $request->userIdArray = [];
            $request['returnType'] = $returnType;
            // $parentData = session('parentData');
            $request['where'] = 0;


            $toDate = $fromDate = date('Y-m-d');

            if (!empty($request->from)) {
                $fromDate = $request->from;
            }

            if (!empty($request->to)) {
                $toDate = $request->to;
            }

            switch ($service) {

                case 'edit-info':
                    $searchData = ['users.name', 'users.email', 'users.mobile', 'cf_merchants.business_name', 'cf_merchants.bank_account_no', 'cf_merchants.van_1', 'cf_merchants.van_2'];
                    $sqlQuery = DB::table('cf_merchants')
                        ->select(
                            'cf_merchants.id',
                            'cf_merchants.business_name',
                            'cf_merchants.bank_ifsc',
                            'cf_merchants.bank_account_no',
                            'cf_merchants.van_1',
                            'cf_merchants.van_1_ifsc',
                            'cf_merchants.van_2',
                            'cf_merchants.van_2_ifsc',
                            'users.name',
                            'users.email',
                            'users.mobile'
                        )
                        ->leftJoin('users', 'cf_merchants.user_id', 'users.id')
                        ->where('users.is_active', '1')
                        ->where('cf_merchants.service_type', 'van');

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('cf_merchants.user_id', $request->user_id);
                    }

                    // $sqlQuery->orderBy('users.name', 'ASC');
                    break;

                default:
                    return abort(404);
                    break;
            }


            if (!empty($request->search['value'])) {
                $searchValue = trim($request->search['value']);
                $sqlQuery->where(function ($sql) use ($searchValue, $searchData) {
                    foreach ($searchData as $value) {
                        $sql->orWhere($value, 'like', '%' . $searchValue . '%');
                    }
                });
            }

            if (!empty($request->order[0]['column'])) {
                $filterColumn = $request->columns[$request->order[0]['column']]['data'];
                $orderBy = $request->order[0]['dir'];
                $sqlQuery->orderBy($filterColumn, $orderBy);
            } else {
                $sqlQuery->orderBy('users.name', 'DESC');
            }


            $sqlQueryCount = $sqlQuery;
            $sqlQueryCount = $sqlQueryCount->get();

            if ($request['length'] != -1) {
                $sqlQuery->skip($request->start)->take($request->length);
            }
            $result = $sqlQuery->get();


            if ($request->return == "all" || $returnType == "all") {
                $json_data = array(
                    "draw"            => intval($request['draw']),
                    "recordsTotal"    => intval(count($sqlQueryCount)),
                    "recordsFiltered" => intval(count($sqlQueryCount)),
                    "data"            => $result,
                    "from_date" => $fromDate,
                    "to_date" => $toDate,
                    "start" => $request->start,
                    "length" => $request->length,
                );
                echo json_encode($json_data);
            } else {
                return response()->json($result);
            }
        } else {
            return abort(404);
        }
    }
}
