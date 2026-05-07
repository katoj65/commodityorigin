<?php

namespace Database\Seeders;

use App\Models\ClimateZoneMetadata;
use Illuminate\Database\Seeder;

class ClimateZoneMetadataSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name'               => 'Highland Equatorial',
                'slug'               => 'highland-equatorial',
                'description'        => 'High-altitude equatorial belt with consistent rainfall and cool temperatures. Ideal for specialty Arabica production.',
                'altitude_min'       => 1600,
                'altitude_max'       => 2200,
                'rainfall_range'     => '1,400–2,000 mm',
                'temperature_range'  => '14–22 °C',
                'humidity_range'     => '65–80 %',
                'coffee_suitability' => 'High',
                'is_active'          => true,
                'sort_order'         => 1,
            ],
            [
                'name'               => 'Mid-Altitude Tropical',
                'slug'               => 'mid-altitude-tropical',
                'description'        => 'Moderate altitude with warm days and cool nights. Suitable for both Arabica and Robusta varieties.',
                'altitude_min'       => 1000,
                'altitude_max'       => 1599,
                'rainfall_range'     => '1,200–1,600 mm',
                'temperature_range'  => '18–26 °C',
                'humidity_range'     => '60–75 %',
                'coffee_suitability' => 'Medium',
                'is_active'          => true,
                'sort_order'         => 2,
            ],
            [
                'name'               => 'Lowland Tropical',
                'slug'               => 'lowland-tropical',
                'description'        => 'Low-altitude tropical zone with high humidity and warm temperatures. Best suited for Robusta production.',
                'altitude_min'       => 200,
                'altitude_max'       => 999,
                'rainfall_range'     => '1,000–1,400 mm',
                'temperature_range'  => '22–32 °C',
                'humidity_range'     => '70–85 %',
                'coffee_suitability' => 'Medium',
                'is_active'          => true,
                'sort_order'         => 3,
            ],
            [
                'name'               => 'Volcanic Highland',
                'slug'               => 'volcanic-highland',
                'description'        => 'Volcanic soil highland zone with mineral-rich earth and high rainfall. Produces complex specialty coffee profiles.',
                'altitude_min'       => 1800,
                'altitude_max'       => 2400,
                'rainfall_range'     => '1,600–2,200 mm',
                'temperature_range'  => '12–20 °C',
                'humidity_range'     => '68–82 %',
                'coffee_suitability' => 'High',
                'is_active'          => true,
                'sort_order'         => 4,
            ],
            [
                'name'               => 'Semi-Arid Transitional',
                'slug'               => 'semi-arid-transitional',
                'description'        => 'Transitional zone between highland and lowland with irregular rainfall. Limited but possible coffee production.',
                'altitude_min'       => 500,
                'altitude_max'       => 1200,
                'rainfall_range'     => '700–1,100 mm',
                'temperature_range'  => '20–30 °C',
                'humidity_range'     => '45–60 %',
                'coffee_suitability' => 'Low',
                'is_active'          => true,
                'sort_order'         => 5,
            ],
        ];

        foreach ($zones as $zone) {
            ClimateZoneMetadata::updateOrCreate(
                ['slug' => $zone['slug']],
                $zone
            );
        }
    }
}
