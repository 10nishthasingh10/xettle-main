<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\BusinessInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserOnboardingController extends Controller
{
    /**
     * Save Video KYC
     */
    public function userEkycSave(Request $request)
    {

        try {

            $userId = $request->user()->id;

            //check that Video KYC is enable or Not
            $isVideoKycEnabled = CommonHelper::checkIsServiceActive('onboard_video_kyc', $userId);

            if ($isVideoKycEnabled !== true) {
                return ResponseHelper::failed('Video KYC is not enabled.');
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'video' => "required|file|max:3072|mimetypes:video/webm,video/mp4",
                    'kyc_number' => "required",
                    'longitude' => "required",
                    'latitude' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $bizInfo = BusinessInfo::where('user_id', $userId)
                ->first();

            if (empty($bizInfo)) {
                return ResponseHelper::failed('Please complete your profile first.');
            }


            // if (
            //     empty($bizInfo->business_type) ||
            //     empty($bizInfo->pan_number) ||
            //     empty($bizInfo->pan_owner_name) ||
            //     empty($bizInfo->aadhar_number) ||
            //     empty($bizInfo->aadhar_verified_at) ||
            //     empty($bizInfo->city) ||
            //     empty($bizInfo->pincode)
            // ) {
            //     return ResponseHelper::failed('Please complete your profile first.');
            // }


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


                return ResponseHelper::success('Video KYC updated successfull.');
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

                // if ($in) {
                //     // $bizInfo->is_active = '0';
                //     // $bizInfo->is_kyc_updated = '1';

                //     $bizInfo->save();
                // }

                return ResponseHelper::success('Video KYC updated successfull.');
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }
}
