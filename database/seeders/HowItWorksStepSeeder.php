<?php

namespace Database\Seeders;

use App\Models\HowItWorksStep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HowItWorksStepSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $steps = [
            'Register & Verify' => [
                'icon' => 'person_add',
                'description' => 'Create an account and set your designation — farmer, cooperative, trader, exporter, processor, buyer, roaster, or another role in the trade.',
            ],
            'List Your Coffee' => [
                'icon' => 'inventory_2',
                'description' => 'Record farm collections, build a traceable lot with grade, variety, and quality data, then list it on the live market.',
            ],
            'Discover & Trade' => [
                'icon' => 'storefront',
                'description' => 'Buyers browse live listings and trade the way that suits them — Buy Now, Auction, Make an Offer, or a Request for Quote.',
            ],
            'Settle & Pay' => [
                'icon' => 'account_balance_wallet',
                'description' => 'Orders settle through escrow and the platform wallet, with currency conversion handled automatically.',
            ],
            'Track From Farm to Cup' => [
                'icon' => 'qr_code_2',
                'description' => 'Every lot carries a QR-backed blockchain ledger, so buyers can trace origin, batches, and activity end-to-end.',
            ],
        ];

        foreach (array_values(array_keys($steps)) as $index => $title) {
            HowItWorksStep::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'description' => $steps[$title]['description'],
                    'icon' => $steps[$title]['icon'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
