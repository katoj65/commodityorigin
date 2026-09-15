<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Display-ready sustainability/compliance checklist items for a lot
     * (e.g. "EUDR Deforestation-Free Pass" / "Satellite verified post-Dec
     * 2020 zero cut" / VERIFIED) — shown on the lot's Sustainability &
     * Compliance card. `status` is a free-form label (e.g. VERIFIED,
     * PASSED, AUDITED, PENDING); it may be set from a
     * sustainability_verification_metadata slug but isn't
     * foreign-keyed to it, matching this app's other status columns
     * (batch_farm_collection.status, lot_batch_farm_collection.status).
     */
    public function up(): void
    {
        Schema::create('lot_sustainability_verification', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->constrained('lots')->cascadeOnDelete();
            $table->string('item');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_sustainability_verification');
    }
};
