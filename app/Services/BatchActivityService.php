<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchActivity;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records and retrieves a batch's activity log — an append-only history
 * of lifecycle events (created, quality recorded, farm collection linked,
 * lot created from it, etc.) used to render the batch's system-log/
 * timeline view.
 */
class BatchActivityService
{
    /**
     * Record an activity-log entry for a batch. `$event` should match a
     * slug in batch_activity_metadata.
     */
    public function record(
        Batch $batch,
        string $event,
        ?string $description = null,
        ?int $userId = null,
    ): BatchActivity {
        return BatchActivity::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $userId,
            'event' => $event,
            'description' => $description,
        ]);
    }

    /**
     * Get a batch's activity log, most recent first.
     *
     * @return Collection<int, BatchActivity>
     */
    public function forBatch(Batch $batch): Collection
    {
        return BatchActivity::query()
            ->where('batch_id', $batch->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }
}
