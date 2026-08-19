<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the business types a registering business can pick from —
     * spanning the coffee supply chain from farm to retail, plus the
     * supporting services that trade alongside it.
     */
    public function run(): void
    {
        $types = [
            'Coffee Farm / Grower' => 'Grows and harvests coffee cherries at the origin.',
            'Cooperative' => 'A farmer-owned group that pools harvests for processing and sale.',
            'Exporter' => 'Buys parchment or green coffee locally and ships it to international buyers.',
            'Importer' => 'Brings green coffee into a destination market for local buyers.',
            'Roaster' => 'Roasts green coffee for wholesale or retail sale.',
            'Retailer' => 'Sells roasted coffee directly to consumers, in store or online.',
            'Trader / Broker' => 'Buys and sells coffee lots between other supply chain businesses.',
            'Logistics & Shipping' => 'Handles freight, warehousing, or customs clearance for coffee shipments.',
            'Financial Institution' => 'Provides financing, credit, or payment services to supply chain businesses.',
            'Certification Body' => 'Audits and certifies farms or lots against a quality or sustainability standard.',
        ];

        $index = 0;
        foreach ($types as $name => $description) {
            $index++;

            BusinessType::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
