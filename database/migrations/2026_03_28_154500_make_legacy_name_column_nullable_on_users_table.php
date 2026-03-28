<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'name')) {
            return;
        }

        DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'name')) {
            return;
        }

        DB::statement("UPDATE users SET name = COALESCE(NULLIF(TRIM(CONCAT(first_name, ' ', last_name)), ''), email) WHERE name IS NULL");
        DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NOT NULL');
    }
};
