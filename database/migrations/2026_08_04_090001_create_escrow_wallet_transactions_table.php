<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrow_wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('escrow_wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['funded', 'spent', 'refunded']);
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('balance_after', 14, 2);
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['escrow_wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrow_wallet_transactions');
    }
};
