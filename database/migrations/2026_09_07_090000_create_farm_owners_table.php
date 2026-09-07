<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A farm's registered owner(s) — distinct from `farms.user_id` (the
     * platform account that registered/manages the farm) and from
     * `farmers` (the smallholders who deliver produce). Supports
     * co-ownership: a farm can have more than one owner, each with their
     * own share.
     */
    public function up(): void
    {
        Schema::create('farm_owners', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('national_id')->nullable();
            $table->string('tel')->nullable();
            $table->string('email')->nullable();
            $table->decimal('ownership_percentage', 5, 2)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_owners');
    }
};
