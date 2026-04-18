<?php

namespace Database\Seeders;

use App\Models\CertificationMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CertificationMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Organic Certified',
            'Rainforest Alliance',
            'Fairtrade',
            'UTZ Certified',
            '4C Verified',
        ];

        foreach ($items as $index => $item) {
            CertificationMetadata::query()->updateOrCreate(
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
