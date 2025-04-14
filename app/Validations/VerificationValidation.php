<?php

namespace App\Validations;

use App\Helpers\ValidationHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VerificationValidation
{
    public function serviceStatus(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";

        try {
            $service = DB::table('user_services')
                ->where(
                    [
                        'user_id' => $request['auth_data']['user_id'],
                        'is_active' => '1'
                    ]
                )->first();

            if (empty($service)) {
                $resp['status'] = false;
                $resp['message'] = 'Service is not active';
                return $resp;
            } else {
                $resp['status'] = true;
                return $resp;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function bank(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";

        $validator = Validator::make(
            $request->all(),
            [
                'accountNumber' =>  ['required', 'digits_between:8,26'],
                'ifsc' => ['required', 'string', 'size:11', 'regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/']
            ]
        );

        if ($validator->fails()) {
            $resp['message'] = json_decode(json_encode($validator->errors()), true);
            return $resp;
        } else {
            $resp['status'] = true;
            return $resp;
        }
    }


    public function ifsc(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";

        $validator = Validator::make(
            $request->all(),
            [
                'ifsc' => ['required', 'string', 'size:11', 'regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/']
            ]
        );

        if ($validator->fails()) {
            $resp['message'] = json_decode(json_encode($validator->errors()), true);
            return $resp;
        } else {
            $resp['status'] = true;
            return $resp;
        }
    }



    public function vpa(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";

        $validator = Validator::make(
            $request->all(),
            [
                'vpa' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[\w\.\-_]{2,}@[a-zA-Z]{2,}/']
            ]
        );

        if ($validator->fails()) {
            $resp['message'] = json_decode(json_encode($validator->errors()), true);
            return $resp;
        } else {
            $resp['status'] = true;
            return $resp;
        }
    }



    public function aadhaar(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";

        $validator = Validator::make(
            $request->all(),
            [
                'aadhaarNumber' =>  ['required', 'digits:12']
            ]
        );


        $validator->after(function ($validation) use ($request) {
            if (!(new ValidationHelper())->validateAadhaar($request->aadhaarNumber)) {
                $validation->errors()->add('aadhaarNumber', 'Aadhaar number id invalid.');
            }
        });

        if ($validator->fails()) {
            $resp['message'] = json_decode(json_encode($validator->errors()), true);
            return $resp;
        } else {
            $resp['status'] = true;
            return $resp;
        }
    }



    public function aadhaarOtp(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";

        $validator = Validator::make(
            $request->all(),
            [
                'otp' =>  ['required', 'digits_between:6,8'],
                // 'clientRefId' => ['required', 'string', 'max:50'],
                'requestId' => ['required', 'string', 'max:50']
            ]
        );

        if ($validator->fails()) {
            $resp['message'] = json_decode(json_encode($validator->errors()), true);
            return $resp;
        } else {
            $resp['status'] = true;
            return $resp;
        }
    }


    public function pan(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";

        $validator = Validator::make(
            $request->all(),
            [
                'pan' => ['required', 'string', 'size:10', 'regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/'],
                // 'clientRefId' => ['required', 'string', 'unique:validations,client_ref_id', 'max:50'],
            ]
        );

        if ($validator->fails()) {
            $resp['message'] = json_decode(json_encode($validator->errors()), true);
            return $resp;
        } else {
            $resp['status'] = true;
            return $resp;
        }
    }



    public function task(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = "";

        $validator = Validator::make(
            $request->all(),
            [
                'requestId' => ['required', 'string', 'max:50'],
            ]
        );

        if ($validator->fails()) {
            $resp['message'] = json_decode(json_encode($validator->errors()), true);
            return $resp;
        } else {
            $resp['status'] = true;
            return $resp;
        }
    }
}
