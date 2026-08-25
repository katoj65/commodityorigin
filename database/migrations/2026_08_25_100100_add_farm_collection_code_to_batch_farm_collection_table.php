<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Denormalizes the linked farm_collections.collection_code onto each
     * pivot row, so a batch's linked collections are readable without a
     * join. No existing pivot rows to backfill — nothing has written to
     * this table yet (see feedback_inventory_creation_modals memory).
     */
    public function up(): void
    {
        Schema::table('batch_farm_collection', function (Blueprint $table): void {
            $table->string('farm_collection_code', 50)->nullable()->after('farm_collection_id');
        });

        DB::table('batch_farm_collection')
            ->join('farm_collections', 'farm_collections.id', '=', 'batch_farm_collection.farm_collection_id')
            ->update(['batch_farm_collection.farm_collection_code' => DB::raw('farm_collections.collection_code')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_farm_collection', function (Blueprint $table): void {
            $table->dropColumn('farm_collection_code');
        });
    }
};
