<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Denormalized 3-way pivot across lots, batches, and farm_collections —
     * populated whenever a batch is linked to a lot (see
     * LotService::attachBatch()), by copying that batch's own
     * batch_farm_collection links. Exists purely to let pages that need a
     * lot's full custody chain (lot -> batch -> farm collection) read one
     * table instead of joining lot_batch + batch_farm_collection every
     * time.
     */
    public function up(): void
    {
        Schema::create('lot_batch_farm_collection', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->constrained('lots')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('farm_collection_id')->constrained('farm_collections')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['lot_id', 'batch_id', 'farm_collection_id'], 'lot_batch_farm_collection_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_batch_farm_collection');
    }
};
