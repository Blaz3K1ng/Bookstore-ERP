<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::prefix('v1')->middleware(VerifyJwtToken::class)->group(function () {
    Route::get('reports/revenue', [InvoiceController::class, 'revenueReport'])
         ->middleware('role:admin,sales_agent');
    Route::get('reports/revenue/monthly', [InvoiceController::class, 'monthlyRevenue'])
         ->middleware('role:admin,sales_agent');

    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show'])
         ->middleware('role:admin,sales_agent');
    Route::patch('invoices/{id}/status', [InvoiceController::class, 'updateStatus'])
         ->middleware('role:admin');
});
