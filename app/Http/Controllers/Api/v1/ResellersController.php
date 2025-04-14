<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use Illuminate\Support\Facades\DB;
use App\Models\Reseller;
use App\Models\User;
use App\Models\UpiCollect;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ResellersController extends Controller
{
    public function reseller_login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => "required|email",
                    'password' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ApiHelper::response('validate', [
                    'message' => $message
                ]);
            }
            $reseller = DB::table('resellers')->where('status', '1')->where('email', $request->email)->first();
            if (!$reseller) {
                return ApiHelper::response('exception', [
                    'message' => "Reseller not found",
                ]);
            }
            if (!Hash::check($request->password, $reseller->password)) {
                return ApiHelper::response('exception', [
                    'message' => "Incorrect password",
                ]);
            }

            $userData = [
                'name' => $reseller->name,
                'email' => $reseller->email,
                'password' => $reseller->password,
                'created_at' => Carbon::now()->toDateTimeString()
            ];

            $token = encrypt($userData);

            DB::table('resellers')->where('id', $reseller->id)->update(['token' => $token]);
            $res = [
                'data' => [
                    'token' => $token
                ],
                'message' => "Login Success"
            ];
            return ApiHelper::response('success', $res);
        } catch (\Exception $e) {
            return ApiHelper::response('internalservererror', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getUserList(Request $request)
    {
        try {
            if (!isset($request->reseller['id'])) {
                return ApiHelper::response('forbidden');
            }
            $userList = User::where('reseller', $request->reseller['id'])->select('name', 'email', 'mobile', 'is_active', 'created_at')->get();
            return ApiHelper::response('success', [
                'message' => "Records Fetched Successfully",
                'details' => ['users'=>$userList]
            ]);
        } catch (\Exception $e) {
            return ApiHelper::response('internalservererror', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getUpiCollectData(Request $request) {
        try {
            if (!isset($request->reseller['id'])) {
                return ApiHelper::response('forbidden');
            }

            $query = User::where('reseller', $request->reseller['id']);
                if ($request->has('user_id')) {
                    $query->where('id', $request->user_id);
                }
            $users = $query->get();
            if ($users->isEmpty()) {
                return ApiHelper::response('noresult');
            }
            $resellerAndUserData = [];

            foreach ($users as $user) {
                $upiCollectData = UpiCollect::where('user_id', $user->id)->select('id','user_id','integration_id','customer_ref_id','bank_txn_id','amount','status','fee','tax','created_at','updated_at')
                                        ->when($request->has('status'), function ($query) use ($request) {
                                            $query->where('status', $request->status);
                                        })->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                                            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
                                        })->get();

                if ($upiCollectData->isEmpty()) {
                    return ApiHelper::response('noresult');
                }

                $statuses = ['success', 'pending', 'rejected'];
                $statusTotalAmounts = [];
                    foreach ($statuses as $status) { 
                        $totalAmount = $upiCollectData->where('status', $status)->sum('amount');
                        $statusTotalAmounts[$status] = $totalAmount;
                    }
                $resellerAndUserData[] = [
                    'user_id'     => $user->id,
                    'name'        => $user->name,
                    'upiData'     => $upiCollectData,
                    'TotalAmount' => $statusTotalAmounts
                ];
                // $resellerAndUserData[] = $userData;
            }
            return ApiHelper::response('success', ['message' => "Records Fetched Successfully", 'details' => ['users'=> $resellerAndUserData ]]);
        } catch (\Exception $e) {
            return ApiHelper::response('internalservererror', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getOrderData(Request $request) {
        try {
            if (!isset($request->reseller['id'])) {
                return ApiHelper::response('forbidden');
            }
    
            $query = User::where('reseller', $request->reseller['id']);
            if ($request->has('user_id')) {
                $query->where('id', $request->user_id);
            }
    
            $users = $query->get();
            if ($users->isEmpty()) {
                return ApiHelper::response('noresult');
            }
            $resellerUserData = [];
            
            foreach ($users as $user) {
                $statuses = ['hold', 'processing', 'processed', 'cancelled', 'reversed', 'failed'];
                $OrderData = Order::where('user_id', $user->id)->select('id', 'user_id', 'bank_reference', 'order_ref_id', 'mode', 'narration', 'purpose', 'status', 'amount', 'fee', 'tax', 'created_at', 'updated_at')
                                ->when($request->has('status'), function ($query) use ($request) {
                                    $query->where('status', $request->status);
                                })->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                                    $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
                                })->get();

                                // if ($OrderData->isEmpty()) {
                                //     return ApiHelper::response('noresult');
                                // }
                    $statusTotalAmounts = [];
                    foreach ($statuses as $status) { 
                        $totalAmount = $OrderData->where('status', $status)->sum('amount');
                        $statusTotalAmounts[$status] = $totalAmount;
                    }
                $userData = [
                    'user_id'     => $user->id,
                    'name'        => $user->name,
                    'orderData'   => $OrderData,
                    'TotalAmount' => $statusTotalAmounts
                ];
    
                $resellerUserData[] = $userData;
            }
            return ApiHelper::response('success', ['message' => "Records Fetched Successfully", 'details' => ['users'=> $resellerUserData ]]);
        } catch (\Exception $e) {
            return ApiHelper::response('internalservererror', [
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getPayinPayoutAmount(Request $request) {
        try {
            if (!isset($request->reseller['id'])) {
                return ApiHelper::response('forbidden');
            }
        
            $query = User::where('reseller', $request->reseller['id']);
            if ($request->has('user_id')) {
                $query->where('id', $request->user_id);
            }
            $users = $query->get();
            if ($users->isEmpty()) {
                return ApiHelper::response('noresult');
            }
            $pipeData = [];
        
            $payinStatuses = ['success', 'pending', 'rejected'];
            $payoutStatuses = ['hold', 'processing', 'cancelled', 'reversed', 'processed', 'failed'];
        
            foreach ($users as $user) {
                $upiCollectAmounts = [];
                $payoutAmounts = [];
        
                foreach ($payinStatuses as $status) {
                    $upiCollectAmounts[$status] = UpiCollect::where('user_id', $user->id)
                        ->when($request->has('status'), function ($query) use ($request) {
                            $query->where('status', $request->status);
                        })->when($request->has('startDate') && $request->has('endDate'), function ($query) use ($request) {
                            return $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
                        })->where('status', $status)->sum('amount');
                }
        
                foreach ($payoutStatuses as $status) {
                    $payoutAmounts[$status] = Order::where('user_id', $user->id)
                        ->when($request->has('status'), function ($query) use ($request) {
                            $query->where('status', $request->status);
                        })->when($request->has('startDate') && $request->has('endDate'), function ($query) use ($request) {
                            return $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
                        })->where('status', $status)->sum('amount');
                }
        
                $pipeData[] = [
                    'user_id' => $user->id,
                    'name'    => $user->name,
                    'payin'   => $upiCollectAmounts,
                    'payout'  => $payoutAmounts,
                ];
            }
            return ApiHelper::response('success', ['message' => "Records Fetched Successfully", 'details' => ['users'=> $pipeData ]]);
        } catch (\Exception $e) {
            return ApiHelper::response('internalservererror', [
                'message' => $e->getMessage()
            ]);
        }
    }

public function assignCommission(Request $request) {
    try {
        if (!isset($request->reseller['id'])) {
            return ApiHelper::response('forbidden');
        }
        
        $resellerIds = DB::table('reseller_commission')
            ->select('user_id', 'reseller_id', 'payin_rate', 'payout_rate', 'created_at')
            ->where('reseller_id', $request->reseller['id']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $resellerIds->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        $resellerIds = $resellerIds->get();

        $responseData = [];
        $processedUsers = [];

        foreach ($resellerIds as $resellerId) {
            $users = User::where('reseller', $resellerId->reseller_id)
                ->when($request->filled('user_id'), function ($query) use ($request) {
                    $query->where('id', $request->user_id);
                })
                ->get();
                if ($users->isEmpty()) {
                    return ApiHelper::response('noresult');
                }
            foreach ($users as $user) {
                if (!in_array($user->id, $processedUsers)) {
                    $payinTotalAmount = UpiCollect::where('user_id', $user->id)->sum('amount');
                    $PayinRate = $resellerId->payin_rate;
                    $payinFinalAmount = ($payinTotalAmount * $PayinRate) / 100;

                    $payoutTotalAmount = Order::where('user_id', $user->id)->sum('amount');
                    $PayoutRate = $resellerId->payout_rate;
                    $payoutFinalAmount = ($payoutTotalAmount * $PayoutRate) / 100;

                    $responseData[] = [
                        // 'user'               => $user,
                        // 'payin_rate'        => $resellerId->payin_rate,
                        // 'payout_rate'       => $resellerId->payout_rate,
                        'Payin totalAmount'  => $payinTotalAmount,
                        'Payin finalAmount'  => $payinFinalAmount,
                        'Payout totalAmount' => $payoutTotalAmount,
                        'Payout finalAmount' => $payoutFinalAmount,
                    ];

                    $processedUsers[] = $user->id;
                }
            }
        }
        return ApiHelper::response('success', ['message' => "Records Fetched Successfully", 'details' => $responseData ]);

    } catch (\Exception $e) {
        return ApiHelper::response('internalservererror', [
            'message' => $e->getMessage()
        ]);
    }
}


}