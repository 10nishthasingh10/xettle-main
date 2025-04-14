<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Services\DocVerify\DocService;
use App\Services\DocVerify\DocVerifyBO;
use App\Services\DocVerify\DocVerifyTechnoApi;
use App\Services\DocVerify\DocVerifyZoopApi;
use App\Services\OCRService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class OnboardingController extends Controller
{

    /**
     * Step: 2
     * Business overview
     */
    public function businessOverview(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'business_name' => 'required',
                    'business_type' => 'required',
                    'business_category' => 'required',
                    'business_description' => 'required',
                    'address' => 'required',
                    'pincode' => 'required|digits:6',
                    'city' => 'required',
                    'state' => 'required'
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }


            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '1') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }

            $userId = Auth::user()->id;


            //check that user info already exist
            $userBizInfo = DB::table('business_infos')
                ->select('id')
                ->where('user_id', $userId)
                ->first();

            if (!empty($userBizInfo)) {
                //update record
                $updateData = [
                    // 'user_id' => Auth::user()->id,
                    'business_name' => $request->business_name,
                    'business_type' => $request->business_type,
                    'business_category_id' => $request->business_category,
                    'business_description' => str_replace(["\n", "\t"], " ", $request->business_description),
                    'address' => $request->address,
                    'pincode' => $request->pincode,
                    'city' => $request->city,
                    'state' => $request->state,
                    'is_active' => '0',
                    // 'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $isInserted = DB::table('business_infos')
                    ->where('user_id', $userId)
                    ->update($updateData);
            } else {

                //insert record

                $insertData = [
                    'user_id' => $userId,
                    'business_name' => $request->business_name,
                    'business_type' => $request->business_type,
                    'business_category_id' => $request->business_category,
                    'business_description' => str_replace(["\n", "\t"], " ", $request->business_description),
                    'address' => $request->address,
                    'pincode' => $request->pincode,
                    'city' => $request->city,
                    'state' => $request->state,
                    'is_active' => '0',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $isInserted = DB::table('business_infos')
                    ->insert($insertData);
            }


            // DB::beginTransaction();

            // $isInserted = DB::table('business_infos')
            //     ->insert($insertData);

            if ($isInserted) {

                //one step up to signup status
                DB::table('users')
                    ->where('id', $userId)
                    ->update(['signup_status' => '2']);

                // DB::commit();

                return ResponseHelper::success('Business Overview updated successfully.', ['signup_step' => '2']);
            }

            return ResponseHelper::failed('Business Overview updation failed.');
        } catch (Exception $e) {
            // DB::rollBack();
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }



    /**
     * Step 3
     * Business and Owner kyc info
     */
    public function businessDetails(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'business_pan' => 'required|size:10|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
                    'owner_pan' => 'required|size:10|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
                    'owner_aadhaar' => 'required|digits:12',
                    'gstin' => [
                        "required", "size:15", "regex:/^([0-9]){2}([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([a-zA-Z0-9]){3}?$/",
                        function ($attr, $val, $fail) {

                            $checkSql = DB::table('business_infos')
                                ->select('id')
                                ->where('gstin', $val)
                                ->where('user_id', '<>', Auth::user()->id)
                                ->first();

                            if (!empty($checkSql)) {
                                $fail("This $attr already used");
                            }
                        }
                    ],
                    // 'web_url' => ['url|active_url', Rule::requiredIf(empty($request->app_url))],
                    // 'app_url' => ['url|active_url', Rule::requiredIf(empty($request->web_url))]
                ]
            );


            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }


            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '2') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }


            // $businessPan = strtoupper(trim($request->business_pan));
            // $ownerPan = strtoupper(trim($request->owner_pan));
            // $ownerAadhaar = trim($request->owner_aadhaar);
            $gstin = strtoupper(trim($request->gstin));
            // $webUrl = (trim($request->web_url));
            // $appUrl = (trim($request->app_url));

            //get and name match of the owner
            $bizInfo = DB::table('business_infos')
                ->select('pan_owner_name', 'aadhaar_name')
                ->where('user_id', Auth::user()->id)
                ->first();

            $validationHelper = new ValidationHelper();
            $percentage = $validationHelper->getNameMatch($bizInfo->pan_owner_name, $bizInfo->aadhaar_name);


            $isInserted = DB::table('business_infos')
                ->where('user_id', Auth::user()->id)
                ->update([
                    // 'web_url' => $webUrl,
                    // 'app_url' => $appUrl,
                    'gstin' => $gstin,
                    'owner_match_percentage' => $percentage,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            if ($isInserted) {

                //one step up to signup status
                DB::table('users')
                    ->where('id', Auth::user()->id)
                    ->update(['signup_status' => '3']);

                // DB::commit();

                return ResponseHelper::success('Business Details updated successfully.', ['signup_step' => '3']);
            }

            return ResponseHelper::failed('Business Details updation failed.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }



    /**
     * Step 4
     * Business and Owner kyc info
     */
    public function businessInfo(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'web_url' => ['required', 'url', 'active_url'],
                    //Rule::requiredIf(empty($request->app_url))
                    'app_url' => ['nullable', 'url', 'active_url']
                ]
            );


            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }


            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '3') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }


            $webUrl = (trim($request->web_url));
            $appUrl = (trim($request->app_url));

            $validationHelper = new ValidationHelper();

            if ($validationHelper->checkIsPublicDomain($webUrl)) {
                return ResponseHelper::missing(['web_url' => ["Are you sure this is your Web URL?"]]);
            }


            $isInserted = DB::table('business_infos')
                ->where('user_id', Auth::user()->id)
                ->update([
                    'web_url' => $webUrl,
                    'app_url' => $appUrl,
                    'is_kyc_updated' => '1',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            if ($isInserted) {

                //one step up to signup status
                DB::table('users')
                    ->where('id', Auth::user()->id)
                    ->update(['signup_status' => '4']);

                // DB::commit();

                return ResponseHelper::success('Business Details updated successfully.', ['signup_step' => '4']);
            }

            return ResponseHelper::failed('Business Details updation failed.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * Step: 5
     * Video Kyc
     */
    public function videoKyc(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'video' => "required|file|max:8072|mimetypes:video/webm,video/mp4",
                    'kyc_number' => "required",
                    'longitude' => "required",
                    'latitude' => "required",
                ]
            );


            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }


            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '4') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }


            $userId = Auth::user()->id;


            $checkVkyc = DB::table('user_video_kyc')
                ->select('id', 'status', 'video_path')
                ->where('user_id', $userId)
                // ->where('status', '<>', '2')
                ->first();

            if (!empty($checkVkyc)) {

                if ($checkVkyc->status === '1') {
                    return ResponseHelper::failed('Your KYC is completed.');
                }

                if ($checkVkyc->status === '0') {
                    return ResponseHelper::failed('Your KYC is under processing.');
                }

                Storage::disk('public')->delete('videos/' . $checkVkyc->video_path);

                $path = Storage::disk('public')->put("videos", $request->video);
                $path = substr($path, strrpos($path, '/') + 1);

                $updateData = [
                    'kyc_text' => ($request->kyc_number),
                    'video_path' => $path,
                    'status' => '0',
                    'longitude' => trim($request->longitude),
                    'latitude' => trim($request->latitude),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                DB::table('user_video_kyc')
                    ->where('id', $checkVkyc->id)
                    ->where('user_id', $userId)
                    ->update($updateData);


                DB::table('users')
                    ->where('id', Auth::user()->id)
                    ->update([
                        'signup_status' => SIGNUP_STATUS_VIDEO_PENDING,
                        'is_profile_updated' => '1'
                    ]);


                return ResponseHelper::success('Video KYC updated successfull.', ['signup_step' => '5']);
            } else {

                $path = Storage::disk('public')->put("videos", $request->video);
                $path = substr($path, strrpos($path, '/') + 1);

                $insertData = [
                    'user_id' => $userId,
                    'kyc_text' => ($request->kyc_number),
                    'video_path' => $path,
                    'status' => '0',
                    'longitude' => trim($request->longitude),
                    'latitude' => trim($request->latitude),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                DB::table('user_video_kyc')->insert($insertData);


                DB::table('users')
                    ->where('id', Auth::user()->id)
                    ->update([
                        'signup_status' => SIGNUP_STATUS_VIDEO_PENDING,
                        'is_profile_updated' => '1'
                    ]);

                return ResponseHelper::success('Video KYC updated successfull.', ['signup_step' => '5']);
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * Verify pan 
     */
    public function verifyPan(Request $request)
    {
        try {

            if ($request->type == 'business') {
                $validationArr = [
                    'required', 'string', 'size:10', 'regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
                    function ($attr, $val, $fail) {

                        $checkSql = DB::table('business_infos')
                            ->select('id')
                            ->where('business_pan', $val)
                            ->where('user_id', '<>', Auth::user()->id)
                            ->first();

                        if (!empty($checkSql)) {
                            $fail("This $attr already used");
                        }
                    }
                ];
            } else {
                $validationArr = [
                    'required', 'string', 'size:10', 'regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
                    function ($attr, $val, $fail) {

                        $checkSql = DB::table('business_infos')
                            ->select('id')
                            ->where('pan_number', $val)
                            ->where('user_id', '<>', Auth::user()->id)
                            ->first();

                        if (!empty($checkSql)) {
                            $fail("This $attr already used");
                        }
                    }
                ];
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'pan' => $validationArr,
                    'type' => "required|in:business,personal"
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }

            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '2') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }

            $pan = strtoupper(trim($request->pan));
            $type = trim($request->type);
            $panChar = strtoupper(substr($pan, 3, 1));
            $isValidPanChar = false;


            $bisinessInfo = DB::table('business_infos')
                ->select('business_type')
                ->where('user_id', Auth::user()->id)
                ->first();


            switch ($type) {
                case 'business':

                    if (strtolower($bisinessInfo->business_type) === 'proprietorship') {
                        if ($panChar === 'P') {
                            $isValidPanChar = true;
                        }
                    } else {
                        if (
                            $panChar === 'C' ||
                            $panChar === 'H' ||
                            $panChar === 'G' ||
                            $panChar === 'L' ||
                            $panChar === 'F' ||
                            $panChar === 'T'
                        ) {
                            $isValidPanChar = true;
                        }
                    }


                    break;

                case 'personal':
                    if ($panChar === 'P') {
                        $isValidPanChar = true;
                    }
                    break;
            }

            if (!$isValidPanChar && strtolower($bisinessInfo->business_type) === 'proprietorship') {
                return ResponseHelper::failed("Please enter your pan.");
            } else if (!$isValidPanChar) {
                return ResponseHelper::failed("Please enter your $type pan.");
            }


            // $helper = new ValidationHelper();

            // $rootType = $helper->getApiRoot('pan');


            // if ($rootType === '1') {

            $groupId = 'XTL_ONB_' . date('Ymd');
            $orderRefId = CommonHelper::getRandomString('xpn', false);


            $body = [
                "task_id" => $orderRefId,
                "group_id" => $groupId,
                "data" => [
                    "id_number" => $pan
                ]
            ];


            $ocrService = new OCRService();

            $response = $ocrService->init(
                $body,
                '/v3/tasks/async/verify_with_source/ind_pan',
                'panValidation',
                Auth::user()->id,
                'yes',
                'verificationOnboarding'
            );

            $responseMessage = isset($response['response']['response']) ? $response['response']['response'] : 'No response found.';

            if (isset($response['response']['response']->request_id)) {
                // if (true) {
                $requestId = $response['response']['response']->request_id;
                // $requestId = 'jakdjkd-kdskdj-skdjskdk';

                return ResponseHelper::success('Success', ['requestId' => $requestId, 'taskId' => $orderRefId]);
            } else {
                return ResponseHelper::failed('Record not fetched.', $responseMessage);
            }
            // } else if ($rootType === '2') {

            //     $orderRefId = CommonHelper::getRandomString('xpn', false);

            //     $bo = new DocVerifyBO();

            //     $bo->param = [
            //         'panno' => $pan,
            //         'clientRefId' => $orderRefId
            //     ];


            //     $bo->userId = Auth::user()->id;
            //     $bo->slug = 'pan';
            //     $bo->clientRefId = $orderRefId;
            //     $bo->uri = 'pan';

            //     $docService = (new DocService(new DocVerifyTechnoApi()))->getService();
            //     $response = $docService->send($bo);

            //     $statusCode = isset($response['response']['response']->statuscode) ? $response['response']['response']->statuscode : '';
            //     $message = isset($response['response']['response']->message) ? $response['response']['response']->message : '';
            //     $resData = isset($response['response']['response']->Data[0]) ? $response['response']['response']->Data[0] : [];


            //     if ($type === 'business') {

            //         DB::table('business_infos')
            //             ->where('user_id', Auth::user()->id)
            //             ->update(['business_pan_response' => json_encode($response)]);
            //     } else if ($type === 'personal') {

            //         DB::table('business_infos')
            //             ->where('user_id', Auth::user()->id)
            //             ->update(['owner_pan_response' => json_encode($response)]);
            //     }


            //     if ($statusCode === '000' && !empty($resData)) {

            //         $clientResponse = $helper->generateResponse('pan_kd', $resData);

            //         if ($clientResponse['currentStatus'] === 'FOUND') {

            //             if ($type === 'business') {

            //                 DB::table('business_infos')
            //                     ->where('user_id', Auth::user()->id)
            //                     ->update([
            //                         'business_pan' => $pan,
            //                         'business_name' => $clientResponse['response']['fullName']
            //                     ]);
            //             } else if ($type === 'personal') {

            //                 DB::table('business_infos')
            //                     ->where('user_id', Auth::user()->id)
            //                     ->update([
            //                         'pan_number' => $pan,
            //                         'pan_owner_name' => $clientResponse['response']['fullName']
            //                     ]);
            //             }
            //         }


            //         return ResponseHelper::success('Record fetched successfully.', $clientResponse);
            //     } else {
            //         $message = !empty($message) ? $message : 'Something went wrong, try after some time.';

            //         return ResponseHelper::failed($message);
            //     }
            // }
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * Verify pan 
     */
    public function verifyPanStatus(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'requestId' => ['required', 'string', 'max:50'],
                    'taskId' => ['required', 'string', 'max:50'],
                    'type' => "required|in:business,personal",
                    'pan' => ['required', 'string', 'size:10', 'regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/'],
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }

            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '2') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }

            $pan = strtoupper(trim($request->pan));
            $type = trim($request->type);
            $requestId = trim($request->requestId);
            $taskId = trim($request->taskId);


            $helper = new ValidationHelper();

            // $rootType = $helper->getApiRoot('pan');


            // if ($rootType === '1') {

            $ocrService = new OCRService();

            if ($requestId && $taskId) {

                $response = $ocrService->init(
                    ['task_id' => $taskId],
                    '/v3/tasks?request_id=' . $requestId,
                    'getTaskDetails',
                    Auth::user()->id,
                    'yes',
                    'verificationOnboarding',
                    'GET'
                );

                // $response['response']['response'] = json_decode('[
                //         {
                //             "action": "verify_with_source",
                //             "completed_at": "2023-03-28T12:10:42+05:30",
                //             "created_at": "2023-03-28T12:10:40+05:30",
                //             "group_id": "XTL_PAN_20230328",
                //             "request_id": "871d87bd-ade6-4d83-b6e3-41301baf17e1",
                //             "result": {
                //                 "source_output": {
                //                     "aadhaar_seeding_status": true,
                //                     "first_name": "Amit",
                //                     "gender": null,
                //                     "id_number": "BJZPG8848R",
                //                     "last_name": "Gupta",
                //                     "middle_name": "Kumar",
                //                     "name_on_card": "Amit Kumar Gupta",
                //                     "source": "NSDL",
                //                     "status": "id_found"
                //                 }
                //             },
                //             "status": "completed",
                //             "task_id": "VPN7742485114478647FBDC",
                //             "type": "ind_pan"
                //         }
                //     ]');

                $apiMessage = !empty($response['response']['response']->message) ? $response['response']['response']->message : '';

                if (isset($response['response']['response']) && empty($apiMessage)) {
                    $status = "";
                    $taskType = '';
                    $apiResponse = null;

                    foreach ($response['response']['response'] as $val) {
                        $status = @$val->status;
                        $taskType = @$val->type;
                        $apiResponse = $val;
                    }


                    if ($type === 'business') {
                        DB::table('business_infos')
                            ->where('user_id', Auth::user()->id)
                            ->update(['business_pan_response' => json_encode($response)]);
                    } else if ($type === 'personal') {
                        DB::table('business_infos')
                            ->where('user_id', Auth::user()->id)
                            ->update(['owner_pan_response' => json_encode($response)]);
                    }


                    $clientResponse = $helper->generateResponse($taskType, $apiResponse);
                    $currentStatus = !empty($clientResponse['currentStatus']) ? $clientResponse['currentStatus'] : '';

                    if ($currentStatus === 'FOUND') {

                        if ($type === 'business') {

                            DB::table('business_infos')
                                ->where('user_id', Auth::user()->id)
                                ->update([
                                    'business_pan' => $pan,
                                    'business_name_from_pan' => $clientResponse['response']['fullName']
                                ]);
                        } else if ($type === 'personal') {

                            DB::table('business_infos')
                                ->where('user_id', Auth::user()->id)
                                ->update([
                                    'pan_number' => $pan,
                                    'pan_owner_name' => $clientResponse['response']['fullName']
                                ]);
                        }
                    }


                    if ($status == 'failed') {
                        return ResponseHelper::failed('Record not fetched.', $clientResponse);
                    } else if ($status == 'in_progress') {
                        return ResponseHelper::pending('Transaction is in process.', $clientResponse);
                    } else if ($status == 'completed') {
                        return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                    }
                } else {
                    return ResponseHelper::failed('Failed. ' . $apiMessage);
                }
            }

            return ResponseHelper::failed('Invalid requestId');
            // }

            // if ($rootType === '2') {

            //     $orderRefId = CommonHelper::getRandomString('xpn', false);

            //     $bo = new DocVerifyBO();

            //     $bo->param = [
            //         'panno' => $pan,
            //         'clientRefId' => $orderRefId
            //     ];


            //     $bo->userId = Auth::user()->id;
            //     $bo->slug = 'pan';
            //     $bo->clientRefId = $orderRefId;
            //     $bo->uri = 'pan';

            //     $docService = (new DocService(new DocVerifyTechnoApi()))->getService();
            //     $response = $docService->send($bo);

            //     $statusCode = isset($response['response']['response']->statuscode) ? $response['response']['response']->statuscode : '';
            //     $message = isset($response['response']['response']->message) ? $response['response']['response']->message : '';
            //     $resData = isset($response['response']['response']->Data[0]) ? $response['response']['response']->Data[0] : [];


            //     if ($type === 'business') {

            //         DB::table('business_infos')
            //             ->where('user_id', Auth::user()->id)
            //             ->update(['business_pan_response' => json_encode($response)]);
            //     } else if ($type === 'personal') {

            //         DB::table('business_infos')
            //             ->where('user_id', Auth::user()->id)
            //             ->update(['owner_pan_response' => json_encode($response)]);
            //     }


            //     if ($statusCode === '000' && !empty($resData)) {

            //         $clientResponse = $helper->generateResponse('pan_kd', $resData);

            //         if ($clientResponse['currentStatus'] === 'FOUND') {

            //             if ($type === 'business') {

            //                 DB::table('business_infos')
            //                     ->where('user_id', Auth::user()->id)
            //                     ->update([
            //                         'business_pan' => $pan,
            //                         'business_name' => $clientResponse['response']['fullName']
            //                     ]);
            //             } else if ($type === 'personal') {

            //                 DB::table('business_infos')
            //                     ->where('user_id', Auth::user()->id)
            //                     ->update([
            //                         'pan_number' => $pan,
            //                         'pan_owner_name' => $clientResponse['response']['fullName']
            //                     ]);
            //             }
            //         }


            //         return ResponseHelper::success('Record fetched successfully.', $clientResponse);
            //     } else {
            //         $message = !empty($message) ? $message : 'Something went wrong, try after some time.';

            //         return ResponseHelper::failed($message);
            //     }
            // }
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * Verify Aadhaar
     */
    public function verifyAadhaar(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'aadhaar' => ['required', 'digits:12']
                ]
            );


            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }


            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '2') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }


            $aadhaar = trim($request->aadhaar);

            $helper = new ValidationHelper();

            if (!$helper->validateAadhaar($aadhaar)) {
                return ResponseHelper::missing(['aadhaar' => ["Aadhaar number is invalid."]]);
            }

            $rootType = (new ValidationHelper())->getApiRoot('aadhaar');
            $orderRefId = CommonHelper::getRandomString('xad', false);

            $bo = new DocVerifyBO();
            $bo->param = [
                'aadhaar' => $aadhaar,
                'clientRefId' => $orderRefId
            ];
            $bo->userId = Auth::user()->id;
            $bo->slug = 'aadhaar';
            $bo->clientRefId = $orderRefId;
            $bo->uri = 'aadhaar';

            if ($rootType === '2') {

                $docService = (new DocService(new DocVerifyZoopApi()))->getService();
                $response = $docService->send($bo);
                $httpStatusCode = $response['response']['statusCode'];


                if ($httpStatusCode === 200) {

                    $requestId = @$response['response']['response']->request_id;
                    $taskId = @$response['response']['response']->task_id;
                    // $responseMessage = @$response['response']['response']->response_message;

                    $userResponse = [
                        'clientRefId' => $orderRefId,
                        'requestId' => $requestId,
                        'taskId' => $taskId
                    ];

                    return ResponseHelper::success('OTP sent successfully.', $userResponse);
                } else {
                    $responseMessage = @$response['response']['response']->response_message;
                    return ResponseHelper::failed("Something went wrong, please try after sometime.", ['message' => $responseMessage]);
                }
            } else if ($rootType === '1') {
                $docService = (new DocService(new DocVerifyTechnoApi()))->getService();

                $response = $docService->send($bo);

                $statusCode = isset($response['response']['response']->statuscode) ? $response['response']['response']->statuscode : '';
                $message = isset($response['response']['response']->message) ? $response['response']['response']->message : 'No Response found.';
                $resData = isset($response['response']['response']->Data[0]) ? $response['response']['response']->Data[0] : [];

                if ($statusCode === '000' && isset($resData->mahareferid)) {

                    $responseId = @$resData->mahareferid;

                    $userResponse = [
                        'clientRefId' => $orderRefId,
                        'requestId' => @$responseId
                    ];


                    DB::table('business_infos')
                        ->where('user_id', Auth::user()->id)
                        ->update([
                            'aadhar_number' => $aadhaar,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                    return ResponseHelper::success('Record fetched successfully.', $userResponse);
                } else {
                    return ResponseHelper::failed($message);
                }
            } else {
                return ResponseHelper::failed('Invalid root for varification');
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * verify Aadhaar OTP submit
     */
    public function verifyAadhaarOtp(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'otp' => ['required', 'digits_between:6,8'],
                    'clientRefId' => ['required', 'string', 'max:50'],
                    'requestId' => ['required', 'string', 'max:50']
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }

            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '2') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }

            $orderRefId = trim($request->clientRefId);
            $requestId = trim($request->requestId);
            $otp = trim($request->otp);

            $bo = new DocVerifyBO();

            $bo->param = [
                'requestId' => $requestId,
                'clientRefId' => $orderRefId,
                'otp' => $otp
            ];

            $bo->userId = Auth::user()->id;
            $bo->slug = 'aadhaarOtp';
            $bo->clientRefId = $orderRefId;
            $bo->uri = 'aadhaarOtp';

            $helper = new ValidationHelper();

            $rootType = $helper->getApiRoot('aadhaar');

            if ($rootType === '2') {
                $bo->param['taskId'] = trim($request->get('taskId'));

                $docService = (new DocService(new DocVerifyZoopApi()))->getService();
                $response = $docService->send($bo);

                $httpStatusCode = $response['response']['statusCode'];

                if ($httpStatusCode === 200) {

                    $requestId = @$response['response']['response']->request_id;
                    // $taskId = @$response['response']['response']->task_id;
                    // $groupId = @$response['response']['response']->group_id;
                    // $success = @$response['response']['response']->success;
                    // $responseCode = @$response['response']['response']->response_code;
                    $responseMessage = @$response['response']['response']->response_message;
                    $resData = @$response['response']['response']->result;


                    if (!empty($resData)) {
                        $clientResponse = [
                            "requestId" => $orderRefId,
                            "currentStatus" =>   $helper->getStatus('success'),
                            "response" => [
                                "fullName" => $resData->user_full_name,
                                "dob" => $resData->user_dob,
                                "gender" => $resData->user_gender,
                                "zip" => $resData->address_zip,
                                "house" => $resData->user_address->house,
                                "district" => $resData->user_address->dist,
                                "vtc" => $resData->user_address->vtc,
                                "loc" => $resData->user_address->loc,
                                "subdistrict" => $resData->user_address->subdist,
                                "country" => $resData->user_address->country,
                                "po" => $resData->user_address->po,
                                "state" => $resData->user_address->state,
                                "street" => $resData->user_address->street,
                                "profileImage" => @$resData->user_profile_image
                            ]
                        ];

                        DB::table('business_infos')
                            ->where('user_id', Auth::user()->id)
                            ->update([
                                'owner_aadhaar_response' => $response,
                                'aadhaar_name' => $clientResponse['response']['fullName'],
                                'aadhar_verified_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

                        return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                    }

                    return ResponseHelper::success('UUID: ' . $responseMessage);
                } else {

                    $responseMessage = @$response['response']['response']->response_message;

                    return ResponseHelper::failed("UUID message: " . $responseMessage);
                }
            } else if ($rootType === '1') {

                $docService = (new DocService(new DocVerifyTechnoApi()))->getService();
                $response = $docService->send($bo);

                $statusCode = isset($response['response']['response']->statuscode) ? $response['response']['response']->statuscode : 'NaN';
                $message = isset($response['response']['response']->message) ? $response['response']['response']->message : 'NA';
                $resData = isset($response['response']['response']->Data[0]) ? $response['response']['response']->Data[0] : [];

                if ($statusCode === '000' && !empty($resData)) {



                    $clientResponse = [
                        "requestId" => $orderRefId,
                        "currentStatus" => isset($resData->uidaimessage) ? $helper->getStatus($resData->uidaimessage) : '',
                        "response" => [
                            "fullName" => $resData->full_name,
                            "dob" => $resData->dob,
                            "gender" => $resData->gender,
                            "zip" => $resData->zip,
                            "house" => $resData->house,
                            "district" => $resData->district,
                            "vtc" => $resData->vtc,
                            "loc" => $resData->loc,
                            "subdistrict" => $resData->subdistrict,
                            "country" => $resData->country,
                            "po" => $resData->po,
                            "state" => $resData->state,
                            "street" => $resData->street,
                            "profileImage" => $resData->profile_image,
                            // "mahareferid" => $resData->mahareferid,
                            // "client_refid" => $resData->client_refid,
                            // "uidaimessage" => $resData->uidaimessage,
                        ]
                    ];


                    DB::table('business_infos')
                        ->where('user_id', Auth::user()->id)
                        ->update([
                            'owner_aadhaar_response' => $response,
                            'aadhaar_name' => $clientResponse['response']['fullName'],
                            'aadhar_verified_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);


                    return ResponseHelper::success('Record fetched successfully.', $clientResponse);
                } else {
                    return ResponseHelper::failed($message);
                }
            }

            return ResponseHelper::failed('Invalid root for varification!!!');
        } catch (Exception $e) {
            return ResponseHelper::failed($e->getMessage());
        }
    }


    /**
     * verify aadhaar bypass 
     */
    public function verifyAadhaarBypass(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'aadhaar' => ['required', 'digits:12']
                ]
            );


            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }

            //get user signup status
            $signupStatus = Auth::user()->signup_status;

            if ($signupStatus !== '2') {
                return ResponseHelper::failed('Your signup status is not correct.');
            }

            $aadhaar = trim($request->aadhaar);

            $helper = new ValidationHelper();

            if (!$helper->validateAadhaar($aadhaar)) {
                return ResponseHelper::missing(['aadhaar' => ["Aadhaar number is invalid."]]);
            }

            DB::table('business_infos')
                ->where('user_id', Auth::user()->id)
                ->update([
                    'aadhar_number' => $aadhaar,
                    'aadhaar_name' => Auth::user()->name,
                    'aadhar_verified_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $clientResponse = [
                "requestId" => $aadhaar
            ];

            $clientResponse = [
                "requestId" => $aadhaar,
                "currentStatus" => '',
                "response" => [
                    "fullName" => Auth::user()->name
                ]
            ];

            return ResponseHelper::success('Record fetched successfully.', $clientResponse);
        } catch (Exception $e) {
            return ResponseHelper::failed($e->getMessage());
        }
    }
}
