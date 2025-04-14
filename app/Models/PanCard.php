<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PanCard extends Model
{
    protected $table = 'pan_agents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'mobile', 'email', 'first_name', 'middle_name', 'last_name', 'dob', 'gender', 'address', 'pan', 'aadhaar', 'client_ref_id', 'psa_id', 'pin', 'state', 'district', 'created_at', 'updated_at', 'status'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function states()
    {
        return $this->hasOne(State::class, 'id', 'state');
    }

    public function district()
    {
        return $this->hasOne(District::class, 'id', 'district');
    }

    

}