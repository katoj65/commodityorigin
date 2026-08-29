<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One row per lot committed to the traceability chain — replaces the
     * old BlockchainService stub (which computed fake block details on the
     * fly, without persisting anything) with a real ledger. `block_number`
     * and `previous_hash` chain sequentially across lots in commit order.
     */
    public function up(): void
    {
        Schema::create('blockchains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->unique()->constrained('lots')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('network')->default('Bean Origin Traceability Chain');
            $table->unsignedBigInteger('block_number')->unique();
            $table->string('hash')->unique();
            $table->string('previous_hash')->nullable();
            $table->string('status')->default('confirmed');
            $table->unsignedInteger('confirmations')->default(0);
            $table->timestamp('committed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchains');
    }
};
