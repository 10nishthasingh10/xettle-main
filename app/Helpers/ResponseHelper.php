<?php

namespace App\Helpers;

use Illuminate\Http\Request;

use CommonHelper;

class ResponseHelper {

    protected const SUCCESS_CODE = "0x0200";
    protected const SUCCESS_STATUS = "SUCCESS";

    protected const UNAUTHORIZED_CODE = "0x0201";
    protected const UNAUTHORIZED_STATUS = "UNAUTHORIZED";

    protected const FAILED_CODE = "0x0202";
    protected const FAILED_STATUS = "FAILURE";

    protected const MISSING_PARAMETER_CODE = "0x0203";
    protected const MISSING_PARAMETER_STATUS = "MISSING_PARAMETER";

    protected const CONNECTION_TIMEOUT_CODE = "0x0204";
    protected const CONNECTION_TIMEOUT_STATUS = "CONNECTION_TIMEOUT";

    protected const SOMETHING_WENT_WRONG_CODE = "0x0205";
    protected const SOMETHING_WENT_WRONG_STATUS = "SOMETHING_WENT_WRONG";

    protected const PENDING_CODE = "0x0206";
    protected const PENDING_STATUS = "PENDING";

    protected const REVERSED_CODE = "0x0207";
    protected const REVERSED_STATUS = "REVERSED";

    protected const TWO_FA_CODE = "0x0208";
    protected const TWO_FA_STATUS = "2FA";

    // public static function success($message = 'Success', $data = [], $respCode = '200')
    // {
    //     $res['code'] = self::SUCCESS_CODE;
    //     $res["message"] = $message;
    //     $res["status"] = self::SUCCESS_STATUS;

    //     if($data) {
    //         $res["data"] = $data;
    //     }

    //     return response()->json($res, $respCode);
    // }

     // public static function failed($message = 'Failure', $data = [], $respCode = '200')
    // {
    //     $res['code'] = self::FAILED_CODE;
    //     $res["status"] = self::FAILED_STATUS;
    //     $res["message"] = $message;

    //     if($data) {
    //         $res["data"] = $data;
    //     }

    //     return response()->json($res, $respCode);
    // }

    public static function success($message = 'Success', $data = [], $respCode = '200')
{
    $res = [
        'statuscode' => (int)$respCode,
        'success' => true,
        'data' => $data,
        'message' => $message,
        'errors' => null,
        'exception' => null,
    ];

    return response()->json($res, (int)$respCode);
}
    public static function unauthorized($message = 'Unauthorized', $data = [],  $respCode = '401')
    {
        $res['code'] = self::UNAUTHORIZED_CODE;
        $res["message"] = $message;
        $res["status"] = self::UNAUTHORIZED_STATUS;

        if($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }

    public static function failed($message = 'Failure', $data = [], $respCode = '500', $errors = null, $exception = null)
{
    $res = [
        'statuscode' => (int)$respCode,
        'status' => false,
        'data' => $data,
        'message' => $message,
        'errors' => $errors,
        'exception' => $exception,
    ];

    return response()->json($res, (int)$respCode);
}

public static function pending($message = 'Pending', $data = [], $respCode = '200')
{
    $res = [
            'statuscode' => (int)$respCode,
            'status' => true,
            'data' => $data,
            'message' => $message,
    ];

    return response()->json($res, $respCode);

}

    public static function missing($message = 'Missing Parameters', $data = [], $respCode = '200')
    {
        $res['code'] = self::MISSING_PARAMETER_CODE;
        $res["status"] = self::MISSING_PARAMETER_STATUS;
        $res["message"] = $message;

        if($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }

    public static function timeout($message = 'Connection Timeout', $data = [], $respCode = '200')
    {
        $res['code'] = self::CONNECTION_TIMEOUT_CODE;
        $res["status"] = self::CONNECTION_TIMEOUT_STATUS;
        $res["message"] = $message;

        if($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }

    public static function swwrong($message = 'Something Went Wrong', $data = [], $respCode = '200')
    {
        $res['code'] = self::SOMETHING_WENT_WRONG_CODE;
        $res["status"] = self::SOMETHING_WENT_WRONG_STATUS;
        $res["message"] = $message;

        if($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }

    // public static function pending($message = 'Pending', $data = [], $respCode = '200')
    // {
    //     $res['code'] = self::PENDING_CODE;
    //     $res["status"] = self::PENDING_STATUS;
    //     $res["message"] = $message;

    //     if($data) {
    //         $res["data"] = $data;
    //     }

    //     return response()->json($res, $respCode);

    // }

    public static function twoFactAuth($message = 'Please do 2Factor Auth', $data = [], $respCode = '200')
    {
        $res['code'] = self::TWO_FA_CODE;
        $res["status"] = self::TWO_FA_STATUS;
        $res["message"] = $message;

        if($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }
}
