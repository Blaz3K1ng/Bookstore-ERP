<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'customer_id', 'customer_name',
        'amount', 'payment_method', 'status',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'order_id'    => 'integer',
        'customer_id' => 'integer',
    ];
}
