<?php

namespace App\Models;

use App\Helpers\CommonHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Recharge extends Model
{
    use HasFactory;

    protected $table = 'recharges';
    public static $tableName = 'recharges';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function operator()
    {
        return $this->hasOne(Operator::class, 'id', 'operator_id');
    }
    public static function create($userId, $orderArray, $taxData)
    {
        $response['status'] = false;
        $response['message'] = 'Order Not created' ;
        DB::beginTransaction();
        try {
            // Transaction Create
            $orderData = [
                'service_id' => RECHARGE_SERVICE_ID,
                'operator_id' => @$orderArray['opt'],
                'user_id' => $userId,
                'order_ref_id' => @$orderArray['agentid'],
                'amount' => @$orderArray['amount'],
                'commission' => @$taxData['fee'],
                'tax' => @$taxData['tax'],
                'status' => 'queued',
                //  'ip' => @$orderArray['ip'],
                'phone' => @$orderArray['mobile'],
                'merchant_code' => @$orderArray['merchantcode'],
            ];
            $createTransaction = DB::table('recharges')->insert($orderData);
            if ($createTransaction) {
                DB::commit();
                $response['status'] = true;
                $response['message'] = 'Order created successfully.';
            } else {
                $response['status'] = false;
                $response['message'] = 'Order not created.';
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response['status'] = false;
            $response['message'] = 'something went wrong : '.$e->getMessage() ;
        }
        return $response;
    }

        /**
     * Check Amount is Postive
     *
     * @param [type] $num
     * @return void
     */
    public static function intPositive($num)
    {
        if ($num < 0) {
            $response['status'] = false;
            $response['message'] = 'Negative integer value';
        } else {
            $response['status'] = true;
            $response['message'] = 'Positive integer value';
        }
        return $response;
    }

    public static function moveOrderToProcessingByOrderId($userId, $orderRefId, $integrationId = null)
    {
       
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {
            $txn = CommonHelper::getRandomString('TXN', false);
            DB::select("CALL debitRechargeBalanceOrder($userId, '".$orderRefId."', '".$txn."', @json)");
            $results = DB::select('select @json as json');
            $response = json_decode($results[0]->json, true);
           
            if($response['status'] == '1') {
                $resp['status'] = true;
                $resp['message'] = 'Order processing successfully.';
            } else {
                $resp['status'] = false;
                $resp['message'] = $response['message'];
            }
        } catch (\Exception $e) {
            $resp['status'] = false;
            $resp['message'] = 'Some errors : '.$e->getMessage();
        }
       
        return $resp;
    }

    public static function fundRefunded($userId, $orderRefId, $failedMessage, $statusCode)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {

            $OrderData = DB::table('recharges')
                ->select('order_ref_id', 'user_id', 'area', 'service_id')
                ->where(['status' => 'processing', 'user_id' => $userId, 'order_ref_id' => $orderRefId])
                ->whereIn('area', ['00', '11', '22'])
                ->first();

            if (isset($OrderData) && !empty($OrderData)) {
                $id = @DB::table('user_services')->where([
                    'user_id' => $userId,
                    'service_id' => RECHARGE_SERVICE_ID
                ])->first()->id;
                $txn = CommonHelper::getRandomString('TXN', false);
                DB::select("CALL rechargestatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id, $id, 'failed', '" . $txn . "', '" . $failedMessage . "', '".$statusCode."','', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);

                if ($response['status'] == '1') {
                    $resp['status'] = true;
                    $resp['message'] = 'Fund refunded successfully.';
                } else {
                    $resp['status'] = false;
                    $resp['message'] = $response['message'];
                }
            }
        } catch (\Exception $e) {
            $resp['status'] = false;
            $resp['message'] = 'Some errors : '.$e->getMessage();
        }
        return $resp;
    }

    public static function fundRefundedAdmin($userId, $orderRefId, $failedMessage, $statusCode, $status)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {
            $OrderData = DB::table('recharges')
                ->select('order_ref_id', 'user_id', 'area', 'service_id')
                ->where(['user_id' => $userId, 'order_ref_id' => $orderRefId])
                ->whereIn('status', ['failed','processed', 'processing'])
                ->whereIn('area', ['00', '11', '22'])
                ->first();

            if (isset($OrderData) && !empty($OrderData)) {
                $id = @DB::table('user_services')->where([
                    'user_id' => $userId,
                    'service_id' => RECHARGE_SERVICE_ID
                ])->first()->id;
                $txn = CommonHelper::getRandomString('TXN', false);
                DB::select("CALL rechargestatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id, $id, '".$status."', '" . $txn . "', '" . $failedMessage . "', '".$statusCode."','', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);

                if ($response['status'] == '1') {
                    $resp['status'] = true;
                    $resp['message'] = 'Fund refunded successfully.';
                } else {
                    $resp['status'] = false;
                    $resp['message'] = $response['message'];
                }
            }
        } catch (\Exception $e) {
            $resp['status'] = false;
            $resp['message'] = 'Some errors : '.$e->getMessage();
        }
        return $resp;
    }
    
    public static function updateRecord($cond = [], $data = [])
    {
        if (self::where($cond)->update($data)){
            return true;
        }
        return false;
    }


}

