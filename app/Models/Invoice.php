<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'invoice_id', 'service_id', 'fee_amount', 'fee_able_amount', 'record_date', 'created_at'
    ];


    public function User()
    {
        return $this->belongsTo(User::class);
    }
}