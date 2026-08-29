<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Controlled vocabulary of the activity types a lot_activities row's
     * `event` column can hold — every kind of lifecycle event that can be
     * recorded against a lot, analogous to batch_activity_metadata.
     */
    public function up(): void
    {
        Schema::create('lot_activity_metadata', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_activity_metadata');
    }
};
