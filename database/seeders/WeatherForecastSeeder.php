<?php

namespace Database\Seeders;

use App\Models\WeatherForecast;
use Illuminate\Database\Seeder;

class WeatherForecastSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            [
                'region' => 'Kampala (Central)',
                'sort_order' => 1,
                'days' => [
                    ['condition' => 'Partly Cloudy', 'temperature_min' => 19.0, 'temperature_max' => 27.0, 'rainfall_mm' => 2.5, 'humidity_percentage' => 68, 'wind_speed_kmh' => 11.0, 'advisory' => 'Light showers possible in the afternoon — good conditions for shade-grown Robusta canopy growth.'],
                    ['condition' => 'Rainy', 'temperature_min' => 18.0, 'temperature_max' => 24.0, 'rainfall_mm' => 14.0, 'humidity_percentage' => 82, 'wind_speed_kmh' => 14.0, 'advisory' => 'Delay pesticide spraying — rain will wash off treatment before it takes effect.'],
                    ['condition' => 'Sunny', 'temperature_min' => 20.0, 'temperature_max' => 29.0, 'rainfall_mm' => 0.0, 'humidity_percentage' => 55, 'wind_speed_kmh' => 9.0, 'advisory' => 'Good drying conditions for parchment coffee laid out on raised beds.'],
                    ['condition' => 'Sunny', 'temperature_min' => 20.0, 'temperature_max' => 30.0, 'rainfall_mm' => 0.0, 'humidity_percentage' => 50, 'wind_speed_kmh' => 8.0, 'advisory' => 'Irrigate young seedlings early morning to offset the dry heat.'],
                ],
            ],
            [
                'region' => 'Mbale (Mt. Elgon)',
                'sort_order' => 2,
                'days' => [
                    ['condition' => 'Cloudy', 'temperature_min' => 15.0, 'temperature_max' => 23.0, 'rainfall_mm' => 4.0, 'humidity_percentage' => 74, 'wind_speed_kmh' => 10.0, 'advisory' => 'Overcast skies favour Arabica flowering — monitor for early cherry set.'],
                    ['condition' => 'Rainy', 'temperature_min' => 14.0, 'temperature_max' => 21.0, 'rainfall_mm' => 22.0, 'humidity_percentage' => 88, 'wind_speed_kmh' => 13.0, 'advisory' => 'Heavy rain risk on steep slopes — check terracing and mulch for erosion control.'],
                    ['condition' => 'Thunderstorms', 'temperature_min' => 14.0, 'temperature_max' => 20.0, 'rainfall_mm' => 31.0, 'humidity_percentage' => 91, 'wind_speed_kmh' => 19.0, 'advisory' => 'Secure drying racks and postpone fieldwork until storms clear.'],
                    ['condition' => 'Partly Cloudy', 'temperature_min' => 15.0, 'temperature_max' => 24.0, 'rainfall_mm' => 3.0, 'humidity_percentage' => 70, 'wind_speed_kmh' => 9.0, 'advisory' => 'Fair window for weeding and canopy pruning between showers.'],
                ],
            ],
            [
                'region' => 'Kasese (Rwenzori)',
                'sort_order' => 3,
                'days' => [
                    ['condition' => 'Sunny', 'temperature_min' => 17.0, 'temperature_max' => 28.0, 'rainfall_mm' => 0.0, 'humidity_percentage' => 58, 'wind_speed_kmh' => 12.0, 'advisory' => 'Clear skies are ideal for sun-drying washed Arabica on raised beds.'],
                    ['condition' => 'Partly Cloudy', 'temperature_min' => 16.0, 'temperature_max' => 26.0, 'rainfall_mm' => 3.5, 'humidity_percentage' => 65, 'wind_speed_kmh' => 14.0, 'advisory' => 'Afternoon cloud build-up near the mountains — bring in drying parchment by 3pm.'],
                    ['condition' => 'Rainy', 'temperature_min' => 15.0, 'temperature_max' => 22.0, 'rainfall_mm' => 18.0, 'humidity_percentage' => 84, 'wind_speed_kmh' => 16.0, 'advisory' => 'Wet conditions favour coffee berry disease — inspect trees for early lesions.'],
                    ['condition' => 'Sunny', 'temperature_min' => 17.0, 'temperature_max' => 27.0, 'rainfall_mm' => 0.0, 'humidity_percentage' => 55, 'wind_speed_kmh' => 10.0, 'advisory' => 'Good conditions to resume harvest picking on ripe cherry.'],
                ],
            ],
            [
                'region' => 'Kabale (Kigezi Highlands)',
                'sort_order' => 4,
                'days' => [
                    ['condition' => 'Cloudy', 'temperature_min' => 11.0, 'temperature_max' => 21.0, 'rainfall_mm' => 5.0, 'humidity_percentage' => 76, 'wind_speed_kmh' => 8.0, 'advisory' => 'Cool highland air — expect slower cherry ripening this week.'],
                    ['condition' => 'Rainy', 'temperature_min' => 10.0, 'temperature_max' => 18.0, 'rainfall_mm' => 12.0, 'humidity_percentage' => 85, 'wind_speed_kmh' => 11.0, 'advisory' => 'Check terrace drainage channels before the next storm front arrives.'],
                    ['condition' => 'Partly Cloudy', 'temperature_min' => 11.0, 'temperature_max' => 20.0, 'rainfall_mm' => 2.0, 'humidity_percentage' => 70, 'wind_speed_kmh' => 9.0, 'advisory' => 'Favourable window for mulching around newly planted seedlings.'],
                    ['condition' => 'Cloudy', 'temperature_min' => 12.0, 'temperature_max' => 21.0, 'rainfall_mm' => 4.0, 'humidity_percentage' => 73, 'wind_speed_kmh' => 8.0, 'advisory' => 'Overcast but dry — suitable for routine farm maintenance.'],
                ],
            ],
            [
                'region' => 'Mubende (Central)',
                'sort_order' => 5,
                'days' => [
                    ['condition' => 'Sunny', 'temperature_min' => 18.0, 'temperature_max' => 28.0, 'rainfall_mm' => 0.0, 'humidity_percentage' => 52, 'wind_speed_kmh' => 10.0, 'advisory' => 'Dry spell continues — prioritise irrigation for young Robusta plots.'],
                    ['condition' => 'Sunny', 'temperature_min' => 19.0, 'temperature_max' => 29.0, 'rainfall_mm' => 0.0, 'humidity_percentage' => 48, 'wind_speed_kmh' => 9.0, 'advisory' => 'Ideal drying conditions — turn parchment coffee every 2 hours.'],
                    ['condition' => 'Partly Cloudy', 'temperature_min' => 18.0, 'temperature_max' => 26.0, 'rainfall_mm' => 3.0, 'humidity_percentage' => 62, 'wind_speed_kmh' => 12.0, 'advisory' => 'Rain returning by evening — cover drying beds before dusk.'],
                    ['condition' => 'Rainy', 'temperature_min' => 17.0, 'temperature_max' => 23.0, 'rainfall_mm' => 9.0, 'humidity_percentage' => 78, 'wind_speed_kmh' => 13.0, 'advisory' => 'Good soak for flowering trees — hold off on fertiliser until it clears.'],
                ],
            ],
        ];

        foreach ($regions as $region) {
            foreach ($region['days'] as $dayIndex => $day) {
                WeatherForecast::updateOrCreate(
                    [
                        'region' => $region['region'],
                        'forecast_date' => now()->addDays($dayIndex)->toDateString(),
                    ],
                    [
                        ...$day,
                        'is_active' => true,
                        'sort_order' => $region['sort_order'],
                    ],
                );
            }
        }
    }
}
