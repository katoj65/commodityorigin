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
        Schema::create('cooperatives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->nullable()->unique();
            $table->string('registration_number', 100)->nullable()->unique();
            $table->string('contact_person')->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('district')->nullable();
            $table->string('sub_county')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('status', 50)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooperatives');
    }
};
