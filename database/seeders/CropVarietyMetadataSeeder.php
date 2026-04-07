<?php

namespace Database\Seeders;

use App\Models\CropVarietyMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CropVarietyMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $varieties = [
            [
                'slug' => 'robusta',
                'name' => 'Robusta',
                'description' => 'Hardy coffee type valued for body, resilience, and stronger roast character.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'arabica',
                'name' => 'Arabica',
                'description' => 'High-value coffee type known for sweetness, acidity, and cup complexity.',
                'sort_order' => 2,
            ],
        ];

        foreach ($varieties as $variety) {
            CropVarietyMetadata::query()->updateOrCreate(
                ['slug' => $variety['slug']],
                [
                    'name' => $variety['name'],
                    'description' => $variety['description'],
                    'sort_order' => $variety['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
