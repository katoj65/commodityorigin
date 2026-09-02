<?php

namespace Database\Seeders;

use App\Models\AromaMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AromaMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The aroma character a lot's cupping profile can be tagged with.
     */
    public function run(): void
    {
        $items = [
            'Berry', 'Blackberry', 'Blueberry', 'Strawberry', 'Raspberry',
            'Citrus', 'Lemon', 'Orange', 'Grapefruit',
            'Apple', 'Pear', 'Peach', 'Apricot', 'Plum',
            'Tropical Fruit', 'Mango', 'Pineapple', 'Passion Fruit',
            'Floral', 'Jasmine', 'Rose', 'Lavender', 'Hibiscus',
            'Caramel', 'Honey', 'Brown Sugar', 'Molasses', 'Vanilla',
            'Cocoa', 'Milk Chocolate', 'Dark Chocolate',
            'Nutty', 'Almond', 'Hazelnut', 'Peanut', 'Walnut',
            'Cinnamon', 'Clove', 'Nutmeg', 'Pepper', 'Cardamom',
            'Herbal', 'Fresh Herb', 'Green Tea', 'Grass',
            'Roasted', 'Toast', 'Cereal', 'Roasted Grain',
            'Woody', 'Earthy', 'Tobacco', 'Leather',
            'Winey', 'Fermented Fruit', 'Rum', 'Brandy',
            'Tea-like', 'Sweet Spice', 'Baked',
        ];

        foreach ($items as $index => $item) {
            AromaMetadata::query()->updateOrCreate(
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
