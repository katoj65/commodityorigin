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
        Schema::table('markets', function (Blueprint $table): void {
            $table->foreignId('blockchain_id')->nullable()->after('lot_id')->constrained('blockchains')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('blockchain_id');
        });
    }
};
