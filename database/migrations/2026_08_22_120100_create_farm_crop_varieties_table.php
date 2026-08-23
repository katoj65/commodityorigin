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
        Schema::create('farm_crop_varieties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crop_variety_metadata_id')->constrained('crop_variety_metadata')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['farm_id', 'crop_variety_metadata_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_crop_varieties');
    }
};
