<?php

namespace Validations;

/**
 * SDKAepsValidation class
 */
class SDKAepsValidation
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
            'clientrefno' => ['required','string', 'min:5'],
            'serviceType' => ['required','in:AEPS,aeps,AP,ap'],
        ];
		return $validation[$key];
	}

    // SDK Validation Start
    /*
    * Ekyc Status function
     *
     * @return void
     */
    public function init()
    {
        $validations = [
            'merchantCode' => 'required|string'
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
            'aadhaarNo' => $this->validation('aadhaar'),
            'rdRequest'     => $this->validation('string'),
            'mobile'        => $this->validation('mobile'),
            'ip'            => $this->validation('ip'),
            'bankiin'       => $this->validation('required'),
            'latitude'      => $this->validation('required'),
            'longitude'     => $this->validation('required'),
            'routeType'     => $this->validation('required'),
            'clientRefId' => $this->validation('clientrefno'),
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
            'clientRefId' => $this->validation('clientrefno'),
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
            'clientRefId' => $this->validation('clientrefno'),
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
            'rdRequest'     => $this->validation('string'),
            'mobile'        => $this->validation('mobile'),
            'ip'            => $this->validation('ip'),
            'bankiin'       => $this->validation('required'),
            'latitude'     => $this->validation('required'),
            'longitude'     => $this->validation('required'),
            'routeType'     => $this->validation('required'),
            'clientRefId' => $this->validation('clientrefno'),
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
            'clientRefId' => 'required|string',
            'merchantCode' => 'required|string'
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
            'mobile'        => $this->validation('mobile'),
            'merchantCode' => $this->validation('required'),
            'aadhaarNo' => $this->validation('aadhaar'),
            'rdRequest'     => $this->validation('string'),
            'serviceType'  => $this->validation('required')
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}


}
