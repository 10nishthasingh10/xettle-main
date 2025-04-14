<?php

namespace Validations;
use Illuminate\Support\Facades\Validator;


class UserValidation
{
	protected $data;
	public function __construct($data){
		$this->data = $data;
	}

	private function validation($key){
		$validation = [
            'name' 	=> 'required',
            'required' 	=> 'required',
            'mobile' 	=> 'required|numeric',
            'beneficiary_name' => "required|regex:/^([a-zA-Z0-9 ]+)$/",
            'account_number' 	=> 'required|digits_between:8,20',
            'ifsc' => "required|size:11|regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/",
            'user_id' 	=> 'required',
            'service_id' 	=> 'required|numeric',
            'new_service_id' 	=> 'required|string',
            'avatar' => 'mimes:jpeg,jpg,png|max:1000',
            'profileImage' => 'mimes:jpeg,jpg,png|max:1000|dimensions:min_width=10,min_height=10,max_width=220,max_height=220',
            'business_proof' => 'mimes:jpeg,jpg,png,docs,pdf|max:1000',
            'email' => 'required|email',
            'ip' => 'required|ip',
            're_eneter_account_number' => 'required_with:account_number|same:account_number',
            're_enter_account_number' => 'required_with:account_number|same:account_number',
            'old_password'=>'required',
            'password'=>'required|min:6',
            'confirm_password' => 'required|min:6|same:password',
            'webhook_url'=>'required|url',
            'transfer_amount'=>'required|numeric',
            'payoutbulk' => 'required|mimes:csv,txt|max:10240',
            ];
		return $validation[$key];
	}

    public function updateProfile(){
        $validations = [
            'name' => $this->validation('name'),
            'mobile' => $this->validation('mobile'),
        ];
        $validator = Validator::make($this->data->all(), $validations,['avatar.dimensions' => 'Profile image size Min Width:10 and Max Width:220 , Min Heigh:10 and Max Height:220']);
        return $validator;
	}
    public function apikeyGenerate(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'service_id' 			=> $this->validation('new_service_id'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function addIp(){
        $validations = [
            'user_id' => $this->validation('user_id'),
            'service_id' => $this->validation('new_service_id'),
            'ip' => $this->validation('ip'),
        ];

        $validator = Validator::make($this->data->all(), $validations, [
            'ip.required|ip' => "IP address is required",
            'ip.ip' => "IP address is invalid", 
        ]);
        return $validator;
	}

    public function addSettlement(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'integration_id' 			=> $this->validation('new_service_id'),
            'id' 			=> $this->validation('new_service_id'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}
    
    public function transferAmount(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'transfer_amount' 			=> $this->validation('transfer_amount'),
            'service_id' 			=> $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function transAmtToMainAcc(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'transfer_amount' 			=> $this->validation('transfer_amount'),
            'service_id' 			=> $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function importBatchFile(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'file' 			=> $this->validation('payoutbulk'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function webhookUpdate(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            //'service_id' 			=> $this->validation('service_id'),
            'webhook_url' 			=> $this->validation('webhook_url'),
            'secret' 			=> $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function profileChangePassword(){
        $validations = [
            'user_id' => $this->validation('user_id'),
            'old_password' => $this->validation('old_password'),
            'password' => "required|min:6|different:old_password",
            'confirm_password' => $this->validation('confirm_password'),
        ];
        $validator = Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function businessProfileUpdate(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'name' 			=> $this->validation('name'),
            'contact_number' 			=> $this->validation('mobile'),
            'contact_email' 			=> $this->validation('email'),
            'contact_email' 			=> $this->validation('email'),
            'business_name' => $this->validation('required'),
            'business_type' 			=> $this->validation('required'),
            'business_category' 			=> $this->validation('required'),
            'pan_number' 			=> $this->validation('required'),
            'pan_owner_name' 			=> $this->validation('required'),
            'billing_label' 			=> $this->validation('required'),
            'address' 			=> $this->validation('required'),
            'pincode' 			=> $this->validation('required'),
            'city' 			=> $this->validation('required'),
            'state' 			=> $this->validation('required'),
            'gstin' 			=> $this->validation('required'),
            //'beneficiary_name' 			=> $this->validation('required'),
            //'ifsc' 			=> $this->validation('required'),
            //'account_number' 			=> $this->validation('account_number'),
            //'re_eneter_account_number' 			=> $this->validation('re_eneter_account_number'),
           // 'aadhar_number' 			=> $this->validation('mobile'),
            'business_proof' 			=> $this->validation('business_proof'),
            'pan_id' 			=> $this->validation('business_proof'),
            'web_url' => 'nullable|required_without:app_url|url|active_url',
            'app_url' => 'nullable|required_without:web_url|url|active_url',
        ];
        $validator = Validator::make($this->data->all(), $validations,[
            'url' => 'This format is not valid.',
            'active_url' => 'This is not an active URL.'
        ]);
        return $validator;
	}

    public function signUp(){
        $validations = [
            'first_name' => ['required', 'string', 'max:255', 'regex:/^([A-Za-z ]+)$/'],
            'last_name' => [ 'regex:/^([A-Za-z ]+)$/'],
            'mobile' => [ 'required','integer', 'numeric', 'digits:10'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' =>[ 'required_with:password','same:password','min:8'],
            // 'g-recaptcha-response' => ['required'],
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function updateBankDetails(){
        $validations = [
            'beneficiary_name' => $this->validation('beneficiary_name'),
            'ifsc' => $this->validation('ifsc'),
            'account_number' => $this->validation('account_number'),
            're_enter_account_number' => $this->validation('re_eneter_account_number'),
        ];
        $validator = Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function claimback(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'service_account' => $this->validation('required'),
            'amount' 			=> $this->validation('transfer_amount'),
            'remarks' 			=> $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}


    public function threshold(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'created_by' 			=> $this->validation('user_id'),
            'threshold_amount' 			=> $this->validation('transfer_amount'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    

    public function aepsTransferAmount(){
        $validations = [
            'user_id' 			=> $this->validation('user_id'),
            'transfer_amount' 			=> $this->validation('transfer_amount'),
            'service_id' 			=> $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}
}