<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A single bonded-warehousing/storage record per item, shared across
     * batches, farm collections, and lots via a polymorphic `item`
     * relation (item_type + item_id) — backs each profile page's
     * "Bonded Warehousing & Storage" card without needing a separate
     * table per entity. `item_type`/`item_id` are unique together, so an
     * item has at most one warehousing record.
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('item_type');
            $table->unique(['item_type', 'item_id']);
            $table->string('storage_bay')->nullable();
            $table->date('date_stored')->nullable();
            $table->decimal('quantity_stored_kg', 10, 2)->nullable();
            $table->string('climate_ambient')->nullable();
            $table->string('physical_pallet')->nullable();
            $table->string('packaging_spec')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
