<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `lots.body`, `lots.acidity`, and `lots.aftertaste` move from 0-10
     * numeric cupping scores to slugs referencing body_metadata /
     * acidity_metadata / aftertaste_metadata — same change already made
     * to `lots.flavor`. `aroma` and `balance` stay numeric SCA scores.
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->string('body')->nullable()->change();
            $table->string('acidity')->nullable()->change();
            $table->string('aftertaste')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->decimal('body', 4, 2)->nullable()->change();
            $table->decimal('acidity', 4, 2)->nullable()->change();
            $table->decimal('aftertaste', 4, 2)->nullable()->change();
        });
    }
};
