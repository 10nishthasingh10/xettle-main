<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ValidationHelper
{
    public function validateAadhaar($aadharNumber)
    {
        /*...multiplication table...*/
        $multiplicationTable = [
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            [1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
            [2, 3, 4, 0, 1, 7, 8, 9, 5, 6],
            [3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
            [4, 0, 1, 2, 3, 9, 5, 6, 7, 8],
            [5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
            [6, 5, 9, 8, 7, 1, 0, 4, 3, 2],
            [7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
            [8, 7, 6, 5, 9, 3, 2, 1, 0, 4],
            [9, 8, 7, 6, 5, 4, 3, 2, 1, 0],
        ];

        /*...permutation table...*/
        $permutationTable = [
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            [1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
            [5, 8, 0, 3, 7, 9, 6, 1, 4, 2],
            [8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
            [9, 4, 5, 3, 1, 2, 6, 8, 7, 0],
            [4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
            [2, 7, 9, 3, 8, 0, 6, 4, 1, 5],
            [7, 0, 4, 6, 9, 1, 3, 2, 5, 8],
        ];

        /*...split aadhar number...*/
        $aadharNumberArr = str_split($aadharNumber);

        /*...check length of aadhar number...*/
        if (count($aadharNumberArr) === 12) {

            /*...reverse aadhar number...*/
            $aadharNumberArrRev = array_reverse($aadharNumberArr);
            $tableIndex         = 0;

            /*...validate...*/
            foreach ($aadharNumberArrRev as $aadharNumberArrKey => $aadharNumberDetail) {
                $tableIndex = $multiplicationTable[$tableIndex][$permutationTable[($aadharNumberArrKey % 8)][$aadharNumberDetail]];
            }
            return ($tableIndex === 0);
        }
        return false;
    }


    public function breakNameString($nameString)
    {
        $nameParts = explode(' ', $nameString);
        $namePartsCount = count($nameParts);

        $response = [
            'firstName' => '',
            'lastName' => '',
            'middleName' => ''
        ];

        switch ($namePartsCount) {
            case 1:
                $response['firstName'] = $nameParts[0];
                break;

            case 2:
                $response['firstName'] = $nameParts[0];
                $response['lastName'] = $nameParts[1];
                break;

            default:
                $response['firstName'] = trim($nameParts[0]);
                $response['lastName'] = trim($nameParts[$namePartsCount - 1]);

                unset($nameParts[0]);
                unset($nameParts[$namePartsCount - 1]);

                $response['middleName'] = trim(implode(' ', $nameParts));
                break;
        }

        return $response;
    }



    public function getPanType($pan)
    {
        $char = strtoupper(substr($pan, 3, 1));

        $type = '';

        switch ($char) {
            case 'P':
                $type = 'Individual';
                break;
            case 'C':
                $type = 'Company';
                break;
            case 'H':
                //Hindu Undivided Family (HUF)
                $type = 'Hindu Undivided Family';
                break;
            case 'A':
                //Association of Persons (AOP)
                $type = 'Association of Persons';
                break;
            case 'B':
                //Body of Individuals (BOI)
                $type = 'Body of Individuals';
                break;
            case 'G':
                //Government Agency
                $type = 'Government Agency';
                break;
            case 'J':
                $type = 'Artificial Juridical Person';
                break;
            case 'L':
                $type = 'Local Authority';
                break;
            case 'F':
                //Firm/ Limited Liability Partnership
                $type = 'Firm / Limited Liability Partnership';
                break;
            case 'T':
                $type = 'Trust';
                break;
        }

        return $type;
    }


    /**
     * Get API root from DB
     */
    public function getApiRoot($service)
    {
        $root = '1';

        switch ($service) {
            case 'aadhaar':
                $globalConfig = DB::table('global_config')
                    ->select('attribute_3')
                    ->where('slug', 'verification_api_root')
                    ->first();

                if (!empty($globalConfig)) {
                    $root = $globalConfig->attribute_3;
                }

                break;

            case 'bank':
                $globalConfig = DB::table('global_config')
                    ->select('attribute_2')
                    ->where('slug', 'verification_api_root')
                    ->first();

                if (!empty($globalConfig)) {
                    $root = $globalConfig->attribute_2;
                }

                break;

            case 'pan':
                $globalConfig = DB::table('global_config')
                    ->select('attribute_1')
                    ->where('slug', 'verification_api_root')
                    ->first();

                if (!empty($globalConfig)) {
                    $root = $globalConfig->attribute_1;
                }

                break;
        }

        return $root;
    }



    /**
     * Generate Client Response
     */
    public function generateResponse($type, $params, $tableData = null)
    {
        $response = [];

        switch ($type) {
            case 'validate_bank_account':

                if ($params->status === 'completed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        "currentStatus" => isset($params->result->status) ? $this->getStatus($params->result->status) : $params->status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $response['response'] = [
                            "accountNumber" => $params->result->bank_account_number,
                            "ifscCode" => $params->result->ifsc_code,
                            "fullName" => $params->result->name_at_bank,
                        ];
                    }
                } else if ($params->status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => @$params->status,
                    ];
                }

                break;

            case 'bank_ob':

                if ($params->verification_status === 'success') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        "currentStatus" => isset($params->verification_status) ? $this->getStatus($params->verification_status) : $params->verification_status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $response['response'] = [
                            "accountNumber" => $params->bene_account_number,
                            "ifscCode" => $params->ifsc_code,
                            "fullName" => $params->recepient_name,
                        ];
                    }
                } else if ($params->verification_status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => isset($params->verification_status) ? $this->getStatus($params->verification_status) : $params->verification_status,
                    ];
                }

                break;

            case 'ind_pan':

                if ($params->status === 'completed') {
                    $response = [
                        'requestId' =>  @$tableData->order_ref_id,
                        "currentStatus" => isset($params->result->source_output->status) ? $this->getStatus($params->result->source_output->status) : $params->status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $arrResp = $this->getFirstAndLastName($params->result->source_output->first_name, $params->result->source_output->last_name);
                        $response['response'] = [
                            "pan" => $params->result->source_output->id_number,
                            "type" => (new ValidationHelper())->getPanType($params->result->source_output->id_number),
                            "firstName" => $arrResp['fname'],
                            "lastName" => $arrResp['lname'],
                            "middleName" => $params->result->source_output->middle_name,
                            "fullName" => $params->result->source_output->name_on_card
                        ];
                    }
                } else if ($params->status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => @$params->status,
                    ];
                }

                break;

            case 'pan_kd':

                // if ($params->status === 'completed') {
                $response = [
                    'requestId' =>  $tableData->order_ref_id,
                    "currentStatus" => !empty($params->full_name) ? 'FOUND' : 'NOT_FOUND',
                ];

                if ($response['currentStatus'] === 'FOUND') {

                    // $helper = new ValidationHelper();
                    $nameParts = $this->breakNameString($params->full_name);
                    $arrResp = $this->getFirstAndLastName($nameParts['firstName'], $nameParts['lastName']);
                    $response['response'] = [
                        "pan" => @$params->pan_number,
                        "type" => $this->getPanType($params->pan_number),
                        "firstName" => $arrResp['fname'],
                        "lastName" => $arrResp['lname'],
                        "middleName" => $nameParts['middleName'],
                        "fullName" => @$params->full_name
                    ];
                }
                // } else if ($params->status === 'failed') {
                //     $response = [
                //         'requestId' => @$tableData->order_ref_id,
                //         'currentStatus' => @$params->status,
                //     ];
                // }

                break;

            case 'ind_vpa':

                if ($params->status === 'completed') {
                    $response = [
                        'requestId' =>  $tableData->order_ref_id,
                        "currentStatus" => isset($params->result->status) ? $this->getStatus($params->result->status) : $params->status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $response['response'] = [
                            "vpa" => $params->result->vpa,
                            "fullName" => $params->result->name_at_bank
                        ];
                    }
                } else if ($params->status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => @$params->status,
                    ];
                }

                break;


            case 'aadhaar_lite':

                if ($params->status === 'completed') {
                    $response = [
                        'requestId' =>  $tableData->order_ref_id,
                        "currentStatus" => isset($params->result->status) ? $this->getStatus($params->result->status) : $params->status,
                    ];

                    if ($response['currentStatus'] === 'FOUND') {
                        $response['response'] = [
                            "gender" => $params->result->source_output->gender,
                            "state" => $params->result->source_output->state
                        ];
                    }
                } else if ($params->status === 'in_progress') {

                    $response = [
                        'requestId' =>  $tableData->order_ref_id,
                        "currentStatus" => strtoupper($params->status),
                    ];
                } else if ($params->status === 'failed') {
                    $response = [
                        'requestId' => @$tableData->order_ref_id,
                        'currentStatus' => @$params->status,
                    ];
                }

                break;
        }

        return $response;
    }


    public function getStatus($status)
    {
        $status = strtolower($status);

        $return = $status;

        switch ($status) {
            case 'id_found':
            case 'success':
                $return = 'FOUND';
                break;

            case 'id_not_found':
            case 'failed':
                $return = 'NOT_FOUND';
                break;
        }

        return $return;
    }


    /**
     * Method getFirstAndLastName
     *
     * @param $firstName  [explicite description]
     * @param $lastName  [explicite description]
     *
     * @return void
     */
    public function getFirstAndLastName($firstName, $lastName)
    {
        $resp['fname'] = "";
        $resp['lname'] = "";
        if (isset($firstName) && !empty($firstName)) {
            $resp['fname'] = $firstName;
            $resp['lname'] = $lastName;
        } else {
            $arr = explode(' ', $lastName);
            if (count($arr) > 0) {
                $resp['fname'] = @$arr[0];
                $resp['lname'] = @$arr[1];
            } else {
                $resp['fname'] = $lastName;
                $resp['lname'] = "";
            }
        }
        return $resp;
    }


    public function getNameMatch(string $firstName, string $secondName)
    {
        $firstName = strtolower(preg_replace('/\s+/', '', trim($firstName)));
        $secondName = strtolower(preg_replace('/\s+/', '', trim($secondName)));

        similar_text($firstName, $secondName, $percent);

        return round($percent, 2);
    }


    public function checkIsPublicDomain($url)
    {

        $publicDomains = [
            'google',
            'youtube',
            'yahoo',
            'bing',
            'msn',
            'outlook',
            'gmail',
            'accweather',
            'bbc',
            'facebook',
            'twitter',
            'linkedin',
            'wikipedia',
            'stackoverflow',
            'w3schools',
            'instagram',
            'telegram',
            'whatsapp',
            'amazon',
            'flipkart',
            'ebay',
            'snapdeal',
            'ajio',
            'shopclues',
            'myntra',
            'wordpress',
            'blogger',
            'samsung',
            'nokia',
            'apple',
            'mi',
            'oppo',
            'vivo',
            'realme'
        ];

        $isPublicDomain = false;

        $pieces = parse_url($url);
        $fullUrl = isset($pieces['host']) ? $pieces['host'] : (isset($pieces['path']) ? $pieces['path'] : '');
        $domain = '';

        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $fullUrl, $regs)) {
            $domain = $regs['domain'];
        }

        $domain = explode('.', $domain);

        if (in_array(strtolower($domain[0]), $publicDomains)) {
            $isPublicDomain = true;
        }

        return $isPublicDomain;
    }
}
