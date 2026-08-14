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

        DB::statement(
            'UPDATE cart_items INNER JOIN markets ON markets.id = cart_items.market_id
             SET cart_items.unit_price = markets.price_per_kg
             WHERE cart_items.unit_price IS NULL'
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
