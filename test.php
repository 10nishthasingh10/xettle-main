<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://dragonpayment.net/api/inr/notice/safepay',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "event": "upi.receive.success",
  "code": "success",
  "message": "Transaction Successful",
  "data": {
    "amount": "100",
    "npciTxnId": "333543815462",
    "originalOrderId": "XTL20925905391",
    "merchantTxnRefId": "1701445301028805",
    "bankTxnId": "f9cbf9af3a064d8baccee5c31a021b9d",
    "customerRefId": "TXN628728369871497E452",
    "payer_vpa": "9752397540@paytm",
    "payerMobile": null,
    "payerAccName": null,
    "payerAccNo": null,
    "payerIFSC": null,
    "type": null,
    "date": null
  }
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
