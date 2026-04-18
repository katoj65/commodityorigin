<?php

namespace Database\Seeders;

use App\Models\FarmingPracticeMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FarmingPracticeMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $practices = [
            'Mulching',
            'Intercropping',
            'Shade Grown',
            'fertilizer type',
            'pesticide type',
        ];

        foreach ($practices as $index => $practice) {
            FarmingPracticeMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($practice)],
                [
                    'name' => $practice,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
