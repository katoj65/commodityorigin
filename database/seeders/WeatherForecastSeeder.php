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

        // Monthly planting-season outlook — one first-of-month entry per
        // region for the six months ahead, reflecting Uganda's bimodal
        // rainy-season pattern (short rains ~Sep–Nov, drier Dec–Feb).
        $monthlyOutlook = [
            'Kampala (Central)' => [
                ['condition' => 'Rainy', 'temperature_min' => 18.0, 'temperature_max' => 25.0, 'rainfall_mm' => 120.0, 'humidity_percentage' => 75, 'advisory' => 'Short rains begin — ideal window to transplant Robusta seedlings.'],
                ['condition' => 'Rainy', 'temperature_min' => 18.0, 'temperature_max' => 26.0, 'rainfall_mm' => 140.0, 'humidity_percentage' => 78, 'advisory' => 'Peak of the short rains — hold off on fertiliser application until spacing between showers.'],
                ['condition' => 'Partly Cloudy', 'temperature_min' => 18.0, 'temperature_max' => 25.0, 'rainfall_mm' => 90.0, 'humidity_percentage' => 72, 'advisory' => 'Rains tapering off — good time for weeding and mulching before the dry spell.'],
                ['condition' => 'Sunny', 'temperature_min' => 19.0, 'temperature_max' => 27.0, 'rainfall_mm' => 25.0, 'humidity_percentage' => 58, 'advisory' => 'Dry season sets in — prioritise irrigation for newly planted seedlings.'],
                ['condition' => 'Sunny', 'temperature_min' => 20.0, 'temperature_max' => 29.0, 'rainfall_mm' => 15.0, 'humidity_percentage' => 52, 'advisory' => 'Driest month — mulch heavily to retain soil moisture around young plants.'],
                ['condition' => 'Partly Cloudy', 'temperature_min' => 20.0, 'temperature_max' => 28.0, 'rainfall_mm' => 35.0, 'humidity_percentage' => 56, 'advisory' => 'Early signs of the long rains approaching — prepare land for the next planting cycle.'],
            ],
            'Mbale (Mt. Elgon)' => [
                ['condition' => 'Rainy', 'temperature_min' => 15.0, 'temperature_max' => 22.0, 'rainfall_mm' => 160.0, 'humidity_percentage' => 82, 'advisory' => 'Heavy short rains on the mountain slopes — reinforce terracing to prevent erosion.'],
                ['condition' => 'Thunderstorms', 'temperature_min' => 14.0, 'temperature_max' => 21.0, 'rainfall_mm' => 190.0, 'humidity_percentage' => 86, 'advisory' => 'Storm risk peaks — secure drying racks and delay any pruning.'],
                ['condition' => 'Rainy', 'temperature_min' => 15.0, 'temperature_max' => 21.0, 'rainfall_mm' => 130.0, 'humidity_percentage' => 80, 'advisory' => 'Consistent rain supports Arabica cherry development — monitor for coffee berry disease.'],
                ['condition' => 'Partly Cloudy', 'temperature_min' => 15.0, 'temperature_max' => 23.0, 'rainfall_mm' => 55.0, 'humidity_percentage' => 68, 'advisory' => 'Rains easing — good window for harvest and drying on raised beds.'],
                ['condition' => 'Sunny', 'temperature_min' => 16.0, 'temperature_max' => 25.0, 'rainfall_mm' => 20.0, 'humidity_percentage' => 58, 'advisory' => 'Clear skies favour sun-drying parchment coffee to target moisture.'],
                ['condition' => 'Partly Cloudy', 'temperature_min' => 16.0, 'temperature_max' => 24.0, 'rainfall_mm' => 45.0, 'humidity_percentage' => 64, 'advisory' => 'Scattered showers return — plan drying activity around forecasted breaks.'],
            ],
            'Kasese (Rwenzori)' => [
                ['condition' => 'Partly Cloudy', 'temperature_min' => 18.0, 'temperature_max' => 27.0, 'rainfall_mm' => 60.0, 'humidity_percentage' => 62, 'advisory' => 'Rain-shadow effect keeps rainfall moderate — supplement with irrigation if dry spells extend.'],
                ['condition' => 'Rainy', 'temperature_min' => 17.0, 'temperature_max' => 25.0, 'rainfall_mm' => 95.0, 'humidity_percentage' => 70, 'advisory' => 'Short rains arrive later here — good time to plant once soil moisture builds.'],
                ['condition' => 'Rainy', 'temperature_min' => 17.0, 'temperature_max' => 24.0, 'rainfall_mm' => 85.0, 'humidity_percentage' => 72, 'advisory' => 'Steady rainfall supports flowering — avoid heavy machinery on saturated soil.'],
                ['condition' => 'Sunny', 'temperature_min' => 18.0, 'temperature_max' => 28.0, 'rainfall_mm' => 20.0, 'humidity_percentage' => 55, 'advisory' => 'Dry season returns — irrigate young Robusta stands regularly.'],
                ['condition' => 'Sunny', 'temperature_min' => 19.0, 'temperature_max' => 29.0, 'rainfall_mm' => 10.0, 'humidity_percentage' => 48, 'advisory' => 'Driest stretch of the year — mulch and shade-check newly planted rows.'],
                ['condition' => 'Sunny', 'temperature_min' => 19.0, 'temperature_max' => 28.0, 'rainfall_mm' => 25.0, 'humidity_percentage' => 52, 'advisory' => 'Still dry — continue irrigation ahead of the long rains in March.'],
            ],
            'Kabale (Kigezi Highlands)' => [
                ['condition' => 'Cloudy', 'temperature_min' => 11.0, 'temperature_max' => 20.0, 'rainfall_mm' => 100.0, 'humidity_percentage' => 78, 'advisory' => 'Cool highland showers — favourable for terraced planting on the hillsides.'],
                ['condition' => 'Rainy', 'temperature_min' => 10.0, 'temperature_max' => 19.0, 'rainfall_mm' => 135.0, 'humidity_percentage' => 84, 'advisory' => 'Wettest month on the highlands — check terrace drainage before storms intensify.'],
                ['condition' => 'Rainy', 'temperature_min' => 11.0, 'temperature_max' => 19.0, 'rainfall_mm' => 110.0, 'humidity_percentage' => 82, 'advisory' => 'Continued rain — good soil moisture for new seedling establishment.'],
                ['condition' => 'Cloudy', 'temperature_min' => 11.0, 'temperature_max' => 20.0, 'rainfall_mm' => 60.0, 'humidity_percentage' => 74, 'advisory' => 'Rain easing gradually — window opens for mulching and weeding.'],
                ['condition' => 'Partly Cloudy', 'temperature_min' => 12.0, 'temperature_max' => 21.0, 'rainfall_mm' => 35.0, 'humidity_percentage' => 66, 'advisory' => 'Driest period for the highlands — light irrigation recommended for seedlings.'],
                ['condition' => 'Cloudy', 'temperature_min' => 12.0, 'temperature_max' => 21.0, 'rainfall_mm' => 55.0, 'humidity_percentage' => 70, 'advisory' => 'Showers picking up again ahead of the long rains — prepare nursery beds.'],
            ],
            'Mubende (Central)' => [
                ['condition' => 'Rainy', 'temperature_min' => 17.0, 'temperature_max' => 25.0, 'rainfall_mm' => 115.0, 'humidity_percentage' => 74, 'advisory' => 'Short rains begin — favourable conditions for Robusta transplanting.'],
                ['condition' => 'Rainy', 'temperature_min' => 17.0, 'temperature_max' => 26.0, 'rainfall_mm' => 145.0, 'humidity_percentage' => 77, 'advisory' => 'Peak rainfall — delay fertiliser application until drier spells.'],
                ['condition' => 'Partly Cloudy', 'temperature_min' => 17.0, 'temperature_max' => 25.0, 'rainfall_mm' => 85.0, 'humidity_percentage' => 71, 'advisory' => 'Rains slowing — good time for canopy pruning and weeding.'],
                ['condition' => 'Sunny', 'temperature_min' => 18.0, 'temperature_max' => 27.0, 'rainfall_mm' => 20.0, 'humidity_percentage' => 56, 'advisory' => 'Dry season begins — irrigate young plants and mulch to retain moisture.'],
                ['condition' => 'Sunny', 'temperature_min' => 19.0, 'temperature_max' => 29.0, 'rainfall_mm' => 12.0, 'humidity_percentage' => 50, 'advisory' => 'Driest month — monitor seedlings closely for heat stress.'],
                ['condition' => 'Partly Cloudy', 'temperature_min' => 19.0, 'temperature_max' => 27.0, 'rainfall_mm' => 30.0, 'humidity_percentage' => 55, 'advisory' => 'Early signs of the long rains — begin land preparation for next planting.'],
            ],
        ];

        $sortOrderByRegion = collect($regions)->pluck('sort_order', 'region');

        foreach ($monthlyOutlook as $regionName => $months) {
            foreach ($months as $monthIndex => $month) {
                WeatherForecast::updateOrCreate(
                    [
                        'region' => $regionName,
                        'forecast_date' => now()->addMonthsNoOverflow($monthIndex + 1)->startOfMonth()->toDateString(),
                    ],
                    [
                        ...$month,
                        'wind_speed_kmh' => null,
                        'is_active' => true,
                        'sort_order' => $sortOrderByRegion[$regionName] ?? 0,
                    ],
                );
            }
        }
    }
}
