<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\BusinessInfo;
use App\Models\Service;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VanController extends Controller
{
    /**
     * Controller for Create van
     */
    public function createVan(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => "required",
            ]
        );

        if ($validator->fails()) {
            $message = ($validator->errors()->get('user_id'));
            return ResponseHelper::missing($message);
        }


        try {
            $userId = decrypt($request->user_id);
        } catch (Exception $e) {
            //$message = $e->getMessage();
            return ResponseHelper::missing("Invalid token value.");
        }


        //fetching user details
        $userInfo = User::select('id', 'account_number')
            ->where('is_active', 1)
            ->find($userId);


        if (empty($userInfo)) {
            return ResponseHelper::failed("Invalid user ID.");
        }


        //fetching van and bank details
        $businessInfo = DB::table('business_infos')
            ->select('id', 'business_name', 'user_id', 'van_acc_id', 'van', 'name', 'email', 'mobile', 'account_number', 'ifsc')
            ->where('is_active', '1')
            ->where('is_kyc_updated', '1')
            ->where('is_bank_updated', '1')
            ->where('user_id', $userId)
            ->first();

        if (empty($businessInfo)) {
            return ResponseHelper::failed("KYC or Bank Info is pending.");
        }

        //check is VAN already created
        else if (!empty($businessInfo->van_acc_id)) {
            return ResponseHelper::failed("VAN already created.");
        }


        $userBankInfo = DB::table('user_bank_infos')
            ->select('*')
            ->where('is_active', '1')
            ->where('is_verified', '1')
            ->where('user_id', $userId)
            ->orderBy('id', 'ASC')
            ->first();

        if (empty($userBankInfo)) {
            return ResponseHelper::failed("Bank accounts are not updated yet.");
        }


        //getting min and max value from database
        $globalService = Service::select('id', 'service_id')
            ->where('service_slug', 'van_collect')->first();

        if (!empty($globalService)) {

            $globalProducts = $globalService->globalProducts->first();

            if (!empty($globalProducts)) {
                $minAmount = $globalProducts->min_order_value;
                $maxAmount = $globalProducts->max_order_value;
            }
        }


        //variable for manage multivan
        // $isMultiVan = 1;


        //customer details array
        // $virtualAccId = strtoupper("MA" . substr($businessInfo->mobile, -3) . substr(str_replace([' ', '.'], '', $businessInfo->name), 0, 3));
        $virtualAccId = substr($userInfo->account_number, -6);
        // $virtualAccId = substr($userInfo->account_number, 0, 3) . substr($userInfo->account_number, -7);

        $params = [
            "vAccountId" => $virtualAccId,
            "name" => $businessInfo->business_name,
            "phone" => "9876543210",
            "email" => "payments.cf@example.com",
            "remitterAccount" => $userBankInfo->account_number,
            "remitterIfsc" => $userBankInfo->ifsc,
        ];


        if (!empty($minAmount) && !empty($maxAmount)) {
            $params["minAmount"] = (float) $minAmount;
            $params["maxAmount"] = (float) $maxAmount;
        }

        if (!empty($isMultiVan)) {
            $params['createMultiple'] = $isMultiVan;
        }


        //creating object
        $vanHelper = new CashfreeAutoCollectHelper();


        $result = $vanHelper->vanManager($params, '/cac/v1/createVA', $userId, 'POST', 'createVan');


        if ($result['code'] == 200) {

            $vanNumber = '';


            $cashfreeResponse = json_decode($result['response']);

            //when response is success
            if ($cashfreeResponse->subCode === "200") {


                if (!empty($isMultiVan)) {

                    $tempVanAccounts = [];

                    foreach ($cashfreeResponse->data as $row) {
                        $tempVanAccounts[] = $row;
                    }

                    $vanNumber = $tempVanAccounts[0]->accountNumber;

                    //update record
                    BusinessInfo::where('user_id', $userId)->update([
                        'van' => $tempVanAccounts[0]->accountNumber,
                        'van_ifsc' => $tempVanAccounts[0]->ifsc,
                        'van_2' => $tempVanAccounts[1]->accountNumber,
                        'van_2_ifsc' => $tempVanAccounts[1]->ifsc,
                        'van_acc_id' => $virtualAccId,
                        'van_status' => '1'
                    ]);
                } else {
                    //update record
                    $vanNumber = $cashfreeResponse->data->accountNumber;
                    BusinessInfo::where('user_id', $userId)->update([
                        'van' => $cashfreeResponse->data->accountNumber,
                        'van_ifsc' => $cashfreeResponse->data->ifsc,
                        'van_acc_id' => $virtualAccId,
                        'van_status' => '1'
                    ]);
                }


                return ResponseHelper::success("VAN created successfully.", ['van' => $vanNumber]);
            }


            return ResponseHelper::failed($cashfreeResponse->message, $cashfreeResponse);
        }


        return ResponseHelper::failed("VAN Creation Failed.", $result);
    }



    /**
     * Change VAN Status
     */
    public function changeVanStatus(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => "required",
                // 'status' => "required|in:ACTIVE,INACTIVE",
            ]
        );

        if ($validator->fails()) {
            $message = ($validator->errors()->get('user_id'));
            return ResponseHelper::missing($message);
        }



        try {
            $userId = decrypt($request->user_id);
        } catch (Exception $e) {
            //$message = $e->getMessage();
            return ResponseHelper::missing("Invalid token value.");
        }


        //fetching user details
        $userInfo = User::select('*')
            ->where('is_active', 1)
            ->find($userId);


        if (empty($userInfo)) {
            return ResponseHelper::failed("Invalid user ID.");
        }


        //fetching van id details
        $businessInfo = BusinessInfo::select('van_acc_id', 'van_status')
            ->where('is_active', '1')
            ->where('is_kyc_updated', '1')
            ->where('is_bank_updated', '1')
            ->where('user_id', $userId)
            ->first();

        if (empty($businessInfo)) {
            return ResponseHelper::failed("Please create your VAN.");
        }


        if ($businessInfo->van_status === '1') {
            $status = 'INACTIVE';
        } else {
            $status = 'ACTIVE';
        }


        $params = [
            "vAccountId" => $businessInfo->van_acc_id,
            "status" => $status,
        ];


        //creating object
        $vanHelper = new CashfreeAutoCollectHelper();


        $result = $vanHelper->vanManager($params, '/cac/v1/changeVAStatus', $userId, 'POST', 'changeVanStatus');


        if ($result['code'] == 200) {

            $cashfreeResponse = json_decode($result['response']);

            //when response is success
            if ($cashfreeResponse->subCode === "200") {

                //insert record
                BusinessInfo::where('user_id', $userId)->update([
                    'van_status' => ($status === "ACTIVE") ? '1' : '0'
                ]);

                return ResponseHelper::success($cashfreeResponse->message, ['status' => $status]);
            }


            return ResponseHelper::failed($cashfreeResponse->message, $cashfreeResponse);
        }


        return ResponseHelper::failed("VAN Creation Failed.", $result);
    }




    /**
     * Change VAN Status
     */
    public function changeVanLimit(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => "required",
                'min_amt' => "required|numeric|min:1",
                'max_amt' => "required|numeric|max:1000000000",
            ]
        );

        if ($validator->fails()) {
            $message = json_encode($validator->errors()->all());
            return ResponseHelper::missing($message);
        }



        try {
            $userId = decrypt($request->user_id);
            $minAmount = $request->min_amt;
            $maxAmount = $request->max_amt;
        } catch (Exception $e) {
            return ResponseHelper::missing("Invalid token value.");
        }


        //fetching user details
        $userInfo = User::select('*')
            ->where('is_active', 1)
            ->find($userId);


        if (empty($userInfo)) {
            return ResponseHelper::failed("Invalid user ID.");
        }


        //fetching van id details
        $businessInfo = BusinessInfo::select('van_acc_id', 'van_status')
            ->where('is_active', '1')
            ->where('is_kyc_updated', '1')
            ->where('is_bank_updated', '1')
            ->where('user_id', $userId)
            ->first();

        if (empty($businessInfo)) {
            return ResponseHelper::failed("Please create your VAN.");
        }


        $params = [
            "vAccountId" => $businessInfo->van_acc_id,
            "minAmount" => $minAmount,
            "maxAmount" => $maxAmount
        ];


        //creating object
        $vanHelper = new CashfreeAutoCollectHelper();


        $result = $vanHelper->vanManager($params, '/cac/v1/editVA', $userId, 'POST', 'changeVanLimit');


        if ($result['code'] == 200) {

            $cashfreeResponse = json_decode($result['response']);

            //when response is success
            if ($cashfreeResponse->subCode === "200") {
                return ResponseHelper::success($cashfreeResponse->message, ['status' => "Updated"]);
            }


            return ResponseHelper::failed($cashfreeResponse->message, $cashfreeResponse);
        }


        return ResponseHelper::failed("VAN Updation Failed.", $result);
    }



    /**
     * getVanDetails
     */
    public function getVanDetails($vId)
    {

        if (empty($vId)) {
            return ResponseHelper::missing('Please provide a virtual account.');
        }


        $params = [];


        //creating object
        $vanHelper = new CashfreeAutoCollectHelper();


        $result = $vanHelper->vanManager($params, '/cac/v1/va/' . $vId, 0, 'GET', 'getVanDetails');


        if ($result['code'] == 200) {

            $cashfreeResponse = json_decode($result['response']);

            return ResponseHelper::success($cashfreeResponse->message, $cashfreeResponse);
        }

        return ResponseHelper::failed("Operation Failed.", $result);
    }
}
