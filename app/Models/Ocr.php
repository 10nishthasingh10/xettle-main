<?php

namespace App\Models;

use App\Helpers\CommonHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ocr extends Model
{

    protected $table = 'ocrs';
    public static $tableName = 'ocrs';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['client_ref_id', 'user_id', 'group_id', 'fee', 'tax', 'type', 'status', 'failed_messages', 'created_at', 'updated_at', 'product_id', 'request_id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public static function create($userId, $orderArray, $type, $taxData = [])
    {
        $response['status'] = false;
        $response['message'] = 'Order Not created' ;
        DB::beginTransaction();
        try {
                    // Transaction Create
                   $orderData = [
                        'service_id' => VALIDATE_SERVICE_ID,
                        'user_id' => $userId,
                        'client_ref_id' => @$orderArray['task_id'],
                        'document1' => @$orderArray['data']['document1'],
                        'document2' => @$orderArray['data']['document2'],
                        'order_ref_id' => @$orderArray['group_id'],
                        'type' => @$type,
                        'status' => 'queued',
                        'fee' => @$taxData['fee'],
                        'tax' => @$taxData['tax'],
                        'product_id' => @$taxData['product_id'],
                        'margin' => @$taxData['margin']
                   ];
                $createTransaction = DB::table(self::$tableName)->insert($orderData);
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

    public static function moveOrderToProcessingByOrderId($userId, $orderRefId, $fee, $tax, $trIdentifiers)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {
            // $txn = CommonHelper::getRandomString('TXN', true, 10);
            $txn = CommonHelper::getRandomString('TXN', false);
            DB::select("CALL debitOCRBalanceOrder($userId, 0, $fee, $tax, '".$orderRefId."', '".$txn."','".$trIdentifiers."', @json)");
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

    public static function fundRefunded($userId, $orderRefId, $failedMessage, $trIdentifiers)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {

            $OrderData = DB::table(self::$tableName)
                ->select('order_ref_id', 'user_services.id as id')
                ->leftJoin('user_services', 'user_services.user_id', self::$tableName.'.user_id')
                ->where([self::$tableName.'.status' => 'pending', self::$tableName.'.user_id' => $userId, 'order_ref_id' => $orderRefId])
                ->where('user_services.service_id', VALIDATE_SERVICE_ID)
                ->first();

            if (isset($OrderData) && !empty($OrderData)) {
                // $txn = CommonHelper::getRandomString('TXN', true, 10);
                $txn = CommonHelper::getRandomString('TXN', false);
                DB::select("CALL ocrStatusUpdate('" . $OrderData->order_ref_id . "', $userId, $OrderData->id, '" . 'failed' . "', '" . $txn . "', '" . $failedMessage . "', '','','" . $trIdentifiers . "',  @json)");
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
