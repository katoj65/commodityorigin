<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // doctrine/dbal isn't installed, so a raw MODIFY statement is used
        // instead of Blueprint::change() to widen the enum. SQLite has no
        // ENUM type — the column is TEXT there, so nothing to widen.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('open', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'withdrawn') NOT NULL DEFAULT 'open'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('open', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'open'");
        }
    }
};
