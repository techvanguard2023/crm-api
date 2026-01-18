<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\ServiceController;

Route::prefix('v1')->group(function () {

    Route::get('status', function () {
        return response()->json(['status' => 'API V1 CRM is alive!'], 200);
    });

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::apiResource('customers', CustomerController::class);
        Route::post('/customers/{customer}/services', [CustomerController::class, 'addService']);
        Route::apiResource('domains', DomainController::class);
        Route::apiResource('services', ServiceController::class);
    });

});
