<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY type ENUM('deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'escrow_hold', 'escrow_release', 'escrow_fund') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY type ENUM('deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'escrow_hold', 'escrow_release') NOT NULL");
    }
};
