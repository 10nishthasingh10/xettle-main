<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AutoCollectApisController extends Controller
{

    public function __construct()
    {
        $this->middleware('IsAdmin');
    }

    /**
     * Serrch by UTR
     */
    public function searchByUTR($utr)
    {

        try {

            $queryString = !empty(Request::getQueryString()) ? "?" . Request::getQueryString() : '';

            $userId = Auth::user()->id;
            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();
            $result = $vanHelper->vanManager([], '/cac/v1/searchUTR/' . $utr . $queryString, $userId, 'GET', 'searchByUTR');


            if ($result['code'] == 200) {

                $response = json_decode($result['response']);

                if ($response->subCode === "200") {
                    return ResponseHelper::success("API Response", $response);
                }

                return ResponseHelper::failed($response->message);
            }

            return ResponseHelper::failed("Search transaction by UTR Failed.");
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }


    /**
     * Fetch Payment by Cashfree Ref Id
     */
    public function searchByRefId($refId)
    {

        try {

            $userId = Auth::user()->id;
            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();
            $result = $vanHelper->vanManager([], '/cac/v1/fetchPaymentById/' . $refId, $userId, 'GET', 'searchByRefId');

            if ($result['code'] == 200) {

                $response = json_decode($result['response']);

                if ($response->subCode === "200") {
                    return ResponseHelper::success("API Response", $response);
                }

                return ResponseHelper::failed($response->message);
            }

            return ResponseHelper::failed("Search Transaction Failed.");
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }


    /**
     * Recent Payments for Virtual Account Id/Virtual VPA Id
     */
    public function searchByAccId($accId)
    {

        try {

            $queryString = !empty(Request::getQueryString()) ? "?" . Request::getQueryString() : '';

            $userId = Auth::user()->id;
            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();
            $result = $vanHelper->vanManager([], '/cac/v1/payments/' . $accId . $queryString, $userId, 'GET', 'searchByAccId');


            if ($result['code'] == 200) {

                $response = json_decode($result['response']);

                if ($response->subCode === "200") {
                    return ResponseHelper::success("API Response", $response);
                }

                return ResponseHelper::failed($response->message);
            }

            return ResponseHelper::failed("Search Transaction Failed.");
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }


    /**
     * Recent Payments
     */
    public function recentPayments()
    {

        try {

            $queryString = !empty(Request::getQueryString()) ? "?" . Request::getQueryString() : '';

            $userId = Auth::user()->id;
            //creating object
            $vanHelper = new CashfreeAutoCollectHelper();
            $result = $vanHelper->vanManager([], '/cac/v1/payments' . $queryString, $userId, 'GET', 'recentPayments');


            if ($result['code'] == 200) {

                $response = json_decode($result['response']);

                return ResponseHelper::success("API Response", $response);
            }

            return ResponseHelper::failed("Search Transaction Failed.");
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }
}
