<?php

namespace Database\Seeders;

use App\Models\MillingMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MillingMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            ['name' => 'Not Yet Milled', 'description' => 'Parchment or dried cherry has not been through the mill.'],
            ['name' => 'Hulled', 'description' => 'Parchment or husk removed to expose the green bean.'],
            ['name' => 'Polished', 'description' => 'Silverskin buffed off the hulled bean for a cleaner finish.'],
            ['name' => 'Graded & Sorted', 'description' => 'Sized and sorted by density, colour, and defect count.'],
            ['name' => 'Fully Milled', 'description' => 'Milling complete and ready for bagging or export.'],
        ];

        foreach ($items as $index => $item) {
            MillingMetadata::query()->updateOrCreate(
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
