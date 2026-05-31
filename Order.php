<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'shipping_address',
        'payment_method',
        'notes',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'customer_id'  => 'integer',
    ];

    // Status flow: pending → processing → shipped → delivered
    //              pending/processing → cancelled
    const STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}
