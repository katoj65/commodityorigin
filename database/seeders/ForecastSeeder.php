<?php

namespace Database\Seeders;

use App\Models\Forecast;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ForecastSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $forecasts = [
            // Arabica price horizons.
            ['crop_type' => 'Arabica', 'category' => 'Price', 'horizon' => '7-Day', 'headline' => '+1.8%', 'detail' => 'Coffee C is consolidating near 186¢/lb; a break above 191¢ would confirm the uptrend.', 'direction' => 'up', 'confidence' => 87, 'effective_date' => now()],
            ['crop_type' => 'Arabica', 'category' => 'Price', 'horizon' => '30-Day', 'headline' => '+4.5%', 'detail' => 'Tightening global supply and steady demand growth support a continued near-term rally.', 'direction' => 'up', 'confidence' => 74, 'effective_date' => now()],
            ['crop_type' => 'Arabica', 'category' => 'Price', 'horizon' => '90-Day', 'headline' => '+9.2%', 'detail' => 'Delayed Brazilian harvest and constrained exportable surplus point to a firmer price floor into next quarter.', 'direction' => 'up', 'confidence' => 61, 'effective_date' => now()],

            // Robusta price horizons.
            ['crop_type' => 'Robusta', 'category' => 'Price', 'horizon' => '7-Day', 'headline' => '+0.9%', 'detail' => 'Vietnamese export flows remain steady, keeping short-term price movement modest.', 'direction' => 'up', 'confidence' => 81, 'effective_date' => now()],
            ['crop_type' => 'Robusta', 'category' => 'Price', 'horizon' => '30-Day', 'headline' => '+2.3%', 'detail' => 'Rising demand for instant coffee blends is gradually absorbing available stock.', 'direction' => 'up', 'confidence' => 68, 'effective_date' => now()],
            ['crop_type' => 'Robusta', 'category' => 'Price', 'horizon' => '90-Day', 'headline' => '+5.1%', 'detail' => 'Capacity easing in Vietnam could cap upside unless demand accelerates further.', 'direction' => 'up', 'confidence' => 55, 'effective_date' => now()],

            // Qualitative signal forecasts.
            ['crop_type' => 'Arabica', 'category' => 'Harvest', 'horizon' => null, 'headline' => 'Delayed 2 weeks (Brazil)', 'detail' => 'Sustained rainfall in Minas Gerais risks delaying peak harvest by up to two weeks, tightening near-term availability.', 'direction' => 'down', 'confidence' => 82, 'effective_date' => now()],
            ['crop_type' => 'Arabica', 'category' => 'Supply', 'horizon' => null, 'headline' => 'Tightening through Q3', 'detail' => 'Global supply is trailing demand by roughly 400K bags this season, the widest gap in three years.', 'direction' => 'down', 'confidence' => 79, 'effective_date' => now()],
            ['crop_type' => 'Arabica', 'category' => 'Demand', 'horizon' => null, 'headline' => 'Steady growth, +4.6% YoY', 'detail' => 'Specialty-grade demand continues to outpace overall market growth across key import regions.', 'direction' => 'up', 'confidence' => 85, 'effective_date' => now()],
            ['crop_type' => 'Robusta', 'category' => 'Export', 'horizon' => null, 'headline' => 'Vietnam capacity easing', 'detail' => 'Export capacity out of Vietnam is normalizing after prior-season logistics bottlenecks.', 'direction' => 'up', 'confidence' => 70, 'effective_date' => now()],
            ['crop_type' => 'Arabica', 'category' => 'Weather', 'horizon' => null, 'headline' => 'Elevated rainfall risk', 'detail' => 'Above-average rainfall forecast across key Brazilian growing regions over the coming weeks.', 'direction' => 'down', 'confidence' => 66, 'effective_date' => now()],
            ['crop_type' => 'Robusta', 'category' => 'Supply', 'horizon' => null, 'headline' => 'Vietnam stocks rebuilding', 'detail' => 'Farmgate stock levels are recovering after last season\'s drought-driven shortfall.', 'direction' => 'up', 'confidence' => 73, 'effective_date' => now()],
            ['crop_type' => 'Robusta', 'category' => 'Demand', 'horizon' => null, 'headline' => 'Rising in instant coffee blends', 'detail' => 'Manufacturers are increasing robusta share in blends amid cost pressure on arabica.', 'direction' => 'up', 'confidence' => 77, 'effective_date' => now()],
            ['crop_type' => 'Arabica', 'category' => 'Trade Risk', 'horizon' => null, 'headline' => 'EUDR compliance deadlines', 'detail' => 'Shipments from exporters without completed geolocation data may face disruption ahead of EUDR deadlines.', 'direction' => 'down', 'confidence' => 86, 'effective_date' => now()],
        ];

        foreach ($forecasts as $forecast) {
            Forecast::query()->updateOrCreate(
                [
                    'crop_type' => $forecast['crop_type'],
                    'category' => $forecast['category'],
                    'horizon' => $forecast['horizon'],
                ],
                $forecast,
            );
        }
    }
}
