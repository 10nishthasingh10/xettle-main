<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Recharge;
use App\Services\RechargeService;
use Exception;
use Auth;
use Illuminate\Support\Facades\Validator;
use Validations\RecharageValidation as Validations;
/**
 * RechargeController
 */
class RechargeController extends Controller
{

	protected const DOCUMENT_RECHARGE_URI = '/xettlerecharge/api';

	    /**
     * construct function init Client Key,Client Secret and Base Url
     */
    public function __construct()
    {
        $this->baseUrl = env('AEPS_BASE_URL');
        $this->key = base64_decode(env('AEPS_SECURITY_KEY'));
        $this->header = array("securitykey:" . $this->key, "Content-Type:application/json");
    }

	public function index()
	{
		$data['page_title'] = 'Recharge List';
		$data['view'] = 'admin.recharge.rechargeList';
		$data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
		return view($data['view'],$data);
	}

	/**
     * rechargeStatusDiscrepancy function
     *
     * @param Request $request
     * @return void
     */
    public function rechargeStatusDiscrepancy(Request $request, $refId = '', RechargeService $rechageService)
    {
        try {

            if (!empty($refId)) {
                $parameters = [];

                $response = $rechageService->init($parameters, self::DOCUMENT_RECHARGE_URI . '/StatusCheck/GetStatus/'.$refId, 'recharge', @$request['auth_data']['user_id'], 'yes', 'recharge', 'GET');
                
                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == '000') {

                    $resp =  self::rechargeResponseFormat($response['response']['response']);
                    return ResponseHelper::success('Record fetched successfully.', @$resp);
                }

                else if (isset($response['response']['response']->statuscode) && in_array($response['response']['response']->statuscode, ['002', '999'])) {
                    $resp =  self::rechargeResponseFormat($response['response']['response']);
                    return ResponseHelper::pending($response['response']['response']->message, @$resp);
                }
                 
                else {

                    $failedMessage = "No record found";
                    if (is_string(@$response['response']['response']->message)) {
                        $failedMessage = $response['response']['response']->message;
                    }
                    if (isset($response['response']['response']->remarks)) {
                        $failedMessage = $response['response']['response']->remarks;
                    }
                  
                    $resp =  self::rechargeResponseFormat($response['response']['response']);
                    return ResponseHelper::failed($failedMessage, $resp);
                }

            } else {
                return ResponseHelper::failed('Client reference id is required.');
            }
        } catch (Exception $e) {
            return ResponseHelper::failed(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }

		/**
     * rechargeStatusUpdate function
     *
     * @param Request $request
     * @return void
     */
    public function rechargeStatusUpdate(Request $request)
    {
        try {
			if(Auth::user()->hasRole('super-admin'))
			{
				/**  Add Transaction Details */

				$validator = Validator::make($request->all(),[
					'orderId' => "required",
					'status' => "required",
					'agentId' => "required",
					//'failedMessage' => "nullable,required",
					]	
				);

				if ($validator->fails()) {
					$resp = json_decode(json_encode($validator->errors()), true);
					return ResponseHelper::failed(@array_values($resp)[0][0]);
				}

				if ($request->status == 'processed') {
                    $count = DB::table('recharges')
                        ->where('order_ref_id', $request->orderId)
                        ->where('status', 'processing')
                        ->count();
                     if ($count) {
                        $data = [
                            'stan_no' => @$request->stanNo,
                            'status' => 'processed',
                            'bank_reference' =>  @$request->utr,
                        ];

                        Recharge::updateRecord( ['order_ref_id' => $request->orderId], $data);
                        $status['status'] = 1;
                    } else {
                        $status = Recharge::fundRefundedAdmin($request->agentId,
                        $request->orderId, $request->failedMessage, 'recharge_amount_dsuccess', 'dispute');
                    }
				} else {
					$count = DB::table('recharges')
							->where('order_ref_id', $request->orderId)
							->where('status', 'processing')
							->count();
					if ($count) {
						$status =  Recharge::fundRefundedAdmin($request->agentId,
						$request->orderId, $request->failedMessage,
						'recharge_amount_refund', 'failed');
					} else {
						$status =  Recharge::fundRefundedAdmin($request->agentId,
						$request->orderId, '',
						'recharge_amount_reversed', 'reversed');
					}
			

				}

				if ($status['status']) {
					return ResponseHelper::success('Status change successfull');
				} else {
					return ResponseHelper::failed($status['message']);
				}
			}

        } catch (Exception $e) {
            return ResponseHelper::failed(SOMETHING_WENT_WRONG);
        }
    }


	public static function rechargeResponseFormat($response)
    {

        if (!empty($response->status)) {
            return  [
                "clientRefId"=> @$response->clientrefid,
                "amount"=> $response->amount,
                "txnId"=> $response->txnid,
                "venderId"=> $response->venderid,
                "customerNumber"=> $response->customernumber,
                "remarks"=> $response->remarks,
            ];
        } else {
            return  [];
        }
    }

}