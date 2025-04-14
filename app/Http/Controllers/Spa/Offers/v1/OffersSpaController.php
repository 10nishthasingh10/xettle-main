<?php

namespace App\Http\Controllers\Spa\Offers\v1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OffersSpaController extends Controller
{

    /**
     * Offer by ID
     */
    public function offerById(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'offerId' => "required"
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }


            $offerId = trim($request->offerId);

            $offer = DB::table('offers')
                ->select(
                    'offers.offer_id',
                    'offers.title',
                    'offers.button_text',
                    'offers.short_description',
                    'offers.shared_description',
                    'offers.description',
                    'offers.shared_image',
                    'offers.desc_image',
                    'offers.offer_logo',
                    'offers.track_url',
                    'offer_category.title as c_title',
                    'offer_category.id as c_id'
                )
                ->join('offer_category', 'offers.category_id', '=', 'offer_category.id')
                ->where('offers.status', '1')
                ->whereDate('offers.expired_at', '>=', date('Y-m-d'))
                ->where('offers.offer_id', $offerId)
                ->first();

            if (!empty($offer)) {

                $offer->aff_id = request()->get('userId') . '_' . request()->get('userAgentId');
                $offer->share_text = $offer->shared_description . "\r\n\r\nOpen now:\r\n" . $offer->track_url . $offer->aff_id;

                return ResponseHelper::success('Offer found', $offer);
            }

            return ResponseHelper::failed('No offer found by this ID');
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG, CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }



    /**
     * Offers by category ID
     */
    public function offerByCategory(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'categoryId' => "required",
                    'limit' => "nullable|int"
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }


            $limit = 10;

            if (!empty($request->limit)) {
                $limit = trim($request->limit);
            }

            $categoryId = trim($request->categoryId);

            $offer = DB::table('offers')
                ->select(
                    'offers.offer_id',
                    'offers.title',
                    'offers.button_text',
                    'offers.short_description',
                    'offers.shared_description',
                    'offers.description',
                    'offers.shared_image',
                    'offers.desc_image',
                    'offers.offer_logo',
                    'offer_category.title as c_title',
                    'offer_category.id as c_id'
                )
                ->join('offer_category', 'offers.category_id', '=', 'offer_category.id')
                ->where('offers.status', '1')
                ->whereDate('offers.expired_at', '>=', date('Y-m-d'))
                ->where('offers.category_id', $categoryId)
                ->limit($limit)
                ->inRandomOrder()
                ->get();

            if (!empty($offer)) {
                return ResponseHelper::success('Offer found', $offer);
            }

            return ResponseHelper::failed('No offer found by this ID');
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG, CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }



    /**
     * Offers of the day
     */
    public function offersOfTheDay(Request $request)
    {
        try {

            $limit = 10;

            if (!empty($request->limit)) {
                $limit = trim($request->limit);
            }

            $offer = DB::table('offers')
                ->select(
                    'offers.offer_id',
                    'offers.title',
                    'offers.button_text',
                    'offers.short_description',
                    'offers.shared_description',
                    'offers.shared_image',
                    'offers.desc_image',
                    'offers.offer_logo',
                    'offer_category.title as c_title',
                    'offer_category.id as c_id'
                )
                ->join('offer_category', 'offers.category_id', '=', 'offer_category.id')
                ->where('offers.status', '1')
                ->whereDate('offers.expired_at', '>=', date('Y-m-d'))
                ->limit($limit)
                ->inRandomOrder()
                ->get();


            return ResponseHelper::success('Offers found.', $offer);
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG, CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }
}
