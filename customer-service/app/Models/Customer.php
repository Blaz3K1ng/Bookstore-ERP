<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'city', 'address',
        'notes', 'status', 'total_orders', 'lifetime_value',
    ];

    protected $casts = [
        'total_orders'   => 'integer',
        'lifetime_value' => 'decimal:2',
    ];
}
