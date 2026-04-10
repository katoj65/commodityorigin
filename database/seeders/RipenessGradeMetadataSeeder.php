<?php

namespace Database\Seeders;

use App\Models\RipenessGradeMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RipenessGradeMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $grades = [
            'Premium Red Cherry',
            'Mostly Red Cherry',
            'Mixed Ripeness',
            'Under-ripe Screening',
        ];

        foreach ($grades as $index => $grade) {
            RipenessGradeMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($grade)],
                [
                    'name' => $grade,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
