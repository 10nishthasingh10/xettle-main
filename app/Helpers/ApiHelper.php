<?php
namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Integration;
use CommonHelper;
use App\Models\TransactionHistory;
class ApiHelper
{
    public static function response($input = '', $params = array())
    {
        $statusResp = array(
            'success' => array(
                'code' => 200,
                'responsecode'=>1,
                'status' => true,
                'message' => 'Success!',
            ),
            'noresult' => array(
                'code' => 200,
                'responsecode'=>2,
                'status' => false,
                'message' => 'No Record Found!',
            ),
            'exception' => array(
                'code' => 201,
                'responsecode'=>3,
                'status' => false,
                'message' => 'Exception Error!',
            ),
            'validate' => array(
                'code' => 422,
                'responsecode'=>4,
                'status' => false,
                'message' => 'Validation Error!',
            ),
            'internalservererror' => array(
                'code' => 500,
                'responsecode'=>5,
                'status' => false,
                'message' => 'HTTP INTERNAL SERVER ERROR!',
            ),
            'forbidden' => array(
                'code' => 403,
                'responsecode'=>5,
                'status' => false,
                'message' => 'Access Denied!',
            ),
        );

        if (isset($statusResp[$input])) {
            $data = $statusResp[$input];
            $code = isset($params['code']) ? $params['code'] : $statusResp[$input]['code'];
            if (!empty($params)) {
                $data = array_merge($data, $params);
            }
            return response()->json($data, $code);
        } else {
            return response()->json($params);
        }
    }

}
