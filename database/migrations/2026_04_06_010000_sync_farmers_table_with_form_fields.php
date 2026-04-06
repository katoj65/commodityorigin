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
        if (! Schema::hasTable('farmers')) {
            return;
        }

        $hasGender = Schema::hasColumn('farmers', 'gender');
        $hasDateOfBirth = Schema::hasColumn('farmers', 'date_of_birth');

        if ($hasGender || $hasDateOfBirth) {
            Schema::table('farmers', function (Blueprint $table) use ($hasGender, $hasDateOfBirth) {
                if ($hasGender) {
                    $table->dropColumn('gender');
                }

                if ($hasDateOfBirth) {
                    $table->dropColumn('date_of_birth');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('farmers')) {
            return;
        }

        $hasGender = Schema::hasColumn('farmers', 'gender');
        $hasDateOfBirth = Schema::hasColumn('farmers', 'date_of_birth');

        if (! $hasGender || ! $hasDateOfBirth) {
            Schema::table('farmers', function (Blueprint $table) use ($hasGender, $hasDateOfBirth) {
                if (! $hasGender) {
                    $table->string('gender')->nullable()->after('last_name');
                }

                if (! $hasDateOfBirth) {
                    $table->date('date_of_birth')->nullable()->after('gender');
                }
            });
        }
    }
};
