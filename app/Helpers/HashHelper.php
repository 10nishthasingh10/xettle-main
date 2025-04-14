<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class HashHelper
{

    const CREATE_CONTACT = 'create_contact';
    const UPDATE_CONTACT = 'update_contact';
    const CREATE_ORDER = 'create_order';
    const CANCLE_ORDER_BY_ID = 'cancel_order_by_id';

    const ACC_BALANCE = 'account_balance';
    const FETCH_ALL_CONTACTS = 'fetch_all_contacts';
    const FETCH_CONTACT_BY_ID = 'fetch_contact_by_id';

    const FETCH_ALL_ORDERS = 'fetch_all_orders';
    const FETCH_ORDER_BY_ID = 'order_by_id';



    /**
     * Algos:
     * 
     * POST/PATCH:
     * SHA256(base64 encoded payload + "/v1/service/payout/contacts" + client-key + #### + salt)
     * 
     * GET:
     * SHA256(“/v1/service/payout/accountInfo” + client-key + #### + salt)
     */



    /**
     * Generate hash method
     */
    public function generate($reqType, $clientKey, $salt, $params = null)
    {

        switch ($reqType) {

            case self::CREATE_CONTACT:
                $arr = [];
                if (isset($params['accountType']) && $params['accountType'] == 'bank_account') {
                    $arr = [
                        'firstName' => isset($params['firstName']) ? $params['firstName'] : '',
                        'lastName' => isset($params['lastName']) ? $params['lastName'] : '',
                        'email' => isset($params['email']) ? $params['email'] : '',
                        'mobile' => isset($params['mobile']) ? $params['mobile'] : '',
                        'type' => isset($params['type']) ? $params['type'] : '',
                        'accountType' => isset($params['accountType']) ? $params['accountType'] : '',
                        'accountNumber' => isset($params['accountNumber']) ? $params['accountNumber'] : '',
                        'ifsc' => isset($params['ifsc']) ? $params['ifsc'] : '',
                        'referenceId' => isset($params['referenceId']) ? $params['referenceId'] : '',
                    ];
                } elseif (isset($params['accountType']) && $params['accountType'] == 'vpa'){
                    $arr = [
                        'firstName' => isset($params['firstName']) ? $params['firstName'] : '',
                        'lastName' => isset($params['lastName']) ? $params['lastName'] : '',
                        'email' => isset($params['email']) ? $params['email'] : '',
                        'mobile' => isset($params['mobile']) ? $params['mobile'] : '',
                        'type' => isset($params['type']) ? $params['type'] : '',
                        'accountType' => isset($params['accountType']) ? $params['accountType'] : '',
                        'vpa' => isset($params['vpa']) ? $params['vpa'] : '',
                        'referenceId' => isset($params['referenceId']) ? $params['referenceId'] : '',
                    ];
                }
                $str = base64_encode(json_encode($arr));

                $str .= "/v1/service/payout/contacts";

                break;

            case self::UPDATE_CONTACT:
                    $arr = [
                        'firstName' => isset($params['firstName']) ? $params['firstName'] : '',
                        'lastName' => isset($params['lastName']) ? $params['lastName'] : '',
                        'email' => isset($params['email']) ? $params['email'] : '',
                        'mobile' => isset($params['mobile']) ? $params['mobile'] : '',
                    ];
                    $str = base64_encode(json_encode($arr));
                    $str .= "/v1/service/payout/contacts/".$params['contactId'];
    
                break;
    
            case self::CREATE_ORDER:

                $arr = [
                    'contactId' => isset($params['contactId']) ? $params['contactId'] : '',
                    'amount' => isset($params['amount']) ? $params['amount'] : '',
                    'purpose' => isset($params['purpose']) ? $params['purpose'] : '',
                    'mode' => isset($params['mode']) ? $params['mode'] : '',
                    'narration' => isset($params['narration']) ? $params['narration'] : '',
                    'remark' => isset($params['remark']) ? $params['remark'] : '',
                    'clientRefId' => isset($params['clientRefId']) ? $params['clientRefId'] : ''
                ];

                $str = base64_encode(json_encode($arr));
                
                $str .= "/v1/service/payout/orders";

                break;



            case self::CANCLE_ORDER_BY_ID:

                $arr = [
                    'orderRefId' => isset($params['orderRefId']) ? $params['orderRefId'] : '',
                    'remark' => isset($params['remark']) ? $params['remark'] : '',
                ];

                $str = base64_encode(json_encode($arr));

                $str .= "/v1/service/payout/cancelOrder";

                break;



            case self::ACC_BALANCE:

                $str = "/v1/service/payout/accountInfo";

                break;



            case self::FETCH_ALL_CONTACTS:

                $str = "/v1/service/payout/contacts";

                break;



            case self::FETCH_CONTACT_BY_ID:

                $str = "/v1/service/payout/contacts/{$params}";

                break;



            case self::FETCH_ALL_ORDERS:

                $str = "/v1/service/payout/orders";

                break;



            case self::FETCH_ORDER_BY_ID:

                $str = "/v1/service/payout/orders/{$params}";

                break;



            default:
                $str = "";
                break;
        }


        $str .= "{$clientKey}####{$salt}";
        //dd($salt);
        $str = hash('sha256', $str);

        return $str;
    }





    /**
     * use sha512 alog to encrypt data
     */
    // public function sha512(string $str)
    // {
    //     return hash('sha512', $str);
    // }



    /**
     * match hash
     */
    // public function match($hashOne, $hashTwo)
    // {
    //     return hash_equals($hashOne, $hashTwo);
    // }



    /**
     * Return current class object
     */
    public static function init()
    {
        return new self();
    }
}