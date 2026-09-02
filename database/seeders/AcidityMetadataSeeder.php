<?php

namespace Database\Seeders;

use App\Models\AcidityMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcidityMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The acidity character a lot's cupping profile can be tagged with.
     */
    public function run(): void
    {
        $items = [
            'Citrus', 'Malic / apple-like', 'Tartaric / grape-like', 'Phosphoric',
            'Acetic', 'Lactic', 'Citric', 'Bright', 'Wine-like',
        ];

        foreach ($items as $index => $item) {
            AcidityMetadata::query()->updateOrCreate(
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
