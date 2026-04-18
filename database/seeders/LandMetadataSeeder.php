<?php

namespace Database\Seeders;

use App\Models\LandMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LandMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Freehold Title',
            'Leasehold Agreement',
            'Customary Tenure',
            'Mapped Boundary',
            'Geo-referenced Parcel',
            'soil type',
            'topography',
            'elevation',
            'land use type',
            'deforestation free',
            'soil health status'

        ];

        foreach ($items as $index => $item) {
            LandMetadata::query()->updateOrCreate(
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
