<?php 
namespace Validations;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;


class OtpValidation{
	protected $data;
	public function __construct($data){
		$this->data = $data;
	}

    public function logout()
    {
		$validate = Validator::make($this->data->all(),[
		'id'              => ['required','numeric'],
		],[

		'id.required'		=> 'Please Enter User Id',
		]);
		return $validate;
    }

	public function otp()
	{
		$validate = Validator::make($this->data->all(),[
			'email'       => ['required','email'],
			'password'       => ['required'],
			// 'g-recaptcha-response' => 'required',
		],
		// [
			// 'g-recaptcha-response.required'    => 'Google Re-captcha field is required',
		// ]
	);
		return $validate;
	}

    public function verifyotp()
    {
		$validate = Validator::make($this->data->all(),[
			'otp'			=> ['required'],
		],[
            'otp.required' 		=> "OTP field is required",
			]);
			return $validate;
    }


}


?>