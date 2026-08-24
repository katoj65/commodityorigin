<?php

namespace Database\Seeders;

use App\Models\DryingMethodMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DryingMethodMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            ['name' => 'Raised Bed', 'description' => 'Dried on elevated mesh beds with airflow on all sides.'],
            ['name' => 'Patio Drying', 'description' => 'Spread on concrete or brick patios and turned by hand.'],
            ['name' => 'Sun-dried on Tarps', 'description' => 'Dried on tarpaulins laid directly on the ground.'],
            ['name' => 'Mechanical Dryer', 'description' => 'Finished or fully dried using a mechanical/heated dryer.'],
            ['name' => 'Solar Dryer', 'description' => 'Dried inside a greenhouse-style solar tunnel or dome.'],
        ];

        foreach ($items as $index => $item) {
            DryingMethodMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
