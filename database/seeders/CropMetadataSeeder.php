<?php

namespace Database\Seeders;

use App\Models\CropMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CropMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $crops = [
            [
                'slug' => 'coffee',
                'name' => 'Coffee',
                'description' => 'Primary exchange crop covering green coffee production, trade, and traceability workflows.',
                'sort_order' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($crops as $crop) {
            CropMetadata::query()->updateOrCreate(
                ['slug' => $crop['slug']],
                $crop,
            );
        }
    }
}
