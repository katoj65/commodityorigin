<?php

namespace Database\Seeders;

use App\Models\FarmInputMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FarmInputMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Organic Fertilizer',
            'Compost',
            'Mulch Materials',
            'Biological Pest Control',
            'Irrigation Supplies',
            'farming method',
            'fertilizer type',
            'pesticide type',
        ];

        foreach ($items as $index => $item) {
            FarmInputMetadata::query()->updateOrCreate(
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
