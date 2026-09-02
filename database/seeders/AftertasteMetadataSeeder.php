<?php

namespace Database\Seeders;

use App\Models\AftertasteMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AftertasteMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The finish character a lot's cupping profile can be tagged with.
     */
    public function run(): void
    {
        $items = [
            'Clean', 'Sweet', 'Fruity', 'Chocolate', 'Nutty', 'Spicy',
            'Floral', 'Smoky', 'Bitter', 'Dry', 'Woody', 'Earthy',
        ];

        foreach ($items as $index => $item) {
            AftertasteMetadata::query()->updateOrCreate(
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
