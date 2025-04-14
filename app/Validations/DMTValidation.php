<?php
namespace Validations;

use App\Models\UserService;
use Illuminate\Support\Facades\Validator;
class DMTValidation {

	protected $data;
	public function __construct($data){
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
            'required'  => 'required',
            'amount'    => 'required|numeric|between:1,100000',
            'mobile'     => ['required', 'integer', 'min:10'],
            'aadhaar'     => ['required', 'integer', 'digits:12'],
            'pan_no'     => ['required', 'string', 'size:10', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
            'email'     => ['required', 'email'],
            'string'     => ['required', 'string'],
            'otpReferenceID'     => ['required', 'string', 'min:5' ],
            'merchantCode' => ['nullable', 'regex:/^[|\a-zA-Z0-9._\-,\s\/]*$/', 'unique:dmt_outlets,merchant_code', 'min:4' ,'max:12'],
            'merchantCodeNotUniqe' => ['nullable', 'regex:/^[|\a-zA-Z0-9._\-,\s\/]*$/', 'min:4' ,'max:12'],
            'url'     => ['required', 'string'],
            'pincode'     => ['required', 'digits:6', 'integer'],
            'outletId'     => ['required', 'integer', 'min:5', 'min:8'],
            'dob'       => ['nullable', 'date_format:Y-m-d'],
            'pan'       => 	['required','string'],
            'ifsc' 	=> ['string','size:11','regex:/^[A-Za-z]{4}[0][A-Z0-9]{6}$/'],
            'firstName' => ['required','regex:/^[a-zA-Z.\s]*$/',  'min:2',  'max:25'],
            'lastName' => ['nullable', 'regex:/^[a-zA-Z. \s]*$/', 'max:25'],
            'client_ref_id'  =>['required','string', 'unique:dmt_fund_transfers,client_ref_id','min:5' ,'max:50'],
            'ifsc' 	=> ['required','string','size:11','regex:/^[A-Za-z]{4}[0][A-Z0-9]{6}$/'],
            'account_number' 	=> ['required','integer','min:8'],
            'consent' 	=> ['required','in:Y,N'],
            'accountNumber' 	=> ['required','numeric','digits_between:8,20'],
            'lat' => ['required','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'long' => ['required','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'paymentMode' 	=> ['required','in:IMPS,NEFT,RTGS,UPI'],
        ];
        return $validation[$key];
    }

    /**
     * Method init
     *
     * @param $request $request [explicite description]
     * @param $method $method [explicite description]
     *
     * @return void
     */
    public static function init($request, $method)
    {
        $resp['status'] = false;
        $resp['message'] = "";
        $validation = new DMTValidation($request);
        $validator = $validation->$method();
        $validator->after(function ($validator) use ($request) {

        });

        if ($validator->fails()) {
            $resp['message'] = json_decode(json_encode($validator->errors()), true);
            return $resp;
        } else {
            $resp['status'] = true;
            return $resp;
        }
    }


	/**
	 * Method remitterRegistration
	 *
	 * @return void
	 */
	public function remitterRegistration()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
            'mobile'      => $this->validation('mobile'),
            'firstName' => $this->validation('firstName'),
            'lastName' => $this->validation('lastName'),
            'pinCode' => $this->validation('pincode'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}


	/**
	 * Method outletInit
	 *
	 * @return void
	 */
	public function outletInit()
	{
        $validations = [
            'mobile'      => $this->validation('mobile'),
            'email' => $this->validation('email'),
            'pan' => $this->validation('pan_no'),
            'latitude' => $this->validation('lat'),
            'longitude' => $this->validation('long'),
            'aadhaar' => $this->validation('aadhaar'),
            'merchantCode' => $this->validation('merchantCodeNotUniqe'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}



	/**
	 * Method outletOTPVerify
	 *
	 * @return void
	 */
	public function outletOTPVerify()
	{
        $validations = [
           // 'outletId'      => $this->validation('outletId'),
            'mobile'      => $this->validation('mobile'),
            'otpReference'      => $this->validation('otpReferenceID'),
            'otp' => $this->validation('pincode'),
            'hash' => $this->validation('otpReferenceID'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    	/**
	 * Method beneficiaryRegistration
	 *
	 * @return void
	 */
	public function beneficiaryRegistration()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
            'name'      => $this->validation('firstName'),
            'remitterMobile'      => $this->validation('mobile'),
            'ifsc'      => $this->validation('ifsc'),
            'accountNumber'      => $this->validation('accountNumber'),
            'bankId'      => $this->validation('otpReferenceID'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}


    	/**
	 * Method beneficiaryRegistration
	 *
	 * @return void
	 */
	public function beneficiaryRemove()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
            'remitterMobile'      => $this->validation('mobile'),
            'beneficiaryId'      => $this->validation('otpReferenceID'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}
 

    /**
	 * Method beneficiaryOTPValidate
	 *
	 * @return void
	 */
	public function beneficiaryOTPValidate()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
            'remitterMobile'      => $this->validation('mobile'),
            'otpReference'      => $this->validation('otpReferenceID'),
            'otp' => $this->validation('pincode'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}


    /**
	 * Method remitterUpdate
	 *
	 * @return void
	 */
	public function remitterUpdate()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
            'mobile'      => $this->validation('mobile'),
            'firstName' => $this->validation('lastName'),
            'lastName' => $this->validation('lastName'),
            'pinCode' => $this->validation('pincode'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    	/**
	 * Method remitterOTPValidate
	 *
	 * @return void
	 */
	public function remitterOTPValidate()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
            'mobile'      => $this->validation('mobile'),
            'otpReference'      => $this->validation('otpReferenceID'),
            'otp' => $this->validation('pincode'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    /**
	 * Method remitterDetails
	 *
	 * @return void
	 */
	public function remitterDetails()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

 /**
	 * Method remitterUpdate
	 *
	 * @return void
	 */
	public function fundTransfer()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
            'remitterMobile'      => $this->validation('mobile'),
            'beneficiaryId' => $this->validation('otpReferenceID'),
            'amount' => $this->validation('amount'),
            'latitude' => $this->validation('lat'),
            'longitude' => $this->validation('long'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

     /**
	 * Method remitterEKYC
	 *
	 * @return void
	 */
	public function remitterEKYC()
	{
        $validations = [
            'outletId'      => $this->validation('outletId'),
            'mobile'      => $this->validation('mobile'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}
}

