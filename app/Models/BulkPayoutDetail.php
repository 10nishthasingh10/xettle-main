<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class BulkPayoutDetail extends Model
{
    /**
     * table variable
     *
     * @var string
     */

    protected $table = 'bulk_payout_details';
    /**
     * fillable variable
     *
     * @var array
     */
    protected $fillable = [
        'contact_first_name', 'contact_last_name', 'contact_email', 'contact_phone', 'message', 'batch_id', 'contact_type',
        'account_type', 'account_number', 'account_ifsc', 'account_vpa', 'payout_mode', 'payout_amount', 'payout_reference', 'payout_purpose',
        'payout_narration', 'status', 'order_ref_id', 'user_id', 'bank_reference', 'note_1', 'note_2', 'failed_from'
    ];

    /**
     * this function statsu update using batch_id and trans id
     *
     * @param [type] $batch_id
     * @param [type] $status
     * @param [type] $txt1
     * @return void
     */
    public static function payStatusUpdate($batch_id, $status, $order_ref_id, $errorDesc, $bankReference = '')
    {

        $BulkPayoutDetail = self::where(['batch_id' => $batch_id, 'order_ref_id' => $order_ref_id])->first();
        $newStatus = 'pending';
        if ($status == 'ACCEPTED' || $status == 'accepted' || $status == 'pending' || $status == 'PENDING' || $status == 'processing' || $status == 'PROCESSING') {
            $newStatus = 'pending';
        } else if ($status == 'success' || $status == 'SUCCESS' || $status == 'processed') {
            $newStatus = 'success';
        } else if ($status == 'Cancelled' || $status == 'cancelled' || $status == 'cancel' || $status == 'CANCELLED') {
            $newStatus = 'cancelled';
        } else if ($status == 'Reversed' || $status == 'reversed' || $status == 'REVERSED') {
            $newStatus = 'reversed';
        } else {
            $newStatus = 'failed';
        }
        if (isset($BulkPayoutDetail) && !empty($BulkPayoutDetail)) {
            $BulkPayoutDetail->status = $newStatus;
            if ($bankReference != '') {
                $BulkPayoutDetail->bank_reference = $bankReference;
            }
            $BulkPayoutDetail->message = $errorDesc;
            $BulkPayoutDetail->failed_from = "1";
            $BulkPayoutDetail->save();
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
}
