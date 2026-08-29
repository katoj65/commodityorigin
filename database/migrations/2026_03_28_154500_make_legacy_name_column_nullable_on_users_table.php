<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NULL');
        } else {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('name')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'name')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET name = COALESCE(NULLIF(TRIM(CONCAT(first_name, ' ', last_name)), ''), email) WHERE name IS NULL");
            DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NOT NULL');
        } else {
            DB::statement("UPDATE users SET name = COALESCE(NULLIF(TRIM(first_name || ' ' || last_name), ''), email) WHERE name IS NULL");
            Schema::table('users', function (Blueprint $table): void {
                $table->string('name')->nullable(false)->change();
            });
        }
    }
};
