<?php

namespace Database\Seeders;

use App\Models\SustainabilityPracticesMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SustainabilityPracticesMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The sustainability practices a farm_sustainability_practices row's
     * `practice` column can hold. `slug` is the exact machine code
     * FarmSustainabilityPracticeService::store() is expected to receive.
     */
    public function run(): void
    {
        $items = [
            ['slug' => 'intercropping', 'name' => 'Intercropping', 'description' => 'Growing coffee alongside other crops or shade trees to improve soil health and biodiversity.'],
            ['slug' => 'organic_composting', 'name' => 'Organic Composting', 'description' => 'Using composted organic matter instead of synthetic fertilizers to enrich the soil.'],
            ['slug' => 'shade_grown', 'name' => 'Shade-Grown Cultivation', 'description' => 'Growing coffee under a forest canopy to protect biodiversity and reduce erosion.'],
            ['slug' => 'water_efficient_irrigation', 'name' => 'Water-Efficient Irrigation', 'description' => 'Using drip or low-volume irrigation methods to conserve water.'],
            ['slug' => 'agroforestry', 'name' => 'Agroforestry', 'description' => 'Integrating trees and shrubs into the farm to improve soil, water, and biodiversity outcomes.'],
            ['slug' => 'soil_conservation', 'name' => 'Soil Conservation', 'description' => 'Terracing, contour planting, or cover cropping to prevent soil erosion.'],
            ['slug' => 'integrated_pest_management', 'name' => 'Integrated Pest Management', 'description' => 'Managing pests through biological and cultural controls instead of synthetic pesticides.'],
            ['slug' => 'renewable_energy', 'name' => 'Renewable Energy Use', 'description' => 'Using solar, biogas, or other renewable energy sources on the farm.'],
            ['slug' => 'waste_reduction_recycling', 'name' => 'Waste Reduction & Recycling', 'description' => 'Reusing coffee pulp, husks, and other by-products instead of discarding them.'],
            ['slug' => 'biodiversity_conservation', 'name' => 'Biodiversity Conservation', 'description' => 'Preserving natural habitats and wildlife corridors on or around the farm.'],
        ];

        foreach ($items as $index => $item) {
            SustainabilityPracticesMetadata::query()->updateOrCreate(
                ['slug' => $item['slug']],
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
