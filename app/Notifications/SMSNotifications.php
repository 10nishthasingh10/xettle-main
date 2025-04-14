<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\OTP;
use App\Models\GlobalConfig;
use App\Models\Apilog;
use App\Jobs\SendEmailOtpJob;
use App\Jobs\WhatsUpJob;
use CommonHelper;
class SMSNotifications extends Notification
{
    use Queueable;

    private const URL = '';
    private const authKey = '';
    Private const SENDER_ID = '';
    Private const ROUTE_NO = '';
    private const DLT_TE_ID = '';
    Private const TEMPLATEID = '';
    private const RESPONSE_TYPE = '';
    private const WHATSUP_URL =  '';

    public static function sendOTP($notification)
    {
        $user_id = $notification['user_id'];
        $otp = $notification['otp'];
        $mobile = $notification['mobile'];
        // $is_validated = $notification['is_validated'];
        // $queryString = '';
        switch($notification['type']) {
            case 'loginOTP':
                $whatsupMessage = "The OTP for your xettle account is $otp. Pls don't share with anyone, if you need help,";
                $message = urlencode("$otp is the OTP for Xettle App login - MPPL");
                $type = 'login';
            break;

            case 'signupOTP':
            break;

            case 'passwordChangeOTP':
            break;

            case 'mobileChangeOTP':
            break;

            case 'emailChangeOTP':
            break;
            case 'bulkPayoutApprove':
                $whatsupMessage = "The OTP for your xettle payout approval is $otp. Pls don't share with anyone, if you need help,";
                $message = urlencode("$otp is the OTP for Xettle App login - MPPL");
                $type = 'approve_bulk_payout';
                break;
            case 'forgotPasswordOTP':
                $message = urlencode("$otp is the OTP for Xettle App login - MPPL");
                $type = 'forgotPassword';
                break;
        }

        $postData = array(
            'authkey' => self::authKey,
            'mobiles' => $mobile,
            'message' => $message,
            'DLT_TE_ID' => self::DLT_TE_ID,
            'sender' => self::SENDER_ID,
            'route' => self::ROUTE_NO,
            'response' => self::RESPONSE_TYPE
        );
        
        $globalConfig = GlobalConfig::select('attribute_1')->where('slug','login_otp_whatsup')->first();
        // dd($globalConfig);
        $otpRefId = CommonHelper::getRandomString('sms_', false, '12');
        self::storeOTP($user_id, $mobile, $otp, $otpRefId, $type);
        $result = json_decode(self::APICaller(self::URL,$postData));
        // dd($result);
        if($globalConfig['attribute_1']=='1' && ($notification['type']=='loginOTP' || $notification['type']=='bulkPayoutApprove'))
        {

            dispatch(new WhatsUpJob(self::WHATSUP_URL,$notification['username'],$whatsupMessage,$mobile));
            //     $whatsupData = [
            //     'template_name' => 'xettle_alert',
            //     'broadcast_name'=> 'xettle_alert',
            //     'parameters' => [
            //             [
            //                 "name"=> "partner_name",
            //                 "value"=> isset($notification['username'])?$notification['username']:''
            //             ],
            //             [
            //                 "name"=> "issue_remark",
            //                 "value"=> $whatsupMessage
            //             ]
            //     ]
            // ];
            // $respoinse= json_decode(self::APICaller(self::WHATSUP_URL.$mobile ,json_encode($whatsupData),true));
            // Apilog::create([
            //     "user_id" => 1,
            //     "integration_id" => 1,
            //     "product_id" => 1,
            //     "url" => self::WHATSUP_URL.$mobile,
            //     "txnid" => 1,
            //     "modal" => 'login_otp_whatsup',
            //     "method" => 'send_whatsup_otp',
            //     "header" => '',
            //     "request" => json_encode($whatsupData),
            //     "response" => json_encode($respoinse),
            // ]);

        }

        if(!empty($result) && $result->type == 'success') {
            $response['status'] = 'success';
            $response['message'] = $result->message;
            $response['otpRefId'] = $otpRefId;
        } else {
            $response['message'] = isset($result->message)?$result->message:'Something went wrong.';
            $response['status'] = 'failure';
        }
        return $response;
    }

    public static function generateOTP()
    {
        $otp = mt_rand(100, 999).rand(100, 999);
        return $otp;
    }

    public static function storeOTP($user_id, $mobile, $otp, $otpRefId, $type)
    {
        $storeOTP = OTP::create([
            'user_id' => $user_id,
            'mobile' => $mobile,
            // 'otp' => encrypt($otp),
            'otp' => $otp,
            'otp_ref_id' => $otpRefId,
            'type' => $type,
            // 'is_validated' => $is_validated
        ]);

        return $storeOTP;
    }

    public static function APICaller($url,$postData,$headers='')
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
        ));
        if($headers)
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
               'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI4ZDdiY2FjMS0xMDVlLTRiNmQtOGIxMS0yYTVjZDM1NjRmZmMiLCJ1bmlxdWVfbmFtZSI6ImFkbWluQG1haGFncmFtLmluIiwibmFtZWlkIjoiYWRtaW5AbWFoYWdyYW0uaW4iLCJlbWFpbCI6ImFkbWluQG1haGFncmFtLmluIiwiYXV0aF90aW1lIjoiMDMvMTIvMjAyMiAxMjozNjozMCIsImRiX25hbWUiOiI1NDczIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQURNSU5JU1RSQVRPUiIsImV4cCI6MjUzNDAyMzAwODAwLCJpc3MiOiJDbGFyZV9BSSIsImF1ZCI6IkNsYXJlX0FJIn0.AoVn0G658nZGnCXhV0Bzw3Pz3V41yD5i1vcOUPMKZ-s',
               'Content-Type: application/json', 
        ));    
        }
        
        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //get response
        $result = curl_exec($ch);
        curl_close($ch);

        Apilog::create([
                "user_id" => 1,
                "integration_id" => 1,
                "product_id" => 1,
                "url" => $url,
                "txnid" => 1,
                "modal" => 'login_otp_sms',
                "method" => 'send_sms_otp',
                "header" => '',
                "request" => json_encode($postData),
                "response" => json_encode($result),
            ]);

        return $result;
    }
}