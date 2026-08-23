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
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            // No inline constrained() here on purpose: `farmers` is created
            // by a later migration (it now depends on `cooperatives`, which
            // itself must run after this one) — the FK is added there
            // instead, once both tables exist. See create_farmers_table.
            $table->foreignId('farmer_id');
            $table->string('name');
            $table->string('farm_code', 50)->nullable()->unique();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('county')->nullable();
            $table->string('subcounty')->nullable();
            $table->string('parish')->nullable();
            $table->string('village')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('elevation', 8, 2)->nullable();
            $table->decimal('total_area', 10, 2)->nullable();
            $table->decimal('coffee_area', 10, 2)->nullable();
            $table->string('coffee_type', 100)->nullable();
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};
