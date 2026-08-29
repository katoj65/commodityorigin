<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\User;
use App\Services\LotActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LotActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_an_activity_persists_it_against_the_lot(): void
    {
        $user = User::factory()->create();
        $lot = $this->makeLot($user);

        $activity = app(LotActivityService::class)->record(
            $lot,
            'assessment',
            $lot->lot_number,
            $user->id,
        );

        $this->assertDatabaseHas('lot_activities', [
            'id' => $activity->id,
            'lot_id' => $lot->id,
            'user_id' => $user->id,
            'event' => 'assessment',
            'description' => $lot->lot_number,
        ]);
    }

    public function test_for_lot_returns_only_that_lots_activities_most_recent_first(): void
    {
        $user = User::factory()->create();
        $lot = $this->makeLot($user);
        $otherLot = $this->makeLot($user);

        $service = app(LotActivityService::class);
        $service->record($lot, 'assessment', null, $user->id);
        $service->record($lot, 'blockchain', null, $user->id);
        $service->record($otherLot, 'assessment', null, $user->id);

        $activities = $service->forLot($lot);

        $this->assertCount(2, $activities);
        $this->assertSame('blockchain', $activities->first()->event);
        $this->assertTrue($activities->every(fn ($a) => $a->lot_id === $lot->id));
        $this->assertSame($user->id, $activities->first()->user->id);
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
        ]);
    }
}
