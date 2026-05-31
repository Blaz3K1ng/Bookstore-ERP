<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service — API Routes  (prefix: /api/v1/auth)
|--------------------------------------------------------------------------
*/

Route::prefix('v1/auth')->group(function () {

    // Public endpoints
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    // Protected endpoints — require valid JWT
    Route::middleware('auth:api')->group(function () {
        Route::get('me',     [AuthController::class, 'me']);
        Route::post('logout',[AuthController::class, 'logout']);
        Route::put('profile',[AuthController::class, 'updateProfile']);
    });
});
