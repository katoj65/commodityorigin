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
        Schema::table('markets', function (Blueprint $table): void {
            $table->string('available_from')->nullable()->after('delivery_location');
            $table->string('delivery_method')->nullable()->after('available_from');
            $table->string('incoterm')->nullable()->after('delivery_method');
            $table->string('dispatch')->nullable()->after('incoterm');
            $table->string('transport_arrangement')->nullable()->after('dispatch');
            $table->string('insurance_arrangement')->nullable()->after('transport_arrangement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table): void {
            $table->dropColumn([
                'available_from',
                'delivery_method',
                'incoterm',
                'dispatch',
                'transport_arrangement',
                'insurance_arrangement',
            ]);
        });
    }
};
