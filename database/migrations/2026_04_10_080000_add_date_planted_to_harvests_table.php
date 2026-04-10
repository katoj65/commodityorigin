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
        if (Schema::hasColumn('harvests', 'date_planted')) {
            return;
        }

        Schema::table('harvests', function (Blueprint $table): void {
            $table->date('date_planted')->nullable()->after('variety');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('harvests', 'date_planted')) {
            return;
        }

        Schema::table('harvests', function (Blueprint $table): void {
            $table->dropColumn('date_planted');
        });
    }
};
