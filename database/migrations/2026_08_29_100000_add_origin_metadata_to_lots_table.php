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
        Schema::table('lots', function (Blueprint $table): void {
            $table->string('variety')->nullable()->after('grade');
            $table->string('origin')->nullable()->after('variety');
            $table->string('region')->nullable()->after('origin');
            $table->unsignedSmallInteger('year_of_harvest')->nullable()->after('region');
            $table->decimal('moisture', 5, 2)->nullable()->after('year_of_harvest');
            $table->decimal('defects_percentage', 5, 2)->nullable()->after('moisture');
            $table->string('screen')->nullable()->after('defects_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->dropColumn([
                'variety',
                'origin',
                'region',
                'year_of_harvest',
                'moisture',
                'defects_percentage',
                'screen',
            ]);
        });
    }
};
