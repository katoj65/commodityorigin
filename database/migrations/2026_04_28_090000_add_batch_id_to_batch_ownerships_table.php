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
        if (Schema::hasColumn('batch_ownerships', 'batch_id')) {
            return;
        }

        Schema::table('batch_ownerships', function (Blueprint $table): void {
            $table->foreignId('batch_id')
                ->nullable()
                ->after('id')
                ->constrained('batches')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('batch_ownerships', 'batch_id')) {
            return;
        }

        Schema::table('batch_ownerships', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('batch_id');
        });
    }
};
