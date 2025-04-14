<?php

use App\Http\Controllers\Spa\Offers\v1\CategorySpaController;
use App\Http\Controllers\Spa\Offers\v1\OffersSpaController;
use App\Http\Controllers\Spa\Offers\v1\TokenAuthSpaController;
use App\Http\Controllers\Spa\Offers\v1\UserSpaController;
use Illuminate\Support\Facades\Route;


Route::prefix('api/offer-spa/v1')->group(function () {

    Route::group(['middleware' => ['offerRequestAuth']], function () {
        Route::post('auth', [TokenAuthSpaController::class, 'authorizeToken']);
    });


    Route::group(['middleware' => ['offerRequestAuth', 'offerTokenAuth']], function () {
        Route::prefix('user')->group(function () {
            Route::get('agent-info', [UserSpaController::class, 'agentInfo']);
            Route::get('logout', [UserSpaController::class, 'logout']);
        });

        Route::prefix('offer')->group(function () {
            Route::post('by-id', [OffersSpaController::class, 'offerById']);
            Route::post('by-category', [OffersSpaController::class, 'offerByCategory']);
            Route::post('popular', [OffersSpaController::class, 'offersOfTheDay']);
        });

        Route::prefix('category')->group(function () {
            Route::post('/', [CategorySpaController::class, 'allCategoryList']);
            Route::post('by-id', [CategorySpaController::class, 'categoryById']);
            Route::post('with-offers', [CategorySpaController::class, 'categoryWithOffers']);
        });
    });
});
