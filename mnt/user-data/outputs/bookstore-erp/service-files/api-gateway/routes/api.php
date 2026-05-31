<?php

use App\Http\Controllers\GatewayController;
use App\Http\Middleware\AuthenticateGateway;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Gateway — Routes
|
| All external traffic hits port 8000. The gateway:
|   1. Validates the JWT (via Auth Service)
|   2. Forwards the request to the correct microservice
|   3. Returns the response transparently
|
| Public routes (no auth): /api/v1/auth/register, /api/v1/auth/login
|--------------------------------------------------------------------------
*/

// ─── Public Auth endpoints ────────────────────────────────────────
Route::prefix('v1/auth')->group(function () {
    Route::post('register', [GatewayController::class, 'handle'])->defaults('service', 'auth');
    Route::post('login',    [GatewayController::class, 'handle'])->defaults('service', 'auth');
});

// ─── Protected endpoints ──────────────────────────────────────────
Route::prefix('v1')->middleware(AuthenticateGateway::class)->group(function () {

    // Auth Service
    Route::any('auth/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')
         ->defaults('service', 'auth');

    // Inventory Service
    Route::any('books/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')
         ->defaults('service', 'inventory');

    Route::get('stock/alerts', [GatewayController::class, 'handle'])
         ->defaults('service', 'inventory');

    // Order Service
    Route::any('orders/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')
         ->defaults('service', 'order');

    // Customer Service
    Route::any('customers/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')
         ->defaults('service', 'customer');

    // Finance Service
    Route::any('invoices/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')
         ->defaults('service', 'finance');

    Route::get('reports/{path?}', [GatewayController::class, 'handle'])
         ->where('path', '.*')
         ->defaults('service', 'finance');
});
