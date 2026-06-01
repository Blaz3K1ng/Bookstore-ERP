<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->get('auth_user') ?? $request->get('gateway_user');

        if (! $user || ! isset($user['role'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized — role information missing.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user['role'] === 'admin' || $user['role'] === 'service') {
            return $next($request);
        }

        if (! in_array($user['role'], $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden — insufficient permissions.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
