<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds an auto-generated, unique collection_code (e.g. COL-2026-AB12CD),
     * mirroring farms.farm_code / batches.batch_number. Backfills existing
     * rows here since new inserts always populate it via
     * FarmCollectionService::generateCollectionCode().
     */
    public function up(): void
    {
        Schema::table('farm_collections', function (Blueprint $table): void {
            $table->string('collection_code', 50)->nullable()->unique()->after('id');
        });

        $collections = DB::table('farm_collections')->whereNull('collection_code')->get(['id', 'created_at']);

        foreach ($collections as $collection) {
            do {
                $year = $collection->created_at ? \Illuminate\Support\Carbon::parse($collection->created_at)->year : now()->year;
                $code = sprintf('COL-%d-%s', $year, strtoupper(Str::random(6)));
            } while (DB::table('farm_collections')->where('collection_code', $code)->exists());

            DB::table('farm_collections')->where('id', $collection->id)->update(['collection_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_collections', function (Blueprint $table): void {
            $table->dropColumn('collection_code');
        });
    }
};
