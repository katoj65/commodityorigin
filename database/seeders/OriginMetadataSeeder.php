<?php

namespace Database\Seeders;

use App\Models\OriginMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OriginMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $origins = [
            ['slug' => 'ethiopia', 'name' => 'Ethiopia', 'region' => 'East Africa', 'flavor_profile' => 'Floral, citrus, tea-like'],
            ['slug' => 'kenya', 'name' => 'Kenya', 'region' => 'East Africa', 'flavor_profile' => 'Blackcurrant, bright acidity, wine-like'],
            ['slug' => 'uganda', 'name' => 'Uganda', 'region' => 'East Africa', 'flavor_profile' => 'Chocolate, nutty, full body'],
            ['slug' => 'rwanda', 'name' => 'Rwanda', 'region' => 'East Africa', 'flavor_profile' => 'Red fruit, floral, balanced acidity'],
            ['slug' => 'burundi', 'name' => 'Burundi', 'region' => 'East Africa', 'flavor_profile' => 'Berry, citrus, crisp acidity'],
            ['slug' => 'tanzania', 'name' => 'Tanzania', 'region' => 'East Africa', 'flavor_profile' => 'Blackcurrant, bright, wine-like'],
            ['slug' => 'colombia', 'name' => 'Colombia', 'region' => 'South America', 'flavor_profile' => 'Caramel, red fruit, balanced'],
            ['slug' => 'brazil', 'name' => 'Brazil', 'region' => 'South America', 'flavor_profile' => 'Nutty, chocolate, low acidity'],
            ['slug' => 'peru', 'name' => 'Peru', 'region' => 'South America', 'flavor_profile' => 'Mild, nutty, cocoa'],
            ['slug' => 'guatemala', 'name' => 'Guatemala', 'region' => 'Central America', 'flavor_profile' => 'Chocolate, spice, full body'],
            ['slug' => 'honduras', 'name' => 'Honduras', 'region' => 'Central America', 'flavor_profile' => 'Caramel, citrus, mild acidity'],
            ['slug' => 'costa-rica', 'name' => 'Costa Rica', 'region' => 'Central America', 'flavor_profile' => 'Honey, citrus, clean acidity'],
            ['slug' => 'nicaragua', 'name' => 'Nicaragua', 'region' => 'Central America', 'flavor_profile' => 'Cocoa, nutty, mild'],
            ['slug' => 'mexico', 'name' => 'Mexico', 'region' => 'North America', 'flavor_profile' => 'Nutty, light body, mild acidity'],
            ['slug' => 'el-salvador', 'name' => 'El Salvador', 'region' => 'Central America', 'flavor_profile' => 'Caramel, apple, balanced'],
            ['slug' => 'vietnam', 'name' => 'Vietnam', 'region' => 'Southeast Asia', 'flavor_profile' => 'Earthy, bold, heavy body (Robusta)'],
            ['slug' => 'indonesia', 'name' => 'Indonesia', 'region' => 'Southeast Asia', 'flavor_profile' => 'Earthy, herbal, full body'],
            ['slug' => 'india', 'name' => 'India', 'region' => 'South Asia', 'flavor_profile' => 'Spicy, low acidity, full body'],
            ['slug' => 'yemen', 'name' => 'Yemen', 'region' => 'Middle East', 'flavor_profile' => 'Wine-like, spice, dried fruit'],
            ['slug' => 'papua-new-guinea', 'name' => 'Papua New Guinea', 'region' => 'Oceania', 'flavor_profile' => 'Fruity, full body, mild acidity'],
        ];

        foreach ($origins as $i => $origin) {
            OriginMetadata::query()->updateOrCreate(
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
