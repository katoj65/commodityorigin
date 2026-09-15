<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A lot's recorded sustainability practices (deforestation-free
     * sourcing, zero-water drying, fair producer wage, etc.), shown on the
     * lot's Sustainability & Compliance card. `practice` holds a slug from
     * sustainability_practices_metadata — same pattern as
     * farm_sustainability_practices and the batch/lot/farm-collection
     * activity logs' `event` column.
     */
    public function up(): void
    {
        Schema::create('lot_sustainability', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('practice');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_sustainability');
    }
};
