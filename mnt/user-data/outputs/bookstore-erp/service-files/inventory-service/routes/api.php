<?php

use App\Http\Controllers\BookController;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory Service — API Routes  (prefix: /api/v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(VerifyJwtToken::class)->group(function () {

    // Books CRUD
    Route::apiResource('books', BookController::class);

    // Stock management (called by Order Service via REST)
    Route::get ('books/{id}/stock',        [BookController::class, 'checkStock']);
    Route::patch('books/{id}/stock/deduct',[BookController::class, 'deductStock']);
    Route::patch('books/{id}/stock/restore',[BookController::class, 'restoreStock']);

    // Low-stock report
    Route::get('stock/alerts', [BookController::class, 'lowStockAlerts']);
});
