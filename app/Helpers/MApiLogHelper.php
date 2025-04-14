<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MApiLogHelper
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

            $userId = trim($request->user_id);
            $serviceType = trim($request->service_type);

            $searchData = [ 'user_id', 'txnid', 'url', 'request', 'response', 'modal','method','created_at'];
            if ($request->database == 'mongodb') {
                $sqlQuery =  DB::connection('mongodb')->table('api_logs')
                ->select('*');
                $orderById = "_id";
            } else {
                $sqlQuery =  DB::table('apilogs')
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
            if (!empty($userId)) {
                $sqlQuery->where('user_id', intval($userId));
            }

            if (!empty($modal)) {
                $sqlQuery->where('modal', $modal);
            }
            if (!empty($method)) {
                $sqlQuery->where('method', $method);
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
            if (isset($data)) {
                foreach ($data as $key => $row) {
                    $row = (object) $row;
                    $users = DB::table('users')
                        ->select('name', 'email', 'mobile')
                        ->where('id', intval($row->user_id))->first();
                    $row->name = @$users->name;
                    $row->email = @$users->email;
                    $row->mobile = @$users->mobile;
                    $row->created_at = date('Y-m-d H:i:s', strtotime($row->created_at));
                    if (isset($row->created_at) && isset($row->updated_at)) {
                        $from_time =  strtotime($row->created_at);
                        $to_time = strtotime($row->updated_at);
                        $row->time = round(abs($from_time - $to_time) / 60,2). " m";
                    } else {
                        $row->time = '0m';
                    }

                    /*$isjon = self::json_validator($row->response);
                    if ($isjon)
                    {
                        Storage::append('public/apilog.txt',$row->response."\n");
                        $result = json_decode($row->response);
                    } else {
                        $row->response = '{"response" : "internal server error"}';
                    }*/

                    $data[$key]= $row;
                }
            }
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


    public function json_validator($data) {
        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }
}
