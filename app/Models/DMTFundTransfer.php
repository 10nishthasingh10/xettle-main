<?php

namespace App\Models;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Clients\Api\v1\DMTController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DMTFundTransfer extends Model
{

    protected $table = 'dmt_fund_transfers';
    public static $tableName = 'dmt_fund_transfers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'mobile','client_ref_id', 'bene_id', 'mode', 'latitude', 'longitude', 'amount', 'fee', 'tax', 'status'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function outlet()
    {
        return $this->hasOne(DMTOutlet::class, 'outlet_id', 'outlet_id');
    }
    public static function create($userId, $orderArray, $fee, $tax, $margin, $orderRefId)
    {
        $response['status'] = false;
        $response['message'] = 'Record Not created' ;
        DB::beginTransaction();
        try {
                $orderData = [
                    'service_id' => DMT_SERVICE_ID,
                    'user_id' => $userId,
                    'outlet_id' => @$orderArray['outletId'],
                    'client_ref_id' => @$orderArray['clientRefId'],
                    'merchant_code' => @$orderArray['merchantCode'],
                    'order_ref_id' => @$orderRefId,
                    'beni_id' => @$orderArray['beneficiaryId'],
                    'mode' => 'IMPS',
                    'mobile' => @$orderArray['remitterMobile'],
                    'latitude' => @$orderArray['latitude'],
                    'longitude' => @$orderArray['longitude'],
                    'amount' => @$orderArray['amount'],
                    'fee' => $fee,
                    'tax' => $tax,
                    'margin' => $margin,
                    'status' => 'queued',
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

    public static function moveOrderToProcessingByOrderId($userId, $orderRefId)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {
            $txn = CommonHelper::getRandomString('TXN', false);
            DB::select("CALL debitDMTFundTransfer($userId, '".$orderRefId."', '".$txn."', @json)");
            $results = DB::select('select @json as json');
            $response = json_decode($results[0]->json, true);
            if($response['status'] == '1') {
                $resp['status'] = true;
                $resp['message'] = 'Order processing successfully.';
            } else {
                if ($response['message'] == 'debit_balance_failed') {
                    self::moveOrderToProcessingByOrderId($userId, $orderRefId);
                }
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

            $OrderData = DB::table(self::$tableName)
                ->select('order_ref_id', 'user_id', 'service_id')
                ->where(['status' => 'processing', 'user_id' => $userId, 'order_ref_id' => $orderRefId])
                ->first();

            if (isset($OrderData) && !empty($OrderData)) {
                $id = @DB::table('user_services')->where([
                    'user_id' => $userId,
                    'service_id' => DMT_SERVICE_ID
                ])->first()->id;
                $txn = CommonHelper::getRandomString('TXN', false);
                DB::select("CALL dmtStatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id, $id, 'failed', '" . $txn . "', '" . $failedMessage . "', '".$statusCode."','', @json)");
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


    public static function cashbackCredit($userId, $orderRefId)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {

            $OrderData = DB::table(self::$tableName)
                ->select('order_ref_id', 'user_id', 'service_id')
                ->where(['status' => 'processed',
                'user_id' => $userId,
                'order_ref_id' => $orderRefId])
                ->first();

            if (isset($OrderData) && !empty($OrderData)) {

                $taxData = DMTController::getFeeAndTaxs($OrderData->user_id, 'cashback');

                $cashbackAmount = $taxData['fee'];
                $margin = $taxData['margin'];
                $txn = CommonHelper::getRandomString('TXN', false);
                DB::select("CALL dmtCashback( $OrderData->user_id, '" . $OrderData->order_ref_id . "', '" . $txn . "',  $cashbackAmount, '" . $margin . "',  @json)");
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
            $fileName = 'public/DMTFundTransaferCashback.txt';
            Storage::disk('local')->put($fileName, $e.date('H:i:s'));
        }
        return $resp;
    }
}
