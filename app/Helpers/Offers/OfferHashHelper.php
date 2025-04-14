<?php

namespace App\Helpers\Offers;


class OfferHashHelper
{
    private const CIPHER_ALGO = "AES-256-CBC";
    private string $webIV = "";
    private string $baseKey;


    public function __construct()
    {
        $this->baseKey = env('OFFER_HASHING_BASE_KEY', '');
        $this->webIV = base64_decode(env('OFFER_HASHING_WEBIV', ''));
    }


    public function encrypt($plainText)
    {
        $encryptedText = base64_encode(openssl_encrypt($plainText, self::CIPHER_ALGO, base64_decode($this->baseKey), OPENSSL_RAW_DATA, $this->webIV));
        return $encryptedText;
    }



    public function decrypt($encryptedText)
    {
        $decryptedText = openssl_decrypt(base64_decode($encryptedText), self::CIPHER_ALGO, base64_decode($this->baseKey), OPENSSL_RAW_DATA, $this->webIV);
        return $decryptedText;
    }
}
