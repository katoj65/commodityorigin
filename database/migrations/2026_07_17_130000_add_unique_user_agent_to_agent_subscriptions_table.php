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
        // Remove duplicate subscriptions for the same user/agent pair, keeping the earliest.
        // The DELETE ... JOIN syntax is MySQL-specific; other drivers (e.g. the
        // SQLite database used by the test suite) start empty, so there is
        // nothing to deduplicate there.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('
                DELETE t1 FROM agent_subscriptions t1
                INNER JOIN agent_subscriptions t2
                    ON t1.user_id = t2.user_id
                    AND t1.agent_id = t2.agent_id
                    AND t1.id > t2.id
            ');
        }

        Schema::table('agent_subscriptions', function (Blueprint $table): void {
            $table->unique(['user_id', 'agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_subscriptions', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'agent_id']);
        });
    }
};
