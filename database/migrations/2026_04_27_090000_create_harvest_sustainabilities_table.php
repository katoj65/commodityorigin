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
        Schema::create('harvest_sustainabilities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('harvest_id')->constrained('harvests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('organic_certified')->default(false);
            $table->boolean('climate_smart')->default(false);
            $table->boolean('shade_grown')->default(false);
            $table->boolean('water_management')->default(false);
            $table->boolean('soil_conservation')->default(false);
            $table->boolean('low_carbon')->default(false);
            $table->boolean('fair_wages')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('harvest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_sustainabilities');
    }
};
