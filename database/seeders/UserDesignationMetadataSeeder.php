<?php

namespace Database\Seeders;

use App\Models\UserDesignationMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserDesignationMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $designations = [
            [
                'slug' => 'farmer',
                'name' => 'Farmer',
                'description' => 'Grows and supplies coffee directly from a farm or smallholding.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'slug' => 'cooperative',
                'name' => 'Cooperative',
                'description' => 'Represents a group of farmers pooling coffee for collective sale.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'slug' => 'trader',
                'name' => 'Trader',
                'description' => 'Buys and sells coffee lots between market participants.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'slug' => 'exporter',
                'name' => 'Exporter',
                'description' => 'Handles export documentation and cross-border shipment of coffee.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'slug' => 'processor',
                'name' => 'Processor',
                'description' => 'Mills, washes, or otherwise processes raw coffee into export-ready form.',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'slug' => 'buyer',
                'name' => 'Buyer',
                'description' => 'Purchases coffee lots for resale, roasting, or consumption.',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'slug' => 'roaster',
                'name' => 'Roaster',
                'description' => 'Roasts green coffee for retail or wholesale distribution.',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'slug' => 'logistics-provider',
                'name' => 'Logistics Provider',
                'description' => 'Moves, stores, or handles coffee shipments across the supply chain.',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'slug' => 'financial-service-provider',
                'name' => 'Financial Service Provider',
                'description' => 'Offers financing, escrow, insurance, or other financial services to the trade.',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'slug' => 'other',
                'name' => 'Other',
                'description' => 'Any participant whose role does not fit the designations above.',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($designations as $designation) {
            UserDesignationMetadata::query()->updateOrCreate(
                ['slug' => $designation['slug']],
                $designation,
            );
        }
    }
}
