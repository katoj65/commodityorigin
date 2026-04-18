<?php

namespace Database\Seeders;

use App\Models\ComplianceMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComplianceMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $requirements = [
            'National ID Verified',
            'Export License',
            'Organic Certification',
            'Fair Trade Compliance',
            'Traceability Audit',
            'Rainforest Alliance',
            'UTZ Certified',
            'ISO 14001',
            'EU_deforestation_compliant',
            'USDA Organic',
            'oecd_supply_chain_compliant',
            'export_license',
            'traceability complete'
        ];

        foreach ($requirements as $index => $requirement) {
            ComplianceMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($requirement)],
                [
                    'name' => $requirement,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
