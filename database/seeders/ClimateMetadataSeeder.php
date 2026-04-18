<?php

namespace Database\Seeders;

use App\Models\ClimateMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClimateMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Rainfall Monitoring',
            'Drought Adaptation',
            'Frost Prevention',
            'Heat Stress Mitigation',
            'Seasonal Forecast Planning',
        ];

        foreach ($items as $index => $item) {
            ClimateMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($item)],
                [
                    'name' => $item,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
