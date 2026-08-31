<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BatchActivity;
use App\Models\BatchActivityMetadata;
use App\Models\User;
use Database\Seeders\BatchActivityMetadataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchProfileActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_creator_can_record_an_activity_via_the_http_endpoint(): void
    {
        $creator = User::factory()->create();
        $batch = $this->makeBatch($creator);
        (new BatchActivityMetadataSeeder())->run();

        $response = $this->actingAs($creator)->post(route('batch.activities.store', $batch), [
            'event' => 'quality_control',
            'description' => 'Moisture: 11.8%',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('batch_activities', [
            'batch_id' => $batch->id,
            'user_id' => $creator->id,
            'event' => 'quality_control',
            'description' => 'Moisture: 11.8%',
        ]);
    }

    public function test_a_non_creator_cannot_record_an_activity(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $batch = $this->makeBatch($creator);
        (new BatchActivityMetadataSeeder())->run();

        $response = $this->actingAs($stranger)->post(route('batch.activities.store', $batch), [
            'event' => 'quality_control',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('batch_activities', ['batch_id' => $batch->id]);
    }

    public function test_the_event_must_be_an_active_activity_metadata_slug(): void
    {
        $creator = User::factory()->create();
        $batch = $this->makeBatch($creator);
        BatchActivityMetadata::query()->create([
            'slug' => 'retired_stage',
            'name' => 'Retired Stage',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $response = $this->actingAs($creator)->post(route('batch.activities.store', $batch), [
            'event' => 'retired_stage',
        ]);

        $response->assertSessionHasErrors('event');

        $response = $this->actingAs($creator)->post(route('batch.activities.store', $batch), [
            'event' => 'not-a-real-slug',
        ]);

        $response->assertSessionHasErrors('event');
    }

    public function test_the_batch_profile_page_exposes_activities_most_recent_first_and_the_option_list(): void
    {
        $creator = User::factory()->create();
        $batch = $this->makeBatch($creator);
        (new BatchActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('batch.activities.store', $batch), ['event' => 'collection']);
        $this->actingAs($creator)->post(route('batch.activities.store', $batch), ['event' => 'drying', 'description' => 'Sun-dried, day 3']);

        $this->actingAs($creator)->get(route('batch.show', $batch))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Batch/BatchProfile')
                ->has('activities', 2)
                ->where('activities.0.event', 'drying')
                ->where('activities.0.description', 'Sun-dried, day 3')
                ->where('activities.0.recorded_by.name', $creator->name)
                ->where('activities.1.event', 'collection')
                ->has('activityOptions', 14)
            );
    }

    public function test_the_creator_can_delete_an_activity(): void
    {
        $creator = User::factory()->create();
        $batch = $this->makeBatch($creator);
        (new BatchActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('batch.activities.store', $batch), ['event' => 'collection']);
        $activity = BatchActivity::query()->where('batch_id', $batch->id)->firstOrFail();

        $response = $this->actingAs($creator)->delete(route('batch.activities.destroy', [$batch, $activity]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseMissing('batch_activities', ['id' => $activity->id]);
    }

    public function test_a_non_creator_cannot_delete_an_activity(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $batch = $this->makeBatch($creator);
        (new BatchActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('batch.activities.store', $batch), ['event' => 'collection']);
        $activity = BatchActivity::query()->where('batch_id', $batch->id)->firstOrFail();

        $response = $this->actingAs($stranger)->delete(route('batch.activities.destroy', [$batch, $activity]));

        $response->assertForbidden();
        $this->assertDatabaseHas('batch_activities', ['id' => $activity->id]);
    }

    public function test_an_activity_belonging_to_a_different_batch_cannot_be_deleted_through_this_batch(): void
    {
        $creator = User::factory()->create();
        $batch = $this->makeBatch($creator);
        $otherBatch = $this->makeBatch($creator, 'BATCH-OTHER-01');
        (new BatchActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('batch.activities.store', $otherBatch), ['event' => 'collection']);
        $activity = BatchActivity::query()->where('batch_id', $otherBatch->id)->firstOrFail();

        $response = $this->actingAs($creator)->delete(route('batch.activities.destroy', [$batch, $activity]));

        $response->assertNotFound();
        $this->assertDatabaseHas('batch_activities', ['id' => $activity->id]);
    }

    private function makeBatch(User $user, string $batchNumber = 'BATCH-TEST-01'): Batch
    {
        return Batch::query()->create([
            'user_id' => $user->id,
            'batch_number' => $batchNumber,
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 10,
            'weight' => 600,
            'status' => 'received',
        ]);
    }
}
