<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MWebhookLog extends Model
{
    //protected $connection = 'mongodb';
    protected $table = 'webhook_logs';
    public $timestamps = false;
    protected $fillable = [
        'uuid', 'httpVerb', 'webhookUrl', 'payload', 'headers', 'meta',
        'tags', 'attempt', 'response', 'errorType', 'errorMessage','transferStats', 'created_at', 'updated_at'
    ];

    public static function insertLog($data)
    {
        $insertedId = self::create($data);
        return $insertedId->_id;
    }

    public static function updateLog($where = [], $data = [])
    {
        if (isset($id) && !empty($id)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            self::where($where)
            ->update($data);
            return true;
        }
        return false;
    }
}
