<?php

namespace Database\Seeders;

use App\Models\HarvestingMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HarvestingMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Selective Picking',
            'Peak Ripeness Harvest',
            'Field Sorting',
            'Batch Separation',
            'Collection Center Delivery',
        ];

        foreach ($items as $index => $item) {
            HarvestingMetadata::query()->updateOrCreate(
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
