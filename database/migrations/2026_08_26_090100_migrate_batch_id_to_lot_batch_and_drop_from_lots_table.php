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
     * Every lot's existing batch_id link is carried over into the new
     * lot_batch pivot table (denormalizing the batch's batch_number, and
     * using the lot's own net_weight_kg as the allocation snapshot) before
     * the batch_id column is dropped from lots.
     */
    public function up(): void
    {
        DB::table('lots')
            ->whereNotNull('batch_id')
            ->join('batches', 'batches.id', '=', 'lots.batch_id')
            ->select('lots.id as lot_id', 'lots.batch_id', 'lots.user_id', 'lots.net_weight_kg', 'batches.batch_number')
            ->get()
            ->each(function ($row): void {
                DB::table('lot_batch')->insert([
                    'lot_id' => $row->lot_id,
                    'batch_id' => $row->batch_id,
                    'batch_number' => $row->batch_number,
                    'allocation_kg' => $row->net_weight_kg,
                    'user_id' => $row->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('lots', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->foreignId('batch_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('lot_batch')->orderBy('id')->get()->each(function ($row): void {
            DB::table('lots')->where('id', $row->lot_id)->update(['batch_id' => $row->batch_id]);
        });
    }
};
