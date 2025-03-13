<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'auth',], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'currentUser']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::apiResource('users', UserController::class);

    Route::group(['prefix' => 'customers'], function () {
        Route::post('process-data', [CustomerController::class, 'processData']);
        Route::group(['prefix' => 'search'], function() {
            Route::get('nama/{q}', [CustomerController::class, 'searchByNama']);
            Route::get('nim/{q}', [CustomerController::class, 'searchByNim']);
            Route::get('ymd/{q}', [CustomerController::class, 'searchByYmd']);
            Route::get('', [CustomerController::class, 'search']);
        });
    });
});
