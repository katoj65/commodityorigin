<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offer_responses', function (Blueprint $table): void {
            $table->foreignId('order_owner_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        // Backfill: the owner is the seller who posted the offer being
        // responded to. Portable (works on MySQL and SQLite).
        $rows = DB::table('offer_responses')
            ->join('offers', 'offers.id', '=', 'offer_responses.offer_id')
            ->select('offer_responses.id', 'offers.seller_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('offer_responses')
                ->where('id', $row->id)
                ->update(['order_owner_id' => $row->seller_id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_responses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('order_owner_id');
        });
    }
};