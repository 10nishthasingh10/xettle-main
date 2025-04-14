<?php

namespace App\Services\OpenBank;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Jobs\PrimaryFundCredit;
use App\Jobs\SendTransactionEmailJob;
use Exception;
use Illuminate\Support\Facades\DB;

class OBApiService
{
    private $key;
    private $secret;
    private $baseUrl;
    private $header;
    // private $tableName = 'user_van_accounts';


    public function __construct()
    {
        $this->baseUrl = env('OPENBANK_URL');
        $this->key = base64_decode(env('OPENBANK_KEY'));
        $this->secret = base64_decode(env('OPENBANK_SECRET'));

        $this->header = array(
            "Authorization" => "Bearer {$this->key}:{$this->secret}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        );
    }


    public function send(OpenBankBO $obj)
    {

        $url = $this->baseUrl . $this->getUri($obj->uri);
        $result = CommonHelper::httpClient(
            $url,
            $obj->http,
            $this->makeParams($obj->uri, $obj->param),
            $this->header,
            $obj->log,
            $obj->userId,
            $obj->table,
            $obj->slug,
            $obj->clientRefId
        );

        return $result;
    }




    public function update($cond = [], $data = [], $table = 'validation')
    {
        switch ($table) {
            case 'validation':
                if (DB::table('validations')->where($cond)->update($data)) {
                    return true;
                }
                return false;
                break;
        }
    }



    public function getUri($type)
    {
        $uri = '';

        switch ($type) {
            case 'van_create':
                $uri = '/v1/virtual_accounts';
                break;

            case 'bank_verify':
                $uri = '/v1/bank_account/verify';
                break;
        }

        return $uri;
    }



    /**
     * Making API Params
     */
    private function makeParams($type, object $params)
    {

        $param = [];

        switch ($type) {
            case 'van_create':
                $param = [
                    "name" => $params->businessName,
                    "primary_contact" => $params->name,
                    "contact_type" => $params->contactType,
                    "email_id" => $params->email,
                    "mobile_number" => $params->mobile
                ];
                break;

            case 'bank_verify':
                $param = [
                    "bene_account_number" => $params->accountNumber,
                    "ifsc_code" => $params->ifscCode,
                    "merchant_ref_id" => $params->refId
                ];
                break;
        }

        return $param;
    }


    /**
     * Handle Van payment callback
     */
    public function vanCallbackHandler($data)
    {
        $hash = $data['hash'];
        unset($data['hash']);

        $generatedHash = $this->verifySignature(json_encode($data), $hash);

        if ($generatedHash === true) {

            $eventTypesId = $data['event_types_id'];

            switch ($eventTypesId) {

                case 4:
                    //va_credited

                    $virtualAccountNumber = $data['virtual_account_number'];
                    $utr = $data['bank_ref_id'];
                    $amount = floatval($data['amount']);
                    $paymentDate = $data['payment_date'];
                    $paymentMode = $data['payment_mode'];
                    // $name = $data['name'];
                    // $primaryContact = $data['primary_contact'];
                    // $emailId = $data['email_id'];
                    // $mobileNumber = $data['mobile_number'];
                    // $vpa = $data['vpa'];


                    //check virtual id is correct or not
                    $merchant = DB::table('user_van_accounts')
                        ->select('*')
                        ->where('root_type', OPEN_BANK_VAN)
                        ->where('account_number', $virtualAccountNumber)
                        ->first();


                    if (empty($merchant)) {
                        $res['status'] = 'FAILURE';
                        $res['message'] = 'Invalid Virtual Account callback received';
                        $res['time'] = date('Y-m-d H:i:s');

                        return $res;
                    }


                    //check for already transaction by UTR
                    $checkUTR = DB::table('fund_receive_callbacks')
                        ->where('root_type', OPEN_BANK_VAN)
                        ->where('utr', $utr)
                        ->first();

                    if (!empty($checkUTR)) {
                        $res['status'] = 'FAILURE';
                        $res['message'] = 'Callback already received.';
                        $res['time'] = date('Y-m-d H:i:s');

                        // DB::table('apilogs')->where('id', $apilogId)
                        //     ->update(['resp_message' => json_encode($res)]);

                        return $res;
                    }


                    $refId = 'OBT_' . $utr;
                    $products = CommonHelper::getProductId('van_collect', 'van_collect');

                    //fee and tax on fee calculation
                    $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $merchant->user_id);
                    $feeRate = $taxFee->margin;
                    $fee = round($taxFee->fee, 2);
                    $tax = round($taxFee->tax, 2);
                    $crAmount = ($amount - $fee - $tax);


                    //store callback response
                    $vanData = [
                        'root_type' => OPEN_BANK_VAN,
                        'user_id' => $merchant->user_id,
                        'ref_no' => $refId,
                        'utr' => $utr,
                        'amount' => $amount,
                        'fee' => $fee,
                        'tax' => $tax,
                        'cr_amount' => $crAmount,
                        'fee_rate' => $feeRate,
                        // 'reference_id' => $paymentId,
                        // 'v_account_id' => $merchant->account_id,
                        'v_account_number' => $virtualAccountNumber,
                        // 'remitter_name' => $remitterName,
                        // 'remitter_account' => $remitterAccount,
                        // 'remitter_ifsc' => $remitterIfsc,
                        'transfer_type' => $paymentMode,
                        // 'remarks' => $remarks,
                        'payment_time' => $paymentDate,
                        'is_trn_credited' => '0',
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    try {

                        $vanData['rowId'] = DB::table('fund_receive_callbacks')->insertGetId($vanData);
                        $vanData['identifire'] = 'ob_van_inward_credit';


                        //check service is enable or not
                        $isServiceActive = CommonHelper::checkIsServiceActive('openbank_partner_van', $merchant->user_id);

                        if ($isServiceActive) {
                            PrimaryFundCredit::dispatch((object) $vanData, 'partner_van_ob_credit')->onQueue('primary_fund_queue');
                        }

                        $res['status'] = true;
                        $res['message'] = 'Request captured successfully';
                        $res['time'] = date('Y-m-d H:i:s');

                        // DB::table('apilogs')->where('id', $apilogId)
                        //     ->update(['resp_message' => json_encode($res)]);

                        $res['data'] = $data;
                        return $res;
                    } catch (Exception $e) {
                        //inward credit error
                        $res['status'] = 'FAILURE';
                        $res['message'] = 'Credit Error: ' . $e->getMessage();
                        $res['time'] = date('Y-m-d H:i:s');

                        return $res;
                    }

                    // "amount":"2.56",
                    //    "bank_ref_id":"022216477238",
                    //    "virtual_account_number":"363611794580225",
                    //    "payment_date":"2020-08-09 16:37:58",
                    //    "payment_mode":"UPI",
                    //    "status":"success",
                    //    "vpa":"open.3000002229@icici",
                    //    "virtual_account_ifsc_code":"ICIC0000104",
                    //    "name":"Faris Vendor 2",
                    //    "primary_contact":"Faris2",
                    //    "email_id":"johntest@gmail.com",
                    //    "mobile_number":"1234567893",
                    break;
            }
        }

        $res['status'] = 'FAILURE';
        $res['message'] = 'Hash not matched.';
        $res['time'] = date('Y-m-d H:i:s');

        return $res;
    }



    /**
     * Verify Signature
     */
    private function verifySignature($params, $expectedSignature)
    {
        $actualSignature = hash_hmac('sha256', $params, $this->secret);

        return hash_equals($actualSignature, $expectedSignature);
    }



    /**
     * Amount credited into user primary wallet
     * When fund comes through VAN API
     * Function used by Jobs
     */
    public static function creditFundJobHandler($data)
    {

        //check for transaction entry, if customer_ref_id exist
        $isTransactions = DB::table('transactions')
            ->select('id')
            ->where('txn_ref_id', $data->ref_no)
            ->count();

        if ($isTransactions > 0) {
            return "Transaction already credited";
        }

        $rowId = $data->rowId;
        $txnId = CommonHelper::getRandomString('txn', false);
        $txnReferenceId = $data->ref_no;
        $identifire = !empty($data->identifire) ? $data->identifire : 'ob_van_inward_credit';


        //getting priduct service ID
        //getting Product ID
        $products = CommonHelper::getProductId('van_collect', 'van_collect');

        $trTotalAmountSigned = ($data->cr_amount >= 0) ? '+' . $data->cr_amount : $data->cr_amount;
        $txnNarration = $data->cr_amount . ' credited against ' . $data->utr;

        DB::select("CALL EbPartnerVanCreditTxnJob($data->user_id, $rowId, '$txnId', '$txnReferenceId', '$data->utr', '$trTotalAmountSigned', $data->amount, $data->fee, $data->tax, $data->cr_amount, '$txnNarration', '$products->service_id', '$data->fee_rate', '$identifire', @outData)");

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
}
