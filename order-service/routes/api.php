<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::prefix('v1')->middleware([VerifyJwtToken::class])->group(function () {
    Route::get('orders/customer/{customerId}', [OrderController::class, 'byCustomer'])
         ->middleware('role:admin,super_admin,orders_admin,customer');
    Route::get('orders/status/{status}', [OrderController::class, 'byStatus'])
         ->middleware('role:admin,super_admin,orders_admin,inventory_admin');
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])
         ->middleware('role:admin,super_admin,orders_admin,inventory_admin');

    Route::apiResource('orders', OrderController::class)
         ->except(['update'])
         ->middleware('role:admin,super_admin,orders_admin,customer');
});

