<?php

namespace Database\Seeders;

use App\Models\FarmCollectionActivityMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FarmCollectionActivityMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The intake-handling stages a farm_collection_activities row's
     * `event` column can hold — every operation performed on a collection
     * as it's brought in and accepted. `slug` is the exact machine code
     * FarmCollectionActivityService::record() is expected to receive in
     * `event`.
     */
    public function run(): void
    {
        $items = [
            ['slug' => 'delivery', 'name' => 'Delivery', 'description' => 'The collection is delivered to the buying or collection point.'],
            ['slug' => 'sampling', 'name' => 'Sampling', 'description' => 'A sample is drawn from the collection for quality assessment.'],
            ['slug' => 'inspection', 'name' => 'Inspection', 'description' => 'The collection is inspected for defects, moisture, or foreign matter.'],
            ['slug' => 'verification', 'name' => 'Verification', 'description' => "The collection's weight, quality, and origin are verified before acceptance."],
        ];

        foreach ($items as $index => $item) {
            FarmCollectionActivityMetadata::query()->updateOrCreate(
                ['slug' => $item['slug']],
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
