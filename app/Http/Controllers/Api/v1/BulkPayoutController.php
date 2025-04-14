<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\ImportHelper;
use App\Http\Controllers\Controller;
use Validations\UserValidation as Validations;
use Illuminate\Http\Request;
use App\Models\BulkPayout;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BulkPayoutDetail;
use App\Models\UserService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;

/**
 * Bulk Payout class
 */
class BulkPayoutController extends Controller
{
    /**
     * Bulk Payout Report function
     *
     * @return void
     */
    public function index()
    {
        $Product = BulkPayout::all();
        $this->message = "Record found successfull";
        $this->data = ['products' => $Product];
        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => $this::SUCCESS_STATUS,
                'data'      => $this->data
            ])
        );
    }


    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function bulkImport(Request $request)
    {

        try {
            $id = decrypt($request->user_id);
            $validation = new Validations($request);
            $validator = $validation->importBatchFile();

            $validator->after(function ($validator) use ($request, $id) {

                $user = DB::table('users')->where('id', $id)->first();

                if (empty($user)) {
                    $validator->errors()->add('file', 'Invalid user');
                } else {
                    if ($user->is_active != '1') {
                        $message = CommonHelper::getUserStatusMessage($user->is_active);
                        $validator->errors()->add('file', $message);
                    } else {

                        $userService = UserService::where(['user_id' => $id, 'service_id' => PAYOUT_SERVICE_ID])->where('is_active', '1')->first();
                        if (empty($userService)) {
                            $validator->errors()->add('file', 'Payout service not enable.');
                        } else {

                            $GlobalConfig = DB::table('global_config')
                                ->select('attribute_1', 'attribute_2', 'attribute_4', 'attribute_3')
                                ->where(['slug' => 'bulk_payout'])
                                ->first();

                            $isPayoutServiceEnable = false;
                            if (isset($GlobalConfig)) {
                                $attribute_2 = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "0";
                                $userIdArray = explode(",", $attribute_2);
                                if (in_array("$id", $userIdArray)) {
                                    $isPayoutServiceEnable = true;
                                } else {
                                    if ($GlobalConfig->attribute_1 == 1) {
                                        $isPayoutServiceEnable = true;
                                    }
                                }
                            } else {
                                $isPayoutServiceEnable = true;
                            }
                            if ($isPayoutServiceEnable == false) {
                                $validator->errors()->add('file', 'Bulk payout service is down. Please try after some time.');
                            }
                            if (!CommonHelper::isServiceEnabled($id, PAYOUT_SERVICE_ID)) {
                                $validator->errors()->add('file', 'Your web service is not enabled.');
                            }


                            if (!empty(request()->file('file'))) {

                                $payoutAmount = Excel::toCollection(collect([]), request()->file('file'));
                                $payoutAmountAllColumn = $payoutAmount->toArray();
                                $i = 0;
                                $totalAmount = 0;
                                $status = true;
                                if (isset($payoutAmountAllColumn[0])) {
                                    foreach ($payoutAmountAllColumn[0] as $dataArray) {
                                        if ($i > 0) {
                                            if (isset($dataArray[10]) && (gettype($dataArray[10]) == "integer"  || gettype($dataArray[10]) == "double" || gettype($dataArray[10]) == "float")) {
                                                $totalAmount += (float)$dataArray[10];
                                            } else {
                                                $status = false;
                                                $message = "Payout amount column not found And please give amount int, float, double";
                                            }
                                        }
                                        $i++;
                                    }
                                } else {
                                    $status = false;
                                    $message = "Heading column not found.";
                                }
                                if (isset($GlobalConfig)) {
                                    $attribute_2 = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "0";
                                    $noOfOrders3 = 0;
                                    if (isset($GlobalConfig->attribute_3) && !empty($GlobalConfig->attribute_3) && $GlobalConfig->attribute_3 != NULL) {
                                        $noOfOrders3 = $GlobalConfig->attribute_3;
                                    }
                                    $noOfOrders4 = 0;
                                    if (isset($GlobalConfig->attribute_4) && !empty($GlobalConfig->attribute_4) && $GlobalConfig->attribute_4 != NULL) {
                                        $noOfOrders4 = $GlobalConfig->attribute_4;
                                    }
                                    $j = $i - 1;
                                    $userIdArray = explode(",", $attribute_2);
                                    if (in_array("$id", $userIdArray)) {
                                        if ($j > $noOfOrders4) {
                                            $validator->errors()->add('file', "Please upload maximum $noOfOrders4 orders. Your total orders rows " . $j);
                                        }
                                    } else {
                                        if ($j > $noOfOrders3) {
                                            $validator->errors()->add('file', "Please upload maximum " . $noOfOrders3 . " orders. Your total orders rows " . $j);
                                        }
                                    }
                                }

                                $serviceId = DB::table('global_services')
                                    ->select('service_id')->where('service_slug', 'payout')->first();
                                if (isset($serviceId) && $status) {
                                    $serviceAccountAmount = DB::table('user_services')->select('transaction_amount')
                                        ->where('service_id', $serviceId->service_id)
                                        ->where('user_id', $id)
                                        ->first();
                                    $totalOrderInQueude = DB::table('orders')
                                        ->select(DB::raw('SUM(amount+fee+tax) as totalAmount'))
                                        ->where('user_id', $id)
                                        ->whereIn('status', ['queued', 'hold'])
                                        ->first();
                                    $totalOrderInQueudeAmount = isset($totalOrderInQueude) ? $totalOrderInQueude->totalAmount : 0;
                                    if ($serviceAccountAmount->transaction_amount < $totalAmount + $totalOrderInQueudeAmount) {
                                        $validator->errors()->add('file', 'Insufficient funds. ' . $serviceAccountAmount->transaction_amount . '. Your total bulk payout funds : ' . $totalAmount . '. Your hold and queued amount ' . $totalOrderInQueudeAmount);
                                    }
                                } else {
                                    $validator->errors()->add('file', $message);
                                }
                            }
                        }
                    }
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {

                // $fileExtension = time().'.'.$request->file->extension();
                $batch_id = CommonHelper::getRandomString('batch');
                $fileNameWithExe = $request->file->getClientOriginalName();
                $filename = pathinfo($fileNameWithExe, PATHINFO_FILENAME);
                $fullFilename = $filename . '_' . $batch_id;

                $header = $request->header();
                $agent['ip'] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
                $agent['area'] = "00";
                $agent['userAgent'] = isset($header["user-agent"][0]) ? $header["user-agent"][0] : "";

                $ExcelResponse = Excel::import(new ImportHelper($batch_id, $id, $agent), request()->file('file'));
                $totalRow = BulkPayoutDetail::where('batch_id', $batch_id)->where('user_id', $id)->count();
                $failedRow = BulkPayoutDetail::where('batch_id', $batch_id)->where('user_id', $id)->where('status', 'failed')->count();
                $totalAmount = BulkPayoutDetail::where('batch_id', $batch_id)->where('user_id', $id)->sum('payout_amount');
                $hold_count  = BulkPayoutDetail::where('batch_id', '=', $batch_id)->where('user_id', $id)->where('status', '=', 'hold')->count();
                $hold_amount  = BulkPayoutDetail::where('batch_id', '=', $batch_id)->where('user_id', $id)->where('status', '=', 'hold')->sum('payout_amount');
                $failed_count  = BulkPayoutDetail::where('batch_id', '=', $batch_id)->where('user_id', $id)->where('status', '=', 'failed')->count();
                $failed_amount  = BulkPayoutDetail::where('batch_id', '=', $batch_id)->where('user_id', $id)->where('status', '=', 'failed')->sum('payout_amount');

                if (session('importFileError')) {
                 
                    $this->status   = false;
                    $this->modal    = false;
                    $this->alert    = true;
                    $this->message  = array('message' =>  session('importFileErrorMessage'));
                    $this->redirect = false;
                    $this->message_object = true;
                } else {
                    if ($totalRow == $failedRow) {
                        $batch = new BulkPayout;
                        $batch->batch_id = $batch_id;
                        $batch->filename = $fullFilename;
                        $batch->total_amount = $totalAmount;
                        $batch->total_count = $totalRow;
                        $batch->hold_count = $hold_count;
                        $batch->hold_amount = $hold_amount;
                        $batch->failed_count = $failed_count;
                        $batch->failed_amount = $failed_amount;
                        $batch->user_id = $id;
                        $batch->status = 'failed';
                        $batch->save();
                        $this->message  = array('message' => ' All record is failed');
                        $this->message_object = true;
                        $this->status   = false;
                    } else {
                        $batch = new BulkPayout;
                        $batch->batch_id = $batch_id;
                        $batch->filename = $fullFilename;
                        $batch->total_amount = $totalAmount;
                        $batch->total_count = $totalRow;
                        $batch->user_id = $id;
                        $batch->hold_count = $hold_count;
                        $batch->hold_amount = $hold_amount;
                        $batch->failed_count = $failed_count;
                        $batch->failed_amount = $failed_amount;
                        $batch->status = 'hold';
                        $batch->save();
                        $this->message  = "Batch File uploaded successfully";
                    }

                    $this->status   = true;
                    $this->modal    = true;
                    $this->alert    = true;
                    $this->redirect = true;
                    return $this->populateresponse();
                }
            }

         
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => false,
                    'data'      => $this->message
                ])
            );
        } catch (Exception $e) {
            $ex['message'] = $e->getMessage();
            $ex['line'] = $e->getLine();
            $ex['file'] = $e->getFile();
            $ex['code'] = $e->getCode();
            Storage::put('bulkPayoutExp' . time() . '.txt', print_r($ex, true));

            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;

            $this->message  = array('message' => "Error: something went wrong ");
            // $this->title = "";
            $this->redirect = false;
            return $this->populateresponse();
        } 
    }
}
