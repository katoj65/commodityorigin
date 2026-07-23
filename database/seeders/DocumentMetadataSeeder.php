<?php

namespace Database\Seeders;

use App\Models\DocumentMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $items = [
            'harvest intake sheet',
            'quality assessment report',
            'moisture analysis record',
            'delivery note',
            'purchase receipt',
            'inspection certificate',
            'transport manifest',
            'compliance attachment',
            'images',
        
        ];

        foreach ($items as $index => $item) {
            DocumentMetadata::query()->updateOrCreate(
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
