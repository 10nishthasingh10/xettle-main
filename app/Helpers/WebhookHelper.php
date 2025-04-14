<?php

namespace App\Helpers;

use App\Http\Controllers\Clients\Api\v1\DMTController;
use Illuminate\Http\Request;
use CommonHelper;
use App\Http\Webhooks\UPIWebhook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WebhookHelper {

    /**
     * UPI Receive Success 
     *
     * @param object $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function UPISuccess($data, $url = '', $secret = '', $headers = '')
    {
        //dd($data);
        $arrayPayLoad['event'] = 'upi.receive.success';
        $arrayPayLoad['code'] = $data->code;
        $arrayPayLoad['message'] = 'Transaction Successful';
        $arrayPayLoad['data'] = [
            //'payeeVPA' => @$data->payee_vpa,
            'amount' => @$data->amount,
            //'txnNote' => @$data->txn_note,
            'npciTxnId' => @$data->npci_txn_id,
            'originalOrderId' => @$data->originalOrderId,
            'merchantTxnRefId' => @$data->merchantTxnRefId,
            'bankTxnId' => @$data->bank_txn_id,
            'customerRefId' => @$data->customerRefId,
            'payer_vpa' => @$data->payer_vpa,
            'payerMobile' => @$data->payer_mobile,
            'payerAccName' => @$data->payer_acc_name,
            'payerAccNo' => @$data->payer_acc_no,
            'payerIFSC' => @$data->payer_ifsc,
            'type' => @$data->type,
            'date' => @$data->txn_date,
        ];

        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
             //   ->timeoutInSeconds(5)
                ->maximumTries(5)
                ->dispatch();
                //dd($data);
                //dd($url);
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>json_encode($arrayPayLoad),
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: XSRF-TOKEN=eyJpdiI6IjI2dlhFUURmaHFHYVdLc1lHR21vRkE9PSIsInZhbHVlIjoiUG16S3FmRXYzR1ZqVnFoSUdKT3JuZzczaWd5a01ac2Mwb2U4OWNCKzJXWXhYSWNQaElTelR4ajlvcjAzUGlsMUQyWTh3bmJwYkxRTC92cEJTbUdYTm5JOGZtaG12bktHL0JxSzdYOGpDMzd4Vy8xcENVS1lrSXBqNzY1UDZpQ24iLCJtYWMiOiI2MWYzN2U1YzliNzUwYzk4YjU5NWYzZGJkZDFmNjY1ZmY5OTQwMjZmZmRlODI0MTM3OTQyYTQ2YWZmYTAyMGUwIiwidGFnIjoiIn0%3D; webhooksite_session=bLWpZI3gGaySxc2I8lg5fhM4gWyozU08lA5BoX4u'
              ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
        } else {
            // dd($url);
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
              //  ->timeoutInSeconds(5)
                ->maximumTries(5)
                ->dispatch();
               // dd($data);
                
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>json_encode($arrayPayLoad),
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: XSRF-TOKEN=eyJpdiI6IjI2dlhFUURmaHFHYVdLc1lHR21vRkE9PSIsInZhbHVlIjoiUG16S3FmRXYzR1ZqVnFoSUdKT3JuZzczaWd5a01ac2Mwb2U4OWNCKzJXWXhYSWNQaElTelR4ajlvcjAzUGlsMUQyWTh3bmJwYkxRTC92cEJTbUdYTm5JOGZtaG12bktHL0JxSzdYOGpDMzd4Vy8xcENVS1lrSXBqNzY1UDZpQ24iLCJtYWMiOiI2MWYzN2U1YzliNzUwYzk4YjU5NWYzZGJkZDFmNjY1ZmY5OTQwMjZmZmRlODI0MTM3OTQyYTQ2YWZmYTAyMGUwIiwidGFnIjoiIn0%3D; webhooksite_session=bLWpZI3gGaySxc2I8lg5fhM4gWyozU08lA5BoX4u'
              ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);    
        }
    }

    /**
     * AEPS Transaction
     *
     * @param object $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function AEPSTransaction($data, $url = '', $secret = '', $headers = '')
    {

        if ($data->statuscode == '000') {
            $arrayPayLoad['event'] = 'aeps.transfer.success';
            $arrayPayLoad['code'] = "0x0200";
            $arrayPayLoad['message'] = 'Transaction Successful';
        } else {
            $arrayPayLoad['event'] = 'aeps.transfer.failed';
            $arrayPayLoad['code'] = "0x0202";
            $arrayPayLoad['message'] = 'Transaction Failed';
        }
        $arrayPayLoad['data'] = [
            'clientRefNo' => @$data->clientrefid,
            'routeType' => @$data->data['routetype'],
            'bankiin' => @$data->data['bankiin'],
            'stanNo' => @$data->data['stanno'],
            'rrn' => @$data->data['rrn'],
            'bankMessage' => @$data->data['bankmessage'],
            'bankCode' => @$data->data['bankcode'],
            'merchantCode' => @$data->data['merchantcode'],
            'merchantMobile' => @$data->data['merchantmobile'],
            'aadhaarNumber' => @$data->data['aadharnumber'],
            'transactionType' => @$data->data['transactiontype'],
            'transactionDateTime' => @$data->data['transactiondatetime'],
            'availableBalance' => @$data->data['availablebalance'],
            'transactionAmount' => @$data->data['transactionAmount']
        ];
        if (@$data->data['transactiontype'] == 'MS') {
            $arrayPayLoad['data']['statement'] =  @$data->data['minidata'];
        }

      
        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    /**
     * PANFirstTransaction Transaction
     *
     * @param object $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @param string $route
     * @return void
     */
    public static function PANFirstTransaction($data, $url = '', $secret = '', $headers = '', $route = 'uti', $status = 'P')
    {

        if (@$status == 'P') {
            $arrayPayLoad['event'] = 'pan.request.pending';
            $arrayPayLoad['code'] = "0x0206";
            $arrayPayLoad['message'] = 'Transaction pending';
        } else {
            $arrayPayLoad['event'] = 'pan.request.failed';
            $arrayPayLoad['code'] = "0x0202";
            $arrayPayLoad['message'] = 'Transaction Failed';
        }
        $isPhysical = 'Digital';
        if (!empty($data['CouponType']) && $data['CouponType'] == 'Physical') {
            $isPhysical = 'Physical';
        } else if (!empty($data['phypanisreq']) && $data['phypanisreq'] == 'Yes') {
            $isPhysical = 'Physical';
        }

        $arrayPayLoad['data'] = [
            'orderRefId' => !empty($data['ServiceProviderId']) ? $data['ServiceProviderId'] : $data['orderid'],
            'appNo' =>  !empty($data['UTIapplicationNo']) ? $data['UTIapplicationNo'] : "",
            'psaId' => !empty($data['VleID']) ? $data['VleID'] : $data['psacode'],
            'status' => $status,
            'nameOnPan' => !empty($data['nameonpan']) ? $data['nameonpan'] : "",
            'panType' => !empty($data['pantype']) ? $data['pantype'] : "",
            'operatorTxnId' => !empty($data['OperatorTxnId']) ? $data['OperatorTxnId'] : "",
            'psaMobile' =>  !empty($data['psamobile']) ? $data['psamobile'] : "",
            'couponType' =>  $isPhysical,
            'routeType' =>  $route
        ];

        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    /**
     * PANSecondTransaction Transaction
     *
     * @param object $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @param string $route
     * @return void
     */
    public static function PANSecondTransaction($data, $url = '', $secret = '', $headers = '', $route = 'uti')
    {

        if (@$data->status == 'S' || @$data->Status == 'S') {
            $arrayPayLoad['event'] = 'pan.request.success';
            $arrayPayLoad['code'] = "0x0200";
            $arrayPayLoad['message'] = 'Transaction Successful';
        } else if (@$data->status == 'P' || @$data->Status == 'P') {
            $arrayPayLoad['event'] = 'pan.request.pending';
            $arrayPayLoad['code'] = "0x0206";
            $arrayPayLoad['message'] = 'Transaction Pending';
        } else {
            $arrayPayLoad['event'] = 'pan.request.failed';
            $arrayPayLoad['code'] = "0x0202";
            $arrayPayLoad['message'] = 'Transaction Failed';
        }
        $orderRefId = !empty($data['ServiceProviderId']) ? $data['ServiceProviderId'] : $data['orderid'];
        $psaId  = @DB::table('pan_txns')
            ->select('psa_code')
            ->where('order_ref_id', $orderRefId)->first()->psa_code;
        $arrayPayLoad['data'] = [
            'orderRefId' => !empty($data['ServiceProviderId']) ? $data['ServiceProviderId'] : $data['orderid'],
            'pasId' => !empty($data['pasId']) ? $data['pasId'] : $psaId,
            'txnId' =>  !empty($data['txnid']) ? $data['txnid'] : $data['OperatorTxnId'],
            'message' => !empty($data['message']) ? $data['message'] : $data['Message'],
            'status' => !empty($data['status']) ? $data['status'] : $data['Status'],
            'routeType' =>  $route,
        ];

        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    
    /**
     * Recharge Transaction
     *
     * @param object $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function RechargeTransaction($data, $url = '', $secret = '', $headers = '')
    {

        if ($data->statuscode == '000') {
            $arrayPayLoad['event'] = 'recharge.transfer.success';
            $arrayPayLoad['code'] = "0x0200";
            $arrayPayLoad['message'] = 'Transaction Successful';
        } else {
            $arrayPayLoad['event'] = 'recharge.transfer.failed';
            $arrayPayLoad['code'] = "0x0202";
            $arrayPayLoad['message'] = 'Transaction Failed';
        }
        $arrayPayLoad['data'] = [
            'clientRefNo' => @$data->clientrefid,
            'stanNo' => @$data->txnid,
            'rrn' =>  @$data->operatorid,
            'bankMessage' => @$data->message,
            'status' => @$data->status,
        ];
        

      
        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }


    /**
     * Payout Transfer Success
     *
     * @param array $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function PayoutSuccess($data = [], $url = '', $secret = '', $headers = '')
    { 
        // dd($headers);
        $arrayPayLoad['event'] = 'payout.transfer.success';
        $arrayPayLoad['code'] = "0x0200";
        $arrayPayLoad['message'] = 'Transaction Successful';
        $arrayPayLoad['data'] = [
            'orderRefId' => @$data->order_ref_id,
            'clientRefId' => @$data->client_ref_id,
            'contactId' => @$data->contact_id,
            'firstName' => @$data->contact->first_name,
            'lastName' => @$data->contact->last_name,
            'email' => @$data->contact->email,
            'phone' => @$data->contact->phone,
            'amount' => @$data->amount,
            'status' => @$data->status,
            'utr' => @$data->bank_reference,
            //'udf1' => @$data->udf1,
            //'udf2' => @$data->udf2
        ];
        
        if ($data->contact->account_type == 'bank_account') {
            $arrayPayLoad['data']['accountNumber'] =  @$data->contact->account_number;
            $arrayPayLoad['data']['accountIFSC'] =  @$data->contact->account_ifsc;
        } elseif ($data->contact->account_type == 'vpa') {
            $arrayPayLoad['data']['vpa'] =  @$data->contact->vpa_address;
        } elseif ($data->contact->account_type == 'card') {
            $arrayPayLoad['data']['card'] =  @$data->contact->card_number;
        }


        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
                //->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    /**
     * Payout Transfer Failed
     *
     * @param array $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function PayoutFailed($data = [], $url = '', $secret = '', $headers = '')
    {
        $arrayPayLoad['event'] = 'payout.transfer.failed';
        $arrayPayLoad['code'] = "0x0202";
        $arrayPayLoad['message'] = 'Transaction Failed';
        $arrayPayLoad['data'] = [
            'orderRefId' => @$data->order_ref_id,
            'clientRefId' => @$data->client_ref_id,
            'contactId' => @$data->contact_id,
            'firstName' => @$data->contact->first_name,
            'lastName' => @$data->contact->last_name,
            'email' => @$data->contact->email,
            'phone' => @$data->contact->phone,
            'amount' => @$data->amount,
            'status' => @$data->status,
            'reason' => @$data->failed_message,
            //'udf1' => @$data->udf1,
            //'udf2' => @$data->udf2
        ];

        if ($data->contact->account_type == 'bank_account') {
            $arrayPayLoad['data']['accountNumber'] =  @$data->contact->account_number;
            $arrayPayLoad['data']['accountIFSC'] =  @$data->contact->account_ifsc;
        } elseif ($data->contact->account_type == 'vpa') {
            $arrayPayLoad['data']['vpa'] =  @$data->contact->vpa_address;
        } elseif ($data->contact->account_type == 'card') {
            $arrayPayLoad['data']['card'] =  @$data->contact->card_number;
        }
        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    /**
     * Payout Transfer Reverse
     *
     * @param array $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function PayoutReverse($data = [], $url = '', $secret = '', $headers = '')
    {
        $arrayPayLoad['event'] = 'payout.transfer.reverse';
        $arrayPayLoad['code'] = "0x0207";
        $arrayPayLoad['message'] = 'Transaction Reversed';
        $arrayPayLoad['data'] = [
            'orderRefId' => @$data->order_ref_id,
            'clientRefId' => @$data->client_ref_id,
            'contactId' => @$data->contact_id,
            'firstName' => @$data->contact->first_name,
            'lastName' => @$data->contact->last_name,
            'email' => @$data->contact->email,
            'phone' => @$data->contact->phone,
            'amount' => @$data->amount,
            'status' => @$data->status,
            'reason' => @$data->failed_message,
            //'udf1' => @$data->udf1,
            //'udf2' => @$data->udf2
        ];
// dd($arrayPayLoad);
        if ($data->contact->account_type == 'bank_account') {
            $arrayPayLoad['data']['accountNumber'] =  @$data->contact->account_number;
            $arrayPayLoad['data']['accountIFSC'] =  @$data->contact->account_ifsc;
        } elseif ($data->contact->account_type == 'vpa') {
            $arrayPayLoad['data']['vpa'] =  @$data->contact->vpa_address;
        } elseif ($data->contact->account_type == 'card') {
            $arrayPayLoad['data']['card'] =  @$data->contact->card_number;
        }

        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    /**
     * DMT Transfer
     *
     * @param array $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function DMTWebhook($data, $url = '', $secret = '', $headers = '', $event  = '', $code, $message)
    {

        $arrayPayLoad['event'] = $event;
        $arrayPayLoad['code'] = $code;
        $arrayPayLoad['message'] = $message;
        $arrayPayLoad['data'] = $data;


        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
                //->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }


    /**
     * VAN and UPI callback
     * when credit comes form cashfree
     */
    public static function autoCollectSuccess($data, $url = '', $secret = '', $headers = [])
    {
        $arrayPayLoad['event'] = 'collect.receive.success';
        $arrayPayLoad['code'] = "0x0200";
        $arrayPayLoad['message'] = 'Transaction Successful';
        $arrayPayLoad['data'] = [
            'amount' => @$data->amount,
            'utr' => @$data->utr,
            'referenceId' => @$data->reference_id,
            'creditRefNo' => @$data->credit_ref_no,
            'remitterAccount' => @$data->remitter_account,
            'remitterIfsc' => @$data->remitter_ifsc,
            'remitterName' => @$data->remitter_name,
            'remitterVpa' => @$data->remitter_vpa,
            'transferType' => @$data->transfer_type,
            'remarks' => @$data->remarks,
            'date' => @$data->created_at,
        ];


        if (!empty($data->is_vpa)) {
            $arrayPayLoad['data']['serviceType'] = 'upi';
            $arrayPayLoad['data']['vpaAccId'] = $data->v_account_id;
        } else {
            $arrayPayLoad['data']['serviceType'] = 'van';
            $arrayPayLoad['data']['vanAccId'] = $data->v_account_id;
        }


        if (!empty($data->virtual_vpa_id)) {
            $arrayPayLoad['data']['virtualVpaId'] = $data->virtual_vpa_id;
        } else if (!empty($data->v_account_number)) {
            $arrayPayLoad['data']['vAccountNumber'] = $data->v_account_number;
        }

        if ($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
              //  ->timeoutInSeconds(5)
                ->maximumTries(5)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(5)
                ->dispatch();
        }
    }




    /**
     * Store Webhook Logs into DB
     */
    public static function storeLog2Db($data, $isFinal = false)
    {
        $data = json_decode($data);
        if(!empty($data->uuid)){
            $uuid = $data->uuid;
            unset($data->uuid);
        }

        $insertArr = [];

        foreach($data as $key => $row){
            $insertArr[$key] = json_encode($row);
        }
        
        if($isFinal){
            \App\Models\MWebhookLog::updateLog(['uuid' => $uuid], $insertArr);
           // DB::table('webhook_logs')->where('uuid', $uuid)->update($insertArr);
            return true;
        }


        $count =  \App\Models\MWebhookLog::select('_id')
            ->where('uuid', $uuid)->count();
        
        if($count > 0)
            return false;

        $insertArr['uuid'] = $uuid;
            $insertArr['created_at'] = date('Y-m-d H:i:s');
            $insertArr['updated_at'] = date('Y-m-d H:i:s');
            \App\Models\MWebhookLog::insertLog($insertArr);

        /*  if (isset($insertArr['headers']) && $insertArr['errorType'] == 'null') {
                $count = DB::table('webhook_logs')->select('id')
                    ->where('headers', $insertArr['headers'])->first();
                if (isset($count) && !empty($count)) {
                    DB::table('webhook_logs')
                        ->where('id', $count->id)
                        ->update(['response' => 'success']);
                }
            }
        */
        return true;
    }
}