<?php

namespace App\Http\Controllers;

use App\Services\DataAggregatorService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(private DataAggregatorService $aggregator) {}

    public function dashboard(): JsonResponse
    {
        $revenue       = $this->aggregator->getRevenueSummary();
        $lowStock      = $this->aggregator->getLowStockBooks();
        $pendingOrders = $this->aggregator->getPendingOrderCount();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_revenue'    => $revenue['total_revenue'] ?? 0,
                'paid_invoices'    => $revenue['paid_invoices'] ?? 0,
                'pending_invoices' => $revenue['pending_invoices'] ?? 0,
                'pending_orders'   => $pendingOrders,
                'low_stock_count'  => count($lowStock),
            ],
        ]);
    }

    public function revenue(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->aggregator->getRevenueSummary(),
        ]);
    }

    public function monthlyRevenue(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->aggregator->getMonthlyRevenue(),
        ]);
    }

    public function topBooks(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->aggregator->getTopBooks(),
        ]);
    }

    public function lowStock(): JsonResponse
    {
        $books = $this->aggregator->getLowStockBooks();

        return response()->json([
            'success' => true,
            'count'   => count($books),
            'data'    => $books,
        ]);
    }
}
