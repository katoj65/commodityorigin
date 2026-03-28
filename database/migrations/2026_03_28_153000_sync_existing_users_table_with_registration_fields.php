<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->default('')->after('name');
            }

            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->default('')->after('first_name');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('last_name');
            }

            if (! Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->default('')->after('role');
            }
        });

        if (Schema::hasColumn('users', 'name') && Schema::hasColumn('users', 'first_name')) {
            DB::statement("
                UPDATE users
                SET first_name = COALESCE(NULLIF(name, ''), email)
                WHERE first_name = '' OR first_name IS NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = array_values(array_filter([
                Schema::hasColumn('users', 'telephone') ? 'telephone' : null,
                Schema::hasColumn('users', 'role') ? 'role' : null,
                Schema::hasColumn('users', 'last_name') ? 'last_name' : null,
                Schema::hasColumn('users', 'first_name') ? 'first_name' : null,
            ]));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
