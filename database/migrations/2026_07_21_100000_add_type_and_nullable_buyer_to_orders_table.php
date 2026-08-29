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
        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('type', ['request', 'offer'])->default('request')->after('order_number');
        });

        // buyer_id must become nullable so a seller can post an "offer" order
        // with no buyer assigned yet. doctrine/dbal isn't installed, so MySQL
        // uses a raw MODIFY statement instead of Blueprint::change(); the
        // SQLite test database doesn't understand MODIFY, so it goes through
        // Schema::table(), which Laravel can do natively (table rebuild)
        // without doctrine/dbal.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY buyer_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('orders', function (Blueprint $table): void {
                $table->unsignedBigInteger('buyer_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY buyer_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('orders', function (Blueprint $table): void {
                $table->unsignedBigInteger('buyer_id')->nullable(false)->change();
            });
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }
};
