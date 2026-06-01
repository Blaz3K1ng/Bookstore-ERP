<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Services\InventoryRestockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    public function __construct(private InventoryRestockService $inventory) {}

    public function index(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::with('supplier')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'book_id'     => 'required|integer',
            'book_title'  => 'required|string|max:255',
            'quantity'    => 'required|integer|min:1',
            'unit_cost'   => 'required|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $data              = $v->validated();
        $data['total_cost'] = $data['unit_cost'] * $data['quantity'];
        $data['status']    = 'draft';

        $po = PurchaseOrder::create($data);

        return response()->json(['success' => true, 'data' => $po->load('supplier')], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => PurchaseOrder::with('supplier')->findOrFail($id)]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'status' => 'required|in:draft,ordered,received,cancelled',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $po        = PurchaseOrder::findOrFail($id);
        $oldStatus = $po->status;
        $newStatus = $request->status;

        if ($oldStatus === 'received') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change status of a received purchase order.',
            ], 422);
        }

        // When receiving a PO, restock inventory via inventory-service
        if ($newStatus === 'received' && $oldStatus !== 'received') {
            $restocked = $this->inventory->restockBook($po->book_id, $po->quantity);

            if (! $restocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to restock inventory. Inventory service may be unavailable.',
                ], 503);
            }

            $po->received_at = now();
        }

        $po->status = $newStatus;
        $po->save();

        return response()->json(['success' => true, 'data' => $po->load('supplier')]);
    }
}
