<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Retires the batch compliance and batch ownership features. Ownership
     * was already dead code — nothing created ownership records outside of
     * tests — and compliance's only write path (BatchController::
     * storeCompliance) was never reachable from the UI (its modal was never
     * mounted), so removing both loses no live data.
     */
    public function up(): void
    {
        Schema::dropIfExists('batch_compliances');
        Schema::dropIfExists('batch_ownerships');
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally a no-op — deliberate, permanent removal, matching this
     * repo's precedent for retired features (see
     * 2026_08_27_100000_drop_harvest_tables.php). Restore from version
     * control if either feature needs to come back.
     */
    public function down(): void
    {
        // Intentional no-op — see docblock above.
    }
};
