<?php

namespace App\Models;

//use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Model;

class MUserApiLog extends Model
{
   /* protected $connection = 'mongodb';
    protected $table = 'user_api_logs';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'service','method', 'url', 'header', 'request', 'response', 'ip', 'created_at', 'updated_at'
    ];*/

    public static function insertLog($ip, $url, $method, $userId, $serviceName, $request, $header)
    {
        /*$insertedId = self::create([
            'ip' => $ip,
            'url' => $url,
            'header' => $header,
            'method' => $method,
            'user_id' => $userId,
            'service' => $serviceName,
            'request' => $request,
            'response' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $insertedId->_id;*/
    }

    public static function updateLog($id, $data = [])
    {
       /* if (isset($id) && !empty($id)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            self::where('_id', $id)
            ->update($data);
            return true;
        }
        return false;*/
    }

}