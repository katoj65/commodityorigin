<?php

namespace Database\Seeders;

use App\Models\PaymentMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The payment terms a market listing can be tagged with.
     */
    public function run(): void
    {
        $items = [
            '100% Advance Payment',
            '50% Deposit, Balance on Delivery',
            'Letter of Credit (L/C)',
            'Cash Against Documents (CAD)',
            'Documentary Collection',
            'Net 30',
            'Net 60',
            'Cash on Delivery (COD)',
            'Open Account',
        ];

        foreach ($items as $index => $item) {
            PaymentMetadata::query()->updateOrCreate(
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
