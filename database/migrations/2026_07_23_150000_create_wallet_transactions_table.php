<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'escrow_hold', 'escrow_release']);
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('balance_after', 14, 2);
            $table->string('description')->nullable();
            $table->foreignId('counterparty_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->enum('status', ['completed', 'pending', 'failed'])->default('completed');
            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
