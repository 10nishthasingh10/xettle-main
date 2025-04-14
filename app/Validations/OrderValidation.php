<?php

namespace Validations;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
/**
* 
*/
class OrderValidation
{
	protected $data;
	public function __construct($data){
		$this->data = $data;
	}

	private function validation($key){
		$validation = [
            'amount' 	     => ['required','numeric','gt:0'],
            'mode' 	         => ['required'],
            'paymentMode' 	 => ['required','in:imps,neft,rtgs,upi,IMPS,NEFT,RTGS,UPI'],
            'paymentPurpose' => ['required','in:reimbursement,salary_disbursement,bonus,incentive,REFUND,refund,others,REIMBURSEMENT,SALARY,DISBURSEMENT,BONUS,INCENTIVE,OTHERS,SALARY_DISBURSEMENT'],
            'purpose' 	     => ['required'],
            'currency' 	     => ['required'],
            'required' 	     => ['required'],
            'contactId' 	 => ['required', 'string', 'min:19'],
            'contact_id'     => ['required'],
            'integration_id' => ['required'],
            'product_id' 	=> ['required'],
            'string' 	=> ['string'],
			];
		return $validation[$key];
	}

    public function addOrder(){
        $validations = [
            'amount' 	    	=> $this->validation('amount'),
            'purpose' 			=> $this->validation('paymentPurpose'),
            'mode' 		        => $this->validation('paymentMode'),
            //'contactId' 		=> $this->validation('contactId'),
            'clientRefId'       => $this->validation('required'),
            'udf1'              => $this->validation('string'),
            'udf2'              => $this->validation('string')
    	];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    public function webAddOrder(){
        $validations = [
            'contact_id' 	   	=> $this->validation('contactId'),
            'integration_id' 	=> $this->validation('required'),
            'product_id' 		=> $this->validation('required'),
            'purpose' 			=> $this->validation('paymentPurpose'),
            'mode' 		        => $this->validation('paymentMode'),
            'amount' 	    	=> $this->validation('amount'),
            'narration'         => $this->validation('required'),
    	];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * addWebOrderByAdmin
     *
     * @return void
     */
    public function addWebOrderByAdmin(){
        $validations = [
            'contact_id' 	=> $this->validation('contactId'),
            'user_id' 		=> $this->validation('required'),
            'purpose' 		=> $this->validation('paymentPurpose'),
           // 'mode'        => $this->validation('paymentMode'),
            'amount' 	   	=> $this->validation('amount'),
            'narration'     => $this->validation('required'),
    	];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    
    public function updateOrder(){
        $validations = [
            'amount' 		=> $this->validation('amount'),
           // 'currency'    => $this->validation('currency'),
            'purpose' 		=> $this->validation('purpose'),
            'mode' 		    => $this->validation('mode'),

    	];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}
        public function updateOrderStatus(){
            $validations = [
            ];
            $validator = \Validator::make($this->data->all(), $validations,[]);
            return $validator;
        }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function orderCancel(){
        $validations = [
            'remarks' 		=> $this->validation('required'),
            'userId' 		=> $this->validation('required'),
            'orderRefId' 	=> $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
    }

        /**
     * Undocumented function
     *
     * @return void
     */
    public function orderReversed(){
        $validations = [
            'remarks' 		=> $this->validation('required'),
            'userId' 		=> $this->validation('required'),
            'orderRefId' 	=> $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
    }
    
        /**
     * Undocumented function
     *
     * @return void
     */
    public function orderCancelAPI(){
        $validations = [
            'remark' 		=> $this->validation('required'),
            'orderRefId' 	=> $this->validation('required'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
    }

}