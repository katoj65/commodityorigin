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
     * Farms are now owned directly by the user who registered them —
     * `farmer_id` (the farmer-profile relation) and `created_by_user_id`
     * are both replaced by a single `user_id` column.
     */
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
        });

        DB::statement('UPDATE farms SET user_id = created_by_user_id WHERE user_id IS NULL AND created_by_user_id IS NOT NULL');
        DB::statement('UPDATE farms JOIN farmers ON farmers.id = farms.farmer_id SET farms.user_id = farmers.user_id WHERE farms.user_id IS NULL AND farmers.user_id IS NOT NULL');

        Schema::table('farms', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('farms', function (Blueprint $table): void {
            $table->dropForeign(['farmer_id']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn(['farmer_id', 'created_by_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Restores the farmer_id/created_by_user_id columns and their foreign
     * keys, but cannot losslessly reconstruct which farmer each farm
     * belonged to (that relation is gone) — both are left null on
     * rollback, matching this repo's precedent for schema-simplifying
     * migrations that don't fully reconstruct on down() (see
     * 2026_08_27_100000_drop_harvest_tables.php).
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table): void {
            $table->foreignId('farmer_id')->nullable()->after('id')->constrained('farmers')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->after('farmer_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('farms', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
