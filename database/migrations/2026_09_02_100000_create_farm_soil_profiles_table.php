<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A farm's recorded soil profile entries — one row per assessment,
     * not one per farm, so a farm can build a history of readings over
     * time (same shape as farm_sustainability_practices). `item` holds
     * a slug from soil_profile_metadata.
     */
    public function up(): void
    {
        Schema::create('farm_soil_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('item');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_soil_profiles');
    }
};
