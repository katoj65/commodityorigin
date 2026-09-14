<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the farmers_farms pivot table. Farm ownership moved onto
     * real user accounts via user_farm_ownership; this table was left
     * empty and its Farm::farmers()/Farmer::farms() relations were
     * never wired into any farm-creation flow (AddFarmModal posts to
     * farm.store, which creates a user_farm_ownership record instead).
     */
    public function up(): void
    {
        Schema::dropIfExists('farmers_farms');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('farmers_farms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->string('farm_code')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }
};
