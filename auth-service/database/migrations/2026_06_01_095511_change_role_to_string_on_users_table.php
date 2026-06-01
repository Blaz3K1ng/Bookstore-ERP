<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Using raw SQL because PostgreSQL ENUM types are complex to alter natively in Laravel
        DB::statement('ALTER TABLE users ALTER COLUMN role DROP DEFAULT');
        DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255) USING role::text');
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't reverse this as it would require recreating the ENUM type and data loss
        DB::statement('ALTER TABLE users ALTER COLUMN role DROP DEFAULT');
    }
};
