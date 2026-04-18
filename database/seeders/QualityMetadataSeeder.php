<?php

namespace Database\Seeders;

use App\Models\QualityMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QualityMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $qualities = [
            'quality score',
            'grade',
            'moisture content ',
            'bean size',
            'defect count',
        
        ];

        foreach ($qualities as $index => $quality) {
            QualityMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($quality)],
                [
                    'name' => $quality,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
