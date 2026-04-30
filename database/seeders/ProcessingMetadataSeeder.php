<?php

namespace Database\Seeders;

use App\Models\ProcessingMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProcessingMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            ['name' => 'Washed', 'description' => 'Fermented and fully washed before drying.'],
            ['name' => 'Natural', 'description' => 'Dried in whole cherry for fruit-forward profiles.'],
            ['name' => 'Honey', 'description' => 'Mucilage retained during drying for added sweetness and body.'],
            ['name' => 'Anaerobic', 'description' => 'Processed with controlled oxygen-free fermentation.'],
            ['name' => 'Pulped Natural', 'description' => 'Depulped with partial mucilage retained during drying.'],
        ];

        foreach ($items as $index => $item) {
            ProcessingMetadata::query()->updateOrCreate(
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
