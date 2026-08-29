<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BatchActivity;
use App\Models\User;
use App\Services\BatchActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_an_activity_persists_it_against_the_batch(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch($user);

        $activity = app(BatchActivityService::class)->record(
            $batch,
            'collection',
            $batch->batch_number,
            $user->id,
        );

        $this->assertDatabaseHas('batch_activities', [
            'id' => $activity->id,
            'batch_id' => $batch->id,
            'user_id' => $user->id,
            'event' => 'collection',
            'description' => $batch->batch_number,
        ]);
    }

    public function test_for_batch_returns_only_that_batchs_activities_most_recent_first(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch($user);
        $otherBatch = $this->makeBatch($user, 'BATCH-OTHER-01');

        $service = app(BatchActivityService::class);
        $service->record($batch, 'collection', $batch->batch_number, $user->id);
        $service->record($batch, 'quality_control', 'Moisture: 11.8%', $user->id);
        $service->record($otherBatch, 'collection', $otherBatch->batch_number, $user->id);

        $activities = $service->forBatch($batch);

        $this->assertCount(2, $activities);
        $this->assertSame('quality_control', $activities->first()->event);
        $this->assertTrue($activities->every(fn (BatchActivity $a) => $a->batch_id === $batch->id));
        $this->assertSame($user->id, $activities->first()->user->id);
    }

    /**
     * Create a persisted batch with the minimum required attributes.
     */
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
