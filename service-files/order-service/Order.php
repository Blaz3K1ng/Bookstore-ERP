<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'customer_name', 'shipping_address',
        'payment_method', 'notes', 'total_amount', 'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'customer_id'  => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
