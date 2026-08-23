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
        Schema::table('farms', function (Blueprint $table): void {
            $table->string('tel', 30)->nullable()->after('coffee_type');
            $table->string('email')->nullable()->after('tel');
            $table->string('soil_type', 150)->nullable()->after('soil_health_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table): void {
            $table->dropColumn(['tel', 'email', 'soil_type']);
        });
    }
};
