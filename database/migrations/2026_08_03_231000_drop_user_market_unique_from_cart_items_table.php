<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The unique(user_id, market_id) constraint didn't account for status —
     * once a lot moves to 'ordered', re-adding it to the cart later collides
     * with that historical row. De-duplication of *active* cart rows is
     * already enforced in CartService::addItem() at the application level.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            // user_id's foreign key relies on the unique index below as its
            // supporting index — give it a dedicated one first so MySQL
            // will let us drop the unique index.
            $table->index('user_id');
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'market_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->unique(['user_id', 'market_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
