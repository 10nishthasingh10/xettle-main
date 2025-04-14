<?php

namespace Validations;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
/**
 * Upi class
 */
class IBLUPIValidation
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
            'required'  => 'required',
            'amount'    => 'required|numeric|between:1,100000',
            'mobile' 	=> ['required','integer','min:10'],
            'aadhaar' 	=> ['required','integer','digits:12'],
            'pan_no' 	=> ['required','string','size:10'],
            'email' 	=> ['required','email'],
            'string' 	=> ['required','string'],
            'url' 	=> ['required','url'],
            'pincode' 	=> ['required','digits:6','integer'],
            'dob'       => ['nullable','date_format:Y-m-d'],
            'vpa'       => ['required','alpha']
        ];
		return $validation[$key];
	}

    /**
     * add Collect function
     *
     * @return void
     */
    public function collect()
    {
        $validations = [
            //'amount'            => $this->validation('amount'),
            //'vpa' 			    => $this->validation('required'),
            'amount'    => 'required|numeric|min:1|max:100000',
            'pgMerchantId'       => 'required',
            'transactionNote'   => 'required',
            'virtualAddress'    =>'required',
            'invoice_name'      =>'required'
        ];
        $validator = \Validator::make($this->data->all(), $validations, []);
        return $validator;
	}

    /**
     * add Sub Merchant function
     *
     * @return void
     */
    public function merchant()
    {
        $validations = [
            //'pgMerchantId'      => $this->validation('required'),
            'mebussname'        => $this->validation('required'),
            'legalStrName'      => $this->validation('required'),
            'merVirtualAdd'     => $this->validation('vpa'),
            'strCntMobile'      => $this->validation('mobile'),
            'panNo'             => $this->validation('pan_no'),
            'settleType'        => $this->validation('required'),
            'meEmailID'         => $this->validation('email')     
            // 'merchantBusinessType'      => $this->validation('required'),
            // 'perDayTxnCount'            => $this->validation('required'),
            // 'perDayTxnLmt'              => $this->validation('required'),
            // 'perDayTxnAmt'              => $this->validation('required'),
            // 'mobile'                    => $this->validation('required'),
            // 'address'                   => $this->validation('required'),
            // 'state'                     => $this->validation('required'),
            // 'city'                      => $this->validation('required'),
            // 'pinCode'                   => $this->validation('required'),
            // 'subMerchantId'             => $this->validation('required'),
            // 'merchantTxnRefId'          => $this->validation('required'),
            // 'mcc'                       => $this->validation('required'),


        ];
        if($this->data->gstConsentFlag=='Y')
        {
            $validations = ['gstin'=>$this->validation('required')];
        }
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}

    /**
     * add Merchant function
     *
     * @return void
     */
    public function addMerchant()
    {
        $validations = [
            //'pgMerchantId'      => $this->validation('required'),
            'mebussname'    => $this->validation('required'),
            'legalStrName'                => $this->validation('required'),
            'merVirtualAdd'                     => $this->validation('required'),
            'mobile'              => $this->validation('mobile'),
            'panNo'                      => $this->validation('pan_no'),
            'settleType'                => $this->validation('required'),
            'city'                => $this->validation('required'),
            'state'                => $this->validation('required'),
            'pincode'                => $this->validation('required'),
            'strCntPhone'                => $this->validation('required'),
            'strCntPhone'                => $this->validation('required'),
            'contactEmail'                => $this->validation('required'),
            'loginEmail'                => $this->validation('required'),
            'requestUrl1'                => $this->validation('required'),
            'requestUrl2'                => $this->validation('required'),
            'requestUrl3'                => $this->validation('required'),
            'requestUrl4'                => $this->validation('required'),
            'accNo'                => $this->validation('required'),
            'ifscCode'                => $this->validation('required'),
            'issueBnk'                => $this->validation('required'),
            'stAdd1'                => $this->validation('required'),
            'stAdd2'                => $this->validation('required'),
            'stAdd3'                => $this->validation('required'),
            'billingFax'                => $this->validation('required')



        ];
        if($this->data->gstConsentFlag=='Y')
        {
            $validations = ['gstin'=>$this->validation('required')];
        }
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
    }

        /**
     * add Sub Merchant function
     *
     * @return void
     */
    public function webMerchant()
    {
        $validations = [
            'merchantBusinessName'      => $this->validation('required'),
            'merchantVirtualAddress'    => $this->validation('required'),
             //'requestUrl'                => $this->validation('url'),
             'panNo'                     => $this->validation('pan_no'),
             'contactEmail'              => $this->validation('email'),
             'merchantBusinessType'      => $this->validation('required'),
             'perDayTxnCount'            => $this->validation('amount'),
             'perDayTxnLmt'              => $this->validation('amount'),
             'perDayTxnAmt'              => $this->validation('amount'),
             'mobile'                    => $this->validation('mobile'),
             'address'                   => $this->validation('required'),
             'state'                     => $this->validation('required'),
             'city'                      => $this->validation('required'),
             'pinCode'                   => $this->validation('pincode'),
        ];
        $validator = \Validator::make($this->data->all(), $validations,[]);
        return $validator;
	}
}