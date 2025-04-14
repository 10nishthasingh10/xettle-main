<?php

namespace App\Http\Controllers\Spa\Offers\v1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\OfferCategoryModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategorySpaController extends Controller
{

    /**
     * All category list
     */
    public function allCategoryList(Request $request)
    {
        try {

            $allCategory = DB::table('offer_category')
                ->where('status', '1');

            if (!empty($request->limit)) {
                $allCategory->limit(trim($request->limit));
            }

            $allCategory = $allCategory->orderBy('title', 'asc')
                ->get();

            return ResponseHelper::success('Category list found.', $allCategory);
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * Category Info by category ID
     */
    public function categoryById(Request $request)
    {

        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'categoryId' => "required"
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }

            $categoryId = trim($request->categoryId);

            $category = DB::table('offer_category')
                ->select(
                    'id',
                    'title',
                    'description',
                    'logo'
                )
                ->where('status', '1')
                ->where('id', $categoryId)
                ->first();

            if (!empty($category)) {
                return ResponseHelper::success('Category found', $category);
            }

            return ResponseHelper::failed('No category found by this ID');
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * Category with offers
     */
    public function categoryWithOffers(Request $request)
    {
        try {

            $limit = 10;

            if (!empty($request->limit)) {
                $limit = trim($request->limit);
            }

            $categoryAndOffers = OfferCategoryModel::with('activeOffers')
                ->where('status', '1')
                ->limit($limit)
                ->orderBy('title', 'asc')
                ->get();


            if ($categoryAndOffers->isNotEmpty()) {

                $categoryOffers = [];

                foreach ($categoryAndOffers as $row) {

                    if ($row->activeOffers->isNotEmpty()) {
                        $categoryOffers[] = $row;
                    }
                }

                return ResponseHelper::success('Category with offers found.', $categoryOffers);
            }

            return ResponseHelper::failed('No category with offers found.');
        } catch (Exception $e) {
              if (str_contains($e->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
            }
            return ResponseHelper::swwrong(SOMETHING_WENT_WRONG);
        }
    }
}
