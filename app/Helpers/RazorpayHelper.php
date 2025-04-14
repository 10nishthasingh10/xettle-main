<?php
namespace App\Helpers;
 
use Illuminate\Http\Request;
use Cashfree;
use App\Models\Contact;
use App\Models\Integration;
use CommonHelper;
use App\Models\TransactionHistory;

class RazorpayHelper {
    private $key;
    private $secret;
    private $baseUrl;
    private $rAccountNo;
    protected $header;

    public function __construct()
    {

        $this->baseUrl = env('RAZPAY_BASE_URL');
        $this->key = base64_decode(env('RAZPAY_PAYOUT_CLIENT_ID'));
        $this->secret = base64_decode(env('RAZPAY_PAYOUT_CLIENT_SECRET'));
        $this->rAccountNo = base64_decode(env('RAZPAY_PAYOUT_ACCOUNT_NO'));
        $this->header = array(
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($this->key . ":" . $this->secret),
            "X-Payout-Idempotency:",
        );

    }

    public function razorpayAddCustomer($name, $email, $contact)
    {

        $request = ["name" => $name, "email" => $email, "contact" => $contact, "type" => "employee", "reference_id" => "djjdjd", 
        "notes" => ["notes_key_1" => "Tea, Earl Grey, Hot", "notes_key_2" => "Tea, Earl Grey… decaf."]];
       
        
        $result = CommonHelper::curl($this->baseUrl.'contacts', "POST",
         json_encode($request) ,$this->header, 'yes');
        $response = json_decode($result['response']);
        
        return $response;

    }
    public function razorpayUpdateCustomer($name, $email, $contact,$id)
    {

        $request = ["name" => $name, "email" => $email,
         "contact" => $contact, "type" => "employee", "reference_id" => "djjdjd", 
         "notes" => ["notes_key_1" => "Tea, Earl Grey, Hot", "notes_key_2" => "Tea, Earl Grey… decaf."]];

        $result = CommonHelper::curl($this->baseUrl.'contacts/'.$id, "PATCH", json_encode($request) , $this->header, 'yes');
        $response = json_decode($result['response']);
        
        return $response;

    }


    public function razorpayAddFundAccount($type, $cust_id, $acc_name = "", $acc_no = "", $ifsc = "")
    {

        if ($type == 'bank_account'){
            $request = ["contact_id" => $cust_id, "account_type" => $type,

            "bank_account" => ["name" => $acc_name, "ifsc" => $ifsc, "account_number" => $acc_no]];
        }
        else if ($type == 'vpa'){
            $request = ["contact_id" => $cust_id, "account_type" => $type,

            "vpa" => ["address" => $acc_name

            ]];
        }else{
            $request = ["contact_id" => $cust_id, "account_type" => $type,

            "card" => ["name" => $acc_name, "number" => $acc_no

            ]];
        }

      
        $result = CommonHelper::curl($this->baseUrl.'fund_accounts', "POST",
         json_encode($request) ,$this->header, 'yes');
        $response = json_decode($result['response']);
        
        return $response;

    }

    public function razorpayAddPayout($account_number, $fund_account_id, $amount)
    {

        $request = ["account_number" => $this->rAccountNo, "fund_account_id" => $fund_account_id, "amount" => $amount * 100, "currency" => "INR", "mode" => "NEFT", "purpose" => "refund", "queue_if_low_balance" => true, "reference_id" => "Mahagramt ID 12345", "narration" => "Acme Corp Fund Transfer",

        "notes" => ["notes_key_1" => "Tea, Earl Grey, Hot", "notes_key_2" => "Tea, Earl Grey… decaf."]];

        
        $result = CommonHelper::curl($this->baseUrl.'payouts', 
        "POST", json_encode($request) ,$this->header, 'yes');
        $response = json_decode($result['response']);
        
        return $response;

    }

    public function razorpayGetPayout($fund_account_id)
    {
     
        $request = [];
        $url=$this->baseUrl.'payouts?account_number='.$this->rAccountNo.'&fund_account_id='.$fund_account_id;
        $result = CommonHelper::curl($url, "GET", json_encode($request) , $this->header, 'yes');
        $response = json_decode($result['response']);
        
        return $response;

    }

    public function razorpayGetPayoutById($id)
    {
     
        $request = [];
        $url=$this->baseUrl.'/v1/payouts/'.$id;
        $result = CommonHelper::curl($url, "GET", json_encode($request) , $this->header, 'yes', $id,
        'razorpay',
        'statusCheck',
        $id);
        $response['data'] = json_decode($result['response']);
        
        return $response;

    }

    public function razorpayCancelPayout($pout_id)
    {

        $request = [];
        $url=$this->baseUrl.'payouts/'.$pout_id.'/cancel';
        $result = CommonHelper::curl($url, "GET", json_encode($request) , $this->header, 'yes');
        $response = json_decode($result['response']);
        
        return $response;

    }


