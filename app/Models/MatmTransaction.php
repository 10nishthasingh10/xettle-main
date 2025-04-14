<?php

namespace App\Models;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Clients\Api\v1\DMTController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MatmTransaction extends Model
{

    protected $table = 'matm_transactions';
    public static $tableName = 'matm_transactions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'merchant_code', 'client_ref_id', 'order_ref_id', 'serial_no', 'mid', 'latitude', 'longitude', 'amount', 'tid', 'mac_address', 'status', 'route_type', 'txn_type', 'imei', 'imsi', 'bankrefno', 'udf_2', 'udf_1'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public static function create($userId, $orderArray, $orderRefId, $comm, $tds, $margin, $txnType)
    {
      
        $response['status'] = false;
        $response['message'] = 'Record Not created' ;
        DB::beginTransaction();
        try {
                $orderData = [
                    'service_id' => MATM_SERVICE_ID,
                    'user_id' => $userId,
                    'client_ref_id' => @$orderArray['clientRefId'],
                    'order_ref_id' => @$orderRefId,
                    'merchant_code' => @$orderArray['merchantCode'],
                    'latitude' => @$orderArray['latitude'],
                    'longitude' => @$orderArray['longitude'],
                    'transaction_amount' => @$orderArray['amount'],
                    'status' => 'pending',
                    'serialno' =>  @$orderArray['serialno'],
                    'reference' =>  @$orderArray['reference'], 
                    'mid' => @$orderArray['mid'],
                    'tid' => @$orderArray['tid'],
                    'mac_address' => @$orderArray['macAddress'],
                    'route_type'=> @$orderArray['routeType'],
                    'transaction_type'=> @$txnType,
                    'imei'=> @$orderArray['imei'],
                    'imsi'=> @$orderArray['imsi'],
                    'bank_ref_no'=> @$orderArray['bankrefno'],
                    'udf_1' => isset($orderArray['udf4']) ? $orderArray['udf4'] : " ",
                    'margin' => @$margin,
                    'commission' => @$comm,
                    'tds' => @$tds,
                ];

                $createTransaction = DB::table(self::$tableName)->insert($orderData);
                if ($createTransaction) {
                    DB::commit();
                    $response['status'] = true;
                    $response['message'] = 'Record created successfully.';
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Record not created.';
                }
        } catch (\Exception $e) {

            DB::rollback();
            $response['status'] = false;
            $response['message'] = 'something went wrong : '.$e->getMessage() ;
        }
        return $response;
    }


    public static function updateRecord($cond = [], $data = [])
    {
        if (self::where($cond)->update($data)){
            return true;
        }
        return false;
    }

}
