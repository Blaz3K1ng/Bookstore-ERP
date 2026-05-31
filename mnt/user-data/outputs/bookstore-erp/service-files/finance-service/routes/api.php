<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance Service — API Routes  (prefix: /api/v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(VerifyJwtToken::class)->group(function () {

    // Invoices
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show']);
    Route::patch('invoices/{id}/status', [InvoiceController::class, 'updateStatus']);

    // Reports
    Route::get('reports/revenue',          [InvoiceController::class, 'revenueReport']);
    Route::get('reports/revenue/monthly',  [InvoiceController::class, 'monthlyRevenue']);
});
