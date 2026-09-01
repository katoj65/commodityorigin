<?php

namespace Database\Seeders;

use App\Models\SoilProfileMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SoilProfileMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The soil types a farm's soil profile entries can be classified as.
     */
    public function run(): void
    {
        $items = [
            'Loamy', 'Clay', 'Sandy', 'Silty', 'Peaty', 'Chalky', 'Volcanic', 'Alluvial', 'Laterite', 'Saline',
        ];

        foreach ($items as $index => $item) {
            SoilProfileMetadata::query()->updateOrCreate(
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
