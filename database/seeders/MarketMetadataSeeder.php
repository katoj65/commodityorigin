<?php

namespace Database\Seeders;

use App\Models\MarketMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            ['name' => 'United Arab Emirates', 'description' => 'Specialty demand with strong premium coffee growth.'],
            ['name' => 'Japan', 'description' => 'High-quality traceable lots for specialty roasters.'],
            ['name' => 'Germany', 'description' => 'Large green coffee import market in Europe.'],
            ['name' => 'United States', 'description' => 'Specialty and commercial coffee buyer market.'],
            ['name' => 'Canada', 'description' => 'Growing specialty segment with stable import demand.'],
            ['name' => 'United Kingdom', 'description' => 'Specialty coffee market with established buyers.'],
            ['name' => 'South Korea', 'description' => 'Premium coffee consumption and specialty growth.'],
            ['name' => 'Netherlands', 'description' => 'European trade gateway for coffee imports.'],
            ['name' => 'Italy', 'description' => 'Strong roasting culture and consistent coffee demand.'],
            ['name' => 'Australia', 'description' => 'Specialty-focused market with quality-driven buyers.'],
        ];

        foreach ($items as $index => $item) {
            MarketMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}

