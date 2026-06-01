<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbOk = false;

        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DB Health Check Failed: ' . $e->getMessage());
        }

        $status = $dbOk ? 'healthy' : 'degraded';

        return response()->json([
            'service'  => env('APP_NAME', 'supplier-service'),
            'status'   => $status,
            'database' => $dbOk ? 'connected' : 'disconnected',
            'time'     => now()->toISOString(),
        ], $dbOk ? 200 : 503);
    }
}




