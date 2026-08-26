<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Permanently retires the Harvest feature. Batches now resolve their
     * source farm via the newer batch_farm_collection -> farm_collections
     * -> farms chain instead of harvest ownership records, and Season no
     * longer tracks harvest aggregates.
     */
    public function up(): void
    {
        // owner_type/owner_id has no foreign key, so nothing else cleans
        // up dangling Harvest-typed ownership rows before the table drops.
        DB::table('batch_ownerships')->where('owner_type', 'App\\Models\\Harvest')->delete();

        Schema::dropIfExists('harvest_documents');
        Schema::dropIfExists('harvest_sustainabilities');
        Schema::dropIfExists('harvesting_metadata');
        Schema::dropIfExists('harvests');
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally a no-op. Unlike this repo's other "drop a column"
     * migrations, which reconstruct the column in down(), this migration
     * is a deliberate, permanent removal of the Harvest feature. Rebuilding
     * the exact historical schema of four tables across roughly a dozen
     * incremental migrations isn't worth the risk here — if the feature
     * needs to come back, restore it from version control instead.
     */
    public function down(): void
    {
        // Intentional no-op — see docblock above.
    }
};
