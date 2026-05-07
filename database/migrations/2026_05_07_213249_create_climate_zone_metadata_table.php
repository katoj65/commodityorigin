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
        Schema::create('climate_zone_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('altitude_min')->nullable()->comment('metres ASL');
            $table->unsignedSmallInteger('altitude_max')->nullable()->comment('metres ASL');
            $table->string('rainfall_range', 100)->nullable()->comment('e.g. 1200–1800 mm');
            $table->string('temperature_range', 100)->nullable()->comment('e.g. 16–24 °C');
            $table->string('humidity_range', 100)->nullable()->comment('e.g. 60–80 %');
            $table->enum('coffee_suitability', ['High', 'Medium', 'Low'])->default('Medium');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('climate_zone_metadata');
    }
};
