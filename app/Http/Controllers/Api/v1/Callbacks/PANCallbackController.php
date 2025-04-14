<?php

namespace App\Http\Controllers\Api\v1\Callbacks;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Clients\Api\v1\PanCardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PANCallbackController extends Controller
{
    /**
     * Method callback
     *
     * @param Request $post [explicite description]
     * @param $api $api [explicite description]
     *
     * @return string
     */
    public function callback(Request $post, $api): string
    {
        $res = [];
        $status = 0;
        $txnId =  CommonHelper::getRandomString('TXN', false);
        $data = $post->all();
        if (isset($data['psacode']) || isset($data['status'])) {
            DB::table('pan_callbacks')
                ->insert([
                    'order_ref_Id' => @$data['orderid'], 'type' => @$data['routetype'],
                    'status' => @$data['status'],  'response' => json_encode($data)
                ]);

                if (in_array($data['status'], ['F', 'S'])) {
                    $userId = DB::table('pan_txns')->where([
                            'order_ref_id' => @$data['orderid']
                        ])
                        ->select('user_id')
                        ->first();
                        if (empty($userId)) {
                            $res = ['status' => 404, 'message' => 'Order id is invalid.'];
                            return json_encode($res);
                        }
                }
            if ($data['status'] == 'P') {
                $userId = DB::table('pan_agents')->where([
                    'psa_id' => @$data['psacode']
                ])->select('user_id')
                    ->first();
                $panTxn = DB::table('pan_txns')->where([
                    'order_ref_id' => @$data['orderid'],
                ])->count();
                if (!empty($userId) && $panTxn == 0) {

                    $isPhysical = 'pan_digital';

                    if (!empty($data['phypanisreq']) && $data['phypanisreq'] == 'Yes') {
                        $isPhysical = 'pan_physical';
                    }

                    $taxData = PanCardController::getFeeAndTaxs(@$userId->user_id, $isPhysical);


                    DB::table('pan_txns')
                        ->insert([
                            'txn_id' => $txnId,
                            'order_ref_id' => @$data['orderid'],
                            'route_type' => @$data['routetype'],
                            'txn_status' => @$data['status'],
                            'status' => 'queued',
                            'psa_code' => @$data['psacode'],
                            'txn_type' => 'nsdl',
                            'phy_pan_is_req' => @$data['phypanisreq'],
                            'pan_type' => @$data['pantype'],
                            'name_on_pan' => @$data['nameonpan'],
                            'tax' => 0,
                            'fee' => $taxData['fee'],
                            'margin' => $taxData['margin'],
                            'service_id' => PAN_CARD_SERVICE_ID,
                            'user_id' => @$userId->user_id,
                        ]);


                        $WebHookStatus = 0;
                        $panCardWebHookResponse = PanCardController::sendFirstNsdlWebhook($post, $userId->user_id);
                        if (!$panCardWebHookResponse) {
                            DB::table('pan_txns')
                                ->where( 'order_ref_id' , @$data['orderid'])
                                ->update([
                                    'status' => 'failed',
                                    'failed_message' => 'webhook_not_receive'
                                ]);

                            $res = ['status' => 201, 'message' => 'Webhook not receive client side'];
                            return json_encode($res);
                        } else {
                            if (@$panCardWebHookResponse->status == 200 && @$panCardWebHookResponse->orderRefId == $data['orderid']) {
                                $WebHookStatus = 1;
                            }
                        }

                        if ($WebHookStatus == 0) {
                            DB::table('pan_txns')
                                ->where( 'order_ref_id' , @$data['orderid'])
                                ->update([
                                    'status' => 'failed',
                                    'failed_message' => 'webhook_not_receive'
                                ]);
                            $res = ['status' => 201, 'message' => 'Webhook not receive client side'];
                            return json_encode($res);
                        }

                    $txnStatus = self::moveOrderToProcessingByOrderId(@$userId->user_id, @$data['orderid']);
                    if ($txnStatus['status']) {
                        PanCardController::sendFirstCallabck($post, $userId->user_id, 'nsdl', 'P');
                    } else {
                        PanCardController::sendFirstCallabck($post, $userId->user_id, 'nsdl', 'F');
                        $res = ['status' => $txnStatus['status'], 'message' => $txnStatus['message']];
                        return json_encode($res);
                    }
                } else {
                    $res = ['status' => 202, 'message' => 'Callback data already exits.'];
                    return json_encode($res);
                }
            } else if ($data['status'] == 'F') {
                $userId = DB::table('pan_txns')->where([
                    'order_ref_id' => @$data['orderid']
                ])
                    ->select('user_id')
                    ->first();

                    PanCardController::sendSecondCallabck($post, $userId->user_id, 'nsdl');
                self::fundRefunded(@$userId->user_id, @$data['orderid'],  $data['message'],  $data['statuscode']);
            } else if ($data['status'] == 'S') {
                $userId = DB::table('pan_txns')->where([
                    'order_ref_id' => @$data['orderid']
                ])
                    ->update([
                        'status' => 'success',
                        'txn_status' => 'S'
                    ]);
                $userId = DB::table('pan_txns')->where([
                        'order_ref_id' => @$data['orderid']
                    ])
                        ->select('user_id')
                        ->first();
                    PanCardController::sendSecondCallabck($post, $userId->user_id, 'nsdl');
            }

            $status = 1;
        }

        if (isset($data['ServiceProviderId']) && isset($data['Status'])) {
            DB::table('pan_callbacks')
                ->insert([
                    'order_ref_Id' => @$data['ServiceProviderId'],  'type' => 'PAN',
                    'status' => @$data['Status'],  'response' => json_encode($data)
                ]);


                if (in_array($data['Status'], ['F', 'S'])) {
                    $userId = DB::table('pan_txns')->where([
                            'order_ref_id' => @$data['ServiceProviderId']
                        ])
                        ->select('user_id')
                        ->first();
                        if (empty($userId)) {
                            $res = ['status' => 404, 'message' => 'Order id is invalid.'];
                            return json_encode($res);
                        }
                }

            if ($data['Status'] == 'P') {
                $userId = DB::table('pan_agents')->where([
                    'psa_id' => @$data['VleID'],
                ])->select('user_id', 'email', 'mobile')
                    ->first();

                $panTxn = DB::table('pan_txns')->where([
                    'order_ref_id' => @$data['ServiceProviderId'],
                ])->count();
                if (!empty($userId) && $panTxn == 0) {
                    $isPhysical = 'pan_digital';

                    if (!empty($data['CouponType']) && $data['CouponType'] == 'Physical') {
                        $isPhysical = 'pan_physical';
                    }

                    $taxData = PanCardController::getFeeAndTaxs(@$userId->user_id, $isPhysical);

                    DB::table('pan_txns')
                        ->insert([
                            'txn_id' => $txnId,
                            'route_type' => 'PAN',
                            'order_ref_id' => @$data['ServiceProviderId'],
                            'app_no' => @$data['UTIapplicationNo'],
                            'ope_txn_id' => @$data['OperatorTxnId'],
                            'coupon_type' => @$data['CouponType'],
                            'txn_status' => @$data['status'],
                            'txn_type' => 'uti',
                            'status' => 'queued',
                            'psa_code' => @$data['VleID'],
                            'tax' => 0,
                            'fee' => $taxData['fee'],
                            'margin' => $taxData['margin'],
                            'service_id' => PAN_CARD_SERVICE_ID,
                            'user_id' => @$userId->user_id,
                        ]);
                        $WebHookStatus = 0;
                        $panCardWebHookResponse = PanCardController::sendFirstNsdlWebhook($post, $userId->user_id);
                        if (!$panCardWebHookResponse) {
                            DB::table('pan_txns')
                                ->where( 'order_ref_id' , @$data['ServiceProviderId'])
                                ->update([
                                    'status' => 'failed',
                                    'failed_message' => 'webhook_not_receive'
                                ]);

                            $res = ['status' => 201, 'message' => 'Webhook not receive client side'];
                            return json_encode($res);
                        } else {
                            if (@$panCardWebHookResponse->status == 200 && @$panCardWebHookResponse->orderRefId == $data['ServiceProviderId']) {
                                $WebHookStatus = 1;
                            }
                        }

                        if ($WebHookStatus == 0) {
                            DB::table('pan_txns')
                                ->where( 'order_ref_id' , @$data['ServiceProviderId'])
                                ->update([
                                    'status' => 'failed',
                                    'failed_message' => 'webhook_not_receive'
                                ]);
                            $res = ['status' => 201, 'message' => 'Webhook not receive client side'];
                            return json_encode($res);
                        }

                    $txnStatus =  self::moveOrderToProcessingByOrderId(@$userId->user_id,  @$data['ServiceProviderId']);
                    if ($txnStatus['status']) {
                        PanCardController::sendFirstCallabck($post, $userId->user_id, 'uti', 'P');
                    } else {
                        PanCardController::sendFirstCallabck($post, $userId->user_id, 'uti', 'F');
                        $res = ['status' => $txnStatus['status'], 'message' => $txnStatus['message']];
                        return json_encode($res);
                    }
                } else {
                    $res = ['status' => 202, 'message' => 'Callback data already exits.'];
                    return json_encode($res);
                }
            } else if ($data['Status'] == 'F') {
                $userId = DB::table('pan_txns')->where([
                    'order_ref_id' =>  @$data['ServiceProviderId']
                ])
                    ->select('user_id')
                    ->first();
                    PanCardController::sendSecondCallabck($post, $userId->user_id, 'uti');
                self::fundRefunded($userId->user_id, @$data['ServiceProviderId'],  @$data['Message'],  @$data['Status']);
            } else if ($data['Status'] == 'S') {
                $userId = DB::table('pan_txns')->where([
                    'order_ref_id' => @$data['ServiceProviderId']
                ])
                    ->update([
                        'status' => 'success',
                        'txn_status' => 'S'
                    ]);
                    $userId = DB::table('pan_txns')->where([
                        'order_ref_id' =>  @$data['ServiceProviderId']
                    ])
                        ->select('user_id')
                        ->first();
                    PanCardController::sendSecondCallabck($post, $userId->user_id, 'uti');
            }

            $status = 1;
        }
        if ($status == 1) {


            if (isset($data['psacode']) && isset($data['status'])) {
                $res = ['statuscode' => "000", 'message' => 'Success', 'txnid' => $txnId];
            } else {
                $res = ['StatusCode' => "000", 'Message' => 'Success', 'PartnerTxnID' => $txnId, 'VendorID' => $txnId];
            }
        } elseif ($status == 2) {
            $res = ['status' => 202, 'message' => 'Callback data already exits.'];
        } else {
            $res = ['status' => 201, 'message' => 'Callback data is invalid.'];
        }

        return json_encode($res);
    }


