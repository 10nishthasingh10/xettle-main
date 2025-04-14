<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\SendEmailOtp;
use Mail;
class SendEmailOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $send_mail;
    protected $otp;
    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($send_mail,$otp,$user)
    {
        $this->send_mail = $send_mail;
        $this->otp = $otp;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new SendEmailOtp($this->otp,$this->user);
        try {
            Mail::to($this->send_mail)->send($email);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
