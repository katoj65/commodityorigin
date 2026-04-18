<?php

namespace Database\Seeders;

use App\Models\SensoryMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SensoryMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Floral Aroma',
            'Citrus Acidity',
            'Chocolate Notes',
            'Balanced Body',
            'Clean Finish',
            'aroma',
            'acidity',
            'body',
            'flavor notes',
            'aftertaste'
        ];

        foreach ($items as $index => $item) {
            SensoryMetadata::query()->updateOrCreate(
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
