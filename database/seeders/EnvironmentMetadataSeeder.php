<?php

namespace Database\Seeders;

use App\Models\EnvironmentMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnvironmentMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Watershed Protection',
            'Tree Canopy Preservation',
            'Soil Erosion Control',
            'Waste Reduction',
            'Habitat Conservation',
            'carbon footprint',
            'water usage',
            'energy source',
            'shade_grown',
            'biodiversity practices',
            

        ];

        foreach ($items as $index => $item) {
            EnvironmentMetadata::query()->updateOrCreate(
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
