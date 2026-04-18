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
        if (Schema::hasColumn('harvests', 'harvest_season')) {
            return;
        }

        Schema::table('harvests', function (Blueprint $table): void {
            $table->string('harvest_season')->nullable()->after('harvest_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('harvests', 'harvest_season')) {
            return;
        }

        Schema::table('harvests', function (Blueprint $table): void {
            $table->dropColumn('harvest_season');
        });
    }
};
