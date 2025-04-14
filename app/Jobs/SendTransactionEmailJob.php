<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\SendTransactionEmail;
use Illuminate\Support\Facades\Storage;
use Mail;

class SendTransactionEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $data;
    protected $upiCallbacks;
    protected $service;
    protected $userName;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$service)
    {
        
        $this->email = isset($data->email)?$data->email:$data['email'];
        $this->userName = isset($data->name)?$data->name:'';
        $this->data = $data;
        $this->upiCallbacks = isset($data->upiCallbacks)?$data->upiCallbacks:'';
        $this->service = $service;

        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $emailData = new SendTransactionEmail($this->data,$this->upiCallbacks,$this->service);
        $mail = Mail::to($this->email);
        $mail->send($emailData);

    }
}
