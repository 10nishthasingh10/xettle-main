<?php
namespace App\Helpers;

use CommonHelper;
use App\Models\MApiLog;
use Illuminate\Support\Facades\DB;

class SafeXPayHelper {
    private $agentId;
    private $sessionId;
    private $baseUrl;
    private $secret;
    private $iv;
    private $attribute_1;

    public function __construct()
    {
       // $Integration = Integration::where('slug', 'safexpay')->where('is_active','1')->first();
        $this->agentId=base64_decode(env('SAFEXPAY_AG_ID'));
        $this->sessionId=base64_decode(env('SAFEXPAY_ME_ID'));
        $this->baseUrl=env('SAFEXPAY_BASE_URL');
        $this->secret=base64_decode(env('SAFEXPAY_ATTR_2'));
        $this->iv=base64_decode(env('SAFEXPAY_ATTR_3'));
        $this->attribute_1=base64_decode(env('SAFEXPAY_ATTR_1'));
       
    }

    public function versionCheck()
    {
        $key = "9v6ZyFBzNYoP2Un8H5cZq5FeBwxL6itqNZsm7lisGBQ=";
        //$key = "/yQkzO9jeKgSyd0j0GFEikaJT5mz+6DzuoJAg7wimr4=";
        
        $iv = "0123456789abcdef";
        $payload ='';
        $request = [
            "agId"=> "AGGR0026548013",
            "payload"=> $payload,
            "uId"=> "CHECK"
        ];
        $header = array(
            'operatingSystem:WEB' ,
            'Content-Type: application/json',
        );
        $result = CommonHelper::curl($this->baseUrl, "POST", json_encode($request) , $header, 'yes');
       
        $response = json_decode($result);
        
        return $response;
    }

    public function payoutWithoutOtp($mobileNo, $txnAmount, $accountNo, $ifscCode, $bankName, $accountHolderName, $txnType, $accountType, $orderRefNo ,$userId)
    {
        $requestType = 'WTW';
        $requestSubType = 'PWTB';

        $req = '{"header": {"operatingSystem": "android", "sessionId": "'.$this->sessionId.'"},"transaction": {"requestType": "'.$requestType.'","requestSubType": "'.$requestSubType.'"},"payOutBean": {"mobileNo": "'.$mobileNo.'","txnAmount":"'.$txnAmount.'","accountNo": "'.$accountNo.'","ifscCode": "'.$ifscCode.'","bankName": "'.$bankName.'","accountHolderName": "'.$accountHolderName.'","txnType": "'.$txnType.'","accountType": "'.$accountType.'","orderRefNo":"'.$orderRefNo.'"}}';
        $requestType = 'payoutWithoutOtp';
        $modal = 'safeXPay';
        $result =  $this->executeRequest($req ,$modal, $requestType, $orderRefNo, $userId);

        return $result;
    }

    public function sfAutoSettlement($mobileNo, $txnAmount, $accountNo, $ifscCode, $bankName, $accountHolderName, $txnType, $accountType, $orderRefNo ,$userId)
    {
        
        $requestType = 'WTW';
        $requestSubType = 'PWTB';

        $req = '{"header": {"operatingSystem": "android", "sessionId": "'.$this->sessionId.'"},"transaction": {"requestType": "'.$requestType.'","requestSubType": "'.$requestSubType.'"},"payOutBean": {"mobileNo": "'.$mobileNo.'","txnAmount":"'.$txnAmount.'","accountNo": "'.$accountNo.'","ifscCode": "'.$ifscCode.'","bankName": "'.$bankName.'","accountHolderName": "'.$accountHolderName.'","txnType": "'.$txnType.'","accountType": "'.$accountType.'","orderRefNo":"'.$orderRefNo.'"}}';
        $requestType = 'sfAutoSettlement';
        $modal = 'safeXPay';
        $result =  $this->executeRequest($req ,$modal, $requestType, $orderRefNo, $userId);

        return $result;
    }

    public function payoutStatusCheckByPayoutId($payout_id,$userId)
    {
        $req = '{"header": {"operatingSystem": "android","sessionId": "'.$this->sessionId.'"},"transaction": {"requestType": "TMH","requestSubType": "STCHK"},"payOutBean": {"payoutId": "'.$payout_id.'"}}';
        $orderRefNo = DB::table('orders')->select('order_ref_id')
            ->where(['payout_id' => $payout_id, 'user_id' => $userId])
            ->first()->order_ref_id;
        $requestType = 'statusById';
        $modal = 'safeXPay';
        $result =  $this->executeRequest($req ,$modal, $requestType, $orderRefNo, $userId);

        return $result;
    }

    public function payoutStatusCheckByOrderRefId($orderRefNo,$userId)
    {


        $req = '{"header": {"operatingSystem": "android","sessionId": "'.$this->sessionId.'"},"transaction": {"requestType": "TMH","requestSubType": "STCHK"},"payOutBean": {"orderRefNo": "'.$orderRefNo.'"}}';

        $requestType = 'statusById';
        $modal = 'safeXPay';
        $result =  $this->executeRequest($req ,$modal, $requestType, $orderRefNo, $userId);

        return $result;
    }
    public function allPayoutStatusCheck()
    {
        $req = '{"header": {"operatingSystem": "android","sessionId": "'.$this->sessionId.'"},"userInfo": {},
        "transaction": {"requestType": "WTW","requestSubType": "PWTB","channel": "android","tranCode": 0,"txnAmt":1},
        "payOutBean": {"customerId": "'.$this->agentId.'","Startdate": "2021-07-05","Enddate": "2021-07-8"}}';

        $requestType = 'status';
        $modal = 'safeXPay';
        $result =  $this->executeRequest($req ,$modal, $requestType);
        return $result;
    }

    public function safexPayPayoutBalanceCheck()
    {
        $req = '{"header": {"operatingSystem": "android", "sessionId": "'.$this->agentId.'",
        "version": "1.0.0" },"userInfo": {},"transaction": {"requestType": "WTW","requestSubType": "GPWB","id": "'.$this->agentId.'"},"preLoadingWallet": {"walletType": "PAYOUT"}}';
        $requestType = 'balance';
        $modal = 'safeXPay';
        $result =  $this->executeRequest($req ,$modal, $requestType);
        return $result;
    }

    public function executeRequest($req ,$modal, $requestType, $orderRefNo = '', $userId = 1)
    {
        $encryptedText = CommonHelper::encrypt($req, $this->secret, $this->iv);

        $request = [
            "agId"=> $this->agentId,
            "payload"=> $encryptedText,
            "uId"=> $this->sessionId
        ];
        $requestLog = '{"agId" :'.$this->agentId.', "payload" : '.$req.', "uId": '.$this->sessionId.' }';

        $header = array(
            'operatingSystem:android' ,
            'Content-Type: application/json',
        );

        $result = CommonHelper::curl($this->baseUrl, "POST", json_encode($request) , $header, 'yes', $userId, $modal, $requestType, $orderRefNo, $requestLog);
        $response = json_decode($result['response']);
        if(isset($result) && !empty($result['code']) && $result['code'] == 503){
            $encryptedText = json_encode(['status' => false, 'code' => $result['code'], 'message' => $result['response']]);
        }else {
            $encryptedText = CommonHelper::decrypt($response->payload, $this->secret, $this->iv);
            MApiLog::updateLog($result['apiLogLastId'], ['response' => $encryptedText]);
        }
        return $encryptedText;
    }
}
