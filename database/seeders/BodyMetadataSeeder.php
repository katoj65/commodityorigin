<?php

namespace Database\Seeders;

use App\Models\BodyMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BodyMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The mouthfeel weight a lot's cupping profile can be tagged with.
     */
    public function run(): void
    {
        $items = [
            'Light', 'Medium-Light', 'Medium', 'Medium-Heavy', 'Heavy', 'Full',
        ];

        foreach ($items as $index => $item) {
            BodyMetadata::query()->updateOrCreate(
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
