<?php


namespace App\Http\Controllers\Admin;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\User;
use App\Models\UserSettlement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validations\UserValidation as Validations;
class AutoSettlementController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['page_title'] = "Auto Settlement Listing";
        $data['site_title'] = "Auto Settlement Payments";
        $data['view']       = ADMIN . ".autosettlement.list";
        $data['integration'] = Integration::where('is_active', '1')->get();
        $data['userData'] = User::get();
        return view($data['view'])->with($data);
    }

    public function newSettlementLog(Request $request)
    {
        $user_id = $request->user_id;
        $createdBy = Auth::user()->id;
        $validation = new Validations($request);
        $validator  = $validation->addSettlement();
        $validator->after(function($validator) use ($request, $user_id){
            $User = User::where('id',$user_id)->first();
            if (isset($User) && $User->is_active != '1') {
                $message = CommonHelper::getUserStatusMessage($User->is_active);
                $validator->errors()->add('user_id', $message);
            }

            $userSettlement = UserSettlement::where('id' , $request->id)->first();
            if(isset($userSettlement) && !empty($userSettlement) && $userSettlement->status != 'failed'){
                $validator->errors()->add('user_id','Record not valid for create order');
            }

        });
        if($validator->fails()){
            $this->message = $validator->errors();
        }else{
            if(Auth::user()->hasRole('super-admin')){
                $user_settlement_logs = DB::table('user_settlement_logs')
                    ->where(['settlement_ref_id' => $request->settlement_ref_id,'user_id' => $user_id, 'status' => 'failed'])
                    ->first();

                if (isset($user_settlement_logs) && !empty($user_settlement_logs)) {

                    $user_settlements = DB::table('user_settlements')
                        ->where(['settlement_ref_id' => $user_settlement_logs->settlement_ref_id,'user_id' => $user_id])
                        ->first();

                    if (isset($user_settlements) && !empty($user_settlements)) {

                        $txnId = CommonHelper::getRandomString('STTID', false);
                        DB::table('user_settlements')->where(['id' => $request->id, 'user_id' => $user_id])->update(['status' => 'processing']);

                        $isInserted2 = DB::table('user_settlement_logs')->insert(
                            [
                                'user_id' => $user_id,
                                'settlement_ref_id' => $user_settlement_logs->settlement_ref_id,
                                'settlement_txn_id' => $txnId,
                                'amount' => $user_settlement_logs->amount,
                                'service_id' => $user_settlement_logs->service_id,
                                'mode' => $user_settlement_logs->mode,
                                'status' => 'processing',
                                'integration_id' => $request->integration_id,
                                'created_by' => $createdBy
                            ]
                        );
                        if (isset($isInserted2) && !empty($isInserted2)) {
                            dispatch(new \App\Jobs\SettlementJobs( $user_id, $txnId))->onQueue('autosettlement_order_queue')->delay(Carbon::now()->addSeconds(30));
                            $this->message  = "Record Added Successfully";
                        } else {
                            $this->message_object = true;
                            $this->message  = array('message' => "Record not created");
                        }
                    } else {
                        $this->message_object = true;
                        $this->message  = array('message' => "Record not created");
                    }
                } else {
                    $this->message_object = true;
                    $this->message  = array('message' => "Record not created");
                }
            } else {
                $this->message_object = true;
                $this->message  = array('message' => "Unauthorized user access");
            }
            $this->status   = true;
            $this->modal    = false;
            $this->alert    = true;
            $this->title    = "Settlement Order";
            $this->redirect = true;
            $this->modalClose = true;
            $this->modalId = "kt_modal_settelement_logs";
            return $this->populateresponse();
        }

        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => false,
                'title'    => 'Order Creation',
                'data'      => $this->message
            ])
        );
    }

        /**
     * Display a listing of the resource.
     */
    public function settlementStatus($id, $stmlRefId)
    {
        if(Auth::user()->hasRole('super-admin')){
            $user_settlement_logs = DB::table('user_settlements')
                ->where(['settlement_ref_id' => $stmlRefId, 'id' => $id])
                ->first();

            if (isset($user_settlement_logs) && !empty($user_settlement_logs)) {

                $user_settlementsLogs = DB::table('user_settlement_logs')
                    ->where(['settlement_ref_id' => $user_settlement_logs->settlement_ref_id,
                    'status' => $user_settlement_logs->status, 'cron_status' => '0'])
                    ->first();

                if (isset($user_settlementsLogs) && !empty($user_settlementsLogs)) {
                    DB::table('user_settlements')
                        ->where(['settlement_ref_id' => $stmlRefId, 'id' => $id])
                        ->update(['status' => 'hold', 'txt_2' => Auth::user()->id]);
                    DB::table('user_settlement_logs')
                        ->where(['settlement_ref_id' => $user_settlement_logs->settlement_ref_id,
                        'status' => $user_settlement_logs->status])
                        ->update(['status' => 'hold' ,'txt_2' => Auth::user()->id ]);

                    $this->status   = true;
                    $this->modal    = false;
                    $this->alert    = true;
                    $this->title    = 'Settlement Order';
                    $this->message  = "Order status changed Successfully";
                    $this->redirect = false;
                    return $this->populateresponse();
                }
            }
        }
        $this->status   = false;
        $this->modal    = false;
        $this->alert    = true;
        $this->title    = 'Settlement Order';
        $this->message  = "Order status not changed Successfully";
        $this->redirect = false;
        return $this->populateresponse();
    }

}
