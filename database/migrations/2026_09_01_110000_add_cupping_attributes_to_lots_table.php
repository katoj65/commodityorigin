<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The SCA cupping-form attributes scored alongside quality_score —
     * each on the standard 0-10 scale, in 0.25-point increments.
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->decimal('acidity', 4, 2)->nullable()->after('quality_score');
            $table->decimal('body', 4, 2)->nullable()->after('acidity');
            $table->decimal('flavor', 4, 2)->nullable()->after('body');
            $table->decimal('aroma', 4, 2)->nullable()->after('flavor');
            $table->decimal('balance', 4, 2)->nullable()->after('aroma');
            $table->decimal('aftertaste', 4, 2)->nullable()->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->dropColumn(['acidity', 'body', 'flavor', 'aroma', 'balance', 'aftertaste']);
        });
    }
};
