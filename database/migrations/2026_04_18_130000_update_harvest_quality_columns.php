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
        Schema::table('harvests', function (Blueprint $table): void {
            if (! Schema::hasColumn('harvests', 'ripeness_percentage')) {
                $table->decimal('ripeness_percentage', 5, 2)->nullable()->after('weight');
            }

            if (! Schema::hasColumn('harvests', 'foreign_matter_present')) {
                $table->boolean('foreign_matter_present')->default(false)->after('ripeness_percentage');
            }

            if (! Schema::hasColumn('harvests', 'pest_damage')) {
                $table->boolean('pest_damage')->default(false)->after('foreign_matter_present');
            }

            if (! Schema::hasColumn('harvests', 'disease_signs')) {
                $table->boolean('disease_signs')->default(false)->after('pest_damage');
            }

            if (! Schema::hasColumn('harvests', 'visible_defects')) {
                $table->boolean('visible_defects')->default(false)->after('disease_signs');
            }
        });

        if (Schema::hasColumn('harvests', 'ripeness_grade')) {
            DB::table('harvests')->update([
                'ripeness_percentage' => DB::raw("
                    CASE ripeness_grade
                        WHEN 'Premium Red Cherry' THEN 95.00
                        WHEN 'Mostly Red Cherry' THEN 88.00
                        WHEN 'Mixed Ripeness' THEN 76.00
                        WHEN 'Under-ripe Screening' THEN 60.00
                        ELSE 82.00
                    END
                "),
            ]);

            Schema::table('harvests', function (Blueprint $table): void {
                $table->dropColumn('ripeness_grade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table): void {
            if (! Schema::hasColumn('harvests', 'ripeness_grade')) {
                $table->string('ripeness_grade')->nullable()->after('weight');
            }
        });

        if (Schema::hasColumn('harvests', 'ripeness_percentage')) {
            DB::table('harvests')->update([
                'ripeness_grade' => DB::raw("
                    CASE
                        WHEN ripeness_percentage >= 92 THEN 'Premium Red Cherry'
                        WHEN ripeness_percentage >= 84 THEN 'Mostly Red Cherry'
                        WHEN ripeness_percentage >= 68 THEN 'Mixed Ripeness'
                        ELSE 'Under-ripe Screening'
                    END
                "),
            ]);
        }

        Schema::table('harvests', function (Blueprint $table): void {
            if (Schema::hasColumn('harvests', 'visible_defects')) {
                $table->dropColumn('visible_defects');
            }

            if (Schema::hasColumn('harvests', 'disease_signs')) {
                $table->dropColumn('disease_signs');
            }

            if (Schema::hasColumn('harvests', 'pest_damage')) {
                $table->dropColumn('pest_damage');
            }

            if (Schema::hasColumn('harvests', 'foreign_matter_present')) {
                $table->dropColumn('foreign_matter_present');
            }

            if (Schema::hasColumn('harvests', 'ripeness_percentage')) {
                $table->dropColumn('ripeness_percentage');
            }
        });
    }
};