    /**
     * Method moveOrderToProcessingByOrderId
     *
     * @param $userId $userId [explicite description]
     * @param $orderRefId $orderRefId [explicite description]
     *
     * @return array
     */
    public static function moveOrderToProcessingByOrderId($userId, $orderRefId): array
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {

            $txn = CommonHelper::getRandomString('TXN', false);
            DB::select("CALL debitPanCardServiceAmount($userId, '" . $orderRefId . "', '" . $txn . "', @json)");
            $results = DB::select('select @json as json');
            $response = json_decode($results[0]->json, true);

            if ($response['status'] == '1') {
                $resp['status'] = true;
                $resp['message'] = 'Order processing successfully.';
            } else {
                if ($response['message'] == 'debit_balance_failed') {
                    self::moveOrderToProcessingByOrderId($userId, $orderRefId);
                }
                $resp['status'] = false;
                $resp['message'] = $response['message'];
            }
        } catch (\Exception $e) {
            $resp['status'] = false;
            $resp['message'] = 'Some errors : ' . $e->getMessage();
        }
        return $resp;
    }

    /**
     * Method fundRefunded
     *
     * @param $userId $userId [explicite description]
     * @param $orderRefId $orderRefId [explicite description]
     * @param $failedMessage $failedMessage [explicite description]
     * @param $statusCode $statusCode [explicite description]
     *
     * @return array
     */
    public static function fundRefunded($userId, $orderRefId, $failedMessage, $statusCode): array
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {

            $OrderData = DB::table('pan_txns')
                ->select('order_ref_id', 'user_id', 'service_id')
                ->where(['status' => 'pending',  'order_ref_id' => $orderRefId])
                ->first();

            if (isset($OrderData) && !empty($OrderData)) {
                $id = @DB::table('user_services')->where([
                    'user_id' => $userId,
                    'service_id' => PAN_CARD_SERVICE_ID
                ])->first()->id;
                $txn = CommonHelper::getRandomString('TXN', false);
                DB::select("CALL panStatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id, $id, 'failed', '" . $txn . "', '" . $failedMessage . "', '" . $statusCode . "','', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);

                if ($response['status'] == '1') {
                    $resp['status'] = true;
                    $resp['message'] = 'Fund refunded successfully.';
                } else {
                    $resp['status'] = false;
                    $resp['message'] = $response['message'];
                }
            }
        } catch (\Exception $e) {
            $resp['status'] = false;
            $resp['message'] = 'Some errors : ' . $e->getMessage();
        }
        return $resp;
    }
}
