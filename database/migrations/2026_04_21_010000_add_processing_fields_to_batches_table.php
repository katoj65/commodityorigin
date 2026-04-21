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
        Schema::table('batches', function (Blueprint $table): void {
            if (! Schema::hasColumn('batches', 'processing_date')) {
                $table->date('processing_date')->nullable()->after('moisture_content');
            }

            if (! Schema::hasColumn('batches', 'processing_method')) {
                $table->string('processing_method')->nullable()->after('processing_date');
            }

            if (! Schema::hasColumn('batches', 'drying_method')) {
                $table->string('drying_method')->nullable()->after('processing_method');
            }

            if (! Schema::hasColumn('batches', 'drying_duration')) {
                $table->unsignedInteger('drying_duration')->nullable()->after('drying_method');
            }

            if (! Schema::hasColumn('batches', 'milling_status')) {
                $table->string('milling_status')->nullable()->after('drying_duration');
            }

            if (! Schema::hasColumn('batches', 'screen_size')) {
                $table->string('screen_size')->nullable()->after('milling_status');
            }

            if (! Schema::hasColumn('batches', 'defect_count')) {
                $table->unsignedInteger('defect_count')->nullable()->after('screen_size');
            }

            if (! Schema::hasColumn('batches', 'cup_score')) {
                $table->decimal('cup_score', 5, 2)->nullable()->after('defect_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table): void {
            foreach ([
                'cup_score',
                'defect_count',
                'screen_size',
                'milling_status',
                'drying_duration',
                'drying_method',
                'processing_method',
                'processing_date',
            ] as $column) {
                if (Schema::hasColumn('batches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
