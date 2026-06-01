<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'isbn', 'title', 'author', 'genre', 'description',
        'price', 'cost_price', 'stock_qty', 'reorder_level',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'cost_price'    => 'decimal:2',
        'stock_qty'     => 'integer',
        'reorder_level' => 'integer',
    ];

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_qty <= $this->reorder_level;
    }

    public function getMarginAttribute(): float
    {
        return (float) $this->price - (float) $this->cost_price;
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_qty', '<=', 'reorder_level');
    }
}
