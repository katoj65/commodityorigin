<?php

namespace Database\Seeders;

use App\Models\SeasonMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeasonMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Same four labels HarvestService::harvestSeasonOptions() has hardcoded
     * up to now — moved into a real lookup table instead of a static array.
     */
    public function run(): void
    {
        $items = [
            'Main Crop',
            'Fly Crop',
            'Early Harvest',
            'Late Harvest',
        ];

        foreach ($items as $index => $item) {
            SeasonMetadata::query()->updateOrCreate(
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
