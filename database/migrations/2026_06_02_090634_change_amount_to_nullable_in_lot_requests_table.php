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
        Schema::table('lot_requests', function (Blueprint $table) {
            // Native column modification in modern Laravel
            $table->decimal('amount', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lot_requests', function (Blueprint $table) {
            // Forces the column back to its strict NOT NULL requirement
            // Note: Ensure your database doesn't have existing null values before rolling back!
            $table->decimal('amount', 12, 2)->nullable(false)->change();
        });
    }
};
