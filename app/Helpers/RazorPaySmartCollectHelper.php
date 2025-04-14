<?php

namespace App\Helpers;

use App\Jobs\SendTransactionEmailJob;
use Exception;
use Illuminate\Support\Facades\DB;

class RazorPaySmartCollectHelper
{
    private $key;
    private $secret;
    private $baseUrl;
    private $webhookSecret;

    public function __construct()
    {
        $this->baseUrl = env('RAZPAY_BASE_URL') . '/v1';
        $this->key = base64_decode(env('RAZPAY_CLIENT_ID'));
        $this->secret = base64_decode(env('RAZPAY_CLIENT_SECRET'));
        $this->webhookSecret = base64_decode(env('RAZPAY_WEBHOOK_SECRET'));
    }


    public function auth()
    {
        $str = base64_encode("{$this->key}:{$this->secret}");
        return $str;
    }


    public function apiCaller($params, $url, $userId = 0, $requestType = "POST", $method = "generateVan", $modal = "RazorPaySmartCollect")
    {

        $header = array(
            'Content-Type: application/json',
            'Accept' => 'application/json',
            "Authorization: Basic " . $this->auth(),
        );


        $result = CommonHelper::curl(
            $this->baseUrl . $url,
            $requestType,
            json_encode($params),
            $header,
            'yes',
            $userId,
            $modal,
            $method
        );

        return $result;
    }


    public function verifySignature($params, $expectedSignature)
    {
        $actualSignature = hash_hmac('sha256', $params, $this->webhookSecret);

        // Use lang's built-in hash_equals if exists to mitigate timing attacks
        if (function_exists('hash_equals')) {
            $verified = hash_equals($actualSignature, $expectedSignature);
        } else {
            $verified = $this->hashEquals($actualSignature, $expectedSignature);
        }

        return $verified;
    }



    private function hashEquals($actualSignature, $expectedSignature)
    {
        if (strlen($expectedSignature) === strlen($actualSignature)) {
            $res = $expectedSignature ^ $actualSignature;
            $return = 0;

            for ($i = strlen($res) - 1; $i >= 0; $i--) {
                $return |= ord($res[$i]);
            }

            return ($return === 0);
        }

        return false;
    }



    /**
     * Amount credited into user primary wallet
     * When fund comes through VAN API
     * Function used by Jobs
     */
    public static function razInstaCollectCreditTxnJob($data)
    {

        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')->select('id')
            ->where('txn_ref_id', $data->ref_no)->count();

        if ($isTransactions > 0) {
            return "Transaction already credited";
        }

        $rowId = $data->rowId;
        $txnId = CommonHelper::getRandomString('txn', false);
        $txnReferenceId = $data->ref_no;
        $identifire = 'raz_van_inward_credit';

        //getting Product ID
        $products = CommonHelper::getProductId('van_collect', 'van_collect');


        $trTotalAmountSigned = ($data->cr_amount >= 0) ? '+' . $data->cr_amount : $data->cr_amount;
        $txnNarration = $data->cr_amount . ' credited against ' . $data->utr;

        DB::select("CALL RazPayPartnerVanCreditTxnJob($data->user_id, $rowId, '$txnId', '$txnReferenceId', '$data->utr', '$trTotalAmountSigned', $data->amount, $data->fee, $data->tax, $data->cr_amount, '$txnNarration', '$products->service_id', '$data->fee_rate', '$identifire', @outData)");

        $results = DB::select('select @outData as outData');
        $response = json_decode($results[0]->outData);


        if (!empty($response->email)) {
            try {
                $mailParms = [
                    'email' => $response->email,
                    'name' => $response->name,
                    'amount' => $data->amount,
                    'transfer_date' => $response->date,
                    'acc_number' => $response->account_number,
                    'ref_number' => $txnId
                ];

                dispatch(new SendTransactionEmailJob((object) $mailParms, 'vanCredit'));
            } catch (Exception $e) {
                //mail not send
                //Storage::prepend('van_cr_mail.txt', print_r(['date' => date('Y-m-d H:i:s'), 'msg' => $e->getMessage(), 'line' => $e->getLine()], true));
            }
        }

        return $response->status;
    }


    public static function bankInfoViaIfsc($bo)
    {
        try {

            $isApi = true;

            //check ifsc info in DB
            $ifscInfo = DB::table('bank_ifsc_infos')
                ->select('id', 'json', 'updated_at')
                ->where('ifsc', $bo->param['ifsc'])
                ->first();

            if (!empty($ifscInfo)) {
                //check how old record
                $dbDate = strtotime($ifscInfo->updated_at);
                $date30Day = strtotime('-30 days', time());

                if ($dbDate >= $date30Day) {
                    $isApi = false;
                    return ['status' => 'success', 'data' => json_decode($ifscInfo->json)];
                }
            }

            if ($isApi) {
                //if not in database, hit api
                $result = CommonHelper::curl(
                    'https://ifsc.razorpay.com/' . $bo->param['ifsc'],
                    $bo->http,
                    '',
                    [],
                    'yes',
                    $bo->userId,
                    $bo->table,
                    $bo->slug,
                    $bo->clientRefId
                );

                if ($result['code'] == 200) {

                    if (!empty($ifscInfo->id)) {
                        DB::table('bank_ifsc_infos')
                            ->where('id', $ifscInfo->id)
                            ->update([
                                'json' => $result['response'],
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                    } else {
                        DB::table('bank_ifsc_infos')
                            ->insert([
                                'ifsc' => $bo->param['ifsc'],
                                'json' => $result['response'],
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                    }

                    return ['status' => 'success', 'data' => json_decode($result['response'])];
                } else if ($result['code'] == 404) {
                    return ['status' => 'failed'];
                }
            }

            return null;
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Some error occurred.'];
        }
    }
}
