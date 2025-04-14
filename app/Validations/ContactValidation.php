<?php

namespace Validations;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class ContactValidation
{
	protected $data;
	public function __construct($data)
    {
		$this->data = $data;
	}

	private function validation($key)
    {
		$validation = [
			'name'		=> ['required','min:2', 'regex:/^([A-Za-z .()]+)$/'],
            'lastname'	=> ['min:2', 'regex:/^([A-Za-z .()]+)$/'],
            'vpa'       => ['min:3'],
            'vpaAddress'       => ['min:3',  'regex:/[a-zA-Z0-9_]{3,}@[a-zA-Z]{3,}/' ],
			'email' 	=> ['required','email'],
			'mobile' 	=> ['required','integer','digits_between:10,11'],
            'ifsc' 	=> ['string','size:11','regex:/^[A-Za-z]{4}[0][A-Z0-9]{6}$/'],
			'account_number' 	=> ['required','integer','min:8'],
            'reference' 	=> ['string','min:5'],
            'beneId' 	=> ['required'],
			'address1' 	=> ['required'],
            'city' 	=> ['required'],
            'state' 	=> ['required'],
			'pincode' 	=> ['required','min:6'],
            'amount' 	=> ['required'],
            'mode' 	=> ['required'],
            'purpose' 	=> ['required'],
            'currency' 	=> ['required'],
            'required'			=> ['required'],
            'cardNumber' 	=> ['integer','digits_between:10,16'],
            'accountNumber' 	=> ['numeric','digits_between:8,20'],
            'accountType'     => ['required','in:bank_account,vpa,card'],
            'type'     => ['required','in:vendor,customer,employee,self'],
            'paymentMode' 	=> ['required','in:imps,neft,rtgs,upi,IMPS,NEFT,RTGS,UPI'],
		];

		return $validation[$key];
	}

    public function addContact()
    {
        $validations = [
            'firstName' 		    => $this->validation('name'),
            'lastName' 		    => $this->validation('lastname'),
            'email'         => $this->validation('email'),
            'mobile' 		=> $this->validation('mobile'),
            'type' 		    => $this->validation('type'),
            'accountType' 	=> $this->validation('accountType'),
            'accountNumber' => $this->validation('accountNumber'),
            'ifsc' 		    => $this->validation('ifsc'),
            'referenceId'  => $this->validation('reference'),
            'cardNumber'  => $this->validation('cardNumber'),
            'vpa'  => $this->validation('vpa')
        ];

        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function addWebContact()
    {
        $validations = [
            'first_name' 		=> $this->validation('name'),
            'email' 		=> $this->validation('email'),
            'mobile' 			=> $this->validation('mobile'),
            'reference_id' 			=> $this->validation('required'),
            'type' 			=> $this->validation('required'),
            'accountType' 		=> $this->validation('required'),
            'name' 		=> $this->validation('name'),
            'ifsc' 		=> $this->validation('ifsc'),
            'accountNumber' 		=> $this->validation('accountNumber'),
          //  'address1' 			=> $this->validation('address1'),
            'note' 			=> $this->validation('address1'),
          //  'state' 			=> $this->validation('state'),
          //  'city' 			=> $this->validation('city'),
          //  'pincode' 			=> $this->validation('pincode'),
            'vpa'  => $this->validation('vpa')
        ];

        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function addWebContactByAdmin()
    {
        $validations = [
            'first_name' 		=> $this->validation('name'),
            'email' 		=> $this->validation('email'),
            'mobile' 			=> $this->validation('mobile'),
            //'reference_id' 			=> $this->validation('required'),
            'type' 			=> $this->validation('required'),
            'accountType' 		=> $this->validation('required'),
          //  'name' 		=> $this->validation('name'),
            'ifsc' 		=> $this->validation('ifsc'),
            'accountNumber' => $this->validation('accountNumber'),
            'user_details' 			=> $this->validation('required'),
            'note' 			=> $this->validation('address1'),
          //  'state' 			=> $this->validation('state'),
          //  'city' 			=> $this->validation('city'),
          //  'pincode' 			=> $this->validation('pincode'),
             'vpaAddress' 			=> $this->validation('vpaAddress'),
        ];

        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function updateContact()
    {
        $validations = [
            'firstName' 		=> $this->validation('name'),
            'email' 			=> $this->validation('email'),
            'mobile' 			=> $this->validation('mobile'),
        ];

        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function updateContactStatus()
    {
        $validations = [
            'is_active' => $this->validation('name'),
        ];

        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function addOrder()
    {
        $validations = [
            'amount' 		=> $this->validation('amount'),
            'currency' 			=> $this->validation('currency'),
            'purpose' 			=> $this->validation('purpose'),
            'mode' 		=> $this->validation('mode'),
        ];

        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}
}