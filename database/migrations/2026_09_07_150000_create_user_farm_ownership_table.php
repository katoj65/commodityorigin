<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Links a farm to the real user account(s) that own it — replaces the
     * earlier `farm_owners` table, which stored owner details as plain
     * columns instead of pointing at a genuine `users` row. Supports
     * co-ownership: a farm can have more than one owner, each with a share.
     */
    public function up(): void
    {
        Schema::create('user_farm_ownership', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('ownership_percentage', 5, 2)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['farm_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_farm_ownership');
    }
};
