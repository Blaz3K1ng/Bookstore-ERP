<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuthenticateGateway
 *
 * Gateway-level JWT validation. Calls the Auth Service to verify
 * the token before forwarding any protected request downstream.
 * If the token is invalid, the request is rejected at the gateway
 * and never reaches the individual microservices.
 */
class AuthenticateGateway
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token is required.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $authResponse = Http::timeout(5)
                ->withToken($token)
                ->get(env('AUTH_SERVICE_URL') . '/api/v1/auth/me');

            if (! $authResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Attach user context to the request for logging / auditing
            $request->merge(['gateway_user' => $authResponse->json('data')]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication service unavailable.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
