<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class UPICollect extends Model
{
    use HasFactory;
    protected $table = 'upi_collects';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'txn_note', 'amount', 'resp_code', 'description', 'payee_vpa', 'customer_ref_id', 'merchant_txn_ref_id', 'txn_id', 'original_order_id', 'bank_txn_id', 'payer_vpa', 'upi_txn_id', 'status','npci_txn_id','payer_acc_name','payer_mobile','payer_acc_no','payer_ifsc', 'code', 'txn_date', 'type'
    ];

    public function User()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function Integration()
    {
        return $this->belongsTo(\App\Models\Integration::class , 'integration_id', 'integration_id');
    }

}