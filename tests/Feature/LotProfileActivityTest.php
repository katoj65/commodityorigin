<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\LotActivity;
use App\Models\LotActivityMetadata;
use App\Models\User;
use Database\Seeders\LotActivityMetadataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LotProfileActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_creator_can_record_an_activity_via_the_http_endpoint(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);
        (new LotActivityMetadataSeeder())->run();

        $response = $this->actingAs($creator)->post(route('lot.activities.store', $lot), [
            'event' => 'inspection',
            'description' => 'Passed export compliance check.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('lot_activities', [
            'lot_id' => $lot->id,
            'user_id' => $creator->id,
            'event' => 'inspection',
            'description' => 'Passed export compliance check.',
        ]);
    }

    public function test_a_non_creator_cannot_record_an_activity(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $lot = $this->makeLot($creator);
        (new LotActivityMetadataSeeder())->run();

        $response = $this->actingAs($stranger)->post(route('lot.activities.store', $lot), [
            'event' => 'inspection',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('lot_activities', ['lot_id' => $lot->id]);
    }

    public function test_the_event_must_be_an_active_activity_metadata_slug(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);
        LotActivityMetadata::query()->create([
            'slug' => 'retired_stage',
            'name' => 'Retired Stage',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $response = $this->actingAs($creator)->post(route('lot.activities.store', $lot), [
            'event' => 'retired_stage',
        ]);
        $response->assertSessionHasErrors('event');

        $response = $this->actingAs($creator)->post(route('lot.activities.store', $lot), [
            'event' => 'not-a-real-slug',
        ]);
        $response->assertSessionHasErrors('event');
    }

    public function test_the_lot_profile_page_exposes_activities_most_recent_first_and_the_option_list(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);
        (new LotActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('lot.activities.store', $lot), ['event' => 'assessment']);
        $this->actingAs($creator)->post(route('lot.activities.store', $lot), ['event' => 'blockchain', 'description' => 'Committed to chain.']);

        $this->actingAs($creator)->get(route('lot.show', $lot))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Lot/LotProfile')
                ->has('activities', 2)
                ->where('activities.0.event', 'blockchain')
                ->where('activities.0.description', 'Committed to chain.')
                ->where('activities.0.recorded_by.name', $creator->name)
                ->where('activities.1.event', 'assessment')
                ->has('activityOptions', 12)
            );
    }

    public function test_the_creator_can_delete_an_activity(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);
        (new LotActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('lot.activities.store', $lot), ['event' => 'assessment']);
        $activity = LotActivity::query()->where('lot_id', $lot->id)->firstOrFail();

        $response = $this->actingAs($creator)->delete(route('lot.activities.destroy', [$lot, $activity]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseMissing('lot_activities', ['id' => $activity->id]);
    }

    public function test_a_non_creator_cannot_delete_an_activity(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $lot = $this->makeLot($creator);
        (new LotActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('lot.activities.store', $lot), ['event' => 'assessment']);
        $activity = LotActivity::query()->where('lot_id', $lot->id)->firstOrFail();

        $response = $this->actingAs($stranger)->delete(route('lot.activities.destroy', [$lot, $activity]));

        $response->assertForbidden();
        $this->assertDatabaseHas('lot_activities', ['id' => $activity->id]);
    }

    public function test_an_activity_belonging_to_a_different_lot_cannot_be_deleted_through_this_lot(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);
        $otherLot = $this->makeLot($creator);
        (new LotActivityMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('lot.activities.store', $otherLot), ['event' => 'assessment']);
        $activity = LotActivity::query()->where('lot_id', $otherLot->id)->firstOrFail();

        $response = $this->actingAs($creator)->delete(route('lot.activities.destroy', [$lot, $activity]));

        $response->assertNotFound();
        $this->assertDatabaseHas('lot_activities', ['id' => $activity->id]);
    }

    /**
     * Create a persisted lot with the minimum required attributes.
     */
    private function makeLot(User $user): Lot
    {
        return Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
            'net_weight_kg' => 600,
            'price' => 5.5,
        ]);
    }
}
