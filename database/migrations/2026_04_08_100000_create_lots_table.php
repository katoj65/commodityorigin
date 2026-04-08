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
        Schema::create('lots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('lot_number')->unique();
            $table->string('process');
            $table->string('grade');
            $table->unsignedInteger('quantity_bags');
            $table->decimal('bag_weight_kg', 10, 2);
            $table->decimal('reserve_price', 12, 2)->nullable();
            $table->decimal('quality_score', 5, 2)->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
