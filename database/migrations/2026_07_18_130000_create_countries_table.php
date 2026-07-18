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
        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->char('iso2', 2)->unique();
            $table->char('iso3', 3)->unique();
            $table->string('phone_code', 8)->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_name')->nullable();
            $table->timestamps();

            $table->index('region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
