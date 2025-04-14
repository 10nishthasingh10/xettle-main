<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UpiCollect;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class IntegrationApiController extends Controller
{ 

    public function viewPipeTxn(Request $request) {
        $integrationsQuery = DB::table('integrations');
    
        if ($request->has('integration_id')) {
            $integrationsQuery->where('integration_id', $request->integration_id);
        }
    
        $integrations = $integrationsQuery->get();
        $pipeData = [];
    
        foreach ($integrations as $integration) {
            $integration_id = $integration->integration_id;
            $upiCollectStatuses = ['success', 'pending', 'rejected'];
            $orderStatuses = ['hold', 'processing', 'cancelled', 'reversed', 'failed'];
    
            $upiCollectAmounts = [];
    
            foreach ($upiCollectStatuses as $status) {
                $upiCollectAmounts[$status] = UpiCollect::where('status', $status)->where('integration_id', $integration_id)
                    ->when($request->has('user_id'), function ($query) use ($request) {
                        return $query->where('user_id', $request->user_id);
                    })->when($request->has('startDate') && $request->has('endDate'), function ($query) use ($request) {
                        return $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
                    })->sum('amount');
            }

            $payoutAmounts = [];
            foreach ($orderStatuses as $status) {
                $payoutAmounts[$status] = Order::where('status', $status)->where('integration_id', $integration_id)
                    ->when($request->has('user_id'), function ($query) use ($request) {
                        return $query->where('user_id', $request->user_id);
                    })->when($request->has('startDate') && $request->has('endDate'), function ($query) use ($request) {
                        return $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
                    })->sum('amount');
            }

            $pipeData[] = [
                'id' => $integration->id,
                'name' => $integration->name,
                'integration_id' => $integration_id,
                'payin' => $upiCollectAmounts,
                'payout' => $payoutAmounts,
            ];
        }
    
        return response()->json($pipeData);
    }


    public function TxnRecords(Request $request){
        $integrationsQuery = DB::table('integrations');
        if ($request->has('integration_id')) {
            $integrationsQuery->where('integration_id', $request->integration_id);
        }
    
        $integrations = $integrationsQuery->get();
        $allpipeData = [];
    
        $timeIntervals = [
            'minutes' => 'qr', 
            'hours' => 'qr',
            'day' => 'qr',
            'week' => 'qr',
            'months' => 'qr',
        ];
    
        if ($request->has('interval')) {
            $interval = $request->interval;
        } else {
            $interval = 'all';
        }
    
        foreach ($integrations as $integration) {
            $integration_id = $integration->integration_id;
            $upiCollectStatuses = ['success', 'pending', 'rejected'];
            $upiCollectAmounts = [];
            $upiAmounts = [];
            foreach ($upiCollectStatuses as $status) {
                $upiCollectAmounts[$status] = [];
                $upiAmounts[$status] = [];
    
                foreach ($timeIntervals as $key => $format) {
                    $minutes = Carbon::now()->subMinute();
                    $minuteOnly = $minutes->format('Y-m-d H:i:s');
                    $hours = Carbon::now()->subHour();
                    $hoursOnly = $hours->format('Y-m-d H:i:s');
                    $date = Carbon::now()->toDate();
                    $dateOnly = $date->format('Y-m-d H:i:s');
                    $week = Carbon::now();
                    $weekOnly = $week->format('Y-m-d');
                    if ($interval === 'all' || $key === $interval) {
                        $query = UpiCollect::select(DB::raw("DATE_FORMAT(created_at, '{$format}') AS interval_time"), 
                                                        DB::raw("COUNT(id) AS count"), 
                                                        DB::raw("SUM(amount) AS amount"))
                                            ->where('status', $status)
                                            ->where('integration_id', $integration_id);
    
                        if ($request->has('user_id')) {
                            $query->where('user_id', $request->user_id);
                        }
    
                        if ($interval === 'minutes') {
                            $query->where('created_at', '>=', $minuteOnly);
                        } elseif ($interval === 'hours') {
                            $query->where('created_at', '>=', $hoursOnly);
                        } elseif ($interval === 'day') {
                            $query->where('created_at', '>=', $dateOnly);
                        } elseif ($interval === 'week') {
                            $query->where('created_at ', '>=', $weekOnly);
                        } elseif ($request->has('startDate') && $request->has('endDate')) {
                            $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
                        }
    
                        $results = $query->groupBy('interval_time')->get();
    
                        foreach ($results as $result) {
                            $upiCollectAmounts[$status][$key] = $result->count;
                            $upiAmounts[$status][$key] = $result->amount;
                            // $upiAmounts[$status][$key][$result->interval_time] = $result->amount;
                        }
                    }
                }
            }
            $allpipeData[] = [
                'id' => $integration->id,
                'name' => $integration->name,
                'integration_id' => $integration_id,
                'qrRecords' => $upiCollectAmounts,
                'TotalAmount' => $upiAmounts,
            ];
        }
        return response()->json($allpipeData);
    }
    
}