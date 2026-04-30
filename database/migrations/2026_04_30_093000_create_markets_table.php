<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lot_code')->unique();
            $table->string('name')->nullable();
            $table->string('origin')->nullable();
            $table->string('type');
            $table->string('process');
            $table->decimal('quality_score', 5, 2)->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('price_per_kg', 10, 2);
            $table->string('demand')->nullable();
            $table->json('badges')->nullable();
            $table->string('target_market')->nullable();
            $table->string('status')->default('live');
            $table->text('notes')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
