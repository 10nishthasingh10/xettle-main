<?php

namespace Validations;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

/**
 * Upi class
 */
class UPIValidation
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
            'mobile'     => ['required', 'integer', 'min:10'],
            'aadhaar'     => ['required', 'integer', 'digits:12'],
            'pan_no'     => ['required', 'string', 'size:10'],
            'email'     => ['required', 'email'],
            'string'     => ['required', 'string'],
            'url'     => ['required', 'url'],
            'pincode'     => ['required', 'digits:6', 'integer'],
            'dob'       => ['nullable', 'date_format:Y-m-d'],
        ];
        return $validation[$key];
    }

    /**
     * add Collect function
     *
     * @return object
     */
    public function collect()
    {
        $validations = [
            //'amount'            => $this->validation('amount'),
            //'vpa' 			    => $this->validation('required'),
            'amount'    => 'required|numeric|min:1|max:100000',
            'referenceId' => [
                        'required',
                        Rule::unique('upi_collects', 'customer_ref_id'),
                    ],
            //'vpa'       => 'required'
        ];
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
    }

    /**
     * add Sub Merchant function
     *
     * @return object
     */
    public function merchant()
    {
        $validations = [
            'root' => 'nullable|in:indus,yesbank,manual',
            'merchantBusinessName'   => "required|min:5|max:25|regex:/^([a-zA-Z0-9 ]+)$/",
            'merchantVirtualAddress' => "required|min:2|max:12|regex:/^([a-zA-Z0-9]+)$/",
            'panNo' => "required|size:10|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/",
            'merchantGenre' => "nullable|in:online,offline",
            // 'requestUrl'                => $this->validation('required'),
            // 'contactEmail'              => $this->validation('required'),
            // 'gstn'                      => $this->validation('required'),
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
        $validator = Validator::make($this->data->all(), $validations, [
            'regex' => 'The :attribute has invalid characters'
        ]);
        return $validator;
    }

    /**
     * add Sub Merchant function
     *
     * @return object
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
        $validator = Validator::make($this->data->all(), $validations, []);
        return $validator;
    }
}
