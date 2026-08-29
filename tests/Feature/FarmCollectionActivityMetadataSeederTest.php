<?php

namespace Tests\Feature;

use App\Models\FarmCollectionActivityMetadata;
use Database\Seeders\FarmCollectionActivityMetadataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmCollectionActivityMetadataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_an_active_entry_for_every_intake_handling_stage(): void
    {
        (new FarmCollectionActivityMetadataSeeder())->run();

        $expectedSlugs = [
            'delivery',
            'sampling',
            'inspection',
            'verification',
        ];

        foreach ($expectedSlugs as $slug) {
            $this->assertDatabaseHas('farm_collection_activity_metadata', ['slug' => $slug, 'is_active' => true]);
        }

        $this->assertSame(count($expectedSlugs), FarmCollectionActivityMetadata::query()->count());
    }

    public function test_seeding_is_idempotent(): void
    {
        $seeder = new FarmCollectionActivityMetadataSeeder();
        $seeder->run();
        $seeder->run();

        $this->assertSame(4, FarmCollectionActivityMetadata::query()->count());
    }
}
