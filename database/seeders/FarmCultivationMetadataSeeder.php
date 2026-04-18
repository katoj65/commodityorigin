<?php

namespace Database\Seeders;

use App\Models\FarmCultivationMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FarmCultivationMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Shade Management',
            'Pruning Cycle',
            'Intercropping',
            'Soil Amendment',
            'Water Retention Practice',
            'variety',
            'shade tree types',
        

        ];

        foreach ($items as $index => $item) {
            FarmCultivationMetadata::query()->updateOrCreate(
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
