<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\HashHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function accountBalance(Request $request)
    {

        $header = $request->header();
        $userSaltKey = CommonHelper::getUserSalt($request["auth_data"]['user_id']);

        //making hash
        $hash = HashHelper::init()->generate(HashHelper::ACC_BALANCE, $header['php-auth-user'][0], $userSaltKey);

        //Storage::put('accountSignature'.time().'.txt', print_r($hash, true));
        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';
        $aaray = array('user_id' => $request["auth_data"]['user_id'], 'xettle' => $hash, 'client' => $signature);
        //Storage::put('accountSignatureStore' . $request["auth_data"]['user_id'] . '_' . time() . '.txt', print_r($aaray, true));


        //match signature
        if (!hash_equals($hash, $signature)) {
            return ResponseHelper::failed('Your signature is invalid.');
        }


        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];


        $userService = DB::table('user_services')
            ->leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
            ->select(
                'global_services.service_name as serviceName',
               // 'global_services.service_id as serviceId',
                'user_services.service_account_number as accountNumber',
                DB::raw('FORMAT(user_services.transaction_amount, 2) as tradeBalance'),
                DB::raw('FORMAT(user_services.locked_amount, 2) as blockedBalance')
            )->where('user_services.service_id', $serviceId)
            ->where('user_services.user_id', $userId)->where('user_services.is_active', '1')->first();
        $User = DB::table('users')
            ->select(
                'account_number as accountNumber',
                DB::raw('FORMAT(transaction_amount, 2) as tradeBalance'),
                DB::raw('FORMAT(locked_amount, 2) as blockedBalance')
            )
            ->where('id', $userId)->where('is_active', '1')->first();
            if (isset($User)) {
                $User->tradeBalance = ($User->tradeBalance > 0 ) ? $User->tradeBalance : "0.00";
                $User->blockedBalance = ($User->blockedBalance > 0 ) ? $User->blockedBalance : "0.00";
                $data['primaryAccount'] = $User;
            } else {
                $data['primaryAccount'] = ["status" => false];
            }
            if (isset($userService)) {
                $userService->blockedBalance = ($userService->blockedBalance > 0 ) ? $userService->blockedBalance : "0.00";
                $data['payoutAccount'] = $userService;
            } else {
                $data['payoutAccount'] = ["status" => false];
            }
        if (!empty($data['primaryAccount']) || !empty($data['payoutAccount'])) {
            return ResponseHelper::success('Record fetched successfully.', $data);
        } else {
            return ResponseHelper::failed('No account found using this service Id ' . $serviceId, []);
        }
    }


        /**
     * Display a listing of the resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function dmtAccountBalance(Request $request)
    {

        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];


        $userService = DB::table('user_services')
            ->leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
            ->select(
                'global_services.service_name as serviceName',
                'user_services.service_account_number as accountNumber',
                DB::raw('FORMAT(user_services.transaction_amount, 2) as tradeBalance'),
                DB::raw('FORMAT(user_services.locked_amount, 2) as blockedBalance')
            )->where('user_services.service_id', $serviceId)
            ->where('user_services.user_id', $userId)
            ->where('user_services.is_active', '1')
            ->first();
        $User = DB::table('users')
            ->select(
                'account_number as accountNumber',
                DB::raw('FORMAT(transaction_amount, 2) as tradeBalance'),
                DB::raw('FORMAT(locked_amount, 2) as blockedBalance')
            )
            ->where('id', $userId)->where('is_active', '1')->first();
            if (isset($User)) {
                $User->tradeBalance = ($User->tradeBalance > 0 ) ? $User->tradeBalance : "0.00";
                $User->blockedBalance = ($User->blockedBalance > 0 ) ? $User->blockedBalance : "0.00";
                $data['primaryAccount'] = $User;
            } else {
                $data['primaryAccount'] = ["status" => false];
            }
            if (isset($userService)) {
                $userService->blockedBalance = ($userService->blockedBalance > 0 ) ? $userService->blockedBalance : "0.00";
                $data['serviceAccount'] = $userService;
            } else {
                $data['serviceAccount'] = ["status" => false];
            }
        if (!empty($data['primaryAccount']) || !empty($data['serviceAccount'])) {
            return ResponseHelper::success('Record fetched successfully.', $data);
        } else {
            return ResponseHelper::failed('No account found using this service Id ' . $serviceId, []);
        }
    }

    /**
     * Display a listing of the resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function accountInfo(Request $request)
    {

        $userId = $request["auth_data"]['user_id'];

        $userService = DB::table('user_services')
            ->leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
            ->select(
                'global_services.service_name as serviceName',
                'user_services.service_account_number as accountNumber',
                DB::raw('FORMAT(user_services.transaction_amount, 2) as tradeBalance'),
                DB::raw('FORMAT(user_services.locked_amount, 2) as blockedBalance')
            )
            ->where('user_services.user_id', $userId)
            ->whereNotNull('user_services.service_account_number')
            ->where('user_services.is_active', '1')
            ->get();
        $User = DB::table('users')
            ->select(
                'account_number as accountNumber',
                DB::raw('FORMAT(transaction_amount, 2) as tradeBalance'),
                DB::raw('FORMAT(locked_amount, 2) as blockedBalance')
            )
            ->where('id', $userId)->where('is_active', '1')->first();
            if (isset($User)) {
                $User->tradeBalance = ($User->tradeBalance > 0 ) ? $User->tradeBalance : "0.00";
                $User->blockedBalance = ($User->blockedBalance > 0 ) ? $User->blockedBalance : "0.00";
                $data['primaryAccount'] = $User;
            } else {
                $data['primaryAccount'] = ["status" => false];
            }
            $seviceData = [];
            if (isset($userService)) {
                foreach ($userService as $key => &$val) {
                        $val->blockedBalance = ($val->blockedBalance > 0 ) ? $val->blockedBalance : "0.00";
                        $seviceData[$key] =  $val;
                }
                $data['serviceAccount'] =  $seviceData;
            } else {
                $data['serviceAccount'] = ["status" => false];
            }

        if (!empty($data['primaryAccount']) || !empty($data['serviceAccount'])) {
            return ResponseHelper::success('Record fetched successfully.', $data);
        } else {
            return ResponseHelper::failed('No account found' []);
        }
    }
}
