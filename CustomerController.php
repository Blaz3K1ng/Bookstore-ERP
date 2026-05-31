<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn ($q2) =>
                    $q2->where('name',  'like', "%$s%")
                       ->orWhere('email', 'like', "%$s%")
                       ->orWhere('city',  'like', "%$s%")
                );
            })
            ->orderBy('name')
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $customers]);
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:customers,email',
            'phone'   => 'required|string|max:30',
            'city'    => 'required|string|max:100',
            'address' => 'nullable|string',
            'notes'   => 'nullable|string',
            'status'  => 'sometimes|in:active,vip,new,inactive',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $customer = Customer::create($v->validated());

        return response()->json(['success' => true, 'data' => $customer], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Customer::findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'    => 'sometimes|string|max:255',
            'email'   => 'sometimes|email|unique:customers,email,' . $customer->id,
            'phone'   => 'sometimes|string|max:30',
            'city'    => 'sometimes|string|max:100',
            'address' => 'nullable|string',
            'notes'   => 'nullable|string',
            'status'  => 'sometimes|in:active,vip,new,inactive',
            'total_orders'   => 'sometimes|integer',
            'lifetime_value' => 'sometimes|numeric',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $customer->update($v->validated());

        return response()->json(['success' => true, 'data' => $customer]);
    }

    public function destroy(int $id): JsonResponse
    {
        Customer::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Customer deleted.']);
    }

    /**
     * Lightweight summary used by Order Service UI to display customer history.
     * In production, this could aggregate from an event store.
     */
    public function ordersSummary(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'customer_id'    => $customer->id,
                'name'           => $customer->name,
                'total_orders'   => $customer->total_orders,
                'lifetime_value' => $customer->lifetime_value,
                'status'         => $customer->status,
            ],
        ]);
    }
}
