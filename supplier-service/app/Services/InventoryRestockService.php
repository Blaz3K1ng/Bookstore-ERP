<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class InventoryRestockService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('INVENTORY_SERVICE_URL', ''), '/');
        $this->token   = env('INTERNAL_SERVICE_TOKEN', '');
    }

    private function http()
    {
        return Http::timeout(10)->withToken($this->token)->acceptJson();
    }

    public function restockBook(int $bookId, int $quantity): bool
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
