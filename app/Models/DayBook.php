<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayBook extends Model
{
    use HasFactory;

    protected $table = 'day_books';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'primary_opening_balance', 'primary_closing_balance', 'payout_opening_balance', 'payout_closing_balance', 'van_in', 'van_out', 'upi_in', 'upi_collect_in', 'order_processed_count', 'order_processed_amount', 'total_tax', 'total_fee', 'payout_RTGS', 'payout_NEFT', 'payout_IMPS', 'order_failed_count', 'order_failed_amount', 'order_processing_count','order_processing_amount'
    ];


    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
