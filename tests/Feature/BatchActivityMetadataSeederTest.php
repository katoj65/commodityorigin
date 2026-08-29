<?php

namespace Tests\Feature;

use App\Models\BatchActivityMetadata;
use Database\Seeders\BatchActivityMetadataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchActivityMetadataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_an_active_entry_for_every_batch_handling_stage(): void
    {
        (new BatchActivityMetadataSeeder())->run();

        $expectedSlugs = [
            'collection',
            'storage',
            'cleaning',
            'drying',
            'processing',
            'fermentation',
            'hulling',
            'sorting',
            'grading',
            'blending',
            'quality_control',
            'inspection',
            'packaging',
            'repackaging',
        ];

        foreach ($expectedSlugs as $slug) {
            $this->assertDatabaseHas('batch_activity_metadata', ['slug' => $slug, 'is_active' => true]);
        }

        $this->assertSame(count($expectedSlugs), BatchActivityMetadata::query()->count());
    }

    public function test_seeding_is_idempotent(): void
    {
        $seeder = new BatchActivityMetadataSeeder();
        $seeder->run();
        $seeder->run();

        $this->assertSame(14, BatchActivityMetadata::query()->count());
    }
}
