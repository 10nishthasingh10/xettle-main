<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class PennyDropApiHelper
{

    const ROOT_IPAY = 'ipay';
    const ROOT_EBUZ = 'ebz';

    public function initPennyDropEb($bankDetails, $checkInDb = true)
    {
        if ($checkInDb) {
            //30 days previous data
            $date = date('Y-m-d H:i:s', strtotime("-7 day", strtotime(date('Y-m-d H:i:s'))));
            $apiData = DB::table('acc_validation_logs')
                ->where('account_no', $bankDetails->account_number)
                ->where('ifsc', $bankDetails->ifsc)
                ->where('root_type', 'ebz')
                ->whereDate('created_at', '>', $date)
                ->orderBy('id', 'DESC')
                ->first();

            if (!empty($apiData)) {
                $result['code'] = 200;
                $result['response'] = $apiData->response;

                $result['root_type'] = 'ebz';
                $result['flag'] = 'db';

                return $result;
            }
        }

        $vanHelper = new EasebuzzInstaCollectHelper();

        $requestNumber = CommonHelper::getRandomString('REQPD');

        $params = [
            "key" => $vanHelper->getKey(),
            "account_no" => $bankDetails->account_number,
            "ifsc" => $bankDetails->ifsc,
            "unique_request_number" => $requestNumber,
        ];

        $authParams['account_number'] = $bankDetails->account_number;
        $authParams['ifsc'] = $bankDetails->ifsc;

        $result = $vanHelper->apiCaller($params, '/beneficiaries/bank_account/verify/', $authParams);
        $result['root_type'] = 'ebz';
        $result['flag'] = 'api';

        return $result;
    }



    /**
     * Return Bank Info from DB when record found not more than 7 days old
     */
    public static function getBankInfo($accNumber, $ifsc, $root)
    {
        switch ($root) {
            case self::ROOT_IPAY:
                $date = date('Y-m-d H:i:s', strtotime("-7 day", time()));

                $bankInfo = DB::table('acc_validation_logs')
                    ->select('*')
                    ->where('root_type', $root)
                    ->where('account_no', $accNumber)
                    ->where('ifsc', $ifsc)
                    ->where('status', 'valid')
                    ->whereDate('created_at', '>', $date)
                    ->orderByDesc('id')
                    ->first();
                break;

            case self::ROOT_EBUZ:
                break;

            default:
                return null;
                break;
        }


        if (!empty($bankInfo)) {
            return ["response" => ($bankInfo->response), "error" => "", 'code' => 200, 'refNo' => $bankInfo->ref_no, 'apiLogLastId' => 0];;
        }

        return null;
    }
}
