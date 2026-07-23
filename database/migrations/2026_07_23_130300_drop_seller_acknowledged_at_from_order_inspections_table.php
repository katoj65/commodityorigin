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
        Schema::table('order_inspections', function (Blueprint $table): void {
            $table->dropColumn('seller_acknowledged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_inspections', function (Blueprint $table): void {
            $table->timestamp('seller_acknowledged_at')->nullable()->after('buyer_acknowledged_at');
        });
    }
};
