<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::prefix('v1')->middleware([VerifyJwtToken::class])->group(function () {
    Route::get('orders/customer/{customerId}', [OrderController::class, 'byCustomer'])
         ->middleware('role:admin,sales_agent,customer');
    Route::get('orders/status/{status}', [OrderController::class, 'byStatus'])
         ->middleware('role:admin,warehouse_manager,sales_agent');
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])
         ->middleware('role:admin,warehouse_manager,sales_agent');

    Route::apiResource('orders', OrderController::class)
         ->except(['update'])
         ->middleware('role:admin,sales_agent,customer');
});
