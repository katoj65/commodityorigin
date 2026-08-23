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
        Schema::table('farms', function (Blueprint $table): void {
            $table->foreignId('soil_metadata_id')->nullable()->after('coffee_type')
                ->constrained('soil_metadata')->nullOnDelete();
            $table->foreignId('climate_zone_metadata_id')->nullable()->after('soil_metadata_id')
                ->constrained('climate_zone_metadata')->nullOnDelete();
            $table->decimal('water_conservation_percentage', 5, 2)->nullable()->after('climate_zone_metadata_id');
            $table->decimal('carbon_sequestration', 8, 2)->nullable()->after('water_conservation_percentage');
            $table->decimal('soil_health_index', 3, 1)->nullable()->after('carbon_sequestration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('soil_metadata_id');
            $table->dropConstrainedForeignId('climate_zone_metadata_id');
            $table->dropColumn(['water_conservation_percentage', 'carbon_sequestration', 'soil_health_index']);
        });
    }
};
