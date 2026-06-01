<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayController extends Controller
{
    private function registry(): array
    {
        return [
            'auth'      => env('AUTH_SERVICE_URL'),
            'inventory' => env('INVENTORY_SERVICE_URL'),
            'order'     => env('ORDER_SERVICE_URL'),
            'customer'  => env('CUSTOMER_SERVICE_URL'),
            'finance'   => env('FINANCE_SERVICE_URL'),
            'supplier'  => env('SUPPLIER_SERVICE_URL'),
            'reporting' => env('REPORTING_SERVICE_URL'),
        ];
    }

    public function handle(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $serviceKey = $request->route('service');
        $registry   = $this->registry();

        if (! isset($registry[$serviceKey])) {
            return response()->json([
                'success' => false,
                'message' => "Unknown service: {$serviceKey}",
            ], 404);
        }

        $baseUrl   = rtrim($registry[$serviceKey], '/');
        $path      = $request->getRequestUri();
        $targetUrl = $baseUrl . $path;

        try {
            $response = Http::timeout(15)
                ->withHeaders($this->forwardHeaders($request))
                ->send($request->method(), $targetUrl, [
                    'query'       => $request->query(),
                    'json'        => $request->isJson() ? $request->json()->all() : null,
                    'form_params' => ! $request->isJson() ? $request->all() : null,
                ]);

            return response($response->body(), $response->status())
                ->withHeaders([
                    'Content-Type' => $response->header('Content-Type') ?? 'application/json',
                    'X-Gateway'    => 'PageCraft-ERP',
                    'X-Service'    => $serviceKey,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => "Service '{$serviceKey}' is unavailable.",
                'error'   => $e->getMessage(),
            ], 503);
        }
    }

    private function forwardHeaders(Request $request): array
    {
        $allowed = ['Authorization', 'Accept', 'Content-Type', 'X-Request-ID'];
        $headers = [];

        foreach ($allowed as $header) {
            if ($request->hasHeader($header)) {
                $headers[$header] = $request->header($header);
            }
        }

        $headers['X-Gateway'] = 'PageCraft-API-Gateway';

        return $headers;
    }
}
