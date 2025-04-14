<?php

namespace App\Models;

use App\Helpers\CommonHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DMTBeneficiary extends Model
{

    protected $table = 'dmt_beneficiary';
    public static $tableName = 'dmt_beneficiary';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'mobile', 'name', 'ifsc', 'account_number',  'bank_id', 'is_active'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public static function create($userId, $orderArray)
    {
        $response['status'] = false;
        $response['message'] = 'Record Not created' ;
        DB::beginTransaction();
        try {
                $orderData = [
                    'service_id' => DMT_SERVICE_ID,
                    'user_id' => $userId,
                    'name' => @$orderArray['name'],
                    'ifsc' => @$orderArray['ifsc'],
                    'account_number' => @$orderArray['accountNumber'],
                    'bank_id' => @$orderArray['bankId'],
                    'mobile' => @$orderArray['mobile'],
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
