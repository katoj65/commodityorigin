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
        if (! Schema::hasColumn('lots', 'batch_id')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->foreignId('batch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('lots', 'user_id')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->foreignId('user_id')->nullable()->after('batch_id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('lots', 'packaging_type')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->string('packaging_type')->nullable()->after('bag_weight_kg');
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
        if (Schema::hasColumn('lots', 'user_id')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasColumn('lots', 'batch_id')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('batch_id');
            });
        }

        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('lots', 'packaging_type') ? 'packaging_type' : null,
            Schema::hasColumn('lots', 'net_weight_kg') ? 'net_weight_kg' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table('lots', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
