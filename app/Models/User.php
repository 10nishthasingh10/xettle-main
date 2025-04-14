<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Permissions\HasPermissionsTrait;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_number',
        'mobile',
        'is_profile_updated',
        'is_active',
        'signup_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function handle(Registered $event)
    {
        if ($event->user instanceof MustVerifyEmail && !$event->user->hasVerifiedEmail()) {
            $event->user->sendEmailVerificationNotification();
        }
    }



    public function businessName()
    {
        return $this->hasOne(BusinessInfo::class, 'user_id', 'id')->select('user_id', 'business_name', 'business_type', 'name', 'mobile', 'email', 'is_kyc_updated', 'pan_number');
    }


    public function serviceName()
    {
        return $this->hasMany(UserService::class, 'user_id', 'id')->select('user_id', 'service_id', 'web_value', 'api_value', 'is_api_enable', 'is_web_enable', 'is_active', 'transaction_amount');
    }

    // public function integrationName()
    // {
    //     return $this->hasMany(Integration::class, 'id')->select('id', 'integration_id', 'name', 'slug');
    // }

    public function integrationName()
{
    return $this->hasMany(Integration::class, 'integration_id')->select('name', 'slug');
}


    public function userBankInfo()
    {
        return $this->hasMany(UserBankInfo::class, 'user_id', 'id');
    }

    public function aepsAgents()
    {
        return $this->hasMany(Agent::class, 'user_id', 'id');
    }

    public function rechargeBack()
    {
        return $this->hasMany(RechargeBack::class, 'user_id', 'id')->select('user_id', 'txn_id', 'integration_id', 'status', 'created_at');
    }

    public function rechargeDth()
    {
        return $this->hasMany(DthRecharge::class, 'user_id', 'id')->select('user_id', 'customer_ref_id', 'amount', 'bank_txn_id', 'total_amount', 'original_order_id', 'status', 'created_at');
    }

    public function rechargeLic()
    {
        return $this->belongsTo(LicRecharge::class, 'user_id', 'id')->select('user_id', 'customer_ref_id', 'amount', 'bank_txn_id', 'total_amount', 'original_order_id', 'status', 'created_at');
    }

    public function rechargeElectricity()
    {
        return $this->belongsTo(ElectricityRecharge::class, 'user_id', 'id')->select('user_id', 'customer_ref_id', 'amount', 'bank_txn_id', 'total_amount', 'original_order_id', 'status', 'created_at');
    }

    public function rechargePostPaid()
    {
        return $this->belongsTo(PostPaidRecharge::class, 'user_id', 'id')->select('user_id', 'customer_ref_id', 'amount', 'bank_txn_id', 'total_amount', 'original_order_id', 'status', 'created_at');
    }

    public function Activitylogs()
    {
        return $this->belongsTo(ActivityLog::class, 'user_id', 'id')->select('type', 'url', 'method', 'ip', 'agent', 'user_id', 'message', 'created_at');
    }


    public function rechargeCreditcard()
    {
        return $this->belongsTo(CreditcardRecharge::class, 'user_id', 'id')->select('user_id', 'customer_ref_id', 'amount', 'bank_txn_id', 'total_amount', 'original_order_id', 'status', 'created_at');
    }

    public function rechargeData()
    {
        return $this->belongsTo(RechargeData::class, 'user_id', 'id')->select('user_id', 'customer_ref_id', 'amount', 'bank_txn_id', 'total_amount', 'original_order_id', 'status', 'created_at');
    }
}
