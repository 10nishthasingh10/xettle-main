<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BulkPayoutDetail;
use Illuminate\Support\Facades\DB;

class BulkPayout extends Model
{
    /**
     * Table Name variable
     *
     * @var string
     */
    protected $table = 'bulk_payouts';
    /**
     * fillable variable
     *
     * @var array
     */
    protected $fillable = ['batch_id', 'user_id', 'status', 'is_active'];
    /**
     * hidden variable
     *
     * @var array
     */
    protected $hidden = ['updated_at'];
    protected $with = ['User'];
    /**
     * Undocumented function
     *
     * @param string $type
     * @param string $keys
     * @param string $where
     * @param string $order_by
     * @param integer $limit
     * @return void
     */
    public static function listing($type = 'array', $keys = '*', $where = '', $order_by = 'bulk_payouts.id-desc', $limit = 10)
    {
        $table_name = self::select($keys, 'bulk_payouts.status as status', 'bulk_payouts.status as statusDetails');
        if ($where) {
            $table_name->whereRaw($where);
        }
        if (!empty($order_by)) {
            $order_by = explode('-', $order_by);
            $table_name->orderBy($order_by[0], $order_by[1]);
        }
        if ($type === 'array') {
            $list = $table_name->get();
            return json_decode(json_encode($list), true);
        } else if ($type === 'obj') {
            return $table_name->limit($limit)->get();
        } else if ($type === 'single') {
            return $table_name->get()->first();
        } else {
            return $table_name->limit($limit)->get();
        }
    }

    public  function listings($type = 'array', $keys = '*', $where = '', $order_by = 'bulk_payouts.id-desc', $limit = 10)
    {
        $table_name = self::select($keys, 'bulk_payouts.status as status', 'bulk_payouts.status as statusDetails');
        if ($where) {
            $table_name->whereRaw($where);
        }
        if (!empty($order_by)) {
            $order_by = explode('-', $order_by);
            $table_name->orderBy($order_by[0], $order_by[1]);
        }
        if ($type === 'array') {
            $list = $table_name->get();
            return $list;
        } else if ($type === 'obj') {
            return $table_name->limit($limit)->get();
        } else if ($type === 'single') {
            return $table_name->get()->first();
        } else {
            return $table_name->limit($limit)->get();
        }
    }
    /**
     * Insert Record function
     *
     * @param [type] $data
     * @return void
     */
    public static function insert_data($data)
    {
        if (!empty($data)) {
            return self::insertGetId($data);
        } else {
            return false;
        }
    }
    /**
     * Update Record function
     *
     * @param [type] $id
     * @param [type] $data
     * @return void
     */
    public static function update_data($id, $data)
    {
        $isUpdated = false;
        if (!empty($data)) {
            if (isset($data['_method'])) {
                unset($data['_method']);
            }
            $table_name = self::where('id', '=', $id);
            $isUpdated = $table_name->update($data);
        }
        return (bool)$isUpdated;
    }

