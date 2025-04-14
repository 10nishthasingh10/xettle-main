<?php

namespace App\Helpers;

use App\Jobs\PrimaryFundCredit;
use Illuminate\Support\Facades\DB;

class XettleUpiHelper
{
    public function __construct() {
        $this->base_url = 'https://dashboard.xettle.net/v1/service/upi/';
    }

    function writeLog($type, $req){
        $array = is_array($req) ? json_encode($req, JSON_UNESCAPED_UNICODE) : $req;
        $logData = [
            'dir' => 'upi/' . $req['function'],
            'type' => $type,
            'data' => $array,
        ];

        Log::channel('upi')->info('Log message', $logData);
    }

    private function hit($reqData) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL =>  $this->base_url."dynamic/qr",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $reqData['method'],
            CURLOPT_POSTFIELDS => $reqData['parameter'],
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic U0FGRUVfNWJjMWU4Zjg5ZWQ3YTJkMzI4NTQwMjg5ODk3NDkyMTQ6NDI2MDU5ZjBjMzdiYjJlNDdlNmVhYmE4YTIxNTg1YTYyODU0MDI4OTg5NzU3OTkx'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;
    }
}