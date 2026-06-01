<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\VerifyJwtToken;
use App\Http\Middleware\VerifyServiceAuth;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

// Service-to-service stock operations (Order Service)
Route::prefix('v1')->middleware(VerifyServiceAuth::class)->group(function () {
    Route::get('books/{id}/stock',         [BookController::class, 'checkStock']);
    Route::patch('books/{id}/stock/deduct',  [BookController::class, 'deductStock']);
    Route::patch('books/{id}/stock/restore', [BookController::class, 'restoreStock']);
});

// Public User-facing endpoints
Route::prefix('v1')->group(function () {
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{book}', [BookController::class, 'show']);
});

// Protected User-facing endpoints
Route::prefix('v1')->middleware(VerifyJwtToken::class)->group(function () {
    Route::get('stock/alerts', [BookController::class, 'lowStockAlerts'])
         ->middleware('role:admin,super_admin,inventory_admin,catalog_admin,orders_admin,finance_admin');

    Route::post('books', [BookController::class, 'store'])->middleware('role:admin,super_admin,inventory_admin,catalog_admin');
    Route::put('books/{book}', [BookController::class, 'update'])->middleware('role:admin,super_admin,inventory_admin,catalog_admin');
    Route::patch('books/{book}', [BookController::class, 'update'])->middleware('role:admin,super_admin,inventory_admin,catalog_admin');
    Route::delete('books/{book}', [BookController::class, 'destroy'])->middleware('role:admin,super_admin,inventory_admin');
});

