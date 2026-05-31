<?php

use App\Http\Controllers\OrderController;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order Service — API Routes  (prefix: /api/v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(VerifyJwtToken::class)->group(function () {

    // Orders CRUD
    Route::apiResource('orders', OrderController::class)->except(['update']);

    // Order lifecycle
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Filtered views
    Route::get('orders/customer/{customerId}', [OrderController::class, 'byCustomer']);
    Route::get('orders/status/{status}',       [OrderController::class, 'byStatus']);
});
