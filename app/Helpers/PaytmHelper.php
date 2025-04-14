<?php
namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Integration;
use CommonHelper;
use App\Models\TransactionHistory;
class PaytmHelper
{
    private $key;
    private $secret;
    private $guid;
    private $baseUrl;

    private static $iv = "@@@@&&&&####$$$$";

    public function __construct()
    {
        $this->key = base64_decode(env('PAYTM_KEY'));
        $this->secret = base64_decode(env('PAYTM_SECRET'));
        $this->baseUrl = env('PAYTM_BASE_URL');
        $this->guid = base64_decode(env('PAYTM_GUID'));
    }

    public function getsubwalletguid()
    {
        $paytmParams = [];

        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

        $checksum = self::generateSignature(
            $post_data,
            $this->secret
        );

        $x_mid = $this->key;
        $x_checksum = $checksum;
        $header = [
            "Content-Type: application/json",
            "x-mid: " . $x_mid,
            "x-checksum: " . $x_checksum,
        ];

        $result = CommonHelper::curl(
            $this->baseUrl . 'account/list',
            "POST",
            json_encode($paytmParams),
            $header,
            'yes'
           
        );
        
        $response['data'] = json_decode($result['response']);
        
        return $response;
    }

    public function paytmOrderWallet($subwalletGuid,$orderId,$beneficiaryPhoneNo,$amount)
    {
        $paytmParams = [
            "subwalletGuid" => $subwalletGuid,
            "orderId" => $orderId,
            "beneficiaryPhoneNo" => $beneficiaryPhoneNo,
            "amount" => $amount,
        ];
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $checksum = self::generateSignature(
            $post_data,
            $this->secret
        );

        $x_mid = $this->key;
        $x_checksum = $checksum;
        $header = [
            "Content-Type: application/json",
            "x-mid: " . $x_mid,
            "x-checksum: " . $x_checksum,
        ];
        $result = CommonHelper::curl(
            $this->baseUrl . 'disburse/order/wallet/communication',
            "POST",
            $post_data,
            $header,
            'yes'
        );
        
        $response['data'] = json_decode($result['response']);
        
        return $response;
    }
    public function paytmOrderBank($orderId,$beneficiaryAccount,$beneficiaryIFSC,$amount,$purpose,$date)
    {
        $paytmParams = [
            "subwalletGuid" => 'bec13d57-d6ed-11ea-b443-fa163e429e83',
            "orderId" => $orderId,
            "beneficiaryAccount" => $beneficiaryAccount,
            "beneficiaryIFSC" => $beneficiaryIFSC,
            "amount" => $amount,
            "purpose" => $purpose,
            "date" => $date,
        ];
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $checksum = self::generateSignature(
            $post_data,
            $this->secret
        );

        $x_mid = $this->key;
        $x_checksum = $checksum;
        $header = [
            "Content-Type: application/json",
            "x-mid: " . $x_mid,
            "x-checksum: " . $x_checksum,
        ];
        $result = CommonHelper::curl(
            $this->baseUrl . 'disburse/order/bank',
            "POST",
            $post_data,
            $header,
            'yes',
            1,
            'paytm',
            'orderBank',
            $orderId
        );
        $response['data'] = json_decode($result['response']);
        TransactionHistory::insert_data([
            'user_id' => 1,
            'header' => json_encode($header),
            'service_id' => 1,
            'request' => json_encode($paytmParams),
            'response' => $result['response'],
            'transaction_id' => 1,
        ]);

        return $response;
    }
    public function paytmOrderReport($subwalletGuid, $fromDate, $toDate)
    {
        $paytmParams = [
            "subwalletGuid" => $subwalletGuid,
            "fromDate" => $fromDate,
            "toDate" => $toDate,
        ];
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $checksum = self::generateSignature(
            $post_data,
            $this->secret
        );

        $x_mid = $this->key;
        $x_checksum = $checksum;
        $header = [
            "Content-Type: application/json",
            "x-mid: " . $x_mid,
            "x-checksum: " . $x_checksum,
        ];
        $result = CommonHelper::curl(
            $this->baseUrl . 'order/report',
            "POST",
            $post_data,
            $header,
            'yes'
        );
        $response['data'] = json_decode($result['response']);
       
        return $response;
    }
    public function paytmOrderStatement($subwalletGuid, $fromDate, $toDate)
    {
        $paytmParams = [
            "subwalletGuid" => $subwalletGuid,
            "fromDate" => $fromDate,
            "toDate" => $toDate,
        ];
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $checksum = self::generateSignature(
            $post_data,
            $this->secret
        );

        $x_mid = $this->key;
        $x_checksum = $checksum;
        $header = [
            "Content-Type: application/json",
            "x-mid: " . $x_mid,
            "x-checksum: " . $x_checksum,
        ];
        $result = CommonHelper::curl(
            $this->baseUrl . 'account/statement',
            "POST",
            $post_data,
            $header,
            'yes'
        );
        $response['data'] = json_decode($result['response']);
   
        return $response;
    }

