<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'supplier-service', 'status' => 'ok']);
});

// Health check alias — Render uses /health by default
Route::get('/health', \App\Http\Controllers\HealthController::class);
