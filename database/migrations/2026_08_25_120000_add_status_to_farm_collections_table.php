<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A lifecycle/usage status — distinct from the existing payment_status
     * (a financial status) — tracking whether a collection has been
     * consumed elsewhere in the pipeline (e.g. linked to a batch), so the
     * same collection can't be used more than once. Defaults every
     * existing row to 'pending' via the column default, no backfill loop
     * needed since there's nothing yet to derive a different value from.
     */
    public function up(): void
    {
        Schema::table('farm_collections', function (Blueprint $table): void {
            $table->string('status', 20)->default('pending')->after('collection_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_collections', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
