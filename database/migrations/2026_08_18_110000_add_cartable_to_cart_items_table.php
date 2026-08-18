<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Makes the cart polymorphic (any purchasable model, not just coffee
     * Market listings) by adding cartable_id/cartable_type, backfilled from
     * the existing market_id column. market_id itself is left in place —
     * unused by new code, but kept for reversibility instead of dropping it.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('cartable_id')->nullable()->after('market_id');
            $table->string('cartable_type')->nullable()->after('cartable_id');
        });

        DB::table('cart_items')->whereNull('cartable_id')->update([
            'cartable_id' => DB::raw('market_id'),
            'cartable_type' => \App\Models\Market::class,
        ]);

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->index(['cartable_id', 'cartable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropIndex(['cartable_id', 'cartable_type']);
            $table->dropColumn(['cartable_id', 'cartable_type']);
        });
    }
};
