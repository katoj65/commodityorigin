<?php

namespace Database\Seeders;

use App\Models\PriceIndex;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceIndexSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the commodity price indexes.
     */
    public function run(): void
    {
        $items = [
            ['item' => 'Uganda Robusta', 'current_price' => 9.20, 'percentage_fluctuation' => 1.20, 'status' => 'active'],
            ['item' => 'Uganda Arabica', 'current_price' => 12.80, 'percentage_fluctuation' => 0.40, 'status' => 'active'],
            ['item' => 'Kenya AA', 'current_price' => 15.60, 'percentage_fluctuation' => -0.80, 'status' => 'active'],
            ['item' => 'Ethiopia Yirgacheffe', 'current_price' => 14.20, 'percentage_fluctuation' => 2.10, 'status' => 'active'],
            ['item' => 'Tanzania Peaberry', 'current_price' => 11.40, 'percentage_fluctuation' => 0.00, 'status' => 'active'],
        ];

        foreach ($items as $item) {
            PriceIndex::query()->updateOrCreate(
                ['item' => $item['item']],
                $item,
            );
        }
    }
}
