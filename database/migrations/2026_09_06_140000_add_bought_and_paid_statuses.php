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
        // doctrine/dbal isn't installed, so raw MODIFY statements are used
        // instead of Blueprint::change() to widen the enums. SQLite has no
        // ENUM type — the columns are TEXT there, so nothing to widen.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE offers MODIFY status ENUM('open', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'bought') NOT NULL DEFAULT 'open'");
            DB::statement("ALTER TABLE offer_responses MODIFY status ENUM('pending', 'accepted', 'declined', 'paid') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE offers MODIFY status ENUM('open', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'open'");
            DB::statement("ALTER TABLE offer_responses MODIFY status ENUM('pending', 'accepted', 'declined') NOT NULL DEFAULT 'pending'");
        }
    }
};