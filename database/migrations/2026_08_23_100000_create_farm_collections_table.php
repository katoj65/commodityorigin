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
        Schema::create('farm_collections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->date('collection_date');
            $table->string('coffee_type');
            $table->string('variety')->nullable();
            $table->string('harvest_season')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 20)->default('kg');
            $table->decimal('initial_moisture', 5, 2)->nullable();
            $table->decimal('initial_defects', 5, 2)->nullable();
            $table->string('initial_grade')->nullable();
            $table->decimal('initial_quality_score', 5, 2)->nullable();
            $table->decimal('collection_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('payment_status')->default('pending');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'collection_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_collections');
    }
};
