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
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('user_designation_metadata_id')->nullable()->after('currency_code')
                ->constrained('user_designation_metadata')->nullOnDelete();
            $table->string('other_name')->nullable()->after('user_designation_metadata_id');
            $table->foreignId('created_by_user_id')->nullable()->after('other_name')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropColumn('other_name');
            $table->dropConstrainedForeignId('user_designation_metadata_id');
        });
    }
};
