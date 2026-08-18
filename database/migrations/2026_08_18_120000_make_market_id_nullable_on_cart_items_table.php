<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * market_id is only meaningful for cart items pointing at a Market
     * listing — now that the cart is polymorphic (cartable_id/cartable_type),
     * an AgriculturalInput cart item has no market_id at all, so the
     * column can no longer be NOT NULL.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->foreignId('market_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->foreignId('market_id')->nullable(false)->change();
        });
    }
};
