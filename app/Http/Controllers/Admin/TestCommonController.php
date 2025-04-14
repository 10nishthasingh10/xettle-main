<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Service;
use App\Models\BulkPayoutDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestCommonController extends Controller
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param [type] $type
     * @param integer $id
     * @param string $returnType
     * @return void
     */
    public function fetchData(Request $request, $type, $id = 0, $returnType = "all")
    {
        $request['return'] = 'all';
        $request->orderIdArray = [];
        $request->serviceIdArray = [];
        $request->userIdArray = [];
        $request->adminUserIdArray = [];
        $request['returnType'] = $returnType;
        $parentData = session('parentData');
        $request['where'] = 0;
        $request['type'] = $type;
        switch ($type) {
            case 'orders':
                $request['table'] = '\App\Models\Order';
                $request['searchData'] = [
                    'order_ref_id', 'client_ref_id', 'integration_id',
                    'order_id', 'batch_id', 'mode', 'amount', 'bank_reference', 'status', 'area',
                    'created_at', 'payout_id', 'contact_id'
                ];
                $request['select'] = 'all';
                $orderIndex = $request->get('order');

                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column']; // Column index
                    $columnName = $columnsIndex[$columnIndex]['data']; // Column name
                    $columnSortOrder = $orderIndex[0]['dir']; // asc or desc
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0' || $columnName == 'order_ref_id') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC'; // asc or desc
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }

                $request['whereIn'] = 'user_id';
                $request['parentData'] = [$id];

                break;

        }

        try {
            $totalData = $this->getData($request, 'count');
        } catch (\Exception $e) {
            $totalData = 0;
        }
        if (isset($request->search['value'])) {
            $request->searchText = $request->search['value'];
        }
        if (isset($request->userId)) {
            $request->adminUserIdArray = $request->userId;
        }
        if (isset($request->searchText) && !empty($request->searchText) && $type == 'orders') {
            $getOrderRefId = self::getOrderRefId($request->searchText);
            $request->orderIdArray = $getOrderRefId;
        }
        if (isset($request->searchText) && !empty($request->searchText) && $type == 'serviceRequest') {
            $getServiceId = self::getServiceId($request->searchText);
            $request->serviceIdArray = $getServiceId;
        }
        if (isset($request->searchText) && !empty($request->searchText) && in_array($type, array('bulkpayouts', 'serviceRequest')) && \Auth::user()->is_admin == '1') {
            $getUserId = self::getUserId($request->searchText);
            $request->userIdArray = $getUserId;
        }

        if ((isset($request->searchText) && !empty($request->searchText)) ||
            (isset($request->to) && !empty($request->to))       ||
            (isset($request->tr_type) && !empty($request->tr_type))       ||
            (isset($request->account_number) && !empty($request->account_number))       ||
            (isset($request->from) && !empty($request->from))       ||
            (isset($request->status) && $request->status != '') ||
            (isset($request->apes_status_array) && $request->apes_status_array != '') ||
            (isset($request->area) && $request->area != '') ||
            (isset($request->account_type) && $request->account_type != '') ||
            (isset($request->tr_identifiers) && $request->tr_identifiers != '') ||
            (isset($request->service_id_array) && $request->service_id_array != '') ||
            (isset($request->integration_id) && $request->integration_id != '') ||
            (isset($request->transaction_type_array) && $request->transaction_type_array != '') ||
            (isset($request->route_type_array) && $request->route_type_array != '') ||
            (isset($request->is_active) && $request->is_active != '') ||
            (isset($request->userId) && $request->userId != '') ||
            (isset($request->user_id) && !empty($request->user_id))
        ) {
            $request['where'] = 1;
        }

        try {
            $totalFiltered = $this->getData($request, 'count');
        } catch (\Exception $e) {
            $totalFiltered = 0;
        }

        try {
            $data = $this->getData($request, 'data');
        } catch (\Exception $e) {
            print_r($e->getMessage());
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
    }

    /**
     * Undocumented function
     *
     * @param [type] $request
     * @param [type] $returnType
     * @return void
     */
    public function getData($request, $returnType)
    {
        $table = $request->table;
        $data = $table::on('slave');
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
        if ($request['type'] == 'admin_user' && Auth::user()->is_admin == '1') {
            $data->where('is_admin', '1');
        }
        if ($request['type'] == 'aepsSettlement') {
            $data->where('tr_identifiers', 'aeps_inward_credit');
        }

        if (!empty($request['is_trn_credited'])) {
            $data->where('is_trn_credited', $request['is_trn_credited']);
        }

        if (!empty($request['havingUnsettle'])) {
            $data->havingRaw("(DATE(`created_at`) >= '" . $request['havingUnsettle'] . "' AND `is_trn_credited` = '0') OR (`is_trn_credited` = '1')");
        }

        if (!empty($request['root_type'])) {
            $data->where('root_type', $request['root_type']);
        }

        if (!empty($request['root_type_in'])) {
            $data->whereIn('root_type', $request['root_type_in']);
        }

        if (!empty($request->filterArray)) {
            foreach ($request->filterArray as $key => $val) {
                $data->where($key, $val);
            }
        }
        if (isset($request->account_type) && $request->account_type != '' && $request->account_type != null) {
            $data->where('account_type', $request->account_type);
        }
        if (isset($request->contact_type) && $request->contact_type != '' && $request->contact_type != null) {
            $data->where('type', $request->contact_type);
        }
        if (isset($request->mode) && $request->mode != '' && $request->mode != null) {
            $data->where('mode', $request->mode);
        }

        if ($request->where) {
            if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to)) && ($request['type'] != 'aeps_agents')) {
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
            if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to)) && (($request['type'] == 'aeps_agents'))) {
                if ($request->date_type == 'document_uploaded_at') {
                    $date_column = 'ekyc_documents_uploaded_at';
                } else {
                    $date_column = 'created_at';
                }
                if ($request->from == $request->to) {
                    $data->whereDate($date_column, '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                } else {
                    $data->whereBetween($date_column, [Carbon::createFromFormat('Y-m-d', $request->from)
                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')]);
                }
            }
            if (isset($request->user_id) && !empty($request->user_id)) {
                $data->where('user_id', $request->user_id);
            }

            if (!empty($request->validation_type)) {
                $data->where('validation_type', $request->validation_type);
            }

            if (isset($request->status) && $request->status != '' && $request->status != null) {
                $data->where('status', $request->status);
            }
            if (isset($request->integration_id) && $request->integration_id != '' && $request->integration_id != null) {
                $data->where('integration_id', $request->integration_id);
            }

            if (isset($request->is_active) && $request->is_active != '' && $request->is_active != null) {
                $data->where('is_active', $request->is_active);
            }
            if (isset($request->route_type_array) && count($request->route_type_array)) {
                $data->whereIn('route_type', $request->route_type_array);
            }

            if (isset($request->transaction_type_array) && count($request->transaction_type_array)) {
                $data->whereIn('transaction_type', $request->transaction_type_array);
            }
            if (!empty($request->service_id)) {
                $data->where('service_id', $request->service_id);
            }
            if (!empty($request->service_id_array)  && count($request->service_id_array)) {
                $data->whereIn('service_id', $request->service_id_array);
            }
            if (!empty($request->apes_status_array)  && count($request->apes_status_array)) {
                $data->whereIn('status', $request->apes_status_array);
            }

            if (isset($request->tr_identifiers) && count($request->tr_identifiers)) {
                $data->whereIn('transactions.tr_identifiers', $request->tr_identifiers);
            }
            if (isset($request->area) && $request->area != '' && $request->area != null) {
                $data->where('area', $request->area);
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

        if ($request->with) {
            $data->with($request->with)->select('*');

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
                if ($request->select == "all" || in_array('created_at', $request->select)) {

                    $data = $data->get();

                    foreach ($data as $key => $value) {
                        if (isset($value->aadhar_number) &&  in_array($request['type'], ['aepsmerchants', 'aepsTransaction', 'aeps_agents'])) {
                            $value->aadhar_number = CommonHelper::masking('aadhar', $value->aadhar_number);
                        }
                        if (isset($value->mobile) &&  in_array($request['type'], ['aepsTransaction', 'aeps_agents'])) {
                            $value->mobile = CommonHelper::masking('mobile', $value->mobile);
                        }

                        if (isset($value->aadhaar_no) &&  in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
                            $value->aadhaar_no = CommonHelper::masking('aadhar', $value->aadhaar_no);
                        }
                        if (isset($value->bank_number) &&  in_array($request['type'], ['validation_suite_txns'])) {
                            $value->bank_number = CommonHelper::masking('aadhar', $value->bank_number);
                        }
                        if (isset($value->pan_no) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction', 'aeps_agents'])) {
                            $value->pan_no = CommonHelper::masking('pan', $value->pan_no);
                        }
                        if (isset($value->created_at)) {
                            $value->new_created_at = $value->created_at->format('Y-m-d H:i:s');
                        }

                        if (isset($value->merchant->aadhar_number) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
                            $value->merchant->aadhar_number = CommonHelper::masking('aadhar', $value->merchant->aadhar_number);
                        }
                        if (isset($value->merchant->pan_no) && in_array($request['type'], ['aepsmerchants', 'aepsTransaction'])) {
                            $value->merchant->pan_no = CommonHelper::masking('aadhar', $value->merchant->pan_no);
                        }
                        if (isset($value->merchant->mobile) && in_array($request['type'], ['aepsTransaction'])) {
                            $value->merchant->mobile = CommonHelper::masking('mobile', $value->merchant->mobile);
                        }
                        if (isset($value->payer_acc_no) && in_array($request['type'], ['upicallbacks', 'upicallbacks_tpv'])) {
                            $value->payer_acc_no = CommonHelper::masking('mobile', $value->payer_acc_no);
                        }
                    }
                    return $data;
                } else {
                    return $data->select($request->select)->get();
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

    public static function getOrderRefId($payoutRefId)
    {
        $data = [];
        $BulkPayoutDetail = BulkPayoutDetail::select('order_ref_id')
            ->where('payout_reference', 'like', $payoutRefId . '%')
            ->orWhere('payout_reference', 'like', '%' . $payoutRefId)
            ->orWhere('payout_reference', 'like', '%' . $payoutRefId . '%')
            ->get();
        foreach ($BulkPayoutDetail as $BulkPayoutDetails) {
            $data[] = $BulkPayoutDetails->order_ref_id;
        }
        return $data;
    }

    public static function getUserId($search)
    {
        $data = [];
        $User = User::select('id')
            ->where('name', 'like', $search . '%')
            ->orWhere('name', 'like', '%' . $search)
            ->orWhere('name', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', $search . '%')
            ->orWhere('email', 'like', '%' . $search)
            ->orWhere('email', 'like', '%' . $search . '%')
            ->orWhere('mobile', 'like', $search . '%')
            ->orWhere('mobile', 'like', '%' . $search)
            ->orWhere('mobile', 'like', '%' . $search . '%')
            ->get();
        foreach ($User as $Users) {
            $data[] = $Users->id;
        }
        return $data;
    }

    public static function getServiceId($serviceName)
    {
        $data = [];
        $services = Service::select('service_id')
            ->where('service_name', 'like', $serviceName . '%')
            ->orWhere('service_name', 'like', '%' . $serviceName)
            ->orWhere('service_name', 'like', '%' . $serviceName . '%')
            ->get();
        foreach ($services as $service) {
            $data[] = $service->service_id;
        }
        return $data;
    }
}
