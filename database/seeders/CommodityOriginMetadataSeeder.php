<?php

namespace Database\Seeders;

use App\Models\CommodityOriginMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommodityOriginMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Ugandan coffee-growing origins only — finer-grained than the
     * country-level OriginMetadata list, for tagging lots/batches/markets
     * with the specific Ugandan region a coffee was grown in.
     */
    public function run(): void
    {
        $origins = [
            ['slug' => 'bugisu-mount-elgon', 'name' => 'Bugisu (Mount Elgon)', 'region' => 'Eastern Uganda', 'flavor_profile' => 'Winey, citrus acidity, full body'],
            ['slug' => 'rwenzori', 'name' => 'Rwenzori', 'region' => 'Western Uganda', 'flavor_profile' => 'Chocolate, dried fruit, balanced acidity'],
            ['slug' => 'west-nile', 'name' => 'West Nile', 'region' => 'Northern Uganda', 'flavor_profile' => 'Bright acidity, floral, tea-like'],
            ['slug' => 'busoga', 'name' => 'Busoga', 'region' => 'Eastern Uganda', 'flavor_profile' => 'Nutty, mild body, low acidity'],
            ['slug' => 'buganda-central', 'name' => 'Buganda (Central)', 'region' => 'Central Uganda', 'flavor_profile' => 'Earthy, full body, chocolate'],
            ['slug' => 'kigezi-ankole', 'name' => 'Kigezi / Ankole', 'region' => 'South Western Uganda', 'flavor_profile' => 'Fruity, wine-like, bright acidity'],
            ['slug' => 'acholi-lango', 'name' => 'Acholi / Lango', 'region' => 'Northern Uganda', 'flavor_profile' => 'Bold, earthy, heavy body'],
            ['slug' => 'sipi-falls', 'name' => 'Sipi Falls', 'region' => 'Eastern Uganda', 'flavor_profile' => 'Floral, citrus, clean finish'],
        ];

        foreach ($origins as $i => $origin) {
            CommodityOriginMetadata::query()->updateOrCreate(
                ['slug' => $origin['slug']],
                [
                    'name' => $origin['name'],
                    'region' => $origin['region'],
                    'flavor_profile' => $origin['flavor_profile'],
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ],
            );
        }
    }
}
