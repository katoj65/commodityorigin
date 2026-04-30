<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lot_storage_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('warehouse')->nullable();
            $table->string('storage_location')->nullable();
            $table->string('packaging_type')->nullable();
            $table->unsignedInteger('quantity_bags')->nullable();
            $table->decimal('bag_weight_kg', 10, 2)->nullable();
            $table->decimal('net_weight_kg', 10, 2)->nullable();
            $table->string('storage_condition')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lot_storage_profiles');
    }
};
