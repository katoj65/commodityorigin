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
            $table->timestamp('buyer_acknowledged_at')->nullable()->after('status');
            $table->timestamp('seller_acknowledged_at')->nullable()->after('buyer_acknowledged_at');
            $table->foreignId('completed_by')->nullable()->after('seller_acknowledged_at')->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable()->after('completed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_inspections', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('completed_by');
            $table->dropColumn(['buyer_acknowledged_at', 'seller_acknowledged_at', 'completed_at']);
        });
    }
};
