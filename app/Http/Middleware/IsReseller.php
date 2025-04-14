<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use App\Models\Reseller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
class IsReseller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

     public function handle($request, Closure $next)
     {
         try {
             $token = $request->header('Authorization');
             if (!$token) {
                return response()->json(['message' => 'Token is missing'], 401);
             }
             // $decryptedToken = $this->opensslDecrypt($token);
             // $createdAt = Carbon::parse($decryptedToken['timestamp']);
             // $expiryTime = $createdAt->addMinutes(5);
             // if (Carbon::now()->gt($expiryTime)) {
             //     return response()->json(['message' => 'Token has expired'], 401);
             // }
             $reseller = Reseller::where('token', $token)->first();
             if($reseller){
                $request->merge(['reseller' => $reseller->toArray()]);
                return $next($request);
             }else{
                return response()->json(['message' => 'Token Mismatch'], 401);
             }
         } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
         }
     }
 
     private function opensslDecrypt($finalToken)
     {
         $key = '785766habcdefgh678jgjhg89';
         $decodedToken = json_decode(base64_decode($finalToken));
         $ivSize = openssl_cipher_iv_length('aes-256-cbc');
         $iv = substr($decodedToken, 0, $ivSize);
         $encryptedData = substr($decodedToken, $ivSize);
         $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
         $jsonData = json_decode($decryptedData, true);
 
         return $jsonData;
     }
    // private function getEncryptionKey()
    // {
    //     return env('ENCRYPTION_KEY');
    // }
}