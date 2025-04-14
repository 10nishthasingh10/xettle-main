<?php
namespace App\Helpers;
use App\Models\BulkPayoutDetail;
use App\Models\Contact;
use App\Models\Order;
use App\Models\GlobalConfig;
use App\Models\User;
use App\Models\UserService;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use CommonHelper;
use Transaction as TransactionHelper;
use Cashfree;
/**
 * Import Bulk Payout class
 */
class ImportHelper implements ToModel, WithHeadingRow
{
    /**
     * Importable.SkipsErrors use traits
     */
    use Importable; use SkipsErrors;

    /**
     * batch_id variable
     *
     * @var [type]
     */

    public $batch_id;

    /**
     * user_id variable
     *
     * @var [type]
     */
    public $user_id;
    public $agent;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    /**
     * Undocumented function
     *
     * @param [type] $batch_id
     * @param [type] $user_id
     */

    public function  __construct($batch_id,$user_id, $agent = array())
    {
        $this->batch_id= $batch_id;
        $this->user_id= $user_id;
        $this->agent= $agent;
    }
    /**
     * Undocumented function
     *
     * @param array $row
     * @return void
     */
    public function model(array $row)
    {
        $errors = "";
        $allError = "";
        $payout_mode_error_check = false;
        $purposeTypeError = false;
        $xettle_payout_reference_id=CommonHelper::getRandomString('REF', false);

        // Checking Column Missing or Extra Set Error On Session Variable
        CommonHelper::batchImportFile($row);

        if(isset($row['contact_first_name'])){
            $name = CommonHelper::stringCheck($row['contact_first_name'],1);
            if($name['error_status']){
                $allError .= $name['error'];
            }
        }else{
            $allError .= $row['contact_first_name'].FIELD_REQUIRED;
        }

        if(isset($row['contact_phone'])){
            $mobile=CommonHelper::integerCheck($row['contact_phone'],9);
            if($mobile['error_status']){
                $allError .=$mobile['error'];
            }
        }else{
            $allError .=$row['contact_phone'].FIELD_REQUIRED;
        }

        // payment mode check. Exp: bank_account,vpa

        if(isset($row['account_type'])){
            if(strtolower($row['account_type']) == 'bank_account'){
                $row['account_number']=ltrim($row['account_number']," ");
                $row['account_number']=rtrim($row['account_number']," ");
                $row['account_number']=ltrim($row['account_number'],",");
                $row['account_number']=rtrim($row['account_number'],",");
                $row['account_ifsc']=ltrim($row['account_ifsc']," ");
                $row['account_ifsc']=rtrim($row['account_ifsc']," ");
                if(isset($row['account_number']) && isset($row['account_ifsc'])){
                    $accountTypeCheck=CommonHelper::accountTypeCheck($row['account_number'],$row['account_ifsc']);
                    if(isset($accountTypeCheck) && $accountTypeCheck['error_status']){
                        $allError .=$accountTypeCheck['error'];
                    }
                }else{
                    $allError .=ACCOUNT_AND_IFSC_REQUIRED;
                }
            }else if(strtolower($row['account_type']) == 'vpa'){

            }else{
                $allError .=$row['account_type'].VALUE_UNKNOWN;
            }
        }else{
            $allError .=$row['account_type'].FIELD_REQUIRED;
        }

        $payout_reference_id_error_check=false;
        if(isset($row['payout_reference_id'])){
            $bulkPayoutDetail = BulkPayoutDetail::where('payout_reference',$row['payout_reference_id'])
            ->whereIn('status',['success','hold','pending'])->where('user_id',$this->user_id)->count();
            if($bulkPayoutDetail) {
                $allError .= $row['payout_reference_id'].' is already present. Please give me another Payout Reference id';
                $payout_reference_id_error_check=true;
            }
        }else{
            $allError .= $row['payout_reference_id'].FIELD_REQUIRED;
            $payout_reference_id_error_check=true;
        }

        // payment mode check. Exp: array('imps','neft','rtgs','upi')
        
        if(isset($row['payout_mode'])){
            $row['payout_mode']=ltrim($row['payout_mode']," ");
            $row['payout_mode']=rtrim($row['payout_mode']," ");
            $payout_mode=CommonHelper::stringCheck($row['payout_mode'],1);
            if($payout_mode['error_status']){
                $allError .=$payout_mode['error'];
                $payout_mode_error_check=true;
            }else{
                $payout_mode_check=CommonHelper::paymentmodeCheck($row['payout_mode']);
                if($payout_mode_check['error_status']){
                    $allError .=$payout_mode_check['error'];
                    $payout_mode_error_check=true;
                }
            }
        }else{
            $allError .=$row['payout_mode'].FIELD_REQUIRED;
            $payout_mode_error_check=true;
        }

        // Purpose  checking Exp: array('reimbursement', 'salary_disbursement', 'bonus','incentive','others')

        if(isset($row['payout_purpose'])){
            $row['payout_purpose']=ltrim($row['payout_purpose']," ");
            $row['payout_purpose']=rtrim($row['payout_purpose']," ");
            $payoutPurpose=CommonHelper::stringCheck($row['payout_purpose'],1);
            if($payoutPurpose['error_status']){
                $allError .=$payoutPurpose['error'];
                $purposeTypeError=true;
            }else{
                $purposeCheck=CommonHelper::purposeCheck($row['payout_purpose']);
                if($purposeCheck['error_status']){
                    $allError .=$purposeCheck['error'];
                    $purposeTypeError=true;
                }
            }
        }else{
            $allError .=$row['payout_purpose'].FIELD_REQUIRED;
            $purposeTypeError=true;
        }

        // Contact Type Check. Exp: array('vendor', 'customer', 'employee', 'self')

        $contact_type_error=false;
        if(isset($row['contact_type'])){
            $row['contact_type']=ltrim($row['contact_type']," ");
            $row['contact_type']=rtrim($row['contact_type']," ");
            $contact_type=CommonHelper::stringCheck($row['contact_type'],1);
            if($contact_type['error_status']){
                $allError .=$contact_type['error'];
                $contact_type_error=true;
            }else{
                $contactTypeCheck=CommonHelper::contactTypeCheck($row['contact_type']);
                if($contactTypeCheck['error_status']){
                    $allError .=$contactTypeCheck['error'];
                    $contact_type_error=true;
                }
            }
        }else{
            $allError .=$row['contact_type'].FIELD_REQUIRED;
            $contact_type_error=true;
        }
        $rowError = false;
        if($allError != ""){
            $rowError = true;
        }

        if(session('importFileError')){

            return BulkPayoutDetail::first();
        }else{

            $contact = '';
            if($row['account_type'] == 'bank_account'){

                $contact = Contact::where([
                    'user_id' => $this->user_id,
                    'account_number' => $row['account_number'],
                    'account_ifsc' => $row['account_ifsc']
                    ])->first();
            }else{
                $contact=Contact::where(['user_id' => $this->user_id,'vpa_address' => $row['account_vpa']])->first();
            }

            $getProductId=CommonHelper::getProductId($row['payout_mode'], 'payout');
            $productId = '';
            $serviceId = '';
            if($getProductId){
                $productId = $getProductId->product_id;
                $serviceId = $getProductId->service_id;
            }

            $row['reference'] = $row['batch_id'] = $this->batch_id;
            // Checking is negative Amount
            $row['payout_amount'] = ltrim($row['payout_amount']," ");
            $row['payout_amount'] = rtrim($row['payout_amount']," ");
           
            $intPositive = TransactionHelper::intPositive($row['payout_amount']);

            // check balance amount
            $getFeesAndTaxes['total_amount'] = 0;
            if($intPositive['status'] == false) {
                $allError .= $intPositive['message'];
            }

            $getProductConfig = TransactionHelper::getProductConfig($row['payout_mode'], $serviceId);
            // any error then not debit your account balance
            if($allError == ''){
                if($getProductConfig['status']){
                    if($getProductConfig['data']['min_order_value'] <= $row['payout_amount'] && $getProductConfig['data']['max_order_value'] >= $row['payout_amount']){
                        // Get Total Amount Fee and Tax Amount
                        $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId,$row['payout_amount'], $this->user_id);
                        $row['fee'] = $getFeesAndTaxes['fee'];
                        $row['tax'] = $getFeesAndTaxes['tax'];
                        $row['margin'] = $getFeesAndTaxes['margin'];
                        $checkAndLock['message'] = 'Fee and tax get sucessfully.';
                        $checkAndLock['status'] = true;
                    }else{
                        $checkAndLock['message'] = $row['payout_amount'].' Provided amount is not in range for '.$row['payout_mode'].' Transaction';
                        $checkAndLock['status'] = false;
                    }
                }else{
                    $checkAndLock['message'] = $getProductConfig['message'];
                    $checkAndLock['status'] = false;
                }
            }else{
                $checkAndLock['status'] = false;
                $checkAndLock['message'] = '';
            }
            // check balance amount
            if($checkAndLock['status'] == false) {
                $allError .= $checkAndLock['message'];
            }
            $row['agent'] = $this->agent; 
            // contact checking
            if(!isset($contact) && $contact_type_error == false){

                $createContact=TransactionHelper::createContact($row, $this->user_id);
                if($createContact['status'] && $checkAndLock['status']){
                    $createOrder = TransactionHelper::createOrder($row, $this->user_id, $createContact['data']->contact_id,
                     $productId, '4', $xettle_payout_reference_id, $serviceId);
                }

            }else{

                if($payout_reference_id_error_check == false && $payout_mode_error_check == false && isset($contact->contact_id) &&  $allError == '' && $purposeTypeError == false){
                    $getServiceAccount = CommonHelper::getServiceAccount($this->user_id, $serviceId);
                    if($getServiceAccount && $checkAndLock['status']){
                        $createOrder = TransactionHelper::createOrder($row, $this->user_id, $contact->contact_id,
                         $productId, '4', $xettle_payout_reference_id, $serviceId);
                    }

                }
            }

            $errors = $allError;
            $status = "hold";
            $failed_from = null;
            if($errors != ""){
                $status = "failed";
                $failed_from = '0';
            }

            return new BulkPayoutDetail([
                'batch_id'        => $this->batch_id,
                'contact_first_name'=> $row['contact_first_name'],
                'contact_last_name' => $row['contact_last_name'],
                'contact_email'   => $row['contact_email'],
                'contact_phone'   => $row['contact_phone'],
                'contact_type'    => $row['contact_type'],
                'account_type'    => $row['account_type'],
                'account_number'  => $row['account_number'],
                'account_ifsc'    => $row['account_ifsc'],
                'account_vpa'     => $row['account_vpa'],
                'payout_mode'     => $row['payout_mode'],
                'payout_amount'   => $row['payout_amount'],
                'bank_reference'  => '',
                'payout_reference'=> $row['payout_reference_id'],
                'payout_purpose'  => $row['payout_purpose'],
                'payout_narration'=> $row['payout_narration'],
                'order_ref_id' => $xettle_payout_reference_id,
                'status'          => $status,
                'user_id'         => $this->user_id,
                'message'   => $errors,
                'failed_from'   => $failed_from,
                'note_1'  => $row['note_1'],
                'note_2'=> $row['note_2']
            ]);

        }
    }

}
