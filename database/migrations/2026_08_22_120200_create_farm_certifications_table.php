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
        Schema::create('farm_certifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_metadata_id')->constrained('certification_metadata')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['farm_id', 'certification_metadata_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_certifications');
    }
};
