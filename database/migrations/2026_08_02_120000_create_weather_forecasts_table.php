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
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('region');
            $table->date('forecast_date');
            $table->enum('condition', ['Sunny', 'Partly Cloudy', 'Cloudy', 'Rainy', 'Thunderstorms'])->default('Sunny');
            $table->decimal('temperature_min', 4, 1)->comment('Degrees Celsius');
            $table->decimal('temperature_max', 4, 1)->comment('Degrees Celsius');
            $table->decimal('rainfall_mm', 5, 1)->nullable();
            $table->unsignedTinyInteger('humidity_percentage')->nullable();
            $table->decimal('wind_speed_kmh', 4, 1)->nullable();
            $table->text('advisory')->nullable()->comment('Short farming tip tied to these conditions');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['region', 'forecast_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_forecasts');
    }
};
