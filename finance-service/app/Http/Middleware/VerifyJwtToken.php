<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyJwtToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'No authentication token provided.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $response = Http::timeout(5)
                ->withToken($token)
                ->get(env('AUTH_SERVICE_URL') . '/api/v1/auth/me');

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated — invalid or expired token.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $request->merge(['auth_user' => $response->json('data')]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auth service unavailable.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
