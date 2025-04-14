<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\EasebuzzInstaCollectHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use CURLFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PartnersVanController extends Controller
{

    /**
     * Ebuz Partner VAN Controller
     */
    public function ebuzzVanList()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] =  "Ebuzz Partners VAN";
            $data['site_title'] =  "Ebuzz Partners VAN";
            $data['view']       = ADMIN . ".partners_van_ebuzz_list";

            $data['userData'] = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('is_admin', '0')
                ->orderBy('id', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Upload KYC doc for Ebuz VAN
     */
    public function uploadEbuzzVanKycDocs(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'row_id' => 'required|numeric|min:1',
                    'id_proof' => "required|mimes:jpg,jpeg,png,pdf|max:1024",
                    'cancelled_cheque' => "required|required|mimes:jpg,jpeg,png,pdf|max:1024",
                    'other_doc_file' => "required|required|mimes:jpg,jpeg,png,pdf|max:1024",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $vanInfo = DB::table('user_van_accounts')
                ->select('*')
                ->where('id', $request->row_id)
                ->first();

            if (empty($vanInfo)) {
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "This User ID is invalid.");
                $this->title = "Upload VAN KYC";
                $this->redirect = false;
                return $this->populateresponse();
            }

            if ($vanInfo->kyc_status === '1') {
                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "KYC is already completed.");
                $this->title = "Upload VAN KYC";
                $this->redirect = false;
                return $this->populateresponse();
            }

            // if ($vanInfo->kyc_status === '2') {
            //     $this->status = true;
            //     $this->modal = true;
            //     $this->alert = true;
            //     $this->message_object = true;
            //     $this->message  = array('message' => "KYC is already under processing.");
            //     $this->title = "Upload VAN KYC";
            //     $this->redirect = false;
            //     return $this->populateresponse();
            // }


            $fileIdProof = $request->file('id_proof');
            $fileNameIdProof = $fileIdProof->getPathName();
            $fileIdProof = new CURLFile($fileNameIdProof, mime_content_type($fileNameIdProof), $vanInfo->account_number . "_id_proof." . strtolower($fileIdProof->getClientOriginalExtension()));

            $filecanCelledCheque = $request->file('cancelled_cheque');
            $fileNameCancelledCheque = $filecanCelledCheque->getPathName();
            $fileIdCheque = new CURLFile($fileNameCancelledCheque, mime_content_type($fileNameCancelledCheque), $vanInfo->account_number . "_bank_proof." . strtolower($filecanCelledCheque->getClientOriginalExtension()));

            $fileOtherDocFile = $request->file('other_doc_file');
            $fileNameOtherDocFile = $fileOtherDocFile->getPathName();
            $fileIdOtherDocFile = new CURLFile($fileNameOtherDocFile, mime_content_type($fileNameOtherDocFile), $vanInfo->account_number . "_other_doc_file." . strtolower($fileOtherDocFile->getClientOriginalExtension()));

            // $fileIdProof = new CURLFile($_FILES['id_proof']['tmp_name'], $_FILES['id_proof']['type'],  $_FILES['id_proof']['name']);
            // $fileIdCheque = new CURLFile($_FILES['cancelled_cheque']['tmp_name'], $_FILES['cancelled_cheque']['type'], $_FILES['cancelled_cheque']['name']);

            $vanHelper = new EasebuzzInstaCollectHelper();

            $params = [
                "key" => $vanHelper->getKey(),
                "id_proof_file" => $fileIdProof,
                "cancelled_cheque_file" => $fileIdCheque,
                "other_doc_file" => $fileIdOtherDocFile
            ];

            $authParams[] = $vanInfo->account_id;

            $result = $vanHelper->uploadKycDocs(
                $params,
                "/insta-collect/virtual_accounts/{$vanInfo->account_id}/update_kyc/",
                $authParams,
                Auth::user()->id,
                'POST',
                'updateKyc',
                'EasebuzzInstaCollect'
            );


            if ($result['code'] == 200) {

                $apiResponse = json_decode($result['response']);

                //when response is success
                if (!empty($apiResponse->success)) {

                    if ($apiResponse->success == true) {

                        if ($apiResponse->data->success === true) {
                            DB::table('user_van_accounts')
                                ->where('id', $vanInfo->id)
                                ->where('root_type', 'eb_van')
                                ->update([
                                    'kyc_status' => '2',
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);

                            $this->status = true;
                            $this->modal = true;
                            $this->alert = true;
                            $this->message = "KYC documents submitted successfully.";
                            $this->title = "Update VAN KYC";
                            $this->redirect = false;
                            return $this->populateresponse();
                        }


                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message_object = true;
                        $this->message  = array('message' => $apiResponse->data->message);
                        // $this->message = $apiResponse->data->message;
                        $this->title = "Update VAN KYC";
                        $this->redirect = false;
                        return $this->populateresponse();
                    }
                }

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message_object = true;
                $this->message  = array('message' => "Operation Failed.");
                $this->title = "Update VAN KYC";
                $this->redirect = false;
                return $this->populateresponse();
            } else if (!empty($result['response'])) {
                $apiResponse = json_decode($result['response']);
                $message = isset($apiResponse->message) ? $apiResponse->message : "Something went wrong.";
            } else {
                $message =  "Something going wrong.";
            }


            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => $message);
            $this->title = "Update VAN KYC";
            $this->redirect = false;
            return $this->populateresponse();
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Partner Van
     */
    public function editInfo()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] =  "Update Partners VAN";
            $data['site_title'] =  "Update Partners VAN";
            $data['view']       = ADMIN . ".partners_van_edit_info";

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Get Products Info
     */
    public function getBizzInfo($bizzId)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $sqlQuery = DB::table('business_infos')
                ->select(
                    'business_infos.id',
                    'business_infos.business_name',
                    'business_infos.beneficiary_name',
                    'business_infos.ifsc',
                    'business_infos.account_number',
                    'users.name',
                    'users.email',
                    'users.mobile'
                )
                ->leftJoin('users', 'business_infos.user_id', 'users.id')
                ->where('users.is_active', '1')
                ->where('business_infos.id', $bizzId)
                ->whereNotNull('business_infos.van_acc_id')
                ->first();

            if (!empty($sqlQuery)) {
                return ResponseHelper::success('success', $sqlQuery);
            }

            return ResponseHelper::failed('failed');
        } else {
            return abort(404);
        }
    }


    /**
     * Fetch record by UTR
     */
    public function editInfoSubmit(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'bizz_id' => 'required',
                    'business_name' => "required",
                    'account_number' => "required",
                    'ifsc' => "required",
                    'beneficiary_name' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            try {

                $apiStatus = 0;
                $apiMessage = 'Something went wrong.';

                $bizzId = $request->bizz_id;
                $businessName = $request->business_name;
                $accountNumber = $request->account_number;
                $ifsc = $request->ifsc;
                $beneficiaryName = $request->beneficiary_name;

                $accountId = DB::table('business_infos')
                    ->select(
                        'van_acc_id',
                        // 'business_name',
                        // 'beneficiary_name',
                        // 'ifsc',
                        // 'account_number',
                    )
                    ->whereNotNull('van_acc_id')
                    ->where('id', $bizzId)
                    ->first();

                if (empty($accountId)) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => "This Biz ID not found.");
                    $this->title = "Update VAN Info";
                    $this->redirect = false;
                    return $this->populateresponse();
                }


                $params = [
                    "vAccountId" => $accountId->van_acc_id,
                    "name" => $businessName,
                    "remitterAccount" => $accountNumber,
                    "remitterIfsc" => $ifsc,
                ];

                $vanHelper = new CashfreeAutoCollectHelper();

                $result = $vanHelper->vanManager($params, '/cac/v1/editVA', Auth::user()->id, 'POST', 'updateVanInfo');

                if ($result['code'] == 200) {

                    $cashfreeResponse = json_decode($result['response']);

                    //when response is success
                    if ($cashfreeResponse->subCode === "200") {

                        DB::table('business_infos')
                            ->where('id', $bizzId)
                            ->update([
                                'business_name' => $businessName,
                                'account_number' => $accountNumber,
                                'ifsc' => $ifsc,
                                'beneficiary_name' => $beneficiaryName,
                            ]);

                        $apiStatus = 1;
                        $apiMessage = "Business Info Updated Successfully";
                    }

                    $apiMessage = $cashfreeResponse->message;
                }

                if ($apiStatus === 0) {
                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message_object = true;
                    $this->message  = array('message' => $apiMessage);
                    $this->title = "Update VAN Info";
                    $this->redirect = false;
                    return $this->populateresponse();
                }

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->message = $apiMessage;
                $this->title = "Update VAN Info";
                $this->redirect = true;
                return $this->populateresponse();
            } catch (Exception $e) {
                return ResponseHelper::failed($e->getMessage());
            }
        } else {
            return abort(404);
        }
    }



    /**
     * Payout Report For All Users and Total amount filtered by date range
     */
    public function reportsAll(Request $request, $service, $returnType = 'all')
    {
        if (Auth::user()->hasRole('super-admin')) {
            $request['return'] = 'all';
            $request->orderIdArray = [];
            $request->serviceIdArray = [];
            $request->userIdArray = [];
            $request['returnType'] = $returnType;
            // $parentData = session('parentData');
            $request['where'] = 0;


            $toDate = $fromDate = date('Y-m-d');

            if (!empty($request->from)) {
                $fromDate = $request->from;
            }

            if (!empty($request->to)) {
                $toDate = $request->to;
            }

            switch ($service) {

                case 'ebuz-pvan-list':
                    $searchData = ['users.name', 'users.email', 'users.mobile', 'user_van_accounts.account_holder_name', 'user_van_accounts.account_number', 'user_van_accounts.account_number_prefix'];
                    $sqlQuery = DB::table('user_van_accounts')
                        ->select(
                            'user_van_accounts.*',
                            'users.name',
                            'users.email',
                            'users.mobile'
                        )
                        ->leftJoin('users', 'user_van_accounts.user_id', 'users.id')
                        ->where('root_type', 'eb_van');

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('user_van_accounts.user_id', $request->user_id);
                    }

                    break;

                case 'edit-info':
                    $searchData = ['users.name', 'users.email', 'users.mobile', 'business_infos.business_name', 'business_infos.beneficiary_name', 'business_infos.account_number', 'business_infos.van', 'business_infos.van_2'];
                    $sqlQuery = DB::table('business_infos')
                        ->select(
                            'business_infos.id',
                            'business_infos.business_name',
                            'business_infos.beneficiary_name',
                            'business_infos.ifsc',
                            'business_infos.account_number',
                            'business_infos.van',
                            'business_infos.van_ifsc',
                            'business_infos.van_2',
                            'business_infos.van_2_ifsc',
                            'users.name',
                            'users.email',
                            'users.mobile'
                        )
                        ->leftJoin('users', 'business_infos.user_id', 'users.id')
                        ->where('users.is_active', '1')
                        ->whereNotNull('business_infos.van_acc_id');

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('business_infos.user_id', $request->user_id);
                    }

                    // $sqlQuery->orderBy('users.name', 'ASC');
                    break;

                default:
                    return abort(404);
                    break;
            }


            if (!empty($request->search['value'])) {
                $searchValue = trim($request->search['value']);
                $sqlQuery->where(function ($sql) use ($searchValue, $searchData) {
                    foreach ($searchData as $value) {
                        $sql->orWhere($value, 'like', '%' . $searchValue . '%');
                    }
                });
            }

            if (!empty($request->order[0]['column'])) {
                $filterColumn = $request->columns[$request->order[0]['column']]['data'];
                $orderBy = $request->order[0]['dir'];
                $sqlQuery->orderBy($filterColumn, $orderBy);
            } else {
                $sqlQuery->orderBy('users.name', 'DESC');
            }


            $sqlQueryCount = $sqlQuery;
            $sqlQueryCount = $sqlQueryCount->get();

            if ($request['length'] != -1) {
                $sqlQuery->skip($request->start)->take($request->length);
            }
            $result = $sqlQuery->get();


            if ($request->return == "all" || $returnType == "all") {
                $json_data = array(
                    "draw"            => intval($request['draw']),
                    "recordsTotal"    => intval(count($sqlQueryCount)),
                    "recordsFiltered" => intval(count($sqlQueryCount)),
                    "data"            => $result,
                    "from_date" => $fromDate,
                    "to_date" => $toDate,
                    "start" => $request->start,
                    "length" => $request->length,
                );
                echo json_encode($json_data);
            } else {
                return response()->json($result);
            }
        } else {
            return abort(404);
        }
    }



    private function getFileName($file)
    {
        return Str::of($file)->basename();
    }
}
