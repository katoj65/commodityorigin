<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks who recorded each collection, independent of who created the
     * farm — a collection is its own piece of content: the person who
     * recorded it may update/delete it regardless of who owns the farm.
     * Named user_id to match the same convention Batch/Lot already use.
     */
    public function up(): void
    {
        Schema::table('farm_collections', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('farm_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('farm_collections', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
