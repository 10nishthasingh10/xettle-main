<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Validations\ContactValidation as Validations;
use App\Models\Contact;
use App\Models\User;
//use App\Models\Integration;
use CommonHelper;
use App\Helpers\HashHelper;
use App\Helpers\ResponseHelper;
use Storage;

class ContactController extends Controller
{
    public function index(Request $request)
    {


        $header = $request->header();

        $userSaltKey = CommonHelper::getUserSalt($request["auth_data"]['user_id']);
        //making hash
        $hash = HashHelper::init()->generate(HashHelper::FETCH_ALL_CONTACTS, $header['php-auth-user'][0], $userSaltKey);

        //Storage::put('contactSignature'.time().'.txt', print_r($hash, true));
        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';
        $aaray = array('user_id'=>$request["auth_data"]['user_id'],'xettle'=>$hash,'client'=>$signature);
        //Storage::put('contactSignature'.$request["auth_data"]['user_id'].'_'.time().'.txt', print_r($aaray, true));

        //match signature
        if (!hash_equals($hash, $signature)) {
            return ResponseHelper::failed('Your signature is invalid.');
        }


        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];

        $contact = Contact::select('contact_id as contactId', 'first_name as firstName', 'last_name as lastName', 'email', 'phone as mobile', 'type', 'account_type as accountType', 'account_number as accountNumber', 'account_ifsc as accountIFSC','vpa_address as vpaAddress', 'card_number as cardNumber', 'reference', 'is_active as isActive')->where('user_id', $userId);
            $contact->orderBy('id', 'DESC');
            if(isset($request->offset) && isset($request->limit)) {
                $contact->offset($request->offset);
                $contact->limit($request->limit);
            }
        $contact = $contact->get();
        foreach ($contact as $key => $value) {
            if ($value->accountType == 'card') {
                unset($value->accountNumber, $value->accountIFSC, $value->vpaAddress);
            }
            if ($value->accountType == 'vpa') {
                unset($value->accountNumber, $value->accountIFSC, $value->cardNumber);
            }
            if ($value->accountType == 'bank_account') {
                unset($value->vpaAddress, $value->cardNumber);
            }
        }
        if (!$contact->isEmpty()) {
            return ResponseHelper::success('Record fetched successfully.', $contact);
        } else {
            return ResponseHelper::failed('No contact found. Its never to late to start adding one.', []);
        }
    }

    public function fetchById(Request $request, $contactId)
    {

        $header = $request->header();
        $userSaltKey = CommonHelper::getUserSalt($request["auth_data"]['user_id']);

        //making hash
        $hash = HashHelper::init()->generate(HashHelper::FETCH_CONTACT_BY_ID, $header['php-auth-user'][0], $userSaltKey, $contactId);
        //Storage::put('contactSignaturefetchById'.time().'.txt', print_r($hash, true));

        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';
        $aaray = array('user_id'=>$request["auth_data"]['user_id'],'xettle'=>$hash,'client'=>$signature);
        //Storage::put('contactSignaturebyId'.$request["auth_data"]['user_id'].'_'.time().'.txt', print_r($aaray, true));

        //match signature
        if (!hash_equals($hash, $signature)) {
            return ResponseHelper::failed('Your signature is invalid.');
        }


        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];
        $contact = Contact::select('account_type')
            ->where('contact_id', $contactId)
            ->where('user_id', $userId)
            ->first();
        $contact = Contact::select(self::columnSelectResponse($contact->account_type))->where('contact_id', $contactId)->where('user_id', $userId)->first();

        if ($contact) {
            return ResponseHelper::success('Record fetched successfully.', $contact);
        } else {
            return ResponseHelper::failed('No contact found using this contact Id ' . $contactId, []);
        }
    }

    public function store(Request $request)
    {
        $header = $request->header();
        $userSaltKey = CommonHelper::getUserSalt($request["auth_data"]['user_id']);

        //making hash
        $hash = HashHelper::init()->generate(HashHelper::CREATE_CONTACT, $header['php-auth-user'][0], $userSaltKey, $request->all());
        
        //Storage::put('contactSignatureCreate'.time().'.txt', print_r($hash, true));
        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';
        $aaray = array('user_id'=>$request["auth_data"]['user_id'],'xettle'=>$hash,'client'=>$signature);
        //Storage::put('contactSignatureStore'.$request["auth_data"]['user_id'].'_'.time().'.txt', print_r($aaray, true));

        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];

        $validation = new Validations($request);

        $validator = $validation->addContact();


        $validator->after(function ($validator) use ($request, $userId, $hash, $signature) {
            
            //match signature
            if (!hash_equals($hash, $signature)) {
                $validator->errors()->add('signature', 'Your signature is invalid.');
            } else {
                $User = User::where('id', $userId)->where('is_active', '1')->first();
                if (empty($User)) {
                    $validator->errors()->add('userId', 'Your account is has been blocked');
                }
                $Contact = Contact::where('reference', $request->referenceId)->count();
                if ($Contact) {
                    $validator->errors()->add('referenceId', 'The reference id has already been taken.');
                }
               
                if ($request->accountType == 'vpa') {
                    if (empty($request->vpa)) {
                        $validator->errors()->add('vpa', 'The vpa field is required.');
                    } else {
                        if (Contact::where('account_type', 'vpa')->where('vpa_address', $request->vpa)->where('user_id', $userId)->count()) {
                            $validator->errors()->add('vpa', 'The vpa has already been taken.');
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
                            $validator->errors()->add('accountNumber', 'The account number has already been taken.');
                            $validator->errors()->add('ifsc', 'The ifsc has already been taken.');
                        }
                    }
                }
           }
        });

        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            if (isset($message['accountNumber']) && isset($message['ifsc'])) {
                if (isset($request->accountNumber) && isset($request->ifsc)) {
                    $contact = Contact::select('contact_id')->where('account_type', 'bank_account')->where(['account_number' => $request->accountNumber, 'account_ifsc' => $request->ifsc])->where('user_id', $userId)->first();
                    if (isset($contact)) {
                        return ResponseHelper::missing($message, array('contactId' => $contact->contact_id));
                    }
                }
            } else if (isset ($message['vpa'])) {
                if (isset($request->vpa)) {
                    $contact = Contact::where('account_type', 'vpa')->where('vpa_address', $request->vpa)->where('user_id', $userId)->first();
                    if (isset($contact)) {
                        return ResponseHelper::missing($message, array('contactId' => $contact->contact_id));
                    }
                }
            }
            return ResponseHelper::missing($message);
        } else {
            $contact = new Contact;
            $contact->contact_id = CommonHelper::getRandomString('cont');
            $contact->user_id = $userId;
            $contact->first_name = self::removeSpecialChar($request->firstName);
            $contact->last_name = self::removeSpecialChar($request->lastName);
            $contact->email = $request->email;
            $contact->phone = $request->mobile;
            $contact->type = $request->type;
            $contact->reference = $request->referenceId;
            $contact->account_type = $request->accountType;

            if ($request->accountType == 'vpa') {
                $contact->vpa_address = $request->vpa;
            } elseif ($request->accountType == 'card') {
                $contact->card_number = $request->cardNumber;
            } else {
                $contact->account_number = $request->accountNumber;
                $contact->account_ifsc = CommonHelper::case($request->ifsc, 'u');
            }

            $contact->save();
            $contactInfo = Contact::select(self::columnSelectResponse($request->accountType))->where('contact_id', $contact->contact_id)->first();

            return ResponseHelper::success('Contact created successfully', $contactInfo, '200');
        }
        return ResponseHelper::failed('Record not created successfully');
    }

    public function update(Request $request, $contact_id)
    { 
        $header = $request->header();
        $userSaltKey = CommonHelper::getUserSalt($request["auth_data"]['user_id']);

        //making hash
        $request->request->add(['contactId' => $contact_id]);
        $hash = HashHelper::init()->generate(HashHelper::UPDATE_CONTACT, $header['php-auth-user'][0], $userSaltKey, $request->all());
        
        //Storage::put('contactSignatureCreate'.time().'.txt', print_r($hash, true));
        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';
        $aaray = array('user_id'=>$request["auth_data"]['user_id'],'xettle'=>$hash,'client'=>$signature);
        //Storage::put('contactSignatureStore'.$request["auth_data"]['user_id'].'_'.time().'.txt', print_r($aaray, true));

        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];

        $validation=new Validations($request);
		$validator = $validation->updateContact();
        $validator->after(function ($validator) use ($request, $userId, $hash, $signature, $contact_id) {

            //match signature
           
            if (!hash_equals($hash, $signature)) {
                $validator->errors()->add('signature', 'Your signature is invalid.');
            } else {
                $User = User::where('id', $userId)->where('is_active', '1')->first();
                if (empty($User)) {
                    $validator->errors()->add('userId', 'Your account is has been blocked');
                }
                $Contact = Contact::where('contact_id', $contact_id)->first();
                if (empty($Contact)) {
                    $validator->errors()->add('contactId', 'No contact found using this contact Id');
                   
                }
            }
        });

        if ($validator->fails()) {

            $message = json_decode(json_encode($validator->errors()), true);
            if(isset($message['contactId']['0'])) {
                return ResponseHelper::failed('No contact found using this contact Id ' . $contact_id, []);
            }else {
                return ResponseHelper::missing($message);
            }
        } else{
            $Contact= Contact::where('contact_id', $contact_id)->first();
            $Contact->first_name = $request->firstName;
            $Contact->last_name = $request->lastName;
            $Contact->email = $request->email;
            $Contact->phone = $request->mobile;
            $Contact->save();

            $contactInfo = Contact::select(self::columnSelectResponse($Contact->account_type))->where('contact_id', $contact_id)->first();

            return ResponseHelper::success('Contact updated successfully .', $contactInfo, '200');
        }
        return ResponseHelper::failed('Record not updated successfully .');
    }

    public static function columnSelectResponse($accountType)
    {
        $selectingColumn = array('contact_id as contactId', 'first_name as firstName',
                        'last_name as lastName', 'email', 'phone as mobile', 'type', 'account_type as accountType',
                         'reference', 'is_active as isActive');
        if($accountType == 'vpa')
        {
            array_push($selectingColumn, 'vpa_address as vpa');
            return $selectingColumn;
        } elseif ($accountType == 'card') {
            array_push($selectingColumn, 'card_number as cardNumber');
            return $selectingColumn;
        } else {
            array_push($selectingColumn, 'account_number as accountNumber', 'account_ifsc as accountIFSC');
            return $selectingColumn;
        }
    }

    public static function removeSpecialChar($str)
    {
        $res = preg_replace('/[0-9\@\.\;\(\)]+/', '', $str);
        $res = ltrim($res);
        $res = rtrim($res);
        return $res;
    }
}
