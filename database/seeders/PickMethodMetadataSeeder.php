<?php

namespace Database\Seeders;

use App\Models\PickMethodMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PickMethodMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $methods = [
            'Selective Picking',
            'Strip Picking',
            'Mechanical Picking',
            'Hand Sorting',
        ];

        foreach ($methods as $index => $method) {
            PickMethodMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($method)],
                [
                    'name' => $method,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
