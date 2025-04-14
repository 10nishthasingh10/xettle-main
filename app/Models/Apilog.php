<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apilog extends Model
{
    protected $fillable = [
        'user_id', 'integration_id', 'product_id', 'url', 'txnid', 'modal','encrypt_request','encrypt_response', 'method', 'header', 'request', 'response', 'call_back_response', 'resp_type', 'resp_code', 'resp_message', 'created_at', 'updated_at'
    ];
}
