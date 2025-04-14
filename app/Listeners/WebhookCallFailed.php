<?php

namespace App\Listeners;

use App\Helpers\WebhookHelper;
use \Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class WebhookCallFailed
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WebhookCallFailedEvent  $event
     * @return void
     */
    public function handle(WebhookCallFailedEvent $event)
    {
        WebhookHelper::storeLog2Db(json_encode($event));

        // $fileName = 'public/reversedStatus'.'.txt';
        // Storage::disk('local')->append($fileName, 'start 2 => failed');
        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => 'https://mahagarm-b42c.restdb.io/rest/event-listeners',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POSTFIELDS => json_encode($event),
        //     CURLOPT_HTTPHEADER => array(
        //             'x-api-key: 600951b21346a1524ff12cb6',
        //             'Content-Type: application/json',
        //             'Accept: application/json'
        //         ),
        //     )
        // );
        // $response = curl_exec($curl);
        // curl_close($curl);
    }
}
