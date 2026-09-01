<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A farm's recorded sustainability practices (intercropping, organic
     * composting, shade-grown cultivation, water-efficient irrigation,
     * etc.), shown on the farm's Sustainability Metrics card. `practice`
     * holds a slug from sustainability_practices_metadata — same pattern
     * as batch/lot/farm-collection activity logs' `event` column.
     */
    public function up(): void
    {
        Schema::create('farm_sustainability_practices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('farm_sustainability_practices');
    }
};
