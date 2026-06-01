<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class InventoryService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('INVENTORY_SERVICE_URL'), '/');
        $this->token   = env('INTERNAL_SERVICE_TOKEN', '');
    }

    private function http()
    {
        return Http::timeout(10)->withToken($this->token)->acceptJson();
    }

    public function checkStock(int $bookId): array
    {
        try {
            $response = $this->http()->get("{$this->baseUrl}/api/v1/books/{$bookId}/stock");

            if (! $response->successful()) {
                throw new \RuntimeException('Inventory service error: ' . $response->body());
            }

            return $response->json('data');
        } catch (RequestException $e) {
            throw new \RuntimeException('Inventory service unavailable: ' . $e->getMessage());
        }
    }

    public function deductStock(int $bookId, int $quantity): bool
    {
        try {
            return $this->http()
                ->patch("{$this->baseUrl}/api/v1/books/{$bookId}/stock/deduct", ['quantity' => $quantity])
                ->successful();
        } catch (RequestException) {
            return false;
        }
    }

    public function restoreStock(int $bookId, int $quantity): bool
    {
        try {
            return $this->http()
                ->patch("{$this->baseUrl}/api/v1/books/{$bookId}/stock/restore", ['quantity' => $quantity])
                ->successful();
        } catch (RequestException) {
            return false;
        }
    }
}