    public function razorpayCompositePayout($orderData)
    {

        $mode = CommonHelper::case($orderData->mode, 'u');
        $purpose = CommonHelper::case($orderData->purpose, 'l');
        $request = [
                "account_number" => $this->rAccountNo,
                "amount" => $orderData->amount * 100,
                //'fund_account_id' => $orderData->order_ref_id,
                "currency" => 'INR',
                "mode" => $mode,
                "purpose" => $purpose,
                "queue_if_low_balance" => true,
                "reference_id" => $orderData->order_ref_id,
                "narration" => $orderData->narration,
                "notes" => [
                    "notes_key_1" => $orderData->notes,
                    "notes_key_2" => $orderData->notes,
                ]
        ];

        if ($orderData->account_type == 'vpa') {
            $request =   array_merge($request, array("fund_account" => [
                "account_type" => $orderData->account_type,
                "vpa" => [
                    "address" => $orderData->vpa_address
                ],
                "contact" => [
                    "name" => $orderData->first_name . ' ' . $orderData->last_name,
                    "email" => 'example@example.com',
                    "contact" => '9999999999',
                    "type" =>  $orderData->type,
                    "reference_id" => $orderData->reference,
                    "notes" => [
                        "notes_key_1" => $orderData->notes,
                        "notes_key_2" => $orderData->notes,
                    ]
                ]
            ]));
        } else if ($orderData->account_type == 'bank_account') {
            $request = array_merge($request, array("fund_account" => [
                "account_type" => $orderData->account_type,
                "bank_account" => [
                    "name" => $orderData->first_name . ' ' . $orderData->last_name,
                    "ifsc" => $orderData->account_ifsc,
                    "account_number" => $orderData->account_number
                ],
                "contact" => [
                    "name" => $orderData->first_name . ' ' . $orderData->last_name,
                    "email" => 'example@example.com',
                    "contact" => '9999999999',
                    "type" =>  $orderData->type,
                    "reference_id" => $orderData->reference,
                    "notes" => [
                        "notes_key_1" => $orderData->notes,
                        "notes_key_2" => $orderData->notes,
                    ]
                ]
            ]));
        }
        $url = $this->baseUrl.'/v1/payouts';
        $result = CommonHelper::curl($url, "POST", json_encode($request) , $this->header, 'yes' , $orderData->user_id,
        'razorpay',
        'razorpayPayoutComposite',
        $orderData->order_ref_id);
        $response['data'] = json_decode($result['response']);

        return $response;

    }

    public function razorpayNewCompositePayout($account_numberf,$amount,$currency,$mode,$purpose,$account_type,
    $name,$ifsc,$account_number,$email,$contact,$type,$reference_idd,$notes_key_1,$notes_key_2,$queue_if_low_balance,
    $reference_id,$narration,$address1,$city,$state,$pincode)
    {

        $request = [
            "account_number"=>$account_numberf,
            "amount"=>$amount*100,
            "currency"=>$currency,
            "mode"=>$mode,
            "purpose"=>$purpose,
            "fund_account"=>[
                "account_type"=>$account_type,
                "bank_account"=>[
                    "name"=>$name,
                    "ifsc"=>$ifsc,
                    "account_number"=>$account_number
                ],
                "contact"=>[
                    "name"=>$name,
                    "email"=>$email,
                    "contact"=>$contact,
                    "type"=>$type,
                    "reference_id"=>$reference_idd,
                    "notes"=>[
                        "notes_key_1"=>$notes_key_1,
                        "notes_key_2"=>$notes_key_2
                    ]
                ]
                    ],
                    "queue_if_low_balance"=>true,
                    "reference_id"=>$reference_id,
                    "narration"=>$narration,
                    "notes"=>[
                        "notes_key_1"=>$notes_key_1,
                        "notes_key_2"=>$notes_key_2
                    ]
        ];
      
        $url=$this->baseUrl.'payouts';
        $result = CommonHelper::curl($url, "POST", json_encode($request) , $this->header, 'yes');
        $response['razorpay']= $razorpaydata= json_decode($result['response']);
        
        $Cashfree= new Cashfree;
        $beneid='bene_'.rand(10000,99999);
        
        $response['cashfree']=  $cashfreedata=$Cashfree->cashfreeAddBeneficiary($this->tokens, $beneid,$name, 
        $email, $contact, $account_number, $ifsc,$address1,
        $city, $state, $pincode,$name,'ff'); 
        
        $Contact= new Contact;
        $Contact->name=$name;
        $Contact->email=$email;
        $Contact->phone=$contact;
        $Contact->type=$type;
        $Contact->address1=$address1;
        $Contact->city=$city;
        $Contact->state=$state;
        $Contact->pincode=$pincode;
        $Contact->reference=$reference_idd;
        $Contact->note1=$notes_key_1;
        $Contact->note2=$notes_key_2;
        foreach($razorpaydata->fund_account->contact as $val){
           
          $razor_contact_id=$val;
        }
        $Contact->razor_contact_id=$razor_contact_id;
        $Contact->cashfree_bene_id=$beneid;
        $Contact->is_active='0';
        $Contact->account_type=$account_type;
        $Contact->number_or_vpa=$account_number;
        $Contact->ifsc=$ifsc;
        $Contact->razor_fund_acc_id=$razorpaydata->fund_account_id;
        $Contact->razor_payout_id=$razorpaydata->id;

        $Contact->save();
        
        return $response;

    }
    
  
}