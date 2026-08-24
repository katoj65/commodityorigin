<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reference list of harvest season labels (e.g. "Main Crop", "Fly
     * Crop") — same shape as the app's other *_metadata lookup tables
     * (harvesting_metadata, crop_variety_metadata, etc.).
     */
    public function up(): void
    {
        Schema::create('seasons_metadata', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons_metadata');
    }
};
