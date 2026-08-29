<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\LotActivity;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records and retrieves a lot's activity log — an append-only history of
 * lifecycle events (created, batch linked, published, committed to the
 * blockchain, etc.) used to render the lot's system-log/timeline view.
 */
class LotActivityService
{
    /**
     * Record an activity-log entry for a lot. `$event` should match a
     * slug in lot_activity_metadata.
     */
    public function record(
        Lot $lot,
        string $event,
        ?string $description = null,
        ?int $userId = null,
    ): LotActivity {
        return LotActivity::query()->create([
            'lot_id' => $lot->id,
            'user_id' => $userId,
            'event' => $event,
            'description' => $description,
        ]);
    }

    /**
     * Get a lot's activity log, most recent first.
     *
     * @return Collection<int, LotActivity>
     */
    public function forLot(Lot $lot): Collection
    {
        return LotActivity::query()
            ->where('lot_id', $lot->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }
}
