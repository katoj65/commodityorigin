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
        Schema::table('batches', function (Blueprint $table): void {
            if (! Schema::hasColumn('batches', 'variety')) {
                $table->string('variety')->nullable()->after('batch_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table): void {
            if (Schema::hasColumn('batches', 'variety')) {
                $table->dropColumn('variety');
            }
        });
    }
};
