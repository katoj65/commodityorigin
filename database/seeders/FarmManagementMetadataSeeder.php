<?php

namespace Database\Seeders;

use App\Models\FarmManagementMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FarmManagementMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Worker Safety Protocol',
            'Seasonal Planning',
            'Record Keeping',
            'Inventory Management',
            'Field Monitoring',
            'irrigation method',
            'pest management strategy',
            'post harvest handling',
            'labor management',
            'pruning practices',
            



        ];

        foreach ($items as $index => $item) {
            FarmManagementMetadata::query()->updateOrCreate(
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
