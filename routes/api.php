<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CustomerServiceController;
use App\Http\Controllers\Api\PaymentController;

Route::prefix('v1')->group(function () {

    Route::get('status', function () {
        return response()->json(['status' => 'API V1 CRM is alive!'], 200);
    });

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/customers/with-services', [CustomerController::class, 'withServices']);
        Route::get('/customers/by-service/{service}', [CustomerController::class, 'byServiceType']);
        Route::apiResource('customers', CustomerController::class);
        Route::post('/customers/{customer}/services', [CustomerController::class, 'addService']);
        Route::apiResource('domains', DomainController::class);
        Route::apiResource('services', ServiceController::class);

        Route::post('/customer-services/{id}/renew', [CustomerServiceController::class, 'renew']);

        Route::post('/customer-services/{id}/payment-request', [PaymentController::class, 'store']);
        Route::put('/payments/callback', [PaymentController::class, 'update']);
    });

});
