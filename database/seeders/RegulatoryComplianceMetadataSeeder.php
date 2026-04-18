<?php

namespace Database\Seeders;

use App\Models\RegulatoryComplianceMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegulatoryComplianceMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'National Registration',
            'Tax Compliance',
            'Export Clearance',
            'Food Safety Compliance',
            'Traceability Reporting',
            'EU deforestation compliant',
            'oecd supply chain compliant',
            'export license',
            'traceability complete'

        ];

        foreach ($items as $index => $item) {
            RegulatoryComplianceMetadata::query()->updateOrCreate(
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
