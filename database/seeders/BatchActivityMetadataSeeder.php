<?php

namespace Database\Seeders;

use App\Models\BatchActivityMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatchActivityMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The post-harvest handling stages a batch_activities row's `event`
     * column can hold — every operation that can be performed on a batch
     * as it moves through the pipeline. `slug` is the exact machine code
     * BatchActivityService::record() is expected to receive in `event`.
     */
    public function run(): void
    {
        $items = [
            ['slug' => 'collection', 'name' => 'Collection', 'description' => 'Cherries or parchment are collected from the source and brought into the batch.'],
            ['slug' => 'storage', 'name' => 'Storage', 'description' => 'The batch is placed into warehouse storage between processing steps.'],
            ['slug' => 'cleaning', 'name' => 'Cleaning', 'description' => 'Foreign matter and defective beans are removed from the batch.'],
            ['slug' => 'drying', 'name' => 'Drying', 'description' => 'The batch is dried down to its target moisture content.'],
            ['slug' => 'processing', 'name' => 'Processing', 'description' => 'The batch undergoes its primary processing method (washed, natural, honey, etc.).'],
            ['slug' => 'fermentation', 'name' => 'Fermentation', 'description' => 'The batch ferments to break down mucilage before washing or drying.'],
            ['slug' => 'hulling', 'name' => 'Hulling', 'description' => 'Parchment or husk is removed from the batch to reveal green coffee.'],
            ['slug' => 'sorting', 'name' => 'Sorting', 'description' => 'Beans in the batch are sorted by size, density, or color.'],
            ['slug' => 'grading', 'name' => 'Grading', 'description' => 'The batch is graded and assigned a quality classification.'],
            ['slug' => 'blending', 'name' => 'Blending', 'description' => 'The batch is blended with other batches or lots.'],
            ['slug' => 'quality_control', 'name' => 'Quality Control', 'description' => 'The batch undergoes quality control checks and cupping.'],
            ['slug' => 'inspection', 'name' => 'Inspection', 'description' => 'The batch is inspected for compliance and export readiness.'],
            ['slug' => 'packaging', 'name' => 'Packaging', 'description' => 'The batch is packaged for storage, transport, or sale.'],
            ['slug' => 'repackaging', 'name' => 'Repackaging', 'description' => 'The batch is repackaged, e.g. after inspection or a partial sale.'],
        ];

        foreach ($items as $index => $item) {
            BatchActivityMetadata::query()->updateOrCreate(
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
