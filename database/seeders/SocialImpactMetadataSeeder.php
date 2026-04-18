<?php

namespace Database\Seeders;

use App\Models\SocialImpactMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SocialImpactMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Youth Employment',
            'Women-led Initiative',
            'Community Training',
            'School Support Program',
            'Health Outreach',
            'fair wage',
            'child labor free',
            'community engagement',
            'farmer income level '
        ];

        foreach ($items as $index => $item) {
            SocialImpactMetadata::query()->updateOrCreate(
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
