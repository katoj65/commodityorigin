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
        Schema::create('batch_storages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->unique()->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('batch_storages');
    }
};
