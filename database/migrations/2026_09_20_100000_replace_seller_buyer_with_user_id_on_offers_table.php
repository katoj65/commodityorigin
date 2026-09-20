<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            $table->dropForeign(['seller_id']);
            $table->dropIndex(['seller_id', 'status']);
            $table->dropColumn('seller_id');

            $table->dropForeign(['buyer_id']);
            $table->dropIndex(['buyer_id', 'status']);
        });

        Schema::table('offers', function (Blueprint $table): void {
            $table->renameColumn('buyer_id', 'user_id');
        });

        Schema::table('offers', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('offers', function (Blueprint $table): void {
            $table->renameColumn('user_id', 'buyer_id');
        });

        Schema::table('offers', function (Blueprint $table): void {
            $table->foreignId('seller_id')->nullable()->after('offer_number')->constrained('users')->cascadeOnDelete();
            $table->foreign('buyer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['seller_id', 'status']);
            $table->index(['buyer_id', 'status']);
        });
    }
};
