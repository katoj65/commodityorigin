<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. This table holds every detail about an item
     * listed in a store.
     */
    public function up(): void
    {
        Schema::create('store_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency_code', 10)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('unit', 30)->nullable();
            $table->string('image')->nullable();
            $table->string('status', 50)->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_items');
    }
};
