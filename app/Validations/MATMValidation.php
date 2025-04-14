<?php
namespace Validations;

use App\Models\UserService;
use Illuminate\Support\Facades\Validator;

class MATMValidation {

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
            'email'     => ['required', 'email'],
            'string'     => ['required', 'string'],
            'numeric'     => ['required', 'numeric'],
            'client_ref_id'  =>['required','string', 'min:5' ,'max:50'],
            'merchantCodeNotUniqe' => ['nullable', 'regex:/^[|\a-zA-Z0-9._\-,\s\/]*$/', 'min:4' ,'max:12'],
            'macAddress' => ['required','regex:/^[0-9A-Fa-f]{2}(?=([:-]))(?:\1[0-9A-Fa-f]{2}){5}$/'],
            'lat' => ['required','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'long' => ['required','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'stringNotRequired' => ['regex:/^[a-zA-Z.\s]*$/',  'min:2',  'max:244'],
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
        $validation = new MATMValidation($request);
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
	public function cpDetails()
	{
        $validations = [
            'sdkVer'      => $this->validation('string'),
            'merchantPhone'      => $this->validation('mobile'),
            'merchantCode' => $this->validation('merchantCodeNotUniqe'),
            'latitude' => $this->validation('lat'),
            'longitude' => $this->validation('long'),
            'merchantEmail' => $this->validation('email'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}


	/**
	 * Method remitterRegistration
	 *
	 * @return void
	 */
	public function txnInit()
	{
        $validations = [
           // 'serialno'      => $this->validation('string'),
            'macAddress'      => $this->validation('macAddress'),
            'merchantCode' => $this->validation('merchantCodeNotUniqe'),
            'latitude' => $this->validation('lat'),
            'longitude' => $this->validation('long'),
            'imei' => $this->validation('string'),
            'imsi' => $this->validation('string'),
            'clientRefId' => $this->validation('client_ref_id'),
            'udf1' => $this->validation('stringNotRequired'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}


	/**
	 * Method txnUpdate
	 *
	 * @return void
	 */
	public function txnUpdate()
	{
        $validations = [


            //'bankresponsecode' => $this->validation('string'),
            'clientrefid' => $this->validation('client_ref_id'),
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
            'clientRefId' => $this->validation('client_ref_id'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

}

