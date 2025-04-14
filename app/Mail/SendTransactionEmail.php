<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Mail\MailForm;
use Illuminate\Support\Facades\Mail;

class SendTransactionEmail extends Mailable
{
    use Queueable, SerializesModels;
    protected $data;
    protected $upiCallbacks;
    protected $service;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data,$upiCallbacks,$service)
    {
       
        $this->data = $data;
        $this->upiCallbacks = $upiCallbacks;
        $this->service = $service;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = '';
        switch($this->service)
        {
            case 'upiCallbacks':
                $subject = 'UPI Transaction Details '.date('Y-m-d');
                $template = 'emails.email';
                break;
            case 'failedOrder':
                $subject = 'Order Status Failed';
                $template = 'emails.orderFailed';
                break;
            case 'processedOrder':
                $subject = 'Order Status Success';
                $template = 'emails.orderSuccess';
                break;

            case 'upiTransaction':
                $subject = 'Upi Transaction';
                $template = 'emails.upiTransaction';
                break;
            case 'cashfreeBalance':
                $subject = 'Account Balance';
                $template = 'emails.cashfreeBalance';
                break;
            case 'vanCredit':
                $subject = 'Your XETTLE Account Credited';
                $template = 'emails.van_credit_mail';
                break;
            case 'dailyTransaction':
                $subject = 'Daily Transaction Details '.date('d-m-Y');
                $template = 'emails.dailyTransaction';
                break;
            case 'sendMessage':
                $subject = $this->data['message_subject'];
                $template = 'emails.sendMessage';
                break;
            case 'sendAEPSCreditTxn':
                $subject = 'Your XETTLE Account Credited';
                $template = 'emails.sendAEPSCreditTxn';
                break;
            case 'sendMATMCreditTxn':
                $subject = 'Your XETTLE Account Credited';
                $template = 'emails.sendMATMCreditTxn';
                break;
            case 'AEPSKycUpdate':
                $subject = 'Your XETTLE AEPS KYC Update';
                $template = 'emails.sendAEPSKycUpdate';
                break;
            case 'AEPSKycAttachment':
                $merchantCode = isset($this->data->merchant_code) ? $this->data->merchant_code : "";
                $merchantCode = "For $merchantCode";
                $subject = "XETTLE AEPS TID Generation $merchantCode";
                $template = 'emails.kycAttachment';
                break;
            case 'userStatusUpdate':
                $subject = "Xettle - {$this->data->name}'s Account Status";
                $template = 'emails.accountStatusUpdate';
                break;
            case 'sdkAppCred':
                $subject = "Xettle - AEPS SDK Credential";
                $template = 'emails.sdkAppCredentials';
                break;
            case 'matmSdkAppCred':
                $subject = "Xettle - MATM SDK Credential";
                $template = 'emails.matmSdkAppCredentials';
                break;
            case 'apiKeyCredentials':
                $subject = "Xettle - {$this->data->serviceName} API Credential";
                $template = 'emails.api_credentials';
                break;
            case 'signup_welcome_email':
                $subject = "Welcome to XETTLE";
                $template = 'emails.signup_welcome';
                break;
        }
        // $data = $this->data;
        // $upiCallbacks = $this->upiCallbacks;
        //$subject = $this->subject;
        //print_r($this->data);
        // foreach ($this->data['data'] as $key => $value) {
        //     // code...
        //     print_r($value);
        // }
        // exit;

         $mail = $this->subject($subject)
            ->view($template,['data'=>$this->data,'upiCallbacks'=>$this->upiCallbacks]);

        if (!empty($this->data->attachment) && is_array($this->data->attachment)) {
            foreach ($this->data->attachment as $file){
                $mail->attach($file);
            }
        }
        if (!empty($this->data->cc) && is_array($this->data->cc)) {
            $mail->cc($this->data->cc);
        }
        return  $mail;
    }
}
