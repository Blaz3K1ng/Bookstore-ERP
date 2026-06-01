<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::get('debug', function() {
    try {
        return response()->json(['users' => \App\Models\User::all()]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::post('debug-login', function(\Illuminate\Http\Request $request) {
    try {
        $token = \Tymon\JWTAuth\Facades\JWTAuth::attempt($request->only('email', 'password'));
        return response()->json(['token' => $token]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
    });
});
