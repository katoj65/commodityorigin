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
        if (Schema::hasColumn('lots', 'farm_id')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('farm_id');
            });
        }

        if (! Schema::hasColumn('lots', 'net_weight_kg')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->decimal('net_weight_kg', 10, 2)->nullable()->after('packaging_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('lots', 'net_weight_kg')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->dropColumn('net_weight_kg');
            });
        }

        if (! Schema::hasColumn('lots', 'farm_id')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->foreignId('farm_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            });
        }
    }
};
