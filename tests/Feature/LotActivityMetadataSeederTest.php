<?php

namespace Tests\Feature;

use App\Models\LotActivityMetadata;
use Database\Seeders\LotActivityMetadataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotActivityMetadataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_an_active_entry_for_every_market_readiness_stage(): void
    {
        (new LotActivityMetadataSeeder())->run();

        $expectedSlugs = [
            'assessment',
            'sampling',
            'inspection',
            'verification',
            'documentation',
            'packaging',
            'approval',
            'blockchain',
            'market_preparation',
            'publication',
            'reservation',
            'closure',
        ];

        foreach ($expectedSlugs as $slug) {
            $this->assertDatabaseHas('lot_activity_metadata', ['slug' => $slug, 'is_active' => true]);
        }

        $this->assertSame(count($expectedSlugs), LotActivityMetadata::query()->count());
    }

    public function test_seeding_is_idempotent(): void
    {
        $seeder = new LotActivityMetadataSeeder();
        $seeder->run();
        $seeder->run();

        $this->assertSame(12, LotActivityMetadata::query()->count());
    }
}
