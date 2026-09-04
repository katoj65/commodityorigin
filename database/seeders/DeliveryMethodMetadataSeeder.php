<?php

namespace Database\Seeders;

use App\Models\DeliveryMethodMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeliveryMethodMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The delivery method a market listing can be tagged with.
     */
    public function run(): void
    {
        $items = [
            'Sea Freight', 'Air Freight', 'Road Freight', 'Rail Freight',
            'Courier', 'Multimodal', 'Buyer Pickup', 'Seller Delivery',
        ];

        foreach ($items as $index => $item) {
            DeliveryMethodMetadata::query()->updateOrCreate(
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
