<?php

namespace Database\Seeders;

use App\Models\FlavorMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FlavorMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The cupping flavor notes a lot's tasting profile can be tagged
     * with.
     */
    public function run(): void
    {
        $items = [
            'Chocolate', 'Cocoa', 'Caramel', 'Honey', 'Brown Sugar', 'Vanilla', 'Toffee', 'Molasses',
            'Almond', 'Hazelnut', 'Cashew', 'Peanut',
            'Blueberry', 'Strawberry', 'Blackcurrant', 'Cherry', 'Apple', 'Peach', 'Apricot', 'Orange', 'Lemon', 'Mango',
            'Tropical Fruit', 'Raisin', 'Dried Fruit',
            'Jasmine', 'Rose',
            'Cinnamon', 'Clove',
            'Black Tea', 'Green Tea', 'Wine', 'Tobacco',
            'Citrus', 'Berry', 'Nutty', 'Floral', 'Herbal', 'Spicy',
        ];

        foreach ($items as $index => $item) {
            FlavorMetadata::query()->updateOrCreate(
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
