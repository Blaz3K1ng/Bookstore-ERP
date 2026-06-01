<?php

use App\Http\Controllers\GatewayController;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\AuthenticateGateway;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [GatewayController::class, 'handle'])->defaults('service', 'auth');
    Route::post('login',    [GatewayController::class, 'handle'])->defaults('service', 'auth');
});

Route::prefix('v1')->middleware(AuthenticateGateway::class)->group(function () {
    Route::any('auth/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')->defaults('service', 'auth');

    Route::any('books/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')->defaults('service', 'inventory');

    Route::get('stock/alerts', [GatewayController::class, 'handle'])
         ->defaults('service', 'inventory');

    Route::any('orders/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')->defaults('service', 'order');

    Route::any('customers/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')->defaults('service', 'customer');

    Route::any('invoices/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')->defaults('service', 'finance');

    // Supplier management routes
    Route::any('suppliers/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')->defaults('service', 'supplier');
    Route::any('purchase-orders/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')->defaults('service', 'supplier');

    // Reporting service — aggregated dashboard and analytics
    Route::get('reports/dashboard',       [GatewayController::class, 'handle'])->defaults('service', 'reporting');
    Route::get('reports/top-books',       [GatewayController::class, 'handle'])->defaults('service', 'reporting');
    Route::get('reports/low-stock',       [GatewayController::class, 'handle'])->defaults('service', 'reporting');

    // Finance revenue reports (directly via finance-service)
    Route::get('reports/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')->defaults('service', 'finance');
});
