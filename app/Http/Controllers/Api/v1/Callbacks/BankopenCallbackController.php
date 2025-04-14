<?php

namespace App\Http\Controllers\Api\v1\Callbacks;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MApiLog;
use App\Services\OpenBank\OBApiService;
use Illuminate\Support\Facades\Storage;

class BankopenCallbackController extends Controller
{
    public function callback(Request $post)
    {
        $json = json_encode($post->all());
        Storage::put('public/bankOpenCallback.text', $json);

        $data = $post->all();
        $headerData = $post->header();

        MApiLog::insert([
            'modal' => 'bankopen_van',
            'txnid' => isset($data['bank_ref_id']) ? $data['bank_ref_id'] : "",
            'method' => 'callback',
            'header' => json_encode($headerData),
            'request' => '',
            'call_back_response' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $eventSource = isset($data['event_source']) ? $data['event_source'] : '';

        switch ($eventSource) {
            case 'virtual_account_payment':
                $callbackHandler = (new OBApiService())->vanCallbackHandler($data);
                return response()->json($callbackHandler);
                break;

            default:
                $res['status'] = 'FAILURE';
                $res['message'] = 'Unexpected response received';
                $res['time'] = date('Y-m-d H:i:s');

                return response()->json($res);
                break;
        }
    }
}
