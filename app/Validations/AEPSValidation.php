<?php

namespace Validations;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
/**
 * Upi class
 */
class AEPSValidation
{
    /**
     * data variable
     *
     * @var [array]
     */
	protected $data;

    /**
     * Construct function
     *
     * @param [type] $data
     */
	public function __construct($data)
    {
		$this->data = $data;
	}

    /**
     * validation function
     *
     * @param [type] $key
     * @return void
     */
    private function validation($key)
    {
		$validation = [
            'required'  => ['required'],
            'amount'    => ['required','numeric','gt:49', 'max:10000'],
            'mobile' 	=> ['required','integer','min:10'],
            'aadhaar' 	=> ['required','integer','digits:12'],
            'pan_no' 	=> ['required','string','size:10'],
            'email' 	=> ['required','email'],
            'string' 	=> ['required','string'],
            'pincode' 	=> ['required','digits:6','integer'],
            'dob'       => ['nullable','date_format:Y-m-d'],
            'ip'        => ['required','ip'],
            'clientrefno' => ['required','string', 'min:4', 'max:25'],
            'req_string' => ['required','string', 'max:50'],
            'req_string_address' => ['required', 'regex:/^[|\a-zA-Z0-9._\-,\s\/]*$/'],
            'firstName' => ['required','regex:/^[a-zA-Z.\s]*$/',  'min:2',  'max:25'],
            'lastName' => ['nullable', 'regex:/^[a-zA-Z. \s]*$/', 'max:25'],
            'middleName' => ['nullable', 'regex:/^[a-zA-Z0-9,-._\s\/]*$/', 'max:25'],
            'shopName' => ['required', 'regex:/^[a-zA-Z0-9,-._\s\/&]*$/', 'max:100',  'min:4'],
            'shopAddress' => ['required', 'regex:/^[|\a-zA-Z0-9._\-,\s\/&]*$/', 'max:100', 'min:4'],
            'serviceType' => ['required','in:AEPS,aeps,AP,ap'],
        ];
		return $validation[$key];
	}

