<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * An append-only activity log for a farm collection — one row per
     * lifecycle event (e.g. created, quality recorded, linked to a batch),
     * matching the timestamp/event/detail shape in UI.md's "Activity /
     * System Log" spec (see batch_activities for the same pattern).
     */
    public function up(): void
    {
        Schema::create('farm_collection_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_collection_id')->constrained('farm_collections')->cascadeOnDelete();
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
        Schema::dropIfExists('farm_collection_activities');
    }
};
