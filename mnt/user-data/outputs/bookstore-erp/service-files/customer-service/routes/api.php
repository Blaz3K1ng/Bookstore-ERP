<?php
// =====================================================================
// routes/api.php
// =====================================================================

use App\Http\Controllers\CustomerController;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(VerifyJwtToken::class)->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{id}/orders-summary', [CustomerController::class, 'ordersSummary']);
});
