<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CustomerService;
use App\Services\FinancePublisher;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct(
        private InventoryService $inventory,
        private FinancePublisher $finance,
        private CustomerService $customers,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = Order::with('items')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'customer_id'      => 'required|integer',
            'customer_name'    => 'required|string|max:255',
            'shipping_address' => 'required|string',
            'payment_method'   => 'required|in:cash,credit_card,gcash,paymaya,cod',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.book_id'  => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $stockErrors   = [];
        $resolvedItems = [];

        foreach ($request->items as $item) {
            try {
                $stock = $this->inventory->checkStock($item['book_id']);
            } catch (\RuntimeException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
            }

            if ($stock['stock_qty'] < $item['quantity']) {
                $stockErrors[] = "Book #{$item['book_id']} ({$stock['title']}): "
                    . "requested {$item['quantity']}, available {$stock['stock_qty']}.";
            }

            $resolvedItems[] = [
                'book_id'    => $item['book_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $stock['price'],
                'subtotal'   => $stock['price'] * $item['quantity'],
            ];
        }

        if (! empty($stockErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock for one or more items.',
                'errors'  => $stockErrors,
            ], 409);
        }

        $order = DB::transaction(function () use ($request, $resolvedItems) {
            $total = collect($resolvedItems)->sum('subtotal');

            $order = Order::create([
                'customer_id'      => $request->customer_id,
                'customer_name'    => $request->customer_name,
                'shipping_address' => $request->shipping_address,
                'payment_method'   => $request->payment_method,
                'notes'            => $request->notes,
                'total_amount'     => $total,
                'status'           => 'pending',
            ]);

            foreach ($resolvedItems as $item) {
                $order->items()->create($item);
            }

            return $order->load('items');
        });

        $deductedItems = [];
        foreach ($resolvedItems as $item) {
            if ($this->inventory->deductStock($item['book_id'], $item['quantity'])) {
                $deductedItems[] = $item;
            } else {
                foreach ($deductedItems as $deducted) {
                    $this->inventory->restoreStock($deducted['book_id'], $deducted['quantity']);
                }
                $order->update(['status' => 'cancelled']);
                return response()->json([
                    'success' => false,
                    'message' => "Stock deduction failed for book #{$item['book_id']}. Order cancelled.",
                ], 409);
            }
        }

        $this->finance->publishOrderCreated($order->toArray());
        $this->customers->recordOrder($order->customer_id, (float) $order->total_amount);

        return response()->json(['success' => true, 'data' => $order], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Order::with('items')->findOrFail($id)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = Order::with('items')->findOrFail($id);

        if (! in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => "Cannot cancel an order with status '{$order->status}'.",
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        foreach ($order->items as $item) {
            $this->inventory->restoreStock($item->book_id, $item->quantity);
        }

        return response()->json(['success' => true, 'message' => 'Order cancelled and stock restored.']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function byCustomer(int $customerId): JsonResponse
    {
        $orders = Order::with('items')->where('customer_id', $customerId)->latest()->get();

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function byStatus(string $status): JsonResponse
    {
        $orders = Order::with('items')->where('status', $status)->latest()->paginate(20);

        return response()->json(['success' => true, 'data' => $orders]);
    }
}
