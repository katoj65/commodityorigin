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
        Schema::table('farm_owners', function (Blueprint $table): void {
            $table->string('first_name')->after('user_id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->after('middle_name');
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_owners', function (Blueprint $table): void {
            $table->string('name')->after('user_id');
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }
};
