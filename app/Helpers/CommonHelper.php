<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Apilog;
use App\Models\UserService;
use App\Models\BulkPayout;
use App\Models\OauthClient;
use App\Models\UPICallback;
use App\Models\GlobalConfig;
use Illuminate\Support\Facades\URL;
use App\Models\BusinessInfo;
use App\Models\AccountManager;
use App\Models\Product;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Agent;
use App\Models\AepsTransaction;
use App\Models\UPICollect;
use App\Models\MApiLog;
use Storage;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CommonHelper
{
    public static function curl($url, $method = 'GET', $parameters, $header=array(), $log = "no", $user_id = '', $modal = '', $reqType = '', $txnId = '', $decryptedRequest = "")
    {
        /*$fileName = 'public/CurlApilogs' . $txnId . '.txt';
        $apiLogStatus = self::apiLogEnableDisable($reqType);
        if ($log == "yes" && $apiLogStatus) {
            $encryptedRequest = '';
            if (in_array($modal, ['safeXPay', 'ibl', ROOT_TYPE_VA])) {
                $decryptedRequest = $decryptedRequest;
                $encryptedRequest = $parameters;
            } else {
                $decryptedRequest = $parameters;
            }
            //$apiLog = MApiLog::insertLog($user_id, $url, $txnId, $modal, $reqType, $decryptedRequest, $encryptedRequest);
        }*/

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($curl, CURLOPT_TIMEOUT, 400);
        curl_setopt($curl, CURLOPT_USERAGENT, 'PostmanRuntime/7.35.0');
       // CURLOPT_USERAGENT=>'PostmanRuntime/7.35.0',
       // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
       // curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        if ($parameters != "") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }

        if (sizeof($header) > 0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        /*$response = curl_exec($curl);
        $err = curl_error($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        //dd([json_encode(json_decode($response, true)),$response],$err);
        $product_id = 1;
        $integration_id = 1;
        if ($reqType == 'instantpayTransfer') {
            Storage::disk('local')->append($fileName, "time: ".date('H:i:s')." , response : $response <br/>,error: $err" );
        }*/
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        if($log != "no"){
                Apilog::create([
                    "url" => $url, 
                    "method" => $method,
                    "modal" => $modal,
                    "txnid" => $txnId,
                    "header" => json_encode($header),
                    "request" => $parameters,
                    "response" => $response
                ]);
            }
        
        
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $apiLogStatus = self::apiLogEnableDisable($reqType);
        if ($log == "yes" && $apiLogStatus) {
            if ($modal == 'cashfree') {
                $headerData = json_encode($header);
            } else {
                $headerData = json_encode($header);
            }
            $apiLogHeader = self::apiLogHeaderEnableDisable($reqType);
            if ($apiLogHeader == false) {
                $headerData = '';
            }

            $encryptedResponse = '';
            $encryptedRequest = '';
            if (in_array($modal, ['safeXPay', 'ibl', ROOT_TYPE_VA])) {
                $encryptedResponse = $response;
                $decryptedRequest = $decryptedRequest;
                $encryptedRequest = $parameters;
            } else {
                $decryptedRequest = $parameters;
            }

            
        }
        $apiLogLastId = isset($apiLog) ? $apiLog : 0;
        return ["response" => $response, "error" => $err, 'code' => $code, 'apiLogLastId' => $apiLogLastId];
    }
    
    
    public static function rCurl($url , $method='GET', $parameters, $header, $log="no", $modal="none", $txnid="none")
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_TIMEOUT, 180);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        if($parameters != ""){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }

        if(sizeof($header) > 0){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
     
        
           if($log != "no"){
                Apilog::create([
                    "url" => $url, 
                    "method" => $method,
                    "modal" => $modal,
                    "txnid" => $txnid,
                    "header" => json_encode($header),
                    "request" => json_encode($parameters),
                    "response" => $response
                ]);
          }

        return ["response" => $response, "error" => $err, 'code' =>$code];
    }
    
    

    public static function httpClient($url, $method = 'GET', $parameters, $header, $log = "no", $user_id = '', $modal = '', $reqType = '', $txnId = '', $decryptedRequest = "")
    {
     
           $response = self::rCurl($url, $method = 'GET', $parameters, $header, $log,  $modal = '',  $txnId = '');
           $apiLogLastId = isset($apiLog) ? $apiLog : 0;

        return ["response" => $response, "error" => '', 'code' => @$response['statusCode'], 'apiLogLastId' => $apiLogLastId];
    }

    public static function callApi($url, $header = [], $token = "", $parameters = [], $method = "GET")
    {
   
    
        $resp['status'] = false;
        $resp['statusCode'] = "";
        $resp['response'] = [];
        if ($method == "GET") {
           $responseData = Http::timeout(300)->withHeaders($header)->accept('application/json')->get($url, $parameters);
        } else if ($method == "DELETE") {

            $responseData = Http::timeout(300)->withHeaders($header)->accept('application/json')->delete($url, $parameters);
        } else if ($method == "PUT") {

            $responseData = Http::timeout(300)->withHeaders($header)->accept('application/json')->put($url, $parameters);
        } else {

            $responseData = Http::timeout(300)->withHeaders($header)->accept('application/json')->post($url, $parameters);
        }
        
        
        $response = $responseData ? $responseData->getBody()->getContents() : null;
        $resp['statusCode'] = $responseData ? $responseData->getStatusCode() : 500;
        if(!empty($responseData->getHeader('One-Time-Token')[0]))
        {
            $resp['insToken'] = $responseData->getHeader('One-Time-Token')[0];
        }

        if ($response && $response !== 'null') {
            $resp['status'] = true;
            $resp['response'] = (object) json_decode($response);
            return $resp;
        } else {
            return $resp;
        }
    }

    public static function isServiceActive($user_id, $service_id = '')
    {
        $status = false;
        if (isset($service_id)) {
            if (UserService::where(['user_id' => $user_id, 'service_id' => $service_id, 'is_active' => '1'])->first()) {
                $status = true;
            }
            return $status;
        } else {
            $userService = UserService::where(['user_id' => $user_id, 'is_active' => '1'])->get();
            return $userService;
        }
    }


    public static function isServiceEnabled($userId, $serviceId, $route = 'web')
    {
        $status = false;

        switch ($route) {
            case 'web':
                $service = DB::table('user_services')
                    ->select('is_web_enable')
                    ->where('user_id', $userId)
                    ->where('service_id', $serviceId)
                    ->where('is_web_enable', '1')
                    ->count();

                if ($service > 0) {
                    $status = true;
                }
                break;
            case 'api':
                $service = DB::table('user_services')
                    ->select('is_api_enable')
                    ->where('user_id', $userId)
                    ->where('service_id', $serviceId)
                    ->where('is_api_enable', '1')
                    ->count();

                if ($service > 0) {
                    $status = true;
                }
                break;
            default:
                break;
        }

        return $status;
    }

    public static function serviceStatusCheck($user_id, $service_id)
    {
        $UserService = UserService::where(['user_id' => $user_id, 'service_id' => $service_id])->first();
        $UserServiceCount = UserService::where(['user_id' => $user_id, 'service_id' => $service_id])->count();
        if (isset($UserService) && $UserService->is_active == '0' && $UserServiceCount == 1) {
            $status = 'Pending';
        } else {
            $status = 'Activate';
        }
        return $status;
    }

    public static function payoutDashboard($user_id)
    {
        $UserService = UserService::where(['user_id' => $user_id, 'is_active' => '1', 'service_id' => PAYOUT_SERVICE_ID])->first();
        if (isset($UserService)) {
            return $UserService;
        } else {
            return 0;
        }
    }

    public static function userActiveServiceCount($user_id)
    {
        return UserService::where(['user_id' => $user_id, 'is_active' => '1'])->count();
    }

    public static function newServiceWalletNumber($service_id)
    {
        $UserServiceAccount = UserService::where('service_id', $service_id)->max('service_account_number');
        if ($UserServiceAccount) {
            return $LastEightDigit = $UserServiceAccount + 1;
        } else {
            if (PAYOUT_SERVICE_ID == $service_id) {
                return PAYOUT_SERVICE_DEFAULT_ACC;
            } elseif (AEPS_SERVICE_ID == $service_id) {
                return AEPS_SERVICE_DEFAULT_ACC;
            } elseif (RECHARGE_SERVICE_ID == $service_id) {
                return RECHARGE_SERVICE_DEFAULT_ACC;
            } elseif (VALIDATE_SERVICE_ID == $service_id) {
                return VALIDATION_SERVICE_DEFAULT_ACC;
            } elseif (DMT_SERVICE_ID == $service_id) {
                return DMT_SERVICE_DEFAULT_ACC;
            }  elseif (PAN_CARD_SERVICE_ID == $service_id) {
                return PAN_CARD_SERVICE_DEFAULT_ACC;
            } else {
                return RECHARGE_SERVICE_DEFAULT_ACC;
            }
        }
    }

    public static function newWalletNumber()
    {
        $UserAccount = User::max('account_number');
        if ($UserAccount) {
            $newAccNo = $UserAccount + 1;

            $checkNumber = User::where('account_number', $newAccNo)->first();
            if ($checkNumber) {
                self::newWalletNumber();
            }
        } else {
            return MAIN_DEFAULT_ACC;
        }
        return $newAccNo;
    }


    public static function newVanApiAccNumber()
    {

        $length = 4;
        $base_str = '0123456789';

        //generate rand number
        $mt_rand = VAN_API_DEFAULT_ACC . substr(str_shuffle($base_str), 0, $length);

        //check if already assigned
        $count = DB::table('cf_merchants')->select('id')
            ->where('v_account_id', $mt_rand)
            ->where('service_type', 'van')
            ->count();

        if ($count > 0) {
            //call self
            return self::newVanApiAccNumber();
        }

        return $mt_rand;
    }


    public static function getUserIdUsingKeyAndSecret($header)
    {
        $hash = hash('sha512', $header['php-auth-pw'][0]);
        $key = $header['php-auth-user'][0];
        $OauthClient = OauthClient::select('user_id')->where(['client_key' => $key, 'client_secret' => $hash])->first();
        return $OauthClient->user_id;
    }

    public static function showSpan($status)
    {
        $html = '<span class="btn %s">%s</span>';
        if (in_array(ucfirst($status), ['Active', 'Success', 'Processed', 'Credit'])) {
            $label = "btn-success";
        } elseif (in_array(ucfirst($status), ['Failed', 'Cancelled', 'Cancel', 'Debit'])) {
            $label = "btn-danger";
        } elseif (in_array(ucfirst($status), ['Pending', 'Progress', 'Processing'])) {
            $label = "btn-warning";
        } elseif (in_array(ucfirst($status), ['Queued'])) {
            $label = "btn-primary";
        } else {
            $label = "btn-default";
        }
        $html = sprintf($html, $label, self::case($status));
        return $html;
    }

    public static function case($text, $type = '')
    {
        if ($type == 'l') {
            return strtolower($text);
        } elseif ($type == 'u') {
            return strtoupper($text);
        } elseif ($type == 'uw') {
            return ucwords($text);
        } else {
            return ucfirst($text);
        }
    }

    /**
     * AES 256 Encryption
     *
     * @param string $platString
     * @param string $key
     * @param string $iv
     * @return void
     */
    public static function encrypt($platString, $key, $iv)
    {
        $encryptedString = openssl_encrypt($platString, 'AES-256-CBC', self::decode($key), OPENSSL_RAW_DATA, $iv);
        return self::encode($encryptedString);
    }

    /**
     * AES 256 Decryption
     *
     * @param string $encryptedString
     * @param string $key
     * @param string $iv
     * @return void
     */
    public static  function decrypt($encryptedString, $key, $iv)
    {
        $decryptedString = openssl_decrypt(self::decode($encryptedString), 'AES-256-CBC', self::decode($key), OPENSSL_RAW_DATA, $iv);
        return $decryptedString;
    }

    /**
     * Base64 Encode
     *
     * @param string $plainString
     * @return void
     */
    public static function encode($plainString)
    {
        return base64_encode($plainString);
    }

    /**
     * Base64 Decode
     *
     * @param string $encodedString
     * @return void
     */
    public static function decode($encodedString)
    {
        return base64_decode($encodedString);
    }

    public static function getServiceAccount($user_id, $service_id)
    {
        $UserService = UserService::where(['user_id' => $user_id, 'service_id' => $service_id])->first();
        if ($UserService) {
            return $UserService;
        }
    }


    public static function batchImportFile($row)
    {

        if (count($row) == BULK_IMPORT_COLUMN_COUNT) {

            foreach ($row as $key => $val) {
                if (!in_array(
                    $key,
                    array(
                        'contact_first_name', 'contact_last_name', 'contact_email', 'contact_phone', 'contact_type', 'contact_type', 'account_type',
                        'account_number', 'account_ifsc', 'account_vpa', 'account_vpa', 'payout_mode', 'payout_amount', 'payout_reference_id', 'payout_purpose',
                        'payout_narration', 'note_1', 'note_2'
                    )
                )) {
                    session(['importFileError' => 1]);
                    $str = str_replace('_', ' ', $key);
                    session(['importFileErrorMessage' => COLUMN_UNKNOWN . ucwords($str)]);
                }
            }
        } else {

            session(['importFileError' => 1]);
            session(['importFileErrorMessage' => COLUMN_MISSMATCH]);
        }
    }

    public static function stringCheck($str = "str", $length = 0)
    {
        $response['error_status'] = false;
        $response['error'] = "";
        if (is_string($str)) {
            if (!strlen($str) > $length) {
                $response['error_status'] = true;
                $response['error'] = $str . " must be length greater then " . $length;
            }
        } else {
            $response['error_status'] = true;
            $response['error'] = $str . FIELD_STRING;
        }

        return $response;
    }

    public static function stringAndIfscCheck($str = "str", $length = 11)
    {
        $response['error_status'] = false;
        $response['error'] = "";

        if (is_string($str)) {
            if (strlen($str) == $length) {
                $pm = preg_match("/^[A-Za-z]{4}[0][A-Z0-9]{6}$/", $str);
                if ($pm) {
                    $response['error_status'] = false;
                    $response['error'] = "";
                } else {
                    $response['error_status'] = true;
                    $response['error'] = IFSC_CODE_NOT_VALID;
                }
            } else {
                $response['error_status'] = true;
                $response['error'] = IFSC_LENGTH;
            }
        } else {
            $response['error_status'] = true;
            $response['error'] = $str . FIELD_STRING;
        }
        return $response;
    }

    public static function integerCheck($str = 1, $length = 1)
    {

        $response['error_status'] = false;
        $response['error'] = "";
        if (is_numeric($str)) {
            if (mb_strlen($str) >= $length) {
                $response['error_status'] = false;
            } else {
                $response['error_status'] = true;
                $response['error'] = $str . FIELD_MIN_LENGTH . $length;
            }
        } else {
            $response['error_status'] = true;
            $response['error'] = $str . FIELD_INTEGER;
        }

        return $response;
    }

    public static function accountTypeCheck($accountNumber, $accountIfsc)
    {

        $res['error_status'] = false;
        $accountNu = self::integerCheck($accountNumber);
        if ($accountNu['error_status']) {
            $res['error_status'] = true;
            $res['error'] = $accountNu['error'];
        }

        $accountNu = self::stringAndIfscCheck($accountIfsc, 11);
        if ($accountNu['error_status']) {
            $res['error_status'] = true;
            $res['error'] = $accountNu['error'];
        }
        return $res;
    }

    /** Get Product Id and Service Id*/

    public static function getProductId($mode, $type)
    {
        $mode = self::case($mode, 'l');
        $product = Product::where('slug', $mode)->where('type', $type)->first();
        if (isset($product)) {
            return $product;
        } else {
            return false;
        }
    }

    public static function paymentmodeCheck($mode)
    {
        $res['error_status'] = false;

        if (in_array(self::case($mode, 'l'), array('imps', 'neft', 'rtgs', 'upi'))) {
            $res['error_status'] = false;
        } else {
            $res['error_status'] = true;
            $res['error'] = ' payment mode dose not exit. exmple of payment mode : IMPS,NEFT,RTGS,UPI';
        }
        return $res;
    }

    /**
     * Contact Type Checking function
     *
     * @param [type] $type
     * @return void
     */
    public static function contactTypeCheck($type)
    {
        $res['error_status'] = false;
        if (in_array(self::case($type, 'l'), array('vendor', 'customer', 'employee', 'self'))) {
            $res['error_status'] = false;
        } else {
            $res['error_status'] = true;
            $res['error'] = ' contact type dose not exit. Example of Contact Type : vendor,customer,employee,self';
        }
        return $res;
    }

    /**
     * The Purpose Check function
     *
     * @param [type] $type
     * @return void
     */
    public static function purposeCheck($type)
    {
        $res['error_status'] = false;
        if (in_array(self::case($type, 'l'), array('reimbursement', 'salary_disbursement', 'bonus', 'incentive', 'others', 'refund', 'cashback', 'payout', 'salary', 'utility bill', 'vendor bill'))) {
            $res['error_status'] = false;
        } else {
            $res['error_status'] = true;
            $res['error'] = ' purpose type dose not exit. Example of Purpose Type : reimbursement,salary_disbursement,others,bonus,incentive,refund,cashback,payout,salary,utility bill,vendor bill';
        }
        return $res;
    }
    /**
     * Undocumented function
     *
     * @param [type] $accountNumber
     * @param [type] $amount
     * @return void
     */

    public static function addFundToGlobal($accountNumber, $amount)
    {

        $User = User::where(['account_number' => $accountNumber, 'is_admin' => '1'])->first();
        if ($User) {
            $User->transaction_amount += $amount;
            $User->save();
            return $User;
        } else {
            return 0;
        }
    }

    public static function getRandomString($prefix = '', $separator = true, $length = 4)
    {
        $ts = hrtime(true);

        $base_str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $str_shuffle = substr(str_shuffle($base_str), 0, $length);
        $str_md5 = substr(md5($str_shuffle), 0, $length);
        $hash = substr(sha1($str_md5), 0, $length);

        if ($prefix) {
            if ($separator) {
                $string = strtoupper($prefix) . '_' . $ts . strtoupper($hash);
            } else {
                $string = strtoupper($prefix) . $ts . strtoupper($hash);
            }
        } else {
            $string = $hash . $ts;
        }
        return $string;
    }

    public static function chart($userId, $tableName, $search = '50Days', $trIdentifiers = 'internal_transfer')
    {
        $response = [];
        $lastMonth = [];
        $getDay = [];

        if (isset($tableName) && !empty($userId) && $tableName == 'orders') {

            $user = User::select('is_admin')->where('id', $userId)->first();
            $lastMonthQuery = Order::select([
                DB::raw('sum(amount) as `amount`'),
                DB::raw('DATE(created_at) as day')
            ]);
            if ($user->is_admin == '0') {
                $lastMonthQuery->where('user_id', $userId);
            }
            if ($search == '30Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(30));
            } else if ($search == '50Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(50));
            } else if ($search == 'thisMonth') {
                $lastMonthQuery->whereMonth('created_at', date('m'));
            }
            $lastMonthQuery->groupBy('day');
            $lastMonth = array_column(json_decode(json_encode($lastMonthQuery->get())), 'amount', 'day');
            foreach (array_keys($lastMonth) as $key) {
                $timestamp = strtotime($key);
                $getDay[] = date('d', $timestamp);
            }
            $response['days'] = implode(",", $getDay);
            $response['arrayDays'] = $getDay;
            $response['amount'] = implode(",", array_values($lastMonth));
            $response['status'] = true;
            $response['message'] = "Record Found Successfully";
        } else if (isset($tableName) && !empty($userId) && $tableName == 'transactions') {

            $user = User::select('is_admin')->where('id', $userId)->first();
            $lastMonthQuery = Transaction::select([
                DB::raw('sum(tr_amount) as `amount`'),
                DB::raw('DATE(created_at) as day')
            ]);
            if ($user->is_admin == '0') {
                $lastMonthQuery->where('user_id', $userId);
            }
            if (!empty($trIdentifiers)) {
                $lastMonthQuery->where('tr_identifiers', $trIdentifiers);
            }
            if ($search == '30Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(30));
            } else if ($search == '50Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(50));
            } else if ($search == '1Days') {
                $lastMonthQuery->where('created_at', 'like', '%' . date('Y-m-d') . '%');
            } else if ($search == '7Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(7));
            } else if ($search == 'thisMonth') {
                $lastMonthQuery->whereMonth('created_at', date('m'));
            }
            $lastMonthQuery->groupBy('day');
            $lastMonth = array_column(json_decode(json_encode($lastMonthQuery->get())), 'amount', 'day');
            foreach (array_keys($lastMonth) as $key) {
                $timestamp = strtotime($key);
                $getDay[] = date('d', $timestamp);
            }
            $response['days'] = implode(",", $getDay);
            $response['arrayDays'] = $getDay;
            $response['amount'] = implode(",", array_values($lastMonth));
            $response['status'] = true;
            $response['message'] = "Record Found Successfully";
        } else {
            $response['status'] = false;
            $response['message'] = "Please send tableName,userId,search";
            return $response;
        }
        return $response;
    }


    public static function updateChart($userId, $tableName, $search = '50Days', $trIdentifiers = 'internal_transfer')
    {
        $response = [];
        $lastMonth = [];
        $getDay = [];
        $data = [];

        if (isset($tableName) && !empty($userId) && $tableName == 'orders') {

            $user = User::select('is_admin')->where('id', $userId)->first();
            $lastMonthQuery = Order::select([
                DB::raw('sum(amount) as `amount`'),
                DB::raw('DATE(created_at) as day')
            ]);
            if ($user->is_admin == '0') {
                $lastMonthQuery->where('user_id', $userId);
            }
            if ($search == '30Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30));
            } else if ($search == '50Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(50));
            } else if ($search == '1Days') {
                $lastMonthQuery->where('created_at', 'like', '%' . date('Y-m-d') . '%');
            } else if ($search == '7Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(7));
            } else if ($search == 'thisMonth') {
                $lastMonthQuery->whereMonth('created_at', date('m'));
            }
            $lastMonthQuery->groupBy('day');
            $lastMonth = array_column(json_decode(json_encode($lastMonthQuery->get())), 'amount', 'day');
            if (count($lastMonth)) {
                foreach ($lastMonth as $key => $value) {
                    $timestamp = strtotime($key);
                    $data[] = array('x' => date('d', $timestamp), 'y' => round($value));
                }
            } else {
                $data[] = array('x' => 0, 'y' => 0);
            }
            $response['data'] = $data;
            $response['status'] = true;
            $response['message'] = "Record Found Successfully";
        } else if (isset($tableName) && !empty($userId) && $tableName == 'transactions') {

            $user = User::select('is_admin')->where('id', $userId)->first();
            $lastMonthQuery = Transaction::select([
                DB::raw('sum(tr_amount) as `amount`'),
                DB::raw('DATE(created_at) as day')
            ]);
            if ($user->is_admin == '0') {
                $lastMonthQuery->where('user_id', $userId);
            }
            if (!empty($trIdentifiers)) {
                $lastMonthQuery->where('tr_identifiers', $trIdentifiers);
                $lastMonthQuery->where('tr_type', 'dr');
            }
            if ($search == '1Days') {
                $lastMonthQuery->where('created_at', 'like', '%' . date('Y-m-d') . '%');
            } else if ($search == '7Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(7));
            } else if ($search == '15Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(15));
            } else if ($search == '30Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30));
            } else if ($search == '90Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(90));
            }
            $lastMonthQuery->groupBy('day');
            $lastMonth = array_column(json_decode(json_encode($lastMonthQuery->get())), 'amount', 'day');
            if (count($lastMonth)) {
                foreach ($lastMonth as $key => $value) {
                    $timestamp = strtotime($key);
                    $data[] = array('x' => date('d', $timestamp), 'y' => $value);
                }
            } else {
                $data[] = array('x' => 0, 'y' => 0);
            }
            $response['data'] = $data;
            $response['status'] = true;
            $response['message'] = "Record Found Successfully";
        } else if (isset($tableName) && !empty($userId) && $tableName == 'callback') {

            $user = User::select('is_admin')->where('id', $userId)->first();
            $lastMonthQuery = UPICallback::select([
                DB::raw('sum(amount) as `amount`'),
                DB::raw('DATE(created_at) as day'),
                DB::raw('(created_at) as hours')
            ]);
            if ($user->is_admin == '0') {
                $lastMonthQuery->where('user_id', $userId);
            }
            $lastTwoDays = \Carbon\Carbon::now()->subDays(2);

            $aa = $lastTwoDays->isoFormat('YYYY-M-D');
            if ($search == '1Days') {
                //$lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(2));
                //$lastMonthQuery->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') >= '$aa'");
                $lastMonthQuery->where('created_at', 'like', '%' . date('Y-m-d') . '%');
            } else if ($search == '7Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(7));
            } else if ($search == '15Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(15));
            } else if ($search == '30Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30));
            } else if ($search == '90Days') {
                $lastMonthQuery->where('created_at', '>=', \Carbon\Carbon::now()->subDays(90));
            }
            if ($search == '1Days') {
                $lastMonthQuery->groupBy('hours');
                $lastMonth = array_column(json_decode(json_encode($lastMonthQuery->get())), 'amount', 'hours');
            } else {
                $lastMonthQuery->groupBy('day');
                $lastMonth = array_column(json_decode(json_encode($lastMonthQuery->get())), 'amount', 'day');
            }


            //print_r($lastMonth);
            if (count($lastMonth)) {
                foreach ($lastMonth as $key => $value) {
                    $timestamp = strtotime($key);
                    if ($search == '1Days') {

                        $data[] = array('x' => date('Y-m-d g:i a', $timestamp), 'y' => $value);
                    } else {
                        $data[] = array('x' => date('d M', $timestamp), 'y' => $value);
                    }
                }
            } else {
                $data[] = array('x' => 0, 'y' => 0);
            }

            $response['data'] = $data;
            $response['status'] = true;
            $response['message'] = "Record Found Successfully";
        } else if (isset($tableName) && !empty($userId) && $tableName == 'callbackuserdata') {

            $user = User::select('is_admin')->where('id', $userId)->first();
            $lastMonthData = DB::table('upi_callbacks')->select('users.name', DB::raw('sum(amount) as amount'), DB::raw('DATE(upi_callbacks.created_at) as day'))->join('users', 'users.id', '=', 'upi_callbacks.user_id');
            if ($user->is_admin == '0') {
                $lastMonthQuery->where('user_id', $userId);
            }
            $lastTwoDays = \Carbon\Carbon::now()->subDays(2);

            $aa = $lastTwoDays->isoFormat('YYYY-M-D');
            if ($search == '1Days') {
                $lastMonthData->where('upi_callbacks.created_at', 'like', '%' . date('Y-m-d') . '%');
                //$lastMonthData->whereRaw("DATE_FORMAT(upi_callbacks.created_at, '%Y-%m-%d') >= '$aa'");
            } else if ($search == '7Days') {
                $lastMonthData->where('upi_callbacks.created_at', '>=', \Carbon\Carbon::now()->subDays(7));
            } else if ($search == '15Days') {
                $lastMonthData->where('upi_callbacks.created_at', '>=', \Carbon\Carbon::now()->subDays(15));
            } else if ($search == '30Days') {
                $lastMonthData->where('upi_callbacks.created_at', '>=', \Carbon\Carbon::now()->subDays(30));
            } else if ($search == '90Days') {
                $lastMonthData->where('upi_callbacks.created_at', '>=', \Carbon\Carbon::now()->subDays(90));
            }
            $lastMonthData->groupBy('user_id');
            $monthData = $lastMonthData->get();
            if (count($monthData)) {
                foreach ($monthData as $key => $value) {
                    $timestamp = strtotime($key);
                    $data[] = array('name' => $value, 'amount' => $key);
                }
            } else {
                $data[] = array('name' => '', 'amount' => 0);
            }
            $response['data'] = $monthData;
            $response['status'] = true;
            $response['message'] = "Record Found Successfully";
        } else {
            $response['status'] = false;
            $response['message'] = "Please send tableName,userId,search";
            return $response;
        }
        return $response;
    }


    public static function payoutUpdateChart($userId, $tableName, $search = '50Days', $trIdentifiers = 'internal_transfer', $trType = 'cr')
    {
        $response = [];
        $lastMonth = [];
        $getDay = [];
        $data = [];

        if (isset($tableName) && !empty($userId) && $tableName == 'transactions') {

            $user = User::select('is_admin')->where('id', $userId)->first();
            $userServiceAccountNumber = UserService::select('service_account_number')->where('user_id', $userId)->where('service_id', 'srv_1626077095')->first()->service_account_number;
            $lastMonthQuery = Transaction::select([
                DB::raw('sum(tr_amount) as `amount`'),
                DB::raw('DATE(created_at) as day')
            ]);
            if ($user->is_admin == '0') {
                $lastMonthQuery->where('user_id', $userId);
            }
            if (!empty($trIdentifiers)) {
                $lastMonthQuery->where('tr_identifiers', $trIdentifiers);
            }
            if (!empty($trType)) {
                $lastMonthQuery->where('tr_type', $trType);
            }
            if (!empty($userServiceAccountNumber)) {
                $lastMonthQuery->where('account_number', $userServiceAccountNumber);
            }
            if ($search == '1Days') {
                $lastMonthQuery->where('created_at', 'like', '%' . date('Y-m-d') . '%');
            } else if ($search == '7Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(7));
            } else if ($search == '15Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(15));
            } else if ($search == '30Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(30));
            } else if ($search == '90Days') {
                $lastMonthQuery->where('created_at', '>=', Carbon::now()->subDays(90));
            }
            $lastMonthQuery->groupBy('day');
            $lastMonth = array_column(json_decode(json_encode($lastMonthQuery->get())), 'amount', 'day');
            if (count($lastMonth)) {
                foreach ($lastMonth as $key => $value) {
                    $timestamp = strtotime($key);
                    $data[] = array('x' => date('d', $timestamp), 'y' => round($value));
                }
            } else {
                $data[] = array('x' => 0, 'y' => 0);
            }
            $response['data'] = $data;
            $response['status'] = true;
            $response['message'] = "Record Found Successfully";
        } else {
            $response['status'] = false;
            $response['message'] = "Please send tableName,userId,search";
            return $response;
        }
        return $response;
    }

    /**
     * Get Masked Mobile Number
     *
     * @param [type] $number 9123456789
     * @return void 91XXXXX789
     */
    public static function mobileMask($number)
    {
        return substr($number, 0, 2) . 'XXXXX' . substr($number, 7);
    }

    /**
     * Undocumented function
     *
     * @param [type] $email
     * @return void
     */
    public static function  emailMask($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // split an email by "@"
            list($first, $last) = explode('@', $email);
            // get half the length of the first part
            $firstLen = floor(strlen($first) / 2);
            // partially hide a first part
            $first = str_replace(substr($first, $firstLen), str_repeat('*', strlen($first) - $firstLen), $first);
            // get the starting position of the "."
            $lastIndex = strpos($last, ".");
            // divide last part in two different strings
            $last1 = substr($last, 0, $lastIndex);
            $last2 = substr($last, $lastIndex);
            // get half the length of the "$last1"
            $lastLen  = floor(strlen($last1) / 2);
            // partially hide a string by "*"
            $last1 = str_replace(substr($last1, $lastLen), str_repeat('*', strlen($last1) - $lastLen), $last1);
            // combine all parts together and return partially hide email
            $partiallyHideEmail = $first . '@' . $last1 . '' . $last2;
            return $partiallyHideEmail;
        }
    }

    public static function  getCaptcha($SecretKey)
    {
        $Response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . env('NOCAPTCHA_SECRET') . "&response={$SecretKey}");
        $Return = json_decode($Response);
        return $Return;
    }

    /**
     * Undocumented function
     *
     * @param [type] $from
     * @param [type] $to
     * @param [type] $type
     * @return void
     */
    public static function twoDateDiff($from, $to, $type)
    {
        // Declare and define two dates
        $resp['status'] = false;
        $resp['data'] = 0;

        $toDate = Carbon::createFromFormat('Y-m-d H:s:i', date("Y-m-d H:s:i", strtotime($from)));
        $fromDate = Carbon::createFromFormat('Y-m-d H:s:i', date("Y-m-d H:s:i", strtotime($to)));

        $resp['data'] = $toDate->diffInMinutes($fromDate);

        return $resp;
    }

    /**
     * Undocumented function
     *
     * @param [type] $from
     * @param [type] $to
     * @param [type] $type
     * @return void
     */
    public static function callBackUPITotalAmount($userId, $type = '')
    {
        $return['amount'] = 0;
        $return['count'] = 0;

       $query = DB::table('upi_collects')
        ->select(
            DB::raw('count(id) as count'),
            DB::raw('sum(amount) as amount')
        )->where('status','success')
        ->where('user_id', $userId);

        if ($type == '7Days') {
            $query->whereDate('created_at', '>=',  Carbon::now()->subDays(7))
                ->where('is_trn_credited', '0');
        } else if ($type == '30Days') {
            $query->whereDate('created_at', '>=',  Carbon::now()->subDays(30))
                ->where('is_trn_credited', '0');
        } else if($type == 'today') {
            $query->whereDate('created_at', date('Y-m-d'))
                ->where('is_trn_credited', '0');
        } else {
            $query->whereDate('created_at', date('Y-m-d'))
            ->where('is_trn_credited', '0');
        }

        $query = $query->first();
       // dd($query);
        $return['amount'] = empty($query->amount)?0:$query->amount;
        $return['count'] = $query->count;

        return $return;
    }


    public static function callBackSmartCollectTotalAmount($userId, $type = '')
    {
        $return['amount'] = 0;
        $return['count'] = 0;

        $query = DB::table('cf_merchants_fund_callbacks')->select(
            DB::raw('count(id) as count'),
            DB::raw('sum(amount) as amount'),
        )->where('user_id', $userId);

        if ($type == '7Days') {
            $query->whereDate('created_at', '>=',  Carbon::now()->subDays(7))
                ->where('is_trn_credited', '1');
        } else if ($type == '30Days') {
            $query->whereDate('created_at', '>=',  Carbon::now()->subDays(30))
                ->where('is_trn_credited', '1');
        } else if($type == 'today') {
            $query->whereDate('created_at', date('Y-m-d'))
                ->where('is_trn_credited', '1');
        } else {
            $query->whereDate('created_at', date('Y-m-d'));
        }

        $query = $query->first();

        $return['amount'] = empty($query->amount)?0:$query->amount;
        $return['count'] = $query->count;

        return $return;
    }


    public static function callBackVirtualAccountTotalAmount($userId, $type = '')
    {
        $return['amount'] = 0;
        $return['count'] = 0;

        $query = DB::table('upi_callbacks')->select(
            DB::raw('count(id) as count'),
            DB::raw('sum(amount) as amount'),
        )->where('user_id', $userId)
        ->where('root_type', 'ibl_tpv');

        if ($type == '7Days') {
            $query->where('is_trn_credited', '1')
                ->whereDate('created_at', '>=',  Carbon::now()->subDays(7));
        } else if ($type == '30Days') {
            $query->where('is_trn_credited', '1')
                ->whereDate('created_at', '>=',  Carbon::now()->subDays(30));
        } else if($type == 'today') {
            $query->whereDate('created_at', date('Y-m-d'))
                ->where('is_trn_credited', '1');
        } else {
            $query->whereDate('created_at', date('Y-m-d'));
        }

        $query = $query->first();

        $return['amount'] = empty($query->amount)?0:$query->amount;
        $return['count'] = $query->count;

        return $return;
    }
    

    public static function activeMerchant($userId)
    {
        $active = \App\Models\UPIMerchant::join('upi_callbacks', 'upi_callbacks.payee_vpa', 'upi_merchants.merchant_virtual_address')
            ->where('upi_merchants.user_id', $userId)
            ->groupBy('upi_merchants.user_id')
            ->count();

        return $active;
    }

    public static function shortName($userId, $name = "")
    {
        $firstChar = "";
        $secondChar = "";
        $data = "NA";
        if (isset($userId) && !empty($userId) || !empty($name)) {
            if (!empty($name)) {
                $name = $name;
            } else {
                $nameData = User::where('id', $userId)->select('name')->first();
                if (isset($nameData)) {
                    $name = $nameData->name;
                }
            }

            if (isset($name) && !empty($name)) {
                $nameArr = explode(" ", $name);
                if (isset($nameArr[0]) && !empty($nameArr[0])) {
                    $firstChar = substr($nameArr[0], 0, 1);
                }
                if (isset($nameArr[1]) && !empty($nameArr[1])) {
                    $secondChar = substr($nameArr[1], 0, 1);
                } else {
                    $firstChar = substr($nameArr[0], 0, 2);
                }
                $data = $firstChar . $secondChar;
            }
        }

        return self::case($data, 'u');
    }

    public static function isKycUpdated($id)
    {
        $kycUpdated = BusinessInfo::select('is_kyc_updated')->where(['user_id' => $id])->first();
        $data['kycUpdated'] = 0;
        if (isset($kycUpdated) && !empty($kycUpdated)) {
            $data['kycUpdated'] = $kycUpdated->is_kyc_updated;
        }
        return $data['kycUpdated'];
    }


    public static function supportInfo($userId)
    {
        $resp = [];
        if (isset($userId) && !empty($userId)) {
            $accountManager = BusinessInfo::select('account_managers.name as name', 'account_managers.email as email', 'account_managers.mobile as mobile')
                ->leftJoin('account_managers', 'account_managers.id', 'business_infos.acc_manager_id')
                ->where('account_managers.type', 'account_manager')
                ->where('account_managers.is_active', '1')
                ->where(['business_infos.user_id' => $userId])
                ->first();
            $accountCoordinator = BusinessInfo::select('account_managers.name as name', 'account_managers.email as email', 'account_managers.mobile as mobile')
                ->leftJoin('account_managers', 'account_managers.id', 'business_infos.acc_coordinator_id')
                ->where('account_managers.type', 'account_coordinator')
                ->where('account_managers.is_active', '1')
                ->where(['business_infos.user_id' => $userId])
                ->first();
            if (!isset($accountManager)) {
                $accountManager = AccountManager::where(['is_active' => '1', 'type' => 'account_manager', 'set_default' => '1'])->first();
            }
            if (!isset($accountCoordinator)) {
                $accountCoordinator = AccountManager::where(['is_active' => '1', 'type' => 'account_coordinator', 'set_default' => '2'])->first();
            }
            $resp['accountCoordinator'] = $accountCoordinator;
            $resp['accountManager'] = $accountManager;
            $resp['status'] = true;
        } else {
            $resp['status'] = false;
        }
        return $resp;
    }

    // public static function payoutIndex($user_id)
    // {
    //     $data['processed']          = 0;
    //     $data['processedAmount']    = 0;
    //     $data['pending']            = 0;
    //     $data['pendingAmount']      = 0;
    //     $data['failed']             = 0;
    //     $data['failedAmount']       = 0;

    //     $isAdmin = User::where(['id' => $user_id])->first()->is_admin;
    //     if ($isAdmin == '1') {
    //         $data['processed']        = BulkPayout::where(['status' => 'processed'])->count();
    //         $data['processedAmount']  = BulkPayout::where(['status' => 'processed'])->sum('total_amount');
    //         $data['pending']          = BulkPayout::where(['status' => 'pending'])->count();
    //         $data['pendingAmount']    = BulkPayout::where(['status' => 'pending'])->sum('total_amount');
    //         $data['failed']           = BulkPayout::where(['status' => 'failed'])->count();
    //         $data['failedAmount']     = BulkPayout::where(['status' => 'failed'])->sum('total_amount');
    //     } else {
    //         $data['processed']        = DB::table('orders')->where(['user_id' => $user_id, 'status' => 'processed'])->count();
    //         $data['processedAmount']  = DB::table('orders')->where(['user_id' => $user_id, 'status' => 'processed'])->sum('amount','fee','tax');
    //         $data['reversed']         = DB::table('orders')->where(['user_id' => $user_id, 'status' => 'reversed'])->count();
    //         $data['reversedAmount']   = DB::table('orders')->where(['user_id' => $user_id, 'status' => 'reversed'])->sum('amount','fee','tax');
    //         $data['failed']           = DB::table('orders')->where(['user_id' => $user_id, 'status' => 'failed'])->count();
    //         $data['failedAmount']     = DB::table('orders')->where(['user_id' => $user_id, 'status' => 'failed'])->sum('amount','fee','tax');
    //     }
    //     return $data;
    // }

    public static function defaultPayoutRoute($slug = '')
    {
        $data['slug'] = "no_route_found";
        $data['integration_id'] = "unknownID";
        if (isset($slug) && !empty($slug) && $slug != "") {
            $defaultRoute = GlobalConfig::select('integrations.slug as slug', 'integrations.integration_id as integration_id')
                ->join('integrations', 'integrations.integration_id', '=', 'global_config.attribute_1')
                ->where('global_config.slug', 'default_' . $slug)
                ->where('integrations.is_active', '1')
                ->first();
                //dd($defaultRoute);
            if (isset($defaultRoute)) {
                $data['slug'] = $defaultRoute->slug;
                $data['integration_id'] = $defaultRoute->integration_id;
            } else {
                $secondaryRoute = GlobalConfig::select('integrations.slug as slug', 'integrations.integration_id as integration_id')
                    ->join('integrations', 'integrations.integration_id', '=', 'global_config.attribute_1')
                    ->where('global_config.slug', 'secondary_' . $slug)
                    ->where('integrations.is_active', '1')
                    ->first();
                if (isset($secondaryRoute)) {
                    $data['slug'] = $secondaryRoute->slug;
                    $data['integration_id'] = $secondaryRoute->integration_id;
                }
            }
        }

        return $data;
    }


    public static function getPayoutRouteUsingUserId($userId = '', $area)
    {
        $data['slug'] = "no_route_found";
        $data['status'] = false;
        $data['integration_id'] = "unknownID";
        $userConfig = DB::table('user_config')->where('user_id', $userId)->first();
        if (isset($userConfig)) {
            if ($area == 'api') {
                $integrationId = $userConfig->api_integration_id;
            } else {
                $integrationId = $userConfig->web_integration_id;
            }
            if (isset($integrationId)) {
                $PayoutRoute = DB::table('integrations')
                    ->where('integrations.integration_id', $integrationId)
                    ->where('integrations.is_active', '1')
                    ->first();
                if (isset($PayoutRoute)) {
                    $data['slug'] = $PayoutRoute->slug;
                    $data['status'] = true;
                    $data['integration_id'] = $PayoutRoute->integration_id;
                }
            }
        }

        return $data;
    }

    public static function defaultUPICollectRoute($slug = '')
    {
        $data['slug'] = "no_route_found";
        $data['integration_id'] = "unknownID";
        if (isset($slug) && !empty($slug) && $slug != "") {
            $defaultRoute = GlobalConfig::select('integrations.slug as slug', 'integrations.integration_id as integration_id')
                ->join('integrations', 'integrations.integration_id', '=', 'global_config.attribute_1')
                ->where('global_config.slug', 'default_' . $slug)
                ->where('integrations.is_active', '1')
                ->first();
            if (isset($defaultRoute)) {
                $data['slug'] = $defaultRoute->slug;
                $data['integration_id'] = $defaultRoute->integration_id;
            }
        }

        return $data;
    }
    public static function getUPICollectRouteUsingUserId($userId = '', $area)
    {
        $data['slug'] = "no_route_found";
        $data['status'] = false;
        $data['integration_id'] = "unknownID";
        $userConfig = DB::table('user_config')->where('user_id', $userId)->first();
        if (isset($userConfig)) {
            if ($area == 'api') {
                $integrationId = $userConfig->api_integration_id;
                
            } else {
                $integrationId = $userConfig->web_integration_id;
            }
            $integrationId = $userConfig->upi_collect_integration_id;
            
            if (isset($integrationId)) {
                $PayoutRoute = DB::table('integrations')
                    ->where('integrations.integration_id', $integrationId)
                    ->where('integrations.is_active', '1')
                    ->first();
                if (isset($PayoutRoute)) {
                    $data['slug'] = $PayoutRoute->slug;
                    $data['status'] = true;
                    $data['integration_id'] = $PayoutRoute->integration_id;
                }
            }
        }

        return $data;
    }


    public static function routeNameByIntegrationId($integration = '', $area = "")
    {
        if ($area == '00') {
            $prefix = "payout_route";
        } else {
            $prefix = "api_payout_route";
        }
        $data['slug'] = "no_route_found";
        $data['integration_id'] = "unknownID";
        if (isset($integration) && !empty($integration) && $integration != "") {
            $defaultRoute = GlobalConfig::select('integrations.slug as slug', 'integrations.integration_id as integration_id')
                ->join('integrations', 'integrations.integration_id', '=', 'global_config.attribute_1')
                ->where('integrations.integration_id', $integration)
                ->where('global_config.slug', 'default_' . $prefix)
                ->where('integrations.is_active', '1')
                ->first();
            if (isset($defaultRoute)) {
                $data['slug'] = $defaultRoute->slug;
                $data['integration_id'] = $defaultRoute->integration_id;
            } else {
                $secondaryRoute = GlobalConfig::select('integrations.slug as slug', 'integrations.integration_id as integration_id')
                    ->join('integrations', 'integrations.integration_id', '=', 'global_config.attribute_1')
                    ->where('integrations.integration_id', $integration)
                    ->where('global_config.slug', 'secondary_' . $prefix)
                    ->where('integrations.is_active', '1')
                    ->first();
                if (isset($secondaryRoute)) {
                    $data['slug'] = $secondaryRoute->slug;
                    $data['integration_id'] = $secondaryRoute->integration_id;
                }
            }
        }
        return $data;
    }

    public static function uatOrProductionConfig($userId, $serviceName)
    {
        $data['appEnv'] = 'uat';
        if (env('APP_ENV') == 'production') {
            $data['appEnv'] = 'production';
        } else {
            $serviceDefaultRoute = \App\Models\UserEnvironment::select("$serviceName as service")
                ->where('user_id', $userId)
                ->first();
            if (isset($serviceDefaultRoute) && !empty($serviceDefaultRoute)) {
                if ($serviceDefaultRoute->service == 'production') {
                    $data['appEnv'] = 'production';
                } else {
                    $data['appEnv'] = 'uat';
                }
            } else {
                $data['appEnv'] = 'uat';
            }
        }
        return $data['appEnv'];
    }

    public static function stateOrDistrictName($id, $model = "district")
    {
        $name = "";
        if ($model == "district") {
            $name = \App\Models\District::select("district_title")
                ->where('id', $id)
                ->first()->district_title;
        } elseif ($model == "state") {
            $name = \App\Models\State::select("state_name")
                ->where('id', $id)
                ->first()->state_name;
        }

        return $name;
    }

    public static function aadhaarMasking($number, $maskingCharacter = 'X')
    {
        return substr($number, 0, 4) . str_repeat($maskingCharacter, strlen($number) - 4) . substr($number, -4);
    }

    public static function masking($type, $number)
    {

        if ($type == 'aadhar') {
            return 'XXXXXXXX' . substr($number, -4);
        } elseif($type == 'pan') {
            return 'XXXXXXX' . substr($number, -4);
        } else {
            return 'XXXXXX' . substr($number, -4);
        }
    }

    public static function getUserSalt($userId)
    {
        $user_salt = '';
        if ($userId) {
            $user_config = DB::table('user_config')->select('user_salt')->where('user_id', $userId)->first();
            if (!empty($user_config)) {
                $user_salt = base64_decode($user_config->user_salt);
                return $user_salt;
            }
        }

        return $user_salt;
    }

    public static function isAutoSettlementActive($userId, $type)
    {
        $status = false;
        if ($userId) {
            if ($type == 'auto_settlement')  {
                $count = DB::table('user_settlements')
                    ->where('user_id', $userId)
                    ->count();
                if ($count > 0) {
                    $status = true;
                } else {
                    $count = DB::table('user_config')
                        ->select('is_auto_settlement')
                        ->where(['user_id' => $userId, 'is_auto_settlement' => '1'] )
                        ->count();
                    if ($count == 1) {
                        $status = true;
                    }
                }
            } else if ($type == 'load_money_request'){
                $count = DB::table('load_money_request')
                    ->where('user_id', $userId)
                    ->count();
                if ($count > 0) {
                    $status = true;
                } else {
                    $count = DB::table('user_config')
                        ->select('load_money_request')
                        ->where(['user_id' => $userId, 'load_money_request' => '1'])
                        ->count();
                    if ($count == 1) {
                        $status = true;
                    }
                }
            }
        }

        return $status;
    }

    /**
     * aepsDashboard
     */
    public static function aepsDashboard($userId, $type)
    {
        $resp['amount'] = 0;
        $resp['merchant'] = 0;
        if (!empty($userId)) {
            $query = AepsTransaction::where('user_id', '=', $userId)->where('status', 'success');
            $merchant = Agent::leftJoin('aeps_transactions', 'aeps_transactions.merchant_code', 'agents.merchant_code')
                ->where('agents.user_id', $userId);
            $commission  = AepsTransaction::select(DB::raw('sum(commission) as totalAmount,count(id) as totalCount'))
                ->where(['status' =>  'success'])
                ->where('user_id',  $userId)
                ->where('commission','!=', 0)
                ->whereIn('transaction_type' ,['cw', 'ms']);
            if ($type == '7Days') {
                $query->whereDate('created_at', '>=',  Carbon::now()->subDays(7));
                $merchant->whereDate('aeps_transactions.created_at', '>=',  Carbon::now()->subDays(7));
                $commission->whereDate('aeps_transactions.created_at', '>=',  Carbon::now()->subDays(7));
            } else if ($type == '30Days') {
                $query->whereDate('created_at', '>=',  Carbon::now()->subDays(30));
                $merchant->whereDate('aeps_transactions.created_at', '>=',  Carbon::now()->subDays(30));
                $commission->whereDate('aeps_transactions.created_at', '>=',  Carbon::now()->subDays(30));
            } else {
                $query->where('created_at', 'like', '%' . date('Y-m-d') . '%');
                $merchant->where('aeps_transactions.created_at', 'like', '%' . date('Y-m-d') . '%');
                $commission->where('aeps_transactions.created_at', 'like', '%' . date('Y-m-d') . '%');
            }

            $amount = $query->where('transaction_type', 'cw')->sum('transaction_amount');
            $merchant = $merchant->distinct('agents.merchant_code')
                ->count();
            $commissionData = $commission->first();
            $resp['merchant'] = self::numberFormat($merchant);
            $resp['amount'] = self::numberFormat($amount);
            $resp['commissionAmount'] = self::numberFormat(@$commissionData->totalAmount);
            $resp['commissionCount'] = self::numberFormat(@$commissionData->totalCount);
        }
        return $resp;
    }

    /**
     * aepsDashboard
     */
    public static function upiDashboardChart($userId, $type)
    {
        $resp = [];
        if (!empty($userId)) {
            
            $query = DB::table('upi_callbacks')->select(
                DB::raw('count(id) as count'),
                DB::raw('sum(amount) as amount'),
            )->where('user_id', $userId)
                ->where('is_trn_credited', '1');

            if ($type == '7Days') {
                $query->whereDate('created_at', '>=',  Carbon::now()->subDays(7));
            } else if ($type == '30Days') {
                $query->whereDate('created_at', '>=',  Carbon::now()->subDays(30));
            } else {
                $query->whereDate('created_at', date('Y-m-d'));
            }

            $query = $query->first();
            $amount = $query->amount;
            $query = $query->count;

            $resp['count'] = $query;
            $resp['amount'] = NumberFormat::init()->change($amount, 2);
        }
        return $resp;
    }

    /**
     * aepsDashboard
     */
    public static function aepsDashboardChart($userId, $search, $fetchType)
    {
        $resp['merchant'] = 0;
        if (!empty($userId)) {
            $previous_week = strtotime("-1 week +1 day");
            $start_week = strtotime("last sunday midnight", $previous_week);
            $end_week = strtotime("next saturday", $start_week);
            $start_week = date("Y-m-d", $start_week);
            $end_week = date("Y-m-d", $end_week);

            if ($fetchType == 'merchants') {
                $merchant = Agent::where(['agents.user_id' => $userId, 'agents.is_active' => '1'])->orderBy('created_at', 'asc');
                if ($search == 'lastWeek') {
                    $merchant->whereBetween('agents.created_at', [$start_week, $end_week]);
                } else if ($search == '30Days') {
                    $merchant->whereDate('agents.created_at', '>=',  Carbon::now()->subDays(30));
                    //$merchant->whereMonth('agents.created_at', '>=', Carbon::now()->subMonth()->month);
                } else if ($search == '7Days') {
                    $merchant->whereDate('agents.created_at', '>=',  Carbon::now()->subDays(7));
                } else {
                    $merchant->where('agents.created_at', 'like', '%' . date('Y-m-d') . '%');
                }
                if ($search == 'today') {
                    $merchant = $merchant->groupBy('x')
                        ->get(array(
                            DB::raw('CONCAT(hour(agents.created_at), " hours") as x'),
                            DB::raw('count(*) as y'),
                            DB::raw("DATE_FORMAT(agents.created_at, '%h %p') as time")
                        ));
                } else {
                    $merchant = $merchant->groupBy('x')
                        ->get(array(
                            DB::raw('Date(agents.created_at) as x'),
                            DB::raw('count(*) as y')
                        ));
                }
                $resp['merchant'] = $merchant;
            } else if ($fetchType == 'cw') {
                $transaction = AepsTransaction::where('user_id', '=', $userId)->where('transaction_type', 'cw')
                ->where('aeps_transactions.status', 'success')
                ->orderBy('created_at', 'asc');
                if ($search == 'lastWeek') {
                    $transaction->whereBetween('aeps_transactions.created_at', [$start_week, $end_week]);
                } else if ($search == '30Days') {
                    $transaction->whereDate('aeps_transactions.created_at', '>=',  Carbon::now()->subDays(30));
                } else if ($search == '7Days') {
                    $transaction->whereDate('aeps_transactions.created_at', '>=',  Carbon::now()->subDays(7));
                } else {
                    $transaction->where('aeps_transactions.created_at', 'like', '%' . date('Y-m-d') . '%');
                }
                if ($search == 'today') {
                    $transaction = $transaction->groupBy('x')
                        ->get(array(
                            DB::raw('hour(aeps_transactions.created_at) as x'),
                            DB::raw('sum(transaction_amount) as y'),
                            DB::raw("DATE_FORMAT(aeps_transactions.created_at, '%h %p') as time")
                        ));
                } else {
                    $transaction = $transaction->groupBy('x')
                        ->get(array(
                            DB::raw('Date(aeps_transactions.created_at) as x'),
                            DB::raw('sum(transaction_amount) as y')
                        ));
                }

                $resp['merchant'] = $transaction;
            }
        }
        return $resp;
    }

    public static function aepsChartByBank($userId)
    {
        $resp['merchant'] = 0;
        if (!empty($userId)) {
            $transaction = AepsTransaction::where('user_id', '=', $userId)
                ->join('banks', 'banks.iin', 'aeps_transactions.bankiin')
                ->where('transaction_type', 'cw')
                ->where('status', 'success')
                ->groupBy('bankiin')
                ->get(array(
                    DB::raw('bank as x'),
                    DB::raw('sum(transaction_amount) as y')
                ));
            $resp['merchant'] = $transaction;
        }
        return $resp;
    }

    /**
     * Convert amount to the crore format
     */
    public static function numberFormat($number, $decimal = 2)
    {
        $minNum = 10000000;

        if ($number >= $minNum) {
            $number = number_format($number / $minNum, 2) . ' Cr';
        } else if ($number >= 100000) {
            //if amount not >= 1 Cr.
            $number = number_format($number / 100000, 2) . ' lac';
        } else if ($number >= 1000) {
            //if amount not >= 1 Lac.
            $number = number_format($number / 1000, 2) . ' k';
        } else {
            $number = number_format($number, $decimal);
        }

        return $number;
    }

    public static function getGlobalConfig($values, $slug)
    {
        $data = GlobalConfig::select($values)->where('slug', $slug)->first();

        return $data;
    }



    /**
     * Get value from user config
     */
    public static function getUserConfig(string $values, int $userId)
    {
        $data = DB::table('user_config')
            ->select($values)
            ->where('user_id', $userId)
            ->first();

        return $data;
    }


      /**
     * AEPS Credit Enable or not
    */
    public static function checkAepsCreditEnable($userId, $merchantCode, $route, $reqType)
    {
        $resp['status'] = false;
        $resp['message'] = "credit not enable.";
        $agentData = DB::table('agents')
                        ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                        ->select('is_credit_enable', 'credit_value', DB::raw("json_extract(credit_value, '$.$route') as route"))
                        ->first();
        if (isset($agentData)) {
            if ($agentData->is_credit_enable == '1' && isset($agentData->credit_value)) {
                $data = json_decode($agentData->credit_value, TRUE);
                if (isset($data)) {
                    $routeData = $data[$route];
                    if (isset($routeData)) {
                        if ($routeData[$reqType] == 1) {
                            $resp['status'] = true;
                            $resp['message'] = "credit enable successfully.";
                        }
                        $resp['message'] = "agent route found : true,  $userId, $merchantCode, $route, $reqType";
                    }
                    $resp['message'] = "agent found : true : $userId, $merchantCode, $route, $reqType";
                } else {
                    $resp['message'] = "agent found : true : $userId, $merchantCode, $route, $reqType";
                }
            }
            $resp['message'] = "agent found : $userId, $merchantCode, $route, $reqType";
        }
        return $resp;
    }


    /**
     * Check service callback is enable or not
     */
    public static function checkIsCallbackActive($userId, $service, $search)
    {

        $isServiceActive = false;

        switch ($service) {
            case 'upi_stack':
            case 'upi_collect':
                $userConfig = DB::table('user_config')
                    ->selectRaw("`user_id`, `upi_stack_callbacks` AS service")
                    ->where('user_id', $userId)
                    ->first();
                    // dd($userConfig);
                break;

            case 'va':
                $userConfig = DB::table('user_config')
                    ->selectRaw("`user_id`, `va_callbacks` AS service")
                    ->where('user_id', $userId)
                    ->first();
                break;

            case 'smart_collect':
                $userConfig = DB::table('user_config')
                    ->selectRaw("`user_id`, `smart_collect_callbacks` AS service")
                    ->where('user_id', $userId)
                    ->first();
                break;

            default:
                return $isServiceActive;
                break;
        }


        if (!empty($userConfig)) {
            $usersIdsArr = explode(",", $userConfig->service);
            if (in_array($search, $usersIdsArr)) {
                $isServiceActive = true;
            }
        }

        return $isServiceActive;
    }


    /**
     * Check service settlement is enable or not
     */
    public static function checkIsSettlementActive($userId, $service)
    {

        $isServiceActive = false;

        switch ($service) {
            case 'upi_stack':
                $userConfig = DB::table('user_config')
                    ->selectRaw("`user_id`, `upi_stack_settlements` AS service")
                    ->where('user_id', $userId)
                    ->first();
                break;

            case 'smart_collect':
                $userConfig = DB::table('user_config')
                    ->selectRaw("`user_id`, `smart_collect_settlements` AS service")
                    ->where('user_id', $userId)
                    ->first();
                break;

            default:
                return $isServiceActive;
                break;
        }


        if (!empty($userConfig)) {
            $usersIdsArr = explode(",", $userConfig->service);
            if ($usersIdsArr) {
                $isServiceActive = true;
            }
        }

        return $isServiceActive;
    }


    /**
     * Check api enable or not
     */
    public static function checkIsApiRootActive($userId, $service, $search)
    {

        $isServiceActive = false;

        switch ($service) {
            case 'upi_stack':
                break;

            case 'smart_collect':
                $userConfig = DB::table('user_config')
                    ->selectRaw("`user_id`, `smart_collect_apis` AS service")
                    ->where('user_id', $userId)
                    ->first();
                break;

            default:
                return $isServiceActive;
                break;
        }


        if (!empty($userConfig)) {
            $usersIdsArr = explode(",", $userConfig->service);
            if (in_array($search, $usersIdsArr)) {
                $isServiceActive = true;
            }
        }

        return $isServiceActive;
    }


    /**
     * Check User service is active in global config
     */
    public static function checkIsServiceActive($slug, $userId)
    {
        // dd($slug);
        $globalConfig = DB::table('global_config')
            ->select('*')
            ->where('slug', $slug)
            ->first();

        $isServiceActive = false;

        if (!empty($globalConfig)) {
            if ($globalConfig->attribute_1 == '1') {
                $isServiceActive = true;
            } else {
                $usersIdsArr = explode(",", $globalConfig->attribute_2);
                if (in_array($userId, $usersIdsArr)) {
                    $isServiceActive = true;
                }
            }
        }

        return $isServiceActive;
    }


    /**
     * Check that load money request is enable or not
     */
    public static function isLoadMoneyRequestActive($userId)
    {
        $isActive = DB::table('user_config')
            ->select('load_money_request')
            ->where('user_id', $userId)
            ->where('load_money_request', '1')
            ->first();

        if (!empty($isActive)) {
            return true;
        }

        return false;
    }

    public static function getUserStatusMessage($status = '0')
    {
        $GlobalConfig = GlobalConfig::select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4')
                            ->where(['slug' => 'user_login_message'])
                            ->first();
        $message = "Your account is inactive. Please contact to your Account Coordinator";

        if ($status == '0') {
            $message = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : "Your account is initiate. Please contact  to your Account Coordinator";
        } else if ($status == '2') {
            $message = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "Your account is inactive. Please contact  to your Account Coordinator";
        } else if ($status == '3') {
            $message = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : "Your account is suspended. Please contact  to your Account Coordinator";
        } else if ($status == '4') {
            $message = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : "Your account is permanently blocked. Please contact  to your Account Coordinator";
        } else if ($status =='5'){
            $message = "Please complete your profile";
        }

        return $message;
    }


    /**
     * Get Unsettled Balance
     */
    public static function getUnsettledBalance($userId, $service, $onlyDate = false)
    {
        $days = DB::table('global_config')
            ->select('attribute_1')
            ->where('slug', 'show_unsettled_balance')
            ->first();

        $days = empty($days) ? 90 : intval($days->attribute_1);
        $timestamp = date('Y-m-d', strtotime("-{$days} days", time()));

        if($onlyDate === true){
            return $timestamp;
        }
        
        switch ($service) {
            case 'smart_collect':
                $balance = DB::table('cf_merchants_fund_callbacks')
                    ->selectRaw("SUM(amount) as amt")
                    ->where('user_id', $userId)
                    ->where('is_trn_credited', '0')
                    ->whereDate('created_at', '>=', $timestamp)
                    ->first();
                break;

            case 'upi_stack':
                $balance = DB::table('upi_collects')
                    ->selectRaw("SUM(amount) as amt")
                    ->where('user_id', $userId)
                    ->where('is_trn_credited', '0')
                    ->whereDate('created_at', '>=', $timestamp)
                    ->first();
                break;

            case 'virtual_account':
                $balance = DB::table('upi_callbacks')
                    ->selectRaw("SUM(amount) as amt")
                    ->where('user_id', $userId)
                    ->where('is_trn_credited', '0')
                    ->where('root_type', 'ibl_tpv')
                    ->whereDate('created_at', '>=', $timestamp)
                    ->first();
                break;
            case 'threshold':
                    $balance = DB::table('user_config')
                        ->select("threshold as amt")
                        ->where('user_id', $userId)
                        ->first();
                    break;
            default:
                return 0;
                break;
        }

        if (!empty($balance)) {
            return $balance->amt;
        }

        return 0;
    }

    /**
     * apiLogEnableDisable
     *
     * @param  mixed $methodName
     * @return void
     */
    public static function apiLogEnableDisable($methodName)
    {
        $GlobalConfig = DB::table('global_config')
            ->select('attribute_1', 'attribute_2', 'attribute_3')
            ->where(['slug' => 'api_log_enable_disable'])
            ->first();
        $bool = 0;
        $method = "";
        $status = true;
        if (isset($GlobalConfig)) {
            $bool = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $method = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "";

            if ($bool && isset($method) && !empty($method)) {
                $methodArray = json_decode($method, 1);
                if (array_key_exists($methodName, $methodArray) && isset($methodArray[$methodName]) && $methodArray[$methodName] == 0)
                {
                    $status = false;
                }
            }
        }
        return $status;
    }


    /**
     * apiLogHeaderEnableDisable
     *
     * @param  mixed $methodName
     * @return void
     */
    public static function apiLogHeaderEnableDisable($methodName)
    {
        $GlobalConfig = DB::table('global_config')
            ->select('attribute_1', 'attribute_2', 'attribute_3')
            ->where(['slug' => 'api_log_header_enable_disable'])
            ->first();
        $bool = 0;
        $method = "";
        $status = true;
        if (isset($GlobalConfig)) {
            $bool = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $method = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "";

            if ($bool && isset($method) && !empty($method)) {
                $methodArray = json_decode($method, 1);
                if (array_key_exists($methodName, $methodArray) && isset($methodArray[$methodName]) && $methodArray[$methodName] == 0)
                {
                    $status = false;
                }
            }
        }
        return $status;
    }

   /**
     * Get dates in bitween dates
     */
    public function dateRange($start, $end, $diff = 'P1D')
    {
        $period = new DatePeriod(
            new DateTime($start),
            new DateInterval($diff),
            new DateTime(date('Y-m-d',strtotime("+1 day", strtotime($end))))
        );

        return $period;
    }


    /**
     * Get Dates
     */
    public function getStartEndDate($filter, &$startDate, &$endDate)
    {
        switch ($filter) {
            case 'D7':
                //getting date range
                $startDate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 6 days'));
                $endDate = date('Y-m-d');
                break;


            case 'D30':
                //getting date range
                $startDate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 29 days'));
                $endDate = date('Y-m-d');
                break;


            case 'D1':
            default:

                //getting date range
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d');

                break;
        }
    }

    public static function internalTransaferAmountCheck($userTransactionAmount, $transactionAmount, $threshold)
    {
        $resp['message'] = "";
        $availBalance = 0;
        # step 1 check transaction amount grater then 0
        if ($transactionAmount > 0) {
            # step 2 check primary transaction amount grater then threshold
            if ($userTransactionAmount > 0) {
                if ($userTransactionAmount >= $threshold) {
                    $availBalance = $userTransactionAmount - $threshold;
                    $additional = ($availBalance - $transactionAmount);
                    $additional = str_replace("-", "", $additional);

                } else {
                    $availBalance = 0;
                    if (($threshold - $userTransactionAmount) > 0) {
                        $additional = ($threshold - $userTransactionAmount )+ $transactionAmount;
                    } else {
                        $additional = ($threshold - $userTransactionAmount)
                        - $transactionAmount;
                    }
                    $additional = str_replace("-", "", $additional);
                }
                # step 3 check primary transaction amount grater then avail Balance
                if ($transactionAmount >= $availBalance) {
                    $resp['message'] = '₹ '.round($additional, 2) .' Additional Funds Are Required To Proceed. Available Balance Is ₹ '.round($availBalance, 2).'. Add  Balance To Proceed Or Retry With A Different Amount';
                } else {
                    $resp['message'] = '₹ '.round($availBalance - $transactionAmount, 2) .' Additional Funds Are Required To Proceed. Available Balance Is ₹ '.round($availBalance, 2).'. Add  Balance To Proceed Or Retry With A Different Amount';
                }
            } else {
                $additional = ($threshold + $transactionAmount);
                $additional = str_replace("-", "", $additional);
                $resp['message'] = '₹ '.round($additional, 2) .' Additional Funds Are Required To Proceed. Available Balance Is ₹ '.round(0, 2).'. Add  Balance To Proceed Or Retry With A Different Amount';
            }

        } else {
            $resp['message'] = "Invalid transaction amount";
        }

        return $resp;
    }

    public static function businessTypeList()
    {
        return [
            'Proprietorship',
            'Partnership',
            'Private Limited',
            'Public Limited',
            'LLP',
            'Trust',
            'Society',
            'NGO',
            'HUF',
            'Not Registered'
        ];
    }

    public function arrayReplace($array, $findArray = [])
    {
        if (is_array($array) && is_array($findArray)) {
    		$newArr = array();
    		foreach ($array as $k => $v) {
    		    $key = array_key_exists( $k, $findArray) ? $findArray[$k] : $k;
    		    $newArr[$key] = is_array($v) ? self::arrayReplace($v, $findArray) : $v;
    		}
    		return $newArr;
    	}

      return $array;
   }


   public function arrayKeysMulti(array $array)
    {
        $keys = array();

        foreach ($array as $key => $value) {
            $keys[] = self::case($key, 'l');

            if (is_array($value)) {
                $keys = array_merge($keys, self::arrayKeysMulti($value));
            }
        }

        return $keys;
    }


  
   /**
     * encAadharOfMerchant
     *
     * @param  mixed $userId
     * @param  mixed $merchantCode
     * @return void
     */

   public static function encAadhar($aadhaarNumber)
  {
        $encryptionKey = env('AEPS_ENCRYPTION_KEY');
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($aadhaarNumber,'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
        $encryptedData = base64_encode($iv . $ciphertext);
        
        return $encryptedData;
       
    } 



    /**
     * Method sendSlackRequestData
     *
     * @param $service $service [explicite description]
     * @param $userId $userId [explicite description]
     *
     * @return void
     */
    public static function sendSlackRequestData($service, $userId)
    {
        $service = @DB::table('global_services')->where('service_id', $service)
            ->select('service_name')->first()->service_name;

        $users = DB::table('users')
            ->select('name', 'email', 'mobile', 'created_at')
            ->where('id', $userId)
            ->first();

        if (!empty($users)) {
            $url = url('admin/userprofile/status').'/'.$userId;
            $mobile  = $users->mobile;
            $name  = $users->name;
            $email  = $users->email;
            $date = $users->created_at;
            $parameters = [
                "blocks" => [
                    [
                        "type" => "header",
                        "text" => [
                            "type" => "plain_text",
                            "text" => "New Service request",
                            "emoji" => true
                        ]
                    ],

                    [
                        "type" => "section",
                        "fields" => [
                            [
                                "type" => "mrkdwn",
                                "text" => "*Service Name:*\n$service"
                            ],
                            [
                                "type" => "mrkdwn",
                                "text" => "*Created By:*\n<tel:$mobile|$name>"
                            ]
                        ]
                    ],
                    [
                        "type" => "section",
                        "fields" => [
                            [
                                "type" => "mrkdwn",
                                "text" => "*When:*\n$date"
                            ],
                            [
                                "type" => "mrkdwn",
                                "text" => "*Email:*\n$email"
                            ]
                        ]
                    ],
                    [
                        "type" => "section",
                        "fields" => [
                            [
                                "type" => "mrkdwn",
                                "text" => "*Action:*\n<$url | View request>"
                            ]
                        ]
                    ]
                ]
            ];


           //self::callApi(env('SLACK_REQUEST_URL'), ['Content-Type' => 'application/json'], '', $parameters, 'POST');
        }

        return false;
    }

    public static function checksum($string){
        $signature = hash_hmac('sha256', $string, 'abcd@123', true);
        $encodedSignature = base64_encode($signature);
        return $encodedSignature;
    }

    public  static function checksumStatus($data)
    {
        $requestData = [
            "amt" => $data['amount'],
            "cir" => "4",
            "cn" => $data['cn'],
            "op" => $data['op'],
            "pwd" => "testalpha1@123",
            "reqid" => $data['customer_ref_id'],
            "uid" => $data['user_id']
        ];

        $requestDataString = json_encode($requestData);
        $checksumResponse = self::checksum($requestDataString);
        $headers = array(
            'Content-Type: application/json',
            'checkSum: ' . $checksumResponse,
        );

        return $headers;
    }



public static function getUsersAssignedToReseller()
{
    $userId = Auth::id();

    $users = User::where('reseller', $userId)->get();
    $userIds = $users->pluck('id')->toArray();
    return $userIds;
}

}
