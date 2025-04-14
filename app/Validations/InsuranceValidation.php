<?php

namespace Validations;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
/**
 *  class
 */
class InsuranceValidation
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
            'mobile' 	=> ['required','string','min:10'],
            'aadhaar' 	=> ['required','integer','digits:12'],
            'panNo' 	=> ['required','string','size:10'],
            'email' 	=> ['required','email'],
            'string' 	=> ['required','string'],
            'name' => ['required','regex:/^[a-zA-Z.\s]*$/',  'min:2',  'max:25'],
            
        ];
		return $validation[$key];
	}

    public function OnBoard()
    {
        $validations = [
            'name'     => $this->validation('name'),
            'email'     => $this->validation('email'),
            'mobile' => $this->validation('mobile'),
            'panNo' => $this->validation('panNo')
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
    }

    public function OTTGenerate()
    {
        $validations = [ 
            'agentId' => $this->validation('required')
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
    }
}