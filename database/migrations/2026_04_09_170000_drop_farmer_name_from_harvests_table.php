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
        if (! Schema::hasColumn('harvests', 'farmer_name')) {
            return;
        }

        Schema::table('harvests', function (Blueprint $table): void {
            $table->dropColumn('farmer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('harvests', 'farmer_name')) {
            return;
        }

        Schema::table('harvests', function (Blueprint $table): void {
            $table->string('farmer_name')->nullable()->after('farm_id');
        });
    }
};
