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
        Schema::table('farms', function (Blueprint $table) {
            $table->unsignedInteger('total_bags_produced')->nullable()->after('notes');
            $table->string('temperature', 100)->nullable()->after('total_bags_produced');
            $table->string('rainfall', 100)->nullable()->after('temperature');
            $table->string('humidity', 100)->nullable()->after('rainfall');
            $table->string('soil_type', 150)->nullable()->after('humidity');
            $table->string('climatic_zone', 150)->nullable()->after('soil_type');
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropColumn([
                'total_bags_produced',
                'temperature',
                'rainfall',
                'humidity',
                'soil_type',
                'climatic_zone',
            ]);
        });
    }
};
