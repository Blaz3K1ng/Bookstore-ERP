<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::query()
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn ($q2) =>
                    $q2->where('name', 'like', "%$s%")
                       ->orWhere('contact_email', 'like', "%$s%")
                );
            })
            ->orderBy('name')
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $suppliers]);
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'contact_name'  => 'nullable|string|max:255',
            'contact_email' => 'required|email|unique:suppliers,contact_email',
            'phone'         => 'required|string|max:30',
            'address'       => 'nullable|string',
            'notes'         => 'nullable|string',
            'status'        => 'sometimes|in:active,inactive',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $supplier = Supplier::create($v->validated());

        return response()->json(['success' => true, 'data' => $supplier], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Supplier::findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:255',
            'contact_name'  => 'nullable|string|max:255',
            'contact_email' => 'sometimes|email|unique:suppliers,contact_email,' . $supplier->id,
            'phone'         => 'sometimes|string|max:30',
            'address'       => 'nullable|string',
            'notes'         => 'nullable|string',
            'status'        => 'sometimes|in:active,inactive',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $supplier->update($v->validated());

        return response()->json(['success' => true, 'data' => $supplier]);
    }

    public function destroy(int $id): JsonResponse
    {
        Supplier::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Supplier deleted.']);
    }
}
