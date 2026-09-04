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
        Schema::create('price_indexes', function (Blueprint $table): void {
            $table->id();
            $table->string('item')->unique();
            $table->decimal('current_price', 12, 4);
            $table->decimal('percentage_fluctuation', 8, 4)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_indexes');
    }
};