    /**
     * add  Merchant function
     *
     * @return void
     */
    public function merchantOnBoard()
    {
        $validations = [
            'firstName'     => $this->validation('firstName'),
            'middleName'     => $this->validation('middleName'),
            'lastName'     => $this->validation('lastName'),
            'mobile'        => $this->validation('required'),
            'email'         => $this->validation('email'),
            'address'       => $this->validation('req_string_address'),
            'pinCode'       => $this->validation('pincode'),
            'dob'           => $this->validation('dob'),
            'aadhaarNo'     => $this->validation('aadhaar'),
            'panNo'         => $this->validation('pan_no'),
            'shopName'      => $this->validation('shopName'),
            'shopAddress'   => $this->validation('shopAddress'),
            'shopPin'       => $this->validation('pincode'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * sendOTP function
     *
     * @return void
     */
    public function sendOTP()
    {
        $validations = [
            'merchantCode'     => $this->validation('required'),
            'mobile'            => $this->validation('required'),
            'aadhaarNo'     => $this->validation('aadhaar'),
            'panNo'            => $this->validation('pan_no'),
            'latitude'          => $this->validation('required'),
            'longitude'         => $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * validateOTP function
     *
     * @return void
     */
    public function validateOTP()
    {
        $validations = [
            'merchantCode'        => $this->validation('required'),
            'otp'               => $this->validation('required'),
            'hash'          => $this->validation('required'),
            'token'     => $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * resendOTP function
     *
     * @return void
     */
    public function resendOTP()
    {
        $validations = [
            'merchantCode'     => $this->validation('required'),
            'primaryId'      => $this->validation('required'),
            'requestId'     => $this->validation('required'),
            'token'     => $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * ekycBioMetric function
     *
     * @return void
     */
    public function ekycBioMetric()
    {
        $validations = [
            'merchantCode' => $this->validation('required'),
            'aadhaarNo' => $this->validation('aadhaar'),
            'rdRequest'     => $this->validation('string'),
            'requestId'  => $this->validation('required'),
            'token'  => $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}
	
	
	/**
     * twoFactAuthCheck function
     *
     * @return void
     */
    public function twoFactAuthCheck()
    {
        $validations = [
            'merchantCode' => $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}
	
	

    /**
     * twoFactAuth function
     *
     * @return void
     */
    public function twoFactAuth()
    {
        $validations = [
            'merchantCode' => $this->validation('required'),
            'latitude'     => $this->validation('required'),
            'longitude'    => $this->validation('required'),
            'fingdata'     => $this->validation('required'),
            'aadhaarNo'    => $this->validation('required')
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}


    /**
     * getBalance function
     *
     * @return void
     */
    public function getBalance()
    {
        $validations = [
            'merchantCode' => $this->validation('required'),
            'aadhaarNo'    => $this->validation('aadhaar'),
            'fingdata'     => $this->validation('string'),
            'clientRefNo'   => $this->validation('string'),
            'mobile'        => $this->validation('mobile'),
            'ip'            => $this->validation('ip'),
            'bankiin'       => $this->validation('required'),
            'latitude'      => $this->validation('required'),
            'longitude'     => $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * Withdrawal function
     *
     * @return void
     */
    public function withdrawal()
    {
        $validations = [
            'merchantCode'  => $this->validation('required'),
            'amount'        => $this->validation('amount'),
            'aadhaarNo'     => $this->validation('aadhaar'),
            'fingdata'      => $this->validation('string'),
            'clientRefNo'   => $this->validation('string'),
            'mobile'        => $this->validation('mobile'),
            'ip'            => $this->validation('ip'),
            'bankiin'       => $this->validation('required'),
            'latitude'      => $this->validation('required'),
            'longitude'     => $this->validation('required')
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * Withdrawal function
     *
     * @return void
     */
    public function aadhaarPay()
    {
        $validations = [
            'merchantCode' => $this->validation('required'),
            'amount'        => $this->validation('amount'),
            'aadhaarNo' => $this->validation('aadhaar'),
            'rdRequest'     => $this->validation('string'),
            'mobile'        => $this->validation('mobile'),
            'ip'            => $this->validation('ip'),
            'bankiin'       => $this->validation('required'),
            'latitude'     => $this->validation('required'),
            'longitude'     => $this->validation('required'),
            'routeType'     => $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * Statement function
     *
     * @return void
     */
    public function statement()
    {
        $validations = [
            'merchantCode' => $this->validation('required'),
            'aadhaarNo' => $this->validation('aadhaar'),
            'fingdata'     => $this->validation('string'),
            'clientRefNo'   => $this->validation('string'),
            'mobile'        => $this->validation('mobile'),
            'ip'            => $this->validation('ip'),
            'bankiin'       => $this->validation('required'),
            'latitude'     => $this->validation('required'),
            'longitude'     => $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}


    /**
     * Ekyc function
     *
     * @return void
     */
    public function aepsEkyc()
    {
        $validations = [
            'merchantCode' => 'required',
            'aadhaarFront' => 'required|mimes:jpeg,png,jpg|max:1024',
            'aadhaarBack' => 'required|mimes:jpeg,png,jpg|max:1024',
            'panFront' => 'required|mimes:jpeg,png,jpg|max:1024',
            'shopPhoto' => 'required|mimes:jpeg,png,jpg|max:1024',
            'photo' => 'required|mimes:jpeg,png,jpg|max:1024',
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

      /**
     * Ekyc Status function
     *
     * @return void
     */
    public function ekycStatus()
    {
        $validations = [
            'merchantCode' => 'required|string',
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}


    /**
     * Ekyc Status function
     *
     * @return void
     */
    public function transactionStatus()
    {
        $validations = [
            'clientRefNo' => 'required|string',
            'merchantCode' => 'required|string'
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

}
