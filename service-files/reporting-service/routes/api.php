<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::prefix('v1/reports')->middleware([VerifyJwtToken::class, 'role:admin,sales_agent'])->group(function () {
    Route::get('dashboard',        [ReportController::class, 'dashboard']);
    Route::get('revenue',          [ReportController::class, 'revenue']);
    Route::get('revenue/monthly',  [ReportController::class, 'monthlyRevenue']);
    Route::get('top-books',        [ReportController::class, 'topBooks']);
    Route::get('low-stock',        [ReportController::class, 'lowStock']);
});
