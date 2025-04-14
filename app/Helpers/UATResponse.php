<?php
namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Integration;
use CommonHelper;
use Storage;
use App\Models\TransactionHistory;
class UATResponse
{
    public static function response($serviceName, $serviceMethod, $returnResponse, $transId, $params = array())
    {
        $message = "";
        $statusCode = "";
        $data = [];
        $status = "";
        switch ($serviceName) {
            case 'aeps':
                switch ($serviceMethod) {
                    case 'merchantOnBoard':
                        if($returnResponse == 'success') {
                            $message = "000: Merchant onboard successfully.";
                            $merchantCode = "MC000123123";
                            $data['data'] = [
                                'merchantCode'  => $merchantCode,
                                'firstName'     => isset($params['first_name']) ? $params['first_name'] : 'Anil',
                                'middleName'    => isset($params['m_name']) ? $params['m_name'] : '',
                                'lastName'      => isset($params['last_name']) ? $params['last_name'] : '',
                                'mobile'        => isset($params['mobile']) ? $params['mobile'] : '',
                                'email'         => isset($params['emailid']) ? $params['emailid'] : '',
                                'pinCode'       => isset($params['pincode']) ? $params['pincode'] : '',
                                'dob'           => isset($params['dob']) ? $params['dob'] : '',
                                'aadhaarNo'     => isset($params['aadharnumber']) ? $params['aadharnumber'] : '',
                                'panNo'         => isset($params['panno']) ? $params['panno'] : '',
                                'status'        => 'A',
                                'remarks'       => '',
                                'service'       => 'ICICI',
                                'cd'            => 'True',
                                'ap'            => 'True',
                                'be'            => 'True',
                                'cw'            => 'True',
                                'ms'            => 'True',
                                'eKycStatus'    => 'False',
                                'state'         => CommonHelper::stateOrDistrictName($params['state'], 'state'),
                                'district'      => CommonHelper::stateOrDistrictName($params['district'], 'district'),
                                "shopName"      => isset($params['shopName']) ? $params['shopName'] : '',
                                "shopAddress"   => isset($params['shopAddress']) ? $params['shopAddress'] : '',
                                "shopPin"       => isset($params['shopPin']) ? $params['shopPin'] : ''
                            ];
                        }else if($returnResponse == 'update_success') {
                            $message = "000: Merchant updated successfully.";
                            $data['data'] = [
                                'merchantCode'  => $transId,
                                'firstName'     => isset($params['first_name']) ? $params['first_name'] : 'Anil',
                                'middleName'    => isset($params['m_name']) ? $params['m_name'] : '',
                                'lastName'      => isset($params['last_name']) ? $params['last_name'] : '',
                                'mobile'        => isset($params['mobile']) ? $params['mobile'] : '',
                                'email'         => isset($params['emailid']) ? $params['emailid'] : '',
                                'pinCode'       => isset($params['pincode']) ? $params['pincode'] : '',
                                'dob'           => isset($params['dob']) ? $params['dob'] : '',
                                'aadhaarNo'     => isset($params['aadharnumber']) ? $params['aadharnumber'] : '',
                                'panNo'         => isset($params['panno']) ? $params['panno'] : '',
                                'status'        => 'A',
                                'remarks'       => '',
                                'service'       => 'ICICI',
                                'cd'            => 'True',
                                'ap'            => 'True',
                                'be'            => 'True',
                                'cw'            => 'True',
                                'ms'            => 'True',
                                'eKycStatus'    => 'False',
                                'state'         => CommonHelper::stateOrDistrictName($params['state'], 'state'),
                                'district'      => CommonHelper::stateOrDistrictName($params['district'], 'district'),
                                "shopName"      => isset($params['shopName']) ? $params['shopName'] : '',
                                "shopAddress"   => isset($params['shopAddress']) ? $params['shopAddress'] : '',
                                "shopPin"       => isset($params['shopPin']) ? $params['shopPin'] : ''
                            ];
                        }else if($returnResponse == 'failed') {
                            $message = "001: {*} Email Id should be unique.";
                        }
                    break;

                    case 'sendOTP':
                        if($returnResponse == 'success') {
                            $message = "000: OTP send successfully.";
                            $data['data'] = [
                                "token" => "794d213a-3187-4a66-9ad6-b151451d314c",
                                "requestId" => "e31abb0c-7066-40d9-9350-881b25b398a7",
                                "primaryId" => "",
                                "info1" => "",
                                "info2" => ""
                            ];
                        }else if($returnResponse == 'failed') {
                            $message = "001: Ekyc already Verified, $transId";
                        }
                    break;

                    case 'validateOTP':
                        if($returnResponse == 'success') {
                            $message = "000: Validate OTP successfully.";
                            $data['data'] = [
                                "token" => "794d213a-3187-4a66-9ad6-b151451d314c",
                                "requestId" => "e31abb0c-7066-40d9-9350-881b25b398a7",
                                "primaryId" => "",
                                "info1" => "",
                                "info2" => ""
                            ];
                        }else if($returnResponse == 'failed') {
                            $message = "001: Invalid OTP ,  $transId";
                        }
                    break;

                    case 'resendOTP':
                        if($returnResponse == 'success') {
                            $message = "000: OTP re-send successfully.";
                            $data['data'] = [
                                "token" => "794d213a-3187-4a66-9ad6-b151451d314c",
                                "requestId" => "e31abb0c-7066-40d9-9350-881b25b398a7",
                                "primaryId" => "43545555",
                                "info1" => "",
                                "info2" => ""
                            ];
                        }else if($returnResponse == 'failed') {
                            $message = "001: Invalid Details,  $transId";
                        }
                    break;

                    case 'ekycBioMetric':
                        if($returnResponse == 'success') {
                            $message = "000: EKYC Completed Successfully";
                            $data['data'] = [];
                        }else if($returnResponse == 'failed') {
                            $message = "10005: Invalid Details in BioMetric API, $transId";
                        }
                    break;

                    case 'getBalance':
                        $routeType = 'ICICI';
                        if($params['routetype'] == 'sbm') {
                            $routeType = 'sbm';
                        }
                        if($returnResponse == 'success') {
                            $message = "000: Balance fetched successfully.";
                            $data['data'] = [
                                'clientRefNo'           => isset($params['clientrefno']) ? $params['clientrefno'] : 'ABE7cbc183117921664800',
                                'routeType'             => $routeType,
                                'bankiin'               => isset($params['bankiin']) ? $params['bankiin'] : '606985',
                                'stanNo'                => 'XTL92DE554FB0B6483A98152C89456264EF',
                                'rrn'                   => '127115858160',
                                'bankMessage'           => 'Request Completed',
                                'bankCode'              => '10000',
                                'merchantCode'          => $transId,
                                'merchantMobile'        => isset($params['mobile']) ? $params['mobile'] : '9651807990',
                                'aadhaarNumber'         => CommonHelper::aadhaarMasking($params['aadharnumber']),
                                'transactionType'       => 'BE',
                                'transactionDateTime'   => date('Y-m-d H:i:s'),
                                'transactionAmount'     => '0',
                                'availableBalance'      => "1000"
                            ];
                        }else if($returnResponse == 'failed') {
                            $message = "001: FAILURE";
                            $data['data'] = [
                                'clientRefNo'           => isset($params['clientrefno']) ? $params['clientrefno'] : 'ABE7cbc183117921664800',
                                'routeType'             => $routeType,
                                'bankiin'               => isset($params['bankiin']) ? $params['bankiin'] : '606985',
                                'stanNo'                => 'XTL92DE554FB0B6483A98152C89456264HF',
                                'rrn'                   => '',
                                'bankMessage'           => 'You have exceeded daily limit of Balance Inquiry transactions',
                                'bankCode'              => '10004',
                                'merchantCode'          => $transId,
                                'merchantMobile'        => isset($params['mobile']) ? $params['mobile'] : '9651807990',
                                'aadhaarNumber'         => CommonHelper::aadhaarMasking($params['aadharnumber']),
                                'transactionType'       => 'BE',
                                'transactionDateTime'   => date('Y-m-d H:i:s'),
                                'transactionAmount'     => '0',
                                'availableBalance'      => ""
                            ];
                        }
                    break;

                    case 'withdrawal':
                        $routeType = 'ICICI';
                        if($params['routetype'] == 'sbm') {
                            $routeType = 'sbm';
                        }
                        if($returnResponse == 'success') {
                            $message = "000: Amount withdrawal successfully.";
                            $data['data'] = [
                                'clientRefNo'           => isset($params['clientrefno']) ? $params['clientrefno'] : 'ACW7cbc183117921664800',
                                'routeType'             => $routeType,
                                'bankiin'               => isset($params['bankiin']) ? $params['bankiin'] : '606985',
                                'stanNo'                => 'XTL92DE554FB0B6483A98152C89456264EF',
                                'rrn'                   => '127115858160',
                                'bankMessage'           => 'Request Completed',
                                'bankCode'              => '10000',
                                'merchantCode'          => $transId,
                                'merchantMobile'        => isset($params['mobile']) ? $params['mobile'] : '9651807990',
                                'aadhaarNumber'         => CommonHelper::aadhaarMasking($params['aadharnumber']),
                                'transactionType'       => 'CW',
                                'transactionDateTime'   => date('Y-m-d H:i:s'),
                                'transactionAmount'     => '100',
                                'availableBalance'      => "900"
                            ];
                        }else if($returnResponse == 'failed') {
                            $message = "001: Failure";
                            $data['data'] = [
                                'clientRefNo'           => isset($params['clientrefno']) ? $params['clientrefno'] : 'ACW7cbc183117921664800',
                                'routeType'             => $routeType,
                                'bankiin'               => isset($params['bankiin']) ? $params['bankiin'] : '606985',
                                'stanNo'                => 'XTL92DE554FB0B6483A98152C89456264EF',
                                'rrn'                   => '127115858160',
                                'bankMessage'           => 'Insufficient fund',
                                'bankCode'              => '10004',
                                'merchantCode'          => $transId,
                                'merchantMobile'        => isset($params['mobile']) ? $params['mobile'] : '9651807990',
                                'aadhaarNumber'         => CommonHelper::aadhaarMasking($params['aadharnumber']),
                                'transactionType'       => 'CW',
                                'transactionDateTime'   => date('Y-m-d H:i:s'),
                                'transactionAmount'     => '100',
                                'availableBalance'      => "0"
                            ];
                        }
                    break;

                    case 'aadhaarPay':
                        $routeType = 'ICICI';
                        if($params['routetype'] == 'sbm') {
                            $routeType = 'sbm';
                        }
                        if($returnResponse == 'success') {
                            $message = "000: AadhaarPay transfer successfully.";
                            $data['data'] = [];
                        }else if($returnResponse == 'failed') {
                            $message = "001: Duplicate rd service found, please scan fingerprint again";
                        }
                    break;

                    case 'statement':
                        $routeType = 'ICICI';
                        if($params['routetype'] == 'sbm') {
                            $routeType = 'sbm';
                            $statement = [
                                ["narration" => "01/10 MAT/D/116660     C 0019600.00"],
                                ["narration" => "28/09 POS/W/086010     D 0000050.00"],
                                ["narration" => "24/09 MAT/W/013473     D 0000500.00"],
                            ];
                        } else {
                            $routeType = $params['routetype'];
                            $statement = [
                                ['date' => '01/10', 'txnType' => 'Cr', 'amount' => '48.0', 'narration' => 'Credit Interest Capi'],
                                ['date' => '02/10', 'txnType' => 'Cr', 'amount' => '40.0', 'narration' => 'Credit Interest Capi'],
                                ['date' => '03/10', 'txnType' => 'Dr', 'amount' => '50.0', 'narration' => 'Debit Interest Capi'],
                            ];
                        }
                        if($returnResponse == 'success') {
                            $message = "000: Mini statement fetched successfully.";
                            $data['data'] = [
                                'clientRefNo'           => isset($params['clientrefno']) ? $params['clientrefno'] : 'AMS7cbc183117921664800',
                                'routeType'             => $routeType,
                                'bankiin'               => isset($params['bankiin']) ? $params['bankiin'] : '606985',
                                'stanNo'                => 'XTL92DE554FB0B6483A98152C89456264EF',
                                'rrn'                   => '127115858160',
                                'bankMessage'           => "SUCCESS",
                                'bankCode'              => "00",
                                'statusCode'            => "",
                                'merchantCode'          => $transId,
                                'merchantMobile'        => isset($params['mobile']) ? $params['mobile'] : '9651807990',
                                'aadhaarNumber'         => CommonHelper::aadhaarMasking($params['aadharnumber']),
                                'transactionType'       => 'MS',
                                'transactionDateTime'   => date('Y-m-d H:i:s'),
                                'transactionAmount'     => '0',
                                'availableBalance'      => "100",
                                'statement'             => $statement
                            ];
                        }else if($returnResponse == 'failed') {
                            $message = "001: FAILURE";
                            $data['data'] = [
                                'clientRefNo'           => isset($params['clientrefno']) ? $params['clientrefno'] : 'AMS7cbc183117921664800',
                                'routeType'             => $routeType,
                                'bankiin'               => isset($params['bankiin']) ? $params['bankiin'] : '606985',
                                'stanNo'                => 'XTL92DE554FB0B6483A98152C89456264EF',
                                'rrn'                   => '127115858160',
                                'bankMessage'           => 'Customer Bank account is Frozen or Frozen Account',
                                'bankCode'              => '10004',
                                'statusCode'            => "",
                                'merchantCode'          => $transId,
                                'merchantMobile'        => isset($params['mobile']) ? $params['mobile'] : '9651807990',
                                'aadhaarNumber'         => CommonHelper::aadhaarMasking($params['aadharnumber']),
                                'transactionType'       => 'MS',
                                'transactionDateTime'   => date('Y-m-d H:i:s'),
                                'transactionAmount'     => '0',
                                'availableBalance'      => "100",
                                "statement"             => []
                            ];
                        }
                    break;

                    case 'state':
                        if($returnResponse == 'success') {
                            $message = "Record found fetched successfully.";
                            $data['data'] = array(
                                array('stateId' => 1, 'stateName' => "Andaman & Nicobar Islands"),
                                array('stateId' => 2, 'stateName' => "Andhra Pradesh")
                            );
                        }else if($returnResponse == 'failed') {
                            $message = "001: FAILURE";
                        }
                    break;

                    case 'district':
                        if($returnResponse == 'success') {
                            $message = "Record found fetched successfully.";
                            $data['data'] = array(
                                array('districtId' => 1, 'stateName' => "Andaman & Nicobar Islands"),
                                array('districtId' => 2, 'stateName' => "Andhra Pradesh")
                            );
                        }else if($returnResponse == 'failed') {
                            $message = "001: FAILURE";
                        }
                    break;

                }
            break;
        }
        if($returnResponse == 'success') {
            $status = "SUCCESS";
            $statusCode = "0x0200";
        }else if($returnResponse == 'failed') {
            $status = "FAILURE";
            $statusCode = "0x0202";
        }
        $resp['message'] = $message;
        if(isset($data['data'])) {
            $resp['data'] = $data['data'];
        } else {
            $resp['data'] = [];
        }
        return $resp;
      
    }
}