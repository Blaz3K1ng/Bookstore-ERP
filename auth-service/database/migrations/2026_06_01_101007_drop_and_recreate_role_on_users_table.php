<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS role CASCADE');
        DB::statement("ALTER TABLE users ADD COLUMN role VARCHAR(255) DEFAULT 'customer' NOT NULL");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS role CASCADE');
        DB::statement("ALTER TABLE users ADD COLUMN role VARCHAR(255) DEFAULT 'customer' NOT NULL");
    }
};
