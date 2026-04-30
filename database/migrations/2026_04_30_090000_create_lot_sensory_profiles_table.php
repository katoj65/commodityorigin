<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lot_sensory_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete()->unique();
            $table->decimal('aroma_score', 5, 2)->nullable();
            $table->decimal('acidity_score', 5, 2)->nullable();
            $table->decimal('body_score', 5, 2)->nullable();
            $table->decimal('aftertaste_score', 5, 2)->nullable();
            $table->string('flavor_notes')->nullable();
            $table->string('fragrance_notes')->nullable();
            $table->text('cupping_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lot_sensory_profiles');
    }
};
