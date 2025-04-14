<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class WhatsUpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $username;
    protected $msg;
    protected $mobile;
    protected $url;

    public function __construct($url,$username,$msg,$mobile)
    {
        $this->username = $username;
        $this->msg = $msg;
        $this->mobile = $mobile;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        try {
            $whatsupData = [
                'template_name' => 'xettle_alert',
                'broadcast_name'=> 'xettle_alert',
                'parameters' => [
                        [
                            "name"=> "partner_name",
                            "value"=> isset($this->username)?$this->username:''
                        ],
                        [
                            "name"=> "issue_remark",
                            "value"=> $this->msg
                        ]
                ]
            ];
            //$respoinse= json_decode(self::APICaller($this->url,$this->mobile ,json_encode($whatsupData),true));
            $url = $this->url.$this->mobile;
            $ch = curl_init();
	        curl_setopt_array($ch, array(
	            CURLOPT_URL => $url,
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_POST => true,
	            CURLOPT_POSTFIELDS => json_encode($whatsupData)
	        ));
	        
	            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	               'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI4ZDdiY2FjMS0xMDVlLTRiNmQtOGIxMS0yYTVjZDM1NjRmZmMiLCJ1bmlxdWVfbmFtZSI6ImFkbWluQG1haGFncmFtLmluIiwibmFtZWlkIjoiYWRtaW5AbWFoYWdyYW0uaW4iLCJlbWFpbCI6ImFkbWluQG1haGFncmFtLmluIiwiYXV0aF90aW1lIjoiMDMvMTIvMjAyMiAxMjozNjozMCIsImRiX25hbWUiOiI1NDczIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQURNSU5JU1RSQVRPUiIsImV4cCI6MjUzNDAyMzAwODAwLCJpc3MiOiJDbGFyZV9BSSIsImF1ZCI6IkNsYXJlX0FJIn0.AoVn0G658nZGnCXhV0Bzw3Pz3V41yD5i1vcOUPMKZ-s',
	               'Content-Type: application/json', 
	        ));    

	        
	        //Ignore SSL certificate verification
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	        //get response
	        $result = curl_exec($ch);
	        curl_close($ch);

            // Apilog::create([
            //     "user_id" => 1,
            //     "integration_id" => 1,
            //     "product_id" => 1,
            //     "url" => self::WHATSUP_URL.$this->mobile,
            //     "txnid" => 1,
            //     "modal" => 'login_otp_whatsup',
            //     "method" => 'send_whatsup_otp',
            //     "header" => '',
            //     "request" => json_encode($whatsupData),
            //     "response" => json_encode($result),
            // ]);
        } catch (\Throwable $th) {
            //throw $th;
            print_r($th->getMessage());
        }
    }
}