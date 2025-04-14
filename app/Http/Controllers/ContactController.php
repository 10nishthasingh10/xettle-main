<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Cities;
use App\Models\State;
use App\Models\User;
use App\Models\Contact;
use App\Models\UserService;
use Validations\ContactValidation as Validations;
use Yajra\DataTables\Html\Builder;
use Auth;
use CommonHelper;

class ContactController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,Builder $builder)
    {
        $data['page_title'] =  "Contacts Listing";
        $data['site_title'] =  "Contacts";
        $data['view']       = USER.'/'.PAYOUT."contact.list";
        $data['cities']       = Cities::where('is_active','1')->get();
        $data['states']       = State::where('is_active','1')->get();
        $data['id'] =  0;
        return view(USER.'/'.PAYOUT.'.contact.list')->with($data);
    }

    public function store(Request $request)
    {

        $validation=new Validations($request);
		$validator=$validation->addWebContactByAdmin();
        $validator->after(function ($validator) use ($request) {

            $userId = isset($request->user_details) ? $request->user_details : Auth::user()->id;
            $User = User::where('id', $userId)->where('is_active', '1')->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'Your account is has been blocked');
            }
            $Contact = Contact::where('reference', $request->reference_id)->count();
            if ($Contact) {
                $validator->errors()->add('reference_id', 'The reference id has already been taken.');
            }

            if ($request->accountType == 'vpa') {
                if (empty($request->vpaAddress)) {
                    $validator->errors()->add('vpaAddress', 'The vpa address field is required.');
                } else {
                    if (Contact::where('account_type', 'vpa')->where('vpa_address', $request->vpaAddress)
                    ->where('user_id', $userId)->count()) {
                        $validator->errors()->add('vpaAddress', 'The vpa address has already been taken.');
                    }
                }
            } else if ($request->accountType == 'card') {
                if (empty($request->cardNumber)) {
                    $validator->errors()->add('cardNumber', 'The card number field is required.');
                } else {
                    if (Contact::where('account_type', 'card')->where('card_number', $request->cardNumber)->where('user_id', $userId)->count()) {
                        $validator->errors()->add('cardNumber', 'The card number has already been taken.');
                    }
                }
            } else {
              
                if (empty($request->accountNumber) || empty($request->ifsc)) {
                    $validator->errors()->add('accountNumber', 'The account number and ifsc field is required.');
                } else {
                    if (Contact::where('account_type', 'bank_account')->where(['account_number' => $request->accountNumber, 'account_ifsc' => $request->ifsc])->where('user_id', $userId)->count()) {
                        $contact = Contact::where('account_type', 'bank_account')->select('contact_id')
                            ->where(['account_number' => $request->accountNumber, 'account_ifsc' => $request->ifsc])
                            ->where('user_id', $userId)->first();

                        $validator->errors()->add('accountNumber', "The account number has already been takens. ".$contact['contact_id']);
                        $validator->errors()->add('ifsc', 'The ifsc has already been taken.');
                    }
                }
            }
        });
		if($validator->fails()){
			$this->message = $validator->errors();
	    }else{
            $userId = isset($request->user_details) ? $request->user_details : Auth::user()->id;
            $beneid = CommonHelper::getRandomString('cont');
            $referenceId = isset($request->reference_id) ? $request->reference_id : CommonHelper::getRandomString('ref');
            $Contact= new Contact;
            $Contact->contact_id = $beneid;
            $Contact->first_name = $request->first_name;
            $Contact->last_name = $request->last_name;
            $Contact->email = $request->email;
            $Contact->phone = $request->mobile;
            $Contact->type = $request->type;
            $Contact->reference = $referenceId;
            $Contact->notes = $request->note;
            $Contact->user_id = $userId;
            $Contact->is_active = '1';
            $Contact->account_type = $request->accountType;

            if($request->accountType == 'vpa'){
                $Contact->vpa_address = $request->vpaAddress;
            }else if($request->accountType == 'card' ){
                $Contact->card_number = $request->card_no;
            }else{
                $Contact->account_number = $request->accountNumber;
                $Contact->account_ifsc = $request->ifsc;
            }
                if ($Contact->save()) {
                    $this->message = "Record Added Successfull";
                    $this->data = ['contact' => $Contact];
                    $this->status = true;
                    $this->redirect = true;
                    $this->modalId = "kt_modal_create_contact";
                    $this->modalClose = true;
                    $this->modal    = true;
                } else {
                    $this->message_object = true;
                    $this->message  = array('message' => "Record not added.");
                    $this->data = [];
                    $this->status = false;
                }

                $this->alert    = true;
                return $this->populateresponse();
        }

        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => false,
                'data'      => $this->message
            ])
        );
    }

    public function getContactByUserId($userId)
    {

        $data['status'] = false;
        $contacts = Contact::where('user_id', $userId)
            ->whereIn('account_type', ['bank_account', 'vpa'])
            ->orderBy('id', 'desc')->get();
        $html = "<option value=''>Select a Contact...</option>";
        foreach ($contacts as $contact) {
            if ($contact->account_type == 'bank_account')
                $html .= "<option value='$contact->contact_id'>" .$contact->account_number.' | '.$contact->account_ifsc.' | '.$contact->first_name. "</option>";
            else
                $html .= "<option value='$contact->contact_id'>" .$contact->vpa_address.' | '.$contact->first_name. "</option>";
            $data['status'] = true;
        }
        $data['option'] = $html;
        $user  = User::where('id', $userId)->select('transaction_amount')->first();
        $balane = "";
        if (isset($user)) {
            $balane .= "Main Balance : $user->transaction_amount.";
        }

        $UserService = UserService::where('user_id', $userId)
            ->where('service_id', PAYOUT_SERVICE_ID)->select('transaction_amount')->first();
        if (isset($UserService)) {
            $balane .= " Payout Balance : $UserService->transaction_amount";
        } else {
            $balane .= "No user service found";
        }

        $data['accountBalance'] = "( $balane )";
        return $data;
    }
}
