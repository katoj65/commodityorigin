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
        Schema::create('farmers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cooperative_id')->nullable()->constrained('cooperatives')->nullOnDelete();
            $table->string('farmer_number', 50)->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('tel', 50);
            $table->string('email')->nullable()->index();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('district');
            $table->string('county')->nullable();
            $table->string('subcounty')->nullable();
            $table->string('parish')->nullable();
            $table->string('village')->nullable();
            $table->string('national_id', 50)->nullable()->unique();
            $table->string('status', 30)->default('active');
            $table->string('verification_status', 30)->default('pending');
            $table->timestamps();
        });

        // farms.farmer_id is declared as a plain column in create_farms_table
        // (which runs before this migration) precisely so the FK can be
        // added here, once `farmers` actually exists.
        Schema::table('farms', function (Blueprint $table): void {
            $table->foreign('farmer_id')->references('id')->on('farmers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table): void {
            $table->dropForeign(['farmer_id']);
        });

        Schema::dropIfExists('farmers');
    }
};
