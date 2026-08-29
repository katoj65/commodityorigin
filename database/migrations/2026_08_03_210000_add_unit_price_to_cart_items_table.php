<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
        });

        // A correlated-subquery UPDATE (rather than UPDATE ... INNER JOIN,
        // which MySQL supports but SQLite doesn't) so this runs on both.
        DB::statement(
            'UPDATE cart_items
             SET unit_price = (SELECT markets.price_per_kg FROM markets WHERE markets.id = cart_items.market_id)
             WHERE unit_price IS NULL
             AND EXISTS (SELECT 1 FROM markets WHERE markets.id = cart_items.market_id)'
        );

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->decimal('unit_price', 10, 2)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropColumn('unit_price');
        });
    }
};
