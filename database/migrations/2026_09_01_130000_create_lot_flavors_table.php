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
        Schema::create('lot_flavors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flavor_metadata_id')->constrained('flavor_metadata')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lot_id', 'flavor_metadata_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_flavors');
    }
};
