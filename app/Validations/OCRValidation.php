<?php
namespace Validations;

use App\Models\UserService;
use Illuminate\Support\Facades\Validator;
class OCRValidation {

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
            'pan_no'     => ['required', 'string', 'size:10'],
            'email'     => ['required', 'email'],
            'string'     => ['required', 'string'],
            'url'     => ['required', 'string'],
            'pincode'     => ['required', 'digits:6', 'integer'],
            'dob'       => ['nullable', 'date_format:Y-m-d'],
            'pan'       => 	['required','string'],
            'client_ref_id'  =>['required','string', 'unique:ocrs,client_ref_id', 'max:25'],
            'ifsc' 	=> ['required','string','size:11','regex:/^[A-Za-z]{4}[0][A-Z0-9]{6}$/'],
            'account_number' 	=> ['required','integer','min:8'],
        ];
        return $validation[$key];
    }

    public static function init($request, $method)
    {
        $resp['status'] = false;
        $resp['message'] = "";
        $validation = new OCRValidation($request);
        $validator = $validation->$method();
        $validator->after(function ($validator) use ($request) {
            $service = UserService::where(['user_id' => $request['auth_data']['user_id'], 'is_active' => '1'])->first();
            if (empty($service)) {
                    $validator->errors()->add('pan', 'User service is not active');
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
    
	public function pan()
	{
        $validations = [
            'pan'      => $this->validation('url'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function driving()
	{
        $validations = [
            'driving'      => $this->validation('url'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function passport()
	{
        $validations = [
            'passport'      => $this->validation('url'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function vaccination()
	{
        $validations = [
            'vaccination'      => $this->validation('url'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function task()
	{
        $validations = [
            'taskId'      => $this->validation('string')
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function bankAccount()
	{
        $validations = [
            'accountNumber'      => $this->validation('account_number'),
            'ifsc'      => $this->validation('ifsc'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    
    public function voter()
	{
        $validations = [
            'voter'      => $this->validation('url'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    
	public function aadhaar()
	{
        $validations = [
            'aadhaarFront'      => $this->validation('url'),
            'aadhaarBack'      => $this->validation('url'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}


    public function cheque()
	{
        $validations = [
            'cheque'      => $this->validation('url'),
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}
}

