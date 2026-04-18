<?php

namespace Database\Seeders;

use App\Models\DocumentationMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentationMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Farmer Registration Form',
            'Land Ownership Record',
            'Input Usage Log',
            'Harvest Ledger',
            'Audit Trail Document',
            'quality certificates',
            'inspection reports',
            'compliance records',
            
        ];

        foreach ($items as $index => $item) {
            DocumentationMetadata::query()->updateOrCreate(
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
