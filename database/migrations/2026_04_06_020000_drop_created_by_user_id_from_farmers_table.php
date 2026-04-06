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
        if (! Schema::hasTable('farmers') || ! Schema::hasColumn('farmers', 'created_by_user_id')) {
            return;
        }

        Schema::table('farmers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('farmers') || Schema::hasColumn('farmers', 'created_by_user_id')) {
            return;
        }

        Schema::table('farmers', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }
};
