<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accepts internal service token OR valid JWT (via Auth Service).
 */
class VerifyServiceAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $internalToken = env('INTERNAL_SERVICE_TOKEN', '');
        $bearer        = $request->bearerToken();

        if ($internalToken && $bearer === $internalToken) {
            $request->merge(['auth_user' => ['role' => 'service', 'name' => 'internal']]);
            return $next($request);
        }

        return app(VerifyJwtToken::class)->handle($request, $next);
    }
}
