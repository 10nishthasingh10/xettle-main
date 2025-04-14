<?php

namespace App\Helpers\Offers;


class OfferRequestHelper
{
    private const CIPHER_ALGO = "AES-256-CBC";
    private const SIGN_APP_NAME = "offerXtlAPP_1_0";

    private string $baseKey;
    private string $webIV;


    public function __construct()
    {
        $this->baseKey = env('OFFER_REQHASH_BASE_KEY', '');
        $this->webIV = base64_decode(env('OFFER_REQHASH_WEBIV', ''));
    }


    /**
     * Validate Request
     */
    public function validateRequest($requestBody, $lastSegment)
    {

        $decryptedArray = [
            'status' => 'UNAUTHORIZED',
            'message' => 'Invalid request auth token.'
        ];


        $key = $this->getKey();
        $iv = $this->getWebIv();


        $decrypt = $this->decrypt($requestBody, $key, $iv);


        if (!empty($decrypt)) {

            $payloadDec = $this->getExplode($decrypt);


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
                    'message' => 'Invalid payload or expired.'
                ];
            }
        }
        return $decryptedArray;
    }


    /**
     * get user key
     */
    private function getKey()
    {
        return $this->baseKey;
    }


    /**
     * get web IV
     */
    private function getWebIv()
    {
        return $this->webIV;
    }


    /**
     * end point check
     */
    private function endPointCheck($reqSegment, $payloadSegment)
    {
        if ($reqSegment === $payloadSegment) {
            return true;
        }
        return false;
    }



    /**
     * explode string
     */
    private function getExplode($payload)
    {

        $array  = explode('*****', $payload);

        $resp['status'] = false;
        $resp['privateKey'] = "";
        $resp['segment'] = "";
        $resp['userid'] = "";
        $resp['time'] = time();

        if (count($array) > 1) {
            $resp['privateKey'] = $array[0];
            $array2  = explode('@@@@@@', $array[1]);
            if (count($array2) > 1) {
                $resp['segment'] = $array2[0];
                $resp['timestamp'] = $array2[1];
                // $resp['status'] = true;

                if ((intval($resp['timestamp'] / 1000) + 60) > time()) {
                    // if ((intval($resp['timestamp']) + 60) > time()) {
                    $resp['status'] = true;
                }
            }
        }
        // }

        return $resp;
    }

    public function decrypt($encryptedText, $key, $iv)
    {
        $decryptedText = openssl_decrypt(base64_decode($encryptedText), self::CIPHER_ALGO, base64_decode($key), OPENSSL_RAW_DATA, $iv);
        return $decryptedText;
    }
}
