<?php

namespace Validations;

/**
 * SDKAepsValidation class
 */
class ReconcileValidation
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
            'clientrefno' => ['required','string'],
        ];
		return $validation[$key];
	}

    /**
     * Reconcile Validation
     *
     * @return void
     */
    public function reconcileReport()
    {
        $validations = [
            'from' => 'required',
            'to' => 'required',
            'user_id' => 'required'
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

}
