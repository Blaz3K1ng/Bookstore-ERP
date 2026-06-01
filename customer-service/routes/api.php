<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\VerifyJwtToken;
use App\Http\Middleware\VerifyServiceAuth;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::prefix('v1')->middleware(VerifyServiceAuth::class)->group(function () {
    Route::patch('customers/{id}/record-order', [CustomerController::class, 'recordOrder']);
});

Route::prefix('v1')->middleware(VerifyJwtToken::class)->group(function () {
    Route::get('customers/{id}/orders-summary', [CustomerController::class, 'ordersSummary'])
         ->middleware('role:admin,super_admin,orders_admin');

    Route::apiResource('customers', CustomerController::class)
         ->middleware('role:admin,super_admin,orders_admin');
});

