<?php

namespace App\Models;

use App\Helpers\CommonHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Remitter extends Model
{

    protected $table = 'dmt_remitters';
    public static $tableName = 'dmt_remitters';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'mobile', 'first_name', 'last_name', 'pin', 'is_active'];

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
                    'first_name' => @$orderArray['firstName'],
                    'last_name' => @$orderArray['lastName'],
                    'pin' => @$orderArray['pinCode'],
                    'mobile' => @$orderArray['mobile'],
                    'outlet_id' => @$orderArray['outletId'],
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
