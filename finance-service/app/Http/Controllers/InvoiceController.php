<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::query()
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $invoices]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Invoice::findOrFail($id)]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), ['status' => 'required|in:pending,paid,voided']);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => $request->status]);

        return response()->json(['success' => true, 'data' => $invoice]);
    }

    public function revenueReport(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total_revenue'    => Invoice::where('status', 'paid')->sum('amount'),
                'total_invoices'   => Invoice::count(),
                'paid_invoices'    => Invoice::where('status', 'paid')->count(),
                'pending_invoices' => Invoice::where('status', 'pending')->count(),
                'voided_invoices'  => Invoice::where('status', 'voided')->count(),
            ],
        ]);
    }

    public function monthlyRevenue(): JsonResponse
    {
        // Use PostgreSQL-compatible TO_CHAR; fallback to strftime for SQLite (testing)
        $dbDriver = config('database.default');

        if ($dbDriver === 'pgsql') {
            $formatExpr = "TO_CHAR(created_at, 'YYYY-MM')";
        } elseif ($dbDriver === 'sqlite') {
            $formatExpr = "strftime('%Y-%m', created_at)";
        } else {
            $formatExpr = "DATE_FORMAT(created_at, '%Y-%m')";
        }

        $data = Invoice::where('status', 'paid')
            ->selectRaw("{$formatExpr} as month, SUM(amount) as revenue, COUNT(*) as orders")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }
}
