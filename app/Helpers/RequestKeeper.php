<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class RequestKeeper
{
    private const CIPHER_ALGO = "AES-256-CBC";
    private const SIGN_APP_NAME = "xettleAPP2_0";

    private string $webIV = "";
    private string $webIV2 = "";
    public string  $USERID = "";
    private string $baseKey;


    public function __construct()
    {
        $this->baseKey = env('REQHASH_BASE_KEY', '');
        $this->webIV = base64_decode(env('REQHASH_WEBIV', ''));
        $this->webIV2 = base64_decode(env('REQHASH_WEBIV2', ''));
    }


    /**
     * get user key
     */
    public function getKey()
    {
        // if ($area == 'app') {
        //     $key = "d0RERVI5ang2SktoVlVmYU1wR3hsaWtSZ1QzQzc3dUQ=";
        // } else {
        // $key = "ZzNQTFFKNHBzQ0hIQzFvWmRvQ0F3MU1JQkRKZXpvMnc=";
        // }


        if (auth('sanctum')->check()) {
            // if ($area == 'app')
            //     SELF::$IV2 .= substr(auth('sanctum')->user()->user_id, -4);
            // else
            $this->webIV2 .= substr(auth('sanctum')->user()->account_number, -4);

            $identifierToken = DB::table('personal_access_tokens')
                ->select('identifier_token')
                ->where('tokenable_id', auth('sanctum')->user()->id)
                ->orderBy('id', 'desc')
                ->first();

            if (!empty($identifierToken->identifier_token)) {
                $this->baseKey = $identifierToken->identifier_token;
            }
        }

        return $this->baseKey;
    }



    /**
     * Validate Request
     */
    public function validateRequest($requestBody, $lastSegment, $area, $request)
    {

        $decryptedArray = [
            'status' => 'UNAUTHORIZED',
            'message' => 'Session expired. please login.'
        ];


        $key = $this->getKey();

        if (auth('sanctum')->check()) {

            // if (in_array(auth('sanctum')->user()->status, ['inactive', 'suspended', 'delete', 'blocked'])) {
            //     return array('status' => 'UNAUTHORIZED', 'message' => "Your account is " . auth('sanctum')->user()->status . ". Please contact support.");
            // }

            // if (auth('sanctum')->user()->mobile == '9651807986' || auth('sanctum')->user()->mobile == '6393784138') {
            //     return array('status' => 'SUCCESS', 'message' => 'Request successful validated.');
            // }

            // if ($area == 'app')
            //     $iv  =  SELF::$IV2;
            // else
            $iv  =  $this->webIV2;
        } else {
            // if ($area == 'app')
            //     $iv  =  SELF::IV;
            // else
            $iv  =  $this->webIV;
        }

        $decrypt = $this->decrypt($requestBody, $key, $iv);

        // dd($decrypt, $requestBody, $key, $iv);

        if (!empty($decrypt)) {

            $payloadDec = $this->getExplode($decrypt, $area);

            if ($payloadDec['status']) {
                if (
                    $this->endPointCheck($lastSegment, $payloadDec['segment']) &&
                    $payloadDec['privateKey'] === self::SIGN_APP_NAME
                ) {
                    $decryptedArray = [
                        'status' => 'SUCCESS',
                        'message' => 'Request successful validated.'
                    ];
                }
            } else {
                $decryptedArray = [
                    'status' => 'UNAUTHORIZED',
                    'message' => 'Invalid payload.'
                ];
            }
        }
        return $decryptedArray;
    }



    /**
     * Endpoint check
     */
    public function endPointCheck($reqSegment, $payloadSegment)
    {

        if ($reqSegment == $payloadSegment) {
            return true;
        }
        return false;
    }



    public function getExplode($payload, $area)
    {

        // if ($area == 'app') {
        //     $array  = explode('#####', $payload);

        //     $resp['status'] = false;
        //     $resp['privateKey'] = "";
        //     $resp['segment'] = "";
        //     $resp['userid'] = "";
        //     if (count($array) > 1) {
        //         $resp['privateKey'] = $array[0];
        //         $array2  = explode('||||||', $array[1]);
        //         if (count($array2) > 1) {
        //             $resp['segment'] = $array2[0];
        //             $resp['userid'] = $array2[1];
        //             $resp['status'] = true;
        //         }
        //     }
        // } else {
        $array  = explode('*****', $payload);

        $resp['status'] = false;
        $resp['privateKey'] = "";
        $resp['segment'] = "";
        $resp['userid'] = "";
        if (count($array) > 1) {
            $resp['privateKey'] = $array[0];
            $array2  = explode('@@@@@@', $array[1]);
            if (count($array2) > 1) {
                $resp['segment'] = $array2[0];
                $resp['timestamp'] = $array2[1];
                // $resp['status'] = true;

                if ((intval($resp['timestamp'] / 1000) + 60) > time()) {
                    $resp['status'] = true;
                }
            }
        }
        // }

        return $resp;
    }



    /**
     * generate cryptographically secure pseudo-random bytes | Session Key
     *
     * @param string $length
     * @return string
     */
    public static function generateKey($length = '32')
    {
        $key = '';
        list($usec, $sec) = explode(' ', microtime());
        mt_srand((float) $sec + ((float) $usec * 100000));

        $inputs = array_merge(range('z', 'a'), range(0, 9), range('A', 'Z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $inputs[mt_rand(0, 61)];
        }

        return $key; //base64_encode($key);
    }


    /**
     * Encode to Base64
     *
     * @param string $str
     * @return void
     */
    // public static function getEncoded($str = '')
    // {
    //     return base64_encode($str);
    // }


    /**
     * Base64 to String
     *
     * @param string $str
     * @return void
     */
    // public static function getDecoded($str = '')
    // {
    //     return base64_decode($str);
    // }


    /**
     * Encrypt Input
     *
     * @param string $plainText
     * @return void
     */
    public function encrypt($plainText, $key)
    {
        $encryptedText = base64_encode(openssl_encrypt($plainText, self::CIPHER_ALGO, base64_decode($key), OPENSSL_RAW_DATA, $this->webIV));
        return $encryptedText;
    }


    /**
     * Decrypt Input
     *
     * @param mix $plainText
     * @return void
     */
    public function decrypt($encryptedText, $key, $iv)
    {
        $decryptedText = openssl_decrypt(base64_decode($encryptedText), self::CIPHER_ALGO, base64_decode($key), OPENSSL_RAW_DATA, $iv);
        return $decryptedText;
    }
}
