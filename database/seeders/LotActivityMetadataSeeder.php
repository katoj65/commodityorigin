<?php

namespace Database\Seeders;

use App\Models\LotActivityMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LotActivityMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The market-readiness stages a lot_activities row's `event` column
     * can hold — every operation a lot goes through from assessment to
     * sale closure. `slug` is the exact machine code
     * LotActivityService::record() is expected to receive in `event`.
     */
    public function run(): void
    {
        $items = [
            ['slug' => 'assessment', 'name' => 'Assessment', 'description' => 'The lot is assessed for quality, grade, and export readiness.'],
            ['slug' => 'sampling', 'name' => 'Sampling', 'description' => 'A sample is drawn from the lot for cupping or lab analysis.'],
            ['slug' => 'inspection', 'name' => 'Inspection', 'description' => "The lot is inspected for compliance and physical condition."],
            ['slug' => 'verification', 'name' => 'Verification', 'description' => "The lot's weight, quality, and origin are verified."],
            ['slug' => 'documentation', 'name' => 'Documentation', 'description' => 'Certificates, contracts, or export documents are attached to the lot.'],
            ['slug' => 'packaging', 'name' => 'Packaging', 'description' => 'The lot is packaged for storage, transport, or sale.'],
            ['slug' => 'approval', 'name' => 'Approval', 'description' => 'The lot is approved for listing or export.'],
            ['slug' => 'blockchain', 'name' => 'Blockchain', 'description' => 'The lot is committed to the traceability blockchain.'],
            ['slug' => 'market_preparation', 'name' => 'Market Preparation', 'description' => 'The lot is prepared for listing on the market (pricing, positioning, etc.).'],
            ['slug' => 'publication', 'name' => 'Publication', 'description' => 'The lot is published to the live market for buyers.'],
            ['slug' => 'reservation', 'name' => 'Reservation', 'description' => 'The lot is reserved or placed on hold by a buyer.'],
            ['slug' => 'closure', 'name' => 'Closure', 'description' => "The lot's sale or trade is closed and finalized."],
        ];

        foreach ($items as $index => $item) {
            LotActivityMetadata::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
