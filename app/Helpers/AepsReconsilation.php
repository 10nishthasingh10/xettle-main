<?php

namespace App\Helpers;

use App\Models\AepsTransaction;
use Illuminate\Support\Facades\DB;

class AepsReconsilation
{

    /**
     * getRecords
     *
     * @param  mixed $request
     * @return void
     */
    public function getRecords($request, $type)
    {
        try {

            $request->return = 'all';
            $request->orderIdArray = [];
            $request->serviceIdArray = [];
            $request->userIdArray = [];

            $toDate = $fromDate = null; //date('Y-m-d');

            $userId = trim($request->user_id);
            $trType = $request->tr_type;
            $routeType = $request->route_type;
            $searchData = [ 'aeps_transactions.user_id','aeps_transactions.transaction_type',  'agents.first_name', 'agents.last_name', 'agents.email_id', 'agents.merchant_code','agents.address','aeps_transactions.aadhaar_no','aeps_transactions.bankiin','aeps_transactions.transaction_amount', 'aeps_transactions.created_at'];

            $sqlQuery =  DB::table('aeps_transactions');
            if ($type == 'list') {
                $sqlQuery =  $sqlQuery->select("banks.bank as iin","agents.first_name","users.name","users.email", "agents.shop_name", "agents.shop_pin","agents.state","agents.district", "aeps_transactions.route_type",  "agents.last_name","aeps_transactions.transaction_type as tr_type", "agents.email_id", "agents.mobile", "agents.address", "agents.shop_address", "aeps_transactions.aadhaar_no", "aeps_transactions.merchant_code", "aeps_transactions.status", "aeps_transactions.created_at", DB::raw("count(aadhaar_no) as aadhaar_no_count"), DB::raw("sum(aeps_transactions.transaction_amount) as totalAmount") ,"states.state_name", "districts.district_title");
            } else {
                $sqlQuery =  $sqlQuery->select("banks.bank as iin","agents.first_name","users.name","users.email", "agents.shop_name", "agents.shop_pin","agents.state","agents.district",  "agents.last_name","aeps_transactions.transaction_type as tr_type","aeps_transactions.route_type", "agents.email_id", "agents.mobile", "agents.address", "agents.shop_address", "aeps_transactions.aadhaar_no", "aeps_transactions.merchant_code", "aeps_transactions.status", "aeps_transactions.created_at","aadhaar_no as aadhaar_no_count", "aeps_transactions.transaction_amount as totalAmount",  "states.state_name", "districts.district_title");
            }
           
            
            $sqlQuery =  $sqlQuery->leftJoin('agents','agents.merchant_code','=','aeps_transactions.merchant_code')
                    ->leftJoin('banks','banks.iin','=','aeps_transactions.bankiin')

                    ->leftJoin('users','users.id','=','aeps_transactions.user_id')
                    ->leftJoin('states','states.id','=','agents.state')
                    ->leftJoin('districts','districts.id','=','agents.district')
                    ->where('aeps_transactions.status','=','success');
                    if ($type == 'list') {
                        $sqlQuery =  $sqlQuery->groupBy('aeps_transactions.aadhaar_no','aeps_transactions.merchant_code','aeps_transactions.transaction_type')
                        ->havingRaw("count(aadhaar_no) > 1");
                    }
             


            if (!empty($request->from) && !empty($request->to)) {
                $fromDate = trim($request->from);
                $toDate = trim($request->to);

                if ($fromDate === $toDate) {
                    $sqlQuery->whereDate('aeps_transactions.created_at', $fromDate);
                } else {
                    $sqlQuery->whereBetween('aeps_transactions.created_at', [$fromDate, $toDate]);
                }
            }
            if (!empty($userId)) {
                $sqlQuery->where('aeps_transactions.user_id', intval($userId));
            }
            if (!empty($trType)) {
                $sqlQuery->where('aeps_transactions.transaction_type', $trType);
            }
            if (!empty($routeType)) {
                $sqlQuery->where('aeps_transactions.route_type', $routeType);
            }
            
         

            $recordsTotal = $sqlQuery;
            $recordsTotal = $recordsTotal->count();


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
                $sqlQuery->orderBy('aadhaar_no_count', 'DESC');
            }
        
            $recordsFiltered = $sqlQuery;
            $recordsFiltered = $recordsFiltered->count();

            if ($request->length != -1) {
                $sqlQuery->skip($request->start)->take($request->length);
            }

            $data = $sqlQuery->get();
   
            $json_data = array(
                "draw"            => intval($request->draw),
                "recordsTotal"    => intval($recordsTotal),
                "recordsFiltered" => intval($recordsFiltered),
                "data"            => $data,
                "from_date" => $fromDate,
                "to_date" => $toDate,
                "start" => $request->start,
                "length" => $request->length,
            );
            return json_encode($json_data);
        } catch (\Exception $th) {
            dd($th);
        }
    }

}
