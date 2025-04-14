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
class OrderCancelEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sendMail;
    protected $message;
    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sendMail,$message,$user)
    {
        $this->sendMail = $sendMail;
        $this->message = $message;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new OrderCancelMail($this->message,$this->user);
        Mail::to($this->sendMail)->send($email);
    }
}
