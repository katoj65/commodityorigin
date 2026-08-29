<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * An append-only activity log for a batch — one row per lifecycle
     * event (e.g. created, quality recorded, farm collection linked, lot
     * created from it), matching the timestamp/event/detail shape in
     * UI.md's "Activity / System Log" spec.
     */
    public function up(): void
    {
        Schema::create('batch_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
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
        Schema::dropIfExists('batch_activities');
    }
};
