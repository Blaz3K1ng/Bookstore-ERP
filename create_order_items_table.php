<?php
// database/migrations/2024_01_01_000002_create_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('book_id');   // FK to Inventory Service (cross-service, no constraint)
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal',   10, 2);
            $table->timestamps();

            $table->index(['order_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
