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
        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('type', ['request', 'offer'])->default('request')->after('order_number');
        });

        // buyer_id must become nullable so a seller can post an "offer" order
        // with no buyer assigned yet. doctrine/dbal isn't installed, so a raw
        // MODIFY statement is used instead of Blueprint::change().
        DB::statement('ALTER TABLE orders MODIFY buyer_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE orders MODIFY buyer_id BIGINT UNSIGNED NOT NULL');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }
};
