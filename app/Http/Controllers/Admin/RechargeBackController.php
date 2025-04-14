<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\UsersDataTable;
use App\Models\User;
use App\Models\UPICollect;
use App\Helpers\ResponseHelper;
use DataTables;
use App\Models\RechargeBack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RechargeBackController extends Controller
{

    public function index()
	{
		$data['page_title'] = 'Charge List';
		$data['view'] = 'admin.reports.recharge_back';
    //     $data['userData']   = DB::table('users')
    //     ->select(DB::raw('id,concat(name," (",email,")") as userName'))
    //     // ->with('UPICollect')
    //    ->where('is_admin', '0')
    //     ->get();
        $data['userData'] = DB::table('upi_collects')
            ->select('*')
            ->join('users', 'upi_collects.user_id', '=', 'users.id')
            ->where('upi_collects.status', 'success')
            ->select('upi_collects.*', 'users.id as user_id', DB::raw('CONCAT(users.name, " ", users.email) as userName'))
            ->where('is_admin', '0')
            ->groupBy('users.id')
            ->get();

		return view($data['view'],$data);
	}

    public function addNewCharge(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {

                $txn_id = $request->txn_id;
                $amount = $request->amount;
                $bank_txn_id = $request->bank_txn_id;

                $chargeData = DB::table('upi_collects')
                ->select('id','user_id','customer_ref_id','integration_id','bank_txn_id','amount','is_chargeback')
                ->where('customer_ref_id',$request->searchTexts)->first();

                if($txn_id != "" && $amount != "" && $bank_txn_id){
                    // dd($chargeData->is_chargeback);
                    if($amount > 0) {

                        if ($chargeData->is_chargeback == 1) {
                            $insert = DB::table('charge_back')
                            ->insert([
                                'user_id' => $chargeData->user_id,
                                'txn_id' => $txn_id,
                                'status' => '1',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                                if ($insert) {
                                    $data = DB::table('users')
                                        ->where('id', $chargeData->user_id)
                                        ->update([
                                            // 'locked_amount' => DB::raw('locked_amount - ' . $amount),
                                            'transaction_amount' => DB::raw('transaction_amount - ' . $amount),
                                            'updated_at' => now(),
                                        ]);
                                        DB::table('upi_collects')
                                        ->where('customer_ref_id', $chargeData->customer_ref_id)
                                        ->update(['is_chargeback' => 0]);
                                
                                        $this->status_code = '200';
                                        $this->status = true;
                                        $this->modal = true;
                                        $this->alert = true;
                                        $this->message = "New charge added succesfully.";
                                        $this->title = "Charge Added";
                                        $this->redirect = false;
                                        return $this->populateresponse();
                
                                } else {
                                    $this->status_code = '100';
                                    $this->status = true;
                                    $this->modal = true;
                                    $this->alert = true;
                                    $this->message_object = true;
                                    $this->message  = array('message' => "Charge not added.");
                                    $this->title = "";
                                    $this->redirect = false;
                                    return $this->populateresponse();        
                                }
                        } else {
                            $this->status = false;
                            $this->modal = true;
                            $this->alert = true;
                            $this->message_object = true;
                            $this->message  = array('message' => "Customer Ref ID already exists.");
                            $this->title = "";
                            $this->redirect = false;
                            return $this->populateresponse();
                        }
                    } else {
                        $this->status = false;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message_object = true;
                        $this->message  = array('message' => "Amount must be greater than 0.");
                        $this->title = "";
                        $this->redirect = false;
                        return $this->populateresponse();    
                    }
                }else{

                    if($chargeData){
                        $data = array(
                            'customer_ref_id' => $chargeData->customer_ref_id,
                            'bank_txn_id' => $chargeData->bank_txn_id,
                            'amount' => $chargeData->amount,
                            'status' => 'success',
                        );
                        
                        echo json_encode($data);
                    }else{
                        $data = array(
                            'status' => 'failed',
                        );
                        
                        echo json_encode($data);

                    }
                }
            } else {
                return abort(404);
            }
        
        } catch (Exception $e) {
            // return response("Error: " . $e->getMessage(), 404);
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


}
