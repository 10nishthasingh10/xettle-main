<?php

namespace App\Http\Controllers\Api\v1\Callbacks;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Http\Controllers\Clients\Api\v1\AEPSController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AEPSCallbackController extends Controller
{
    public function callback(Request $post, $api)
    {
        $res = [];
        $status = 0;
        switch ($api) {
            case 'paytm':
                $data = $post->all();
                if (isset($data['statuscode'])) {
                    $exists = DB::table('aeps_callbacks')
                                ->where(['client_ref_Id'=> $data['clientrefid'],
                                    'status'=> $data['statuscode']
                                ])->count();
                    if ($exists == 0) {
                        $type = $data['data']['transactiontype'];
                        $aepsTransactions = DB::table('aeps_transactions')
                            ->where('client_ref_id', $data['clientrefid'])
                            ->where('is_trn_credited', '0')
                            ->where('status', 'pending')
                            ->select('user_id', 'status', 'merchant_code', 'route_type',
                            'transaction_type')
                            ->first();
                        if (isset($aepsTransactions) && $aepsTransactions->status == 'pending') {
                            if ($data['statuscode'] == '000') {
                                DB::table('aeps_transactions')
                                    ->where('client_ref_id', $data['clientrefid'])
                                    ->update(['status' => 'success', 'rrn' => $data['data']['rrn']]);
                            } elseif ($data['statuscode'] == '002') {
                                $rrn = isset($data['data']['rrn']) ? $data['data']['rrn'] : "";
                                DB::table('aeps_transactions')
                                    ->where('client_ref_id', $data['clientrefid'])
                                    ->update(['status' => 'pending', 'rrn' => $rrn]);
                            } elseif ($data['statuscode'] == '001' || $data['statuscode'] == '003') {
                                $bankMessage = isset($data['data']['bankmessage']) ? $data['data']['bankmessage'] : "";
                                $rrn = isset($data['data']['rrn']) ? $data['data']['rrn'] : "";
                                DB::table('aeps_transactions')
                                    ->where('client_ref_id', $data['clientrefid'])
                                    ->update(['status' => 'failed', 'rrn' => $rrn, 'failed_message' => $bankMessage]);
                        }

                            DB::table('aeps_callbacks')
                                ->insert(['client_ref_Id'=> $data['clientrefid'], 'is_credit'=> '0', 'type'=> $type,
                                'status'=> $data['statuscode'], 'response' => json_encode($data)
                                ]);
                                AEPSController::sendAEPSCallabck($post, $aepsTransactions->user_id);
                        } else {
                            DB::table('aeps_callbacks')
                                ->insert(['client_ref_Id'=> $data['clientrefid'], 'is_credit'=> '0', 'type'=> $type,
                                'status'=> $data['statuscode'],  'response' => json_encode($data)
                                ]);
                            $bankMessage = isset($data['data']['bankmessage']) ? $data['data']['bankmessage'] : "";
                            DB::table('aeps_transactions')
                                ->where('client_ref_id', $data['clientrefid'])
                                ->update(['status' => 'failed', 'failed_message' => $bankMessage]);
                        }
                        $status = 1;
                    } else {
                        $status = 2;
                    }
                }
            break;

            default:
                Storage::put('aepsCallbackDefault' . time() . '.txt', print_r($post->all(), true));
                break;
        }
        if($status == 1) {
            $res = ['status' => 200,'message' => 'Callback data accepected.'];
        } elseif($status == 2) {
            $res = ['status' => 202,'message' => 'Callback data already exits.'];
        } else {
            $res = ['status' => 201,'message' => 'Callback data is invalid.'];
        }

        return json_encode($res);
    }


}