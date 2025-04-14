<?php

namespace App\Http\Controllers\Api\v1\Callbacks;

use App\Helpers\CommonHelper;
use App\Helpers\RazorPaySmartCollectHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrimaryFundCredit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RazorPayCallbackController extends Controller
{
    public function callback(Request $request)
    {
        try {

            $data = $request->all();
            $headerData = $request->header();

            $utr = isset($data['payload']['bank_transfer']['entity']['bank_reference']) ? ($data['payload']['bank_transfer']['entity']['bank_reference']) : "";

            $apilogId = DB::table('apilogs')->insertGetId([
                'modal' => 'RazorPayAutoCollect',
                'txnid' => $utr,
                'method' => 'Callback',
                'header' => json_encode($headerData),
                'request' => 'NA',
                'call_back_response' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);


            if (empty($data['event'])) {
                $res['status'] = 'FAILURE';
                $res['message'] = 'Event is empty.';
                $res['time'] = date('Y-m-d H:i:s');

                DB::table('apilogs')->where('id', $apilogId)
                    ->update(['resp_message' => json_encode($res)]);

                return response()->json($res);
            }


            $event = $data['event'];


            switch ($event) {

                case 'virtual_account.credited':

                    $webhookSignature = !empty($headerData['x-razorpay-signature'][0]) ? $headerData['x-razorpay-signature'][0] : '';

                    //validate signature
                    $cacHelper = new RazorPaySmartCollectHelper();
                    $signMatch = $cacHelper->verifySignature(json_encode($data), $webhookSignature);

                    if ($signMatch === true) {

                        // $accountId = $data['payload']['virtual_account']['entity']['id'];
                        $virtualAccountId = $data['payload']['bank_transfer']['entity']['virtual_account_id'];
                        // $customerId = $data['payload']['virtual_account']['entity']['customer_id'];
                        $amount = $data['payload']['bank_transfer']['entity']['amount'];
                        $remitterName = $data['payload']['bank_transfer']['entity']['payer_bank_account']['name'];
                        $remitterIfsc = $data['payload']['bank_transfer']['entity']['payer_bank_account']['ifsc'];
                        $remitterAccount = $data['payload']['bank_transfer']['entity']['payer_bank_account']['account_number'];
                        $vanAccountNumber = $data['payload']['virtual_account']['entity']['receivers'][0]['account_number'];
                        $paymentId = $data['payload']['bank_transfer']['entity']['payment_id'];
                        $paymentMode = $data['payload']['bank_transfer']['entity']['mode'];
                        $remarks = $data['payload']['virtual_account']['entity']['notes']['Important'];
                        $createdAt = $data['created_at'];


                        //check virtual id is correct or not
                        $merchant = DB::table('user_van_accounts')
                            ->select('*')
                            ->where('root_type', 'raz_van')
                            ->where('account_id', $virtualAccountId)
                            ->first();

                        if (empty($merchant)) {
                            $res['status'] = 'FAILURE';
                            $res['message'] = 'Invalid Virtual Account callback received';
                            $res['time'] = date('Y-m-d H:i:s');

                            DB::table('apilogs')->where('id', $apilogId)
                                ->update(['resp_message' => json_encode($res)]);

                            return response()->json($res);
                        }


                        //check for already transaction by UTR
                        $checkUTR = DB::table('fund_receive_callbacks')
                            ->where('root_type', 'raz_van')
                            ->where('utr', $utr)
                            ->first();


                        if (!empty($checkUTR)) {
                            $res['status'] = 'FAILURE';
                            $res['message'] = 'Transaction Already Credited.';
                            $res['time'] = date('Y-m-d H:i:s');

                            DB::table('apilogs')->where('id', $apilogId)
                                ->update(['resp_message' => json_encode($res)]);

                            return response()->json($res);
                        }


                        $refId = 'RBT_' . $utr;

                        //getting priduct service ID
                        //getting Product ID
                        $products = CommonHelper::getProductId('van_collect', 'van_collect');

                        //fee and tax on fee calculation
                        $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $merchant->user_id);
                        $feeRate = $taxFee->margin;
                        $fee = round($taxFee->fee, 2);
                        $tax = round($taxFee->tax, 2);
                        $crAmount = ($amount - $fee - $tax);

                        //store callback response
                        $vanData = [
                            'root_type' => 'raz_van',
                            'user_id' => $merchant->user_id,
                            'ref_no' => $refId,
                            'utr' => $utr,
                            'amount' => $amount,
                            'fee' => $fee,
                            'tax' => $tax,
                            'cr_amount' => $crAmount,
                            'fee_rate' => $feeRate,
                            'reference_id' => $paymentId,
                            'v_account_id' => $virtualAccountId,
                            'v_account_number' => $vanAccountNumber,
                            'remitter_name' => $remitterName,
                            'remitter_account' => $remitterAccount,
                            'remitter_ifsc' => $remitterIfsc,
                            'transfer_type' => $paymentMode,
                            'remarks' => $remarks,
                            'payment_time' => date('Y-m-d H:i:s', $createdAt),
                            'is_trn_credited' => '0',
                            'created_at' => date('Y-m-d H:i:s')
                        ];


                        try {

                            $vanData['rowId'] = DB::table('fund_receive_callbacks')->insertGetId($vanData);


                            //check service is enable or not
                            $isServiceActive = CommonHelper::checkIsServiceActive('raz_partner_van', $merchant->user_id);

                            if ($isServiceActive) {
                                PrimaryFundCredit::dispatch((object) $vanData, 'partner_van_raz_credit')->onQueue('primary_fund_queue');
                            }

                            $res['status'] = true;
                            $res['message'] = 'Request captured successfully';
                            $res['time'] = date('Y-m-d H:i:s');

                            DB::table('apilogs')->where('id', $apilogId)
                                ->update(['resp_message' => json_encode($res)]);

                            $res['data'] = $data;
                            return response()->json($res);
                        } catch (Exception $e) {
                            //inward credit error
                            $res['status'] = 'FAILURE';
                            $res['message'] = 'Credit Error: ' . $e->getMessage();
                            $res['time'] = date('Y-m-d H:i:s');

                            return response()->json($res);
                        }
                    }

                    //when signature is not matched
                    else {
                        // Reject this call
                        $res['status'] = 'FAILURE';
                        $res['message'] = 'Invalid Signature';
                        $res['time'] = date('Y-m-d H:i:s');

                        DB::table('apilogs')->where('id', $apilogId)
                            ->update(['resp_message' => json_encode($res)]);

                        return response()->json($res);
                    }

                    break;


                default:
                    $res['status'] = 'FAILURE';
                    $res['message'] = 'Unexpected response received';
                    $res['time'] = date('Y-m-d H:i:s');

                    return response()->json($res);
                    break;
            }
        } catch (Exception $e) {
            $res['status'] = 'FAILURE';
            $res['message'] = 'Error: ' . $e->getMessage();
            $res['time'] = date('Y-m-d H:i:s');

            return response()->json($res);
        }
    }
}
