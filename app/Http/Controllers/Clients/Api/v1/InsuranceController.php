<?php
namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Validations\InsuranceValidation as Validations;
use App\Services\InsuranceService;
use App\Helpers\CommonHelper;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class InsuranceController extends Controller
{
	protected const DOCUMENT_URI = '/iam-pos/api/v1/user/auth/partner';

	public function index(Request $request, InsuranceService $serv)
	{
		try {
			$userId = Auth::user()->id;
			if (!CommonHelper::isServiceActive($userId, INSURANCE_SERVICE_ID)) {
				return ResponseHelper::failed('Service is not active');
			}
			$user = User::where('users.id', $userId)->select('users.email','users.mobile','business_infos.pan_number','business_infos.pan_owner_name')->leftJoin('business_infos','business_infos.user_id','=','users.id')->where('users.is_active', '1')->first();
			if (empty($user)) {
				return ResponseHelper::failed('User is not active');
			}
			$insertdata = [
				'user_id' =>$userId,
				'name' => trim($user->pan_owner_name),
				'email' => trim($user->email),
				'mobile' => trim($user->mobile),
				'pan' => trim($user->pan_number),
				'agentId' => 'INS' . time()
			];

			$checkMobile = DB::table('insurance_agents')->select('agentId')
				->orwhere('mobile', $insertdata['mobile'])
				->orwhere('pan', $insertdata['pan'])
				->first();
			if (empty($checkMobile)) {
				
				$insertDB = DB::table('insurance_agents')->insert($insertdata);
			}
			
			$parameters = [
				"referenceAuthId" => !empty($user->mobile) ? $user->mobile : '',
				"mobile" => !empty($user->mobile) ? $user->mobile : ''
			];

			$resp = $serv->init($parameters, self::DOCUMENT_URI, 'insurance', $userId, 'yes');
			return ResponseHelper::success('User Onboarded Successfully', $resp);
			
			
			
			
		}catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
	}

	public function redirectUrl(Request $request)
	{
		if (!empty($request->insToken)) {
			$url = env('INS_REDIRECT_URL') . '?one-time-token=' . $request->insToken;
			return \Redirect::to($url);
		} else {
			return ResponseHelper::failed('Token is missing');
		}
	}

	public function init(Request $request, InsuranceService $serv)
	{
		try {
			$validation = new Validations($request);
			$validator = $validation->OTTGenerate();
			//print_r($request->user());exit;
			if ($validator->fails()) {
				$message = json_decode(json_encode($validator->errors()), true);
				return ResponseHelper::missing($message);
			} else {
				$userId = $request['auth_data']['user_id'];
				if (!CommonHelper::isServiceActive($userId, INSURANCE_SERVICE_ID)) {
					return ResponseHelper::failed('Service is not active');
				}
				$user = User::where('id', $userId)->where('is_active', '1')->first();
				if (empty($user)) {
					return ResponseHelper::failed('User is not active');
				}
				$agentData = DB::table('insurance_agents')->where('agentId',$request->agentId)->first();
				$parameters = [
					"referenceAuthId" => !empty($agentData->mobile) ? $agentData->mobile : '',
					"mobile" => !empty($agentData->mobile) ? $agentData->mobile : ''
				];

				$resp = $serv->init($parameters, self::DOCUMENT_URI, 'insurance', $request['auth_data']['user_id'], 'yes');
				//print_r($resp['response']['insToken']);
				if (!empty($resp['response']['insToken'])) {
					$url['redirectUrl'] = env('INS_REDIRECT_URL') . '?one-time-token=' . $resp['response']['insToken'];
					return ResponseHelper::success('Request send successfully', $url);
				}
				return ResponseHelper::success('Request send successfully', $resp);
			}
		}catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
	}

	public function onBoarding(Request $request)
	{
		try {
			$validation = new Validations($request);
			$validator = $validation->OnBoard();
			if ($validator->fails()) {
				$message = json_decode(json_encode($validator->errors()), true);
				return ResponseHelper::missing($message);
			} else {
				$insertdata = [
					'user_id' =>$request['auth_data']['user_id'],
					'name' => trim($request->name),
					'email' => trim($request->email),
					'mobile' => trim($request->mobile),
					'pan' => trim($request->panNo),
					'agentId' => 'INS' . time()
				];

				$checkMobile = DB::table('insurance_agents')->select('agentId')
					->orwhere('mobile', $insertdata['mobile'])
					->orwhere('pan', $insertdata['pan'])
					->first();
				if (!empty($checkMobile)) {
					return ResponseHelper::success('Record already exist', ['agentId' => $checkMobile->agentId]);
				} else {
					$insertDB = DB::table('insurance_agents')->insert($insertdata);
					if ($insertDB) {
						return ResponseHelper::success('User Onboarded Successfully', ['agentId' => $insertdata['agentId']]);
					} else {
						return ResponseHelper::failed('Please try after sometimes');
					}
				}
			}
		}catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
	}
}