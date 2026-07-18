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
        Schema::create('exchange_rates', function (Blueprint $table): void {
            $table->id();
            $table->string('base_currency', 8);
            $table->string('quote_currency', 8);
            $table->decimal('rate', 18, 6);
            $table->decimal('daily_change_percent', 8, 4)->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->unique(['base_currency', 'quote_currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
