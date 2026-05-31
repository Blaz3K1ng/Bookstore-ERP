<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

/**
 * InventoryService
 *
 * Handles all synchronous REST communication with the Inventory microservice.
 * Communication method: HTTP REST  (satisfies inter-service requirement #1)
 */
class InventoryService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('INVENTORY_SERVICE_URL'), '/');
        // Internal service-to-service token (set a dedicated service account JWT)
        $this->token   = env('INTERNAL_SERVICE_TOKEN', '');
    }

    private function http()
    {
        return Http::timeout(10)
                   ->withToken($this->token)
                   ->acceptJson();
    }

    /**
     * Check if a book has enough stock before placing an order.
     *
     * @return array{available: bool, stock_qty: int, price: float}
     * @throws \RuntimeException
     */
    public function checkStock(int $bookId): array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/api/v1/books/{$bookId}/stock");

            if (! $response->successful()) {
                throw new \RuntimeException(
                    "Inventory service error: " . $response->body()
                );
            }

            return $response->json('data');
        } catch (RequestException $e) {
            throw new \RuntimeException('Inventory service unavailable: ' . $e->getMessage());
        }
    }

    /**
     * Deduct stock after a successful order.
     * Rolls back if the request fails (caller handles compensation).
     */
    public function deductStock(int $bookId, int $quantity): bool
    {
        try {
            $response = $this->http()
                ->patch("{$this->baseUrl}/api/v1/books/{$bookId}/stock/deduct", [
                    'quantity' => $quantity,
                ]);

            return $response->successful();
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Restore stock when an order is cancelled (compensating transaction).
     */
    public function restoreStock(int $bookId, int $quantity): bool
    {
        try {
            $response = $this->http()
                ->patch("{$this->baseUrl}/api/v1/books/{$bookId}/stock/restore", [
                    'quantity' => $quantity,
                ]);

            return $response->successful();
        } catch (RequestException $e) {
            return false;
        }
    }
}
