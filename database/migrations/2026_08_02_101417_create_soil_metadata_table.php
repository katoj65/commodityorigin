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
        Schema::create('soil_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('ph_range', 50)->nullable()->comment('e.g. 5.5–6.5');
            $table->string('drainage', 100)->nullable()->comment('e.g. Well-drained');
            $table->enum('fertility', ['High', 'Medium', 'Low'])->default('Medium');
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
        Schema::dropIfExists('soil_metadata');
    }
};
