<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn')->unique();
            $table->string('title');
            $table->string('author');
            $table->string('genre', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->unsignedInteger('reorder_level')->default(10);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['genre']);
            $table->index(['stock_qty', 'reorder_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
