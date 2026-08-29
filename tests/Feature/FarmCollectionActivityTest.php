<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\FarmCollection;
use App\Models\User;
use App\Services\FarmCollectionActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmCollectionActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_an_activity_persists_it_against_the_collection(): void
    {
        $user = User::factory()->create();
        $collection = $this->makeCollection($user);

        $activity = app(FarmCollectionActivityService::class)->record(
            $collection,
            'delivery',
            $collection->collection_code,
            $user->id,
        );

        $this->assertDatabaseHas('farm_collection_activities', [
            'id' => $activity->id,
            'farm_collection_id' => $collection->id,
            'user_id' => $user->id,
            'event' => 'delivery',
            'description' => $collection->collection_code,
        ]);
    }

    public function test_for_collection_returns_only_that_collections_activities_most_recent_first(): void
    {
        $user = User::factory()->create();
        $collection = $this->makeCollection($user, 'COL-TEST-01');
        $otherCollection = $this->makeCollection($user, 'COL-TEST-02');

        $service = app(FarmCollectionActivityService::class);
        $service->record($collection, 'delivery', null, $user->id);
        $service->record($collection, 'verification', null, $user->id);
        $service->record($otherCollection, 'delivery', null, $user->id);

        $activities = $service->forCollection($collection);

        $this->assertCount(2, $activities);
        $this->assertSame('verification', $activities->first()->event);
        $this->assertTrue($activities->every(fn ($a) => $a->farm_collection_id === $collection->id));
        $this->assertSame($user->id, $activities->first()->user->id);
    }

    /**
     * Create a persisted farm collection with the minimum required
     * attributes.
     */
    private function makeCollection(User $user, string $code = 'COL-TEST-01'): FarmCollection
    {
        $farm = Farm::query()->create(['user_id' => $user->id, 'name' => 'Test Farm']);

        return FarmCollection::query()->create([
            'user_id' => $user->id,
            'farm_id' => $farm->id,
            'collection_code' => $code,
            'status' => 'pending',
            'collection_date' => now(),
            'coffee_type' => 'Arabica',
            'quantity' => 100,
        ]);
    }
}
