<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class MApiLog extends Eloquent
{
    protected $connection = 'mongodb';
    protected $table = 'api_logs';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'url', 'txnid', 'modal', 'method', 'header', 'request',
        'encrypted_request', 'response', 'encrypted_response', 'call_back_response', 'resp_type','resp_code',
        'resp_message', 'event', 'is_reversed', 'created_at', 'updated_at'
    ];

    public static function insertLog($user_id, $url, $txnId, $modal, $reqType, $decryptedRequest, $encryptedRequest)
    {
        $insertedId = self::create([
            'user_id' => $user_id,
            'txnid' => $txnId,
            'url' => $url,
            'modal' => $modal,
            'method' => $reqType,
            'header' => '',
            'request' => $decryptedRequest,
            'encrypted_request' => $encryptedRequest,
            'response' => '',
            'encrypted_response' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $insertedId->_id;
    }

    public static function updateLog($id, $data = [])
    {
    
        if (isset($id) && !empty($id)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            self::where('_id', $id)
            ->update($data);
            return true;
        }
        return false;
    }

}