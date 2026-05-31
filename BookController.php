<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    // ─── GET /books ────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = Book::query();

        if ($request->filled('genre')) {
            $query->where('genre', $request->genre);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('title',  'like', "%$q%")
                   ->orWhere('author', 'like', "%$q%")
                   ->orWhere('isbn',   'like', "%$q%");
            });
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_qty', '<=', 'reorder_level');
        }

        $books = $query->orderBy('title')->paginate(
            perPage: $request->input('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'data'    => $books,
        ]);
    }

    // ─── POST /books ───────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'isbn'          => 'required|string|unique:books,isbn',
            'title'         => 'required|string|max:255',
            'author'        => 'required|string|max:255',
            'genre'         => 'required|string|max:100',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
            'stock_qty'     => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $book = Book::create($v->validated());

        return response()->json(['success' => true, 'data' => $book], 201);
    }

    // ─── GET /books/{id} ───────────────────────────────────────────

    public function show(int $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        return response()->json(['success' => true, 'data' => $book]);
    }

    // ─── PUT /books/{id} ───────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $v = Validator::make($request->all(), [
            'isbn'          => 'sometimes|string|unique:books,isbn,' . $book->id,
            'title'         => 'sometimes|string|max:255',
            'author'        => 'sometimes|string|max:255',
            'genre'         => 'sometimes|string|max:100',
            'description'   => 'nullable|string',
            'price'         => 'sometimes|numeric|min:0',
            'cost_price'    => 'sometimes|numeric|min:0',
            'stock_qty'     => 'sometimes|integer|min:0',
            'reorder_level' => 'sometimes|integer|min:0',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $book->update($v->validated());

        return response()->json(['success' => true, 'data' => $book]);
    }

    // ─── DELETE /books/{id} ────────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        Book::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Book deleted.']);
    }

    // ─── GET /books/{id}/stock ─────────────────────────────────────
    // Called by Order Service before placing an order

    public function checkStock(int $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        return response()->json([
            'success'   => true,
            'data'      => [
                'book_id'       => $book->id,
                'title'         => $book->title,
                'stock_qty'     => $book->stock_qty,
                'reorder_level' => $book->reorder_level,
                'available'     => $book->stock_qty > 0,
                'price'         => $book->price,
            ],
        ]);
    }

    // ─── PATCH /books/{id}/stock/deduct ───────────────────────────
    // Called by Order Service after a successful order is created

    public function deductStock(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $book = Book::findOrFail($id);

        if ($book->stock_qty < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock. Available: {$book->stock_qty}",
            ], 409);
        }

        $book->decrement('stock_qty', $request->quantity);

        return response()->json([
            'success'   => true,
            'message'   => 'Stock deducted.',
            'remaining' => $book->fresh()->stock_qty,
        ]);
    }

    // ─── PATCH /books/{id}/stock/restore ──────────────────────────
    // Called when an order is cancelled

    public function restoreStock(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $book = Book::findOrFail($id);
        $book->increment('stock_qty', $request->quantity);

        return response()->json([
            'success'   => true,
            'message'   => 'Stock restored.',
            'remaining' => $book->fresh()->stock_qty,
        ]);
    }

    // ─── GET /stock/alerts ─────────────────────────────────────────

    public function lowStockAlerts(): JsonResponse
    {
        $books = Book::whereColumn('stock_qty', '<=', 'reorder_level')
                     ->orderBy('stock_qty')
                     ->get();

        return response()->json([
            'success' => true,
            'count'   => $books->count(),
            'data'    => $books,
        ]);
    }
}
