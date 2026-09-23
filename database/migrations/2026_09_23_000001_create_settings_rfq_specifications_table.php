<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings_rfq_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('target_price', 12, 2)->nullable();
            $table->string('destination')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('grade')->nullable();
            $table->string('type')->nullable();
            $table->decimal('min_weight', 12, 2)->nullable();
            $table->decimal('max_weight', 12, 2)->nullable();
            $table->string('incoterms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_rfq_specifications');
    }
};
