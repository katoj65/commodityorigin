<?php

namespace Database\Seeders;

use App\Models\SoilMetadata;
use Illuminate\Database\Seeder;

class SoilMetadataSeeder extends Seeder
{
    public function run(): void
    {
        $soils = [
            [
                'name'               => 'Volcanic Loam',
                'slug'               => 'volcanic-loam',
                'description'        => 'Mineral-rich, well-drained soil formed from volcanic ash. Prized for producing complex, high-quality specialty coffee.',
                'ph_range'           => '5.5–6.5',
                'drainage'           => 'Well-drained',
                'fertility'          => 'High',
                'coffee_suitability' => 'High',
                'is_active'          => true,
                'sort_order'         => 1,
            ],
            [
                'name'               => 'Red Laterite',
                'slug'               => 'red-laterite',
                'description'        => 'Iron- and aluminium-rich tropical soil, deeply weathered with good drainage and moderate acidity.',
                'ph_range'           => '4.5–5.5',
                'drainage'           => 'Well-drained',
                'fertility'          => 'Medium',
                'coffee_suitability' => 'High',
                'is_active'          => true,
                'sort_order'         => 2,
            ],
            [
                'name'               => 'Loam',
                'slug'               => 'loam',
                'description'        => 'Balanced mix of sand, silt, and clay with excellent structure, moisture retention, and drainage.',
                'ph_range'           => '6.0–7.0',
                'drainage'           => 'Well-drained',
                'fertility'          => 'High',
                'coffee_suitability' => 'High',
                'is_active'          => true,
                'sort_order'         => 3,
            ],
            [
                'name'               => 'Alluvial Soil',
                'slug'               => 'alluvial-soil',
                'description'        => 'Fertile, fine-grained soil deposited by rivers and floodplains, rich in nutrients.',
                'ph_range'           => '6.0–7.0',
                'drainage'           => 'Well-drained',
                'fertility'          => 'High',
                'coffee_suitability' => 'Medium',
                'is_active'          => true,
                'sort_order'         => 4,
            ],
            [
                'name'               => 'Sandy Clay Loam',
                'slug'               => 'sandy-clay-loam',
                'description'        => 'Moderately fertile soil with a mix of sand and clay, offering balanced drainage and water retention.',
                'ph_range'           => '5.5–6.8',
                'drainage'           => 'Moderate',
                'fertility'          => 'Medium',
                'coffee_suitability' => 'Medium',
                'is_active'          => true,
                'sort_order'         => 5,
            ],
            [
                'name'               => 'Clay Loam',
                'slug'               => 'clay-loam',
                'description'        => 'Dense, nutrient-retentive soil with moderate drainage; can be prone to compaction if not well managed.',
                'ph_range'           => '6.0–7.0',
                'drainage'           => 'Moderate',
                'fertility'          => 'Medium',
                'coffee_suitability' => 'Medium',
                'is_active'          => true,
                'sort_order'         => 6,
            ],
            [
                'name'               => 'Silty Loam',
                'slug'               => 'silty-loam',
                'description'        => 'Smooth-textured soil with good water retention and moderate drainage, fertile but can crust when dry.',
                'ph_range'           => '6.0–7.0',
                'drainage'           => 'Moderate',
                'fertility'          => 'Medium',
                'coffee_suitability' => 'Medium',
                'is_active'          => true,
                'sort_order'         => 7,
            ],
            [
                'name'               => 'Black Cotton Soil',
                'slug'               => 'black-cotton-soil',
                'description'        => 'Clay-rich vertisol that expands when wet and cracks when dry, with high fertility but poor drainage.',
                'ph_range'           => '6.5–7.5',
                'drainage'           => 'Poor',
                'fertility'          => 'High',
                'coffee_suitability' => 'Low',
                'is_active'          => true,
                'sort_order'         => 8,
            ],
            [
                'name'               => 'Sandy Soil',
                'slug'               => 'sandy-soil',
                'description'        => 'Coarse, loose soil that drains quickly but has low nutrient and moisture retention.',
                'ph_range'           => '5.5–7.0',
                'drainage'           => 'Excessive',
                'fertility'          => 'Low',
                'coffee_suitability' => 'Low',
                'is_active'          => true,
                'sort_order'         => 9,
            ],
            [
                'name'               => 'Peaty Soil',
                'slug'               => 'peaty-soil',
                'description'        => 'Dark, organic-rich soil that retains large amounts of water; often waterlogged and highly acidic.',
                'ph_range'           => '4.0–5.5',
                'drainage'           => 'Poor',
                'fertility'          => 'High',
                'coffee_suitability' => 'Low',
                'is_active'          => true,
                'sort_order'         => 10,
            ],
            [
                'name'               => 'Chalky Soil',
                'slug'               => 'chalky-soil',
                'description'        => 'Alkaline, free-draining soil over limestone or chalk bedrock, often low in nutrients for acid-loving crops.',
                'ph_range'           => '7.5–8.5',
                'drainage'           => 'Well-drained',
                'fertility'          => 'Low',
                'coffee_suitability' => 'Low',
                'is_active'          => true,
                'sort_order'         => 11,
            ],
            [
                'name'               => 'Rocky/Stony Soil',
                'slug'               => 'rocky-stony-soil',
                'description'        => 'Shallow, coarse-fragment soil with excessive drainage and low water/nutrient holding capacity.',
                'ph_range'           => '5.0–7.5',
                'drainage'           => 'Excessive',
                'fertility'          => 'Low',
                'coffee_suitability' => 'Low',
                'is_active'          => true,
                'sort_order'         => 12,
            ],
        ];

        foreach ($soils as $soil) {
            SoilMetadata::updateOrCreate(
                ['slug' => $soil['slug']],
                $soil
            );
        }
    }
}