    public function paytmOrderQuery($orderId)
    {
        $paytmParams = [
            "subwalletGuid" => 'bec13d57-d6ed-11ea-b443-fa163e429e83',
            "orderId" => $orderId,
        ];
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $checksum = self::generateSignature(
            $post_data,
            $this->secret
        );

        $x_mid = $this->key;
        $x_checksum = $checksum;
        $header = [
            "Content-Type: application/json",
            "x-mid: " . $x_mid,
            "x-checksum: " . $x_checksum,
        ];
        $result = CommonHelper::curl(
            $this->baseUrl . 'disburse/order/query',
            "POST",
            $post_data,
            $header,
            'yes',1,'paytm','statusCheck',$orderId
        );
        $response['data'] = json_decode($result['response']);
       
        return $response;
    }
    public function paytmBeneficiaryValidate($subwalletGuid,$orderId,$beneficiaryAccount,$beneficiaryIFSC)
    {
        $paytmParams = [
            "subwalletGuid" => $subwalletGuid,
            "orderId" => $orderId,
            "beneficiaryAccount" => $beneficiaryAccount,
            "beneficiaryIFSC" => $beneficiaryIFSC,
        ];
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $checksum = self::generateSignature(
            $post_data,
            $this->secret
        );

        $x_mid = $this->key;
        $x_checksum = $checksum;
        $header = [
            "Content-Type: application/json",
            "x-mid: " . $x_mid,
            "x-checksum: " . $x_checksum,
        ];
        $result = CommonHelper::curl(
            $this->baseUrl . 'beneficiary/validate',
            "POST",
            $post_data,
            $header,
            'yes'
        );
        
        $response['data'] = json_decode($result['response']);
      
        return $response;
    }

    public static function encrypt($input, $key)
    {
        $key = html_entity_decode($key);

        if (function_exists('openssl_encrypt')) {
            $data = openssl_encrypt($input, "AES-128-CBC", $key, 0, self::$iv);
        } else {
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
            $input = self::pkcs5Pad($input, $size);
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
            mcrypt_generic_init($td, $key, self::$iv);
            $data = mcrypt_generic($td, $input);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $data = base64_encode($data);
        }

        return $data;
    }

    public static function decrypt($encrypted, $key)
    {
        $key = html_entity_decode($key);

        if (function_exists('openssl_decrypt')) {
            $data = openssl_decrypt(
                $encrypted,
                "AES-128-CBC",
                $key,
                0,
                self::$iv
            );
        } else {
            $encrypted = base64_decode($encrypted);
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
            mcrypt_generic_init($td, $key, self::$iv);
            $data = mdecrypt_generic($td, $encrypted);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $data = self::pkcs5Unpad($data);
            $data = rtrim($data);
        }
        return $data;
    }

    public static function generateSignature($params, $key)
    {
        if (!is_array($params) && !is_string($params)) {
            throw new Exception(
                "string or array expected, " . gettype($params) . " given"
            );
        }

        if (is_array($params)) {
            $params = self::getStringByParams($params);
        }
        return self::generateSignatureByString($params, $key);
    }

    public static function verifySignature($params, $key, $checksum)
    {
        if (!is_array($params) && !is_string($params)) {
            throw new Exception(
                "string or array expected, " . gettype($params) . " given"
            );
        }
        if (isset($params['CHECKSUMHASH'])) {
            unset($params['CHECKSUMHASH']);
        }
        if (is_array($params)) {
            $params = self::getStringByParams($params);
        }
        return self::verifySignatureByString($params, $key, $checksum);
    }

    private static function generateSignatureByString($params, $key)
    {
        $salt = self::generateRandomString(4);

        return self::calculateChecksum($params, $key, $salt);
    }

    private static function verifySignatureByString($params, $key, $checksum)
    {
        $paytm_hash = self::decrypt($checksum, $key);
        $salt = substr($paytm_hash, -4);
        return $paytm_hash == self::calculateHash($params, $salt)
            ? true
            : false;
    }

    private static function generateRandomString($length)
    {
        $random = "";
        srand((float) microtime() * 1000000);

        $data =
            "9876543210ZYXWVUTSRQPONMLKJIHGFEDCBAabcdefghijklmnopqrstuvwxyz!@#$&_";

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, rand() % strlen($data), 1);
        }

        return $random;
    }

    private static function getStringByParams($params)
    {
        ksort($params);
        $params = array_map(function ($value) {
            return $value !== null && strtolower($value) !== "null"
                ? $value
                : "";
        }, $params);
        return implode("|", $params);
    }

    private static function calculateHash($params, $salt)
    {
        $finalString = $params . "|" . $salt;
        $hash = hash("sha256", $finalString);

        return $hash . $salt;
    }

    private static function calculateChecksum($params, $key, $salt)
    {
        $hashString = self::calculateHash($params, $salt);
        return self::encrypt($hashString, $key);
    }

    private static function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private static function pkcs5Unpad($text)
    {
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text)) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}
