<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class WebhookLogHelper
{
    public function getRecords($request)
    {
        try {

            $request->return = 'all';
            $request->orderIdArray = [];
            $request->serviceIdArray = [];
            $request->userIdArray = [];
            $method = $request->method;
            $modal = $request->modal;
            $toDate = $fromDate = null; //date('Y-m-d');

            $searchData = [ 'httpVerb', 'webhookUrl', 'payload', 'headers', 'meta', 'tags','attempt','uuid','response','transferStats','errorMessage','errorType','created_at'];
            if ($request->database == 'mongodb') {
                $sqlQuery =  DB::connection('mongodb')->table('webhook_logs')
                ->select('*');
                $orderById = "_id";
            } else {
                $sqlQuery =  DB::table('webhook_logs')
                                 ->select('*');
                $orderById = "id";
            }

            if (!empty($request->from) && !empty($request->to)) {
                $fromDate = trim($request->from);
                $toDate = trim($request->to);
                if ($request->database == 'mongodb') {
                    if ($fromDate === $toDate) {
                        $sqlQuery->where('created_at', 'LIKE', "%$fromDate%");
                    } else {
                      $sqlQuery->where('created_at', '>=', $fromDate)
                        ->where('created_at', '<=', $toDate.' 59:59:59');
                    }
                } else {
                    if ($fromDate === $toDate) {
                        $sqlQuery->whereDate('created_at', $fromDate);
                    } else {
                        $sqlQuery->whereBetween('created_at', [$fromDate, $toDate]);
                    }
                }
            }

            if (!empty($request->order[0]['column'])) {
                $filterColumn = $request->columns[$request->order[0]['column']]['data'];
                $orderBy = $request->order[0]['dir'];
                $sqlQuery->orderBy($filterColumn, $orderBy);
            } else {
                $sqlQuery->orderBy($orderById, 'DESC');
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


            $recordsFiltered = $sqlQuery;
            $recordsFiltered = $recordsFiltered->count();

            if ($request->length != -1) {
                $sqlQuery->skip($request->start)->take($request->length);
            }

            $data = (object) $sqlQuery->get();
        //    dd($data);
           // dd($data);
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
