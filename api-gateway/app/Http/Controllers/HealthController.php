<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'service'  => env('APP_NAME', 'api-gateway'),
            'status'   => 'healthy',
            'time'     => now()->toISOString(),
        ], 200);
    }
}




