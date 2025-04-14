<?php

namespace App\Http\Controllers;

use App\Helpers\TransactionHelper;
use Illuminate\Http\Request;
use App\Models\BulkPayout;
use App\Models\User;
use App\Models\GlobalConfig;
use App\Models\BulkPayoutDetail;
use App\Models\Order;
use App\Models\OTP;
use DataTables;
use Auth;
use ExportExcelHelper;
use Yajra\DataTables\Html\Builder;
use App\Notifications\SMSNotifications;
use Validations\OtpValidation;
use Validations\BulkPayoutValidation;
use App\Jobs\SendEmailOtpJob;
use CommonHelper;
// use Transaction as TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BulkPayoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Builder $builder)
    {

        $data['page_title'] =  "Bulk Payout Listing";
        $data['site_title'] =  "Bulk Payout";
        $data['view']       = USER . '/' . PAYOUT . ".bulkpayout.list";

        $id = 0;
        return view($data['view'], compact('id'))->with($data);
    }


    /**
     * Export Bulk Payout function
     *
     * @param [type] $id
     * @return void
     */
    public function exportBulkPayout($id)
    {
        $BulkPayout = BulkPayout::where('id', $id)->first();
        $filename = $BulkPayout->filename . '.xlsx';

        $sqlModel = \App\Models\BulkPayoutDetail::query()->select(
            'batch_id',
            'contact_first_name',
            'contact_last_name',
            'contact_email',
            'contact_phone',
            'contact_type',
            'account_type',
            'account_number',
            'account_ifsc',
            'account_vpa',
            'payout_mode',
            'payout_amount',
            'payout_reference',
            'order_ref_id',
            'bank_reference',
            'payout_purpose',
            'payout_narration',
            'message',
            'status',
            'note_1',
            'note_2',
            \DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as created_at ")
        )->where('batch_id', $BulkPayout->batch_id);
        $heading = [
            'Batch Id', 'Contact First Name', 'Contact Last Name', 'Contact Email', 'Contact Phone', 'Contact Type', 'Account Type',
            'Account Number', 'Account ifsc', 'Account vpa', 'Payout Mode', 'Payout Amount', 'Payout Reference', 'Order Reference Id', 'Bank Reference',
            'Payout Purpose', 'Payout Narration', 'Error Description', 'Status', 'Note 1', 'Note 2', 'Created'
        ];

        return (new ExportExcelHelper($heading, $sqlModel))->download($filename);
    }


    /**
     * Export Bulk Payout function
     *
     * @param [type] $id
     * @return void
     */
    public function exportBulkPayoutByDate($from, $to)
    {
        $filename = date('d-m-Y H:i:s') . '.xlsx';
        if (isset($from) && !empty($from)) {
            $from = date('Y-m-d', strtotime($from));
        } else {
            $from = date('Y-m-d');
        }
        if (isset($to) && !empty($to)) {
            $to = date('Y-m-d', strtotime($to));
        } else {
            $to = date('Y-m-d');
        }
        $userId = 1;
        if(Auth::user()->is_admin == 1) {
            $userId = 1;
        } else {
            $userId = Auth::user()->id;
        }
        $sqlModel = \App\Models\BulkPayoutDetail::query()->select(
            'batch_id',
            'contact_first_name',
            'contact_last_name',
            'contact_email',
            'contact_phone',
            'contact_type',
            'account_type',
            'account_number',
            'account_ifsc',
            'account_vpa',
            'payout_mode',
            'payout_amount',
            'payout_reference',
            'order_ref_id',
            'bank_reference',
            'payout_purpose',
            'payout_narration',
            'message',
            'status',
            'note_1',
            'note_2',
            \DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as created_at ")
        )
            ->whereBetween('bulk_payout_details.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            if($userId != 1) {
                $sqlModel = $sqlModel->where('bulk_payout_details.user_id', Auth::user()->id);
            }
        $heading = [
            'Batch Id',
            'Contact First Name', 'Contact Last Name', 'Contact Email', 'Contact Phone', 'Contact Type', 'Account Type',
            'Account Number', 'Account ifsc', 'Account vpa', 'Payout Mode', 'Payout Amount', 'Payout Reference', 'Order Reference Id', 'Bank Reference',
            'Payout Purpose', 'Payout Narration', 'Error Description', 'Status', 'Note 1', 'Note 2', 'Created'
        ];

        return (new ExportExcelHelper($heading, $sqlModel))->download($filename);
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @return void
     */
    public function bulkPayoutApproveOtp($id)
    {
        $batchPkId = $id;
        $resp['status'] = false;
        $resp['userId'] = '';
        $resp['batchId'] = '';
        $resp['message'] = '';
        $bulkPayout = BulkPayout::where('id', $batchPkId)->first();
        if (isset($bulkPayout) && !empty($bulkPayout->user_id)) {
            $userDetails = User::where('id', $bulkPayout->user_id)->where('is_active', '1')->first();
            if (isset($userDetails) && !empty($userDetails)) {
                $otp = rand(100000, 999999);
                $message  = $otp . " is your verification code.";
                $number[] = $userDetails['mobile'];
                if (isset($userDetails->mobile) && !empty($userDetails->mobile)) {
                    if (!empty($userDetails->email)) {
                        dispatch(new SendEmailOtpJob($userDetails->email, $otp, $userDetails));
                    }
                    $otpresponce = SMSNotifications::sendOTP(array('otp' => $otp, 'user_id' => $bulkPayout->user_id, 'mobile' => $userDetails->mobile,'username'=>$userDetails->name, 'type' => 'bulkPayoutApprove'));
                    if ($otpresponce['status'] == "failure") {
                        $this->message    = $otpresponce->message;
                        $this->cartmessage = $otpresponce->message;
                        $this->message  = "OTP Not Sent";
                        return "false";
                    } else {
                        $this->message  =  "Enter OTP sent to " . CommonHelper::mobileMask($userDetails->mobile);
                        $this->cartmessage =  "Enter OTP sent to " . CommonHelper::mobileMask($userDetails->mobile);
                    }
                } else {
                    if (!empty($userDetails->email)) {
                        dispatch(new SendEmailOtpJob($userDetails->email, $otp, $userDetails));
                        $this->message  = "Enter OTP sent to " . CommonHelper::emailMask($userDetails->email);
                        $this->cartmessage =   "Enter OTP sent to " . CommonHelper::emailMask($userDetails->email);
                    } else {
                        $this->message  = "Email is not available";
                        $this->cartmessage =  "Email is not available";
                        return "false";
                    }
                }
                $userupdate['otp_sent_at'] = date('Y-m-d h:i:s');
                $userupdate['otp']        = $otp;
                DB::table('users')->where('id', $userDetails['id'])->update($userupdate);
                $resp['status'] = true;
                $resp['userId'] = encrypt($userDetails['id']);
                $resp['batchId'] = $bulkPayout->batch_id;
                $resp['message'] = $this->message;
            }
        }

        return $resp;
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function verifyOtpForBulkPayout(Request $request)
    {
        $id = decrypt($request->user_id);
        $validation = new OtpValidation($request);
        $validator   = $validation->verifyotp();
        $otp     = $request->otp;
        $validator->after(function ($validator) use ($request, $id, $otp) {
            if (!CommonHelper::isServiceEnabled($id, PAYOUT_SERVICE_ID)) {
                $validator->errors()->add('otp', 'Your web service is not enabled.');
            } else {

                $userDetails = User::where('id', $id)->where('otp', $otp)->first();
                if (empty($userDetails)) {
                    $validator->errors()->add('otp', 'Your OTP is not correct!');
                } else {
                    $status     = $userDetails['is_active'];
                    $globalConfig = GlobalConfig::where('slug', 'otp_expire_time')->first();
                    /*if(isset($globalConfig) && !empty($globalConfig)) {
                $from = strtotime($userDetails['otp_sent_at']);
                $to = strtotime(date('Y-m-d H:i:s'));
                $diffTwoDate = CommonHelper::twoDateDiff($from, $to, $globalConfig->attribute_2);dd($diffTwoDate,$globalConfig->attribute_2);
                if(isset($diffTwoDate)){
                    if($diffInMinutes > $globalConfig->attribute_1) {
                        $validator->errors()->add('otp','Your OTP is valid for '.$globalConfig->attribute_1.' '.$globalConfig->attribute_2);
                    }
                }
            }*/
                    if ($status != '1') {
                        $validator->errors()->add('otp', 'Your OTP is not Correct');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $this->message = $validator->errors();
        } else {
            $userDetails = User::where('id', $id)->where('otp', $otp)->first();
            if (isset($userDetails) && !empty($userDetails)) {
                $update['otp'] = null;
                $bulkPayoutStatus = DB::table('bulk_payouts')->where(['user_id' => $id, 'batch_id' => $request->batch_id])->update(['status' => 'pending']);
                $bulkPayoutDetailsStatus = DB::table('bulk_payout_details')->where(['user_id' => $id, 'batch_id' => $request->batch_id, 'status' => 'hold'])->update(['status' => 'pending']);
                $ordersStatus = DB::table('orders')->where(['user_id' => $id, 'batch_id' => $request->batch_id, 'status' => 'hold'])->update(['status' => 'queued']);
                $orderList = DB::table('orders')->where(['user_id' => $id, 'batch_id' => $request->batch_id, 'status' => 'queued'])->select('order_ref_id')->get();
                foreach ($orderList as $orderLists) {

                        dispatch(new \App\Jobs\WebOrderProcessJob($orderLists->order_ref_id, $id))->delay(rand(1, 10))->onQueue('bulk_payout_queue');
                }
                if ($bulkPayoutStatus && $ordersStatus) {
                    DB::table('users')->where('id', $id)->update($update);
                    $otpData = OTP::where(['user_id' => $id, 'otp' => $otp, 'type' => 'approve_bulk_payout'])->first();
                    BulkPayout::updateStatusByBatch($request->batch_id, array('status' => 'pending'));
                    if (isset($otpData) && !empty($otpData)) {
                        $otpData->is_validated = '1';
                        $otpData->save();
                    }
                    $this->message  = "Your bulk payout approved successfully";
                } else {

                    $this->message  = "Order already approved";
                }
                $this->status   = true;
                $this->modal    = true;
                $this->alert    = false;
                $this->redirect = true;
                return $this->populateresponse();
            } else {
                $this->status   = false;
                $this->modal    = false;
                $this->alert    = false;
                $this->message  = array('message' => 'User not found');
                return response()->json(
                    $this->populate([
                        'message'   => $this->message,
                        'status'    => false,
                        'data'      => $this->message
                    ])
                );
            }
        }

        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => false,
                'data'      => $this->message
            ])
        );
    }

    public function resendOtpOrderApprove(Request $request, $id)
    {
        $userid = decrypt($request->id);
        $userDetails = User::where('id', $userid)->first();
        $otp = rand(100000, 999999);
        $message  = $otp . " is your verification code.";
        $number[] = $userDetails['mobile'];

        if (isset($userDetails->mobile) && !empty($userDetails->mobile)) {
            if (!empty($userDetails->email)) {
                dispatch(new SendEmailOtpJob($userDetails->email, $otp, $userDetails));
            }
            $otpresponce = SMSNotifications::sendOTP(array('otp' => $otp, 'user_id' => $id, 'mobile' => $userDetails->mobile, 'username'=>$userDetails->name, 'type' => 'loginOTP'));
            if ($otpresponce['status'] == "failure") {
                $this->message    = $otpresponce->message;
                $this->cartmessage = $otpresponce->message;
                $this->message  = "OTP Not Sent";
                return "false";
            } else {
                $this->message  =  "Enter OTP sent to " . CommonHelper::mobileMask($userDetails->mobile);
                $this->cartmessage =  "Enter OTP sent to " . CommonHelper::mobileMask($userDetails->mobile);
            }
        } else {
            if (!empty($userDetails->email)) {
                dispatch(new SendEmailOtpJob($userDetails->email, $otp, $userDetails));
                $this->message  = "Enter OTP sent to " . CommonHelper::emailMask($userDetails->email);
                $this->cartmessage =   "Enter OTP sent to " . CommonHelper::emailMask($userDetails->email);
            } else {
                $this->message  = "Email is not available";
                $this->cartmessage =  "Email is not available";
            }
        }
        $userupdate['otp_sent_at'] = date('Y-m-d h:i:s');
        $userupdate['otp']        = $otp;
        DB::table('users')->where('id', $userid)->update($userupdate);
        return "true";
    }

    /**
     * Undocumented function
     *
     * @param [type] $orderRefId
     * @param [type] $orderId
     * @return void
     */
    public function batchCancel(Request $request)
    {

        $validation = new BulkPayoutValidation($request);

        $validator = $validation->batchCancel();

        $validator->after(function ($validator) use ($request) {
            $bulkPayout = BulkPayout::where(['batch_id' => $request->batchId, 'user_id' => $request->userId])->first();
            if (empty($bulkPayout)) {
                $validator->errors()->add('message', 'Batch Record not found');
            } else {
                if ($bulkPayout->status != 'hold') {
                    $validator->errors()->add('message', 'Batch is not valid for cancel');
                }
            }
        });

        if ($validator->fails()) {
            $this->message = $validator->errors();
        } else {
            $bulkPayout = BulkPayout::where(['batch_id' => $request->batchId, 'user_id' => $request->userId])->first();

            if (!empty($bulkPayout)) {

                $bulkPayoutDetails = BulkPayoutDetail::where(['batch_id' => $request->batchId, 'user_id' => $request->userId])->get();

                foreach ($bulkPayoutDetails as $bulkPayoutDetail) {

                    $Order = Order::where(['batch_id' => $bulkPayoutDetail->batch_id, 'order_ref_id' => $bulkPayoutDetail->order_ref_id])->first();

                    if (!empty($Order)) {

                        $Order->status = 'cancelled';
                        $Order->cancellation_reason = $request->remarks;
                        $Order->cancelled_at = date('Y-m-d H:i:s');

                        if ($Order->save()) {

                            BulkPayoutDetail::payStatusUpdate($Order->batch_id, 'cancelled', $Order->order_ref_id, 'Order Cancelled', '');
                        }
                    }
                }


                BulkPayout::updateStatusByBatch($request->batchId, array('status' => 'cancelled'));

                //new code
                $bulkPayout->status = 'cancelled';
                $bulkPayout->save();

                // dd($bulkPayout);
            }

            $this->message = "Batch Cancelled Successfully";
            $this->status   = true;
            $this->modal    = true;
            $this->alert    = true;
            $this->redirect = true;
            return $this->populateresponse();
        }
        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => $this::FAILED_STATUS,
                'data'      => $this->message
            ])
        );
    }
}