    public static function updateStatusByBatchBK($batchId, $data)
    {
       //$response =  DB::table('bulk_payout_details')->select(DB::raw('count(id) as count, sum(payout_amount) as amount'))->groupBy('status')->get();
        $data['success_count'] = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'success')->count();
        $data['success_amount'] = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'success')->sum('payout_amount');
        $data['hold_count']  = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'hold')->count();
        $data['hold_amount']  = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'hold')->sum('payout_amount');
        $data['failed_count']  = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'failed')->count();
        $data['failed_amount']  = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'failed')->sum('payout_amount');
        $data['cancelled_count']  = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'cancelled')->count();
        $data['cancelled_amount']  = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'cancelled')->sum('payout_amount');
        $data['pending_count']  = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'pending')->count();
        $data['pending_amount']  = BulkPayoutDetail::where('batch_id', '=', $batchId)->where('status', '=', 'pending')->sum('payout_amount');
        $isUpdated = false;
        if (!empty($data)) {
            if (isset($data['_method'])) {
                unset($data['_method']);
            }
            $table_name = self::where('batch_id', '=', $batchId);
            $isUpdated = $table_name->update($data);
        }
        return (bool)$isUpdated;
    }

    public static function updateStatusByBatch($batchId, $data)
    {
        $response =  DB::table('bulk_payout_details')
            ->select(DB::raw('count(id) as count, sum(payout_amount) as amount'), 'status')
            ->where('batch_id', '=', $batchId)
            ->groupBy('status')
            ->get();
            $updateData['success_count'] = 0;
            $updateData['success_amount'] = 0;
            $updateData['hold_count'] = 0;
            $updateData['hold_amount'] = 0;
            $updateData['failed_count'] = 0;
            $updateData['failed_amount'] = 0;
            $updateData['cancelled_count'] = 0;
            $updateData['cancelled_amount'] = 0;
            $updateData['pending_count'] = 0;
            $updateData['pending_amount'] = 0;
            $updateData['status'] = 'processed';
        foreach ($response as $responses) {
            if ($responses->status == 'success') {
                $updateData['success_count'] = $responses->count;
                $updateData['success_amount'] = $responses->amount;
            }
            if ($responses->status == 'hold') {
                $updateData['hold_count'] = $responses->count;
                $updateData['hold_amount'] = $responses->amount;
            }
            if ($responses->status == 'failed') {
                $updateData['failed_count'] = $responses->count;
                $updateData['failed_amount'] = $responses->amount;
            }
            if ($responses->status == 'cancelled') {
                $updateData['cancelled_count'] = $responses->count;
                $updateData['cancelled_amount'] = $responses->amount;
            }
            if ($responses->status == 'pending') {
                $updateData['pending_count'] = $responses->count;
                $updateData['pending_amount'] = $responses->amount;
            }
        }
        if (isset($updateData) && count($updateData)) {
            DB::table('bulk_payouts')
            ->where('batch_id', '=', $batchId)
            ->update($updateData);
        }
        return true;
    }

    public static function updateStatusByBatchCancelOrder($batchId, $data)
    {
        $response =  DB::table('bulk_payout_details')
            ->select(DB::raw('count(id) as count, sum(payout_amount) as amount'), 'status')
            ->where('batch_id', '=', $batchId)
            ->groupBy('status')
            ->get();
            $updateData['success_count'] = 0;
            $updateData['success_amount'] = 0;
            $updateData['hold_count'] = 0;
            $updateData['hold_amount'] = 0;
            $updateData['failed_count'] = 0;
            $updateData['failed_amount'] = 0;
            $updateData['cancelled_count'] = 0;
            $updateData['cancelled_amount'] = 0;
            $updateData['pending_count'] = 0;
            $updateData['pending_amount'] = 0;
           // $updateData['status'] = 'processed';
        foreach ($response as $responses) {
            if ($responses->status == 'success') {
                $updateData['success_count'] = $responses->count;
                $updateData['success_amount'] = $responses->amount;
            }
            if ($responses->status == 'hold') {
                $updateData['hold_count'] = $responses->count;
                $updateData['hold_amount'] = $responses->amount;
            }
            if ($responses->status == 'failed') {
                $updateData['failed_count'] = $responses->count;
                $updateData['failed_amount'] = $responses->amount;
            }
            if ($responses->status == 'cancelled') {
                $updateData['cancelled_count'] = $responses->count;
                $updateData['cancelled_amount'] = $responses->amount;
            }
            if ($responses->status == 'pending') {
                $updateData['pending_count'] = $responses->count;
                $updateData['pending_amount'] = $responses->amount;
            }
        }
        if (isset($updateData) && count($updateData)) {
            DB::table('bulk_payouts')
            ->where('batch_id', '=', $batchId)
            ->update($updateData);
        }
        return true;
    }
    /**
     * Update Status function
     *
     * @param [type] $id
     * @param [type] $data
     * @return void
     */
    public static function updateStatus($id, $data)
    {
        $isUpdated = false;
        if (!empty($data)) {
            $table_name = self::where('id', $id);
            $isUpdated = $table_name->update($data);
        }
        return (bool)$isUpdated;
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
