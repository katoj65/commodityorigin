<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `lots.flavor` moves from a 0-10 numeric cupping score to a slug
     * referencing flavor_metadata — the "Flavor" field is now a single
     * dropdown pick, same lookup table as the "Flavor Notes" tags.
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->string('flavor')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->decimal('flavor', 4, 2)->nullable()->change();
        });
    }
};
