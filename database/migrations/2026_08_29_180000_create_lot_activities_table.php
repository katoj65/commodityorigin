<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * An append-only activity log for a lot — one row per lifecycle event
     * (e.g. created, batch linked, published, committed to the blockchain),
     * matching the timestamp/event/detail shape in UI.md's "Activity /
     * System Log" spec (see batch_activities for the same pattern).
     */
    public function up(): void
    {
        Schema::create('lot_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->constrained('lots')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_activities');
    }
};
