<?php
namespace Validations;

use Illuminate\Support\Facades\Validator;

class PanCardValidation {

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
            'name'		=> ['required','min:2', 'regex:/^([A-Za-z .()]+)$/'],
            'lastname'	=> ['min:2', 'regex:/^([A-Za-z .()]+)$/'],
            'amount'    => 'required|numeric|between:1,100000',
            'mobile' 	=> ['required','integer','digits_between:10,11'],
            'email'     => ['required', 'email'],
            'string'     => ['required', 'string'],
            'dob'       => ['nullable', 'date_format:Y-m-d'],
            'numeric'     => ['required', 'numeric'],
            'pincode'     => ['required', 'digits:6', 'integer'],
            'gender'     => ['required','in:M,F'],
            'title' => ['required','in:1,2,3'],
            'type'    => ['required','in:nsdl,uti'],
            'applnMode' => ['required','in:K,E'],
            'isPhyPan' => ['required','in:Y,N'],
            'pan' 	=> ['required','string','size:10'],
            'panNotReq' 	=> ['string','size:10'],
            'aadhaar' 	=> ['required','integer','digits:12'],
            'client_ref_id'  =>['required','string', 'min:5' ,'max:50'],
            'merchantCodeNotUniqe' => ['nullable', 'regex:/^[|\a-zA-Z0-9._\-,\s\/]*$/', 'min:4' ,'max:12'],
            'lat' => ['required','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'long' => ['required','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'stringNotRequired' => ['regex:/^[-a-zA-Z0-9\/.\s]*$/',  'min:2',  'max:244'],
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
        $validation = new PanCardValidation($request);
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
	public function addAgent()
	{
        $validations = [
            'firstName'      => $this->validation('name'),
            'middleName'      => $this->validation('lastname'),
            'lastName'      => $this->validation('lastname'),
            'mobile'      => $this->validation('mobile'),
            'dob' => $this->validation('dob'),
            'email' => $this->validation('email'),
            'pinCode' => $this->validation('pincode'),
            'address' => $this->validation('stringNotRequired'),
            'gender' => $this->validation('gender'),
            'stateId' => $this->validation('numeric'),
            'districtId' => $this->validation('numeric'),
            'pan' => $this->validation('pan'),
            'aadhaar' => $this->validation('aadhaar'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
    }


	/**
	 * Method remitterRegistration
	 *
	 * @return void
	 */
	public function txnInitFromNSDl()
	{
        $validations = [
            'firstName'      => $this->validation('name'),
            'nameOnPan'  => $this->validation('name'),
            'middleName'      => $this->validation('lastname'),
            'lastName'      => $this->validation('lastname'),
            'mobile'      => $this->validation('mobile'),
            'dob' => $this->validation('dob'),
            'email' => $this->validation('email'),
            'pinCode' => $this->validation('pincode'),
            'applnMode' => $this->validation('applnMode'),
            'isPhyPan' => $this->validation('isPhyPan'),
            'gender' => $this->validation('gender'),
            'title' => $this->validation('title'),
            'pan' => $this->validation('panNotReq'),
            'pasId' => $this->validation('merchantCodeNotUniqe'),
            'orderRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
    }

	/**
	 * Method initTxn
	 *
	 * @return void
	 */
	public function initTxn()
	{
        $validations = [
            'mobile'      => $this->validation('mobile'),
           // 'email' => $this->validation('email'),
            'psaId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
    }

    /**
	 * Method txnStatus
	 *
	 * @return void
	 */
	public function txnStatus()
	{
        $validations = [
            'orderRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}
}

