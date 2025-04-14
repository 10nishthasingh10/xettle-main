<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Order extends Model
{   protected $hidden = ['id'];
    // protected $with = ['bulkPayoutDetail','contact', 'User','Integration'];

    public static function listing($type='array',$keys='*',$where='',$order_by='orders.created_at-desc',$limit=10)
    {
        $table_name = self::select($keys,'orders.status as status','orders.status as expStatus','orders.created_at as created_at');
        $table_name->leftJoin('contacts','contacts.contact_id','orders.contact_id');
        $table_name->leftJoin('bulk_payout_details','bulk_payout_details.order_ref_id','orders.order_ref_id');
        if($where){
            $table_name->whereRaw($where);
        }

        if(!empty($order_by)){
            $order_by = explode('-', $order_by);
            $table_name->orderBy($order_by[0],$order_by[1]);
        }
        if($type === 'array'){
            $list = $table_name->get();
            return json_decode(json_encode($list), true );
        }else if($type === 'obj'){
            return $table_name->limit($limit)->get();
        }else if($type === 'single'){
            return $table_name->get()->first();
        }else{
            return $table_name->limit($limit)->get();
        }
	}

    public static function updateStatus($id,$data)
    {
        $isUpdated = false;
        if(!empty($data)){
            $table_name=self::where('id',$id);
            $isUpdated = $table_name->update($data);
        }
        return (bool)$isUpdated;
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }
    public function Integration()
    {
        return $this->belongsTo(Integration::class, 'integration_id', 'integration_id');
    }

    public static function listingSerching($order_ref_id = '',$batch_id = '',$mode = '',$payout_reference = '',$status = '',$from = '',$to = '',$type='array',$keys='*',$where='',$order_by='orders.id-desc',$limit=10)
    {
        $table_name = self::select($keys,'orders.status as status','orders.status as expStatus','orders.created_at as created_at');
        $table_name->leftJoin('contacts','contacts.contact_id','orders.contact_id');
        $table_name->leftJoin('bulk_payout_details','bulk_payout_details.order_ref_id','orders.order_ref_id');
        if($where){
            $table_name->whereRaw($where);
        }

        if(!empty($order_ref_id)){
            $table_name->where('bulk_payout_details.order_ref_id',$order_ref_id);
        }
        if(!empty($batch_id)){
            $table_name->where('orders.batch_id',$batch_id);
        }
        if(!empty($mode)){
            $table_name->where('bulk_payout_details.payout_mode',$mode);
        }
        if(!empty($payout_reference)){
            $table_name->where('bulk_payout_details.payout_reference',$payout_reference);
        }
        if(!empty($status)){
            $table_name->where('orders.status',$status);
        }
        if(!empty($from) && !empty($to)){
            $table_name->whereBetween('orders.created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        }
        if(!empty($order_by)){
            $order_by = explode('-', $order_by);
            $table_name->orderBy($order_by[0],$order_by[1]);
        }
        if($type === 'array'){
            $list = $table_name->get();
            return $list;
        }else if($type === 'obj'){
            return $table_name->limit($limit)->get();
        }else if($type === 'single'){
            return $table_name->get()->first();
        }else{
            return $table_name->limit($limit)->get();
        }
	}

    public function bulkPayoutDetail()
    {
        return $this->belongsTo('App\Models\BulkPayoutDetail','order_ref_id','order_ref_id');
    }

}
