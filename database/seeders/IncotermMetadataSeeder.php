<?php

namespace Database\Seeders;

use App\Models\IncotermMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IncotermMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The ICC Incoterms a market listing can be tagged with.
     */
    public function run(): void
    {
        $items = [
            'EXW', 'FCA', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP', 'FAS', 'FOB', 'CFR', 'CIF',
        ];

        foreach ($items as $index => $item) {
            IncotermMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($item)],
                [
                    'name' => $item,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
