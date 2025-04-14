<?php

namespace App\Http\Controllers\Api\v1\Callbacks;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Http\Controllers\Clients\Api\v1\AEPSController;
use App\Http\Controllers\Clients\Api\v1\RechargeController;
use App\Models\Recharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RechargeCallbackController extends Controller
{
    public function callback(Request $post, $api)
    {
        $res = [];
        $status = 0;
        switch ($api) {
            case 'recharge':
                $data = $post->all();
                //Storage::put('rechargeCallbackDefault' . time() . '.txt', print_r($post->all(), true));
                if (isset($data['statuscode'])) {
 
                    $exists = DB::table('recharge_callbacks')
                                    ->where(['client_ref_Id'=> $data['clientrefid'],
                                        'status'=> $data['statuscode']
                                    ])->count();

                    if ($exists == 0) {
                        $aepsTransactions = DB::table('recharges')
                            ->where('order_ref_id', $data['clientrefid'])
                            ->where('status', 'processing')
                            ->select('user_id', 'status', 'merchant_code')
                            ->first();
                            if (isset($aepsTransactions) && $aepsTransactions->status == 'processing') {
                                if ($data['statuscode'] == '000') {

                                    DB::table('recharges')
                                        ->where('order_ref_id', $data['clientrefid'])
                                        ->update(['status' => 'processed','stan_no' => $data['txnid'], 'bank_reference' => $data['operatorid']]);
                                } elseif ($data['statuscode'] == '002') {

                                    $rrn = isset($data['operatorid']) ? $data['operatorid'] : "";
                                    DB::table('recharges')
                                        ->where('order_ref_id', $data['clientrefid'])
                                        ->update(['status' => 'processing', 'stan_no' => $data['txnid'],  'bank_reference' => $rrn]);
                                } elseif ($data['statuscode'] == '001' || $data['statuscode'] == '015') {

                                    $bankMessage = isset($data['message']) ? $data['message'] : "";
                                    $rrn = isset($data['operatorid']) ? $data['operatorid'] : "";

                                    DB::table('recharges')
                                    ->where('order_ref_id', $data['clientrefid'])
                                    ->update([ 'stan_no' => $data['txnid'], 'bank_reference' => $data['operatorid']]);

                                    $status =  Recharge::fundRefundedAdmin($aepsTransactions->user_id, $data['clientrefid'],
                                    $bankMessage,
                                    'callback', 'failed');
                                }

                                 DB::table('recharge_callbacks')
                                    ->insert(['client_ref_Id'=> $data['clientrefid'], 'is_credit'=> '0',
                                    'status'=> $data['statuscode'], 'response' => json_encode($data)
                                    ]);
                                RechargeController::sendCallabck($post, $aepsTransactions->user_id);
                            } else {


                                $rechargeTransactions = DB::table('recharges')
                                    ->where('order_ref_id', $data['clientrefid'])
                                    ->where('status', 'processed')
                                    ->select('user_id', 'status', 'merchant_code')
                                    ->first();

                                    if (isset($rechargeTransactions->user_id) &&  !empty($rechargeTransactions->user_id) && $data['statuscode'] == '015') {

                                        $bankMessage = isset($data['message']) ? $data['message'] : "";
                                        $rrn = isset($data['operatorid']) ? $data['operatorid'] : "";
                                        $status =  Recharge::fundRefundedAdmin($rechargeTransactions->user_id,
                                        $data['clientrefid'], '',
                                        'recharge_amount_reversed', 'reversed');
                                        RechargeController::sendCallabck($post, $rechargeTransactions->user_id);
                                    }

                                DB::table('recharge_callbacks')
                                ->insert(['client_ref_Id'=> $data['clientrefid'], 'is_credit'=> '0',
                                'status'=> $data['statuscode'], 'response' => json_encode($data)
                                ]);
                            }
                        $status = 1;
                    } else {
                        $status = 2;
                    }
                }
            break;

            default:
                Storage::put('rechargeCallbackDefault' . time() . '.txt', print_r($post->all(), true));
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