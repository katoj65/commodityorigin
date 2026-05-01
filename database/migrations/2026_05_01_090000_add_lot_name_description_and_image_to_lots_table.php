<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lots', 'lot_name')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->string('lot_name')->nullable()->after('lot_number');
            });
        }

        if (! Schema::hasColumn('lots', 'description')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->text('description')->nullable()->after('lot_name');
            });
        }

        if (! Schema::hasColumn('lots', 'image')) {
            Schema::table('lots', function (Blueprint $table): void {
                $table->string('image')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('lots', 'image') ? 'image' : null,
            Schema::hasColumn('lots', 'description') ? 'description' : null,
            Schema::hasColumn('lots', 'lot_name') ? 'lot_name' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table('lots', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};

