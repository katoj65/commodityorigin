<?php

namespace App\Services;

use App\Models\FarmCollection;
use App\Models\FarmCollectionActivity;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records and retrieves a farm collection's activity log — an append-only
 * history of lifecycle events (created, quality recorded, linked to a
 * batch, etc.) used to render the collection's system-log/timeline view.
 */
class FarmCollectionActivityService
{
    /**
     * Record an activity-log entry for a farm collection. `$event` should
     * match a slug in farm_collection_activity_metadata.
     */
    public function record(
        FarmCollection $collection,
        string $event,
        ?string $description = null,
        ?int $userId = null,
    ): FarmCollectionActivity {
        return FarmCollectionActivity::query()->create([
            'farm_collection_id' => $collection->id,
            'user_id' => $userId,
            'event' => $event,
            'description' => $description,
        ]);
    }

    /**
     * Get a farm collection's activity log, most recent first.
     *
     * @return Collection<int, FarmCollectionActivity>
     */
    public function forCollection(FarmCollection $collection): Collection
    {
        return FarmCollectionActivity::query()
            ->where('farm_collection_id', $collection->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Remove a mistaken or duplicate activity-log entry.
     */
    public function delete(FarmCollectionActivity $activity): void
    {
        $activity->delete();
    }
}
