<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('season_id');
            $table->string('currency', 3)->nullable()->default('USD')->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table): void {
            $table->dropColumn('currency');
            $table->foreignId('season_id')
                ->nullable()
                ->after('user_id')
                ->constrained('seasons')
                ->nullOnDelete();
        });
    }
};
