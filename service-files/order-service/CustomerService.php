<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CustomerService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('CUSTOMER_SERVICE_URL', ''), '/');
        $this->token   = env('INTERNAL_SERVICE_TOKEN', '');
    }

    public function recordOrder(int $customerId, float $amount): void
    {
        if (! $this->baseUrl) {
            return;
        }

        try {
            Http::timeout(5)
                ->withToken($this->token)
                ->patch("{$this->baseUrl}/api/v1/customers/{$customerId}/record-order", [
                    'amount' => $amount,
                ]);
        } catch (\Exception $e) {
            \Log::warning('CustomerService: failed to update stats', [
                'customer_id' => $customerId,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
