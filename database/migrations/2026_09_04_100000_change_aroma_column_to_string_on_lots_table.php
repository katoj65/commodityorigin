<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `lots.aroma` moves from a 0-10 numeric cupping score to a slug
     * referencing aroma_metadata — same change already made to
     * `lots.flavor` / `body` / `acidity` / `aftertaste`. `balance` stays
     * the last numeric SCA score.
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->string('aroma')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->decimal('aroma', 4, 2)->nullable()->change();
        });
    }
};
