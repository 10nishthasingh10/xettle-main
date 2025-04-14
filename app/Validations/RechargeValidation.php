<?php
namespace Validations;

use App\Models\UserService;
use Illuminate\Support\Facades\Validator;

class RechargeValidation {

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
            'mobile'     => ['required', 'digits_between:10,18'],
            'aadhaar'     => ['required', 'integer', 'digits:12'],
            'pan_no'     => ['required', 'string', 'size:10'],
            'email'     => ['required', 'email'],
            'string'     => ['required', 'string'],
            'url'     => ['required', 'string'],
            'pincode'     => ['required', 'digits:6', 'integer'],
            'dob'       => ['nullable', 'date_format:Y-m-d'],
            'pan'       => 	['required','string'],
            'client_ref_id'  =>['required','string', 'unique:recharges,order_ref_id', 'max:25'],
            'ifsc' 	=> ['required','string','size:11','regex:/^[A-Za-z]{4}[0][A-Z0-9]{6}$/'],
            'account_number' 	=> ['required','integer','min:8'],
            'lat' => ['required','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'long' => ['required','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'amount' 	=> ['required','numeric','between:0,100000'],
            'idType'     => ['required','in:custId,phone'],
            'operatorId' 	=> ['required','numeric'],
            'numberWithRequired' 	=> ['required','numeric'],
            'circleId' 	=> ['numeric'],
            'number' 	=> ['string'],
            'type' 	=> ['required','in:mobilePlans,dthPlans,mobileOffers,dthOffers,dthPlanWithChannels'],
        ];
        return $validation;
    }


    public function dthRecharge(){
        $validations = [
            'uid'   => $this->validation('uid'),
            'pwd'   => $this->validation('pwd'),
            'cn'    => $this->validation('cn'),
            'op'    => $this->validation('op'),
            'cir'   => $this->validation('cir'),
            'amt'   => $this->validation('amt'),
            'reqid' => 'required|string',
    	];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function postpaid(){
        $validations = [
            'uid'   => $this->validation('uid'),
            'pwd'   => $this->validation('pwd'),
            'cn'    => $this->validation('cn'),
            'op'    => $this->validation('op'),
            'cir'   => $this->validation('cir'),
            'amt'   => $this->validation('amt'),
            'reqid' => $this->validation('reqid'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function rechargeval(){
        $validations = [
            'uid'   => $this->validation('uid'),
            'pwd'   => $this->validation('pwd'),
            'cn'    => $this->validation('cn'),
            'op'    => $this->validation('op'),
            'cir'   => $this->validation('cir'),
            'amt'   => $this->validation('amt'),
            'reqid' => 'required|string',
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function billview(){
        $validations = [
            'cir'      => $this->validation('cir'),
            'uid'      => $this->validation('uid'),
            'pswd'     => $this->validation('pswd'),
            'cn'       => $this->validation('cn'),
            'op'       => $this->validation('op'),
            'adParams' => 'required|json',
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function electicity(){
        $validations = [
            'uid'      => 'required|email',
            'pwd'      => $this->validation('pwd'),
            'cn'       => $this->validation('cn'),
            'amt'      => $this->validation('amt'),
            'reqid'    => $this->validation('reqid'),
            'op'       => $this->validation('op'),
            'ad1'      => 'required',
            'ad2'      => 'required',
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function lic(){
        $validations = [
            'uid' => 'required|email',
            'pwd' => $this->validation('pwd'),
            'cn' => $this->validation('cn'),
            'op' => $this->validation('op'),
            'amt' => $this->validation('amt'),
            'reqid' => 'required|string',
            'ad1' => 'required|email',
            'ad2' => 'required|string',
            'ad3' => 'required|string',
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function creditcard(){
        $validations = [
            'uid' => $this->validation('uid'),
            'pwd' => $this->validation('pwd'),
            'op' => $this->validation('op'),
            'cir' => $this->validation('cir'),
            'amt' => $this->validation('amt'),
            'reqid' => $this->validation('reqid'),
            'cn' => 'required|string',
            
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}
    public static function init($request, $method)
    {
        $resp['status'] = false;
        $resp['message'] = "";
        $validation = new RechargeValidation($request);
        $validator = $validation->$method();
        $validator->after(function ($validator) use ($request) {
            $service = UserService::where(['user_id' => $request['auth_data']['user_id'], 'is_active' => '1'])->first();
            if (empty($service)) {
                    $validator->errors()->add('user_id', 'User service is not active');
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

	public function rechargePlan()
	{
        $validations = [
            'circle'      => $this->validation('string'),
            'operator' => $this->validation('string'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function rOfferPlan()
	{
        $validations = [
            'number'      => $this->validation('string'),
            'operatorId' => $this->validation('operatorId'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}


    public function dthPlan()
	{
        $validations = [
            'operator' => $this->validation('string'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function dthROffer()
	{
        $validations = [
            'custId' => $this->validation('string'),
            'operator' => $this->validation('string'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function customerInfo()
	{
        $validations = [
            'number' => $this->validation('string'),
           // 'type' => $this->validation('idType'),
            'operatorId' => $this->validation('string'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    public function getOperatorAndCircle()
	{
        $validations = [
            'number' => $this->validation('numberWithRequired')
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    
    public function planAndOffers()
	{
        $validations = [
            'operatorId' => $this->validation('operatorId'),
            'type' => $this->validation('type'),
            'number' => $this->validation('number'),
            'circleId' => $this->validation('number'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    
    
    public function recharge()
	{
        $validations = [
           // 'merchantCode' => $this->validation('string'),
            'phone' => $this->validation('mobile'),
            'amount' => $this->validation('amount'),
            'operatorId' => $this->validation('string'),
           // 'lat' => $this->validation('lat'),
           // 'long' => $this->validation('long'),
            'clientRefId' => $this->validation('string'),
           // 'routeType' => $this->validation('string'),
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

}


?>