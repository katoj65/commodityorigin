<?php

namespace Database\Seeders;

use App\Models\DeliveryTermsMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeliveryTermsMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The delivery terms a market listing can be tagged with.
     */
    public function run(): void
    {
        $items = [
            'Ex Works (EXW)',
            'Free Carrier (FCA)',
            'Free on Board (FOB)',
            'Cost and Freight (CFR)',
            'Cost, Insurance & Freight (CIF)',
            'Carriage Paid To (CPT)',
            'Delivered at Place (DAP)',
            'Delivered Duty Paid (DDP)',
        ];

        foreach ($items as $index => $item) {
            DeliveryTermsMetadata::query()->updateOrCreate(
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
