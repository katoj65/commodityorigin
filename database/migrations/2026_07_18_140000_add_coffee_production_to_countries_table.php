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
        Schema::table('countries', function (Blueprint $table): void {
            $table->boolean('is_coffee_producer')->default(false)->after('currency_name');
            $table->unsignedBigInteger('coffee_production_bags')->nullable()->after('is_coffee_producer');

            $table->index('is_coffee_producer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            $table->dropIndex(['is_coffee_producer']);
            $table->dropColumn(['is_coffee_producer', 'coffee_production_bags']);
        });
    }
};
