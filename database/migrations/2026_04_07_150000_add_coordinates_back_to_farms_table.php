<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            if (! Schema::hasColumn('farms', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('variety');
            }

            if (! Schema::hasColumn('farms', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            if (Schema::hasColumn('farms', 'longitude')) {
                $table->dropColumn('longitude');
            }

            if (Schema::hasColumn('farms', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
