<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DataAggregatorService
{
    private string $financeUrl;
    private string $inventoryUrl;
    private string $orderUrl;
    private string $token;

    public function __construct()
    {
        $this->financeUrl   = rtrim(env('FINANCE_SERVICE_URL', ''), '/');
        $this->inventoryUrl = rtrim(env('INVENTORY_SERVICE_URL', ''), '/');
        $this->orderUrl     = rtrim(env('ORDER_SERVICE_URL', ''), '/');
        $this->token        = env('INTERNAL_SERVICE_TOKEN', '');
    }

    private function http()
    {
        return Http::timeout(10)->withToken($this->token)->acceptJson();
    }

    public function getRevenueSummary(): array
    {
        try {
            $response = $this->http()->get("{$this->financeUrl}/api/v1/reports/revenue");
            return $response->successful() ? ($response->json('data') ?? []) : [];
        } catch (\Exception) {
            return [];
        }
    }

    public function getMonthlyRevenue(): array
    {
        try {
            $response = $this->http()->get("{$this->financeUrl}/api/v1/reports/revenue/monthly");
            return $response->successful() ? ($response->json('data') ?? []) : [];
        } catch (\Exception) {
            return [];
        }
    }

    public function getLowStockBooks(): array
    {
        try {
            $response = $this->http()->get("{$this->inventoryUrl}/api/v1/stock/alerts");
            return $response->successful() ? ($response->json('data') ?? []) : [];
        } catch (\Exception) {
            return [];
        }
    }

    public function getPendingOrderCount(): int
    {
        try {
            $response = $this->http()->get("{$this->orderUrl}/api/v1/orders", [
                'status'   => 'pending',
                'per_page' => 1,
            ]);
            return $response->successful() ? ($response->json('data.total') ?? 0) : 0;
        } catch (\Exception) {
            return 0;
        }
    }

    public function getTopBooks(): array
    {
        try {
            $response = $this->http()->get("{$this->orderUrl}/api/v1/orders", ['per_page' => 200]);
            if (! $response->successful()) {
                return [];
            }

            $orders = $response->json('data.data', []);
            $books  = [];

            foreach ($orders as $order) {
                foreach ($order['items'] ?? [] as $item) {
                    $key = $item['book_id'];
                    if (! isset($books[$key])) {
                        $books[$key] = [
                            'book_id'       => $key,
                            'total_qty'     => 0,
                            'total_revenue' => 0,
                        ];
                    }
                    $books[$key]['total_qty']     += $item['quantity'];
                    $books[$key]['total_revenue'] += $item['subtotal'];
                }
            }

            usort($books, fn ($a, $b) => $b['total_qty'] - $a['total_qty']);

            return array_slice(array_values($books), 0, 10);
        } catch (\Exception) {
            return [];
        }
    }
}
