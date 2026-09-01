<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\FarmCollection;
use App\Models\FarmCollectionActivity;
use App\Models\FarmCollectionActivityMetadata;
use App\Models\User;
use Database\Seeders\FarmCollectionActivityMetadataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmCollectionProfileActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_creator_can_record_an_activity_via_the_http_endpoint(): void
    {
        $creator = User::factory()->create();
        $collection = $this->makeCollection($creator);
        (new FarmCollectionActivityMetadataSeeder())->run();

        $response = $this->actingAs($creator)->post(route('farm-collection.activities.store', $collection), [
            'event' => 'inspection',
            'description' => 'No visible defects, moisture within range.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('farm_collection_activities', [
            'farm_collection_id' => $collection->id,
            'user_id' => $creator->id,
            'event' => 'inspection',
            'description' => 'No visible defects, moisture within range.',
        ]);
    }

    public function test_a_non_creator_cannot_record_an_activity(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $collection = $this->makeCollection($creator);
        (new FarmCollectionActivityMetadataSeeder())->run();

        $response = $this->actingAs($stranger)->post(route('farm-collection.activities.store', $collection), [
            'event' => 'inspection',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('farm_collection_activities', ['farm_collection_id' => $collection->id]);
    }

    public function test_the_event_must_be_an_active_activity_metadata_slug(): void
    {
        $creator = User::factory()->create();
        $collection = $this->makeCollection($creator);
        FarmCollectionActivityMetadata::query()->create([
            'slug' => 'retired_stage',
            'name' => 'Retired Stage',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $response = $this->actingAs($creator)->post(route('farm-collection.activities.store', $collection), [
            'event' => 'retired_stage',
        ]);
        $response->assertSessionHasErrors('event');

        $response = $this->actingAs($creator)->post(route('farm-collection.activities.store', $collection), [
            'event' => 'not-a-real-slug',
        ]);
        $response->assertSessionHasErrors('event');
    }

    public function test_the_collection_profile_page_exposes_activities_most_recent_first_and_the_option_list(): void
    {
        $creator = User::factory()->create();
        $collection = $this->makeCollection($creator);
        (new FarmCollectionActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('farm-collection.activities.store', $collection), ['event' => 'delivery']);
        $this->actingAs($creator)->post(route('farm-collection.activities.store', $collection), ['event' => 'verification', 'description' => 'Weight and origin confirmed.']);

        $this->actingAs($creator)->get(route('farm-collection.show', $collection))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('FarmCollection/FarmCollectionProfile')
                ->has('activities', 2)
                ->where('activities.0.event', 'verification')
                ->where('activities.0.description', 'Weight and origin confirmed.')
                ->where('activities.0.recorded_by.name', $creator->name)
                ->where('activities.1.event', 'delivery')
                ->has('activityOptions', 4)
            );
    }

    public function test_the_creator_can_delete_an_activity(): void
    {
        $creator = User::factory()->create();
        $collection = $this->makeCollection($creator);
        (new FarmCollectionActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('farm-collection.activities.store', $collection), ['event' => 'delivery']);
        $activity = FarmCollectionActivity::query()->where('farm_collection_id', $collection->id)->firstOrFail();

        $response = $this->actingAs($creator)->delete(route('farm-collection.activities.destroy', [$collection, $activity]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseMissing('farm_collection_activities', ['id' => $activity->id]);
    }

    public function test_a_non_creator_cannot_delete_an_activity(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $collection = $this->makeCollection($creator);
        (new FarmCollectionActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('farm-collection.activities.store', $collection), ['event' => 'delivery']);
        $activity = FarmCollectionActivity::query()->where('farm_collection_id', $collection->id)->firstOrFail();

        $response = $this->actingAs($stranger)->delete(route('farm-collection.activities.destroy', [$collection, $activity]));

        $response->assertForbidden();
        $this->assertDatabaseHas('farm_collection_activities', ['id' => $activity->id]);
    }

    public function test_an_activity_belonging_to_a_different_collection_cannot_be_deleted_through_this_collection(): void
    {
        $creator = User::factory()->create();
        $collection = $this->makeCollection($creator);
        $otherCollection = $this->makeCollection($creator, 'COL-OTHER-01');
        (new FarmCollectionActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('farm-collection.activities.store', $otherCollection), ['event' => 'delivery']);
        $activity = FarmCollectionActivity::query()->where('farm_collection_id', $otherCollection->id)->firstOrFail();

        $response = $this->actingAs($creator)->delete(route('farm-collection.activities.destroy', [$collection, $activity]));

        $response->assertNotFound();
        $this->assertDatabaseHas('farm_collection_activities', ['id' => $activity->id]);
    }

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
