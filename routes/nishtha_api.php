<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\ResellersController;

Route::post('reseller/login', [ResellersController::class, 'reseller_login']);

Route::group(['middleware' => 'IsReseller'], function () {
    Route::group(['prefix' => 'api/v1'], function () {
        Route::post('userlist', [ResellersController::class, 'getUserList']);
        Route::post('getupicollect', [ResellersController::class, 'getUpiCollectData']);
        Route::post('getorder', [ResellersController::class, 'getOrderData']);
        Route::post('total-records', [ResellersController::class, 'getPayinPayoutAmount']);
        Route::post('assigncommission', [ResellersController::class, 'assignCommission']);
    });
});