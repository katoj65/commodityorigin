<?php

namespace Database\Seeders;

use App\Models\FobMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FobMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The FOB (Free On Board) export ports Ugandan coffee actually
     * ships through. Uganda is landlocked, so every export corridor
     * routes through a neighbouring country's seaport.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'FOB Mombasa', 'port' => 'Mombasa', 'country' => 'Kenya', 'description' => 'The Northern Corridor route — the primary export gateway for Ugandan coffee.'],
            ['name' => 'FOB Dar es Salaam', 'port' => 'Dar es Salaam', 'country' => 'Tanzania', 'description' => 'The Central Corridor route via the Tanzanian port.'],
            ['name' => 'FOB Djibouti', 'port' => 'Djibouti', 'country' => 'Djibouti', 'description' => 'The Northern Route via Djibouti, used for Red Sea-bound shipments.'],
        ];

        foreach ($items as $index => $item) {
            FobMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'port' => $item['port'],
                    'country' => $item['country'],
                    'description' => $item['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
