<?php

namespace Database\Seeders;

use App\Models\SustainableMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SustainableMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $sustainabilityFlags = [
            'energy source',
            'water usage',
            'carbon_footprint',
            'shade_grown',
            'biodiversity practices'
        ];

        foreach ($sustainabilityFlags as $index => $item) {
            SustainableMetadata::query()->updateOrCreate(
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
